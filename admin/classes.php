<?php
/**
 * Админ: Управление занятиями
 */
require_once __DIR__ . '/../src/helpers/config.php';
AuthController::requireAdmin();

$classModel = new ClassModel();
$userModel  = new UserModel();
$trainers   = $userModel->getAllByRole('trainer');
$classes    = $classModel->getAll();

$categoryLabels = [
    'yoga' => 'Йога', 'pilates' => 'Пилатес', 'cardio' => 'Кардио', 'strength' => 'Силовая',
    'dance' => 'Танцы', 'crossfit' => 'CrossFit', 'boxing' => 'Бокс', 'stretching' => 'Стретчинг'
];
$dayNames = [1=>'Пн',2=>'Вт',3=>'Ср',4=>'Чт',5=>'Пт',6=>'Сб',7=>'Вс'];

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        try {
            $classModel->create([
                'trainer_id'       => (int)$_POST['trainer_id'],
                'name'             => $_POST['name'],
                'description'      => $_POST['description'],
                'category'         => $_POST['category'],
                'day_of_week'      => (int)$_POST['day_of_week'],
                'start_time'       => $_POST['start_time'],
                'duration_min'     => (int)$_POST['duration_min'],
                'max_participants' => (int)$_POST['max_participants'],
                'image'            => $_POST['image'] ?? ''
            ]);
            $message = '<div class="alert alert-success">Занятие добавлено</div>';
            $classes = $classModel->getAll();
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">' . $e->getMessage() . '</div>';
        }
    } elseif ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        $classModel->delete((int)$_POST['id']);
        $message = '<div class="alert alert-success">Занятие удалено</div>';
        $classes = $classModel->getAll();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Занятия — Админ FitLife</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/admin/assets/css/admin.css">
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="logo">💪 <span>Fit</span>Life</div>
        <nav class="admin-nav">
            <a href="index.php">📊 Дашборд</a>
            <a href="classes.php" class="active">🏋️ Занятия</a>
            <a href="bookings.php">📋 Записи</a>
            <a href="members.php">👥 Клиенты</a>
            <a href="memberships.php">🏷️ Карты</a>
            <a href="visits.php">📅 Посещения</a>
            <a href="/">🌐 На сайт</a>
            <a href="/logout">🚪 Выйти</a>
        </nav>
    </aside>

    <main class="admin-content">
        <div class="admin-header">
            <h1>🏋️ Управление занятиями</h1>
        </div>

        <?= $message ?>

        <!-- Форма добавления -->
        <div class="card" style="margin-bottom:30px;">
            <div class="card-body">
                <h3 style="margin-bottom:16px;">Добавить занятие</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="create">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div class="form-group">
                            <label>Название</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Категория</label>
                            <select name="category" class="form-control" required>
                                <?php foreach ($categoryLabels as $val => $label): ?>
                                    <option value="<?= $val ?>"><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Тренер</label>
                            <select name="trainer_id" class="form-control" required>
                                <?php foreach ($trainers as $t): ?>
                                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>День недели</label>
                            <select name="day_of_week" class="form-control" required>
                                <?php foreach ($dayNames as $n => $name): ?>
                                    <option value="<?= $n ?>"><?= $name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Время начала</label>
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Длительность (мин)</label>
                            <input type="number" name="duration_min" class="form-control" value="60" min="15" max="180">
                        </div>
                        <div class="form-group">
                            <label>Макс. участников</label>
                            <input type="number" name="max_participants" class="form-control" value="20" min="1" max="100">
                        </div>
                        <div class="form-group">
                            <label>Изображение</label>
                            <input type="text" name="image" class="form-control" placeholder="class_name.png">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Описание</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Добавить занятие</button>
                </form>
            </div>
        </div>

        <!-- Таблица занятий -->
        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom:16px;">Список занятий</h3>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr><th>ID</th><th>Название</th><th>Категория</th><th>Тренер</th><th>День</th><th>Время</th><th>Действие</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($classes as $c): ?>
                            <tr>
                                <td>#<?= $c['id'] ?></td>
                                <td><?= htmlspecialchars($c['name']) ?></td>
                                <td><?= $categoryLabels[$c['category']] ?? $c['category'] ?></td>
                                <td><?= htmlspecialchars($c['trainer_name']) ?></td>
                                <td><?= $dayNames[$c['day_of_week']] ?></td>
                                <td><?= substr($c['start_time'],0,5) ?> (<?= $c['duration_min'] ?> мин)</td>
                                <td>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Удалить занятие?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
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
