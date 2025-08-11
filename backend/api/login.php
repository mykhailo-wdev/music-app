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

// Шукаємо користувача
$stmt = $mysqli->prepare("SELECT id, email, password, failed_attempts, status FROM users WHERE email = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Помилка підготовки запиту"]);
    exit;
}
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

if (!password_verify($password, $user['password'])) {
    $stmt = $mysqli->prepare("UPDATE users SET failed_attempts = failed_attempts + 1 WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user['id']);
        $stmt->execute();
        $stmt->close();
    }
    echo json_encode(["status" => "error", "message" => "Невірний email або пароль"]);
    exit;
}

// Скидаємо лічильник невдалих входів, оновлюємо last_login_at і login_ip
$stmt = $mysqli->prepare("UPDATE users SET failed_attempts = 0, last_login_at = NOW(), login_ip = ? WHERE id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Помилка оновлення даних користувача"]);
    exit;
}
$stmt->bind_param("si", $ip, $user['id']);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Не вдалося оновити дані користувача"]);
    $stmt->close();
    exit;
}
$stmt->close();

// Параметри JWT
$secret_key = "aD8SZNhKlC5McZBe2sac2YDdZ6JN7un0OJTULKgJ35w=";
$issuer_claim = "yourdomain.com";
$audience_claim = "yourdomain.com";
$issuedat_claim = time();
$notbefore_claim = $issuedat_claim;

// Access Token (1 година)
$access_token_payload = [
    "iss" => $issuer_claim,
    "aud" => $audience_claim,
    "iat" => $issuedat_claim,
    "nbf" => $notbefore_claim,
    "exp" => $issuedat_claim + 3600,
    "data" => ["id" => $user['id'], "email" => $user['email']]
];
$access_token = JWT::encode($access_token_payload, $secret_key, 'HS256');

// Refresh Token (7 днів)
$refresh_token_payload = [
    "iss" => $issuer_claim,
    "aud" => $audience_claim,
    "iat" => $issuedat_claim,
    "nbf" => $notbefore_claim,
    "exp" => $issuedat_claim + 604800,
    "data" => ["id" => $user['id']]
];
$refresh_token = JWT::encode($refresh_token_payload, $secret_key, 'HS256');

// Видаляємо старі refresh токени
$stmt = $mysqli->prepare("DELETE FROM refresh_tokens WHERE user_id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Помилка видалення старих токенів"]);
    exit;
}
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$stmt->close();

// Вставляємо новий refresh token
$stmt = $mysqli->prepare("INSERT INTO refresh_tokens (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY))");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Помилка підготовки вставки токена"]);
    exit;
}
$stmt->bind_param("is", $user['id'], $refresh_token);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Не вдалося зберегти токен"]);
    $stmt->close();
    exit;
}
$stmt->close();

// Віддаємо JSON з токенами
echo json_encode([
    "status" => "success",
    "message" => "Вхід успішний",
    "access_token" => $access_token,
    "refresh_token" => $refresh_token,
    "user" => [
        "id" => $user['id'],
        "email" => $user['email'],
    ]
]);
