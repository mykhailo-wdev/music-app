<?php
// api/profile-user-playlist.php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json");
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
$jwt = str_replace('Bearer ', '', $authHeader);

if (!$jwt) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Missing token"]);
    exit;
}

$secret_key = $_ENV['JWT_SECRET_KEY'];

try {
    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
    $user_id = $decoded->data->id ?? null;

    if (!$user_id) {
        throw new Exception("Invalid token payload");
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) as total_playlists FROM playlists WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $playlistCount = (int)($result['total_playlists'] ?? 0);

    echo json_encode([
        "status" => "success",
        "count" => $playlistCount
    ]);

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Invalid or expired token"]);
}
