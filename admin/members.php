<?php
/**
 * Админ: Управление клиентами
 */
require_once __DIR__ . '/../src/helpers/config.php';
AuthController::requireAdmin();

$userModel = new UserModel();
$members = $userModel->getAllByRole('member');

$roleLabels = ['member' => 'Член клуба', 'trainer' => 'Тренер', 'admin' => 'Админ', 'manager' => 'Менеджер'];

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    try {
        $userModel->create([
            'email'      => $_POST['email'],
            'password'   => $_POST['password'],
            'first_name' => $_POST['first_name'],
            'last_name'  => $_POST['last_name'],
            'phone'      => $_POST['phone'] ?? '',
            'role'       => $_POST['role'] ?? 'member',
        ]);
        $message = '<div class="alert alert-success">Пользователь добавлен</div>';
        $members = $userModel->getAllByRole('member');
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
    <title>Клиенты — Админ FitLife</title>
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
            <a href="members.php" class="active">👥 Клиенты</a>
            <a href="memberships.php">🏷️ Карты</a>
            <a href="visits.php">📅 Посещения</a>
            <a href="/">🌐 На сайт</a>
            <a href="/logout">🚪 Выйти</a>
        </nav>
    </aside>

    <main class="admin-content">
        <div class="admin-header">
            <h1>👥 Клиенты</h1>
        </div>

        <?= $message ?>

        <div class="card" style="margin-bottom:30px;">
            <div class="card-body">
                <h3 style="margin-bottom:16px;">Добавить клиента</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Пароль *</label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label>Имя *</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Фамилия *</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Телефон</label>
                            <input type="tel" name="phone" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Роль</label>
                            <select name="role" class="form-control">
                                <option value="member">Член клуба</option>
                                <option value="trainer">Тренер</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Добавить</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom:16px;">Список клиентов</h3>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr><th>ID</th><th>Имя</th><th>Email</th><th>Телефон</th><th>Дата регистрации</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $m): ?>
                            <tr>
                                <td>#<?= $m['id'] ?></td>
                                <td><?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?></td>
                                <td><?= htmlspecialchars($m['email']) ?></td>
                                <td><?= htmlspecialchars($m['phone']) ?></td>
                                <td><?= date('d.m.Y', strtotime($m['created_at'])) ?></td>
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
