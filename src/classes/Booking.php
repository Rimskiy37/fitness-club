<?php
/**
 * Класс записи на занятие
 */
class Booking
{
    private int    $id;
    private int    $userId;
    private int    $classId;
    private string $bookingDate;
    private string $status;
    private string $createdAt;

    private const VALID_STATUSES = ['new', 'confirmed', 'cancelled', 'completed', 'no_show'];

    public function __construct(
        int    $id,
        int    $userId,
        int    $classId,
        string $bookingDate,
        string $status = 'new',
        string $createdAt = ''
    ) {
        $this->validateStatus($status);
        $this->validateDate($bookingDate);

        $this->id          = $id;
        $this->userId      = $userId;
        $this->classId     = $classId;
        $this->bookingDate = $bookingDate;
        $this->status      = $status;
        $this->createdAt   = $createdAt;
    }

    public function getId(): int           { return $this->id; }
    public function getUserId(): int       { return $this->userId; }
    public function getClassId(): int      { return $this->classId; }
    public function getBookingDate(): string { return $this->bookingDate; }
    public function getStatus(): string    { return $this->status; }
    public function getCreatedAt(): string { return $this->createdAt; }

    public function getStatusLabel(): string
    {
        $labels = [
            'new' => 'Новая', 'confirmed' => 'Подтверждена',
            'cancelled' => 'Отменена', 'completed' => 'Завершена', 'no_show' => 'Не явился'
        ];
        return $labels[$this->status] ?? $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->validateStatus($status);
        $this->status = $status;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['new', 'confirmed']);
    }

    private function validateStatus(string $s): void
    {
        if (!in_array($s, self::VALID_STATUSES, true)) {
            throw new \InvalidArgumentException("Недопустимый статус: {$s}");
        }
    }

    private function validateDate(string $date): void
    {
        if (strtotime($date) === false) {
            throw new \InvalidArgumentException("Некорректная дата: {$date}");
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int)   $data['id'],
            (int)   $data['user_id'],
            (int)   $data['class_id'],
            (string)$data['booking_date'],
            (string)$data['status'],
            (string)($data['created_at'] ?? '')
        );
    }
}
