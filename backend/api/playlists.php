<?php
// CORS headers
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/auth.php';


global $pdo;
$userId = auth_user_id();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->prepare("SELECT id, name, created_at, updated_at 
                           FROM playlists 
                           WHERE user_id = ? 
                           ORDER BY updated_at DESC");
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

    try {
        $stmt = $pdo->prepare("INSERT INTO playlists (user_id, name, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
        $stmt->execute([$userId, $name]);

        $id = (int)$pdo->lastInsertId();

        $stmt = $pdo->prepare("SELECT id, name, created_at, updated_at 
                               FROM playlists 
                               WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        $playlist = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'data' => $playlist
        ]);
        
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') { 
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'Playlist with this name already exists']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'DB error']);
        }
    }
    exit();
}

if ($method === 'DELETE') {
    parse_str($_SERVER['QUERY_STRING'] ?? '', $query);
    $playlistId = (int)($query['id'] ?? 0);

    if ($playlistId <= 0) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'Playlist ID is required']);
        exit();
    }

    $stmt = $pdo->prepare("SELECT id FROM playlists WHERE id = ? AND user_id = ?");
    $stmt->execute([$playlistId, $userId]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Playlist not found']);
        exit();
    }

    $pdo->prepare("DELETE FROM playlist_tracks WHERE playlist_id = ?")->execute([$playlistId]);

    $stmt = $pdo->prepare("DELETE FROM playlists WHERE id = ? AND user_id = ?");
    $stmt->execute([$playlistId, $userId]);

    echo json_encode(['status' => 'success', 'message' => 'Playlist deleted']);
    exit();
}

// --- Якщо метод не підтримується ---
http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
exit();
