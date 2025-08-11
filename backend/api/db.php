<?php
$host = 'mysql';
$user = 'user';
$password = 'password';
$database = 'testdb';

$mysqli = new mysqli($host, $user, $password, $database);
if ($mysqli->connect_error) {
    die(json_encode(["status" => "error", "message" => "DB connection error: " . $mysqli->connect_error]));
}
$mysqli->set_charset("utf8mb4");
?>
