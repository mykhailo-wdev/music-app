<?php
// backend/api/playlist_tracks.php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php'; 

header('Content-Type: application/json');

function response($status, $data = []) {
    echo json_encode(['status' => $status] + $data, JSON_UNESCAPED_UNICODE);
    exit;
}

global $pdo;
$userId = auth_user_id();
$method = $_SERVER['REQUEST_METHOD'];

// -------------------------------------------------
// GET → список треків плейлиста
// -------------------------------------------------
if ($method === 'GET') {
    $playlistId = (int)($_GET['playlist_id'] ?? 0);
    if ($playlistId <= 0) {
        http_response_code(422);
        response('error', ['message' => 'playlist_id required']);
    }

    // Перевірти власність
    $stmt = $pdo->prepare("SELECT id FROM playlists WHERE id = ? AND user_id = ?");
    $stmt->execute([$playlistId, $userId]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        response('error', ['message' => 'Playlist not found']);
    }

    $stmt = $pdo->prepare("
        SELECT id, track_source_id, track_name, artist_name, album_image,
               audio_url, duration_sec, position_idx, added_at
        FROM playlist_tracks
        WHERE playlist_id = ?
        ORDER BY position_idx ASC, id ASC
    ");
    $stmt->execute([$playlistId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Локальний час
    foreach ($rows as &$r) {
        $r['added_at_local'] = $r['added_at'] ? utcToLocal($r['added_at']) : null;
    }

    response('success', ['data' => $rows]);
}

// -------------------------------------------------
// POST → додати трек
// -------------------------------------------------
if ($method === 'POST') {
    $body = json_input();
    $playlistId    = (int)($body['playlist_id'] ?? 0);
    $trackSourceId = trim($body['track_source_id'] ?? '');
    $trackName     = trim($body['track_name'] ?? '');
    $artistName    = trim($body['artist_name'] ?? '');
    $albumImage    = trim($body['album_image'] ?? '');
    $audioUrl      = trim($body['audio_url'] ?? '');
    $durationSec   = isset($body['duration_sec']) ? (int)$body['duration_sec'] : null;

    if ($playlistId <= 0 || $trackSourceId === '' || $trackName === '' || $artistName === '' || $audioUrl === '') {
        http_response_code(422);
        response('error', ['message' => 'Required fields missing']);
    }

    // Перевірти власність
    $stmt = $pdo->prepare("SELECT id FROM playlists WHERE id = ? AND user_id = ?");
    $stmt->execute([$playlistId, $userId]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        response('error', ['message' => 'Playlist not found']);
    }

    try {
        // INSERT у UTC (заповнюємо added_at)
        $stmt = $pdo->prepare("
            INSERT INTO playlist_tracks
            (playlist_id, track_source_id, track_name, artist_name, album_image, audio_url, duration_sec, added_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$playlistId, $trackSourceId, $trackName, $artistName, $albumImage, $audioUrl, $durationSec, nowUtc()]);

        // Оновлюємо оновлення плейлиста (UTC)
        $pdo->prepare("UPDATE playlists SET updated_at = ? WHERE id = ?")->execute([nowUtc(), $playlistId]);

        response('success');
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            http_response_code(409);
            response('error', ['message' => 'Track already in playlist']);
        } else {
            http_response_code(500);
            response('error', ['message' => 'DB error']);
        }
    }
}

// -------------------------------------------------
// DELETE → видалити трек
// -------------------------------------------------
if ($method === 'DELETE') {
    $body = json_input();
    $playlistId = (int)($body['playlist_id'] ?? 0);
    $trackId    = (int)($body['track_id'] ?? 0);

    if ($playlistId <= 0 || $trackId <= 0) {
        http_response_code(422);
        response('error', ['message' => 'playlist_id and track_id required']);
    }

    // Перевірти власність
    $stmt = $pdo->prepare("SELECT id FROM playlists WHERE id = ? AND user_id = ?");
    $stmt->execute([$playlistId, $userId]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        response('error', ['message' => 'Playlist not found']);
    }

    $stmt = $pdo->prepare("DELETE FROM playlist_tracks WHERE id = ? AND playlist_id = ?");
    $stmt->execute([$trackId, $playlistId]);

    // Оновлюємо оновлення плейлиста (UTC)
    $pdo->prepare("UPDATE playlists SET updated_at = ? WHERE id = ?")->execute([nowUtc(), $playlistId]);

    response('success');
}

// Метод не підтримується
http_response_code(405);
response('error', ['message' => 'Method not allowed']);
