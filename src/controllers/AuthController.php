<?php
/**
 * Контроллер авторизации
 */
class AuthController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Вход пользователя
     */
    public function login(string $email, string $password): array
    {
        $user = $this->userModel->getByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'error' => 'Неверный email или пароль'];
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];

        return ['success' => true, 'user' => $user];
    }

    /**
     * Регистрация нового пользователя (член клуба)
     * Автоматически создаёт базовую клубную карту
     */
    public function register(array $data): array
    {
        // Валидация
        if (empty($data['email']) || empty($data['password']) || empty($data['first_name']) || empty($data['last_name'])) {
            return ['success' => false, 'error' => 'Заполните все обязательные поля'];
        }

        if (strlen($data['password']) < 6) {
            return ['success' => false, 'error' => 'Пароль должен быть не менее 6 символов'];
        }

        if ($this->userModel->emailExists($data['email'])) {
            return ['success' => false, 'error' => 'Этот email уже зарегистрирован'];
        }

        $data['role'] = 'member';
        $id = $this->userModel->create($data);

        $_SESSION['user_id'] = $id;
        $_SESSION['user_role'] = 'member';
        $_SESSION['user_name'] = $data['first_name'] . ' ' . $data['last_name'];

        // Автоматически создаём базовую клубную карту на 1 месяц
        $membershipModel = new MembershipModel();
        $membershipModel->create([
            'user_id'      => $id,
            'type'         => 'basic',
            'start_date'   => date('Y-m-d'),
            'end_date'     => date('Y-m-d', strtotime('+1 month')),
            'visits_total' => 8,
            'visits_used'  => 0,
            'price'        => 8000.00,
            'status'       => 'active',
        ]);

        return ['success' => true, 'user_id' => $id];
    }

    /**
     * Выход
     */
    public function logout(): void
    {
        session_unset();
        session_destroy();
    }

    /**
     * Проверка авторизации
     */
    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Проверка права администратора
     */
    public static function isAdmin(): bool
    {
        return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'manager']);
    }

    /**
     * Получить текущего пользователя
     */
    public static function user(): ?array
    {
        if (!self::check()) return null;
        $model = new UserModel();
        return $model->getById($_SESSION['user_id']);
    }

    /**
     * Требовать авторизацию (редирект если не авторизован)
     */
    public static function requireAuth(): void
    {
        if (!self::check()) {
            header('Location: /login');
            exit;
        }
    }

    /**
     * Требовать права администратора
     */
    public static function requireAdmin(): void
    {
        self::requireAuth();
        if (!self::isAdmin()) {
            header('Location: /');
            exit;
        }
    }
}
