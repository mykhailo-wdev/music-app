<?php
// api/profile-user-data.php
// require_once __DIR__ . '/cors.php';
// header("Content-Type: application/json");
// require_once __DIR__ . '/db.php';
// require_once __DIR__ . '/../vendor/autoload.php';

// use Firebase\JWT\JWT;
// use Firebase\JWT\Key;

// $headers = getallheaders();
// $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';

// if (!preg_match('/Bearer\s+(\S+)/', $auth, $matches)) {
//     http_response_code(401);
//     echo json_encode(["status" => "error", "message" => "Missing token"]);
//     exit;
// }

// $jwt = $matches[1];

// try {
//     $decoded = JWT::decode($jwt, new Key($_ENV['JWT_SECRET_KEY'], 'HS256'));
//     $userId = $decoded->data->id ?? null; 

//     if (!$userId) {
//         throw new Exception("Invalid token payload");
//     }

//     $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE id = :id LIMIT 1");
//     $stmt->execute(['id' => $userId]);
//     $user = $stmt->fetch(PDO::FETCH_ASSOC);

//     if (!$user) {
//         http_response_code(404);
//         echo json_encode(["status" => "error", "message" => "User not found"]);
//         exit;
//     }

//     echo json_encode([
//         "status" => "success",
//         "user" => $user,
//     ]);

// } catch (Exception $e) {
//     http_response_code(401);
//     echo json_encode(["status" => "error", "message" => "Invalid or expired token"]);
// }

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

    $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "User not found"]);
        exit;
    }

    echo json_encode([
        "status" => "success",
        "user" => $user
    ]);

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Invalid or expired token"]);
}
