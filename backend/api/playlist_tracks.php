<?php
//playlist_tracks.php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');
$userId = auth_user_id();
$method = $_SERVER['REQUEST_METHOD'];
global $pdo;

function response($status, $data = []) {
    echo json_encode(['status' => $status] + $data);
    exit;
}

// -------------------------------------------------
// GET → список треків плейлиста
// -------------------------------------------------
if ($method === 'GET') {
    $playlistId = (int)($_GET['playlist_id'] ?? 0);
    if ($playlistId <= 0) {
        http_response_code(422);
        response('error', ['message' => 'playlist_id required']);
    }

    $stmt = $pdo->prepare("SELECT id FROM playlists WHERE id = ? AND user_id = ?");
    $stmt->execute([$playlistId, $userId]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        response('error', ['message' => 'Playlist not found']);
    }

    $stmt = $pdo->prepare("SELECT id, track_source_id, track_name, artist_name, album_image, audio_url, duration_sec, position_idx, added_at 
                           FROM playlist_tracks 
                           WHERE playlist_id = ? 
                           ORDER BY position_idx ASC, id ASC");
    $stmt->execute([$playlistId]);
    response('success', ['data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
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

    $stmt = $pdo->prepare("SELECT id FROM playlists WHERE id = ? AND user_id = ?");
    $stmt->execute([$playlistId, $userId]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        response('error', ['message' => 'Playlist not found']);
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO playlist_tracks 
            (playlist_id, track_source_id, track_name, artist_name, album_image, audio_url, duration_sec) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$playlistId, $trackSourceId, $trackName, $artistName, $albumImage, $audioUrl, $durationSec]);
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

    $stmt = $pdo->prepare("SELECT id FROM playlists WHERE id = ? AND user_id = ?");
    $stmt->execute([$playlistId, $userId]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        response('error', ['message' => 'Playlist not found']);
    }

    $stmt = $pdo->prepare("DELETE FROM playlist_tracks WHERE id = ? AND playlist_id = ?");
    $stmt->execute([$trackId, $playlistId]);

    response('success');
}

// Якщо метод не підтримується
http_response_code(405);
response('error', ['message' => 'Method not allowed']);
