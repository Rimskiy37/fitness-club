<?php
require_once __DIR__ . '/../src/helpers/config.php';

$classModel = new ClassModel();
$categories = $classModel->getCategories();
$popularClasses = array_slice($classModel->getAll(), 0, 6);

$userName = $_SESSION['user_name'] ?? '';
$userRole = $_SESSION['user_role'] ?? '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitLife — Фитнес-клуб</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script>const APP_URL = '<?= APP_URL ?>';</script>
</head>
<body>
    <!-- Шапка -->
    <header class="header">
        <div class="container">
            <a href="/" class="logo">
                💪 <span>Fit</span>Life
            </a>
            <nav class="nav">
                <a href="/" class="active">Главная</a>
                <a href="/classes">Расписание</a>
                <?php if (AuthController::check()): ?>
                    <a href="/profile">Личный кабинет</a>
                    <a href="/logout">Выйти</a>
                <?php else: ?>
                    <a href="/login" class="btn btn-outline btn-sm">Войти</a>
                    <a href="/register" class="btn btn-primary btn-sm">Регистрация</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Герой-баннер -->
    <section class="hero">
        <div class="container">
            <!-- Анимационный элемент: бегущий спортсмен -->
            <div class="runner-container">
                <div class="runner">🏃</div>
                <div class="runner-track"></div>
            </div>
            <h1>Добро пожаловать в FitLife!</h1>
            <p>Современный фитнес-клуб с групповыми занятиями, персональными тренировками и индивидуальным подходом к каждому клиенту.</p>
            <!-- Анимация: пульсирующие точки -->
            <div class="dots-animation">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
            <a href="/classes" class="btn btn-primary" style="background:#fff;color:var(--primary);">
                📋 Расписание занятий
            </a>
        </div>
        <img src="/assets/images/hero_bg.png" alt="" class="hero-img">
    </section>

    <!-- Популярные занятия -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Популярные <span>занятия</span></h2>

            <!-- Фильтры по категориям -->
            <div class="filters">
                <button class="filter-btn active" onclick="filterByCategory('all', this)">Все</button>
                <?php foreach ($categories as $cat): ?>
                    <button class="filter-btn" onclick="filterByCategory('<?= $cat ?>', this)">
                        <?= $cat ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div id="classCards" class="cards-grid">
                <?php foreach ($popularClasses as $c): ?>
                <div class="card fade-in">
                    <img class="card-img" src="/assets/images/<?= htmlspecialchars($c['image'] ?: 'class_default.png') ?>" alt="<?= htmlspecialchars($c['name']) ?>">
                    <div class="card-body">
                        <span class="card-category"><?= htmlspecialchars($c['category']) ?></span>
                        <h3 class="card-title"><?= htmlspecialchars($c['name']) ?></h3>
                        <p class="card-text"><?= htmlspecialchars(substr($c['description'] ?? '', 0, 80)) ?>...</p>
                        <div class="card-meta">
                            <span>👤 <?= htmlspecialchars($c['trainer_name']) ?></span>
                            <span>👥 до <?= $c['max_participants'] ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div style="text-align:center;margin-top:30px;">
                <a href="/classes" class="btn btn-secondary">Смотреть все занятия →</a>
            </div>
        </div>
    </section>

    <!-- Преимущества -->
    <section class="section" style="background:var(--bg-white);">
        <div class="container">
            <h2 class="section-title">Наши <span>преимущества</span></h2>
            <div class="cards-grid">
                <div class="card" style="text-align:center;padding:30px;">
                    <div style="margin-bottom:12px;">
                        <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="48" height="48" rx="12" fill="#F1FAEE"/>
                            <rect x="8" y="20" width="6" height="16" rx="2" fill="#E63946"/>
                            <rect x="17" y="14" width="6" height="22" rx="2" fill="#457B9D"/>
                            <rect x="26" y="18" width="6" height="18" rx="2" fill="#E63946"/>
                            <rect x="35" y="22" width="6" height="14" rx="2" fill="#457B9D"/>
                        </svg>
                    </div>
                    <h3>Современное оборудование</h3>
                    <p class="card-text">Полный набор тренажёров и свободных весов для любых целей</p>
                </div>
                <div class="card" style="text-align:center;padding:30px;">
                    <div style="margin-bottom:12px;">
                        <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="48" height="48" rx="12" fill="#F1FAEE"/>
                            <circle cx="24" cy="14" r="6" fill="#457B9D"/>
                            <path d="M16 26c0-4.4 3.6-8 8-8s8 3.6 8 8v12H16V26z" fill="#1D3557"/>
                            <rect x="14" y="34" width="20" height="3" rx="1.5" fill="#F4A261"/>
                        </svg>
                    </div>
                    <h3>Профессиональные тренеры</h3>
                    <p class="card-text">Сертифицированные специалисты с многолетним опытом</p>
                </div>
                <div class="card" style="text-align:center;padding:30px;">
                    <div style="margin-bottom:12px;">
                        <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="48" height="48" rx="12" fill="#F1FAEE"/>
                            <rect x="12" y="10" width="24" height="34" rx="4" stroke="#457B9D" stroke-width="2.5" fill="none"/>
                            <rect x="20" y="6" width="8" height="6" rx="2" fill="#E63946"/>
                            <line x1="18" y1="20" x2="30" y2="20" stroke="#1D3557" stroke-width="2" stroke-linecap="round"/>
                            <line x1="18" y1="26" x2="28" y2="26" stroke="#1D3557" stroke-width="2" stroke-linecap="round"/>
                            <line x1="18" y1="32" x2="26" y2="32" stroke="#1D3557" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <h3>Онлайн-управление</h3>
                    <p class="card-text">Запись на занятия, отслеживание посещений и карты — всё онлайн</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Футер -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <h4>FitLife</h4>
                    <p>Современная система управления фитнес-клубом для автоматизации процессов и повышения удержания клиентов.</p>
                </div>
                <div>
                    <h4>Навигация</h4>
                    <p><a href="/">Главная</a></p>
                    <p><a href="/classes">Расписание</a></p>
                    <p><a href="/login">Войти</a></p>
                </div>
                <div>
                    <h4>Контакты</h4>
                    <p>📍 г. Москва, ул. Спортивная, 15</p>
                    <p>📞 +7 (495) 123-45-67</p>
                    <p>📧 info@fitnessclub.ru</p>
                </div>
            </div>
            <div class="footer-bottom">
                &copy; <?= date('Y') ?> FitLife. Система управления фитнес-клубом.
            </div>
        </div>
    </footer>

    <script src="/assets/js/app.js"></script>
</body>
</html>
