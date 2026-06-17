<?php
/**
 * API: Проверка email на уникальность при регистрации (AJAX)
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../src/helpers/config.php';

$email = trim($_GET['email'] ?? '');

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['exists' => false]);
    exit;
}

$userModel = new UserModel();
echo json_encode(['exists' => $userModel->emailExists($email)]);
