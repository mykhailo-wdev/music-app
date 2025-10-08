<?php
// backend/api/playlist.php
require_once __DIR__ . '/cors.php';      
require_once __DIR__ . '/auth.php';      

global $pdo;
$userId = auth_user_id();
$method = $_SERVER['REQUEST_METHOD'];

// GET /playlist.php
if ($method === 'GET') {
    $stmt = $pdo->prepare(
        "SELECT id, name, created_at, updated_at
         FROM playlists
         WHERE user_id = ?
         ORDER BY updated_at DESC"
    );
    $stmt->execute([$userId]);
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)], JSON_UNESCAPED_UNICODE);
    exit;
}

// POST /playlist.php  body: { name }
if ($method === 'POST') {
    $body = json_input();
    $name = trim($body['name'] ?? '');

    if ($name === '') {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'Name is required'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO playlists (user_id, name, created_at, updated_at)
             VALUES (?, ?, NOW(), NOW())"
        );
        $stmt->execute([$userId, $name]);

        $id = (int)$pdo->lastInsertId();

        $stmt = $pdo->prepare(
            "SELECT id, name, created_at, updated_at
             FROM playlists
             WHERE id = ? AND user_id = ?"
        );
        $stmt->execute([$id, $userId]);
        $playlist = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'data' => $playlist], JSON_UNESCAPED_UNICODE);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'Playlist with this name already exists'], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'DB error'], JSON_UNESCAPED_UNICODE);
        }
    }
    exit;
}

// DELETE /playlist.php?id=123
if ($method === 'DELETE') {
    $playlistId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($playlistId <= 0) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'Playlist ID is required'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Переконуємось, що плейлист належить юзеру
    $stmt = $pdo->prepare("SELECT id FROM playlists WHERE id = ? AND user_id = ?");
    $stmt->execute([$playlistId, $userId]);
    if (!$stmt->fetchColumn()) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Playlist not found'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Спочатку видалимо зв’язки треків
    $pdo->prepare("DELETE FROM playlist_tracks WHERE playlist_id = ?")->execute([$playlistId]);

    // Потім сам плейлист
    $stmt = $pdo->prepare("DELETE FROM playlists WHERE id = ? AND user_id = ?");
    $stmt->execute([$playlistId, $userId]);

    echo json_encode(['status' => 'success', 'message' => 'Playlist deleted'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Метод не підтримується
http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
exit;
