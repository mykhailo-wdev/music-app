<?php // api/playlist_add_track.php
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');
$userId = auth_user_id();
$body = json_input();

$playlistId     = (int)($body['playlist_id'] ?? 0);
$trackSourceId  = trim($body['track_source_id'] ?? '');
$trackName      = trim($body['track_name'] ?? '');
$artistName     = trim($body['artist_name'] ?? '');
$albumImage     = trim($body['album_image'] ?? '');
$audioUrl       = trim($body['audio_url'] ?? '');
$durationSec    = isset($body['duration_sec']) ? (int)$body['duration_sec'] : null;

if ($playlistId <= 0 || $trackSourceId === '' || $trackName === '' || $artistName === '' || $audioUrl === '') {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Required fields missing']);
    exit;
}

global $pdo;
// перевірка власності плейлиста
$stmt = $pdo->prepare("SELECT id FROM playlists WHERE id = ? AND user_id = ?");
$stmt->execute([$playlistId, $userId]);
if (!$stmt->fetch()) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Playlist not found']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO playlist_tracks 
        (playlist_id, track_source_id, track_name, artist_name, album_image, audio_url, duration_sec) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$playlistId, $trackSourceId, $trackName, $artistName, $albumImage, $audioUrl, $durationSec]);
    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') { // дубль
        http_response_code(409);
        echo json_encode(['status' => 'error', 'message' => 'Track already in playlist']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'DB error']);
    }
}
