<?php
/**
 * Admin API: Статистика для дашборда (AJAX)
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../src/helpers/config.php';
AuthController::requireAdmin();

$userModel       = new UserModel();
$membershipModel = new MembershipModel();
$bookingModel    = new BookingModel();
$visitModel      = new VisitModel();

echo json_encode([
    'success' => true,
    'stats' => [
        'total_members'   => $userModel->countByRole('member'),
        'active_cards'    => $membershipModel->getStats()['active'] ?? 0,
        'today_bookings'  => $bookingModel->getStats()['new'] ?? 0,
        'week_visits'     => $visitModel->getStats()['total'] ?? 0,
    ]
]);
