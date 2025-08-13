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
    echo json_encode(["status" => "success", "message" => "Реєстрація успішна!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Помилка реєстрації"]);
}
