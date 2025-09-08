<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}


require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$data = json_decode(file_get_contents("php://input"), true);
$email = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

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

// --- Шукаємо користувача ---
$stmt = $pdo->prepare("SELECT id, email, password, failed_attempts, status FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode([
        "status" => "error",
        "message" => "Невірний email або пароль",
        "errors" => ["general" => "Невірний логін або пароль"]
    ]);
    exit;
}

// --- Якщо вже в статусі banned ---
if ($user['status'] === 'banned') {
    echo json_encode(["status" => "error", "message" => "Ваш акаунт заблоковано"]);
    exit;
}

// --- Якщо failed_attempts >= 5, блокуємо ---
if ((int)$user['failed_attempts'] >= 5) {
    $update = $pdo->prepare("UPDATE users SET status = 'banned' WHERE id = ?");
    $update->execute([$user['id']]);
    echo json_encode(["status" => "error", "message" => "Акаунт тимчасово заблоковано через багато невдалих входів"]);
    exit;
}

// --- Перевірка пароля ---
if (!password_verify($password, $user['password'])) {
    // Інкремент failed_attempts
    $update = $pdo->prepare("
        UPDATE users 
        SET failed_attempts = failed_attempts + 1, 
            status = CASE WHEN failed_attempts + 1 >= 5 THEN 'banned' ELSE status END 
        WHERE id = ?
    ");
    $update->execute([$user['id']]);

    // Перевіримо стан після оновлення
    $stmt2 = $pdo->prepare("SELECT failed_attempts, status FROM users WHERE id = ?");
    $stmt2->execute([$user['id']]);
    $updated = $stmt2->fetch(PDO::FETCH_ASSOC);

    if ($updated['status'] === 'banned') {
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

// --- Пароль вірний: скидаємо лічильник і оновлюємо last_login_at, login_ip, статус ---
$update = $pdo->prepare("
    UPDATE users 
    SET failed_attempts = 0, last_login_at = NOW(), login_ip = ?, status = 'active' 
    WHERE id = ?
");
$update->execute([$ip, $user['id']]);

// --- JWT ---
$secret_key = "aD8SZNhKlC5McZBe2sac2YDdZ6JN7un0OJTULKgJ35w=";
$issuer_claim = "yourdomain.com";
$audience_claim = "yourdomain.com";
$issuedat_claim = time();
$notbefore_claim = $issuedat_claim;

// Access Token (1 година)
$access_token_payload = [
    "iss" => $issuer_claim,
    "aud" => $audience_claim,
    "iat" => $issuedat_claim,
    "nbf" => $notbefore_claim,
    "exp" => $issuedat_claim + 3600,
    "data" => ["id" => $user['id'], "email" => $user['email']]
];
$access_token = JWT::encode($access_token_payload, $secret_key, 'HS256');

// Refresh Token (7 днів)
$refresh_token_payload = [
    "iss" => $issuer_claim,
    "aud" => $audience_claim,
    "iat" => $issuedat_claim,
    "nbf" => $notbefore_claim,
    "exp" => $issuedat_claim + 604800,
    "data" => ["id" => $user['id']]
];
$refresh_token = JWT::encode($refresh_token_payload, $secret_key, 'HS256');

// Видаляємо старі refresh токени
$stmt = $pdo->prepare("DELETE FROM refresh_tokens WHERE user_id = ?");
$stmt->execute([$user['id']]);

// Вставляємо новий refresh token
$stmt = $pdo->prepare("INSERT INTO refresh_tokens (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY))");
$stmt->execute([$user['id'], $refresh_token]);

// --- Віддаємо JSON з токенами ---
echo json_encode([
    "status" => "success",
    "message" => "Вхід успішний",
    "access_token" => $access_token,
    "refresh_token" => $refresh_token,
    "user" => [
        "id" => $user['id'],
        "email" => $user['email'],
    ]
]);
