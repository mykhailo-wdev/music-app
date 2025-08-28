<?php // api/playlist_tracks.php
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');
$userId = auth_user_id();
$playlistId = (int)($_GET['playlist_id'] ?? 0);
if ($playlistId <= 0) {
    http_response_code(422);
    echo json_encode(['status'=>'error','message'=>'playlist_id required']);
    exit;
}
global $pdo;

// перевірка, що плейлист належить юзеру
$stmt = $pdo->prepare("SELECT id FROM playlists WHERE id = ? AND user_id = ?");
$stmt->execute([$playlistId, $userId]);
if (!$stmt->fetch()) {
    http_response_code(404);
    echo json_encode(['status'=>'error','message'=>'Playlist not found']);
    exit;
}

$stmt = $pdo->prepare("SELECT id, track_source_id, track_name, artist_name, album_image, audio_url, duration_sec, position_idx, added_at 
                       FROM playlist_tracks WHERE playlist_id = ? ORDER BY position_idx ASC, id ASC");
$stmt->execute([$playlistId]);
echo json_encode(['status'=>'success','data'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
