-- ============================================================
-- Аналитические SQL-запросы для Системы управления фитнес-клубом
-- ============================================================

USE fitness_club;

-- -----------------------------------------------------------
-- Запрос 1: Выборка с объединением таблиц (JOIN)
-- Список записей на занятия с данными клиента и тренера
-- -----------------------------------------------------------
SELECT
    b.id                    AS booking_id,
    CONCAT(u.first_name, ' ', u.last_name) AS client_name,
    c.name                  AS class_name,
    c.category,
    CONCAT(t.first_name, ' ', t.last_name) AS trainer_name,
    b.booking_date,
    b.status                AS booking_status
FROM bookings b
JOIN users   u ON u.id = b.user_id
JOIN classes c ON c.id = b.class_id
JOIN users   t ON t.id = c.trainer_id
ORDER BY b.booking_date DESC, b.id;

-- -----------------------------------------------------------
-- Запрос 2: Агрегация с группировкой (GROUP BY)
-- Количество записей и средняя загрузка по каждой категории занятий
-- -----------------------------------------------------------
SELECT
    c.category                            AS category,
    COUNT(b.id)                           AS total_bookings,
    COUNT(DISTINCT c.id)                  AS class_count,
    ROUND(AVG(c.max_participants), 1)     AS avg_max_participants,
    ROUND(COUNT(b.id) * 1.0 / COUNT(DISTINCT c.id), 1) AS avg_bookings_per_class
FROM classes c
LEFT JOIN bookings b ON b.class_id = c.id
GROUP BY c.category
ORDER BY total_bookings DESC;

-- -----------------------------------------------------------
-- Запрос 3: Фильтрация групп (HAVING)
-- Категории, на которые записано более 2 уникальных клиентов
-- -----------------------------------------------------------
SELECT
    c.category,
    COUNT(DISTINCT b.user_id) AS unique_clients,
    COUNT(b.id)               AS total_bookings
FROM bookings b
JOIN classes c ON c.id = b.class_id
GROUP BY c.category
HAVING COUNT(DISTINCT b.user_id) > 2;

-- -----------------------------------------------------------
-- Запрос 4: LEFT JOIN — клиенты, у которых нет ни одной записи
-- (работа с отсутствующими связями)
-- -----------------------------------------------------------
SELECT
    u.id,
    CONCAT(u.first_name, ' ', u.last_name) AS client_name,
    u.email,
    u.phone
FROM users u
LEFT JOIN bookings b ON b.user_id = u.id
WHERE u.role = 'member' AND b.id IS NULL;
