<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require 'db.php';
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

// Шукаємо користувача
$stmt = $mysqli->prepare("SELECT id, email, password, failed_attempts, status FROM users WHERE email = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Помилка підготовки запиту"]);
    exit;
}
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo json_encode([
        "status" => "error",
        "message" => "Невірний email або пароль",
        "errors" => [
            "email" => "Невірний email",
            "password" => "Невірний пароль"
        ]
    ]);
    exit;
}

// Якщо вже в статусі banned — блокуємо
if ($user['status'] === 'banned') {
    echo json_encode(["status" => "error", "message" => "Ваш акаунт заблоковано"]);
    exit;
}

// Якщо лічильник вже >= 5 — оновимо статус (щоб прив'язати блокування до статусу) і повернемо повідомлення
if ((int)$user['failed_attempts'] >= 5) {
    $ustmt = $mysqli->prepare("UPDATE users SET status = 'banned' WHERE id = ?");
    if ($ustmt) {
        $ustmt->bind_param("i", $user['id']);
        $ustmt->execute();
        $ustmt->close();
    }
    echo json_encode(["status" => "error", "message" => "Акаунт тимчасово заблоковано через багато невдалих входів"]);
    exit;
}

// Перевіряємо пароль
if (!password_verify($password, $user['password'])) {
    // Інкрементуємо failed_attempts і, якщо після інкременту >=5, ставимо status='banned'
    $ustmt = $mysqli->prepare("UPDATE users SET failed_attempts = failed_attempts + 1, status = IF(failed_attempts + 1 >= 5, 'banned', status) WHERE id = ?");
    if ($ustmt) {
        $ustmt->bind_param("i", $user['id']);
        $ustmt->execute();
        $ustmt->close();
    }

    // Дізнаємось актуальний стан після оновлення
    $rstmt = $mysqli->prepare("SELECT failed_attempts, status FROM users WHERE id = ?");
    if ($rstmt) {
        $rstmt->bind_param("i", $user['id']);
        $rstmt->execute();
        $rres = $rstmt->get_result();
        $updated = $rres->fetch_assoc();
        $rstmt->close();
    } else {
        $updated = ["failed_attempts" => $user['failed_attempts'] + 1, "status" => $user['status']];
    }

    // Якщо після інкременту користувач заблокований — повертаємо повідомлення про блокування
    if (isset($updated['status']) && $updated['status'] === 'banned') {
        echo json_encode(["status" => "error", "message" => "Акаунт тимчасово заблоковано через багато невдалих входів"]);
        exit;
    }

    // Інакше повертаємо звичну помилку входу (можна показати загальну)
    echo json_encode([
        "status" => "error",
        "message" => "Невірний email або пароль",
        "errors" => [
            "general" => "Невірний логін або пароль"
        ]
    ]);
    exit;
}

// Якщо пароль вірний — скидаємо лічильник і оновлюємо last_login_at, login_ip та статус = 'active'
$ustmt = $mysqli->prepare("UPDATE users SET failed_attempts = 0, last_login_at = NOW(), login_ip = ?, status = 'active' WHERE id = ?");
if (!$ustmt) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Помилка оновлення даних користувача"]);
    exit;
}
$ustmt->bind_param("si", $ip, $user['id']);
if (!$ustmt->execute()) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Не вдалося оновити дані користувача"]);
    $ustmt->close();
    exit;
}
$ustmt->close();

// Параметри JWT
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
$stmt = $mysqli->prepare("DELETE FROM refresh_tokens WHERE user_id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Помилка видалення старих токенів"]);
    exit;
}
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$stmt->close();

// Вставляємо новий refresh token
$stmt = $mysqli->prepare("INSERT INTO refresh_tokens (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY))");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Помилка підготовки вставки токена"]);
    exit;
}
$stmt->bind_param("is", $user['id'], $refresh_token);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Не вдалося зберегти токен"]);
    $stmt->close();
    exit;
}
$stmt->close();

// Віддаємо JSON з токенами
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
