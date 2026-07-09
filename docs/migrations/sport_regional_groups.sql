-- Migration: Sport Regional Groups
-- Description: Thêm bảng gộp cụm và chỉ tiêu cho các môn thể thao
-- Date: 2026-07-09

-- --------------------------------------------------------
-- 1. Bảng nhóm cụm (gộp nhiều regional thành 1 nhóm thi đấu)
-- --------------------------------------------------------
CREATE TABLE `sport_regional_groups` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_sport_id` BIGINT UNSIGNED NOT NULL COMMENT 'FK → event_sports',
    `group_code` VARCHAR(20) NOT NULL COMMENT 'Mã nhóm: A, B, C...',
    `group_name` VARCHAR(100) DEFAULT NULL COMMENT 'Tên hiển thị: Cụm 1+2+3',
    `total_participants` INT UNSIGNED DEFAULT 0 COMMENT 'Tổng đội/VĐV trong nhóm',
    `quota_value` DECIMAL(3,1) NOT NULL COMMENT 'Chỉ tiêu: 2.5, 1.5, 1.0...',
    `qualification_method` ENUM('elimination','time_based','direct') NOT NULL DEFAULT 'elimination' COMMENT 'elimination=đấu loại, time_based=bơi/chạy, direct=đi thẳng VCK',
    `top_n` INT UNSIGNED DEFAULT NULL COMMENT 'Số lượng lấy (cho time_based)',
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_event_sport_group` (`event_sport_id`, `group_code`),
    KEY `idx_srg_event_sport` (`event_sport_id`),
    CONSTRAINT `fk_srg_event_sport` FOREIGN KEY (`event_sport_id`)
        REFERENCES `event_sports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Nhóm cụm (gộp regional) cho từng môn thể thao';

-- --------------------------------------------------------
-- 2. Bảng thành viên nhóm (regional nào thuộc nhóm nào)
-- --------------------------------------------------------
CREATE TABLE `sport_regional_group_members` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `group_id` BIGINT UNSIGNED NOT NULL COMMENT 'FK → sport_regional_groups',
    `regional_id` BIGINT UNSIGNED NOT NULL COMMENT 'FK → regionals',
    `created_at` INT UNSIGNED DEFAULT NULL COMMENT 'Unix timestamp',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_group_regional` (`group_id`, `regional_id`),
    KEY `idx_srgm_regional` (`regional_id`),
    CONSTRAINT `fk_srgm_group` FOREIGN KEY (`group_id`)
        REFERENCES `sport_regional_groups` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_srgm_regional` FOREIGN KEY (`regional_id`)
        REFERENCES `regionals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Cụm (regional) thuộc nhóm nào';

-- --------------------------------------------------------
-- 3. Thêm cột vào sport_stage_teams để lưu kết quả vòng loại
-- --------------------------------------------------------
ALTER TABLE `sport_stage_teams`
    ADD COLUMN `time_result` INT UNSIGNED DEFAULT NULL COMMENT 'Thời gian (ms) - cho bơi/chạy' AFTER `final_rank`,
    ADD COLUMN `is_playoff` TINYINT(1) DEFAULT 0 COMMENT 'Cần đấu playoff (suất 0.5)' AFTER `time_result`,
    ADD COLUMN `regional_group_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'FK → sport_regional_groups' AFTER `is_playoff`,
    ADD KEY `idx_sst_regional_group` (`regional_group_id`);
