<?php
header("Content-Type: application/json");
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

try {
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    $jwt = trim(preg_replace('/^Bearer\s+/i', '', $authHeader ?? '') ?? '');

    if (!$jwt && isset($_GET['token'])) {
        $jwt = trim((string)$_GET['token']);
    }

    if (!$jwt || substr_count($jwt, '.') !== 2) {
        http_response_code(401);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Missing or invalid JWT',
        ]);
        exit;
    }

    $secret_key = $_ENV['JWT_SECRET_KEY'] ?? '';
    if (!$secret_key) {
        throw new RuntimeException('JWT secret is not configured');
    }

    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));

    $user_id = $decoded->data->id
        ?? $decoded->sub
        ?? $decoded->user_id
        ?? null;

    if (!$user_id) {
        throw new RuntimeException('User ID not found in token payload');
    }

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmtTracks = $pdo->prepare("SELECT track_id FROM favorites WHERE user_id = ?");
    $stmtTracks->execute([$user_id]);
    $tracksRows = $stmtTracks->fetchAll(PDO::FETCH_ASSOC);

    $stmtMax = $pdo->prepare("
        SELECT GREATEST(
            COALESCE(MAX(updated_at), '1970-01-01 00:00:00'),
            COALESCE(MAX(created_at), '1970-01-01 00:00:00')
        ) AS updated_at
        FROM favorites
        WHERE user_id = ?
    ");
    $stmtMax->execute([$user_id]);
    $lastUpdatedUtc = $stmtMax->fetchColumn();

    if ($lastUpdatedUtc === '1970-01-01 00:00:00') {
        $lastUpdatedUtc = null;
    }

    $playlist = [
        'id'               => 'favorites',
        'name'             => 'Favorites',
        'updated_at'       => $lastUpdatedUtc,
        'updated_at_local' => $lastUpdatedUtc ? utcToLocal($lastUpdatedUtc) : null, // з db.php
        'tracks'           => array_map(
            static fn(array $t) => ['id' => $t['track_id']],
            $tracksRows ?: []
        ),
    ];

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'data'   => $playlist,
    ]);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage(),
    ]);
    exit;
}

