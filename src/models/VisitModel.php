<?php
/**
 * Модель для работы с таблицей visits (посещения)
 */
class VisitModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByUserId(int $userId, int $limit = 30): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM visits WHERE user_id = ? ORDER BY visit_date DESC, visit_time DESC LIMIT ?'
        );
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO visits (user_id, visit_date, visit_time, visit_type, notes) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['user_id'], $data['visit_date'], $data['visit_time'],
            $data['visit_type'] ?? 'individual', $data['notes'] ?? ''
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function countByUser(int $userId, ?string $fromDate = null): int
    {
        $sql = 'SELECT COUNT(*) FROM visits WHERE user_id = ?';
        $params = [$userId];
        if ($fromDate) {
            $sql .= ' AND visit_date >= ?';
            $params[] = $fromDate;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Общая статистика посещений
     */
    public function getStats(): array
    {
        $stmt = $this->db->query(
            "SELECT
                COUNT(*) AS total,
                COUNT(DISTINCT user_id) AS unique_visitors,
                SUM(CASE WHEN visit_type = 'group' THEN 1 ELSE 0 END) AS group_visits,
                SUM(CASE WHEN visit_type = 'individual' THEN 1 ELSE 0 END) AS individual_visits,
                SUM(CASE WHEN visit_type = 'personal_training' THEN 1 ELSE 0 END) AS personal_visits
             FROM visits"
        );
        return $stmt->fetch();
    }

    /**
     * Посещения по дням за последние N дней
     */
    public function getDailyStats(int $days = 7): array
    {
        $stmt = $this->db->prepare(
            'SELECT visit_date, COUNT(*) AS cnt
             FROM visits
             WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             GROUP BY visit_date
             ORDER BY visit_date'
        );
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }
}
