<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require 'db.php';

require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

if (!$email || !$password) {
    echo json_encode(["status" => "error", "message" => "Введіть email та пароль"]);
    exit;
}

$stmt = $mysqli->prepare("SELECT id, email, password, failed_attempts, status FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo json_encode(["status" => "error", "message" => "Невірний email або пароль"]);
    exit;
}

if ($user['status'] === 'banned') {
    echo json_encode(["status" => "error", "message" => "Ваш акаунт заблоковано"]);
    exit;
}

if ($user['failed_attempts'] >= 5) {
    echo json_encode(["status" => "error", "message" => "Акаунт тимчасово заблоковано через багато невдалих входів"]);
    exit;
}

// Перевірка пароля
if (!password_verify($password, $user['password'])) {
    $stmt = $mysqli->prepare("UPDATE users SET failed_attempts = failed_attempts + 1 WHERE id = ?");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $stmt->close();

    echo json_encode(["status" => "error", "message" => "Невірний email або пароль"]);
    exit;
}

// Скидаємо failed_attempts та оновлюємо дані входу
$stmt = $mysqli->prepare("UPDATE users SET failed_attempts = 0, last_login_at = NOW(), login_ip = ? WHERE id = ?");
$stmt->bind_param("si", $ip, $user['id']);
$stmt->execute();
$stmt->close();

// Параметри для JWT
$secret_key = "aD8SZNhKlC5McZBe2sac2YDdZ6JN7un0OJTULKgJ35w=";  
$issuer_claim = "yourdomain.com";          
$audience_claim = "yourdomain.com";        
$issuedat_claim = time();                  
$notbefore_claim = $issuedat_claim;        
$expire_claim = $issuedat_claim + 3600;    

$token = [
    "iss" => $issuer_claim,
    "aud" => $audience_claim,
    "iat" => $issuedat_claim,
    "nbf" => $notbefore_claim,
    "exp" => $expire_claim,
    "data" => [
        "id" => $user['id'],
        "email" => $user['email'],
    ]
];

// Генеруємо JWT
$jwt = JWT::encode($token, $secret_key, 'HS256');

echo json_encode([
    "status" => "success",
    "message" => "Вхід успішний",
    "token" => $jwt,
    "user" => [
        "id" => $user['id'],
        "email" => $user['email'],
    ]
]);
