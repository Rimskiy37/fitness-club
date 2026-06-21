<?php
/**
 * Админ: Журнал посещений
 */
require_once __DIR__ . '/../src/helpers/config.php';
AuthController::requireAdmin();

$visitModel = new VisitModel();
$userModel  = new UserModel();
$stats      = $visitModel->getStats();
$daily      = $visitModel->getDailyStats(14);
$members    = $userModel->getAllByRole('member');

$typeLabels = ['individual'=>'Самостоятельно','group'=>'Групповое','personal_training'=>'Персональная'];

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    try {
        $visitModel->create([
            'user_id'    => (int)$_POST['user_id'],
            'visit_date' => $_POST['visit_date'],
            'visit_time' => $_POST['visit_time'],
            'visit_type' => $_POST['visit_type'],
        ]);
        $message = '<div class="alert alert-success">Посещение зафиксировано</div>';
        $stats = $visitModel->getStats();
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">' . $e->getMessage() . '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Посещения — Админ FitLife</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/admin/assets/css/admin.css">
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="logo">💪 <span>Fit</span>Life</div>
        <nav class="admin-nav">
            <a href="index.php">📊 Дашборд</a>
            <a href="classes.php">🏋️ Занятия</a>
            <a href="bookings.php">📋 Записи</a>
            <a href="members.php">👥 Клиенты</a>
            <a href="memberships.php">🏷️ Карты</a>
            <a href="visits.php" class="active">📅 Посещения</a>
            <a href="/">🌐 На сайт</a>
            <a href="/logout">🚪 Выйти</a>
        </nav>
    </aside>

    <main class="admin-content">
        <div class="admin-header">
            <h1>📅 Журнал посещений</h1>
        </div>

        <?= $message ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="label">Всего посещений</div>
                <div class="value"><?= $stats['total'] ?? 0 ?></div>
            </div>
            <div class="stat-card success">
                <div class="label">Уникальных клиентов</div>
                <div class="value"><?= $stats['unique_visitors'] ?? 0 ?></div>
            </div>
            <div class="stat-card warning">
                <div class="label">Групповых</div>
                <div class="value"><?= $stats['group_visits'] ?? 0 ?></div>
            </div>
            <div class="stat-card info">
                <div class="label">Персональных</div>
                <div class="value"><?= $stats['personal_visits'] ?? 0 ?></div>
            </div>
        </div>

        <!-- Фиксация посещения -->
        <div class="card" style="margin-bottom:30px;">
            <div class="card-body">
                <h3 style="margin-bottom:16px;">Зафиксировать посещение</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:16px;">
                        <div class="form-group">
                            <label>Клиент</label>
                            <select name="user_id" class="form-control" required>
                                <?php foreach ($members as $m): ?>
                                    <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Дата</label>
                            <input type="date" name="visit_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Время</label>
                            <input type="time" name="visit_time" class="form-control" value="<?= date('H:i') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Тип</label>
                            <select name="visit_type" class="form-control">
                                <option value="individual">Самостоятельно</option>
                                <option value="group">Групповое</option>
                                <option value="personal_training">Персональная</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Зафиксировать</button>
                </form>
            </div>
        </div>

        <!-- Посещения по дням -->
        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom:16px;">Статистика за последние 14 дней</h3>
                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Дата</th><th>Посещений</th></tr></thead>
                        <tbody>
                            <?php foreach ($daily as $d): ?>
                            <tr>
                                <td><?= date('d.m.Y (D)', strtotime($d['visit_date'])) ?></td>
                                <td><strong><?= $d['cnt'] ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
