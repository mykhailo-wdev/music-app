<?php
// forgot_password.php
require_once __DIR__ . '/cors.php';
header('Content-Type: application/json');

require_once __DIR__ . '/db.php';       
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// --- Отримуємо email із запиту ---
$data = json_decode(file_get_contents("php://input"), true);
$email = trim($data['email'] ?? '');
$errors = [];

// --- Валідація email ---
if (empty($email)) {
    $errors['email'] = 'Введіть email';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Невірний формат email';
}

if ($errors) {
    echo json_encode(['status' => 'error', 'errors' => $errors]);
    exit;
}

// --- Перевірка користувача ---
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    $errors['email'] = 'Користувач з таким email не знайдений';
    echo json_encode(['status' => 'error', 'errors' => $errors]);
    exit;
}

// --- Видаляємо старі токени ---
$pdo->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$user['id']]);

// --- Генеруємо новий токен ---
$token = bin2hex(random_bytes(32));
$expires = date("Y-m-d H:i:s", strtotime('+1 hour'));
$stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
$stmt->execute([$user['id'], $token, $expires]);

$resetLink = "http://localhost:5173/reset-password?token=$token";


try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->SMTPDebug = 0; 
    $mail->Debugoutput = 'html';
    $mail->Host       = $_ENV['SMTP_HOST'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['SMTP_USERNAME'];
    $mail->Password   = $_ENV['SMTP_PASSWORD'];
    $mail->SMTPSecure = $_ENV['SMTP_SECURE'];
    $mail->Port       = (int)$_ENV['SMTP_PORT'];

    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    $mail->setFrom($_ENV['SMTP_FROM_EMAIL'], $_ENV['SMTP_FROM_NAME']);
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Відновлення паролю';
    $mail->Body    = "Привіт!<br><br>Щоб змінити пароль, перейдіть за посиланням:<br><a href='$resetLink'>$resetLink</a><br><br>Якщо це не ви, ігноруйте цей лист.";

    $mail->send(); 
    echo json_encode([
        'status' => 'success',
        'message' => 'Інструкції надіслані на ваш email'
    ]);
    exit();
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'errors' => ['general' => 'Лист не надіслано. Помилка: ' . $mail->ErrorInfo]
    ]);
    exit();
}
