<?php
// api/profile-user-tracks.php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json");

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
$jwt = str_replace('Bearer ', '', $authHeader);

if (!$jwt) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Missing token"]);
    exit;
}

try {
    $decoded = JWT::decode($jwt, new Key($_ENV['JWT_SECRET_KEY'], 'HS256'));
    $userId = $decoded->data->id ?? null;

    if (!$userId) {
        throw new Exception("Invalid token payload");
    }

    $stmt = $pdo->prepare("
        SELECT COUNT(pt.id) AS total_tracks
        FROM playlist_tracks pt
        INNER JOIN playlists p ON p.id = pt.playlist_id
        WHERE p.user_id = :user_id
    ");
    $stmt->execute(['user_id' => $userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $tracksCount = (int)($result['total_tracks'] ?? 0);

    echo json_encode([
        "status" => "success",
        "count" => $tracksCount
    ]);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Invalid or expired token"]);
}
