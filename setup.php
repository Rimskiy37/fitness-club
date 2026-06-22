<?php
/**
 * Скрипт инициализации базы данных
 * Запуск: php setup.php
 *
 * Создаёт базу, таблицы и тестовые данные с правильными хэшами паролей.
 */

echo "=== FitLife: Настройка базы данных ===\n\n";

// Настройки
$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = '';
$dbName = 'fitness_club';
$dbCharset = 'utf8mb4';
$defaultPassword = 'password123';

// Пароль из аргументов командной строки (если передан)
if (isset($argv[1])) {
    $dbPass = $argv[1];
    echo "Используется пароль MySQL из аргументов.\n";
}

try {
    // Подключение без указания БД (для создания базы)
    $pdo = new PDO(
        "mysql:host={$dbHost};charset={$dbCharset}",
        $dbUser, $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✓ Подключение к MySQL успешно\n";

    // Создание базы
    $pdo->exec("DROP DATABASE IF EXISTS `{$dbName}`");
    $pdo->exec("CREATE DATABASE `{$dbName}` CHARACTER SET {$dbCharset} COLLATE {$dbCharset}_unicode_ci");
    $pdo->exec("USE `{$dbName}`");
    echo "✓ База данных `{$dbName}` создана\n";

    // ===== ТАБЛИЦЫ =====
    echo "\n--- Создание таблиц ---\n";

    $pdo->exec("CREATE TABLE `users` (
        `id`            INT AUTO_INCREMENT PRIMARY KEY,
        `email`         VARCHAR(255)    NOT NULL UNIQUE,
        `password_hash` VARCHAR(255)    NOT NULL,
        `first_name`    VARCHAR(100)    NOT NULL,
        `last_name`     VARCHAR(100)    NOT NULL,
        `phone`         VARCHAR(20)     DEFAULT NULL,
        `role`          ENUM('member','trainer','admin','manager') NOT NULL DEFAULT 'member',
        `avatar`        VARCHAR(255)    DEFAULT NULL,
        `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET={$dbCharset} COLLATE={$dbCharset}_unicode_ci");
    echo "  ✓ users\n";

    $pdo->exec("CREATE TABLE `memberships` (
        `id`            INT AUTO_INCREMENT PRIMARY KEY,
        `user_id`       INT             NOT NULL,
        `type`          ENUM('basic','standard','premium','unlimited') NOT NULL DEFAULT 'basic',
        `start_date`    DATE            NOT NULL,
        `end_date`      DATE            NOT NULL,
        `visits_total`  INT             NOT NULL DEFAULT 0,
        `visits_used`   INT             NOT NULL DEFAULT 0,
        `price`         DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
        `status`        ENUM('active','expired','suspended','cancelled') NOT NULL DEFAULT 'active',
        `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET={$dbCharset} COLLATE={$dbCharset}_unicode_ci");
    echo "  ✓ memberships\n";

    $pdo->exec("CREATE TABLE `visits` (
        `id`            INT AUTO_INCREMENT PRIMARY KEY,
        `user_id`       INT             NOT NULL,
        `visit_date`    DATE            NOT NULL,
        `visit_time`    TIME            NOT NULL,
        `visit_type`    ENUM('individual','group','personal_training') NOT NULL DEFAULT 'individual',
        `notes`         TEXT            DEFAULT NULL,
        `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET={$dbCharset} COLLATE={$dbCharset}_unicode_ci");
    echo "  ✓ visits\n";

    $pdo->exec("CREATE TABLE `classes` (
        `id`                INT AUTO_INCREMENT PRIMARY KEY,
        `trainer_id`        INT             NOT NULL,
        `name`              VARCHAR(200)    NOT NULL,
        `description`       TEXT            DEFAULT NULL,
        `category`          ENUM('yoga','pilates','cardio','strength','dance','crossfit','boxing','stretching') NOT NULL,
        `day_of_week`       TINYINT         NOT NULL,
        `start_time`        TIME            NOT NULL,
        `duration_min`      INT             NOT NULL DEFAULT 60,
        `max_participants`  INT             NOT NULL DEFAULT 20,
        `image`             VARCHAR(255)    DEFAULT NULL,
        `is_active`         TINYINT(1)      NOT NULL DEFAULT 1,
        `created_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`trainer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET={$dbCharset} COLLATE={$dbCharset}_unicode_ci");
    echo "  ✓ classes\n";

    $pdo->exec("CREATE TABLE `bookings` (
        `id`            INT AUTO_INCREMENT PRIMARY KEY,
        `user_id`       INT             NOT NULL,
        `class_id`      INT             NOT NULL,
        `booking_date`  DATE            NOT NULL,
        `status`        ENUM('new','confirmed','cancelled','completed','no_show') NOT NULL DEFAULT 'new',
        `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`)  REFERENCES `users`(`id`)   ON DELETE CASCADE,
        FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE,
        UNIQUE KEY `uniq_booking` (`user_id`, `class_id`, `booking_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$dbCharset} COLLATE={$dbCharset}_unicode_ci");
    echo "  ✓ bookings\n";

    // Индексы
    $pdo->exec("CREATE INDEX idx_users_role       ON `users`(`role`)");
    $pdo->exec("CREATE INDEX idx_memberships_user ON `memberships`(`user_id`)");
    $pdo->exec("CREATE INDEX idx_visits_user_date ON `visits`(`user_id`, `visit_date`)");
    $pdo->exec("CREATE INDEX idx_classes_day      ON `classes`(`day_of_week`, `start_time`)");
    $pdo->exec("CREATE INDEX idx_bookings_status  ON `bookings`(`status`)");

    // ===== ТЕСТОВЫЕ ДАННЫЕ =====
    echo "\n--- Заполнение тестовыми данными ---\n";
    echo "  Пароль для всех: {$defaultPassword}\n\n";

    $hash = password_hash($defaultPassword, PASSWORD_DEFAULT);
    echo "  Хеш пароля: " . substr($hash, 0, 30) . "...\n";

    // Пользователи
    $users = [
        ['admin@fitnessclub.ru',       'Иван',     'Петров',   '+7(900)100-00-01', 'admin',   'avatar_default.png'],
        ['manager@fitnessclub.ru',     'Елена',    'Сидорова', '+7(900)100-00-02', 'manager', 'avatar_default.png'],
        ['trainer1@fitnessclub.ru',    'Алексей',  'Козлов',   '+7(900)200-00-01', 'trainer', 'avatar_default.png'],
        ['trainer2@fitnessclub.ru',    'Мария',    'Новикова', '+7(900)200-00-02', 'trainer', 'avatar_default.png'],
        ['trainer3@fitnessclub.ru',    'Дмитрий',  'Волков',   '+7(900)200-00-03', 'trainer', 'avatar_default.png'],
        ['trainer4@fitnessclub.ru',    'Ольга',    'Морозова', '+7(900)200-00-04', 'trainer', 'avatar_default.png'],
        ['member1@mail.ru',            'Анна',     'Иванова',  '+7(900)300-00-01', 'member',  'avatar_default.png'],
        ['member2@mail.ru',            'Сергей',   'Кузнецов', '+7(900)300-00-02', 'member',  'avatar_default.png'],
        ['member3@mail.ru',            'Наталья',  'Попова',   '+7(900)300-00-03', 'member',  'avatar_default.png'],
        ['member4@mail.ru',            'Павел',    'Лебедев',  '+7(900)300-00-04', 'member',  'avatar_default.png'],
        ['member5@mail.ru',            'Татьяна',  'Соколова', '+7(900)300-00-05', 'member',  'avatar_default.png'],
    ];

    $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, first_name, last_name, phone, role, avatar) VALUES (?, ?, ?, ?, ?, ?, ?)');
    foreach ($users as $u) {
        $stmt->execute([$u[0], $hash, $u[1], $u[2], $u[3], $u[4], $u[5]]);
    }
    echo "  ✓ " . count($users) . " пользователей\n";

    // Клубные карты
    $pdo->exec("INSERT INTO memberships (user_id, type, start_date, end_date, visits_total, visits_used, price, status) VALUES
        (7,  'premium',   '2026-01-01','2026-12-31', 0,  0,  35000.00, 'active'),
        (8,  'standard',  '2026-01-01','2026-06-30', 24, 10, 18000.00, 'active'),
        (9,  'basic',     '2026-02-01','2026-05-01', 8,  7,   8000.00, 'active'),
        (10, 'unlimited', '2026-01-15','2026-07-15', 0,  0,  42000.00, 'active'),
        (11, 'standard',  '2025-07-01','2025-12-31', 24, 22, 18000.00, 'expired')
    ");
    echo "  ✓ 5 клубных карт\n";

    // Посещения
    $pdo->exec("INSERT INTO visits (user_id, visit_date, visit_time, visit_type) VALUES
        (7,  '2026-05-20','09:00:00','group'),
        (7,  '2026-05-22','10:00:00','individual'),
        (7,  '2026-05-25','18:00:00','personal_training'),
        (8,  '2026-05-20','09:00:00','group'),
        (8,  '2026-05-23','11:00:00','individual'),
        (8,  '2026-05-24','19:00:00','group'),
        (9,  '2026-05-21','08:00:00','individual'),
        (9,  '2026-05-28','08:00:00','individual'),
        (10, '2026-05-20','07:00:00','individual'),
        (10, '2026-05-21','09:00:00','group'),
        (10, '2026-05-22','07:00:00','individual'),
        (10, '2026-05-23','18:00:00','personal_training'),
        (10, '2026-05-24','09:00:00','group'),
        (10, '2026-05-25','07:00:00','individual')
    ");
    echo "  ✓ 14 посещений\n";

    // Групповые занятия
    $pdo->exec("INSERT INTO classes (trainer_id, name, description, category, day_of_week, start_time, duration_min, max_participants, image) VALUES
        (3,'Йога для начинающих',   'Мягкая практика для расслабления и гибкости. Подходит для всех уровней.',    'yoga',       1,'09:00:00',60,15,'class_yoga.png'),
        (4,'Пилатес',               'Укрепление мышц кора, улучшение осанки и координации.',                       'pilates',    1,'11:00:00',50,12,'class_pilates.png'),
        (6,'Стретчинг',             'Глубокая растяжка всего тела для восстановления после нагрузок.',              'stretching', 1,'18:00:00',45,20,'class_stretching.png'),
        (5,'CrossFit WOD',          'Высокоинтенсивная тренировка с функциональными движениями.',                  'crossfit',   2,'10:00:00',60,10,'class_crossfit.png'),
        (3,'Йога средний уровень',  'Продолжение практики для тех, кто освоил базовые асаны.',                     'yoga',       2,'19:00:00',75,15,'class_yoga.png'),
        (6,'Танцевальный фитнес',   'Энергичные танцы под музыку — кардио + настроение.',                         'dance',      3,'10:00:00',60,25,'class_dance.png'),
        (5,'Боксёрская тренировка', 'Основы бокса: стойка, удары, работа на мешках.',                             'boxing',     3,'18:00:00',60,8, 'class_boxing.png'),
        (4,'Кардио-микс',           'Интервальная кардиотренировка: бег, прыжки, велотренажёр.',                   'cardio',     4,'09:00:00',45,20,'class_cardio.png'),
        (5,'Силовая тренировка',    'Работа со свободными весами, тренажёрами и собственным весом.',               'strength',   4,'17:00:00',60,15,'class_strength.png'),
        (3,'Утренняя йога',         'Бодрящая утренняя практика для пробуждения тела.',                            'yoga',       5,'08:00:00',50,15,'class_yoga.png'),
        (6,'Пилатес продвинутый',   'Интенсивная работа на баланс и стабилизацию.',                                'pilates',    5,'19:00:00',55,12,'class_pilates.png'),
        (4,'HIIT',                  'Высокоинтенсивная интервальная тренировка — максимум за минимум времени.',    'cardio',     6,'10:00:00',30,20,'class_cardio.png')
    ");
    echo "  ✓ 12 групповых занятий\n";

    // Записи
    $pdo->exec("INSERT INTO bookings (user_id, class_id, booking_date, status) VALUES
        (7,  1,'2026-06-09','confirmed'),
        (7,  5,'2026-06-09','confirmed'),
        (8,  2,'2026-06-09','confirmed'),
        (8,  6,'2026-06-10','new'),
        (9,  3,'2026-06-09','new'),
        (10, 1,'2026-06-09','confirmed'),
        (10, 4,'2026-06-09','confirmed'),
        (10, 7,'2026-06-10','new')
    ");
    echo "  ✓ 8 записей на занятия\n";

    echo "\n=== Готово! ===\n\n";
    echo "Запусти сервер:\n";
    echo "  php -S localhost:8000 router.php\n\n";
    echo "Открой в браузере:\n";
    echo "  http://localhost:8000\n\n";
    echo "Тестовые аккаунты (пароль: {$defaultPassword}):\n";
    echo "  Админ:   admin@fitnessclub.ru\n";
    echo "  Клиент:  member1@mail.ru\n";
    echo "  Админка: http://localhost:8000/admin\n";

} catch (PDOException $e) {
    echo "\n✗ ОШИБКА: " . $e->getMessage() . "\n\n";
    echo "Возможные причины:\n";
    echo "  1. MySQL не запущен — запусти через XAMPP Control Panel\n";
    echo "  2. Неверный пароль MySQL — запусти: php setup.php \"твой_пароль\"\n";
    echo "  3. MySQL не на стандартном порту — проверь настройки\n";
}
