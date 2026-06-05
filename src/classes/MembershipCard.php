<?php
/**
 * Класс клубной карты (ООП: инкапсуляция, валидация, исключения)
 *
 * Реализует расчёт статуса клубной карты:
 * определение активности и остатка посещений.
 */
class MembershipCard
{
    private int    $id;
    private int    $userId;
    private string $type;           // basic | standard | premium | unlimited
    private string $startDate;
    private string $endDate;
    private int    $visitsTotal;    // 0 = безлимит
    private int    $visitsUsed;
    private float  $price;
    private string $status;         // active | expired | suspended | cancelled

    /**
     * Массив допустимых значений для полей-enum
     */
    private const VALID_TYPES   = ['basic', 'standard', 'premium', 'unlimited'];
    private const VALID_STATUSES = ['active', 'expired', 'suspended', 'cancelled'];

    // -------------------------------------------------------
    // Конструктор
    // -------------------------------------------------------
    public function __construct(
        int    $id,
        int    $userId,
        string $type,
        string $startDate,
        string $endDate,
        int    $visitsTotal,
        int    $visitsUsed,
        float  $price,
        string $status = 'active'
    ) {
        $this->validateType($type);
        $this->validateStatus($status);
        $this->validateDates($startDate, $endDate);
        $this->validateVisits($visitsTotal, $visitsUsed);

        $this->id          = $id;
        $this->userId      = $userId;
        $this->type        = $type;
        $this->startDate   = $startDate;
        $this->endDate     = $endDate;
        $this->visitsTotal = $visitsTotal;
        $this->visitsUsed  = $visitsUsed;
        $this->price       = $price;
        $this->status      = $status;
    }

    // -------------------------------------------------------
    // Геттеры (инкапсуляция данных)
    // -------------------------------------------------------
    public function getId(): int           { return $this->id; }
    public function getUserId(): int       { return $this->userId; }
    public function getType(): string      { return $this->type; }
    public function getStartDate(): string { return $this->startDate; }
    public function getEndDate(): string   { return $this->endDate; }
    public function getVisitsTotal(): int  { return $this->visitsTotal; }
    public function getVisitsUsed(): int   { return $this->visitsUsed; }
    public function getPrice(): float      { return $this->price; }
    public function getStatus(): string    { return $this->status; }

    // -------------------------------------------------------
    // Бизнес-логика: расчёт остатка посещений
    // -------------------------------------------------------
    public function getVisitsRemaining(): int
    {
        if ($this->visitsTotal === 0) {
            return -1; // безлимит
        }
        return max(0, $this->visitsTotal - $this->visitsUsed);
    }

    /**
     * Расчёт процента использования карты
     */
    public function getUsagePercent(): float
    {
        if ($this->visitsTotal === 0) {
            return 0.0;
        }
        return round(($this->visitsUsed / $this->visitsTotal) * 100, 1);
    }

    // -------------------------------------------------------
    // Бизнес-логика: определение текущего статуса активности
    // (постановка задачи №2)
    // -------------------------------------------------------
    public function calculateStatus(): string
    {
        $today = date('Y-m-d');

        // 1. Карта отменена или приостановлена — статус не меняется
        if (in_array($this->status, ['cancelled', 'suspended'])) {
            return $this->status;
        }

        // 2. Срок действия истёк
        if ($today > $this->endDate) {
            return 'expired';
        }

        // 3. Лимит посещений исчерпан (только для не-безлимитных)
        if ($this->visitsTotal > 0 && $this->visitsUsed >= $this->visitsTotal) {
            return 'expired';
        }

        return 'active';
    }

    /**
     * Полная информация о состоянии карты
     */
    public function getInfo(): array
    {
        $currentStatus = $this->calculateStatus();
        return [
            'id'              => $this->id,
            'type'            => $this->type,
            'start_date'      => $this->startDate,
            'end_date'        => $this->endDate,
            'visits_total'    => $this->visitsTotal,
            'visits_used'     => $this->visitsUsed,
            'visits_remaining'=> $this->getVisitsRemaining(),
            'usage_percent'   => $this->getUsagePercent(),
            'price'           => $this->price,
            'status'          => $currentStatus,
            'is_active'       => $currentStatus === 'active',
        ];
    }

    // -------------------------------------------------------
    // Сеттеры с валидацией
    // -------------------------------------------------------
    public function incrementVisit(): void
    {
        if ($this->getVisitsRemaining() === 0) {
            throw new \RuntimeException('Лимит посещений исчерпан');
        }
        $this->visitsUsed++;
    }

    public function setStatus(string $status): void
    {
        $this->validateStatus($status);
        $this->status = $status;
    }

    // -------------------------------------------------------
    // Валидация входных данных
    // -------------------------------------------------------
    private function validateType(string $type): void
    {
        if (!in_array($type, self::VALID_TYPES, true)) {
            throw new \InvalidArgumentException(
                "Недопустимый тип карты: {$type}. Допустимые: " . implode(', ', self::VALID_TYPES)
            );
        }
    }

    private function validateStatus(string $status): void
    {
        if (!in_array($status, self::VALID_STATUSES, true)) {
            throw new \InvalidArgumentException(
                "Недопустимый статус: {$status}. Допустимые: " . implode(', ', self::VALID_STATUSES)
            );
        }
    }

    private function validateDates(string $start, string $end): void
    {
        if (strtotime($start) === false || strtotime($end) === false) {
            throw new \InvalidArgumentException('Некорректный формат даты');
        }
        if (strtotime($start) > strtotime($end)) {
            throw new \InvalidArgumentException('Дата начала не может быть позже даты окончания');
        }
    }

    private function validateVisits(int $total, int $used): void
    {
        if ($total < 0 || $used < 0) {
            throw new \InvalidArgumentException('Количество посещений не может быть отрицательным');
        }
        if ($total > 0 && $used > $total) {
            throw new \InvalidArgumentException('Использованных посещений больше лимита');
        }
    }

    // -------------------------------------------------------
    // Создание из массива БД
    // -------------------------------------------------------
    public static function fromArray(array $data): self
    {
        return new self(
            (int)   $data['id'],
            (int)   $data['user_id'],
            (string)$data['type'],
            (string)$data['start_date'],
            (string)$data['end_date'],
            (int)   $data['visits_total'],
            (int)   $data['visits_used'],
            (float) $data['price'],
            (string)$data['status']
        );
    }
}
