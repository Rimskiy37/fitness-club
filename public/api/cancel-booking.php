<?php
/**
 * API: Отмена записи на занятие (AJAX)
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../src/helpers/config.php';

if (!AuthController::check()) {
    echo json_encode(['success' => false, 'error' => 'Необходима авторизация']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$bookingId = (int) ($data['booking_id'] ?? 0);
$userId    = $_SESSION['user_id'];

if ($bookingId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Некорректный ID записи']);
    exit;
}

$bookingModel = new BookingModel();
$result = $bookingModel->cancel($bookingId, $userId);

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Не удалось отменить запись']);
}
