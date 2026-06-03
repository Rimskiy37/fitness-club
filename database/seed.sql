-- ============================================================
-- Начальные данные (seed) для Системы управления фитнес-клубом
-- ============================================================

USE fitness_club;

-- Пароль для всех тестовых пользователей: password123
-- Хеш: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi

-- -----------------------------------------------------------
-- Пользователи
-- -----------------------------------------------------------
INSERT INTO `users` (`email`,`password_hash`,`first_name`,`last_name`,`phone`,`role`,`avatar`) VALUES
-- Администратор
('admin@fitnessclub.ru','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Иван','Петров','+7(900)100-00-01','admin','avatar_admin.png'),
-- Менеджер
('manager@fitnessclub.ru','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Елена','Сидорова','+7(900)100-00-02','manager','avatar_manager.png'),
-- Тренеры
('trainer1@fitnessclub.ru','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Алексей','Козлов','+7(900)200-00-01','trainer','avatar_trainer1.png'),
('trainer2@fitnessclub.ru','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Мария','Новикова','+7(900)200-00-02','trainer','avatar_trainer2.png'),
('trainer3@fitnessclub.ru','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Дмитрий','Волков','+7(900)200-00-03','trainer','avatar_trainer3.png'),
('trainer4@fitnessclub.ru','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Ольга','Морозова','+7(900)200-00-04','trainer','avatar_trainer4.png'),
-- Члены клуба
('member1@mail.ru','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Анна','Иванова','+7(900)300-00-01','member','avatar_member1.png'),
('member2@mail.ru','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Сергей','Кузнецов','+7(900)300-00-02','member','avatar_member2.png'),
('member3@mail.ru','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Наталья','Попова','+7(900)300-00-03','member','avatar_member3.png'),
('member4@mail.ru','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Павел','Лебедев','+7(900)300-00-04','member','avatar_member4.png'),
('member5@mail.ru','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Татьяна','Соколова','+7(900)300-00-05','member','avatar_member5.png');

-- -----------------------------------------------------------
-- Клубные карты / абонементы
-- -----------------------------------------------------------
INSERT INTO `memberships` (`user_id`,`type`,`start_date`,`end_date`,`visits_total`,`visits_used`,`price`,`status`) VALUES
(7, 'premium',     '2026-01-01','2026-12-31', 0,  0, 35000.00, 'active'),
(8, 'standard',    '2026-01-01','2026-06-30', 24, 10, 18000.00, 'active'),
(9, 'basic',       '2026-02-01','2026-05-01', 8,  7,  8000.00, 'active'),
(10,'unlimited',   '2026-01-15','2026-07-15', 0,  0, 42000.00, 'active'),
(11,'standard',    '2025-07-01','2025-12-31', 24, 22, 18000.00, 'expired');

-- -----------------------------------------------------------
-- Посещения
-- -----------------------------------------------------------
INSERT INTO `visits` (`user_id`,`visit_date`,`visit_time`,`visit_type`) VALUES
(7, '2026-05-20','09:00:00','group'),
(7, '2026-05-22','10:00:00','individual'),
(7, '2026-05-25','18:00:00','personal_training'),
(8, '2026-05-20','09:00:00','group'),
(8, '2026-05-23','11:00:00','individual'),
(8, '2026-05-24','19:00:00','group'),
(9, '2026-05-21','08:00:00','individual'),
(9, '2026-05-28','08:00:00','individual'),
(10,'2026-05-20','07:00:00','individual'),
(10,'2026-05-21','09:00:00','group'),
(10,'2026-05-22','07:00:00','individual'),
(10,'2026-05-23','18:00:00','personal_training'),
(10,'2026-05-24','09:00:00','group'),
(10,'2026-05-25','07:00:00','individual');

-- -----------------------------------------------------------
-- Групповые занятия
-- -----------------------------------------------------------
INSERT INTO `classes` (`trainer_id`,`name`,`description`,`category`,`day_of_week`,`start_time`,`duration_min`,`max_participants`,`image`) VALUES
(3,'Йога для начинающих','Мягкая практика для расслабления и гибкости. Подходит для всех уровней.','yoga',1,'09:00:00',60,15,'class_yoga.png'),
(4,'Пилатес','Укрепление мышц кора, улучшение осанки и координации.','pilates',1,'11:00:00',50,12,'class_pilates.png'),
(6,'Стретчинг','Глубокая растяжка всего тела для восстановления после нагрузок.','stretching',1,'18:00:00',45,20,'class_stretching.png'),
(5,'CrossFit WOD','Высокоинтенсивная тренировка с функциональными движениями.','crossfit',2,'10:00:00',60,10,'class_crossfit.png'),
(3,'Йога средний уровень','Продолжение практики для тех, кто освоил базовые асаны.','yoga',2,'19:00:00',75,15,'class_yoga.png'),
(6,'Танцевальный фитнес','Энергичные танцы под музыку — кардио + настроение.','dance',3,'10:00:00',60,25,'class_dance.png'),
(5,'Боксёрская тренировка','Основы бокса: стойка, удары, работа на мешках.','boxing',3,'18:00:00',60,8,'class_boxing.png'),
(4,'Кардо-микс','Интервальная кардиотренировка: бег, прыжки, велотренажёр.','cardio',4,'09:00:00',45,20,'class_cardio.png'),
(5,'Силовая тренировка','Работа со свободными весами, тренажёрами и собственным весом.','strength',4,'17:00:00',60,15,'class_strength.png'),
(3,'Утренняя йога','Бодрящая утренняя практика для пробуждения тела.','yoga',5,'08:00:00',50,15,'class_yoga.png'),
(6,'Пилатес продвинутый','Интенсивная работа на баланс и стабилизацию.','pilates',5,'19:00:00',55,12,'class_pilates.png'),
(4,'HIIT','Высокоинтенсивная интервальная тренировка — максимум за минимум времени.','cardio',6,'10:00:00',30,20,'class_cardio.png');

-- -----------------------------------------------------------
-- Записи на занятия
-- -----------------------------------------------------------
INSERT INTO `bookings` (`user_id`,`class_id`,`booking_date`,`status`) VALUES
(7,1,'2026-06-09','confirmed'),
(7,5,'2026-06-09','confirmed'),
(8,2,'2026-06-09','confirmed'),
(8,6,'2026-06-10','new'),
(9,3,'2026-06-09','new'),
(10,1,'2026-06-09','confirmed'),
(10,4,'2026-06-09','confirmed'),
(10,7,'2026-06-10','new');
