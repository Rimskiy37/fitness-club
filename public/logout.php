<?php
require_once __DIR__ . '/../src/helpers/config.php';
$auth = new AuthController();
$auth->logout();
header('Location: /');
exit;
