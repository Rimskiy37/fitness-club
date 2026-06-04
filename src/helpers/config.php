<?php
/**
 * Конфигурация проекта «Система управления фитнес-клубом»
 * Запуск: php -S localhost:8000 router.php
 */

// --- Настройки базы данных ---
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'fitness_club');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// --- Настройки приложения ---
define('APP_NAME', 'FitLife — Фитнес-клуб');

// Автоопределение URL (работает с php -S)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
define('APP_URL', $protocol . '://' . $host);
define('APP_ROOT', dirname(__DIR__, 2));

// --- Настройки сессии ---
define('SESSION_LIFETIME', 3600); // 1 час

// --- Автозагрузка классов ---
spl_autoload_register(function (string $class) {
    $dirs = [
        APP_ROOT . '/src/classes/',
        APP_ROOT . '/src/models/',
        APP_ROOT . '/src/controllers/',
    ];
    foreach ($dirs as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// --- Запуск сессии ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
