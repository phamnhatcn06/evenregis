-- Event Registration System Database Schema
-- Compatible with MySQL 5.7+ / MariaDB 10+

CREATE DATABASE IF NOT EXISTS `eventregis` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `eventregis`;

-- Users table
CREATE TABLE IF NOT EXISTS `tbl_user` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_user_username` (`username`),
    UNIQUE KEY `uniq_user_email` (`email`),
    KEY `idx_user_status` (`status`),
    KEY `idx_user_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Session table (for CDbHttpSession)
CREATE TABLE IF NOT EXISTS `tbl_session` (
    `id` CHAR(32) NOT NULL,
    `expire` INT(11) NOT NULL,
    `data` LONGBLOB,
    PRIMARY KEY (`id`),
    KEY `idx_session_expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tbl_admin_session` (
    `id` CHAR(32) NOT NULL,
    `expire` INT(11) NOT NULL,
    `data` LONGBLOB,
    PRIMARY KEY (`id`),
    KEY `idx_session_expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Events table
CREATE TABLE IF NOT EXISTS `tbl_event` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `location` VARCHAR(255),
    `start_date` DATETIME NOT NULL,
    `end_date` DATETIME NOT NULL,
    `max_participants` INT(11) DEFAULT NULL,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `created_by` INT(11) UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_event_status` (`status`),
    KEY `idx_event_start_date` (`start_date`),
    KEY `fk_event_user` (`created_by`),
    CONSTRAINT `fk_event_user` FOREIGN KEY (`created_by`) REFERENCES `tbl_user`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Event registrations table
CREATE TABLE IF NOT EXISTS `tbl_registration` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_id` INT(11) UNSIGNED NOT NULL,
    `user_id` INT(11) UNSIGNED NOT NULL,
    `status` ENUM('pending', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending',
    `notes` TEXT,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_registration` (`event_id`, `user_id`),
    KEY `idx_registration_status` (`status`),
    KEY `fk_registration_user` (`user_id`),
    CONSTRAINT `fk_registration_event` FOREIGN KEY (`event_id`) REFERENCES `tbl_event`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_registration_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_user`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123)
INSERT INTO `tbl_user` (`username`, `email`, `password`, `role`, `status`, `created_at`, `updated_at`)
VALUES ('admin', 'admin@example.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/X4vjUcR.YIwYKY.Wm', 'admin', 1, NOW(), NOW());
