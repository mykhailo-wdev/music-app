<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header("Content-Type: application/json");
require_once __DIR__ . '/db.php';

require_once __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Не отримано JSON або некоректний JSON"]);
    exit;
}

$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');

// Перевірка заповнення
if (!$name || !$email || !$password) {
    echo json_encode(["status" => "error", "message" => "Заповніть всі поля"]);
    exit;
}

// Перевірка формату email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "error", "message" => "Невірний email"]);
    exit;
}

// Перевірка на існування користувача
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode(["status" => "error", "message" => "Користувач з таким email вже існує"]);
    exit;
}

// Хешуємо пароль
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Вставляємо користувача у БД
$stmt = $pdo->prepare("INSERT INTO users (name, email, password, status) VALUES (?, ?, ?, 'pending')");
if ($stmt->execute([$name, $email, $hashedPassword])) {
    $userId = $pdo->lastInsertId();

    // --- Створюємо JWT для верифікації email ---
    $secret_key = "aD8SZNhKlC5McZBe2sac2YDdZ6JN7un0OJTULKgJ35w=";
    $payload = [
        'iss' => 'music-app',
        'aud' => 'music-app',
        'iat' => time(),
        'exp' => time() + 3600, // токен дійсний 1 година
        'data' => [
            'id' => $userId,
            'email' => $email
        ]
    ];

    $verificationToken = JWT::encode($payload, $secret_key, 'HS256');
    $verifyLink = "http://localhost:5173/verify-email?token=$verificationToken";

    // --- Відправляємо листа ---
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.ukr.net';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'everestads@ukr.net';
        $mail->Password   = 'oT5AlM245gZwItm9';
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;

        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        $mail->setFrom('everestads@ukr.net', 'Music App');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Підтвердження email';
        $mail->Body    = "Привіт, $name!<br><br>
                          Щоб активувати акаунт, перейдіть за посиланням:<br>
                          <a href='$verifyLink'>$verifyLink</a><br><br>
                          Якщо це не ви, ігноруйте цей лист.";

        $mail->send();
        echo json_encode(['status' => 'success', 'message' => 'Реєстрація успішна! Перевірте email для підтвердження.']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Не вдалося надіслати лист. ' . $mail->ErrorInfo]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Помилка реєстрації"]);
}
