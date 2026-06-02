-- ============================================================
-- Система управления фитнес-клубом
-- Схема базы данных
-- ============================================================

CREATE DATABASE IF NOT EXISTS fitness_club
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE fitness_club;

-- -----------------------------------------------------------
-- Таблица пользователей (члены клуба, тренеры, администраторы, менеджеры)
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `email`         VARCHAR(255)    NOT NULL UNIQUE,
  `password_hash` VARCHAR(255)    NOT NULL,
  `first_name`    VARCHAR(100)    NOT NULL,
  `last_name`     VARCHAR(100)    NOT NULL,
  `phone`         VARCHAR(20)     DEFAULT NULL,
  `role`          ENUM('member','trainer','admin','manager') NOT NULL DEFAULT 'member',
  `avatar`        VARCHAR(255)    DEFAULT NULL,
  `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- Таблица клубных карт / абонементов
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `memberships`;
CREATE TABLE `memberships` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`       INT             NOT NULL,
  `type`          ENUM('basic','standard','premium','unlimited') NOT NULL DEFAULT 'basic',
  `start_date`    DATE            NOT NULL,
  `end_date`      DATE            NOT NULL,
  `visits_total`  INT             NOT NULL DEFAULT 0    COMMENT '0 = безлимит',
  `visits_used`   INT             NOT NULL DEFAULT 0,
  `price`         DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
  `status`        ENUM('active','expired','suspended','cancelled') NOT NULL DEFAULT 'active',
  `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- Таблица посещений
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `visits`;
CREATE TABLE `visits` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`       INT             NOT NULL,
  `visit_date`    DATE            NOT NULL,
  `visit_time`    TIME            NOT NULL,
  `visit_type`    ENUM('individual','group','personal_training') NOT NULL DEFAULT 'individual',
  `notes`         TEXT            DEFAULT NULL,
  `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- Таблица групповых занятий
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `classes`;
CREATE TABLE `classes` (
  `id`                INT AUTO_INCREMENT PRIMARY KEY,
  `trainer_id`        INT             NOT NULL,
  `name`              VARCHAR(200)    NOT NULL,
  `description`       TEXT            DEFAULT NULL,
  `category`          ENUM('yoga','pilates','cardio','strength','dance','crossfit','boxing','stretching') NOT NULL,
  `day_of_week`       TINYINT         NOT NULL COMMENT '1=Пн ... 7=Вс',
  `start_time`        TIME            NOT NULL,
  `duration_min`      INT             NOT NULL DEFAULT 60,
  `max_participants`  INT             NOT NULL DEFAULT 20,
  `image`             VARCHAR(255)    DEFAULT NULL,
  `is_active`         TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`trainer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- Таблица записей на групповые занятия
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `bookings`;
CREATE TABLE `bookings` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`       INT             NOT NULL,
  `class_id`      INT             NOT NULL,
  `booking_date`  DATE            NOT NULL,
  `status`        ENUM('new','confirmed','cancelled','completed','no_show') NOT NULL DEFAULT 'new',
  `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`)  REFERENCES `users`(`id`)   ON DELETE CASCADE,
  FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uniq_booking` (`user_id`, `class_id`, `booking_date`)
) ENGINE=InnoDB;

-- -----------------------------------------------------------
-- Индексы
-- -----------------------------------------------------------
CREATE INDEX idx_users_role       ON `users`(`role`);
CREATE INDEX idx_memberships_user ON `memberships`(`user_id`);
CREATE INDEX idx_visits_user_date ON `visits`(`user_id`, `visit_date`);
CREATE INDEX idx_classes_day      ON `classes`(`day_of_week`, `start_time`);
CREATE INDEX idx_bookings_status  ON `bookings`(`status`);
