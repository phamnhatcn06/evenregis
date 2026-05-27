-- =====================================================
-- Migration: Approval Workflow Multi-Level
-- Date: 2026-05-27
-- Description: Quy trình duyệt nhiều cấp theo index
-- =====================================================

-- 1. Workflow template
CREATE TABLE IF NOT EXISTS `approval_workflows` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(50) NOT NULL COMMENT 'Mã workflow (unique)',
    `name` VARCHAR(255) NOT NULL COMMENT 'Tên workflow',
    `description` TEXT NULL COMMENT 'Mô tả',
    `total_steps` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Tổng số bước duyệt',
    `is_default` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Workflow mặc định',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` INT UNSIGNED NULL,
    `updated_at` INT UNSIGNED NULL,
    `created_by` INT UNSIGNED NULL COMMENT 'Portal user ID',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_approval_workflows_code` (`code`),
    KEY `idx_approval_workflows_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Template quy trình duyệt';

-- 2. Người duyệt theo index
CREATE TABLE IF NOT EXISTS `approval_workflow_approvers` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `workflow_id` INT UNSIGNED NOT NULL,
    `step_index` TINYINT UNSIGNED NOT NULL COMMENT 'Thứ tự bước (1, 2, 3...)',
    `step_name` VARCHAR(255) NOT NULL COMMENT 'Tên bước (GĐ đơn vị, NS TĐ...)',
    `portal_user_id` INT UNSIGNED NOT NULL COMMENT 'User ID từ Portal SSO',
    `portal_user_name` VARCHAR(255) NULL COMMENT 'Tên hiển thị (cache)',
    `portal_user_email` VARCHAR(255) NULL COMMENT 'Email (cache)',
    `organization_id` INT UNSIGNED NULL COMMENT 'NULL = tất cả, có giá trị = chỉ đơn vị này',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` INT UNSIGNED NULL,
    `updated_at` INT UNSIGNED NULL,
    PRIMARY KEY (`id`),
    KEY `idx_approvers_workflow_step` (`workflow_id`, `step_index`),
    KEY `idx_approvers_portal_user` (`portal_user_id`),
    KEY `idx_approvers_org` (`organization_id`),
    CONSTRAINT `fk_approvers_workflow` FOREIGN KEY (`workflow_id`)
        REFERENCES `approval_workflows` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_approvers_organization` FOREIGN KEY (`organization_id`)
        REFERENCES `organizations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Phân quyền người duyệt theo index';

-- 3. Tracking trạng thái duyệt của từng đơn
CREATE TABLE IF NOT EXISTS `registration_approvals` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `registration_id` INT UNSIGNED NOT NULL,
    `workflow_id` INT UNSIGNED NOT NULL,
    `current_index` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Đang chờ duyệt ở bước nào',
    `total_steps` TINYINT UNSIGNED NOT NULL COMMENT 'Tổng số bước (snapshot từ workflow)',
    `status` ENUM('pending', 'approved', 'rejected', 'revision') NOT NULL DEFAULT 'pending',
    `started_at` INT UNSIGNED NULL COMMENT 'Thời điểm bắt đầu quy trình',
    `completed_at` INT UNSIGNED NULL COMMENT 'Thời điểm hoàn tất',
    `created_at` INT UNSIGNED NULL,
    `updated_at` INT UNSIGNED NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_registration_approvals_reg` (`registration_id`),
    KEY `idx_reg_approvals_status` (`status`),
    KEY `idx_reg_approvals_current` (`current_index`, `status`),
    CONSTRAINT `fk_reg_approvals_registration` FOREIGN KEY (`registration_id`)
        REFERENCES `registrations` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_reg_approvals_workflow` FOREIGN KEY (`workflow_id`)
        REFERENCES `approval_workflows` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tracking quy trình duyệt của từng đơn';

-- 4. Lịch sử duyệt từng bước
CREATE TABLE IF NOT EXISTS `registration_approval_logs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `registration_id` INT UNSIGNED NOT NULL,
    `step_index` TINYINT UNSIGNED NOT NULL COMMENT 'Bước được duyệt',
    `step_name` VARCHAR(255) NULL COMMENT 'Tên bước (snapshot)',
    `action` ENUM('approved', 'rejected', 'revision', 'submitted', 'resubmitted') NOT NULL,
    `approver_portal_id` INT UNSIGNED NULL COMMENT 'Portal user ID người duyệt',
    `approver_name` VARCHAR(255) NULL COMMENT 'Tên người duyệt (cache)',
    `comment` TEXT NULL COMMENT 'Ghi chú/Lý do',
    `acted_at` INT UNSIGNED NOT NULL COMMENT 'Thời điểm thực hiện',
    `created_at` INT UNSIGNED NULL,
    PRIMARY KEY (`id`),
    KEY `idx_approval_logs_registration` (`registration_id`),
    KEY `idx_approval_logs_step` (`registration_id`, `step_index`),
    KEY `idx_approval_logs_approver` (`approver_portal_id`),
    KEY `idx_approval_logs_acted` (`acted_at`),
    CONSTRAINT `fk_approval_logs_registration` FOREIGN KEY (`registration_id`)
        REFERENCES `registrations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lịch sử duyệt từng bước';

-- =====================================================
-- Sample data (optional - xóa khi deploy production)
-- =====================================================

-- INSERT INTO `approval_workflows` (`code`, `name`, `total_steps`, `is_default`, `is_active`, `created_at`) VALUES
-- ('STANDARD', 'Quy trình duyệt chuẩn', 3, 1, 1, UNIX_TIMESTAMP());
