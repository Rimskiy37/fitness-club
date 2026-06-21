<?php
/**
 * Админ: Управление записями
 */
require_once __DIR__ . '/../src/helpers/config.php';
AuthController::requireAdmin();

$bookingModel = new BookingModel();
$bookings = $bookingModel->getAll();

$statusLabels = [
    'new' => 'Новая', 'confirmed' => 'Подтверждена', 'cancelled' => 'Отменена',
    'completed' => 'Завершена', 'no_show' => 'Не явился'
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Записи — Админ FitLife</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/admin/assets/css/admin.css">
    <script>const APP_URL = '<?= APP_URL ?>';</script>
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="logo">💪 <span>Fit</span>Life</div>
        <nav class="admin-nav">
            <a href="index.php">📊 Дашборд</a>
            <a href="classes.php">🏋️ Занятия</a>
            <a href="bookings.php" class="active">📋 Записи</a>
            <a href="members.php">👥 Клиенты</a>
            <a href="memberships.php">🏷️ Карты</a>
            <a href="visits.php">📅 Посещения</a>
            <a href="/">🌐 На сайт</a>
            <a href="/logout">🚪 Выйти</a>
        </nav>
    </aside>

    <main class="admin-content">
        <div class="admin-header">
            <h1>📋 Управление записями</h1>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr><th>ID</th><th>Клиент</th><th>Занятие</th><th>Дата</th><th>Статус</th><th>Изменить статус</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $b): ?>
                            <tr data-booking-id="<?= $b['id'] ?>">
                                <td>#<?= $b['id'] ?></td>
                                <td><?= htmlspecialchars($b['client_name']) ?></td>
                                <td><?= htmlspecialchars($b['class_name']) ?></td>
                                <td><?= date('d.m.Y', strtotime($b['booking_date'])) ?></td>
                                <td><span class="status status-<?= $b['status'] ?>"><?= $statusLabels[$b['status']] ?? $b['status'] ?></span></td>
                                <td>
                                    <select onchange="changeBookingStatus(<?= $b['id'] ?>, this.value)" class="form-control" style="padding:4px 8px;font-size:0.85rem;width:auto;">
                                        <?php foreach ($statusLabels as $val => $label): ?>
                                            <option value="<?= $val ?>" <?= $b['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
<script src="/assets/js/app.js"></script>
</body>
</html>
