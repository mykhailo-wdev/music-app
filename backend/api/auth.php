<?php
// backend/api/auth.php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

$secret_key = $_ENV['JWT_SECRET_KEY'] ?? '';
if ($secret_key === '') {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'JWT secret not configured'], JSON_UNESCAPED_UNICODE);
    exit;
}

JWT::$leeway = 60;

function getAuthorizationHeader(): ?string {
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) return trim($_SERVER['HTTP_AUTHORIZATION']);

    if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) return trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);

    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        if ($headers) {

            $headers = array_change_key_case($headers, CASE_LOWER);
            if (isset($headers['authorization'])) return trim($headers['authorization']);
        }
    }
    return null;
}

function getBearerToken(): ?string {
    $header = getAuthorizationHeader();
    if ($header && preg_match('/Bearer\s+(\S+)/i', $header, $m)) return $m[1];
    return null;
}

function json_input(): array {
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function auth_user_id(): int {
    global $secret_key, $pdo;

    $token = getBearerToken();
    if (!$token || substr_count($token, '.') !== 2) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Missing Bearer token'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));

        $arr = json_decode(json_encode($decoded), true);

        $userId = $arr['data']['id'] ?? $arr['sub'] ?? $arr['user_id'] ?? null;
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid token payload'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $userId = (int)$userId;

        $stmt = $pdo->prepare("SELECT status FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $status = $stmt->fetchColumn();

        if (!$status || $status !== 'active') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Account not active'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        return $userId;

    } catch (ExpiredException $e) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Expired token'], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (\Throwable $e) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Invalid token'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
