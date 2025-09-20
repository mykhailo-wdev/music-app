<?php
// get-users.php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json");

require 'db.php';

$result = $mysqli->query("SELECT * FROM users");

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode($users);
