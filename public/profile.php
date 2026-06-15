<?php
require_once __DIR__ . '/../src/helpers/config.php';
AuthController::requireAuth();

$userModel       = new UserModel();
$membershipModel = new MembershipModel();
$bookingModel    = new BookingModel();
$visitModel      = new VisitModel();

$currentUser = AuthController::user();
$membership  = $membershipModel->getActiveByUserId($currentUser['id']);
$bookings    = $bookingModel->getByUserId($currentUser['id']);
$visits      = $visitModel->getByUserId($currentUser['id'], 10);

$categoryLabels = [
    'yoga' => 'Йога', 'pilates' => 'Пилатес', 'cardio' => 'Кардио', 'strength' => 'Силовая',
    'dance' => 'Танцы', 'crossfit' => 'CrossFit', 'boxing' => 'Бокс', 'stretching' => 'Стретчинг'
];
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
    <title>Личный кабинет — FitLife</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script>const APP_URL = '<?= APP_URL ?>';</script>
</head>
<body>
    <header class="header">
        <div class="container">
            <a href="/" class="logo">💪 <span>Fit</span>Life</a>
            <nav class="nav">
                <a href="/">Главная</a>
                <a href="/classes">Расписание</a>
                <a href="/profile" class="active">Личный кабинет</a>
                <?php if (AuthController::isAdmin()): ?>
                    <a href="/admin/" class="btn btn-secondary btn-sm">Админ</a>
                <?php endif; ?>
                <a href="/logout" class="btn btn-outline btn-sm">Выйти</a>
            </nav>
        </div>
    </header>

    <section class="section">
        <div class="container">
            <!-- Профиль -->
            <div class="profile-header">
                <img src="/assets/images/avatar_default.png"
                     alt="Аватар" class="profile-avatar">
                <div class="profile-info">
                    <h2><?= htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?></h2>
                    <p class="role"><?= htmlspecialchars($currentUser['email']) ?> · <?= $currentUser['phone'] ?? '' ?></p>
                </div>
            </div>

            <!-- Карта -->
            <div class="card" style="margin-bottom:30px;" id="membershipCard">
                <div class="card-body">
                    <h3 style="margin-bottom:16px;">🏷️ Моя клубная карта</h3>
                    <?php if ($membership): ?>
                        <?php
                        $card = MembershipCard::fromArray($membership);
                        $info = $card->getInfo();
                        $typeLabels = ['basic'=>'Базовая','standard'=>'Стандарт','premium'=>'Премиум','unlimited'=>'Безлимит'];
                        ?>
                        <div class="stats-grid" style="margin-bottom:0;">
                            <div class="stat-card">
                                <div class="label">Тип карты</div>
                                <div class="value" style="font-size:1.2rem;"><?= $typeLabels[$info['type']] ?? strtoupper($info['type']) ?></div>
                            </div>
                            <div class="stat-card success">
                                <div class="label">Статус</div>
                                <div class="value" style="font-size:1.2rem;">
                                    <span class="status status-<?= $info['status'] ?>"><?= $info['status'] === 'active' ? 'Активна' : 'Неактивна' ?></span>
                                </div>
                            </div>
                            <div class="stat-card warning">
                                <div class="label">Остаток посещений</div>
                                <div class="value"><?= $info['visits_remaining'] === -1 ? '∞' : $info['visits_remaining'] ?></div>
                            </div>
                            <div class="stat-card info">
                                <div class="label">Действует до</div>
                                <div class="value" style="font-size:1.1rem;"><?= date('d.m.Y', strtotime($info['end_date'])) ?></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning" style="margin-bottom:16px;">
                            У вас пока нет активной клубной карты. Оформите базовую карту прямо сейчас!
                        </div>
                        <p style="color:var(--text-muted);margin-bottom:16px;">
                            📋 <strong>Базовая карта</strong> — 8 посещений, 1 месяц, 8 000 ₽
                        </p>
                        <button class="btn btn-primary" onclick="getCard(this)" id="getCardBtn">
                            Оформить клубную карту
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Записи -->
            <div class="card" style="margin-bottom:30px;">
                <div class="card-body">
                    <h3 style="margin-bottom:16px;">📋 Мои записи на занятия</h3>
                    <?php if ($bookings): ?>
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Занятие</th>
                                        <th>Дата</th>
                                        <th>Тренер</th>
                                        <th>Статус</th>
                                        <th>Действие</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bookings as $b): ?>
                                    <tr data-booking-id="<?= $b['id'] ?>">
                                        <td><?= htmlspecialchars($b['class_name']) ?></td>
                                        <td><?= date('d.m.Y', strtotime($b['booking_date'])) ?></td>
                                        <td><?= htmlspecialchars($b['trainer_name'] ?? '') ?></td>
                                        <td><span class="status status-<?= $b['status'] ?>"><?= $statusLabels[$b['status']] ?? $b['status'] ?></span></td>
                                        <td>
                                            <?php if (in_array($b['status'], ['new', 'confirmed'])): ?>
                                                <button class="btn btn-danger btn-sm" onclick="cancelBooking(<?= $b['id'] ?>, this)">
                                                    Отменить
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p style="color:var(--text-muted);">У вас пока нет записей. <a href="/classes">Выберите занятие</a></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Последние посещения -->
            <div class="card">
                <div class="card-body">
                    <h3 style="margin-bottom:16px;">📊 Последние посещения</h3>
                    <?php if ($visits): ?>
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr><th>Дата</th><th>Время</th><th>Тип</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($visits as $v): ?>
                                    <tr>
                                        <td><?= date('d.m.Y', strtotime($v['visit_date'])) ?></td>
                                        <td><?= substr($v['visit_time'], 0, 5) ?></td>
                                        <td>
                                            <?php
                                            $types = ['individual' => 'Самостоятельно', 'group' => 'Групповое', 'personal_training' => 'Персональная'];
                                            echo $types[$v['visit_type']] ?? $v['visit_type'];
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p style="color:var(--text-muted);">Посещений пока не зафиксировано.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">&copy; <?= date('Y') ?> FitLife</div>
        </div>
    </footer>

    <script src="/assets/js/app.js"></script>
    <script>
    /**
     * AJAX: Оформить клубную карту
     */
    function getCard(btn) {
        btn.disabled = true;
        btn.textContent = 'Оформляем...';

        fetch(APP_URL + '/api/get-card.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                // Перезагружаем страницу чтобы показать карту
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.error || 'Ошибка', 'danger');
                btn.disabled = false;
                btn.textContent = 'Оформить клубную карту';
            }
        })
        .catch(err => {
            showToast('Произошла ошибка', 'danger');
            btn.disabled = false;
            btn.textContent = 'Оформить клубную карту';
        });
    }
    </script>
</body>
</html>
