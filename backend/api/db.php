<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$host = $_ENV['DB_HOST'];
$dbname = $_ENV['DB_NAME'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    $pdo->exec("SET time_zone = '+00:00'");
    date_default_timezone_set('UTC');

    $clientTz = $_SERVER['HTTP_X_TIMEZONE'] ?? null;

    function activeTzName(): string {
        global $clientTz;
        if ($clientTz) {
            try { new DateTimeZone($clientTz); return $clientTz; } catch (Exception $e) {}
        }
        return 'UTC';
    }
    function tz(): DateTimeZone { return new DateTimeZone(activeTzName()); }
    function nowUtc(): string { return gmdate('Y-m-d H:i:s'); }
    function utcToLocal(string $utc): string {
        $d = new DateTimeImmutable($utc, new DateTimeZone('UTC'));
        return $d->setTimezone(tz())->format('Y-m-d H:i:s');
    }

} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(["status"=>"error","message"=>"DB connection failed: ".$e->getMessage()]));
}

