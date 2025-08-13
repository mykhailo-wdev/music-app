<?php
$host = 'mysql';       // ім'я сервісу MySQL у Docker
$dbname = 'testdb';    // назва твоєї БД
$username = 'user';    // користувач
$password = 'password'; // пароль

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    // Кидаємо помилки як виключення
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die(json_encode([
        "status" => "error",
        "message" => "DB connection failed: " . $e->getMessage()
    ]));
}
