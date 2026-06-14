<?php
require_once __DIR__ . '/../src/helpers/config.php';

if (AuthController::check()) {
    header('Location: /profile');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new AuthController();
    $result = $auth->login($_POST['email'] ?? '', $_POST['password'] ?? '');
    if ($result['success']) {
        header('Location: /profile');
        exit;
    }
    $error = $result['error'];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход — FitLife</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <a href="/" class="logo">💪 <span>Fit</span>Life</a>
            <nav class="nav">
                <a href="/">Главная</a>
                <a href="/classes">Расписание</a>
            </nav>
        </div>
    </header>

    <div class="auth-wrapper">
        <div class="auth-card">
            <h2>🔑 Вход в систему</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="example@mail.ru">
                </div>
                <div class="form-group">
                    <label>Пароль</label>
                    <input type="password" name="password" class="form-control" required placeholder="••••••">
                </div>
                <button type="submit" class="btn btn-primary">Войти</button>
            </form>

            <p style="text-align:center;margin-top:20px;">
                Нет аккаунта? <a href="/register">Зарегистрируйтесь</a>
            </p>
        </div>
    </div>
</body>
</html>
