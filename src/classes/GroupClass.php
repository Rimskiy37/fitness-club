<?php
/**
 * Класс группового занятия
 */
class GroupClass
{
    private int    $id;
    private int    $trainerId;
    private string $name;
    private string $description;
    private string $category;
    private int    $dayOfWeek;       // 1=Пн ... 7=Вс
    private string $startTime;
    private int    $durationMin;
    private int    $maxParticipants;
    private string $image;
    private bool   $isActive;

    private const VALID_CATEGORIES = [
        'yoga', 'pilates', 'cardio', 'strength',
        'dance', 'crossfit', 'boxing', 'stretching'
    ];

    private const DAY_NAMES = [
        1 => 'Понедельник', 2 => 'Вторник', 3 => 'Среда',
        4 => 'Четверг',     5 => 'Пятница', 6 => 'Суббота', 7 => 'Воскресенье'
    ];

    public function __construct(
        int    $id,
        int    $trainerId,
        string $name,
        string $description,
        string $category,
        int    $dayOfWeek,
        string $startTime,
        int    $durationMin = 60,
        int    $maxParticipants = 20,
        string $image = '',
        bool   $isActive = true
    ) {
        $this->validateCategory($category);
        $this->validateDayOfWeek($dayOfWeek);

        $this->id              = $id;
        $this->trainerId       = $trainerId;
        $this->name            = $name;
        $this->description     = $description;
        $this->category        = $category;
        $this->dayOfWeek       = $dayOfWeek;
        $this->startTime       = $startTime;
        $this->durationMin     = $durationMin;
        $this->maxParticipants = $maxParticipants;
        $this->image           = $image;
        $this->isActive        = $isActive;
    }

    // Геттеры
    public function getId(): int              { return $this->id; }
    public function getTrainerId(): int       { return $this->trainerId; }
    public function getName(): string         { return $this->name; }
    public function getDescription(): string  { return $this->description; }
    public function getCategory(): string     { return $this->category; }
    public function getDayOfWeek(): int       { return $this->dayOfWeek; }
    public function getStartTime(): string    { return $this->startTime; }
    public function getDurationMin(): int     { return $this->durationMin; }
    public function getMaxParticipants(): int { return $this->maxParticipants; }
    public function getImage(): string        { return $this->image; }
    public function isActive(): bool          { return $this->isActive; }

    public function getDayName(): string
    {
        return self::DAY_NAMES[$this->dayOfWeek] ?? 'Неизвестно';
    }

    public function getEndTime(): string
    {
        $end = strtotime($this->startTime) + $this->durationMin * 60;
        return date('H:i', $end);
    }

    public function getCategoryLabel(): string
    {
        $labels = [
            'yoga' => 'Йога', 'pilates' => 'Пилатес', 'cardio' => 'Кардио',
            'strength' => 'Силовая', 'dance' => 'Танцы',
            'crossfit' => 'CrossFit', 'boxing' => 'Бокс', 'stretching' => 'Стретчинг'
        ];
        return $labels[$this->category] ?? $this->category;
    }

    private function validateCategory(string $cat): void
    {
        if (!in_array($cat, self::VALID_CATEGORIES, true)) {
            throw new \InvalidArgumentException("Недопустимая категория: {$cat}");
        }
    }

    private function validateDayOfWeek(int $d): void
    {
        if ($d < 1 || $d > 7) {
            throw new \InvalidArgumentException("День недели должен быть 1-7, получено: {$d}");
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int)   $data['id'],
            (int)   $data['trainer_id'],
            (string)$data['name'],
            (string)$data['description'],
            (string)$data['category'],
            (int)   $data['day_of_week'],
            (string)$data['start_time'],
            (int)   $data['duration_min'],
            (int)   $data['max_participants'],
            (string)($data['image'] ?? ''),
            (bool)  ($data['is_active'] ?? true)
        );
    }
}
