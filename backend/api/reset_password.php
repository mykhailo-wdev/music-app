<?php
// reset_password.php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';  
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$token = trim($data['token'] ?? '');
$password = trim($data['password'] ?? '');

$errors = [];

if (empty($token) || strlen($token) !== 64) {
    $errors['general'] = 'Невірний або відсутній токен';
}
if (empty($password)) {
    $errors['password'] = 'Введіть пароль';
} elseif (strlen($password) < 6) {
    $errors['password'] = 'Пароль має містити мінімум 6 символів';
}

if ($errors) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// Перевіряємо токен
$stmt = $pdo->prepare("SELECT user_id, expires_at FROM password_resets WHERE token = ?");
$stmt->execute([$token]);
$row = $stmt->fetch();

if (!$row || strtotime($row['expires_at']) < time()) {
    $errors['general'] = 'Токен недійсний або прострочений';
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// Оновлюємо пароль
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashedPassword, $row['user_id']]);

// Видаляємо токен
$pdo->prepare("DELETE FROM password_resets WHERE token = ?")->execute([$token]);

echo json_encode(['success' => true, 'message' => 'Пароль успішно змінено']);
