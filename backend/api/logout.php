<?php
// logout.php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json");

require_once __DIR__ . '/db.php'; 
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Отримуємо Authorization header
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Токен не надано']);
    exit;
}

$jwt = $matches[1];
$secret_key = $_ENV['JWT_SECRET_KEY'];

// Отримуємо refresh token із тіла запиту
$data = json_decode(file_get_contents("php://input"), true);
$refresh_token = $data['refresh_token'] ?? '';

if (!$refresh_token) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Refresh token не надано']);
    exit;
}

try {
    // Декодуємо access token
    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
    $userId = $decoded->data->id;

    // Оновлюємо статус і час останнього логауту
    $stmt = $pdo->prepare("UPDATE users SET last_logout_at = NOW(), status = 'pending' WHERE id = ?");
    $stmt->execute([$userId]);

    // Видаляємо конкретний refresh token (інвалідовуємо сесію)
    $stmt = $pdo->prepare("DELETE FROM refresh_tokens WHERE user_id = ? AND token = ?");
    $stmt->execute([$userId, $refresh_token]);

    echo json_encode(['status' => 'success', 'message' => 'Вихід виконано, статус змінено на pending']);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Невалідний токен']);
    exit;
}
