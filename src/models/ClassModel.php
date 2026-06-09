<?php
/**
 * Модель для работы с таблицей classes (групповые занятия)
 */
class ClassModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, CONCAT(u.first_name, \' \', u.last_name) AS trainer_name
             FROM classes c
             JOIN users u ON u.id = c.trainer_id
             WHERE c.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT c.*, CONCAT(u.first_name, \' \', u.last_name) AS trainer_name
             FROM classes c
             JOIN users u ON u.id = c.trainer_id
             WHERE c.is_active = 1
             ORDER BY c.day_of_week, c.start_time'
        );
        return $stmt->fetchAll();
    }

    /**
     * Получить занятия по категории (для AJAX-фильтрации)
     */
    public function getByCategory(string $category): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, CONCAT(u.first_name, \' \', u.last_name) AS trainer_name
             FROM classes c
             JOIN users u ON u.id = c.trainer_id
             WHERE c.category = ? AND c.is_active = 1
             ORDER BY c.day_of_week, c.start_time'
        );
        $stmt->execute([$category]);
        return $stmt->fetchAll();
    }

    /**
     * Поиск занятий по названию (для AJAX-поиска)
     */
    public function search(string $query): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, CONCAT(u.first_name, \' \', u.last_name) AS trainer_name
             FROM classes c
             JOIN users u ON u.id = c.trainer_id
             WHERE c.is_active = 1 AND (c.name LIKE ? OR c.description LIKE ?)
             ORDER BY c.day_of_week, c.start_time'
        );
        $like = "%{$query}%";
        $stmt->execute([$like, $like]);
        return $stmt->fetchAll();
    }

    /**
     * Расписание на неделю
     */
    public function getWeeklySchedule(): array
    {
        $classes = $this->getAll();
        $schedule = [];
        for ($d = 1; $d <= 7; $d++) {
            $schedule[$d] = [];
        }
        foreach ($classes as $c) {
            $schedule[$c['day_of_week']][] = $c;
        }
        return $schedule;
    }

    public function getCategories(): array
    {
        $stmt = $this->db->query(
            "SELECT DISTINCT category FROM classes WHERE is_active = 1 ORDER BY category"
        );
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Количество записей на занятие
     */
    public function getBookingCount(int $classId, string $date): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM bookings
             WHERE class_id = ? AND booking_date = ? AND status IN ('new','confirmed')"
        );
        $stmt->execute([$classId, $date]);
        return (int) $stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO classes (trainer_id, name, description, category, day_of_week, start_time, duration_min, max_participants, image)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['trainer_id'], $data['name'], $data['description'], $data['category'],
            $data['day_of_week'], $data['start_time'], $data['duration_min'] ?? 60,
            $data['max_participants'] ?? 20, $data['image'] ?? ''
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE classes SET name=?, description=?, category=?, day_of_week=?, start_time=?, duration_min=?, max_participants=?, image=?
             WHERE id = ?'
        );
        return $stmt->execute([
            $data['name'], $data['description'], $data['category'],
            $data['day_of_week'], $data['start_time'], $data['duration_min'],
            $data['max_participants'], $data['image'] ?? '', $id
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE classes SET is_active = 0 WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
