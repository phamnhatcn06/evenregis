-- =====================================================================
-- Bảng: attendee_replacements
-- Mục đích: Lưu lịch sử THAY THẾ / HUỶ TƯ CÁCH người tham dự trên màn hình
--           phê duyệt đăng ký (admin/approveRegistrations).
-- Dùng để: audit + render mục "Thay đổi nhân sự" trong email xác nhận đơn vị.
-- Convention: InnoDB, utf8mb4, snake_case, thời gian = INT UNSIGNED (unix timestamp).
-- =====================================================================

CREATE TABLE `attendee_replacements` (
    `id`                 BIGINT UNSIGNED   NOT NULL AUTO_INCREMENT,

    -- Ngữ cảnh
    `registration_id`    BIGINT UNSIGNED   NOT NULL COMMENT 'Phiếu đăng ký bị tác động',
    `event_id`           BIGINT UNSIGNED   NULL     COMMENT 'Sự kiện',
    `property_id`        BIGINT UNSIGNED   NULL     COMMENT 'Đơn vị',

    -- Loại thao tác: 'replace' = thay thế, 'withdraw' = huỷ tư cách không thay
    `action`             VARCHAR(20)       NOT NULL COMMENT 'replace | withdraw',

    -- Người bị thay / bị huỷ
    `old_attendee_id`    BIGINT UNSIGNED   NULL     COMMENT 'Người bị thay/huỷ',
    `old_attendee_name`  VARCHAR(255)      NULL     COMMENT 'Tên tại thời điểm thao tác (snapshot)',
    `old_staff_code`     VARCHAR(50)       NULL     COMMENT 'Mã NV người bị thay/huỷ (snapshot)',

    -- Người thay thế (NULL nếu action = withdraw)
    `new_attendee_id`    BIGINT UNSIGNED   NULL     COMMENT 'Người thay thế',
    `new_attendee_name`  VARCHAR(255)      NULL     COMMENT 'Tên người thay (snapshot)',
    `new_staff_code`     VARCHAR(50)       NULL     COMMENT 'Mã NV người thay (snapshot)',

    -- Chi tiết ảnh hưởng (JSON snapshot để hiển thị lại trong email)
    -- affected_contents ví dụ:
    --   { "sports":      [{"team_id":12,"sport_name":"Bóng đá nam","team_name":"KS A","jersey_number":9,"is_captain":1}],
    --     "competitions":[{"competition_id":5,"competition_name":"Lễ tân","candidate_number":"NV012"}],
    --     "roles":       [{"role_id":3,"role_name":"Vận động viên"}] }
    `affected_contents`  JSON              NULL     COMMENT 'Nội dung thể thao/nghiệp vụ/vai trò bị ảnh hưởng',

    -- cancelled_teams ví dụ: [{"team_id":12,"sport_name":"Bóng đá nam","team_name":"KS A"}]
    `cancelled_teams`    JSON              NULL     COMMENT 'Các đội bị huỷ do thao tác này',

    -- Metadata
    `reason`             TEXT              NULL     COMMENT 'Lý do thay/huỷ',
    `performed_by`       VARCHAR(255)      NULL     COMMENT 'Email người thực hiện (từ SSO)',

    `created_at`         INT UNSIGNED      NULL     COMMENT 'Unix timestamp tạo',
    `updated_at`         INT UNSIGNED      NULL     COMMENT 'Unix timestamp cập nhật',
    `deleted_at`         INT UNSIGNED      NULL     COMMENT 'Soft delete (unix timestamp)',

    PRIMARY KEY (`id`),
    KEY `idx_attendee_replacements_registration_id` (`registration_id`),
    KEY `idx_attendee_replacements_event_id` (`event_id`),
    KEY `idx_attendee_replacements_property_id` (`property_id`),
    KEY `idx_attendee_replacements_action` (`action`),
    KEY `idx_attendee_replacements_old_attendee_id` (`old_attendee_id`),
    KEY `idx_attendee_replacements_new_attendee_id` (`new_attendee_id`),
    KEY `idx_attendee_replacements_created_at` (`created_at`),

    CONSTRAINT `fk_attendee_replacements_registrations`
        FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Lịch sử thay thế / huỷ tư cách người tham dự';

-- Ghi chú:
-- 1) Không đặt FK cho old_attendee_id / new_attendee_id để tránh chặn khi attendee bị
--    soft delete (huỷ tư cách). Đã lưu snapshot tên + mã NV để email hiển thị đúng dù bản
--    ghi attendee gốc đã bị đổi/ẩn.
-- 2) affected_contents & cancelled_teams dùng kiểu JSON (MySQL 5.7+). Nếu bản MySQL cũ hơn,
--    thay bằng LONGTEXT và lưu chuỗi JSON.
-- 3) API cần cung cấp:
--    - POST /api/attendee-replacements/store              (tạo bản ghi)
--    - GET  /api/attendee-replacements?registration_id=X  (list theo phiếu, loại deleted_at)
