<?php
/**
 * Модель для работы с таблицей memberships
 */
class MembershipModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM memberships WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getByUserId(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM memberships WHERE user_id = ? ORDER BY created_at DESC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getActiveByUserId(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM memberships
             WHERE user_id = ? AND status = 'active' AND end_date >= CURDATE()
             ORDER BY end_date DESC LIMIT 1"
        );
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT m.*, CONCAT(u.first_name, \' \', u.last_name) AS client_name, u.email
             FROM memberships m
             JOIN users u ON u.id = m.user_id
             ORDER BY m.status, m.end_date DESC'
        );
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO memberships (user_id, type, start_date, end_date, visits_total, visits_used, price, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['user_id'], $data['type'], $data['start_date'], $data['end_date'],
            $data['visits_total'], $data['visits_used'] ?? 0,
            $data['price'], $data['status'] ?? 'active'
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function incrementVisit(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE memberships SET visits_used = visits_used + 1 WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE memberships SET status = ? WHERE id = ?');
        return $stmt->execute([$status, $id]);
    }

    /**
     * Обновление просроченных карт
     */
    public function expireOutdated(): int
    {
        $stmt = $this->db->prepare(
            "UPDATE memberships SET status = 'expired'
             WHERE status = 'active' AND end_date < CURDATE()"
        );
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Статистика по картам
     */
    public function getStats(): array
    {
        $stmt = $this->db->query(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active,
                SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) AS expired,
                SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) AS suspended,
                ROUND(AVG(price), 2) AS avg_price
             FROM memberships"
        );
        return $stmt->fetch();
    }

    /**
     * Retention rate: клиенты, продлившие абонемент / всего с истёкшим × 100
     */
    public function getRetentionRate(): float
    {
        $stmt = $this->db->query(
            "SELECT
                COUNT(DISTINCT m1.user_id) AS total_expired,
                COUNT(DISTINCT m2.user_id) AS renewed
             FROM memberships m1
             LEFT JOIN memberships m2
                ON m2.user_id = m1.user_id
                AND m2.start_date > m1.end_date
                AND m2.status IN ('active','expired')
             WHERE m1.status = 'expired'"
        );
        $row = $stmt->fetch();
        if ($row && $row['total_expired'] > 0) {
            return round(($row['renewed'] / $row['total_expired']) * 100, 1);
        }
        return 0.0;
    }
}
