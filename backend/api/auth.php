<?php
// auth.php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json");

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

$secret_key = "aD8SZNhKlC5McZBe2sac2YDdZ6JN7un0OJTULKgJ35w=";

// невеликий запас часу, щоб уникнути фальшивих "expired"
JWT::$leeway = 60;

function getAuthorizationHeader() {
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) return trim($_SERVER['HTTP_AUTHORIZATION']);
    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        $headers = array_change_key_case($headers, CASE_LOWER);
        if (isset($headers['authorization'])) return trim($headers['authorization']);
    }
    return null;
}

function getBearerToken() {
    $header = getAuthorizationHeader();
    if ($header && preg_match('/Bearer\s(\S+)/', $header, $m)) return $m[1];
    return null;
}

function auth_user_id() {
    global $secret_key, $pdo;

    $token = getBearerToken();
    if (!$token) {
        http_response_code(401);
        header("Content-Type: application/json");
        echo json_encode(["status"=>"error","message"=>"Missing Bearer token"]);
        exit();
    }

    try {
        $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
        // перетворимо в масив для надійного доступу
        $arr = json_decode(json_encode($decoded), true);

        if (!isset($arr['data']['id'])) {
            http_response_code(401);
            header("Content-Type: application/json");
            echo json_encode(["status"=>"error","message"=>"Invalid token payload"]);
            exit();
        }

        $userId = (int)$arr['data']['id'];

        // перевірка статусу користувача
        $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $status = $stmt->fetchColumn();

        if (!$status || $status !== 'active') {
            http_response_code(403);
            header("Content-Type: application/json");
            echo json_encode(["status"=>"error","message"=>"Account not active"]);
            exit();
        }

        return $userId;

    } catch (ExpiredException $e) {
        // ВАЖЛИВО: віддавати саме 401 та саме "Expired token"
        http_response_code(401);
        header("Content-Type: application/json");
        echo json_encode(["status"=>"error","message"=>"Expired token"]);
        exit();
    } catch (\Throwable $e) {
        http_response_code(401);
        header("Content-Type: application/json");
        echo json_encode(["status"=>"error","message"=>"Invalid token"]);
        exit();
    }
}

function json_input(): array {
    $data = json_decode(file_get_contents("php://input"), true);
    return is_array($data) ? $data : [];
}
