<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization"); 

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$data = json_decode(file_get_contents("php://input"), true);
$token = trim($data['token'] ?? '');
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

if (!$token) {
    echo json_encode(["status"=>"error","message"=>"Невірний токен"]);
    exit;
}

$secret_key = "aD8SZNhKlC5McZBe2sac2YDdZ6JN7un0OJTULKgJ35w=";

try {
    $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
    $userId = $decoded->data->id;

    // --- Оновлюємо email_verified_at, статус, last_login_at та login_ip ---
    $stmt = $pdo->prepare("
        UPDATE users 
        SET email_verified_at = NOW(), 
            status = 'active',
            last_login_at = NOW(),
            login_ip = ?
        WHERE id = ?
    ");
    $stmt->execute([$ip, $userId]);

    // Генеруємо access_token і refresh_token для Vuex
    $issuedAt = time();
    $accessToken = JWT::encode([
        "iss"=>"music-app",
        "aud"=>"music-app",
        "iat"=>$issuedAt,
        "nbf"=>$issuedAt,
        "exp"=>$issuedAt + 3600,
        "data"=>["id"=>$userId]
    ], $secret_key, 'HS256');

    $refreshToken = JWT::encode([
        "iss"=>"music-app",
        "aud"=>"music-app",
        "iat"=>$issuedAt,
        "nbf"=>$issuedAt,
        "exp"=>$issuedAt + 604800,
        "data"=>["id"=>$userId]
    ], $secret_key, 'HS256');

    // Видаляємо старі refresh токени та додаємо новий
    $pdo->prepare("DELETE FROM refresh_tokens WHERE user_id=?")->execute([$userId]);
    $pdo->prepare("INSERT INTO refresh_tokens (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY))")
        ->execute([$userId, $refreshToken]);

    echo json_encode([
        "status"=>"success",
        "message"=>"Email підтверджено",
        "access_token"=>$accessToken,
        "refresh_token"=>$refreshToken,
        "user"=>["id"=>$userId]
    ]);

} catch (Exception $e) {
    echo json_encode(["status"=>"error","message"=>"Невірний токен"]);
}
