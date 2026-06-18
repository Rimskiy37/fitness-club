<?php
/**
 * API: Получить клубную карту (AJAX)
 * Создаёт базовую карту со стартовыми параметрами
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../src/helpers/config.php';

if (!AuthController::check()) {
    echo json_encode(['success' => false, 'error' => 'Необходима авторизация']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Метод не поддерживается']);
    exit;
}

$userId = $_SESSION['user_id'];
$membershipModel = new MembershipModel();

// Проверяем, нет ли уже активной карты
$existing = $membershipModel->getActiveByUserId($userId);
if ($existing) {
    echo json_encode(['success' => false, 'error' => 'У вас уже есть активная карта']);
    exit;
}

// Создаём базовую карту
try {
    $membershipModel->create([
        'user_id'      => $userId,
        'type'         => 'basic',
        'start_date'   => date('Y-m-d'),
        'end_date'     => date('Y-m-d', strtotime('+1 month')),
        'visits_total' => 8,
        'visits_used'  => 0,
        'price'        => 8000.00,
        'status'       => 'active',
    ]);
    echo json_encode(['success' => true, 'message' => 'Клубная карта успешно оформлена!']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Ошибка: ' . $e->getMessage()]);
}
