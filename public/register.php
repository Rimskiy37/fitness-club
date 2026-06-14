<?php
require_once __DIR__ . '/../src/helpers/config.php';

if (AuthController::check()) {
    header('Location: /profile');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new AuthController();
    $result = $auth->register([
        'email'      => $_POST['email'] ?? '',
        'password'   => $_POST['password'] ?? '',
        'first_name' => $_POST['first_name'] ?? '',
        'last_name'  => $_POST['last_name'] ?? '',
        'phone'      => $_POST['phone'] ?? '',
    ]);

    if ($result['success']) {
        $success = 'Регистрация прошла успешно! Перенаправление...';
        header('Refresh: 2; URL=profile.php');
    } else {
        $error = $result['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация — FitLife</title>
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
            </nav>
        </div>
    </header>

    <div class="auth-wrapper">
        <div class="auth-card">
            <h2>📝 Регистрация</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST" action="" id="registerForm">
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" id="regEmail" class="form-control" required placeholder="example@mail.ru">
                    <div id="emailFeedback" class="invalid-feedback"></div>
                </div>
                <div class="form-group">
                    <label>Имя *</label>
                    <input type="text" name="first_name" class="form-control" required placeholder="Иван">
                </div>
                <div class="form-group">
                    <label>Фамилия *</label>
                    <input type="text" name="last_name" class="form-control" required placeholder="Иванов">
                </div>
                <div class="form-group">
                    <label>Телефон</label>
                    <input type="tel" name="phone" class="form-control" placeholder="+7(900)123-45-67">
                </div>
                <div class="form-group">
                    <label>Пароль * (минимум 6 символов)</label>
                    <input type="password" name="password" class="form-control" required minlength="6" placeholder="••••••">
                </div>
                <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
            </form>

            <p style="text-align:center;margin-top:20px;">
                Уже есть аккаунт? <a href="/login">Войдите</a>
            </p>
        </div>
    </div>

    <script src="/assets/js/app.js"></script>
</body>
</html>
