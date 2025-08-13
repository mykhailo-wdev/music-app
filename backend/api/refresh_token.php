<?php
header("Content-Type: application/json");
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$data = json_decode(file_get_contents("php://input"), true);
$refresh_token = $data['refresh_token'] ?? '';

if (!$refresh_token) {
    echo json_encode(["status" => "error", "message" => "Відсутній refresh token"]);
    exit;
}

$secret_key = "aD8SZNhKlC5McZBe2sac2YDdZ6JN7un0OJTULKgJ35w=";

try {
    // Декодуємо JWT
    $decoded = JWT::decode($refresh_token, new Key($secret_key, 'HS256'));
    $user_id = $decoded->data->id;

    // --- Перевіряємо токен у БД ---
    $stmt = $pdo->prepare("SELECT id, expires_at FROM refresh_tokens WHERE user_id = ? AND token = ?");
    $stmt->execute([$user_id, $refresh_token]);
    $token_row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$token_row || strtotime($token_row['expires_at']) < time()) {
        echo json_encode(["status" => "error", "message" => "Невалідний або прострочений refresh token"]);
        exit;
    }

    // --- Генеруємо новий access token ---
    $issuedat_claim = time();
    $access_token_payload = [
        "iss" => "yourdomain.com",
        "aud" => "yourdomain.com",
        "iat" => $issuedat_claim,
        "nbf" => $issuedat_claim,
        "exp" => $issuedat_claim + 3600, // 1 година
        "data" => ["id" => $user_id]
    ];
    $access_token = JWT::encode($access_token_payload, $secret_key, 'HS256');

    echo json_encode([
        "status" => "success",
        "access_token" => $access_token
    ]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Невалідний refresh token"]);
}
