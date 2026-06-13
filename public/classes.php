<?php
require_once __DIR__ . '/../src/helpers/config.php';

$classModel = new ClassModel();
$categories = $classModel->getCategories();

// Расписание на неделю
$schedule = $classModel->getWeeklySchedule();
$dayNames = [1 => 'Понедельник', 2 => 'Вторник', 3 => 'Среда', 4 => 'Четверг', 5 => 'Пятница', 6 => 'Суббота', 7 => 'Воскресенье'];
$categoryLabels = [
    'yoga' => 'Йога', 'pilates' => 'Пилатес', 'cardio' => 'Кардио', 'strength' => 'Силовая',
    'dance' => 'Танцы', 'crossfit' => 'CrossFit', 'boxing' => 'Бокс', 'stretching' => 'Стретчинг'
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Расписание занятий — FitLife</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script>const APP_URL = '<?= APP_URL ?>';</script>
</head>
<body>
    <!-- Шапка -->
    <header class="header">
        <div class="container">
            <a href="/" class="logo">💪 <span>Fit</span>Life</a>
            <nav class="nav">
                <a href="/">Главная</a>
                <a href="/classes" class="active">Расписание</a>
                <?php if (AuthController::check()): ?>
                    <a href="/profile">Личный кабинет</a>
                    <a href="/logout">Выйти</a>
                <?php else: ?>
                    <a href="/login" class="btn btn-outline btn-sm">Войти</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Поиск -->
    <section class="section" style="padding-bottom:0;">
        <div class="container">
            <h2 class="section-title">Расписание <span>занятий</span></h2>
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="🔍 Поиск занятий..." autocomplete="off">
            </div>
            <div id="searchResults"></div>

            <!-- Фильтры -->
            <div class="filters">
                <button class="filter-btn active" onclick="filterByCategory('all', this)">Все</button>
                <?php foreach ($categories as $cat): ?>
                    <button class="filter-btn" onclick="filterByCategory('<?= $cat ?>', this)">
                        <?= $categoryLabels[$cat] ?? $cat ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Карточки занятий -->
    <section class="section" style="padding-top:10px;">
        <div class="container">
            <div id="classCards" class="cards-grid">
                <?php foreach ($classModel->getAll() as $c): ?>
                <div class="card fade-in">
                    <img class="card-img" src="/assets/images/<?= htmlspecialchars($c['image'] ?: 'class_default.png') ?>" alt="<?= htmlspecialchars($c['name']) ?>">
                    <div class="card-body">
                        <span class="card-category"><?= $categoryLabels[$c['category']] ?? $c['category'] ?></span>
                        <h3 class="card-title"><?= htmlspecialchars($c['name']) ?></h3>
                        <p class="card-text"><?= htmlspecialchars($c['description'] ?? '') ?></p>
                        <div class="card-meta">
                            <span>📅 <?= $dayNames[$c['day_of_week']] ?></span>
                            <span>🕐 <?= substr($c['start_time'], 0, 5) ?> (<?= $c['duration_min'] ?> мин)</span>
                        </div>
                        <div class="card-meta" style="margin-top:8px;">
                            <span>👤 Тренер: <?= htmlspecialchars($c['trainer_name']) ?></span>
                            <span>👥 до <?= $c['max_participants'] ?></span>
                        </div>
                        <?php if (AuthController::check()): ?>
                        <button class="btn btn-primary btn-sm pulse-btn" style="margin-top:12px;width:100%"
                            data-class-id="<?= $c['id'] ?>"
                            onclick="bookClass(<?= $c['id'] ?>, getNextDate(<?= $c['day_of_week'] ?>))">
                            Записаться
                        </button>
                        <?php else: ?>
                        <a href="/login" class="btn btn-outline btn-sm" style="margin-top:12px;width:100%;justify-content:center;">Войдите для записи</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Расписание таблицей -->
    <section class="section" style="background:var(--bg-white);">
        <div class="container">
            <h2 class="section-title">Расписание на <span>неделю</span></h2>
            <div class="table-wrapper">
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th>Время</th>
                            <?php foreach ($dayNames as $d => $name): ?>
                                <th><?= $name ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Собираем все уникальные времена
                        $times = [];
                        foreach ($schedule as $dayClasses) {
                            foreach ($dayClasses as $c) {
                                $t = substr($c['start_time'], 0, 5);
                                $times[$t] = $t;
                            }
                        }
                        sort($times);
                        foreach ($times as $time):
                        ?>
                        <tr>
                            <td><strong><?= $time ?></strong></td>
                            <?php for ($d = 1; $d <= 7; $d++): ?>
                            <td>
                                <?php foreach ($schedule[$d] as $c): ?>
                                    <?php if (substr($c['start_time'], 0, 5) === $time): ?>
                                        <div style="margin-bottom:4px;">
                                            <strong><?= htmlspecialchars($c['name']) ?></strong><br>
                                            <small><?= htmlspecialchars($c['trainer_name']) ?> (<?= $c['duration_min'] ?> мин)</small>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </td>
                            <?php endfor; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Футер -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                &copy; <?= date('Y') ?> FitLife. Система управления фитнес-клубом.
            </div>
        </div>
    </footer>

    <script src="/assets/js/app.js"></script>
</body>
</html>
