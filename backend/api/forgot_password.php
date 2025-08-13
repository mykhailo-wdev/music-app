<?php
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $origin");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header('Content-Type: application/json');

require_once __DIR__ . '/db.php';       
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



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

// --- Відправка листа через PHPMailer ---
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->SMTPDebug = 2; // поставити 2 для логів
    $mail->Debugoutput = 'html';
    $mail->Host       = 'smtp.ukr.net';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'everestads@ukr.net'; // твій email
    $mail->Password   = 'oT5AlM245gZwItm9';   // пароль додатку
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;

    $mail->setFrom('everestads@ukr.net', 'Music App');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Відновлення паролю';
    $mail->Body    = "Привіт!<br><br>Щоб змінити пароль, перейдіть за посиланням:<br><a href='$resetLink'>$resetLink</a><br><br>Якщо це не ви, ігноруйте цей лист.";

    var_dump($resetLink);
    $mail->send();

    echo json_encode(['status' => 'success', 'message' => 'Інструкції надіслані на ваш email']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'errors' => ['general' => 'Лист не надіслано. Помилка: ' . $mail->ErrorInfo]]);
}
