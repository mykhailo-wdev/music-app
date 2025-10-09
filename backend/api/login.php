<?php
// backend/api/login.php
require_once __DIR__ . '/cors.php';
header("Content-Type: application/json");
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;

$data = json_decode(file_get_contents("php://input"), true);
$email    = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');
$tz       = trim($data['timezone'] ?? activeTzName()); 
$ip       = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

if (!$email || !$password) {
    echo json_encode([
        "status" => "error",
        "message" => "Введіть email та пароль",
        "errors" => [
            "email" => !$email ? "Введіть email" : "",
            "password" => !$password ? "Введіть пароль" : ""
        ]
    ]);
    exit;
}

// Знайти користувача
$stmt = $pdo->prepare("SELECT id, email, password, failed_attempts, status FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode([
        "status" => "error",
        "message" => "Невірний email або пароль",
        "errors" => ["general" => "Невірний логін або пароль"]
    ]);
    exit;
}

// Статус banned
if ($user['status'] === 'banned') {
    echo json_encode(["status" => "error", "message" => "Ваш акаунт заблоковано"]);
    exit;
}

// Ліміт спроб
if ((int)$user['failed_attempts'] >= 5) {
    $pdo->prepare("UPDATE users SET status = 'banned' WHERE id = ?")->execute([$user['id']]);
    echo json_encode(["status" => "error", "message" => "Акаунт тимчасово заблоковано через багато невдалих входів"]);
    exit;
}

// Перевірка пароля
if (!password_verify($password, $user['password'])) {
    $pdo->prepare("
        UPDATE users
        SET failed_attempts = failed_attempts + 1,
            status = CASE WHEN failed_attempts + 1 >= 5 THEN 'banned' ELSE status END
        WHERE id = ?
    ")->execute([$user['id']]);

    $st = $pdo->prepare("SELECT status FROM users WHERE id = ?");
    $st->execute([$user['id']]);
    if ($st->fetchColumn() === 'banned') {
        echo json_encode(["status" => "error", "message" => "Акаунт тимчасово заблоковано через багато невдалих входів"]);
        exit;
    }

    echo json_encode([
        "status" => "error",
        "message" => "Невірний email або пароль",
        "errors" => ["general" => "Невірний логін або пароль"]
    ]);
    exit;
}

// Валідність таймзони
try { new DateTimeZone($tz); } catch (Exception $e) { $tz = 'UTC'; }

// Успішний логін: скидаємо лічильник, оновлюємо last_login_at (UTC), IP, статус і timezone
$pdo->prepare("
    UPDATE users
    SET failed_attempts = 0,
        last_login_at = ?,
        login_ip = ?,
        status = 'active',
        timezone = ?
    WHERE id = ?
")->execute([nowUtc(), $ip, $tz, $user['id']]);

// JWT
$secret_key      = $_ENV['JWT_SECRET_KEY'];
$issuer_claim    = "yourdomain.com";
$audience_claim  = "yourdomain.com";
$issuedat_claim  = time();

// Access (1 година)
$access_token_payload = [
    "iss"  => $issuer_claim,
    "aud"  => $audience_claim,
    "iat"  => $issuedat_claim,
    "nbf"  => $issuedat_claim,
    "exp"  => $issuedat_claim + 3600,
    "data" => ["id" => $user['id'], "email" => $user['email']]
];
$access_token = JWT::encode($access_token_payload, $secret_key, 'HS256');

// Refresh (7 днів)
$refresh_token_payload = [
    "iss"  => $issuer_claim,
    "aud"  => $audience_claim,
    "iat"  => $issuedat_claim,
    "nbf"  => $issuedat_claim,
    "exp"  => $issuedat_claim + 604800,
    "data" => ["id" => $user['id']]
];
$refresh_token = JWT::encode($refresh_token_payload, $secret_key, 'HS256');

$pdo->prepare("DELETE FROM refresh_tokens WHERE user_id = ?")->execute([$user['id']]);

$pdo->prepare("
  INSERT INTO refresh_tokens (user_id, token, expires_at)
  VALUES (?, ?, DATE_ADD(UTC_TIMESTAMP(), INTERVAL 7 DAY))
")->execute([$user['id'], $refresh_token]);

echo json_encode([
    "status" => "success",
    "message" => "Вхід успішний",
    "access_token" => $access_token,
    "refresh_token" => $refresh_token,
    "user" => [
        "id" => $user['id'],
        "email" => $user['email'],
        "timezone" => $tz
    ]
]);
