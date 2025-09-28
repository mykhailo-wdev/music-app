<?php
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
    echo json_encode(["status" => "error", "message" => "User not authenticated"]);
    exit;
}

// --- Декодуємо токен ---
$secret_key = "aD8SZNhKlC5McZBe2sac2YDdZ6JN7un0OJTULKgJ35w=";
try {
    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
    $user_id = $decoded->data->id ?? null;
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Invalid token"]);
    exit;
}

if (!$user_id) {
    echo json_encode(["status" => "error", "message" => "User not authenticated"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":
        $stmt = $pdo->prepare("SELECT track_id FROM favorites WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $tracks = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo json_encode(["status" => "success", "favorites" => $tracks]);
        break;

    case "POST": 
        $data = json_decode(file_get_contents("php://input"), true);
        $track_id = $data['track_id'] ?? null;
        if ($track_id) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO favorites (user_id, track_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $track_id]);
            echo json_encode(["status" => "success", "message" => "Added"]);
        } else {
            echo json_encode(["status" => "error", "message" => "track_id required"]);
        }
        break;

    case "DELETE": 
        $data = json_decode(file_get_contents("php://input"), true);
        $track_id = $data['track_id'] ?? null;
        if ($track_id) {
            $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND track_id = ?");
            $stmt->execute([$user_id, $track_id]);
            echo json_encode(["status" => "success", "message" => "Removed"]);
        } else {
            echo json_encode(["status" => "error", "message" => "track_id required"]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Method not allowed"]);
}
