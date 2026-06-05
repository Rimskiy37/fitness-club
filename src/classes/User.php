<?php
/**
 * Класс пользователя системы
 */
class User
{
    private int    $id;
    private string $email;
    private string $firstName;
    private string $lastName;
    private string $phone;
    private string $role;
    private string $avatar;
    private string $createdAt;

    private const VALID_ROLES = ['member', 'trainer', 'admin', 'manager'];

    public function __construct(
        int    $id,
        string $email,
        string $firstName,
        string $lastName,
        string $phone = '',
        string $role = 'member',
        string $avatar = '',
        string $createdAt = ''
    ) {
        $this->validateEmail($email);
        $this->validateRole($role);

        $this->id        = $id;
        $this->email     = $email;
        $this->firstName = $firstName;
        $this->lastName  = $lastName;
        $this->phone     = $phone;
        $this->role      = $role;
        $this->avatar    = $avatar;
        $this->createdAt = $createdAt;
    }

    public function getId(): int           { return $this->id; }
    public function getEmail(): string     { return $this->email; }
    public function getFirstName(): string { return $this->firstName; }
    public function getLastName(): string  { return $this->lastName; }
    public function getPhone(): string     { return $this->phone; }
    public function getRole(): string      { return $this->role; }
    public function getAvatar(): string    { return $this->avatar; }
    public function getCreatedAt(): string { return $this->createdAt; }

    public function getFullName(): string
    {
        return "{$this->firstName} {$this->lastName}";
    }

    public function getRoleLabel(): string
    {
        $labels = [
            'member'  => 'Член клуба',
            'trainer' => 'Тренер',
            'admin'   => 'Администратор',
            'manager' => 'Менеджер'
        ];
        return $labels[$this->role] ?? $this->role;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'manager']);
    }

    public function isTrainer(): bool
    {
        return $this->role === 'trainer';
    }

    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    public function getAvatarUrl(): string
    {
        if ($this->avatar && $this->avatar !== 'avatar_default.png') {
            return APP_URL . '/assets/images/' . $this->avatar;
        }
        return APP_URL . '/assets/images/avatar_default.png';
    }

    private function validateEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Некорректный email: {$email}");
        }
    }

    private function validateRole(string $role): void
    {
        if (!in_array($role, self::VALID_ROLES, true)) {
            throw new \InvalidArgumentException("Недопустимая роль: {$role}");
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int)   $data['id'],
            (string)$data['email'],
            (string)$data['first_name'],
            (string)$data['last_name'],
            (string)($data['phone'] ?? ''),
            (string)$data['role'],
            (string)($data['avatar'] ?? ''),
            (string)($data['created_at'] ?? '')
        );
    }
}
