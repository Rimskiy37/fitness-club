<?php
/**
 * API: Запись на занятие (AJAX)
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

$data = json_decode(file_get_contents('php://input'), true);
$classId = (int) ($data['class_id'] ?? 0);
$date    = trim($data['date'] ?? '');
$userId  = $_SESSION['user_id'];

// Валидация
if ($classId <= 0 || !$date) {
    echo json_encode(['success' => false, 'error' => 'Некорректные данные']);
    exit;
}

$bookingModel = new BookingModel();
$classModel   = new ClassModel();

// Проверяем, не записан ли уже
if ($bookingModel->isBooked($userId, $classId, $date)) {
    echo json_encode(['success' => false, 'error' => 'Вы уже записаны на это занятие']);
    exit;
}

// Проверяем лимит участников
$classInfo = $classModel->getById($classId);
if (!$classInfo) {
    echo json_encode(['success' => false, 'error' => 'Занятие не найдено']);
    exit;
}

$currentBookings = $classModel->getBookingCount($classId, $date);
if ($currentBookings >= $classInfo['max_participants']) {
    echo json_encode(['success' => false, 'error' => 'Нет свободных мест']);
    exit;
}

// Проверяем активную карту
$membershipModel = new MembershipModel();
$membership = $membershipModel->getActiveByUserId($userId);
if (!$membership) {
    echo json_encode(['success' => false, 'error' => 'У вас нет активной клубной карты']);
    exit;
}

$card = MembershipCard::fromArray($membership);
if (!$card->getInfo()['is_active']) {
    echo json_encode(['success' => false, 'error' => 'Ваша карта неактивна']);
    exit;
}
if ($card->getVisitsRemaining() !== -1 && $card->getVisitsRemaining() <= 0) {
    echo json_encode(['success' => false, 'error' => 'Лимит посещений исчерпан']);
    exit;
}

try {
    $bookingId = $bookingModel->create($userId, $classId, $date);
    echo json_encode(['success' => true, 'booking_id' => $bookingId]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Ошибка записи: ' . $e->getMessage()]);
}
