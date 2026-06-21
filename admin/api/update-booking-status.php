<?php
/**
 * Admin API: Обновление статуса записи (AJAX)
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../src/helpers/config.php';
AuthController::requireAdmin();

$data = json_decode(file_get_contents('php://input'), true);
$bookingId = (int) ($data['booking_id'] ?? 0);
$newStatus = trim($data['status'] ?? '');

$statusLabels = [
    'new' => 'Новая', 'confirmed' => 'Подтверждена', 'cancelled' => 'Отменена',
    'completed' => 'Завершена', 'no_show' => 'Не явился'
];

if ($bookingId <= 0 || !isset($statusLabels[$newStatus])) {
    echo json_encode(['success' => false, 'error' => 'Некорректные данные']);
    exit;
}

$bookingModel = new BookingModel();
$result = $bookingModel->updateStatus($bookingId, $newStatus);

echo json_encode([
    'success'      => $result,
    'status_label' => $statusLabels[$newStatus]
]);
