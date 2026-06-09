<?php
/**
 * Модель для работы с таблицей bookings (записи на занятия)
 */
class BookingModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT b.*, c.name AS class_name, c.category,
                    CONCAT(u.first_name, \' \', u.last_name) AS client_name
             FROM bookings b
             JOIN classes c ON c.id = b.class_id
             JOIN users u ON u.id = b.user_id
             WHERE b.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getByUserId(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT b.*, c.name AS class_name, c.category, c.start_time, c.duration_min, c.day_of_week,
                    CONCAT(t.first_name, \' \', t.last_name) AS trainer_name
             FROM bookings b
             JOIN classes c ON c.id = b.class_id
             JOIN users t ON t.id = c.trainer_id
             WHERE b.user_id = ?
             ORDER BY b.booking_date DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getByClassId(int $classId, ?string $date = null): array
    {
        $sql = 'SELECT b.*, CONCAT(u.first_name, \' \', u.last_name) AS client_name, u.email, u.phone
                FROM bookings b
                JOIN users u ON u.id = b.user_id
                WHERE b.class_id = ?';
        $params = [$classId];
        if ($date) {
            $sql .= ' AND b.booking_date = ?';
            $params[] = $date;
        }
        $sql .= ' ORDER BY b.status, b.created_at';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT b.*, c.name AS class_name, c.category,
                    CONCAT(u.first_name, \' \', u.last_name) AS client_name
             FROM bookings b
             JOIN classes c ON c.id = b.class_id
             JOIN users u ON u.id = b.user_id
             ORDER BY b.booking_date DESC, b.status'
        );
        return $stmt->fetchAll();
    }

    /**
     * Создать запись на занятие
     */
    public function create(int $userId, int $classId, string $date): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO bookings (user_id, class_id, booking_date, status) VALUES (?, ?, ?, \'new\')'
        );
        $stmt->execute([$userId, $classId, $date]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Обновить статус записи (AJAX)
     */
    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE bookings SET status = ? WHERE id = ?');
        return $stmt->execute([$status, $id]);
    }

    /**
     * Отменить запись
     */
    public function cancel(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE bookings SET status = 'cancelled'
             WHERE id = ? AND user_id = ? AND status IN ('new','confirmed')"
        );
        $stmt->execute([$id, $userId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Проверить, записан ли уже пользователь
     */
    public function isBooked(int $userId, int $classId, string $date): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM bookings
             WHERE user_id = ? AND class_id = ? AND booking_date = ? AND status IN ('new','confirmed')"
        );
        $stmt->execute([$userId, $classId, $date]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Статистика записей
     */
    public function getStats(): array
    {
        $stmt = $this->db->query(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) AS new_count,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed,
                SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) AS no_show
             FROM bookings"
        );
        return $stmt->fetch();
    }
}
