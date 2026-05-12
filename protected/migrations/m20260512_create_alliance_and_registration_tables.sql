-- Migration: Create alliance and registration detail tables
-- Date: 2026-05-12
-- Description: Add alliance (liên quân) feature and detailed registration support

-- ============================================
-- 1. Alliance Requests (Yêu cầu liên quân)
-- ============================================
CREATE TABLE IF NOT EXISTS `alliance_requests` (
  `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id`            INT UNSIGNED NOT NULL,
  `requester_org_id`    INT UNSIGNED NOT NULL COMMENT 'Đơn vị gửi yêu cầu',
  `target_org_id`       INT UNSIGNED NOT NULL COMMENT 'Đơn vị nhận yêu cầu',
  `status`              ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `requested_by`        INT UNSIGNED NOT NULL COMMENT 'unit_accounts.id',
  `requested_at`        INT UNSIGNED,
  `reviewed_by`         INT UNSIGNED COMMENT 'unit_accounts.id hoặc users.id',
  `reviewed_at`         INT UNSIGNED,
  `rejection_reason`    TEXT,
  `note`                TEXT,
  `created_at`          INT UNSIGNED,
  `updated_at`          INT UNSIGNED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_alliance_request` (`event_id`, `requester_org_id`, `target_org_id`),
  KEY `idx_ar_event` (`event_id`),
  KEY `idx_ar_requester` (`requester_org_id`),
  KEY `idx_ar_target` (`target_org_id`),
  KEY `idx_ar_status` (`status`),
  CONSTRAINT `fk_ar_event` FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ar_requester` FOREIGN KEY (`requester_org_id`) REFERENCES `organizations`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ar_target` FOREIGN KEY (`target_org_id`) REFERENCES `organizations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Yêu cầu liên quân giữa các đơn vị';

-- ============================================
-- 2. Alliances (Quan hệ liên quân đã xác nhận)
-- ============================================
CREATE TABLE IF NOT EXISTS `alliances` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id`        INT UNSIGNED NOT NULL,
  `org_a_id`        INT UNSIGNED NOT NULL COMMENT 'Đơn vị A (requester)',
  `org_b_id`        INT UNSIGNED NOT NULL COMMENT 'Đơn vị B (target)',
  `request_id`      INT UNSIGNED COMMENT 'alliance_requests.id gốc',
  `status`          ENUM('active','dissolved') NOT NULL DEFAULT 'active',
  `confirmed_at`    INT UNSIGNED,
  `dissolved_at`    INT UNSIGNED,
  `dissolved_by`    INT UNSIGNED,
  `dissolved_reason` TEXT,
  `note`            TEXT,
  `created_at`      INT UNSIGNED,
  `updated_at`      INT UNSIGNED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_alliance_event_orgs` (`event_id`, `org_a_id`, `org_b_id`),
  KEY `idx_alliance_event` (`event_id`),
  KEY `idx_alliance_org_a` (`org_a_id`),
  KEY `idx_alliance_org_b` (`org_b_id`),
  KEY `idx_alliance_status` (`status`),
  CONSTRAINT `fk_all_event` FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_all_org_a` FOREIGN KEY (`org_a_id`) REFERENCES `organizations`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_all_org_b` FOREIGN KEY (`org_b_id`) REFERENCES `organizations`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_all_request` FOREIGN KEY (`request_id`) REFERENCES `alliance_requests`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Liên quân đã xác nhận - mỗi đơn vị chỉ có 1 liên quân active per event';

-- ============================================
-- 3. Alter registration_details - Add registration_type
-- ============================================
ALTER TABLE `registration_details`
ADD COLUMN `registration_type` ENUM('quantity','detailed') NOT NULL DEFAULT 'quantity'
COMMENT 'quantity=số lượng đội, detailed=danh sách cụ thể'
AFTER `quantity`;

-- ============================================
-- 4. Registration Detail Attendees (Chi tiết người đăng ký)
-- ============================================
CREATE TABLE IF NOT EXISTS `registration_detail_attendees` (
  `id`                      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `registration_detail_id`  INT UNSIGNED NOT NULL,
  `attendee_id`             INT UNSIGNED NOT NULL,
  `status`                  ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  `note`                    TEXT,
  `created_at`              INT UNSIGNED,
  `updated_at`              INT UNSIGNED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_rd_attendee` (`registration_detail_id`, `attendee_id`),
  KEY `idx_rda_detail` (`registration_detail_id`),
  KEY `idx_rda_attendee` (`attendee_id`),
  KEY `idx_rda_status` (`status`),
  CONSTRAINT `fk_rda_detail` FOREIGN KEY (`registration_detail_id`) REFERENCES `registration_details`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rda_attendee` FOREIGN KEY (`attendee_id`) REFERENCES `attendees`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Chi tiết người tham gia khi đăng ký detailed';

-- ============================================
-- 5. Add index for checking active alliance per org
-- ============================================
CREATE INDEX `idx_alliance_org_a_active` ON `alliances` (`org_a_id`, `event_id`, `status`);
CREATE INDEX `idx_alliance_org_b_active` ON `alliances` (`org_b_id`, `event_id`, `status`);
