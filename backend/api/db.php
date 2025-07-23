<?php
$host = 'mysql';
$user = 'user';
$password = 'password';
$database = 'testdb';

$mysqli = new mysqli($host, $user, $password, $database);
if ($mysqli->connect_error) {
    die("DB connection error: " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8");
?>
