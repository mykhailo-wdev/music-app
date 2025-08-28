<?php
// api/playlists.php
require_once __DIR__ . '/auth.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS, GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// --- CORS preflight ---
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

global $pdo;

// --- аутентифікація користувача ---
$userId = auth_user_id();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Список плейлистів користувача
    $stmt = $pdo->prepare("SELECT id, name, created_at, updated_at FROM playlists WHERE user_id = ? ORDER BY updated_at DESC");
    $stmt->execute([$userId]);
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit();
}

if ($method === 'POST') {
    $body = json_input();
    $name = trim($body['name'] ?? '');
    if ($name === '') {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'Name is required']);
        exit();
    }

    // Створюємо плейлист і ловимо дублікати
    try {
        $stmt = $pdo->prepare("INSERT INTO playlists (user_id, name) VALUES (?, ?)");
        $stmt->execute([$userId, $name]);
        echo json_encode([
            'status' => 'success',
            'data' => [
                'id' => (int)$pdo->lastInsertId(),
                'name' => $name
            ]
        ]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') { // duplicate
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'Playlist with this name already exists']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'DB error']);
        }
    }
    exit();
}

// --- якщо метод не GET/POST ---
http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
exit();
