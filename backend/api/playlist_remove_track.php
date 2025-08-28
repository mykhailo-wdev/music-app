<?php // api/playlist_remove_track.php
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');
$userId = auth_user_id();
$body = json_input();

$playlistId = (int)($body['playlist_id'] ?? 0);
$trackId    = (int)($body['track_id'] ?? 0);
if ($playlistId<=0 || $trackId<=0) {
    http_response_code(422);
    echo json_encode(['status'=>'error','message'=>'playlist_id and track_id required']);
    exit;
}

global $pdo;
// перевірка власності плейлиста
$stmt = $pdo->prepare("SELECT id FROM playlists WHERE id = ? AND user_id = ?");
$stmt->execute([$playlistId, $userId]);
if (!$stmt->fetch()) {
    http_response_code(404);
    echo json_encode(['status'=>'error','message'=>'Playlist not found']);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM playlist_tracks WHERE id = ? AND playlist_id = ?");
$stmt->execute([$trackId, $playlistId]);

echo json_encode(['status'=>'success']);
