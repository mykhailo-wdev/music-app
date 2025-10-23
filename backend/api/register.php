<?php
// backend/api/register.php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json");
require_once __DIR__ . '/db.php';

require_once __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Firebase\JWT\JWT;

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo json_encode(["status" => "error", "message" => "Не отримано JSON або некоректний JSON"]);
    exit;
}

$name     = trim($data['name'] ?? '');
$email    = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');
$tz       = trim($data['timezone'] ?? activeTzName()); 

if (!$name || !$email || !$password) {
    echo json_encode(["status" => "error", "message" => "Заповніть всі поля"]);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "error", "message" => "Невірний email"]);
    exit;
}


$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo json_encode(["status" => "error", "message" => "Користувач з таким email вже існує"]);
    exit;
}

// Валідність таймзони
try { new DateTimeZone($tz); } catch (Exception $e) { $tz = 'UTC'; }

// Створення користувача (все по UTC)
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
$stmt = $pdo->prepare("
  INSERT INTO users (name, email, password, timezone, status, created_at)
  VALUES (?, ?, ?, ?, 'pending', ?)
");
$stmt->execute([$name, $email, $hashedPassword, $tz, nowUtc()]);
$userId = (int)$pdo->lastInsertId();

// JWT для підтвердження email (1 година)
$secret_key = $_ENV['JWT_SECRET_KEY'];
$payload = [
    'iss' => 'music-app',
    'aud' => 'music-app',
    'iat' => time(),
    'exp' => time() + 3600,
    'data' => ['id' => $userId, 'email' => $email]
];
$verificationToken = JWT::encode($payload, $secret_key, 'HS256');
$verifyLink = "http://localhost:5173/verify-email?token=$verificationToken";

// Надсилаємо лист
try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = $_ENV['SMTP_HOST'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['SMTP_USERNAME'];
    $mail->Password   = $_ENV['SMTP_PASSWORD'];
    $mail->SMTPSecure = $_ENV['SMTP_SECURE'];
    $mail->Port       = (int)$_ENV['SMTP_PORT'];
    $mail->CharSet    = 'UTF-8';
    $mail->Encoding   = 'base64';

    $mail->setFrom($_ENV['SMTP_FROM_EMAIL'], $_ENV['SMTP_FROM_NAME']);
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Підтвердження email';
    $mail->Body    = "Привіт, $name!<br><br>Щоб активувати акаунт, перейдіть за посиланням:<br>
                      <a href='$verifyLink'>$verifyLink</a><br><br>Якщо це не ви, ігноруйте цей лист.";

    $mail->send();
    echo json_encode(['status' => 'success', 'message' => 'Реєстрація успішна! Перевірте email для підтвердження.']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Не вдалося надіслати лист. ' . $mail->ErrorInfo]);
}
