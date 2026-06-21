<?php
/**
 * Админ: Управление клубными картами
 */
require_once __DIR__ . '/../src/helpers/config.php';
AuthController::requireAdmin();

$membershipModel = new MembershipModel();
$userModel       = new UserModel();
$memberships     = $membershipModel->getAll();
$members         = $userModel->getAllByRole('member');
$stats           = $membershipModel->getStats();

$typeLabels = ['basic'=>'Базовая','standard'=>'Стандарт','premium'=>'Премиум','unlimited'=>'Безлимит'];
$statusLabels = ['active'=>'Активна','expired'=>'Истекла','suspended'=>'Приостановлена','cancelled'=>'Отменена'];

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        try {
            $membershipModel->create([
                'user_id'      => (int)$_POST['user_id'],
                'type'         => $_POST['type'],
                'start_date'   => $_POST['start_date'],
                'end_date'     => $_POST['end_date'],
                'visits_total' => (int)$_POST['visits_total'],
                'price'        => (float)$_POST['price'],
            ]);
            $message = '<div class="alert alert-success">Карта добавлена</div>';
            $memberships = $membershipModel->getAll();
            $stats = $membershipModel->getStats();
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">' . $e->getMessage() . '</div>';
        }
    } elseif ($_POST['action'] === 'update_status') {
        $membershipModel->updateStatus((int)$_POST['id'], $_POST['status']);
        $message = '<div class="alert alert-success">Статус обновлён</div>';
        $memberships = $membershipModel->getAll();
        $stats = $membershipModel->getStats();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Карты — Админ FitLife</title>
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
            <a href="memberships.php" class="active">🏷️ Карты</a>
            <a href="visits.php">📅 Посещения</a>
            <a href="/">🌐 На сайт</a>
            <a href="/logout">🚪 Выйти</a>
        </nav>
    </aside>

    <main class="admin-content">
        <div class="admin-header">
            <h1>🏷️ Клубные карты</h1>
        </div>

        <?= $message ?>

        <!-- Статистика -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="label">Всего карт</div>
                <div class="value"><?= $stats['total'] ?? 0 ?></div>
            </div>
            <div class="stat-card success">
                <div class="label">Активных</div>
                <div class="value"><?= $stats['active'] ?? 0 ?></div>
            </div>
            <div class="stat-card warning">
                <div class="label">Просроченных</div>
                <div class="value"><?= $stats['expired'] ?? 0 ?></div>
            </div>
            <div class="stat-card info">
                <div class="label">Средняя цена</div>
                <div class="value"><?= number_format($stats['avg_price'] ?? 0, 0, '', ' ') ?> ₽</div>
            </div>
        </div>

        <!-- Форма добавления -->
        <div class="card" style="margin-bottom:30px;">
            <div class="card-body">
                <h3 style="margin-bottom:16px;">Добавить карту</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
                        <div class="form-group">
                            <label>Клиент</label>
                            <select name="user_id" class="form-control" required>
                                <?php foreach ($members as $m): ?>
                                    <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Тип карты</label>
                            <select name="type" class="form-control" required>
                                <?php foreach ($typeLabels as $val => $label): ?>
                                    <option value="<?= $val ?>"><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Цена (₽)</label>
                            <input type="number" name="price" class="form-control" value="18000" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>Дата начала</label>
                            <input type="date" name="start_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="form-group">
                            <label>Дата окончания</label>
                            <input type="date" name="end_date" class="form-control" required value="<?= date('Y-m-d', strtotime('+6 months')) ?>">
                        </div>
                        <div class="form-group">
                            <label>Лимит посещений (0=безлимит)</label>
                            <input type="number" name="visits_total" class="form-control" value="24">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Добавить карту</button>
                </form>
            </div>
        </div>

        <!-- Таблица -->
        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom:16px;">Все карты</h3>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr><th>ID</th><th>Клиент</th><th>Тип</th><th>Период</th><th>Посещения</th><th>Цена</th><th>Статус</th><th>Действие</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($memberships as $m): ?>
                            <tr>
                                <td>#<?= $m['id'] ?></td>
                                <td><?= htmlspecialchars($m['client_name']) ?></td>
                                <td><?= $typeLabels[$m['type']] ?? $m['type'] ?></td>
                                <td><?= date('d.m.Y', strtotime($m['start_date'])) ?> — <?= date('d.m.Y', strtotime($m['end_date'])) ?></td>
                                <td><?= $m['visits_used'] ?> / <?= $m['visits_total'] === '0' ? '∞' : $m['visits_total'] ?></td>
                                <td><?= number_format($m['price'], 0, '', ' ') ?> ₽</td>
                                <td><span class="status status-<?= $m['status'] ?>"><?= $statusLabels[$m['status']] ?? $m['status'] ?></span></td>
                                <td>
                                    <form method="POST" style="display:inline-flex;gap:4px;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                        <select name="status" class="form-control" style="padding:4px 8px;font-size:0.8rem;width:auto;" onchange="this.form.submit()">
                                            <?php foreach ($statusLabels as $val => $label): ?>
                                                <option value="<?= $val ?>" <?= $m['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
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
</body>
</html>
