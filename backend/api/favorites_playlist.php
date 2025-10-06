<?php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json");

$headers = function_exists('getallheaders') ? getallheaders() : [];
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
$jwt = trim(preg_replace('/^Bearer\s+/i', '', $authHeader ?? '') ?? '');

// ДОДАТКОВО: дозволимо токен через query (?token=) для тестів у браузері
if (!$jwt && isset($_GET['token'])) {
    $jwt = trim($_GET['token']);
}
    
$secret_key = $_ENV['JWT_SECRET_KEY'];

try {
    if (!$jwt || substr_count($jwt, '.') !== 2) {
        http_response_code(401);
        echo json_encode([
            "status" => "error",
            "message" => "Missing or invalid JWT"
        ]);
        exit;
    }

    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));

    // ПІДГОНКА ПІД ТВОЮ СХЕМУ ПЕЙЛОАДА:
    // часто це $decoded->sub або $decoded->user_id; ти використовуєш $decoded->data->id
    $user_id = $decoded->data->id ?? $decoded->sub ?? $decoded->user_id ?? null;
    if (!$user_id) {
        throw new Exception("User ID not found in token payload");
    }

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT track_id FROM favorites WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $tracks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $playlist = [
        "id" => "favorites",
        "name" => "Favorites",
        "tracks" => array_map(fn($t) => ["id" => $t["track_id"]], $tracks)
    ];

    echo json_encode([
        "status" => "success",
        "data" => $playlist
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
