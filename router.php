<?php
/**
 * Router для встроенного PHP-сервера
 * Запуск: php -S localhost:8000 router.php
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

/** Отдача статического файла с правильным MIME-типом */
function serveStatic(string $file): void
{
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $mimes = [
        'css'   => 'text/css; charset=utf-8',
        'js'    => 'application/javascript; charset=utf-8',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'svg'   => 'image/svg+xml',
        'ico'   => 'image/x-icon',
        'webp'  => 'image/webp',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
    ];
    $mime = $mimes[$ext] ?? 'application/octet-stream';
    header("Content-Type: {$mime}");
    header("Cache-Control: public, max-age=3600");
    readfile($file);
}

// --- Статика: публичная (/assets/) ---
if (preg_match('#^/assets/(.+)#', $uri, $m)) {
    $file = __DIR__ . '/public/assets/' . $m[1];
    if (is_file($file)) {
        serveStatic($file);
        return;
    }
}

// --- Статика: админка (/admin/assets/) ---
if (preg_match('#^/admin/assets/(.+)#', $uri, $m)) {
    $file = __DIR__ . '/admin/assets/' . $m[1];
    if (is_file($file)) {
        serveStatic($file);
        return;
    }
}

// --- API: публичная (/api/) ---
if (preg_match('#^/api/(.+\.php)$#', $uri, $m)) {
    $file = __DIR__ . '/public/api/' . $m[1];
    if (is_file($file)) {
        require $file;
        return;
    }
}

// --- API: админка (/admin/api/) ---
if (preg_match('#^/admin/api/(.+\.php)$#', $uri, $m)) {
    $file = __DIR__ . '/admin/api/' . $m[1];
    if (is_file($file)) {
        require $file;
        return;
    }
}

// --- Страницы админки (/admin/...) ---
if (preg_match('#^/admin/(.+\.php)$#', $uri, $m)) {
    $file = __DIR__ . '/admin/' . $m[1];
    if (is_file($file)) {
        header('Content-Type: text/html; charset=utf-8');
        require $file;
        return;
    }
}
if ($uri === '/admin' || $uri === '/admin/') {
    header('Content-Type: text/html; charset=utf-8');
    require __DIR__ . '/admin/index.php';
    return;
}

// --- Публичные страницы ---
$publicRoutes = [
    '/'             => 'index.php',
    '/index.php'    => 'index.php',
    '/classes'      => 'classes.php',
    '/classes.php'  => 'classes.php',
    '/login'        => 'login.php',
    '/login.php'    => 'login.php',
    '/register'     => 'register.php',
    '/register.php' => 'register.php',
    '/profile'      => 'profile.php',
    '/profile.php'  => 'profile.php',
    '/logout'       => 'logout.php',
    '/logout.php'   => 'logout.php',
];

$route = rtrim($uri, '/') ?: '/';
if (isset($publicRoutes[$route])) {
    header('Content-Type: text/html; charset=utf-8');
    require __DIR__ . '/public/' . $publicRoutes[$route];
    return;
}

// --- 404 ---
http_response_code(404);
echo '<h1>404 — Страница не найдена</h1><p><a href="/">На главную</a></p>';
