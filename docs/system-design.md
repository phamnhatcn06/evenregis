# Tài Liệu Phân Tích Thiết Kế Hệ Thống — Quản Lý Sự Kiện Đại Hội

---

## 1. Tổng Quan Hệ Thống

### 1.1 Mục đích & Phạm vi

Hệ thống quản lý toàn bộ vòng đời của một sự kiện đại hội tập trung ~600 người tham dự từ nhiều đơn vị khác nhau, bao gồm:

- Quản lý đăng ký tham dự theo đơn vị trong khung thời gian quy định
- Phê duyệt danh sách tập trung từ HO (Head Office)
- Cấp và in thẻ tham dự có QR Code
- Quản lý thi nghiệp vụ, thi đấu thể thao, tiệc và bữa ăn
- Cung cấp thông tin lịch trình qua quét QR

### 1.2 Constraints & Assumptions

| Ràng buộc                  | Chi tiết                                                    |
| -------------------------- | ----------------------------------------------------------- |
| Tech stack                 | **Yii1 PHP Framework** (PHP 5.6 — 7.4)                      |
| Quy mô                     | ~600 người tham dự, ~50–100 đơn vị                          |
| Thời gian sử dụng cao điểm | Ngày đăng ký, ngày in thẻ, ngày diễn ra sự kiện             |
| QR Code                    | Được quét bằng bất kỳ thiết bị nào (không cần app riêng)    |
| In thẻ                     | Xuất ảnh PNG/JPG theo kích thước chuẩn (85×54mm — thẻ CR80) |
| Đơn vị                     | Mỗi đơn vị có đúng 1 tài khoản đăng ký                      |

---

## 2. Actors & Use Cases

### 2.1 Actors

| Actor                      | Tài khoản                                                             | Quyền hạn                            |
| -------------------------- | --------------------------------------------------------------------- | ------------------------------------ |
| **Admin HO**               | `users` (role=admin)                                                  | Toàn quyền hệ thống                  |
| **Nhân sự HO (HR)**        | `users` (role=hr)                                                     | Phê duyệt danh sách, quản lý đăng ký |
| **Đại diện đơn vị**        | `unit_accounts`                                                       | Đăng ký danh sách đơn vị, upload ảnh |
| **Trưởng đoàn**            | `users` (role=team_lead) hoặc `attendees` có flag `is_team_lead=true` | Báo cắt ăn cho đoàn                  |
| **BTC Thi nghiệp vụ**      | `users` (role=competition_organizer)                                  | Quản lý thi NV, cấp số báo danh      |
| **BTC Thể thao**           | `users` (role=sports_organizer)                                       | Quản lý lịch đấu, kết quả            |
| **BTC Tiệc**               | `users` (role=banquet_organizer)                                      | Quản lý sơ đồ bàn, phân chỗ          |
| **Người tham dự (Public)** | Không cần tài khoản                                                   | Quét QR xem thông tin                |

### 2.2 Use Case Map

```
[Đại diện đơn vị]
  ├── UC01: Đăng nhập tài khoản đơn vị
  ├── UC02: Tạo bản đăng ký tham dự (đăng ký các môn/nội dung, chưa cần điền danh sách chi tiết)
  ├── UC03: Nhập danh sách người tham dự (tên, chức danh, ảnh) — sau khi đăng ký được duyệt
  ├── UC04: Chỉnh sửa danh sách (khi status = draft)
  ├── UC05: Nộp đăng ký
  └── UC06: Xem trạng thái phê duyệt

[Admin HO / HR]
  ├── UC06: Xem tất cả đăng ký
  ├── UC07: Phê duyệt / Từ chối đăng ký (kèm lý do)
  ├── UC08: Chỉnh sửa thông tin người tham dự sau phê duyệt
  ├── UC09: Gán vai trò cho người tham dự
  ├── UC10: Tạo/xuất thẻ tham dự theo lô
  ├── UC11: Gán trưởng đoàn cho từng đơn vị
  └── UC12: Dashboard tổng hợp

[Trưởng đoàn]
  ├── UC13: Xem danh sách thành viên đoàn mình
  ├── UC14: Báo cắt ăn từng người
  └── UC15: Báo cắt ăn cả đoàn (bulk)

[BTC Thi nghiệp vụ]
  ├── UC16: Tạo cuộc thi và các vòng thi
  ├── UC17: Cấp số báo danh (tự động hoặc thủ công)
  ├── UC18: Xuất danh sách thí sinh + số báo danh
  └── UC19: Quản lý lịch thi từng vòng

[BTC Thể thao]
  ├── UC20: Tạo các môn thi đấu
  ├── UC21: Tạo lịch thi đấu (giải đấu, vòng bảng, knockout)
  ├── UC22: Cập nhật kết quả trận đấu
  └── UC23: Xếp hạng và bảng điểm

[BTC Tiệc]
  ├── UC24: Tạo sự kiện tiệc
  ├── UC25: Thiết lập sơ đồ bàn (số bàn, vị trí, capacity)
  ├── UC26: Phân bổ người vào bàn/ghế
  └── UC27: Xem sơ đồ tổng quan

[Người tham dự — Public]
  ├── UC28: Quét QR → Xem thông tin cá nhân
  ├── UC29: Quét QR → Xem agenda đại hội
  ├── UC30: Quét QR → Xem lịch thi nghiệp vụ của mình
  └── UC31: Quét QR → Xem lịch thi đấu thể thao đơn vị mình
```

---

## 3. Database Schema

### 3.1 Danh sách tất cả bảng

| #   | Bảng                        | Mô tả                                               |
| --- | --------------------------- | --------------------------------------------------- | --- |
| 1   | `organizations`             | Đơn vị tham dự                                      |
| 2   | `unit_accounts`             | Tài khoản đăng nhập của từng đơn vị                 |
| 3   | `users`                     | Người dùng hệ thống (Admin HO, BTC các ban)         |
| 4   | `registration_periods`      | Khung thời gian đăng ký                             |
| 5   | `registrations`             | Phiếu đăng ký của từng đơn vị                       |
| 6   | `attendees`                 | Người tham dự (từng cá nhân)                        |
| 7   | `roles`                     | Danh mục vai trò                                    |
| 8   | `attendee_roles`            | Gán vai trò cho người tham dự (many-to-many)        |
| 9   | `badges`                    | Thông tin thẻ tham dự                               |
| 10  | `event_agenda`              | Chương trình đại hội                                |
| 11  | `competitions`              | Cuộc thi nghiệp vụ                                  |
| 12  | `competition_rounds`        | Các vòng thi trong cuộc thi                         | l   |
| 13  | `competition_registrations` | Đăng ký thi nghiệp vụ + số báo danh                 |
| 14  | `competition_schedules`     | Lịch thi từng vòng                                  |
| 15  | `sports`                    | Môn thể thao                                        |
| 16  | `sport_teams`               | Đội thi đấu (có thể là đội đơn vị hoặc đội hỗn hợp) |
| 17  | `sport_team_members`        | Thành viên đội                                      |
| 18  | `sport_matches`             | Trận đấu                                            |
| 19  | `sport_match_results`       | Kết quả trận đấu                                    |
| 20  | `sport_standings`           | Bảng xếp hạng                                       |
| 21  | `banquet_events`            | Sự kiện tiệc                                        |
| 22  | `banquet_tables`            | Bàn trong tiệc                                      |
| 23  | `banquet_seats`             | Chỗ ngồi từng người                                 |
| 24  | `meals`                     | Các bữa ăn trong sự kiện                            |
| 25  | `meal_cutoffs`              | Báo cắt ăn                                          |
| 26  | `audit_logs`                | Lịch sử thay đổi (audit trail)                      |
| 27  | `events`                    | Sự kiện đại hội                                     |
| 28  | `event_units`               | Đơn vị tham gia sự kiện                             |
| 29  | `contents`                  | Nội dung hoạt động (Thể thao, Miss, Nghiệp vụ...)   |
| 30  | `event_contents`            | Sự kiện có những nội dung nào                       |
| 31  | `event_sports`              | Sự kiện cho thi đấu những môn nào                   |
| 32  | `event_competitions`        | Sự kiện thi những nghiệp vụ nào                     |
| 33  | `registration_details`      | Chi tiết phiếu đăng ký theo nội dung                |
| 34  | `transports`                | Phương tiện di chuyển                               |
| 35  | `staff`                     | Nhân viên (sync từ SMILE hoặc CRUD)                 |
| 36  | `meal_tables`               | Bàn ăn trong bữa ăn                                 |
| 37  | `meal_attendees`            | Phân bổ người vào bàn ăn                            |
| 38  | `meal_checkins`             | Check-in bữa ăn                                     |
| 39  | `competition_round_results` | Kết quả thi từng vòng nghiệp vụ                     |
| 40  | `sport_stages`              | Giai đoạn thi đấu (vòng loại, chung kết)            |
| 41  | `sport_stage_teams`         | Đội tham gia từng giai đoạn                         |
| 42  | `beauty_contests`           | Cuộc thi sắc đẹp (Miss)                             |
| 43  | `beauty_contestants`        | Thí sinh thi Miss                                   |
| 44  | `beauty_rounds`             | Vòng thi Miss (áo dài, bikini, tài năng...)         |
| 45  | `beauty_scores`             | Điểm chấm từng vòng                                 |
| 46  | `talent_shows`              | Cuộc thi văn nghệ                                   |
| 47  | `talent_categories`         | Thể loại (đơn ca, tốp ca, múa, tài năng)            |
| 48  | `talent_entries`            | Tiết mục đăng ký                                    |
| 49  | `talent_entry_members`      | Thành viên tiết mục (nếu tốp ca/nhóm múa)           |
| 50  | `talent_scores`             | Điểm chấm văn nghệ                                  |
| 51  | `regionals`                 | Phân khu vực (mỗi đơn vị thuộc một khu vực)         |

---

### 3.2 SQL Schema chi tiết

```sql
-- ============================================================
-- 0. REGIONALS — Phân khu vực
-- ============================================================
CREATE TABLE `regionals` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`        VARCHAR(50)  NOT NULL COMMENT 'Mã khu vực',
  `name`        VARCHAR(255) NOT NULL COMMENT 'Tên khu vực',
  `description` TEXT         COMMENT 'Mô tả',
  `status`      TINYINT      NOT NULL DEFAULT 1 COMMENT '1=active, 0=inactive',
  `created_at`  TIMESTAMP    NULL DEFAULT NULL,
  `updated_at`  TIMESTAMP    NULL DEFAULT NULL,
  `deleted_at`  TIMESTAMP    NULL DEFAULT NULL COMMENT 'Soft delete',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_regionals_code` (`code`),
  KEY `idx_regionals_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Phân khu vực - mỗi đơn vị thuộc một khu vực';


-- ============================================================
-- 1. ORGANIZATIONS — Đơn vị
-- ============================================================
CREATE TABLE `organizations` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `regional_id` BIGINT UNSIGNED NULL COMMENT 'Khu vực đơn vị thuộc về',
  `name`        VARCHAR(255) NOT NULL COMMENT 'Tên đơn vị',
  `code`        VARCHAR(50)  NOT NULL COMMENT 'Mã đơn vị (viết tắt)',
  `address`     TEXT         COMMENT 'Địa chỉ',
  `phone`       VARCHAR(20),
  `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
  `sort_order`  INT          NOT NULL DEFAULT 0,
  `created_at`  INT UNSIGNED COMMENT 'Unix timestamp',
  `updated_at`  INT UNSIGNED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_organizations_code` (`code`),
  KEY `idx_organizations_active` (`is_active`),
  KEY `idx_organizations_regional` (`regional_id`),
  CONSTRAINT `fk_organizations_regional`
    FOREIGN KEY (`regional_id`) REFERENCES `regionals`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Danh sách đơn vị tham dự';


-- ============================================================
-- 2. UNIT_ACCOUNTS — Tài khoản đơn vị (để đăng ký danh sách)
-- ============================================================
CREATE TABLE `unit_accounts` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organization_id` INT UNSIGNED NOT NULL,
  `username`        VARCHAR(100) NOT NULL,
  `password_hash`   VARCHAR(255) NOT NULL,
  `display_name`    VARCHAR(255),
  `is_active`       TINYINT(1)   NOT NULL DEFAULT 1,
  `last_login_at`   INT UNSIGNED,
  `created_at`      INT UNSIGNED,
  `updated_at`      INT UNSIGNED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_unit_accounts_username` (`username`),
  UNIQUE KEY `uq_unit_accounts_org` (`organization_id`),
  KEY `idx_unit_accounts_active` (`is_active`),
  CONSTRAINT `fk_unit_accounts_org`
    FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='Tài khoản đăng nhập của từng đơn vị — 1 đơn vị 1 tài khoản';


-- ============================================================
-- 3. USERS — Người dùng nội bộ (Admin HO, BTC các ban)
-- ============================================================
CREATE TABLE `users` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`        VARCHAR(100) NOT NULL,
  `password_hash`   VARCHAR(255) NOT NULL,
  `full_name`       VARCHAR(255) NOT NULL,
  `email`           VARCHAR(255),
  `role`            ENUM(
                      'admin',
                      'hr',
                      'competition_organizer',
                      'sports_organizer',
                      'banquet_organizer'
                    ) NOT NULL DEFAULT 'hr',
  `organization_id` INT UNSIGNED COMMENT 'NULL = HO staff',
  `is_active`       TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`      INT UNSIGNED,
  `updated_at`      INT UNSIGNED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`),
  KEY `idx_users_role` (`role`),
  CONSTRAINT `fk_users_org`
    FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 4. REGISTRATION_PERIODS — Khung thời gian đăng ký
-- ============================================================
CREATE TABLE `registration_periods` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(255) NOT NULL COMMENT 'VD: Đợt 1 - Đăng ký chính thức',
  `start_time`  INT UNSIGNED NOT NULL COMMENT 'Thời gian bắt đầu nhận đăng ký',
  `end_time`    INT UNSIGNED NOT NULL COMMENT 'Thời gian kết thúc nhận đăng ký',
  `max_per_org` INT          COMMENT 'Số người tối đa mỗi đơn vị (NULL = không giới hạn)',
  `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
  `note`        TEXT,
  `created_at`  INT UNSIGNED,
  `updated_at`  INT UNSIGNED,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='Các đợt đăng ký được mở bởi HO';


-- ============================================================
-- 5. REGISTRATIONS — Phiếu đăng ký của đơn vị
-- ============================================================
CREATE TABLE `registrations` (
  `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id`            INT UNSIGNED COMMENT 'Sự kiện đăng ký',
  `organization_id`     INT UNSIGNED NOT NULL,
  `relation_unit_id`    INT UNSIGNED COMMENT 'Đơn vị liên quân (nếu có)',
  `period_id`           INT UNSIGNED NOT NULL,
  `submitted_by`        INT UNSIGNED NOT NULL COMMENT 'unit_accounts.id',
  `status`              ENUM('draft','submitted','approved','rejected') NOT NULL DEFAULT 'draft',
  `document`            VARCHAR(500) COMMENT 'Tài liệu đính kèm (công văn phê duyệt)',
  `submitted_at`        INT UNSIGNED,
  `reviewed_by`         INT UNSIGNED COMMENT 'users.id — HR review',
  `reviewed_at`         INT UNSIGNED,
  `rejection_reason`    TEXT,
  `note`                TEXT         COMMENT 'Ghi chú thêm của đơn vị',
  `created_at`          INT UNSIGNED,
  `updated_at`          INT UNSIGNED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_registrations_org_period` (`organization_id`, `period_id`)
    COMMENT 'Mỗi đơn vị chỉ có 1 phiếu/đợt',
  KEY `idx_registrations_status` (`status`),
  KEY `idx_registrations_period` (`period_id`),
  KEY `idx_registrations_event` (`event_id`),
  CONSTRAINT `fk_registrations_event`
    FOREIGN KEY (`event_id`) REFERENCES `events`(`id`),
  CONSTRAINT `fk_registrations_org`
    FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`),
  CONSTRAINT `fk_registrations_relation_unit`
    FOREIGN KEY (`relation_unit_id`) REFERENCES `organizations`(`id`),
  CONSTRAINT `fk_registrations_period`
    FOREIGN KEY (`period_id`) REFERENCES `registration_periods`(`id`),
  CONSTRAINT `fk_registrations_reviewer`
    FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 6. ATTENDEES — Người tham dự
-- ============================================================
CREATE TABLE `attendees` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id`        INT UNSIGNED COMMENT 'Sự kiện tham dự',
  `registration_id` INT UNSIGNED NOT NULL,
  `organization_id` INT UNSIGNED NOT NULL COMMENT 'Denormalized để query nhanh',
  `staff_id`        INT UNSIGNED COMMENT 'Liên kết nhân viên (nếu có)',
  `role_id`         INT UNSIGNED COMMENT 'Vai trò chính trong sự kiện',
  `full_name`       VARCHAR(255) NOT NULL,
  `position`        VARCHAR(255) COMMENT 'Chức danh',
  `unit_label`      VARCHAR(255) COMMENT 'Tên đơn vị hiển thị trên thẻ (có thể khác org.name)',
  `photo_path`      VARCHAR(500) COMMENT 'Đường dẫn file ảnh crop',
  `photo_full_path` VARCHAR(500) COMMENT 'Đường dẫn file ảnh gốc',
  `qr_token`        VARCHAR(64)  UNIQUE COMMENT 'Token ngẫu nhiên cho QR (không phải ID)',
  `badge_number`    VARCHAR(20)  UNIQUE COMMENT 'Số thứ tự trên thẻ, VD: 001',
  `badge_generated` TINYINT(1)   NOT NULL DEFAULT 0,
  `badge_printed`   TINYINT(1)   NOT NULL DEFAULT 0,
  `transport_id`    INT UNSIGNED COMMENT 'Phương tiện di chuyển',
  `check_in_date`   DATE         COMMENT 'Ngày check-in dự kiến',
  `check_out_date`  DATE         COMMENT 'Ngày check-out dự kiến',
  `is_team_lead`    TINYINT(1)   NOT NULL DEFAULT 0 COMMENT 'Có phải trưởng đoàn không',
  `is_active`       TINYINT(1)   NOT NULL DEFAULT 1 COMMENT 'Soft delete',
  `sort_order`      INT          NOT NULL DEFAULT 0,
  `approved_by`     INT UNSIGNED COMMENT 'users.id phê duyệt',
  `approved_at`     INT UNSIGNED,
  `note`            TEXT,
  `created_at`      INT UNSIGNED,
  `updated_at`      INT UNSIGNED,
  `deleted_at`      INT UNSIGNED COMMENT 'Soft delete timestamp',
  PRIMARY KEY (`id`),
  KEY `idx_attendees_event` (`event_id`),
  KEY `idx_attendees_registration` (`registration_id`),
  KEY `idx_attendees_org` (`organization_id`),
  KEY `idx_attendees_staff` (`staff_id`),
  KEY `idx_attendees_qr` (`qr_token`),
  KEY `idx_attendees_team_lead` (`is_team_lead`),
  CONSTRAINT `fk_attendees_event`
    FOREIGN KEY (`event_id`) REFERENCES `events`(`id`),
  CONSTRAINT `fk_attendees_registration`
    FOREIGN KEY (`registration_id`) REFERENCES `registrations`(`id`),
  CONSTRAINT `fk_attendees_org`
    FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`),
  CONSTRAINT `fk_attendees_staff`
    FOREIGN KEY (`staff_id`) REFERENCES `staff`(`id`),
  CONSTRAINT `fk_attendees_role`
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`),
  CONSTRAINT `fk_attendees_transport`
    FOREIGN KEY (`transport_id`) REFERENCES `transports`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 7. ROLES — Danh mục vai trò trong đại hội
-- ============================================================
CREATE TABLE `roles` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(100) NOT NULL,
  `code`        VARCHAR(50)  NOT NULL UNIQUE,
  `color`       VARCHAR(7)   COMMENT 'Màu hex hiển thị trên badge, VD: #FF0000',
  `icon`        VARCHAR(50)  COMMENT 'CSS class icon',
  `description` TEXT,
  `sort_order`  INT          NOT NULL DEFAULT 0,
  `created_at`  INT UNSIGNED,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed data
INSERT INTO `roles` (`name`, `code`, `color`, `sort_order`) VALUES
('Hỗ trợ đại hội',   'support',          '#2196F3', 1),
('Thi thể thao',     'sports',           '#4CAF50', 2),
('Thi nghiệp vụ',    'competition',      '#FF9800', 3),
('Giám đốc',         'director',         '#9C27B0', 4),
('Phó Giám đốc',     'deputy_director',  '#673AB7', 5),
('Khách mời',        'guest',            '#607D8B', 6),
('Trưởng đoàn',      'team_lead',        '#F44336', 7),
('Ban tổ chức',      'btc',              '#E91E63', 8);


-- ============================================================
-- 8. ATTENDEE_ROLES — Gán vai trò (many-to-many)
-- ============================================================
CREATE TABLE `attendee_roles` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `attendee_id` INT UNSIGNED NOT NULL,
  `role_id`     INT UNSIGNED NOT NULL,
  `assigned_by` INT UNSIGNED COMMENT 'users.id',
  `assigned_at` INT UNSIGNED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_attendee_role` (`attendee_id`, `role_id`),
  KEY `idx_attendee_roles_role` (`role_id`),
  CONSTRAINT `fk_ar_attendee`
    FOREIGN KEY (`attendee_id`) REFERENCES `attendees`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ar_role`
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 9. BADGES — Thẻ tham dự (thông tin xuất ảnh)
-- ============================================================
CREATE TABLE `badges` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `attendee_id`     INT UNSIGNED NOT NULL UNIQUE,
  `template_id`     INT UNSIGNED COMMENT 'badge_templates.id — nếu có nhiều mẫu thẻ',
  `generated_path`  VARCHAR(500) COMMENT 'Đường dẫn file ảnh thẻ đã render',
  `width_mm`        DECIMAL(6,2) NOT NULL DEFAULT 85.60  COMMENT 'Chiều rộng thẻ mm (CR80)',
  `height_mm`       DECIMAL(6,2) NOT NULL DEFAULT 53.98  COMMENT 'Chiều cao thẻ mm (CR80)',
  `dpi`             INT          NOT NULL DEFAULT 300,
  `generated_at`    INT UNSIGNED,
  `print_count`     INT          NOT NULL DEFAULT 0,
  `last_printed_at` INT UNSIGNED,
  `created_at`      INT UNSIGNED,
  `updated_at`      INT UNSIGNED,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_badges_attendee`
    FOREIGN KEY (`attendee_id`) REFERENCES `attendees`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 10. EVENT_AGENDA — Chương trình đại hội
-- ============================================================
CREATE TABLE `event_agenda` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`       VARCHAR(255) NOT NULL,
  `description` TEXT,
  `location`    VARCHAR(255),
  `start_time`  INT UNSIGNED NOT NULL,
  `end_time`    INT UNSIGNED,
  `type`        ENUM('plenary','break','workshop','ceremony','other') NOT NULL DEFAULT 'plenary',
  `is_public`   TINYINT(1)   NOT NULL DEFAULT 1 COMMENT 'Hiển thị khi quét QR',
  `sort_order`  INT          NOT NULL DEFAULT 0,
  `created_at`  INT UNSIGNED,
  `updated_at`  INT UNSIGNED,
  PRIMARY KEY (`id`),
  KEY `idx_agenda_time` (`start_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 11. COMPETITIONS — Cuộc thi nghiệp vụ
-- ============================================================
CREATE TABLE `competitions` (
  `id`                    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`                  VARCHAR(255) NOT NULL,
  `description`           TEXT,
  `registration_open_at`  INT UNSIGNED COMMENT 'Mở đăng ký từ',
  `registration_close_at` INT UNSIGNED COMMENT 'Đóng đăng ký lúc',
  `candidate_number_prefix` VARCHAR(10) COMMENT 'VD: NV → số báo danh NV001',
  `candidate_number_start`  INT         NOT NULL DEFAULT 1,
  `candidate_number_pad`    INT         NOT NULL DEFAULT 3 COMMENT 'Độ dài số báo danh',
  `max_per_org`           INT          COMMENT 'Giới hạn số người/đơn vị (NULL=không giới hạn)',
  `has_qualification`     TINYINT(1)   NOT NULL DEFAULT 1 COMMENT 'Có vòng loại không',
  `allow_direct_final`    TINYINT(1)   NOT NULL DEFAULT 0 COMMENT 'Cho phép ghi danh thẳng chung kết',
  `is_active`             TINYINT(1)   NOT NULL DEFAULT 1,
  `created_by`            INT UNSIGNED,
  `created_at`            INT UNSIGNED,
  `updated_at`            INT UNSIGNED,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Thi nghiệp vụ - hỗ trợ: vòng loại→chung kết hoặc ghi danh thẳng';


-- ============================================================
-- 12. COMPETITION_ROUNDS — Các vòng thi
-- ============================================================
CREATE TABLE `competition_rounds` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `competition_id` INT UNSIGNED NOT NULL,
  `name`           VARCHAR(100) NOT NULL COMMENT 'VD: Vòng loại, Bán kết, Chung kết',
  `round_order`    INT          NOT NULL DEFAULT 1,
  `location`       VARCHAR(255),
  `start_time`     INT UNSIGNED,
  `end_time`       INT UNSIGNED,
  `instructions`   TEXT         COMMENT 'Hướng dẫn thi',
  `created_at`     INT UNSIGNED,
  PRIMARY KEY (`id`),
  KEY `idx_comp_rounds_comp` (`competition_id`),
  CONSTRAINT `fk_comp_rounds_comp`
    FOREIGN KEY (`competition_id`) REFERENCES `competitions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 13. COMPETITION_REGISTRATIONS — Đăng ký thi + số báo danh
-- ============================================================
CREATE TABLE `competition_registrations` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `competition_id`   INT UNSIGNED NOT NULL,
  `attendee_id`      INT UNSIGNED NOT NULL,
  `candidate_number` VARCHAR(20)  UNIQUE COMMENT 'Số báo danh, VD: NV001',
  `status`           ENUM('pending','confirmed','cancelled','no_show') NOT NULL DEFAULT 'pending',
  `registered_at`    INT UNSIGNED,
  `confirmed_by`     INT UNSIGNED COMMENT 'users.id',
  `confirmed_at`     INT UNSIGNED,
  `note`             TEXT,
  `created_at`       INT UNSIGNED,
  `updated_at`       INT UNSIGNED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_comp_reg_attendee` (`competition_id`, `attendee_id`),
  KEY `idx_comp_reg_status` (`status`),
  CONSTRAINT `fk_comp_reg_comp`
    FOREIGN KEY (`competition_id`) REFERENCES `competitions`(`id`),
  CONSTRAINT `fk_comp_reg_attendee`
    FOREIGN KEY (`attendee_id`) REFERENCES `attendees`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 14. SPORTS — Môn thể thao (hỗ trợ cấu trúc cha-con)
-- ============================================================
-- Lưu dạng cha-con: Bóng đá (parent_id=NULL) -> Bóng đá nam, Bóng đá nữ (parent_id=Bóng đá)
CREATE TABLE `sports` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`        VARCHAR(50)  UNIQUE COMMENT 'Mã môn',
  `name`        VARCHAR(100) NOT NULL COMMENT 'VD: Bóng đá, Cầu lông, Kéo co',
  `parent_id`   INT UNSIGNED COMMENT 'Môn cha (NULL=root)',
  `type`        ENUM('team','individual') NOT NULL DEFAULT 'team',
  `description` TEXT,
  `document`    VARCHAR(500) COMMENT 'Đường dẫn file điều lệ thi đấu',
  `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
  `sort_order`  INT          NOT NULL DEFAULT 0,
  `created_at`  INT UNSIGNED,
  PRIMARY KEY (`id`),
  KEY `idx_sports_parent` (`parent_id`),
  CONSTRAINT `fk_sports_parent` FOREIGN KEY (`parent_id`) REFERENCES `sports`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 15. SPORT_TEAMS — Đội thi đấu
-- ============================================================
CREATE TABLE `sport_teams` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`            VARCHAR(50)  UNIQUE,
  `event_id`        INT UNSIGNED COMMENT 'Sự kiện',
  `sport_id`        INT UNSIGNED NOT NULL,
  `organization_id` INT UNSIGNED COMMENT 'NULL nếu đội hỗn hợp (có thể nhiều đơn vị)',
  `name`            VARCHAR(255) NOT NULL,
  `short_name`      VARCHAR(50)  COMMENT 'Tên rút gọn hiển thị trên bảng',
  `color`           VARCHAR(7)   COMMENT 'Màu đội',
  `is_active`       TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`      INT UNSIGNED,
  `updated_at`      INT UNSIGNED,
  PRIMARY KEY (`id`),
  KEY `idx_sport_teams_event` (`event_id`),
  KEY `idx_sport_teams_sport` (`sport_id`),
  CONSTRAINT `fk_sport_teams_event`
    FOREIGN KEY (`event_id`) REFERENCES `events`(`id`),
  CONSTRAINT `fk_sport_teams_sport`
    FOREIGN KEY (`sport_id`) REFERENCES `sports`(`id`),
  CONSTRAINT `fk_sport_teams_org`
    FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 16. SPORT_TEAM_MEMBERS — Thành viên đội
-- ============================================================
CREATE TABLE `sport_team_members` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`        VARCHAR(50)  UNIQUE,
  `team_id`     INT UNSIGNED NOT NULL,
  `attendee_id` INT UNSIGNED NOT NULL,
  `name`        VARCHAR(255) COMMENT 'Tên hiển thị (override attendee name nếu cần)',
  `image`       VARCHAR(500) COMMENT 'Ảnh riêng cho đội (override nếu cần)',
  `jersey_number` VARCHAR(10),
  `position`    VARCHAR(100) COMMENT 'Vị trí thi đấu',
  `is_captain`  TINYINT(1)   NOT NULL DEFAULT 0,
  `note`        TEXT,
  `status`      TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`  INT UNSIGNED,
  `updated_at`  INT UNSIGNED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_team_member` (`team_id`, `attendee_id`),
  CONSTRAINT `fk_stm_team`
    FOREIGN KEY (`team_id`) REFERENCES `sport_teams`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_stm_attendee`
    FOREIGN KEY (`attendee_id`) REFERENCES `attendees`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 17. SPORT_MATCHES — Lịch/trận đấu
-- ============================================================
CREATE TABLE `sport_matches` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`          VARCHAR(50)  UNIQUE,
  `event_id`      INT UNSIGNED COMMENT 'Sự kiện',
  `sport_id`      INT UNSIGNED NOT NULL,
  `stage_id`      INT UNSIGNED COMMENT 'Giai đoạn (vòng loại/chung kết/play-off)',
  `round`         VARCHAR(100) COMMENT 'Vòng bảng A / Tứ kết / Bán kết / Chung kết',
  `match_type`    ENUM('group','knockout','playoff','final') NOT NULL DEFAULT 'group'
                  COMMENT 'Loại trận: vòng bảng, loại trực tiếp, play-off, chung kết',
  `description`   TEXT,
  `match_order`   INT          NOT NULL DEFAULT 0,
  `team_a_id`     INT UNSIGNED COMMENT 'sport_teams.id — NULL nếu chưa biết (TBD)',
  `team_b_id`     INT UNSIGNED COMMENT 'sport_teams.id',
  `match_time`    INT UNSIGNED,
  `location`      VARCHAR(255),
  `final_score`   VARCHAR(50)  COMMENT 'Kết quả chung cuộc: 3-2, 1-0...',
  `status`        ENUM('scheduled','ongoing','completed','cancelled','postponed')
                  NOT NULL DEFAULT 'scheduled',
  `note`          TEXT,
  `created_at`    INT UNSIGNED,
  `updated_at`    INT UNSIGNED,
  PRIMARY KEY (`id`),
  KEY `idx_matches_event` (`event_id`),
  KEY `idx_matches_sport` (`sport_id`),
  KEY `idx_matches_stage` (`stage_id`),
  KEY `idx_matches_time` (`match_time`),
  KEY `idx_matches_status` (`status`),
  CONSTRAINT `fk_matches_event`
    FOREIGN KEY (`event_id`) REFERENCES `events`(`id`),
  CONSTRAINT `fk_matches_sport`
    FOREIGN KEY (`sport_id`) REFERENCES `sports`(`id`),
  CONSTRAINT `fk_matches_stage`
    FOREIGN KEY (`stage_id`) REFERENCES `sport_stages`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_matches_team_a`
    FOREIGN KEY (`team_a_id`) REFERENCES `sport_teams`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_matches_team_b`
    FOREIGN KEY (`team_b_id`) REFERENCES `sport_teams`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 18. SPORT_MATCH_RESULTS — Kết quả trận đấu
-- ============================================================
CREATE TABLE `sport_match_results` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `match_id`       INT UNSIGNED NOT NULL UNIQUE,
  `score_a`        VARCHAR(20)  COMMENT 'Điểm/tỉ số đội A',
  `score_b`        VARCHAR(20)  COMMENT 'Điểm/tỉ số đội B',
  `winner_team_id` INT UNSIGNED COMMENT 'NULL nếu hòa',
  `is_draw`        TINYINT(1)   NOT NULL DEFAULT 0,
  `detail`         TEXT         COMMENT 'Chi tiết kết quả, VD: hiệp 1, hiệp 2',
  `note`           TEXT,
  `status`         TINYINT(1)   NOT NULL DEFAULT 1,
  `recorded_by`    INT UNSIGNED COMMENT 'users.id',
  `recorded_at`    INT UNSIGNED,
  `created_at`     INT UNSIGNED,
  `updated_at`     INT UNSIGNED,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_results_match`
    FOREIGN KEY (`match_id`) REFERENCES `sport_matches`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_results_winner`
    FOREIGN KEY (`winner_team_id`) REFERENCES `sport_teams`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 19. BANQUET_EVENTS — Sự kiện tiệc
-- ============================================================
CREATE TABLE `banquet_events` (
  `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`                VARCHAR(255) NOT NULL COMMENT 'VD: Tiệc tối khai mạc',
  `event_time`          INT UNSIGNED NOT NULL,
  `location`            VARCHAR(255),
  `total_tables`        INT          NOT NULL DEFAULT 0,
  `seats_per_table`     INT          NOT NULL DEFAULT 10,
  `layout_description`  TEXT         COMMENT 'Mô tả sơ đồ (hướng sân khấu, lối vào...)',
  `layout_image_path`   VARCHAR(500) COMMENT 'Ảnh sơ đồ tổng quan',
  `canvas_width`        INT          NOT NULL DEFAULT 1200 COMMENT 'px — canvas sơ đồ',
  `canvas_height`       INT          NOT NULL DEFAULT 800,
  `is_active`           TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`          INT UNSIGNED,
  `updated_at`          INT UNSIGNED,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 20. BANQUET_TABLES — Bàn tiệc
-- ============================================================
CREATE TABLE `banquet_tables` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id`     INT UNSIGNED NOT NULL,
  `table_number` INT          NOT NULL,
  `label`        VARCHAR(100) COMMENT 'Nhãn bàn, VD: VIP-01, A1',
  `capacity`     INT          NOT NULL DEFAULT 10,
  `pos_x`        INT          NOT NULL DEFAULT 0 COMMENT 'Vị trí X trên canvas (px)',
  `pos_y`        INT          NOT NULL DEFAULT 0 COMMENT 'Vị trí Y trên canvas (px)',
  `shape`        ENUM('circle','rectangle') NOT NULL DEFAULT 'circle',
  `note`         VARCHAR(255) COMMENT 'VD: Bàn VIP, Ban Giám đốc',
  `created_at`   INT UNSIGNED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_table_number_event` (`event_id`, `table_number`),
  KEY `idx_banquet_tables_event` (`event_id`),
  CONSTRAINT `fk_tables_event`
    FOREIGN KEY (`event_id`) REFERENCES `banquet_events`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 21. BANQUET_SEATS — Phân chỗ ngồi
-- ============================================================
CREATE TABLE `banquet_seats` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `table_id`    INT UNSIGNED NOT NULL,
  `attendee_id` INT UNSIGNED NOT NULL,
  `seat_number` INT          COMMENT 'Số ghế trong bàn (1-based, NULL = không đánh số)',
  `assigned_by` INT UNSIGNED COMMENT 'users.id',
  `assigned_at` INT UNSIGNED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_seat_attendee_event` (`table_id`, `attendee_id`),
  KEY `idx_banquet_seats_attendee` (`attendee_id`),
  CONSTRAINT `fk_seats_table`
    FOREIGN KEY (`table_id`) REFERENCES `banquet_tables`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_seats_attendee`
    FOREIGN KEY (`attendee_id`) REFERENCES `attendees`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 22. MEALS — Các bữa ăn
-- ============================================================
CREATE TABLE `meals` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`         VARCHAR(255) NOT NULL COMMENT 'VD: Bữa trưa ngày 1',
  `meal_date`    DATE         NOT NULL,
  `meal_type`    ENUM('breakfast','lunch','dinner') NOT NULL,
  `serving_time` INT UNSIGNED COMMENT 'Giờ phục vụ',
  `location`     VARCHAR(255),
  `total_count`  INT          COMMENT 'Tổng số suất đăng ký (auto-calculated)',
  `cutoff_deadline` INT UNSIGNED COMMENT 'Deadline báo cắt ăn',
  `is_active`    TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`   INT UNSIGNED,
  `updated_at`   INT UNSIGNED,
  PRIMARY KEY (`id`),
  KEY `idx_meals_date` (`meal_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 23. MEAL_CUTOFFS — Báo cắt ăn
-- ============================================================
CREATE TABLE `meal_cutoffs` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `meal_id`      INT UNSIGNED NOT NULL,
  `attendee_id`  INT UNSIGNED NOT NULL,
  `is_cutoff`    TINYINT(1)   NOT NULL DEFAULT 0 COMMENT '1=cắt ăn, 0=có ăn',
  `reason`       VARCHAR(255) COMMENT 'Lý do cắt ăn',
  `reported_by`  INT UNSIGNED NOT NULL COMMENT 'attendees.id của trưởng đoàn',
  `reported_at`  INT UNSIGNED,
  `approved_by`  INT UNSIGNED COMMENT 'users.id phê duyệt',
  `approved_at`  INT UNSIGNED,
  `created_at`   INT UNSIGNED,
  `updated_at`   INT UNSIGNED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_meal_attendee` (`meal_id`, `attendee_id`),
  KEY `idx_meal_cutoffs_meal` (`meal_id`),
  KEY `idx_meal_cutoffs_attendee` (`attendee_id`),
  CONSTRAINT `fk_mc_meal`
    FOREIGN KEY (`meal_id`) REFERENCES `meals`(`id`),
  CONSTRAINT `fk_mc_attendee`
    FOREIGN KEY (`attendee_id`) REFERENCES `attendees`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 24. AUDIT_LOGS — Lịch sử thay đổi
-- ============================================================
CREATE TABLE `audit_logs` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `actor_type`  ENUM('user','unit_account') NOT NULL,
  `actor_id`    INT UNSIGNED NOT NULL,
  `action`      VARCHAR(100) NOT NULL COMMENT 'VD: registration.approve, attendee.update',
  `entity_type` VARCHAR(50)  NOT NULL COMMENT 'Tên bảng bị tác động',
  `entity_id`   INT UNSIGNED NOT NULL,
  `old_data`    TEXT         COMMENT 'JSON dữ liệu cũ',
  `new_data`    TEXT         COMMENT 'JSON dữ liệu mới',
  `ip_address`  VARCHAR(45),
  `created_at`  INT UNSIGNED,
  PRIMARY KEY (`id`),
  KEY `idx_audit_entity` (`entity_type`, `entity_id`),
  KEY `idx_audit_actor` (`actor_type`, `actor_id`),
  KEY `idx_audit_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 25. EVENTS — Sự kiện đại hội
-- ============================================================
CREATE TABLE `events` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`        VARCHAR(50)  NOT NULL UNIQUE,
  `name`        VARCHAR(255) NOT NULL,
  `from_date`   DATE         NOT NULL,
  `to_date`     DATE         NOT NULL,
  `description` TEXT,
  `status`      ENUM('draft','active','completed','cancelled') NOT NULL DEFAULT 'draft',
  `created_at`  INT UNSIGNED,
  `updated_at`  INT UNSIGNED,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Sự kiện đại hội';


-- ============================================================
-- 26. EVENT_UNITS — Đơn vị tham gia sự kiện
-- ============================================================
CREATE TABLE `event_units` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id`        INT UNSIGNED NOT NULL,
  `organization_id` INT UNSIGNED NOT NULL,
  `status`          ENUM('invited','confirmed','declined') NOT NULL DEFAULT 'invited',
  `description`     TEXT,
  `created_at`      INT UNSIGNED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_event_unit` (`event_id`, `organization_id`),
  CONSTRAINT `fk_event_units_event` FOREIGN KEY (`event_id`) REFERENCES `events`(`id`),
  CONSTRAINT `fk_event_units_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 27. CONTENTS — Nội dung hoạt động (Thể thao, Miss, Nghiệp vụ...)
-- ============================================================
CREATE TABLE `contents` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`        VARCHAR(50)  NOT NULL UNIQUE,
  `name`        VARCHAR(255) NOT NULL,
  `description` TEXT,
  `status`      TINYINT(1)   NOT NULL DEFAULT 1,
  `sort_order`  INT          NOT NULL DEFAULT 0,
  `created_at`  INT UNSIGNED,
  `updated_at`  INT UNSIGNED,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Nội dung hoạt động: Thể thao, Miss, Nghiệp vụ...';

INSERT INTO `contents` (`code`, `name`, `sort_order`) VALUES
('sports', 'Thi đấu thể thao', 1),
('competition', 'Thi nghiệp vụ', 2),
('miss', 'Hội thi sắc đẹp', 3),
('talent', 'Hội diễn văn nghệ', 4),
('ceremony', 'Lễ khai/bế mạc', 5);


-- ============================================================
-- 28. EVENT_CONTENTS — Sự kiện có những nội dung nào
-- ============================================================
CREATE TABLE `event_contents` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id`    INT UNSIGNED NOT NULL,
  `content_id`  INT UNSIGNED NOT NULL,
  `status`      TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`  INT UNSIGNED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_event_content` (`event_id`, `content_id`),
  CONSTRAINT `fk_ec_event` FOREIGN KEY (`event_id`) REFERENCES `events`(`id`),
  CONSTRAINT `fk_ec_content` FOREIGN KEY (`content_id`) REFERENCES `contents`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 29. EVENT_SPORTS — Sự kiện cho thi đấu những môn nào
-- ============================================================
CREATE TABLE `event_sports` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id`    INT UNSIGNED NOT NULL,
  `sport_id`    INT UNSIGNED NOT NULL,
  `status`      TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`  INT UNSIGNED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_event_sport` (`event_id`, `sport_id`),
  CONSTRAINT `fk_es_event` FOREIGN KEY (`event_id`) REFERENCES `events`(`id`),
  CONSTRAINT `fk_es_sport` FOREIGN KEY (`sport_id`) REFERENCES `sports`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 30. EVENT_COMPETITIONS — Sự kiện thi những nghiệp vụ nào
-- ============================================================
CREATE TABLE `event_competitions` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id`       INT UNSIGNED NOT NULL,
  `competition_id` INT UNSIGNED NOT NULL,
  `status`         TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`     INT UNSIGNED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_event_competition` (`event_id`, `competition_id`),
  CONSTRAINT `fk_ecomp_event` FOREIGN KEY (`event_id`) REFERENCES `events`(`id`),
  CONSTRAINT `fk_ecomp_comp` FOREIGN KEY (`competition_id`) REFERENCES `competitions`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 31. REGISTRATION_DETAILS — Chi tiết phiếu đăng ký
-- ============================================================
CREATE TABLE `registration_details` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `registration_id`  INT UNSIGNED NOT NULL,
  `role_id`          INT UNSIGNED COMMENT 'Vai trò tham dự (event_roles)',
  `content_id`       INT UNSIGNED COMMENT 'Nội dung đăng ký (contents)',
  `sport_id`         INT UNSIGNED COMMENT 'Môn thể thao (nếu content=sports)',
  `competition_id`   INT UNSIGNED COMMENT 'Nghiệp vụ (nếu content=competition)',
  `quantity`         INT          NOT NULL DEFAULT 1,
  `note`             TEXT,
  `status`           TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`       INT UNSIGNED,
  `updated_at`       INT UNSIGNED,
  PRIMARY KEY (`id`),
  KEY `idx_regdetail_registration` (`registration_id`),
  CONSTRAINT `fk_rd_registration` FOREIGN KEY (`registration_id`) REFERENCES `registrations`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rd_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`),
  CONSTRAINT `fk_rd_content` FOREIGN KEY (`content_id`) REFERENCES `contents`(`id`),
  CONSTRAINT `fk_rd_sport` FOREIGN KEY (`sport_id`) REFERENCES `sports`(`id`),
  CONSTRAINT `fk_rd_competition` FOREIGN KEY (`competition_id`) REFERENCES `competitions`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Chi tiết đăng ký: số lượng theo vai trò, nội dung';


-- ============================================================
-- 32. TRANSPORTS — Phương tiện di chuyển
-- ============================================================
CREATE TABLE `transports` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`        VARCHAR(50)  NOT NULL UNIQUE,
  `name`        VARCHAR(255) NOT NULL,
  `description` TEXT,
  `status`      TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`  INT UNSIGNED,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `transports` (`code`, `name`) VALUES
('plane', 'Máy bay'),
('train', 'Tàu hỏa'),
('bus', 'Xe buýt/Ô tô'),
('self', 'Tự túc');


-- ============================================================
-- 33. STAFF — Nhân viên (sync từ SMILE hoặc CRUD)
-- ============================================================
-- Nguồn dữ liệu:
--   - Nhân viên Tập đoàn: sync từ SMILE (không cho sửa)
--   - Nhân viên chưa có SMILE: CRUD thủ công
--   - Nhân viên ngoài: CRUD thủ công
-- Có thể import từ Excel
CREATE TABLE `staff` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`            VARCHAR(50)  NOT NULL UNIQUE COMMENT 'Mã nhân viên',
  `full_name`       VARCHAR(255) NOT NULL,
  `organization_id` INT UNSIGNED,
  `department`      VARCHAR(255) COMMENT 'Phòng ban',
  `position`        VARCHAR(255) COMMENT 'Chức vụ',
  `email`           VARCHAR(255),
  `phone`           VARCHAR(20),
  `source`          ENUM('smile','manual','external') NOT NULL DEFAULT 'manual',
  `smile_id`        VARCHAR(100) COMMENT 'ID từ SMILE nếu có',
  `is_active`       TINYINT(1)   NOT NULL DEFAULT 1,
  `synced_at`       INT UNSIGNED COMMENT 'Lần sync cuối từ SMILE',
  `created_at`      INT UNSIGNED,
  `updated_at`      INT UNSIGNED,
  PRIMARY KEY (`id`),
  KEY `idx_staff_org` (`organization_id`),
  KEY `idx_staff_source` (`source`),
  CONSTRAINT `fk_staff_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Danh sách nhân viên - sync từ SMILE hoặc CRUD';


-- ============================================================
-- 34. MEAL_TABLES — Bàn ăn trong bữa ăn
-- ============================================================
CREATE TABLE `meal_tables` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `meal_id`     INT UNSIGNED NOT NULL,
  `name`        VARCHAR(100) NOT NULL,
  `capacity`    INT          NOT NULL DEFAULT 10,
  `status`      TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`  INT UNSIGNED,
  `updated_at`  INT UNSIGNED,
  PRIMARY KEY (`id`),
  KEY `idx_meal_tables_meal` (`meal_id`),
  CONSTRAINT `fk_mt_meal` FOREIGN KEY (`meal_id`) REFERENCES `meals`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 35. MEAL_ATTENDEES — Phân bổ người vào bàn ăn
-- ============================================================
CREATE TABLE `meal_attendees` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `meal_id`     INT UNSIGNED NOT NULL,
  `attendee_id` INT UNSIGNED NOT NULL,
  `table_id`    INT UNSIGNED COMMENT 'Bàn được phân (NULL=chưa phân)',
  `status`      ENUM('registered','confirmed','cancelled') NOT NULL DEFAULT 'registered',
  `created_at`  INT UNSIGNED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_meal_attendee` (`meal_id`, `attendee_id`),
  KEY `idx_ma_table` (`table_id`),
  CONSTRAINT `fk_ma_meal` FOREIGN KEY (`meal_id`) REFERENCES `meals`(`id`),
  CONSTRAINT `fk_ma_attendee` FOREIGN KEY (`attendee_id`) REFERENCES `attendees`(`id`),
  CONSTRAINT `fk_ma_table` FOREIGN KEY (`table_id`) REFERENCES `meal_tables`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 36. MEAL_CHECKINS — Check-in bữa ăn
-- ============================================================
CREATE TABLE `meal_checkins` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `meal_id`       INT UNSIGNED NOT NULL,
  `attendee_id`   INT UNSIGNED NOT NULL,
  `check_in_time` INT UNSIGNED NOT NULL,
  `status`        TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`    INT UNSIGNED,
  `updated_at`    INT UNSIGNED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_meal_checkin` (`meal_id`, `attendee_id`),
  CONSTRAINT `fk_mc2_meal` FOREIGN KEY (`meal_id`) REFERENCES `meals`(`id`),
  CONSTRAINT `fk_mc2_attendee` FOREIGN KEY (`attendee_id`) REFERENCES `attendees`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 37. COMPETITION_ROUND_RESULTS — Kết quả thi từng vòng nghiệp vụ
-- ============================================================
-- Hỗ trợ 2 trường hợp:
--   1. Vòng loại → Chung kết (passed=1 để vào vòng sau)
--   2. Ghi danh thẳng chung kết (entry_type='direct')
CREATE TABLE `competition_round_results` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `round_id`          INT UNSIGNED NOT NULL COMMENT 'competition_rounds.id',
  `registration_id`   INT UNSIGNED NOT NULL COMMENT 'competition_registrations.id',
  `score`             DECIMAL(8,2) COMMENT 'Điểm số',
  `rank`              INT          COMMENT 'Thứ hạng vòng này',
  `passed`            TINYINT(1)   NOT NULL DEFAULT 0 COMMENT 'Vào vòng sau?',
  `entry_type`        ENUM('qualification','direct') NOT NULL DEFAULT 'qualification'
                      COMMENT 'qualification=qua vòng loại, direct=ghi danh thẳng',
  `note`              TEXT,
  `scored_by`         INT UNSIGNED COMMENT 'users.id',
  `scored_at`         INT UNSIGNED,
  `created_at`        INT UNSIGNED,
  `updated_at`        INT UNSIGNED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_round_registration` (`round_id`, `registration_id`),
  KEY `idx_crr_passed` (`passed`),
  CONSTRAINT `fk_crr_round`
    FOREIGN KEY (`round_id`) REFERENCES `competition_rounds`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_crr_reg`
    FOREIGN KEY (`registration_id`) REFERENCES `competition_registrations`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Kết quả thi nghiệp vụ từng vòng';


-- ============================================================
-- 38. SPORT_STAGES — Giai đoạn thi đấu thể thao
-- ============================================================
-- Hỗ trợ: Vòng loại → Chung kết, hoặc Play-off
CREATE TABLE `sport_stages` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id`    INT UNSIGNED NOT NULL,
  `sport_id`    INT UNSIGNED NOT NULL,
  `name`        VARCHAR(100) NOT NULL COMMENT 'Vòng loại, Chung kết, Play-off',
  `stage_type`  ENUM('qualification','playoff','final') NOT NULL DEFAULT 'qualification',
  `stage_order` INT          NOT NULL DEFAULT 1,
  `start_date`  DATE,
  `end_date`    DATE,
  `location`    VARCHAR(255),
  `rules`       TEXT         COMMENT 'Điều lệ riêng giai đoạn này',
  `status`      ENUM('upcoming','ongoing','completed') NOT NULL DEFAULT 'upcoming',
  `created_at`  INT UNSIGNED,
  `updated_at`  INT UNSIGNED,
  PRIMARY KEY (`id`),
  KEY `idx_stages_event_sport` (`event_id`, `sport_id`),
  CONSTRAINT `fk_stages_event` FOREIGN KEY (`event_id`) REFERENCES `events`(`id`),
  CONSTRAINT `fk_stages_sport` FOREIGN KEY (`sport_id`) REFERENCES `sports`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Giai đoạn thi đấu: vòng loại, chung kết, play-off';


-- ============================================================
-- 39. SPORT_STAGE_TEAMS — Đội tham gia từng giai đoạn
-- ============================================================
-- Quản lý đội nào vào vòng nào (vòng loại, chung kết...)
CREATE TABLE `sport_stage_teams` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `stage_id`         INT UNSIGNED NOT NULL,
  `team_id`          INT UNSIGNED NOT NULL,
  `entry_type`       ENUM('registered','promoted','playoff_winner') NOT NULL DEFAULT 'registered'
                     COMMENT 'registered=đăng ký, promoted=thăng hạng từ vòng trước, playoff_winner=thắng play-off',
  `qualified_from`   INT UNSIGNED COMMENT 'stage_id vòng trước (nếu promoted)',
  `seed`             INT          COMMENT 'Hạt giống (seeding)',
  `final_rank`       INT          COMMENT 'Xếp hạng cuối vòng này',
  `status`           ENUM('active','eliminated','withdrawn') NOT NULL DEFAULT 'active',
  `note`             TEXT,
  `created_at`       INT UNSIGNED,
  `updated_at`       INT UNSIGNED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_stage_team` (`stage_id`, `team_id`),
  KEY `idx_sst_entry` (`entry_type`),
  CONSTRAINT `fk_sst_stage` FOREIGN KEY (`stage_id`) REFERENCES `sport_stages`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sst_team` FOREIGN KEY (`team_id`) REFERENCES `sport_teams`(`id`),
  CONSTRAINT `fk_sst_qualified` FOREIGN KEY (`qualified_from`) REFERENCES `sport_stages`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Đội tham gia từng giai đoạn thi đấu';


-- ============================================================
-- 40. BEAUTY_CONTESTS — Cuộc thi sắc đẹp (Miss)
-- ============================================================
CREATE TABLE `beauty_contests` (
  `id`                    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id`              INT UNSIGNED NOT NULL,
  `name`                  VARCHAR(255) NOT NULL COMMENT 'Miss Đại hội 2026',
  `description`           TEXT,
  `gender`                ENUM('female') NOT NULL DEFAULT 'female' COMMENT 'Chỉ dành cho nữ',
  `age_min`               INT          COMMENT 'Tuổi tối thiểu',
  `age_max`               INT          COMMENT 'Tuổi tối đa',
  `registration_open_at`  INT UNSIGNED,
  `registration_close_at` INT UNSIGNED,
  `contest_date`          DATE,
  `location`              VARCHAR(255),
  `max_per_org`           INT          COMMENT 'Số thí sinh tối đa mỗi đơn vị',
  `candidate_prefix`      VARCHAR(10)  COMMENT 'Tiền tố SBD, VD: MS',
  `candidate_start`       INT          NOT NULL DEFAULT 1,
  `is_active`             TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`            INT UNSIGNED,
  `updated_at`            INT UNSIGNED,
  PRIMARY KEY (`id`),
  KEY `idx_bc_event` (`event_id`),
  CONSTRAINT `fk_bc_event` FOREIGN KEY (`event_id`) REFERENCES `events`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Cuộc thi sắc đẹp - chỉ dành cho nữ';


-- ============================================================
-- 41. BEAUTY_CONTESTANTS — Thí sinh thi Miss
-- ============================================================
CREATE TABLE `beauty_contestants` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `contest_id`       INT UNSIGNED NOT NULL,
  `attendee_id`      INT UNSIGNED NOT NULL,
  `candidate_number` VARCHAR(20)  UNIQUE COMMENT 'Số báo danh, VD: MS01',
  `height_cm`        DECIMAL(5,2) COMMENT 'Chiều cao (cm)',
  `weight_kg`        DECIMAL(5,2) COMMENT 'Cân nặng (kg)',
  `measurements`     VARCHAR(50)  COMMENT 'Số đo 3 vòng (nếu cần)',
  `talent`           VARCHAR(255) COMMENT 'Tài năng',
  `bio`              TEXT         COMMENT 'Tiểu sử ngắn',
  `photo_portrait`   VARCHAR(500) COMMENT 'Ảnh chân dung',
  `photo_full_body`  VARCHAR(500) COMMENT 'Ảnh toàn thân',
  `status`           ENUM('registered','confirmed','withdrawn','disqualified') NOT NULL DEFAULT 'registered',
  `final_rank`       INT          COMMENT 'Xếp hạng cuối cùng',
  `award`            VARCHAR(255) COMMENT 'Giải thưởng (Hoa hậu, Á hậu 1, 2...)',
  `registered_at`    INT UNSIGNED,
  `created_at`       INT UNSIGNED,
  `updated_at`       INT UNSIGNED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_bc_attendee` (`contest_id`, `attendee_id`),
  KEY `idx_bcon_status` (`status`),
  CONSTRAINT `fk_bcon_contest` FOREIGN KEY (`contest_id`) REFERENCES `beauty_contests`(`id`),
  CONSTRAINT `fk_bcon_attendee` FOREIGN KEY (`attendee_id`) REFERENCES `attendees`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Thí sinh thi Miss';


-- ============================================================
-- 42. BEAUTY_ROUNDS — Vòng thi Miss
-- ============================================================
CREATE TABLE `beauty_rounds` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `contest_id`  INT UNSIGNED NOT NULL,
  `name`        VARCHAR(100) NOT NULL COMMENT 'Áo dài, Bikini, Tài năng, Ứng xử',
  `round_type`  ENUM('ao_dai','bikini','talent','qa','final') NOT NULL,
  `round_order` INT          NOT NULL DEFAULT 1,
  `max_score`   DECIMAL(5,2) NOT NULL DEFAULT 10.00 COMMENT 'Điểm tối đa',
  `weight`      DECIMAL(3,2) NOT NULL DEFAULT 1.00 COMMENT 'Trọng số điểm',
  `start_time`  INT UNSIGNED,
  `end_time`    INT UNSIGNED,
  `note`        TEXT,
  `created_at`  INT UNSIGNED,
  PRIMARY KEY (`id`),
  KEY `idx_br_contest` (`contest_id`),
  CONSTRAINT `fk_br_contest` FOREIGN KEY (`contest_id`) REFERENCES `beauty_contests`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 43. BEAUTY_SCORES — Điểm chấm Miss
-- ============================================================
CREATE TABLE `beauty_scores` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `round_id`      INT UNSIGNED NOT NULL,
  `contestant_id` INT UNSIGNED NOT NULL,
  `judge_id`      INT UNSIGNED NOT NULL COMMENT 'users.id - giám khảo',
  `score`         DECIMAL(5,2) NOT NULL,
  `note`          TEXT,
  `scored_at`     INT UNSIGNED,
  `created_at`    INT UNSIGNED,
  `updated_at`    INT UNSIGNED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_score_judge` (`round_id`, `contestant_id`, `judge_id`),
  KEY `idx_bs_contestant` (`contestant_id`),
  CONSTRAINT `fk_bs_round` FOREIGN KEY (`round_id`) REFERENCES `beauty_rounds`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bs_contestant` FOREIGN KEY (`contestant_id`) REFERENCES `beauty_contestants`(`id`),
  CONSTRAINT `fk_bs_judge` FOREIGN KEY (`judge_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 44. TALENT_SHOWS — Cuộc thi văn nghệ
-- ============================================================
CREATE TABLE `talent_shows` (
  `id`                    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id`              INT UNSIGNED NOT NULL,
  `name`                  VARCHAR(255) NOT NULL COMMENT 'Hội diễn văn nghệ 2026',
  `description`           TEXT,
  `registration_open_at`  INT UNSIGNED,
  `registration_close_at` INT UNSIGNED,
  `show_date`             DATE,
  `location`              VARCHAR(255),
  `max_entries_per_org`   INT          COMMENT 'Số tiết mục tối đa mỗi đơn vị',
  `is_active`             TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`            INT UNSIGNED,
  `updated_at`            INT UNSIGNED,
  PRIMARY KEY (`id`),
  KEY `idx_ts_event` (`event_id`),
  CONSTRAINT `fk_ts_event` FOREIGN KEY (`event_id`) REFERENCES `events`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Cuộc thi văn nghệ';


-- ============================================================
-- 45. TALENT_CATEGORIES — Thể loại văn nghệ
-- ============================================================
CREATE TABLE `talent_categories` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`        VARCHAR(50)  NOT NULL UNIQUE,
  `name`        VARCHAR(100) NOT NULL,
  `type`        ENUM('solo','group') NOT NULL DEFAULT 'solo'
                COMMENT 'solo=cá nhân (đơn ca), group=nhóm (tốp ca, múa)',
  `min_members` INT          NOT NULL DEFAULT 1,
  `max_members` INT          NOT NULL DEFAULT 1,
  `max_duration_seconds` INT COMMENT 'Thời lượng tối đa (giây)',
  `description` TEXT,
  `sort_order`  INT          NOT NULL DEFAULT 0,
  `created_at`  INT UNSIGNED,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `talent_categories` (`code`, `name`, `type`, `min_members`, `max_members`, `sort_order`) VALUES
('solo_singing', 'Đơn ca', 'solo', 1, 1, 1),
('group_singing', 'Tốp ca', 'group', 2, 10, 2),
('solo_dance', 'Múa đơn', 'solo', 1, 1, 3),
('group_dance', 'Múa nhóm', 'group', 2, 20, 4),
('instrument', 'Nhạc cụ', 'solo', 1, 5, 5),
('comedy', 'Tiểu phẩm/Kịch', 'group', 2, 10, 6),
('other', 'Tài năng khác', 'solo', 1, 10, 99);


-- ============================================================
-- 46. TALENT_ENTRIES — Tiết mục đăng ký
-- ============================================================
CREATE TABLE `talent_entries` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `show_id`          INT UNSIGNED NOT NULL,
  `category_id`      INT UNSIGNED NOT NULL,
  `organization_id`  INT UNSIGNED NOT NULL COMMENT 'Đơn vị đăng ký',
  `title`            VARCHAR(255) NOT NULL COMMENT 'Tên tiết mục',
  `description`      TEXT,
  `duration_seconds` INT          COMMENT 'Thời lượng dự kiến (giây)',
  `music_path`       VARCHAR(500) COMMENT 'File nhạc nền',
  `video_path`       VARCHAR(500) COMMENT 'Video preview (nếu có)',
  `performance_order` INT         COMMENT 'Thứ tự biểu diễn',
  `status`           ENUM('draft','submitted','approved','rejected','performed') NOT NULL DEFAULT 'draft',
  `final_score`      DECIMAL(5,2) COMMENT 'Điểm cuối cùng',
  `final_rank`       INT          COMMENT 'Xếp hạng',
  `award`            VARCHAR(255) COMMENT 'Giải thưởng',
  `note`             TEXT,
  `submitted_at`     INT UNSIGNED,
  `approved_by`      INT UNSIGNED,
  `approved_at`      INT UNSIGNED,
  `created_at`       INT UNSIGNED,
  `updated_at`       INT UNSIGNED,
  PRIMARY KEY (`id`),
  KEY `idx_te_show` (`show_id`),
  KEY `idx_te_category` (`category_id`),
  KEY `idx_te_org` (`organization_id`),
  KEY `idx_te_status` (`status`),
  CONSTRAINT `fk_te_show` FOREIGN KEY (`show_id`) REFERENCES `talent_shows`(`id`),
  CONSTRAINT `fk_te_category` FOREIGN KEY (`category_id`) REFERENCES `talent_categories`(`id`),
  CONSTRAINT `fk_te_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tiết mục văn nghệ đăng ký';


-- ============================================================
-- 47. TALENT_ENTRY_MEMBERS — Thành viên tiết mục
-- ============================================================
-- Dùng cho tốp ca, nhóm múa... (nhiều người)
CREATE TABLE `talent_entry_members` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `entry_id`    INT UNSIGNED NOT NULL,
  `attendee_id` INT UNSIGNED NOT NULL,
  `role`        VARCHAR(100) COMMENT 'Vai diễn / Vị trí (lead vocal, dancer...)',
  `is_lead`     TINYINT(1)   NOT NULL DEFAULT 0 COMMENT 'Người đại diện tiết mục',
  `sort_order`  INT          NOT NULL DEFAULT 0,
  `created_at`  INT UNSIGNED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_entry_member` (`entry_id`, `attendee_id`),
  CONSTRAINT `fk_tem_entry` FOREIGN KEY (`entry_id`) REFERENCES `talent_entries`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tem_attendee` FOREIGN KEY (`attendee_id`) REFERENCES `attendees`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 48. TALENT_SCORES — Điểm chấm văn nghệ
-- ============================================================
CREATE TABLE `talent_scores` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `entry_id`    INT UNSIGNED NOT NULL,
  `judge_id`    INT UNSIGNED NOT NULL COMMENT 'users.id - giám khảo',
  `score`       DECIMAL(5,2) NOT NULL,
  `criteria`    VARCHAR(100) COMMENT 'Tiêu chí chấm (nội dung, kỹ thuật, sáng tạo...)',
  `note`        TEXT,
  `scored_at`   INT UNSIGNED,
  `created_at`  INT UNSIGNED,
  `updated_at`  INT UNSIGNED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_talent_score` (`entry_id`, `judge_id`, `criteria`),
  CONSTRAINT `fk_tsc_entry` FOREIGN KEY (`entry_id`) REFERENCES `talent_entries`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tsc_judge` FOREIGN KEY (`judge_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

### 3.3 Entity Relationship Diagram

```
┌─────────────────┐     ┌──────────────────┐     ┌────────────────────┐
│  organizations  │────<│  unit_accounts   │     │      users         │
│                 │1   1│                  │     │  (Admin/HR/BTC)    │
└────────┬────────┘     └──────────────────┘     └────────────────────┘
         │1                                                │
         │                                                 │ reviews
         │N                                                ▼
┌────────┴────────┐     ┌──────────────────┐     ┌────────────────────┐
│  registrations  │────<│registration_period│     │    audit_logs      │
│                 │N   1│                  │     │                    │
└────────┬────────┘     └──────────────────┘     └────────────────────┘
         │1
         │N
┌────────┴────────┐     ┌──────────────────┐     ┌────────────────────┐
│   attendees     │────<│ attendee_roles   │────>│      roles         │
│                 │1   N│                  │N   1│                    │
└────────┬────────┘     └──────────────────┘     └────────────────────┘
         │1
    ┌────┴────┬─────────────┬──────────────┬─────────────┐
    │         │             │              │             │
    ▼1        ▼N            ▼N             ▼N            ▼N
┌───────┐ ┌───────────┐ ┌──────────┐ ┌───────────┐ ┌───────────┐
│badges │ │competition│ │sport_team│ │banquet_   │ │meal_      │
│       │ │_registra- │ │_members  │ │seats      │ │cutoffs    │
└───────┘ │tions      │ └────┬─────┘ └─────┬─────┘ └─────┬─────┘
          └─────┬─────┘      │N            │N            │N
                │N           │1            │1            │1
                │1      ┌────┴─────┐  ┌────┴─────┐  ┌────┴─────┐
          ┌─────┴─────┐ │sport_    │  │banquet_  │  │  meals   │
          │competitions│ │teams     │  │tables    │  │          │
          └─────┬─────┘ └────┬─────┘  └────┬─────┘  └──────────┘
                │1           │N            │N
                │N           │1            │1
          ┌─────┴─────┐ ┌────┴─────┐  ┌────┴─────┐
          │competition│ │  sports  │  │banquet_  │
          │_rounds    │ │          │  │events    │
          └───────────┘ └────┬─────┘  └──────────┘
                             │1
                             │N
                        ┌────┴─────┐
                        │sport_    │
                        │matches   │
                        └────┬─────┘
                             │1
                             │1
                        ┌────┴─────┐
                        │sport_    │
                        │match_    │
                        │results   │
                        └──────────┘
```

### 3.4 Relationships tóm tắt

```
# Core entities
events (1) ─────────────── (N) event_units
events (1) ─────────────── (N) event_contents
events (1) ─────────────── (N) event_sports
events (1) ─────────────── (N) event_competitions
events (1) ─────────────── (N) registrations
events (1) ─────────────── (N) attendees

organizations (1) ────────  (1) unit_accounts
organizations (1) ────────  (N) staff
organizations (1) ────────  (N) registrations
organizations (1) ────────  (N) sport_teams

# Registration flow
registration_periods (1) ── (N) registrations
registrations (1) ─────────  (N) registration_details
registrations (1) ─────────  (N) attendees

# Attendee relationships
attendees (N) ─────────────  (1) staff             [staff_id FK]
attendees (1) ─────────────  (1) badges
attendees (N) ─────────────  (M) roles              [attendee_roles]
attendees (N) ─────────────  (M) competitions       [competition_registrations]
attendees (N) ─────────────  (M) banquet_tables     [banquet_seats]
attendees (N) ─────────────  (M) meals              [meal_cutoffs, meal_attendees]
attendees (N) ─────────────  (M) sport_teams        [sport_team_members]

# Contents & Activities
contents (1) ──────────────  (N) event_contents
contents (1) ──────────────  (N) registration_details

# Sports hierarchy
sports (N) ────────────────  (1) sports             [parent_id self-reference]
sports (1) ────────────────  (N) event_sports
sports (1) ────────────────  (N) sport_teams
sports (1) ────────────────  (N) sport_matches
sport_matches (1) ─────────  (1) sport_match_results

# Competitions
competitions (1) ──────────  (N) event_competitions
competitions (1) ──────────  (N) competition_rounds
competitions (1) ──────────  (N) competition_registrations

# Banquet & Meals
banquet_events (1) ────────  (N) banquet_tables
banquet_tables (1) ────────  (N) banquet_seats

meals (1) ─────────────────  (N) meal_tables
meals (1) ─────────────────  (N) meal_attendees
meals (1) ─────────────────  (N) meal_cutoffs
meals (1) ─────────────────  (N) meal_checkins
```

---

## 4. Kiến Trúc Hệ Thống

### 4.1 Tổng quan kiến trúc (Yii1 Frontend + External API + Portal SSO)

```
┌─────────────────────────────────────────────────────────────────────────┐
│                     portal.muongthanh.vn                                 │
│  ┌─────────────────────────────────────────────────────────────────┐    │
│  │  User đăng nhập → Click button "Event Regis"                    │    │
│  │  → Redirect với JWT token trong URL                             │    │
│  └─────────────────────────────────────────────────────────────────┘    │
└───────────────────────────────┬─────────────────────────────────────────┘
                                │
                                │ redirect?token=<JWT>
                                ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                      FRONTEND (Yii1 MVC)                                 │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐                   │
│  │ AuthHandler  │  │ ApiClient    │  │ Controllers  │                   │
│  │ - validate   │  │ - API key    │  │ + Views      │                   │
│  │   JWT token  │  │ - gọi API    │  │              │                   │
│  │ - create     │  │   external   │  │              │                   │
│  │   session    │  │              │  │              │                   │
│  └──────┬───────┘  └──────┬───────┘  └──────────────┘                   │
│         │                 │                                              │
│         │    ┌────────────┴────────────────────────────┐                │
│         │    │  SESSION (Yii CHttpSession)             │                │
│         │    │  - user info từ JWT                     │                │
│         │    │  - permissions                          │                │
│         │    │  - TTL với auto-refresh khi active      │                │
│         │    └─────────────────────────────────────────┘                │
└─────────┼───────────────────┼───────────────────────────────────────────┘
          │                   │
          │ refresh           │ API Key (config)
          │ token             │
          ▼                   ▼
┌──────────────────┐   ┌──────────────────────────────────────────────────┐
│ Portal API       │   │            EXTERNAL API (endpoint riêng)         │
│ /api/token/refresh│   │  ┌─────────────┐  ┌─────────────┐               │
└──────────────────┘   │  │ REST API    │  │   MySQL     │               │
                       │  │ Endpoints   │──│   Database  │               │
                       │  └─────────────┘  └─────────────┘               │
                       └──────────────────────────────────────────────────┘
```

### 4.2 Authentication Flow (Portal Redirect + JWT)

```
┌──────────┐     ┌─────────────┐     ┌──────────────┐     ┌──────────────┐
│  User    │     │   Portal    │     │ Yii1 Frontend│     │ External API │
└────┬─────┘     └──────┬──────┘     └──────┬───────┘     └──────┬───────┘
     │                  │                   │                    │
     │ 1. Login Portal  │                   │                    │
     │─────────────────▶│                   │                    │
     │                  │                   │                    │
     │ 2. Click "Event Regis" button        │                    │
     │─────────────────▶│                   │                    │
     │                  │                   │                    │
     │ 3. Redirect: /auth/callback?token=<JWT>                   │
     │                  │──────────────────▶│                    │
     │                  │                   │                    │
     │                  │                   │ 4. Validate JWT    │
     │                  │                   │    (decode + verify)
     │                  │                   │                    │
     │                  │                   │ 5. Create Yii session
     │                  │                   │    - user info     │
     │                  │                   │    - permissions   │
     │                  │                   │    - token_expires │
     │                  │                   │    - last_activity │
     │                  │                   │                    │
     │ 6. Redirect to Dashboard             │                    │
     │◀─────────────────────────────────────│                    │
     │                  │                   │                    │
     │ 7. User action (view/edit data)      │                    │
     │─────────────────────────────────────▶│                    │
     │                  │                   │                    │
     │                  │                   │ 8. Call API        │
     │                  │                   │    + API Key       │
     │                  │                   │───────────────────▶│
     │                  │                   │                    │
     │                  │                   │ 9. Return data     │
     │                  │                   │◀───────────────────│
     │                  │                   │                    │
     │ 10. Render view                      │                    │
     │◀─────────────────────────────────────│                    │
     │                  │                   │                    │
```

### 4.3 JWT Token Payload từ Portal

```json
{
  "sub": "12345",
  "username": "nguyenvana",
  "full_name": "Nguyễn Văn A",
  "email": "nguyenvana@muongthanh.vn",
  "unit_code": "HN01",
  "permissions": {
    "event": "1 1 1 1",
    "registration": "1 1 1 0",
    "attendee": "1 1 1 1",
    "badge": "1 0 0 0",
    "sport": "1 1 1 1",
    "competition": "1 1 1 1",
    "meal": "1 1 0 0",
    "report": "1 0 0 0"
  },
  "iat": 1714838400,
  "exp": 1714842000
}
```

**Permission format**: `"controller": "C R U D"` (1=có quyền, 0=không)
| Position | Operation | Ví dụ actions |
|----------|-----------|---------------|
| 0 | Create | create, store |
| 1 | Read | index, view, list |
| 2 | Update | edit, update |
| 3 | Delete | delete, destroy |

### 4.4 Session Management & Token Refresh

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         SESSION LIFECYCLE                                │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  ┌─────────────────┐                                                    │
│  │ User action     │◀──────────────────────────────────────────────┐    │
│  └────────┬────────┘                                               │    │
│           │                                                        │    │
│           ▼                                                        │    │
│  ┌─────────────────────────────────────────────────────┐           │    │
│  │ Check session còn hợp lệ?                           │           │    │
│  │ - session tồn tại?                                  │           │    │
│  │ - last_activity + SESSION_TIMEOUT > now?            │           │    │
│  └────────┬──────────────────────────┬─────────────────┘           │    │
│           │                          │                             │    │
│         YES                         NO                             │    │
│           │                          │                             │    │
│           ▼                          ▼                             │    │
│  ┌─────────────────────────┐  ┌─────────────────────────┐          │    │
│  │ Cần refresh token?      │  │ Redirect → Portal       │          │    │
│  │ (last_refresh +         │  │ (session hết hạn)       │          │    │
│  │  REFRESH_INTERVAL > now)│  └─────────────────────────┘          │    │
│  └────────┬────────────────┘                                       │    │
│           │                                                        │    │
│         YES                                                        │    │
│           │                                                        │    │
│           ▼                                                        │    │
│  ┌─────────────────────────────────────────────────────┐           │    │
│  │ Call Portal API /api/token/refresh                  │           │    │
│  │ → Nhận JWT mới                                      │           │    │
│  │ → Cập nhật session (permissions, expires...)        │           │    │
│  │ → Cập nhật last_refresh = now                       │           │    │
│  └────────┬────────────────────────────────────────────┘           │    │
│           │                                                        │    │
│           ▼                                                        │    │
│  ┌─────────────────────────────────────────────────────┐           │    │
│  │ Update last_activity = now                          │           │    │
│  │ Tiếp tục xử lý request                              │──────────┘    │
│  └─────────────────────────────────────────────────────┘                │
│                                                                          │
└─────────────────────────────────────────────────────────────────────────┘

Config values:
- SESSION_TIMEOUT = 30 phút (không hoạt động → hết session)
- REFRESH_INTERVAL = 15 phút (mỗi 15 phút refresh token nếu active)
```

### 4.5 Cấu trúc thư mục dự án

```
eventregis/
├── protected/                        # Yii1 application core
│   ├── components/
│   │   ├── Controller.php            # Base controller
│   │   ├── AdminController.php       # Admin base controller (check session)
│   │   ├── AuthHandler.php           # JWT validation + session management
│   │   ├── ApiClient.php             # Gọi External API với API Key
│   │   ├── PermissionHelper.php      # Check CRUD permissions
│   │   ├── MyHelper.php              # Utility functions
│   │   └── ...
│   │
│   ├── config/
│   │   ├── main.php                  # Main config
│   │   ├── params.php                # API key, Portal URL, session config
│   │   └── console.php
│   │
│   ├── controllers/                  # Admin controllers
│   │   ├── AuthController.php        # /auth/callback (nhận JWT từ Portal)
│   │   ├── DefaultController.php     # Dashboard
│   │   ├── EventController.php
│   │   ├── RegistrationController.php
│   │   ├── AttendeeController.php
│   │   ├── BadgeController.php
│   │   ├── SportController.php
│   │   ├── CompetitionController.php
│   │   ├── MealController.php
│   │   └── ...
│   │
│   ├── models/                       # AR Models (nếu query DB local)
│   │   └── ...
│   │
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── main.php              # Main layout
│   │   │   └── column2.php
│   │   ├── auth/
│   │   │   └── callback.php
│   │   ├── default/
│   │   │   └── index.php             # Dashboard
│   │   ├── event/
│   │   ├── registration/
│   │   ├── attendee/
│   │   └── ...
│   │
│   ├── extensions/
│   │   ├── jwt/                      # JWT decode library
│   │   ├── booster/                  # Bootstrap widgets
│   │   └── ...
│   │
│   └── runtime/                      # Logs, cache
│
├── themes/
│   └── hope-ui/                      # Dashboard theme
│       ├── assets/
│       └── views/layouts/
│
├── uploads/                          # Uploaded files
├── badges/                           # Generated badge images
│
├── docs/
│   └── system-design.md
│
├── admin.php                         # Admin entry point
├── index.php                         # Public entry (QR scan)
└── .htaccess
```

### 4.6 Core Components (Yii1)

#### AuthHandler — Xử lý JWT từ Portal

```php
// protected/components/AuthHandler.php
class AuthHandler extends CComponent
{
    public static function handleCallback($token)
    {
        $payload = self::decodeJwt($token);
        if (!$payload) {
            throw new CHttpException(401, 'Invalid token');
        }
        
        // Tạo Yii session
        $session = Yii::app()->session;
        $session['user'] = [
            'id' => $payload['sub'],
            'username' => $payload['username'],
            'full_name' => $payload['full_name'],
            'email' => $payload['email'],
            'unit_code' => $payload['unit_code'],
        ];
        $session['permissions'] = $payload['permissions'];
        $session['token_exp'] = $payload['exp'];
        $session['last_activity'] = time();
        $session['last_refresh'] = time();
        
        return true;
    }
    
    public static function checkSession()
    {
        $session = Yii::app()->session;
        $params = Yii::app()->params;
        
        // Session không tồn tại
        if (!isset($session['user'])) {
            return false;
        }
        
        $now = time();
        $lastActivity = $session['last_activity'] ?? 0;
        $sessionTimeout = $params['sessionTimeout'] ?? 1800; // 30 phút
        
        // Kiểm tra session timeout (không hoạt động)
        if ($now - $lastActivity > $sessionTimeout) {
            Yii::app()->session->destroy();
            return false;
        }
        
        // Cập nhật last_activity
        $session['last_activity'] = $now;
        
        // Kiểm tra cần refresh token không
        $lastRefresh = $session['last_refresh'] ?? 0;
        $refreshInterval = $params['refreshInterval'] ?? 900; // 15 phút
        
        if ($now - $lastRefresh > $refreshInterval) {
            self::refreshToken();
        }
        
        return true;
    }
    
    public static function refreshToken()
    {
        $params = Yii::app()->params;
        $session = Yii::app()->session;
        
        $ch = curl_init($params['portalRefreshUrl']);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'user_id' => $session['user']['id'],
            ]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-API-Key: ' . $params['portalApiKey'],
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            $session['permissions'] = $data['permissions'] ?? $session['permissions'];
            $session['token_exp'] = $data['exp'] ?? $session['token_exp'];
            $session['last_refresh'] = time();
        }
    }
    
    private static function decodeJwt($token)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;
        
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        if (!$payload) return null;
        
        // Kiểm tra expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }
        
        return $payload;
    }
}
```

#### ApiClient — Gọi External API

```php
// protected/components/ApiClient.php
class ApiClient extends CComponent
{
    private static $baseUrl;
    private static $apiKey;
    
    public static function init()
    {
        self::$baseUrl = Yii::app()->params['externalApiUrl'];
        self::$apiKey = Yii::app()->params['externalApiKey'];
    }
    
    public static function get($endpoint, $params = [])
    {
        return self::request('GET', $endpoint, $params);
    }
    
    public static function post($endpoint, $data = [])
    {
        return self::request('POST', $endpoint, [], $data);
    }
    
    public static function put($endpoint, $data = [])
    {
        return self::request('PUT', $endpoint, [], $data);
    }
    
    public static function delete($endpoint)
    {
        return self::request('DELETE', $endpoint);
    }
    
    private static function request($method, $endpoint, $params = [], $data = null)
    {
        self::init();
        
        $url = self::$baseUrl . $endpoint;
        if ($params) {
            $url .= '?' . http_build_query($params);
        }
        
        $ch = curl_init($url);
        
        $headers = [
            'Content-Type: application/json',
            'X-API-Key: ' . self::$apiKey,
        ];
        
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
        ];
        
        switch ($method) {
            case 'POST':
                $options[CURLOPT_POST] = true;
                $options[CURLOPT_POSTFIELDS] = json_encode($data);
                break;
            case 'PUT':
                $options[CURLOPT_CUSTOMREQUEST] = 'PUT';
                $options[CURLOPT_POSTFIELDS] = json_encode($data);
                break;
            case 'DELETE':
                $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                break;
        }
        
        curl_setopt_array($ch, $options);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new CException("API Error: {$error}");
        }
        
        return [
            'code' => $httpCode,
            'data' => json_decode($response, true),
        ];
    }
}
```

#### AdminController — Base controller cho Admin

```php
// protected/components/AdminController.php
class AdminController extends Controller
{
    public $layout = '//layouts/admin';
    
    public function init()
    {
        parent::init();
        
        // Kiểm tra session
        if (!AuthHandler::checkSession()) {
            $this->redirectToPortal();
        }
    }
    
    protected function redirectToPortal()
    {
        $portalUrl = Yii::app()->params['portalUrl'];
        $appCode = Yii::app()->params['appCode'];
        
        $this->redirect($portalUrl . '/apps/' . $appCode);
    }
    
    protected function getUser()
    {
        return Yii::app()->session['user'];
    }
    
    protected function can($controller, $operation)
    {
        return PermissionHelper::can($controller, $operation);
    }
    
    protected function checkPermission($controller, $operation)
    {
        if (!$this->can($controller, $operation)) {
            throw new CHttpException(403, 'Access denied');
        }
    }
}
```

#### PermissionHelper — Check quyền CRUD

```php
// protected/components/PermissionHelper.php
class PermissionHelper
{
    const CREATE = 0;
    const READ = 1;
    const UPDATE = 2;
    const DELETE = 3;
    
    public static function can($controller, $operation)
    {
        $permissions = Yii::app()->session['permissions'] ?? [];
        
        if (!isset($permissions[$controller])) {
            return false;
        }
        
        $crud = explode(' ', $permissions[$controller]);
        $idx = is_string($operation) ? self::operationToIndex($operation) : $operation;
        
        return isset($crud[$idx]) && $crud[$idx] === '1';
    }
    
    private static function operationToIndex($operation)
    {
        $map = [
            'create' => self::CREATE, 'store' => self::CREATE,
            'read' => self::READ, 'index' => self::READ, 'view' => self::READ,
            'update' => self::UPDATE, 'edit' => self::UPDATE,
            'delete' => self::DELETE, 'destroy' => self::DELETE,
        ];
        return $map[$operation] ?? self::READ;
    }
}
```

#### AuthController — Nhận token từ Portal

```php
// protected/controllers/AuthController.php
class AuthController extends Controller
{
    public function actionCallback($token)
    {
        try {
            AuthHandler::handleCallback($token);
            $this->redirect(['default/index']);
        } catch (Exception $e) {
            Yii::app()->user->setFlash('error', 'Authentication failed');
            $this->redirect(Yii::app()->params['portalUrl']);
        }
    }
    
    public function actionLogout()
    {
        Yii::app()->session->destroy();
        $this->redirect(Yii::app()->params['portalUrl'] . '/logout');
    }
}
```

### 4.7 Config

```php
// protected/config/params.php
return [
    // Portal SSO
    'portalUrl' => 'https://portal.muongthanh.vn',
    'portalRefreshUrl' => 'https://portal.muongthanh.vn/api/token/refresh',
    'portalApiKey' => 'YOUR_PORTAL_API_KEY',
    'appCode' => 'eventregis',
    
    // External API
    'externalApiUrl' => 'https://api.muongthanh.vn/eventregis',
    'externalApiKey' => 'YOUR_EXTERNAL_API_KEY',
    
    // Session
    'sessionTimeout' => 1800,   // 30 phút - không hoạt động thì hết session
    'refreshInterval' => 900,   // 15 phút - refresh token nếu vẫn active
];
```

---

## 5. Các Màn Hình Chính & Luồng Nghiệp Vụ

### 5.1 Luồng Đăng Ký → Phê Duyệt → In Thẻ

```
[Đại diện đơn vị]
  1. Đăng nhập tài khoản đơn vị
  2. Kiểm tra trạng thái đợt đăng ký (còn hạn không?)
  3. Tạo phiếu đăng ký (status=draft)
  4. Thêm từng người:
     - Nhập: họ tên, chức danh, tên đơn vị hiển thị
     - Upload ảnh (JPG/PNG, tối đa 2MB, tỷ lệ 3:4 gợi ý)
     - Hệ thống preview trước
  5. Nộp phiếu (status=submitted) → hết hạn chỉnh sửa

[Admin HO / HR]
  6. Nhận danh sách đăng ký mới
  7. Review từng phiếu: xem danh sách, ảnh, thông tin
  8. Phê duyệt (status=approved):
     - Hệ thống auto-generate qr_token (unique UUID)
     - Hệ thống auto-assign badge_number (theo sequence)
  9. Hoặc từ chối (status=rejected) + ghi lý do
     → Đơn vị được phép chỉnh sửa và nộp lại

[Admin HO — In thẻ]
  10. Vào trang Badge Management
  11. Lọc theo đơn vị / toàn bộ
  12. Preview thẻ từng người (ảnh + tên + chức danh + đơn vị + QR)
  13. Xuất ảnh PNG (300 DPI, 85.6×53.98mm = 1011×638px)
  14. Xuất lô (ZIP nhiều ảnh hoặc PDF nhiều trang)
  15. Đánh dấu badge_printed=1
```

**Edge cases:**

- Đơn vị nộp rồi muốn chỉnh sửa → chỉ cho phép khi `status=draft` hoặc sau khi bị `rejected`
- HR phê duyệt một phần: không hỗ trợ phê duyệt từng người riêng lẻ — phải approve/reject toàn bộ phiếu
- Ảnh không đạt chất lượng → HR reject với lý do cụ thể
- Đơn vị đăng ký vượt `max_per_org` → hệ thống báo lỗi khi nộp

---

### 5.2 Màn hình chính

**A. Admin — Dashboard**

- Tổng số đơn vị đã đăng ký / chưa đăng ký
- Số phiếu đang chờ duyệt
- Tổng số người tham dự đã confirmed
- Số thẻ đã in / chưa in
- Timeline đợt đăng ký

**B. Unit — Trang đăng ký**

- Thông tin đợt đăng ký (hạn cuối, số người tối đa)
- Form thêm người (ảnh + thông tin)
- Bảng danh sách đã nhập (sửa/xóa khi còn draft)
- Nút Nộp đăng ký
- Trạng thái phê duyệt

**C. Admin — Badge Management**

- Bộ lọc: đơn vị, trạng thái in
- Grid ảnh thẻ preview
- Nút xuất đơn / xuất lô
- Thống kê đã in / chưa in

**D. Thi nghiệp vụ — Danh sách thí sinh**

- Lọc theo cuộc thi, vòng thi
- Danh sách: số báo danh | họ tên | đơn vị | trạng thái
- Xuất Excel/PDF danh sách phòng thi

**E. Thể thao — Bảng lịch thi đấu**

- Theo môn (tab)
- Hiển thị dạng bracket (vòng loại/knockout) hoặc bảng vòng tròn
- Kết quả trực tiếp (cập nhật live)

**F. Tiệc — Sơ đồ bàn (Canvas)**

- Canvas drag-and-drop đặt vị trí bàn
- Click bàn → xem danh sách người ngồi
- Màu sắc bàn theo loại (VIP, thường, BTC)
- Tooltip hiển thị tên người khi hover

**G. Trưởng đoàn — Báo cắt ăn**

- Danh sách bữa ăn sắp tới
- Danh sách thành viên đoàn với checkbox
- Mặc định tất cả "có ăn", tích chọn để "cắt ăn"
- Ghi lý do (tùy chọn)
- Confirm trước khi lưu
- Deadline cảnh báo nếu qua giờ cutoff

**H. Public — QR Landing Page**

- Responsive mobile
- Ảnh đại diện + tên + chức danh + đơn vị
- Tab: Thông tin cá nhân | Lịch thi nghiệp vụ | Lịch thể thao | Agenda đại hội
- Không hiển thị thông tin nhạy cảm

---

### 5.3 Luồng Thi Nghiệp Vụ

```
[BTC Thi nghiệp vụ]
  1. Tạo cuộc thi (tên, mô tả, thời gian đăng ký, prefix số báo danh)
  2. Tạo các vòng thi (vòng loại / bán kết / chung kết, địa điểm, thời gian)

[Người tham dự / Đơn vị]
  3. Trong thời gian mở đăng ký: đăng ký thi
  4. Hệ thống kiểm tra giới hạn max_per_org (nếu có)

[BTC Thi nghiệp vụ]
  5. Xem danh sách đăng ký
  6. Cấp số báo danh (bulk): hệ thống tự generate theo sequence
     → VD: prefix="NV", start=1, pad=3 → NV001, NV002...
  7. Xuất danh sách thí sinh + số báo danh (Excel/PDF)
  8. Sau thi: cập nhật kết quả (nếu cần)
```

**Edge cases:**

- Người đăng ký thi nhưng không đến (`status=no_show`)
- Huỷ đăng ký: chỉ được trước deadline
- Cùng người đăng ký 2 cuộc thi khác nhau: cho phép

---

### 5.4 Luồng Thi Đấu Thể Thao

```
[BTC Thể thao]
  1. Tạo môn thể thao
  2. Tạo đội (gán vào đơn vị hoặc đội hỗn hợp)
  3. Thêm thành viên vào đội
  4. Tạo lịch thi đấu (trận × vòng × thời gian × địa điểm)
  5. Cập nhật kết quả từng trận
  6. Hệ thống tự tính bảng xếp hạng (nếu vòng tròn)
```

---

## 6. API Endpoints

### 6.1 Public Endpoints (không cần auth)

| Method | URL                       | Mô tả                       |
| ------ | ------------------------- | --------------------------- |
| GET    | `/api/public/qr/:token`   | Thông tin từ QR code        |
| GET    | `/api/public/agenda`      | Chương trình đại hội        |
| GET    | `/api/public/sports`      | Lịch thi đấu thể thao       |
| GET    | `/api/public/competitions`| Lịch thi nghiệp vụ          |

**Response quét QR (`/api/public/qr/:token`):**

```json
{
  "success": true,
  "data": {
    "attendee": {
      "full_name": "Nguyễn Văn A",
      "position": "Giám đốc",
      "unit_label": "Chi nhánh Hà Nội",
      "photo_url": "/uploads/photos/abc123.jpg",
      "badge_number": "001",
      "roles": ["Giám đốc", "Thi nghiệp vụ"]
    },
    "competition": {
      "name": "Thi nghiệp vụ 2026",
      "candidate_number": "NV042",
      "rounds": [
        { "name": "Vòng loại", "time": "08:00 28/04/2026", "location": "Phòng A" }
      ]
    },
    "sport_schedule": [
      { "sport": "Bóng đá", "match": "HN vs HCM", "time": "14:00 28/04", "location": "Sân A" }
    ],
    "agenda": [
      { "title": "Khai mạc đại hội", "time": "08:00", "location": "Hội trường lớn" }
    ]
  }
}
```

### 6.2 REST API Endpoints (Bearer Token required)

**Event Management**
| Method | URL | Permission | Mô tả |
|--------|-----|------------|-------|
| GET | `/api/event` | event:R | Danh sách sự kiện |
| GET | `/api/event/:id` | event:R | Chi tiết sự kiện |
| POST | `/api/event` | event:C | Tạo sự kiện mới |
| PUT | `/api/event/:id` | event:U | Cập nhật sự kiện |
| DELETE | `/api/event/:id` | event:D | Xóa sự kiện |

**Registration Management**
| Method | URL | Permission | Mô tả |
|--------|-----|------------|-------|
| GET | `/api/registration` | registration:R | Danh sách đăng ký |
| GET | `/api/registration/:id` | registration:R | Chi tiết phiếu đăng ký |
| POST | `/api/registration` | registration:C | Tạo phiếu đăng ký |
| PUT | `/api/registration/:id` | registration:U | Cập nhật phiếu |
| POST | `/api/registration/:id/submit` | registration:U | Nộp phiếu |
| POST | `/api/registration/:id/approve` | registration:U | Phê duyệt |
| POST | `/api/registration/:id/reject` | registration:U | Từ chối |

**Attendee Management**
| Method | URL | Permission | Mô tả |
|--------|-----|------------|-------|
| GET | `/api/attendee` | attendee:R | Danh sách người tham dự |
| GET | `/api/attendee/:id` | attendee:R | Chi tiết người tham dự |
| POST | `/api/attendee` | attendee:C | Thêm người tham dự |
| PUT | `/api/attendee/:id` | attendee:U | Cập nhật thông tin |
| DELETE | `/api/attendee/:id` | attendee:D | Xóa người tham dự |
| POST | `/api/attendee/:id/upload-photo` | attendee:U | Upload ảnh |
| POST | `/api/attendee/:id/assign-role` | attendee:U | Gán vai trò |

**Badge Management**
| Method | URL | Permission | Mô tả |
|--------|-----|------------|-------|
| GET | `/api/badge` | badge:R | Danh sách thẻ |
| POST | `/api/badge/generate/:attendeeId` | badge:C | Tạo thẻ |
| POST | `/api/badge/generate-batch` | badge:C | Tạo thẻ hàng loạt |
| GET | `/api/badge/export/:id` | badge:R | Xuất ảnh thẻ |
| GET | `/api/badge/export-batch` | badge:R | Xuất ZIP nhiều thẻ |

**Sport Management**
| Method | URL | Permission | Mô tả |
|--------|-----|------------|-------|
| GET | `/api/sport` | sport:R | Danh sách môn |
| POST | `/api/sport` | sport:C | Tạo môn mới |
| GET | `/api/sport/team` | sport:R | Danh sách đội |
| POST | `/api/sport/team` | sport:C | Tạo đội |
| GET | `/api/sport/match` | sport:R | Danh sách trận đấu |
| POST | `/api/sport/match` | sport:C | Tạo trận đấu |
| PUT | `/api/sport/match/:id/result` | sport:U | Cập nhật kết quả |

**Competition Management**
| Method | URL | Permission | Mô tả |
|--------|-----|------------|-------|
| GET | `/api/competition` | competition:R | Danh sách cuộc thi |
| POST | `/api/competition` | competition:C | Tạo cuộc thi |
| POST | `/api/competition/:id/assign-numbers` | competition:U | Cấp số báo danh |
| GET | `/api/competition/:id/export` | competition:R | Xuất danh sách |

**Meal Management**
| Method | URL | Permission | Mô tả |
|--------|-----|------------|-------|
| GET | `/api/meal` | meal:R | Danh sách bữa ăn |
| POST | `/api/meal` | meal:C | Tạo bữa ăn |
| GET | `/api/meal/:id/attendees` | meal:R | DS người tham gia |
| POST | `/api/meal/:id/cutoff` | meal:U | Báo cắt ăn |
| GET | `/api/meal/report` | meal:R | Báo cáo suất ăn |

**Banquet Management**
| Method | URL | Permission | Mô tả |
|--------|-----|------------|-------|
| GET | `/api/banquet` | banquet:R | Danh sách tiệc |
| POST | `/api/banquet` | banquet:C | Tạo sự kiện tiệc |
| GET | `/api/banquet/:id/tables` | banquet:R | DS bàn tiệc |
| POST | `/api/banquet/:id/assign-seat` | banquet:U | Phân chỗ ngồi |

### 6.3 Response Format

**Success Response:**
```json
{
  "success": true,
  "data": { ... },
  "message": "Optional message"
}
```

**Error Response:**
```json
{
  "success": false,
  "error": "Error message"
}
```

**Paginated Response:**
```json
{
  "success": true,
  "data": [ ... ],
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 150,
    "total_pages": 8
  }
}
```

---

## 7. Bảo Mật

### 7.1 Xác thực qua SSO (OAuth2)

| Layer | Cơ chế | Ghi chú |
|-------|--------|---------|
| **Authentication** | OAuth2 + JWT Bearer Token | Token từ portal.muongthanh.vn |
| **Authorization** | Permission-based (CRUD) | Permission trả về cùng token |
| **Session** | Server-side session | Lưu user + permissions, không lưu DB |
| **Token Refresh** | Refresh token | Auto refresh trước khi hết hạn |
| **Public API** | Không cần auth | Chỉ `/api/public/*` endpoints |

### 7.2 Token Flow

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  Frontend   │     │   Portal    │     │  Backend    │
└──────┬──────┘     └──────┬──────┘     └──────┬──────┘
       │                   │                   │
       │ 1. Login redirect │                   │
       │──────────────────▶│                   │
       │                   │                   │
       │ 2. User auth      │                   │
       │◀──────────────────│                   │
       │                   │                   │
       │ 3. Exchange code  │                   │
       │──────────────────▶│                   │
       │                   │                   │
       │ 4. JWT + permissions                  │
       │◀──────────────────│                   │
       │                   │                   │
       │ 5. API call with Bearer token         │
       │──────────────────────────────────────▶│
       │                   │                   │
       │                   │ 6. Verify token   │
       │                   │◀──────────────────│
       │                   │                   │
       │                   │ 7. Valid + payload│
       │                   │──────────────────▶│
       │                   │                   │
       │ 8. Response                           │
       │◀──────────────────────────────────────│
```

### 7.3 Permission Matrix (dựa trên CRUD format)

| Controller | Admin | HR | Competition BTC | Sports BTC | Banquet BTC | Unit | Team Lead |
|------------|-------|-----|-----------------|------------|-------------|------|-----------|
| `event` | 1 1 1 1 | 1 1 0 0 | 1 0 0 0 | 1 0 0 0 | 1 0 0 0 | 1 0 0 0 | 1 0 0 0 |
| `registration` | 1 1 1 1 | 1 1 1 0 | 0 1 0 0 | 0 1 0 0 | 0 1 0 0 | 1 1 1 0 | 0 1 0 0 |
| `attendee` | 1 1 1 1 | 1 1 1 0 | 0 1 0 0 | 0 1 0 0 | 0 1 0 0 | 1 1 1 1 | 0 1 0 0 |
| `badge` | 1 1 1 1 | 1 1 0 0 | 0 0 0 0 | 0 0 0 0 | 0 0 0 0 | 0 1 0 0 | 0 0 0 0 |
| `sport` | 1 1 1 1 | 0 1 0 0 | 0 0 0 0 | 1 1 1 1 | 0 0 0 0 | 0 1 0 0 | 0 1 0 0 |
| `competition` | 1 1 1 1 | 0 1 0 0 | 1 1 1 1 | 0 0 0 0 | 0 0 0 0 | 0 1 0 0 | 0 1 0 0 |
| `meal` | 1 1 1 1 | 0 1 0 0 | 0 0 0 0 | 0 0 0 0 | 0 1 0 0 | 0 1 0 0 | 0 1 1 0 |
| `banquet` | 1 1 1 1 | 0 1 0 0 | 0 0 0 0 | 0 0 0 0 | 1 1 1 1 | 0 1 0 0 | 0 1 0 0 |
| `report` | 1 1 0 0 | 1 1 0 0 | 0 1 0 0 | 0 1 0 0 | 0 1 0 0 | 0 1 0 0 | 0 0 0 0 |

**Format**: `C R U D` (Create Read Update Delete) — `1`=có quyền, `0`=không

### 7.4 Các quy tắc bảo mật quan trọng

**Token & Session:**
- JWT token verify với Portal mỗi request (hoặc cache verify result ngắn hạn)
- Session chỉ lưu user info + permissions, không lưu vào DB local
- Refresh token tự động trước khi access token hết hạn (60s trước)
- Logout clear cả localStorage và server session

**Data Access:**
- User chỉ query được data thuộc `unit_code` của mình (trừ Admin)
- `qr_token` là UUID ngẫu nhiên, không chứa ID hay thông tin nhạy cảm
- API response không trả về sensitive fields (password, internal IDs)

**Upload & Files:**
- Kiểm tra MIME type thực (không chỉ extension)
- Giới hạn 2MB per file
- Rename file random, lưu ngoài webroot
- Chỉ serve qua API endpoint có auth check

**API Security:**
- CORS chỉ cho phép frontend domain
- Rate limiting: 100 requests/minute per user
- Request body limit: 10MB
- SQL injection prevention qua Yii1 parameterized queries

---

## 8. Xử Lý Thẻ Tham Dự (Badge Generation)

### 8.1 Quy trình tạo ảnh thẻ

```
[Input]
  - Ảnh chân dung attendee (từ uploads/)
  - Thông tin: tên, chức danh, đơn vị, số thẻ
  - Danh sách role (để hiển thị nhãn màu)
  - QR Code (sinh từ qr_token → URL: https://event.domain.vn/qr/<token>)

[Processing — PHP GD hoặc Imagick]
  1. Load template thẻ (PNG nền)
  2. Resize ảnh chân dung → crop tỷ lệ, đặt vào vùng ảnh
  3. In text: tên (font lớn, bold), chức danh, đơn vị, số thẻ
  4. Sinh QR code (dùng thư viện endroid/qr-code hoặc BaconQrCode)
     → Kích thước QR: ~25×25mm trên thẻ (≈295px tại 300DPI)
  5. Composite QR vào góc thẻ
  6. In nhãn màu vai trò (nếu có)

[Output]
  - File PNG: 1011×638px (85.6×53.98mm @ 300DPI)
  - Lưu tại: /data/badges/<attendee_id>.png
  - Cập nhật badges.generated_path + badges.generated_at
```

### 8.2 Xuất lô để in

- **Xuất từng thẻ**: trả về file PNG đơn
- **Xuất ZIP**: đóng gói nhiều PNG, đặt tên `<badge_number>_<full_name>.png`
- **Xuất PDF A4**: layout 2×4 thẻ/trang (hoặc 1 thẻ/trang tùy cấu hình)
- Sau khi xuất: cập nhật `badges.print_count += 1`, `badges.last_printed_at = now()`

---

## 9. Cấu Hình & Triển Khai

### 9.1 Tech Stack

**Backend (API):**
| Component | Lựa chọn | Ghi chú |
|-----------|----------|---------|
| Framework | Yii 1.1.x | PHP 7.4 |
| PHP | 7.4+ | GD2, mbstring, pdo_mysql, curl, fileinfo |
| Database | MySQL 5.7+ | InnoDB engine |
| Session | PHP Session | Lưu user + permissions từ Portal |
| Image | PHP GD2 | Badge generation |
| QR Code | `phpqrcode` | QR code generation |
| Export | `PhpSpreadsheet` | Excel export |
| PDF | `TCPDF` | PDF export |

**Frontend (SPA):**
| Component | Lựa chọn | Ghi chú |
|-----------|----------|---------|
| Framework | React 18+ | Vite build |
| Language | TypeScript | Strict mode |
| Styling | Tailwind CSS | + shadcn/ui |
| State | Zustand | Auth state |
| Data Fetching | TanStack Query | API caching |
| Routing | React Router | v6 |

### 9.2 Backend Configuration

```php
// protected/config/main.php
<?php
return [
    'name' => 'Event Registration API',
    
    'components' => [
        'db' => [
            'connectionString' => 'mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_NAME'),
            'username' => getenv('DB_USER'),
            'password' => getenv('DB_PASS'),
            'charset' => 'utf8mb4',
        ],
        'urlManager' => [
            'urlFormat' => 'path',
            'showScriptName' => false,
            'rules' => [
                // Public endpoints
                'api/public/qr/<token:\w+>' => 'public/qr',
                'api/public/<action:\w+>' => 'public/<action>',
                
                // REST API
                ['api/<controller>/index', 'pattern' => 'api/<controller:\w+>', 'verb' => 'GET'],
                ['api/<controller>/view', 'pattern' => 'api/<controller:\w+>/<id:\d+>', 'verb' => 'GET'],
                ['api/<controller>/create', 'pattern' => 'api/<controller:\w+>', 'verb' => 'POST'],
                ['api/<controller>/update', 'pattern' => 'api/<controller:\w+>/<id:\d+>', 'verb' => 'PUT'],
                ['api/<controller>/delete', 'pattern' => 'api/<controller:\w+>/<id:\d+>', 'verb' => 'DELETE'],
                
                // Custom actions
                'api/<controller>/<id:\d+>/<action:\w+>' => 'api/<controller>/<action>',
            ],
        ],
        'session' => [
            'class' => 'CHttpSession',
            'timeout' => 3600,
        ],
        'log' => [
            'class' => 'CLogRouter',
            'routes' => [[
                'class' => 'CFileLogRoute',
                'levels' => 'error, warning',
                'logFile' => 'api.log',
            ]],
        ],
    ],
    
    'params' => [
        // Portal SSO
        'portalUrl' => getenv('PORTAL_URL'),
        'portalVerifyUrl' => getenv('PORTAL_URL') . '/oauth/verify',
        'portalClientId' => getenv('PORTAL_CLIENT_ID'),
        
        // Frontend
        'frontendUrl' => getenv('FRONTEND_URL'),
        
        // File paths
        'uploadPath' => getenv('UPLOAD_PATH'),
        'badgePath' => getenv('BADGE_PATH'),
        
        // Badge settings
        'badgeDPI' => 300,
        'badgeWidthPx' => 1011,
        'badgeHeightPx' => 638,
        'uploadMaxSizeMB' => 2,
        
        // QR
        'qrBaseUrl' => getenv('QR_BASE_URL'),
    ],
];
```

### 9.3 Frontend Configuration

```typescript
// frontend/.env.production
VITE_API_URL=https://api.event.muongthanh.vn
VITE_PORTAL_URL=https://portal.muongthanh.vn
VITE_CLIENT_ID=event-registration-app
```

```typescript
// frontend/vite.config.ts
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: { '@': path.resolve(__dirname, './src') },
  },
  server: {
    port: 3000,
    proxy: {
      '/api': {
        target: 'http://localhost:8080',
        changeOrigin: true,
      },
    },
  },
});
```

### 9.4 Environment Variables

**Backend (.env):**
```bash
# Database
DB_HOST=localhost
DB_NAME=event_registration
DB_USER=eventuser
DB_PASS=secret

# Portal SSO
PORTAL_URL=https://portal.muongthanh.vn
PORTAL_CLIENT_ID=event-registration-app

# Frontend CORS
FRONTEND_URL=https://event.muongthanh.vn

# File Storage
UPLOAD_PATH=/var/www/api/uploads/
BADGE_PATH=/var/www/api/badges/

# QR Code
QR_BASE_URL=https://event.muongthanh.vn/qr/
```

**Frontend (.env):**
```bash
VITE_API_URL=https://api.event.muongthanh.vn
VITE_PORTAL_URL=https://portal.muongthanh.vn
VITE_CLIENT_ID=event-registration-app
```

### 9.5 Server Requirements

| Component | Requirement |
|-----------|-------------|
| **API Server** | |
| PHP | 7.4+ với GD2, mbstring, pdo_mysql, curl, fileinfo |
| MySQL | 5.7+ |
| Nginx | Reverse proxy + static files |
| Disk | 10GB+ |
| RAM | 2GB+ |
| **Frontend Server** | |
| Node.js | 18+ (build only) |
| Nginx | Static file serving |
| CDN | Optional (cho assets) |

### 9.6 Nginx Configuration

```nginx
# API Server
server {
    listen 80;
    server_name api.event.muongthanh.vn;
    root /var/www/api;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location /uploads/ {
        alias /var/www/api/uploads/;
        expires 30d;
    }
    
    location /badges/ {
        alias /var/www/api/badges/;
        expires 30d;
    }
}

# Frontend Server
server {
    listen 80;
    server_name event.muongthanh.vn;
    root /var/www/frontend/dist;
    index index.html;
    
    location / {
        try_files $uri $uri/ /index.html;
    }
    
    location /assets/ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### 9.7 Deployment Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                        CI/CD Pipeline                            │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐ │
│  │   Push   │───▶│  Build   │───▶│   Test   │───▶│  Deploy  │ │
│  │  to Git  │    │          │    │          │    │          │ │
│  └──────────┘    └──────────┘    └──────────┘    └──────────┘ │
│                                                                  │
│  Frontend:  npm install → npm run build → upload dist/          │
│  Backend:   composer install → php migrate → sync files         │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## 10. Edge Cases & Xử Lý Đặc Biệt

| Tình huống                                    | Xử lý                                                                            |
| --------------------------------------------- | -------------------------------------------------------------------------------- |
| Đơn vị nộp đúng deadline nhưng server timeout | Kiểm tra `submitted_at` so với `period.end_time` — không dùng giờ server         |
| HR approve → đơn vị muốn đổi ảnh một người    | HR chỉnh sửa trực tiếp trong trang admin, log audit, re-generate badge           |
| Người được gán nhiều vai trò                  | Bảng `attendee_roles` many-to-many, hiển thị tất cả trên thẻ                     |
| Trưởng đoàn báo cắt ăn quá deadline           | Block form, hiện thông báo. Admin có thể override                                |
| Cùng người được gán vào 2 bàn tiệc            | Unique constraint ngăn chặn (per table) → cần thêm check ở application layer     |
| QR token bị chia sẻ                           | Token không có expiry vì dùng trong suốt sự kiện; nội dung public không nhạy cảm |
| Số báo danh trùng khi cấp đồng thời           | Dùng `SELECT MAX(candidate_number) FOR UPDATE` trong transaction                 |
| Xuất badge lô > 200 người                     | Giới hạn 50 thẻ/lần hoặc dùng background job (CLI command Yii1)                  |
| Bàn tiệc đã full capacity                     | Kiểm tra `COUNT(banquet_seats) < banquet_tables.capacity` trước khi assign       |

---

## 11. Ước Tính Timeline Triển Khai

### 11.1 Phase Overview

| Phase | Nội dung | Thời gian |
|-------|----------|-----------|
| **1 — Setup & SSO** | Project setup, SSO integration với Portal | 4 ngày |
| **2 — Core API** | Base controllers, filters, models | 3 ngày |
| **3 — Frontend Setup** | React project, auth flow, layout | 3 ngày |
| **4 — Registration** | Event, Registration, Attendee CRUD | 6 ngày |
| **5 — Badge & QR** | Badge generation, QR code, export | 4 ngày |
| **6 — Sports** | Sports, Teams, Matches, Results | 5 ngày |
| **7 — Competition** | Competition, Rounds, Candidate numbers | 4 ngày |
| **8 — Meal & Banquet** | Meals, Cutoff, Banquet, Seating | 4 ngày |
| **9 — Reports** | Dashboard, Reports, Export | 3 ngày |
| **10 — Testing & Deploy** | UAT, bug fixes, deployment | 5 ngày |
| **Tổng** | | **~41 ngày (~8 tuần)** |

### 11.2 Chi tiết từng Phase

**Phase 1 — Setup & SSO (4 ngày)**
- [ ] Khởi tạo project Yii1 API
- [ ] Khởi tạo project React + Vite
- [ ] Tích hợp OAuth2 với Portal
- [ ] JWT verification filter
- [ ] Permission filter (CRUD)
- [ ] Test SSO flow end-to-end

**Phase 2 — Core API (3 ngày)**
- [ ] Base ApiController
- [ ] Response formatter (success/error/paginate)
- [ ] CORS middleware
- [ ] Database models (base)
- [ ] API routing setup

**Phase 3 — Frontend Setup (3 ngày)**
- [ ] Auth service + store
- [ ] API client với interceptors
- [ ] Layout components (Sidebar, Header)
- [ ] Permission component (`<Can>`)
- [ ] Routing với protected routes

**Phase 4 — Registration (6 ngày)**
- [ ] Event CRUD (API + UI)
- [ ] Organization list
- [ ] Registration CRUD
- [ ] Registration detail (contents, sports)
- [ ] Attendee CRUD
- [ ] Photo upload

**Phase 5 — Badge & QR (4 ngày)**
- [ ] Badge template engine
- [ ] QR code generation
- [ ] Badge image export (single/batch)
- [ ] Public QR page
- [ ] Print preview

**Phase 6-8 — Modules (13 ngày)**
- Sports management
- Competition management  
- Meal management
- Banquet seating

**Phase 9-10 — Reports & Deploy (8 ngày)**
- Dashboard
- Reports & exports
- UAT testing
- Production deployment

---

## 12. Data Center — Quản Lý Dữ Liệu Gốc

### 12.1 Tổng quan

Hệ thống có nhóm bảng **Data Center** chứa dữ liệu gốc (master data) được sử dụng xuyên suốt:

| Bảng | Nguồn dữ liệu | Ghi chú |
|------|---------------|---------|
| `organizations` | Portal API | API endpoint có cơ chế đồng bộ tự động |
| `staff` | SMILE + CRUD | NV Tập đoàn sync từ SMILE; NV chưa dùng SMILE/ngoài: CRUD |
| `contents` | Admin CRUD | Nội dung hoạt động: Thể thao, Miss, Nghiệp vụ... |
| `sports` | Admin CRUD | Hỗ trợ cấu trúc cha-con |
| `roles` | Admin CRUD | Vai trò trong đại hội |
| `transports` | Admin CRUD | Phương tiện di chuyển |

### 12.2 Đồng bộ nhân viên từ SMILE

```
┌─────────────┐     API call     ┌──────────────┐
│   SMILE     │ ───────────────► │  staff table │
│  (External) │  /api/employees  │  source=smile│
└─────────────┘                  └──────────────┘
                                        │
                                        ▼
                                 ┌──────────────┐
                                 │  attendees   │
                                 │  staff_id FK │
                                 └──────────────┘
```

**Rules:**
- Nhân viên từ SMILE (`source=smile`): chỉ đọc, không cho phép sửa trực tiếp
- Nhân viên chưa dùng SMILE (`source=manual`): CRUD bình thường
- Nhân viên ngoài (`source=external`): CRUD bình thường
- Hỗ trợ import Excel cho bulk create

### 12.3 Cấu trúc môn thể thao (cha-con)

```
Bóng đá (parent_id = NULL)
├── Bóng đá nam (parent_id = 'Bóng đá')
└── Bóng đá nữ (parent_id = 'Bóng đá')

Cầu lông (parent_id = NULL)
├── Cầu lông đơn nam
├── Cầu lông đơn nữ
└── Cầu lông đôi nam nữ
```

### 12.4 Luồng đăng ký chi tiết

```
[Bước 1: Đăng ký khung]
  Đơn vị tạo registration với:
  - Các nội dung tham gia (registration_details.content_id)
  - Số lượng dự kiến theo vai trò (registration_details.quantity)
  - Môn thể thao cụ thể (nếu content=sports)
  - Upload tài liệu phê duyệt của đơn vị

[Bước 2: Đăng ký chi tiết nhân sự]
  Sau khi registration được approved:
  - Đơn vị nhập danh sách attendees cụ thể
  - Gán staff_id (liên kết với nhân viên trong staff table)
  - Upload ảnh, chọn phương tiện di chuyển

[Bước 3: HO phê duyệt danh sách]
  - Review từng attendee
  - Approve/reject với lý do
  - Generate QR token và badge
```

---

## 13. Tóm tắt

Hệ thống quản lý sự kiện đại hội ~600 người sử dụng kiến trúc **Yii1 Frontend + External API + Portal SSO**.

### Kiến trúc

```
┌─────────────────────┐
│  portal.muongthanh  │
│  (SSO + JWT Token)  │
└─────────┬───────────┘
          │ redirect?token=<JWT>
          ▼
┌─────────────────────┐          ┌─────────────────────┐
│  Frontend (Yii1)    │── API ──▶│  External API       │
│  - Validate JWT     │   Key    │  - REST Endpoints   │
│  - Session + TTL    │◀─────────│  - MySQL Database   │
│  - Auto refresh     │          └─────────────────────┘
└─────────────────────┘
```

### Điểm chính

- **Portal SSO**: User đăng nhập portal → click button → redirect với JWT token
- **Session Management**: TTL 30 phút idle, auto-refresh 15 phút khi active
- **Permission-based Authorization** format `"controller": "C R U D"` 
- **50 bảng database** với đầy đủ constraints, indexes, audit trail
- **External API** kết nối bằng API Key (không bao giờ mất kết nối)
- **Yii1 Frontend** với Hope UI theme
- **Data Center** quản lý dữ liệu gốc: organizations, staff (sync SMILE), contents, sports
- **Registration 2 bước**: đăng ký khung → đăng ký chi tiết nhân sự
- **Badge generation** dùng PHP GD2 render ảnh PNG 300DPI kèm QR token
- **Public QR page** không yêu cầu đăng nhập

### Ưu tiên triển khai

```
Phase 1: Portal SSO Integration + Core Modules
Phase 2: Registration + Attendee + Badge + QR Public (core flow)
Phase 3: Sports + Competition + Meal + Banquet (parallel modules)
Phase 4: Reports + Polish + Deploy
```

### Timeline

**~41 ngày (~8 tuần)** cho full implementation.

---

## 14. Module Liên Quân & Đăng Ký Chi Tiết (Cập nhật 2026-05-12)

### 14.1 Tổng quan

Mở rộng module đăng ký với 2 tính năng chính:
1. **Liên quân (Alliance)**: Cho phép 2 đơn vị ghép chung thành viên vào đội
2. **Đăng ký chi tiết**: Hỗ trợ 2 cách đăng ký (số lượng đội / danh sách cụ thể)

### 14.2 User Stories

#### Epic: Đăng ký nội dung tham gia

**US-REG-01: Chọn nội dung tham gia**
- **As a** Đại diện đơn vị
- **I want to** chọn danh sách các bộ môn (thể thao, nghiệp vụ, văn nghệ, miss) đơn vị sẽ tham gia
- **So that** BTC biết đơn vị tham gia những hoạt động nào

**Acceptance Criteria:**
- [ ] Hiển thị danh sách nội dung từ `event_contents` + `event_sports` + `event_competitions`
- [ ] Đơn vị tick chọn các môn muốn tham gia
- [ ] Lưu vào `registration_details`

---

**US-REG-02: Đăng ký theo số lượng đội**
- **As a** Đại diện đơn vị
- **I want to** đăng ký số đội tham gia mỗi môn thể thao (VD: 2 đội bóng đá nam)
- **So that** BTC biết cần chuẩn bị bao nhiêu đội từ đơn vị

**Acceptance Criteria:**
- [ ] Với môn thể thao team-based, nhập số lượng đội (quantity)
- [ ] Không cần khai tên từng người ở bước này
- [ ] Sau khi đăng ký được duyệt, BTC hoặc đơn vị mới tạo đội chi tiết

---

**US-REG-03: Đăng ký danh sách chi tiết**
- **As a** Đại diện đơn vị
- **I want to** đăng ký danh sách cụ thể ai tham gia thi nghiệp vụ
- **So that** BTC có danh sách chính xác để cấp số báo danh

**Acceptance Criteria:**
- [ ] Với nội dung cần danh sách chi tiết (competition, miss), phải chọn attendee cụ thể
- [ ] Giới hạn số lượng theo `max_per_org` của từng cuộc thi
- [ ] Lưu vào bảng `registration_detail_attendees`

---

#### Epic: Liên quân

**US-ALLY-01: Gửi yêu cầu liên quân**
- **As a** Đại diện đơn vị A
- **I want to** gửi yêu cầu liên quân tới đơn vị B
- **So that** hai đơn vị có thể ghép chung thành viên vào đội

**Acceptance Criteria:**
- [ ] Mỗi đơn vị chỉ được liên quân với TỐI ĐA 1 đơn vị khác
- [ ] Nếu đã có liên quân active, không cho gửi thêm
- [ ] Ghi nhận request vào bảng `alliance_requests`

---

**US-ALLY-02: Phê duyệt yêu cầu liên quân**
- **As a** Đại diện đơn vị B (hoặc Admin)
- **I want to** chấp nhận/từ chối yêu cầu liên quân
- **So that** xác nhận quan hệ liên quân giữa 2 đơn vị

**Acceptance Criteria:**
- [ ] Hiển thị danh sách request pending
- [ ] Approve → tạo record `alliances` với status=active
- [ ] Reject → cập nhật request status=rejected

---

**US-ALLY-03: Tạo đội với thành viên liên quân**
- **As a** Đại diện đơn vị
- **I want to** chọn thành viên từ đơn vị liên quân khi tạo đội
- **So that** đội có thể bao gồm nhân viên từ cả 2 đơn vị

**Acceptance Criteria:**
- [ ] Kiểm tra đơn vị có alliance active không
- [ ] Nếu có, danh sách chọn attendee bao gồm cả 2 đơn vị
- [ ] Nếu không, chỉ hiển thị attendee đơn vị mình

---

### 14.3 Database Schema - Tables mới

#### Bảng `alliance_requests` (Yêu cầu liên quân)

```sql
CREATE TABLE `alliance_requests` (
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
  UNIQUE KEY `uq_alliance_request` (`event_id`, `requester_org_id`, `target_org_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Yêu cầu liên quân giữa các đơn vị';
```

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT UNSIGNED | Primary key |
| `event_id` | INT UNSIGNED | FK → events |
| `requester_org_id` | INT UNSIGNED | Đơn vị gửi yêu cầu |
| `target_org_id` | INT UNSIGNED | Đơn vị nhận yêu cầu |
| `status` | ENUM | pending/approved/rejected/cancelled |
| `requested_by` | INT UNSIGNED | Người gửi (unit_accounts.id) |
| `reviewed_by` | INT UNSIGNED | Người duyệt |
| `rejection_reason` | TEXT | Lý do từ chối |

---

#### Bảng `alliances` (Quan hệ liên quân đã xác nhận)

```sql
CREATE TABLE `alliances` (
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
  UNIQUE KEY `uq_alliance_event_orgs` (`event_id`, `org_a_id`, `org_b_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Liên quân đã xác nhận';
```

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT UNSIGNED | Primary key |
| `event_id` | INT UNSIGNED | FK → events |
| `org_a_id` | INT UNSIGNED | Đơn vị A |
| `org_b_id` | INT UNSIGNED | Đơn vị B |
| `request_id` | INT UNSIGNED | FK → alliance_requests |
| `status` | ENUM | active/dissolved |
| `dissolved_reason` | TEXT | Lý do hủy liên quân |

**Constraint quan trọng:** Mỗi đơn vị chỉ có 1 liên quân active per event.

---

#### Bảng `registration_detail_attendees` (Chi tiết người đăng ký)

```sql
CREATE TABLE `registration_detail_attendees` (
  `id`                      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `registration_detail_id`  INT UNSIGNED NOT NULL,
  `attendee_id`             INT UNSIGNED NOT NULL,
  `status`                  ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  `note`                    TEXT,
  `created_at`              INT UNSIGNED,
  `updated_at`              INT UNSIGNED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_rd_attendee` (`registration_detail_id`, `attendee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Chi tiết người tham gia khi đăng ký detailed';
```

| Column | Type | Description |
|--------|------|-------------|
| `registration_detail_id` | INT UNSIGNED | FK → registration_details |
| `attendee_id` | INT UNSIGNED | FK → attendees |
| `status` | ENUM | pending/confirmed/cancelled |

---

#### Sửa bảng `registration_details`

Thêm cột `registration_type`:

```sql
ALTER TABLE `registration_details` ADD COLUMN 
  `registration_type` ENUM('quantity','detailed') NOT NULL DEFAULT 'quantity' 
  COMMENT 'quantity=số lượng đội, detailed=danh sách cụ thể';
```

| Type | Khi nào dùng |
|------|--------------|
| `quantity` | Thể thao team-based (đăng ký số đội) |
| `detailed` | Thi nghiệp vụ, Miss (đăng ký danh sách cụ thể) |

---

### 14.4 Entity Relationship (Mở rộng)

```
organizations (1) ─── (N) alliance_requests (N) ─── (1) organizations
                              │
                              │ approved
                              ▼
                         alliances
                    (org_a_id, org_b_id)
                              │
                              │ enables
                              ▼
                    ┌─────────────────────┐
                    │ sport_team_members  │
                    │ can select from     │
                    │ both orgs           │
                    └─────────────────────┘

registration_details ─── (N) registration_detail_attendees ─── (1) attendees
  (registration_type)
```

---

### 14.5 Wireframe Flow đăng ký

```
┌─────────────────────────────────────────────────────────────────┐
│  STEP 1: Chọn nội dung tham gia                                 │
├─────────────────────────────────────────────────────────────────┤
│  ☑ Thể thao                                                     │
│     ☑ Bóng đá nam    [Số đội: 2 ▼]   ← registration_type=quantity│
│     ☑ Cầu lông đôi   [Số đội: 1 ▼]                              │
│     ☐ Kéo co                                                    │
│                                                                 │
│  ☑ Thi nghiệp vụ                                                │
│     ☑ Thi Lễ tân     [Chọn người ▼]  ← registration_type=detailed│
│        ├─ Nguyễn Văn A                                          │
│        └─ Trần Thị B                                            │
│                                                                 │
│  ☑ Miss                                                         │
│     [Chọn thí sinh ▼]                ← registration_type=detailed│
│        └─ Lê Thị C                                              │
│                                                                 │
│  ☐ Văn nghệ                                                     │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  STEP 2: Liên quân (Optional)                                   │
├─────────────────────────────────────────────────────────────────┤
│  Trạng thái liên quân: [Chưa có liên quân]                      │
│                                                                 │
│  [+ Gửi yêu cầu liên quân]                                      │
│     Chọn đơn vị: [Khách sạn ABC      ▼]                         │
│     Ghi chú:     [_____________________]                        │
│                              [Gửi yêu cầu]                      │
│                                                                 │
│  ── HOẶC ──                                                     │
│                                                                 │
│  Yêu cầu đang chờ duyệt:                                        │
│  ┌──────────────────────────────────────────┐                   │
│  │ KS XYZ gửi yêu cầu liên quân             │                   │
│  │ Ngày gửi: 10/05/2026                     │                   │
│  │         [Chấp nhận]  [Từ chối]           │                   │
│  └──────────────────────────────────────────┘                   │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  STEP 3: Tạo đội (sau khi đăng ký được duyệt)                   │
├─────────────────────────────────────────────────────────────────┤
│  Môn: Bóng đá nam | Đội 1 / 2                                   │
│                                                                 │
│  Tên đội: [Đội Bóng Sông Hồng_________]                         │
│                                                                 │
│  Chọn thành viên:                                               │
│  ┌────────────────────────────────────────────────────┐         │
│  │ 📍 Đơn vị: KS Mường Thanh Hà Nội                   │         │
│  │   ☑ Nguyễn Văn A (Tiền đạo)                        │         │
│  │   ☑ Trần Văn B (Thủ môn)                           │         │
│  │   ☐ Lê Văn C                                       │         │
│  │                                                    │         │
│  │ 📍 Đơn vị liên quân: KS Mường Thanh Nha Trang      │  ← NEW  │
│  │   ☑ Phạm Văn D (Hậu vệ)                            │         │
│  │   ☐ Hoàng Văn E                                    │         │
│  └────────────────────────────────────────────────────┘         │
│                                                                 │
│                              [Lưu đội]                          │
└─────────────────────────────────────────────────────────────────┘
```

---

### 14.6 Edge Cases

#### Liên quân

| Case | Xử lý |
|------|-------|
| Đơn vị A gửi request cho B, B đã có liên quân với C | Reject request, thông báo "Đơn vị B đã có liên quân" |
| Đơn vị A gửi request cho B, A đã có liên quân với C | Không cho gửi, thông báo "Đơn vị đã có liên quân" |
| A và B liên quân, sau đó B muốn hủy | Cần confirm từ cả 2 bên hoặc Admin quyết định |
| A gửi request cho B, B gửi request cho A cùng lúc | Accept 1 trong 2, auto-cancel cái còn lại |
| Liên quân sau khi đã tạo đội | Không ảnh hưởng đội đã tạo, chỉ áp dụng cho đội mới |
| Hủy liên quân khi đã có đội ghép | Giữ nguyên đội đã tạo, không cho sửa thành viên từ đơn vị kia |

#### Đăng ký

| Case | Xử lý |
|------|-------|
| Đăng ký quantity nhưng event đòi detailed | Báo lỗi validation |
| Số người đăng ký vượt max_per_org | Không cho thêm, hiển thị warning |
| Attendee chưa được approve | Không hiển thị trong danh sách chọn |
| Sửa đăng ký sau khi submitted | Chỉ Admin mới sửa được, đơn vị không được sửa |
| Đăng ký cùng người cho nhiều cuộc thi conflict giờ | Warning nhưng vẫn cho đăng ký (BTC sắp xếp) |

#### Tạo đội

| Case | Xử lý |
|------|-------|
| Attendee đã có trong đội khác cùng môn | Không cho thêm, thông báo "Đã có trong đội X" |
| Số thành viên vượt quá limit môn | Validation error |
| Thành viên từ đơn vị không liên quân | Không hiển thị trong danh sách chọn |

---

### 14.7 Business Rules tóm tắt

1. **Mỗi đơn vị tối đa 1 liên quân active** per event
2. **Đăng ký quantity**: chỉ nhập số đội, tạo đội chi tiết sau khi approved
3. **Đăng ký detailed**: phải chọn attendee cụ thể ngay khi đăng ký
4. **Liên quân cho phép chọn attendee từ cả 2 đơn vị** khi tạo đội
5. **Mặc định**: manager đơn vị nào chỉ được chọn nhân viên đơn vị đó
6. **Sau khi hủy liên quân**: đội đã tạo giữ nguyên, không cho sửa thêm người từ đơn vị kia

---

### 14.8 Liên quân theo nội dung (Content-level Alliance)

> **Yêu cầu mới**: Liên quân không áp dụng chung cho tất cả nội dung trong event, mà được chọn **riêng biệt theo từng nội dung**.

#### Sự khác biệt với thiết kế cũ

| Tiêu chí | Thiết kế cũ (Event-level) | Thiết kế mới (Content-level) |
|----------|---------------------------|------------------------------|
| Phạm vi | 1 liên quân cho toàn bộ event | Liên quân riêng cho từng nội dung |
| Giới hạn | Tối đa 1 đơn vị liên quân | Cấu hình số đơn vị tối đa theo nội dung |
| Ví dụ | A liên quân với B → áp dụng cho Bóng đá, Văn nghệ,... | A liên quân với B cho Bóng đá, với C cho Văn nghệ |

#### Cấu hình số đơn vị liên quân tối đa

Thêm cột `max_alliance_orgs` vào bảng `event_contents`:

```sql
ALTER TABLE `event_contents` ADD COLUMN 
  `max_alliance_orgs` TINYINT UNSIGNED NOT NULL DEFAULT 0 
  COMMENT 'Số đơn vị tối đa được liên quân cho nội dung này (0=không cho phép liên quân)';
```

| Giá trị | Ý nghĩa |
|---------|---------|
| 0 | Không cho phép liên quân cho nội dung này |
| 1 | Cho phép liên quân với tối đa 1 đơn vị |
| 2 | Cho phép liên quân với tối đa 2 đơn vị |
| N | Cho phép liên quân với tối đa N đơn vị |

#### Bảng `content_alliance_requests` (Yêu cầu liên quân theo nội dung)

```sql
CREATE TABLE `content_alliance_requests` (
  `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_content_id`    INT UNSIGNED NOT NULL COMMENT 'FK → event_contents',
  `registration_id`     INT UNSIGNED NOT NULL COMMENT 'FK → registrations (đơn vị gửi yêu cầu)',
  `target_org_id`       INT UNSIGNED NOT NULL COMMENT 'FK → organizations (đơn vị nhận yêu cầu)',
  `status`              ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `requested_by`        INT UNSIGNED NOT NULL COMMENT 'users.id (SSO) người gửi',
  `requested_at`        INT UNSIGNED,
  `reviewed_by`         INT UNSIGNED COMMENT 'users.id (SSO) người duyệt',
  `reviewed_at`         INT UNSIGNED,
  `rejection_reason`    TEXT,
  `note`                TEXT,
  `created_at`          INT UNSIGNED,
  `updated_at`          INT UNSIGNED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_content_alliance_req` (`event_content_id`, `registration_id`, `target_org_id`),
  KEY `idx_target_org` (`target_org_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Yêu cầu liên quân theo từng nội dung';
```

#### Bảng `content_alliances` (Liên quân đã xác nhận theo nội dung)

```sql
CREATE TABLE `content_alliances` (
  `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_content_id`    INT UNSIGNED NOT NULL COMMENT 'FK → event_contents',
  `registration_id`     INT UNSIGNED NOT NULL COMMENT 'FK → registrations (đơn vị chủ)',
  `ally_org_id`         INT UNSIGNED NOT NULL COMMENT 'FK → organizations (đơn vị liên quân)',
  `request_id`          INT UNSIGNED COMMENT 'FK → content_alliance_requests',
  `status`              ENUM('active','dissolved') NOT NULL DEFAULT 'active',
  `confirmed_at`        INT UNSIGNED,
  `dissolved_at`        INT UNSIGNED,
  `dissolved_by`        INT UNSIGNED,
  `dissolved_reason`    TEXT,
  `created_at`          INT UNSIGNED,
  `updated_at`          INT UNSIGNED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_content_alliance` (`event_content_id`, `registration_id`, `ally_org_id`),
  KEY `idx_ally_org` (`ally_org_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Liên quân đã xác nhận theo từng nội dung';
```

#### Entity Relationship (Content-level Alliance)

```
event_contents (1) ─── max_alliance_orgs
       │
       │ (1)
       ▼
content_alliance_requests (N) ─── status: pending/approved/rejected
       │
       │ approved
       ▼
content_alliances (N)
  ├── registration_id → registrations → organizations (đơn vị chủ)
  └── ally_org_id → organizations (đơn vị liên quân)
              │
              │ enables
              ▼
     Khi tạo đội cho nội dung này,
     có thể chọn attendee từ các đơn vị liên quân
```

#### User Stories mới

**US-ALLY-04: Cấu hình số đơn vị liên quân theo nội dung**
- **As a** Admin HO
- **I want to** cấu hình số đơn vị tối đa được liên quân cho từng nội dung
- **So that** kiểm soát được quy mô liên quân theo từng loại hoạt động

**Acceptance Criteria:**
- [ ] Trong form edit Event Content, có field "Số đơn vị liên quân tối đa"
- [ ] Giá trị mặc định = 0 (không cho liên quân)
- [ ] Validation: số nguyên >= 0

---

**US-ALLY-05: Gửi yêu cầu liên quân theo nội dung**
- **As a** Đại diện đơn vị A
- **I want to** gửi yêu cầu liên quân cho từng nội dung riêng biệt
- **So that** có thể liên quân với đơn vị khác nhau cho các nội dung khác nhau

**Acceptance Criteria:**
- [ ] Trong form đăng ký, mỗi nội dung có section "Chọn đơn vị liên quân"
- [ ] Chỉ hiển thị nếu `max_alliance_orgs > 0`
- [ ] Cho phép chọn tối đa = `max_alliance_orgs` đơn vị
- [ ] Dropdown hiển thị danh sách đơn vị khác đã đăng ký event
- [ ] Ghi nhận vào bảng `content_alliance_requests`

---

**US-ALLY-06: Phê duyệt yêu cầu liên quân theo nội dung**
- **As a** Đại diện đơn vị B
- **I want to** xem và phê duyệt các yêu cầu liên quân cho từng nội dung
- **So that** xác nhận liên quân cho nội dung cụ thể

**Acceptance Criteria:**
- [ ] Hiển thị danh sách request pending theo từng nội dung
- [ ] Approve → tạo record `content_alliances`
- [ ] Reject → cập nhật status, ghi reason

---

**US-ALLY-07: Tạo đội với thành viên từ nhiều đơn vị liên quân**
- **As a** Đại diện đơn vị
- **I want to** chọn thành viên từ các đơn vị liên quân khi tạo đội
- **So that** đội bao gồm nhân viên từ nhiều đơn vị đã liên quân cho nội dung đó

**Acceptance Criteria:**
- [ ] Lấy danh sách `content_alliances` active cho nội dung
- [ ] Danh sách chọn attendee bao gồm: đơn vị mình + các đơn vị liên quân
- [ ] Nhóm theo đơn vị để dễ phân biệt

---

#### Wireframe Flow mới

```
┌─────────────────────────────────────────────────────────────────┐
│  ĐĂNG KÝ THAM GIA SỰ KIỆN                                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ☑ Bóng đá nam                                                  │
│     Số đội: [2 ▼]                                                │
│     ┌─────────────────────────────────────────────────────┐     │
│     │ Liên quân (tối đa 2 đơn vị):                        │     │
│     │   ☑ KS Mường Thanh Nha Trang     [Đã duyệt ✓]       │     │
│     │   ☑ KS Mường Thanh Đà Nẵng       [Chờ duyệt...]     │     │
│     │   [+ Thêm đơn vị liên quân]                         │     │
│     └─────────────────────────────────────────────────────┘     │
│                                                                 │
│  ☑ Văn nghệ - Tốp ca                                            │
│     Số tiết mục: [1 ▼]                                          │
│     ┌─────────────────────────────────────────────────────┐     │
│     │ Liên quân (tối đa 3 đơn vị):                        │     │
│     │   ☑ KS Grand Thanh Hóa           [Đã duyệt ✓]       │     │
│     │   [+ Thêm đơn vị liên quân]                         │     │
│     └─────────────────────────────────────────────────────┘     │
│                                                                 │
│  ☑ Thi Lễ tân                                                   │
│     ┌─────────────────────────────────────────────────────┐     │
│     │ Nội dung này không cho phép liên quân               │     │
│     └─────────────────────────────────────────────────────┘     │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

#### Edge Cases (Content-level Alliance)

| Case | Xử lý |
|------|-------|
| Nội dung có `max_alliance_orgs = 0` | Ẩn section liên quân, không cho chọn |
| Đơn vị chọn quá số lượng cho phép | Validation error: "Chỉ được liên quân tối đa X đơn vị" |
| Đơn vị B từ chối liên quân | Đơn vị A có thể chọn đơn vị khác (nếu chưa đạt max) |
| Đơn vị A liên quân với B cho Bóng đá, với C cho Văn nghệ | Hợp lệ - liên quân độc lập theo nội dung |
| Hủy liên quân sau khi đã tạo đội | Đội giữ nguyên, không cho sửa thêm người từ đơn vị đã hủy |
| Đơn vị B chưa đăng ký event | Không hiển thị trong dropdown chọn liên quân |

---

#### Business Rules (Content-level Alliance)

1. **Liên quân theo nội dung**: Mỗi nội dung có cấu hình liên quân riêng
2. **Số lượng liên quân**: Tối đa theo `event_contents.max_alliance_orgs`
3. **Cần phê duyệt**: Đơn vị được chọn phải approve trước khi liên quân có hiệu lực
4. **Phạm vi**: Liên quân chỉ áp dụng cho nội dung đã chọn, không ảnh hưởng nội dung khác
5. **Chọn thành viên**: Khi tạo đội cho nội dung, có thể chọn attendee từ tất cả đơn vị liên quân đã approved
6. **Backward compatibility**: Bảng `alliances`, `alliance_requests` cũ vẫn giữ nguyên cho các event đã tạo
