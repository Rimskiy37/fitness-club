<?php
require_once __DIR__ . '/../src/helpers/config.php';
AuthController::requireAdmin();

$userModel       = new UserModel();
$membershipModel = new MembershipModel();
$bookingModel    = new BookingModel();
$visitModel      = new VisitModel();

$totalMembers   = $userModel->countByRole('member');
$mStats         = $membershipModel->getStats();
$activeCards    = $mStats['active'] ?? 0;
$bookingStats   = $bookingModel->getStats();
$visitStats     = $visitModel->getStats();
$retentionRate  = $membershipModel->getRetentionRate();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель — FitLife</title>
    <link rel="stylesheet" href="/admin/assets/css/admin.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <script>const APP_URL = '<?= APP_URL ?>';</script>
</head>
<body>
<div class="admin-layout">
    <!-- Боковое меню -->
    <aside class="admin-sidebar">
        <div class="logo">💪 <span>Fit</span>Life</div>
        <nav class="admin-nav">
            <a href="index.php" class="active">📊 Дашборд</a>
            <a href="classes.php">🏋️ Занятия</a>
            <a href="bookings.php">📋 Записи</a>
            <a href="members.php">👥 Клиенты</a>
            <a href="memberships.php">🏷️ Карты</a>
            <a href="visits.php">📅 Посещения</a>
            <a href="/">🌐 На сайт</a>
            <a href="/logout">🚪 Выйти</a>
        </nav>
    </aside>

    <!-- Контент -->
    <main class="admin-content">
        <div class="admin-header">
            <h1>📊 Дашборд</h1>
            <span>Привет, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Администратор') ?>!</span>
        </div>

        <!-- Статистика -->
        <div class="stats-grid" id="dashboardStats">
            <div class="stat-card">
                <div class="label">Всего клиентов</div>
                <div class="value"><?= $totalMembers ?></div>
            </div>
            <div class="stat-card success">
                <div class="label">Активных карт</div>
                <div class="value"><?= $activeCards ?></div>
            </div>
            <div class="stat-card warning">
                <div class="label">Всего записей</div>
                <div class="value"><?= $bookingStats['total'] ?? 0 ?></div>
            </div>
            <div class="stat-card info">
                <div class="label">Посещений</div>
                <div class="value"><?= $visitStats['total'] ?? 0 ?></div>
            </div>
        </div>

        <!-- KPI -->
        <div class="card" style="margin-bottom:30px;">
            <div class="card-body">
                <h3 style="margin-bottom:16px;">📈 Ключевые показатели</h3>
                <div class="stats-grid" style="margin-bottom:0;">
                    <div class="stat-card">
                        <div class="label">Retention Rate</div>
                        <div class="value" style="color:var(--success);"><?= $retentionRate ?>%</div>
                    </div>
                    <div class="stat-card success">
                        <div class="label">Средний чек</div>
                        <div class="value"><?= number_format($mStats['avg_price'] ?? 0, 0, '', ' ') ?> ₽</div>
                    </div>
                    <div class="stat-card info">
                        <div class="label">Уникальных посетителей</div>
                        <div class="value"><?= $visitStats['unique_visitors'] ?? 0 ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Последние записи -->
        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom:16px;">📋 Последние записи на занятия</h3>
                <div class="table-wrapper">
                    <?php
                    $recentBookings = array_slice($bookingModel->getAll(), 0, 10);
                    $statusLabels = ['new' => 'Новая', 'confirmed' => 'Подтверждена', 'cancelled' => 'Отменена', 'completed' => 'Завершена', 'no_show' => 'Не явился'];
                    ?>
                    <table>
                        <thead>
                            <tr><th>ID</th><th>Клиент</th><th>Занятие</th><th>Дата</th><th>Статус</th><th>Действие</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentBookings as $b): ?>
                            <tr data-booking-id="<?= $b['id'] ?>">
                                <td>#<?= $b['id'] ?></td>
                                <td><?= htmlspecialchars($b['client_name']) ?></td>
                                <td><?= htmlspecialchars($b['class_name']) ?></td>
                                <td><?= date('d.m.Y', strtotime($b['booking_date'])) ?></td>
                                <td><span class="status status-<?= $b['status'] ?>"><?= $statusLabels[$b['status']] ?? $b['status'] ?></span></td>
                                <td>
                                    <select onchange="changeBookingStatus(<?= $b['id'] ?>, this.value)" class="form-control" style="padding:4px 8px;font-size:0.85rem;width:auto;">
                                        <option value="new" <?= $b['status'] === 'new' ? 'selected' : '' ?>>Новая</option>
                                        <option value="confirmed" <?= $b['status'] === 'confirmed' ? 'selected' : '' ?>>Подтверждена</option>
                                        <option value="completed" <?= $b['status'] === 'completed' ? 'selected' : '' ?>>Завершена</option>
                                        <option value="no_show" <?= $b['status'] === 'no_show' ? 'selected' : '' ?>>Не явился</option>
                                        <option value="cancelled" <?= $b['status'] === 'cancelled' ? 'selected' : '' ?>>Отменена</option>
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
