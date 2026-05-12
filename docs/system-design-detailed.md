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
  ├── UC02: Nhập danh sách người tham dự (tên, chức danh, ảnh)
  ├── UC03: Chỉnh sửa danh sách (khi status = draft)
  ├── UC04: Nộp đăng ký
  └── UC05: Xem trạng thái phê duyệt

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
| --- | --------------------------- | --------------------------------------------------- |
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
| 12  | `competition_rounds`        | Các vòng thi trong cuộc thi                         |l
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

---

### 3.2 SQL Schema chi tiết

```sql
-- ============================================================
-- 1. ORGANIZATIONS — Đơn vị
-- ============================================================
CREATE TABLE `organizations` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
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
  KEY `idx_organizations_active` (`is_active`)
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
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organization_id`   INT UNSIGNED NOT NULL,
  `period_id`         INT UNSIGNED NOT NULL,
  `submitted_by`      INT UNSIGNED NOT NULL COMMENT 'unit_accounts.id',
  `status`            ENUM('draft','submitted','approved','rejected') NOT NULL DEFAULT 'draft',
  `submitted_at`      INT UNSIGNED,
  `reviewed_by`       INT UNSIGNED COMMENT 'users.id — HR review',
  `reviewed_at`       INT UNSIGNED,
  `rejection_reason`  TEXT,
  `note`              TEXT         COMMENT 'Ghi chú thêm của đơn vị',
  `created_at`        INT UNSIGNED,
  `updated_at`        INT UNSIGNED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_registrations_org_period` (`organization_id`, `period_id`)
    COMMENT 'Mỗi đơn vị chỉ có 1 phiếu/đợt',
  KEY `idx_registrations_status` (`status`),
  KEY `idx_registrations_period` (`period_id`),
  CONSTRAINT `fk_registrations_org`
    FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`),
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
  `registration_id` INT UNSIGNED NOT NULL,
  `organization_id` INT UNSIGNED NOT NULL COMMENT 'Denormalized để query nhanh',
  `full_name`       VARCHAR(255) NOT NULL,
  `position`        VARCHAR(255) COMMENT 'Chức danh',
  `unit_label`      VARCHAR(255) COMMENT 'Tên đơn vị hiển thị trên thẻ (có thể khác org.name)',
  `photo_path`      VARCHAR(500) COMMENT 'Đường dẫn file ảnh',
  `qr_token`        VARCHAR(64)  UNIQUE COMMENT 'Token ngẫu nhiên cho QR (không phải ID)',
  `badge_number`    VARCHAR(20)  UNIQUE COMMENT 'Số thứ tự trên thẻ, VD: 001',
  `badge_generated` TINYINT(1)   NOT NULL DEFAULT 0,
  `badge_printed`   TINYINT(1)   NOT NULL DEFAULT 0,
  `is_team_lead`    TINYINT(1)   NOT NULL DEFAULT 0 COMMENT 'Có phải trưởng đoàn không',
  `is_active`       TINYINT(1)   NOT NULL DEFAULT 1 COMMENT 'Soft delete',
  `sort_order`      INT          NOT NULL DEFAULT 0,
  `created_at`      INT UNSIGNED,
  `updated_at`      INT UNSIGNED,
  PRIMARY KEY (`id`),
  KEY `idx_attendees_registration` (`registration_id`),
  KEY `idx_attendees_org` (`organization_id`),
  KEY `idx_attendees_qr` (`qr_token`),
  KEY `idx_attendees_team_lead` (`is_team_lead`),
  CONSTRAINT `fk_attendees_registration`
    FOREIGN KEY (`registration_id`) REFERENCES `registrations`(`id`),
  CONSTRAINT `fk_attendees_org`
    FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`)
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
('Trưởng đoàn',      'team_lead',        '#F44336', 7);


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
  `is_active`             TINYINT(1)   NOT NULL DEFAULT 1,
  `created_by`            INT UNSIGNED,
  `created_at`            INT UNSIGNED,
  `updated_at`            INT UNSIGNED,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


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
-- 14. SPORTS — Môn thể thao
-- ============================================================
CREATE TABLE `sports` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(100) NOT NULL COMMENT 'VD: Bóng đá, Cầu lông, Kéo co',
  `type`        ENUM('team','individual') NOT NULL DEFAULT 'team',
  `description` TEXT,
  `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`  INT UNSIGNED,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- 15. SPORT_TEAMS — Đội thi đấu
-- ============================================================
CREATE TABLE `sport_teams` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sport_id`        INT UNSIGNED NOT NULL,
  `organization_id` INT UNSIGNED COMMENT 'NULL nếu đội hỗn hợp',
  `name`            VARCHAR(255) NOT NULL,
  `short_name`      VARCHAR(50)  COMMENT 'Tên rút gọn hiển thị trên bảng',
  `color`           VARCHAR(7)   COMMENT 'Màu đội',
  `is_active`       TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`      INT UNSIGNED,
  PRIMARY KEY (`id`),
  KEY `idx_sport_teams_sport` (`sport_id`),
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
  `team_id`     INT UNSIGNED NOT NULL,
  `attendee_id` INT UNSIGNED NOT NULL,
  `jersey_number` VARCHAR(10),
  `position`    VARCHAR(100) COMMENT 'Vị trí thi đấu',
  `is_captain`  TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at`  INT UNSIGNED,
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
  `sport_id`      INT UNSIGNED NOT NULL,
  `round`         VARCHAR(100) COMMENT 'Vòng bảng A / Tứ kết / Bán kết / Chung kết',
  `match_order`   INT          NOT NULL DEFAULT 0,
  `team_a_id`     INT UNSIGNED COMMENT 'sport_teams.id — NULL nếu chưa biết (TBD)',
  `team_b_id`     INT UNSIGNED COMMENT 'sport_teams.id',
  `match_time`    INT UNSIGNED,
  `location`      VARCHAR(255),
  `status`        ENUM('scheduled','ongoing','completed','cancelled','postponed')
                  NOT NULL DEFAULT 'scheduled',
  `note`          TEXT,
  `created_at`    INT UNSIGNED,
  `updated_at`    INT UNSIGNED,
  PRIMARY KEY (`id`),
  KEY `idx_matches_sport` (`sport_id`),
  KEY `idx_matches_time` (`match_time`),
  KEY `idx_matches_status` (`status`),
  CONSTRAINT `fk_matches_sport`
    FOREIGN KEY (`sport_id`) REFERENCES `sports`(`id`),
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
  `recorded_by`    INT UNSIGNED COMMENT 'users.id',
  `recorded_at`    INT UNSIGNED,
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
organizations (1) ────── (1) unit_accounts
organizations (1) ────── (N) attendees            [via registrations]
organizations (1) ────── (N) registrations
organizations (1) ────── (N) sport_teams

registration_periods (1) ── (N) registrations
registrations (1) ───────── (N) attendees

attendees (1) ──────────── (1) badges
attendees (N) ──────────── (M) roles               [attendee_roles]
attendees (N) ──────────── (M) competitions        [competition_registrations]
attendees (N) ──────────── (M) banquet_tables       [banquet_seats]
attendees (N) ──────────── (M) meals               [meal_cutoffs]
attendees (N) ──────────── (M) sport_teams         [sport_team_members]

competitions (1) ─────────  (N) competition_rounds
competitions (1) ─────────  (N) competition_registrations

sports (1) ───────────────  (N) sport_teams
sports (1) ───────────────  (N) sport_matches
sport_matches (1) ─────────  (1) sport_match_results

banquet_events (1) ───────  (N) banquet_tables
banquet_tables (1) ───────  (N) banquet_seats

meals (1) ────────────────  (N) meal_cutoffs
```

---

## 4. Kiến Trúc Hệ Thống

### 4.1 Tổng quan kiến trúc

```
┌─────────────────────────────────────────────────────────────────────┐
│                          CLIENTS                                     │
│  ┌──────────────┐  ┌───────────────┐  ┌────────────────────────┐   │
│  │ Admin HO     │  │ Đại diện đơn  │  │ Người tham dự          │   │
│  │ (Desktop)    │  │ vị (Desktop)  │  │ (Mobile — QR Scan)     │   │
│  └──────┬───────┘  └───────┬───────┘  └───────────┬────────────┘   │
└─────────┼──────────────────┼──────────────────────┼────────────────┘
          │  HTTPS           │  HTTPS               │  HTTPS
          ▼                  ▼                       ▼
┌─────────────────────────────────────────────────────────────────────┐
│                        NGINX Web Server                              │
│  • Reverse proxy                                                     │
│  • Static file serving (badge images, uploads)                      │
│  • Gzip compression                                                  │
│  • Rate limiting                                                     │
└────────────────────────────┬────────────────────────────────────────┘
                             │ PHP-FPM / mod_php
                             ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    YII1 APPLICATION                                  │
│                                                                      │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌───────────────────┐  │
│  │  Module  │  │  Module  │  │  Module  │  │      Module       │  │
│  │  Admin   │  │  Unit    │  │  Public  │  │   Api (REST)      │  │
│  │  (Web)   │  │  (Web)   │  │  (Web)   │  │   /api/*          │  │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  └─────────┬─────────┘  │
│       └─────────────┴─────────────┴──────────────────┘            │
│                             │                                        │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │                    Services Layer                            │   │
│  │  RegistrationService │ BadgeService │ QRCodeService         │   │
│  │  CompetitionService  │ SportService │ MealService           │   │
│  │  ImageExportService  │ AuditService                         │   │
│  └──────────────────────────┬──────────────────────────────────┘   │
│                             │                                        │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │                      Models (Active Record — Yii1)          │   │
│  │  Organization │ Attendee │ Registration │ Badge             │   │
│  │  Competition  │ Sport    │ BanquetEvent │ Meal              │   │
│  └──────────────────────────┬──────────────────────────────────┘   │
└───────────────────────────  │  ──────────────────────────────────────┘
                              │
          ┌───────────────────┼───────────────────┐
          ▼                   ▼                   ▼
  ┌──────────────┐   ┌──────────────┐   ┌──────────────────┐
  │   MySQL 5.7+ │   │  File System │   │  Session Store   │
  │  (Primary DB)│   │  /uploads/   │   │  (Files / DB)    │
  └──────────────┘   │  /badges/    │   └──────────────────┘
                     └──────────────┘
```

### 4.2 Cấu trúc thư mục Yii1

```
protected/
├── config/
│   ├── main.php              # Cấu hình chính
│   ├── console.php           # Cấu hình CLI
│   └── params.php            # Tham số hệ thống
│
├── modules/
│   ├── admin/                # Module quản trị HO
│   │   ├── controllers/
│   │   │   ├── DefaultController.php       # Dashboard
│   │   │   ├── OrganizationController.php  # Đơn vị
│   │   │   ├── RegistrationController.php  # Phê duyệt đăng ký
│   │   │   ├── AttendeeController.php      # Người tham dự
│   │   │   ├── BadgeController.php         # Thẻ tham dự
│   │   │   ├── CompetitionController.php   # Thi nghiệp vụ
│   │   │   ├── SportController.php         # Thể thao
│   │   │   ├── BanquetController.php       # Tiệc
│   │   │   ├── MealController.php          # Bữa ăn
│   │   │   └── AgendaController.php        # Chương trình
│   │   └── views/
│   │
│   ├── unit/                 # Module đơn vị
│   │   ├── controllers/
│   │   │   ├── DefaultController.php
│   │   │   ├── RegistrationController.php  # Đăng ký danh sách
│   │   │   └── AttendeeController.php      # Quản lý thành viên
│   │   └── views/
│   │
│   ├── teamlead/             # Module trưởng đoàn
│   │   ├── controllers/
│   │   │   ├── DefaultController.php
│   │   │   └── MealController.php          # Báo cắt ăn
│   │   └── views/
│   │
│   └── public/               # Module public (không cần đăng nhập)
│       ├── controllers/
│       │   ├── QrController.php            # Quét QR
│       │   └── ScheduleController.php      # Lịch trình
│       └── views/
│
├── models/
│   ├── Organization.php
│   ├── UnitAccount.php
│   ├── User.php
│   ├── RegistrationPeriod.php
│   ├── Registration.php
│   ├── Attendee.php
│   ├── Role.php
│   ├── AttendeeRole.php
│   ├── Badge.php
│   ├── EventAgenda.php
│   ├── Competition.php
│   ├── CompetitionRound.php
│   ├── CompetitionRegistration.php
│   ├── Sport.php
│   ├── SportTeam.php
│   ├── SportTeamMember.php
│   ├── SportMatch.php
│   ├── SportMatchResult.php
│   ├── BanquetEvent.php
│   ├── BanquetTable.php
│   ├── BanquetSeat.php
│   ├── Meal.php
│   ├── MealCutoff.php
│   └── AuditLog.php
│
├── services/
│   ├── RegistrationService.php    # Xử lý đăng ký, phê duyệt
│   ├── BadgeService.php           # Tạo thẻ, render ảnh
│   ├── QRCodeService.php          # Sinh và decode QR
│   ├── ImageExportService.php     # Xuất ảnh thẻ in
│   ├── CompetitionService.php     # Cấp số báo danh
│   ├── MealService.php            # Tính toán suất ăn
│   └── AuditService.php           # Ghi log thay đổi
│
├── components/
│   ├── AppController.php          # Base controller có auth
│   ├── UnitAuthFilter.php         # Filter xác thực unit_account
│   ├── RoleAccessFilter.php       # Filter kiểm tra role
│   └── UploadHelper.php           # Xử lý upload ảnh
│
└── views/
    └── layouts/
        ├── admin.php
        ├── unit.php
        └── public.php
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

| Method | URL                            | Mô tả                       |
| ------ | ------------------------------ | --------------------------- |
| GET    | `/public/qr/<token>`           | Trang thông tin từ QR       |
| GET    | `/public/agenda`               | Chương trình đại hội (JSON) |
| GET    | `/public/competition-schedule` | Lịch thi nghiệp vụ          |
| GET    | `/public/sport-schedule`       | Lịch thi đấu thể thao       |

**Response quét QR (`/public/qr/<token>`):**

```json
{
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
    {
      "sport": "Bóng đá",
      "match": "CN Hà Nội vs CN TP.HCM",
      "time": "14:00 28/04/2026",
      "location": "Sân A"
    }
  ],
  "agenda": [
    {
      "title": "Khai mạc đại hội",
      "time": "08:00",
      "location": "Hội trường lớn"
    }
  ]
}
```

### 6.2 Unit Account Endpoints

| Method | URL                           | Mô tả                      |
| ------ | ----------------------------- | -------------------------- |
| POST   | `/unit/auth/login`            | Đăng nhập tài khoản đơn vị |
| POST   | `/unit/auth/logout`           | Đăng xuất                  |
| GET    | `/unit/registration/index`    | Xem phiếu đăng ký          |
| POST   | `/unit/registration/create`   | Tạo phiếu mới              |
| POST   | `/unit/registration/submit`   | Nộp phiếu                  |
| GET    | `/unit/attendee/index`        | Danh sách thành viên       |
| POST   | `/unit/attendee/create`       | Thêm thành viên            |
| POST   | `/unit/attendee/update`       | Cập nhật thành viên        |
| POST   | `/unit/attendee/delete`       | Xóa thành viên             |
| POST   | `/unit/attendee/upload-photo` | Upload ảnh                 |

### 6.3 Admin Endpoints

| Method | URL                                 | Mô tả                |
| ------ | ----------------------------------- | -------------------- |
| POST   | `/admin/registration/approve`       | Phê duyệt đăng ký    |
| POST   | `/admin/registration/reject`        | Từ chối + lý do      |
| POST   | `/admin/badge/generate`             | Tạo thẻ cho attendee |
| POST   | `/admin/badge/generate-batch`       | Tạo thẻ hàng loạt    |
| GET    | `/admin/badge/export/<id>`          | Xuất file ảnh thẻ    |
| GET    | `/admin/badge/export-batch`         | Xuất ZIP nhiều thẻ   |
| POST   | `/admin/attendee/assign-role`       | Gán vai trò          |
| POST   | `/admin/competition/assign-numbers` | Cấp số báo danh bulk |
| POST   | `/admin/sport/result`               | Cập nhật kết quả     |
| POST   | `/admin/banquet/assign-seat`        | Phân bổ chỗ ngồi     |
| GET    | `/admin/meal/report`                | Báo cáo số suất ăn   |

### 6.4 Team Lead Endpoints

| Method | URL                          | Mô tả                 |
| ------ | ---------------------------- | --------------------- |
| GET    | `/teamlead/meal/index`       | Danh sách bữa ăn      |
| GET    | `/teamlead/meal/team`        | Danh sách đoàn        |
| POST   | `/teamlead/meal/cutoff`      | Báo cắt ăn từng người |
| POST   | `/teamlead/meal/cutoff-team` | Báo cắt ăn cả đoàn    |

---

## 7. Bảo Mật

### 7.1 Phân tầng xác thực

| Tầng         | Cơ chế                                                | Ghi chú                              |
| ------------ | ----------------------------------------------------- | ------------------------------------ |
| Admin/HR/BTC | Session-based (Yii1 CWebUser)                         | Role check qua `accessRules()`       |
| Unit Account | Session riêng biệt hoặc custom auth                   | Chỉ xem được dữ liệu của đơn vị mình |
| Team Lead    | Kiểm tra `is_team_lead=1` trong `attendees` + session |                                      |
| Public QR    | Không cần auth                                        | Chỉ trả thông tin không nhạy cảm     |

### 7.2 Access Control Matrix

| Chức năng         | Admin | HR  | Competition BTC | Sports BTC | Banquet BTC | Unit | Team Lead | Public |
| ----------------- | ----- | --- | --------------- | ---------- | ----------- | ---- | --------- | ------ |
| Phê duyệt đăng ký | ✅    | ✅  | ❌              | ❌         | ❌          | ❌   | ❌        | ❌     |
| Tạo/sửa đơn vị    | ✅    | ❌  | ❌              | ❌         | ❌          | ❌   | ❌        | ❌     |
| Gán vai trò       | ✅    | ✅  | ❌              | ❌         | ❌          | ❌   | ❌        | ❌     |
| Xuất thẻ in       | ✅    | ✅  | ❌              | ❌         | ❌          | ❌   | ❌        | ❌     |
| Đăng ký danh sách | ❌    | ❌  | ❌              | ❌         | ❌          | ✅   | ❌        | ❌     |
| Quản lý thi NV    | ✅    | ❌  | ✅              | ❌         | ❌          | ❌   | ❌        | ❌     |
| Quản lý thể thao  | ✅    | ❌  | ❌              | ✅         | ❌          | ❌   | ❌        | ❌     |
| Quản lý tiệc      | ✅    | ❌  | ❌              | ❌         | ✅          | ❌   | ❌        | ❌     |
| Báo cắt ăn        | ❌    | ❌  | ❌              | ❌         | ❌          | ❌   | ✅        | ❌     |
| Xem QR            | ❌    | ❌  | ❌              | ❌         | ❌          | ❌   | ❌        | ✅     |

### 7.3 Các quy tắc bảo mật quan trọng

- `qr_token` là UUID ngẫu nhiên, không chứa ID hay thông tin nhạy cảm
- Password hash bằng `bcrypt` hoặc `SHA-256 + salt` (Yii1 tích hợp sẵn)
- Unit account chỉ query được `attendees` của `organization_id` mình
- Upload ảnh: kiểm tra MIME type thực (không chỉ extension), giới hạn 2MB, lưu ngoài webroot hoặc rename random
- CSRF token cho tất cả form POST (Yii1 hỗ trợ sẵn)
- Rate limiting đăng nhập: khóa sau 5 lần sai trong 15 phút

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

### 9.1 Yii1 Tech Stack chi tiết

| Component   | Lựa chọn                      | Ghi chú                             |
| ----------- | ----------------------------- | ----------------------------------- |
| Framework   | Yii 1.1.x                     | PHP 5.6–7.4                         |
| PHP Version | 7.4 (khuyến nghị)             | Cao nhất tương thích Yii1           |
| Database    | MySQL 5.7+                    | InnoDB engine                       |
| Session     | Database hoặc File            | DB session cho multi-server         |
| Cache       | File cache hoặc APC           | Redis nếu cần scale                 |
| Image       | PHP GD2                       | hoặc Imagick nếu available          |
| QR Code     | `phpqrcode` (free)            | hoặc `endroid/qr-code` via Composer |
| Export      | `PHPExcel` / `PhpSpreadsheet` | Export Excel danh sách              |
| PDF         | `TCPDF` hoặc `FPDF`           | Xuất PDF thẻ/danh sách              |
| Upload      | Yii1 `CUploadedFile`          | Validate + resize ảnh               |

### 9.2 Cấu hình main.php (Yii1)

```php
<?php
return [
    'name' => 'Quản Lý Sự Kiện Đại Hội',
    'defaultController' => 'site',

    'modules' => [
        'admin'    => ['class' => 'application.modules.admin.AdminModule'],
        'unit'     => ['class' => 'application.modules.unit.UnitModule'],
        'teamlead' => ['class' => 'application.modules.teamlead.TeamleadModule'],
        'public'   => ['class' => 'application.modules.public.PublicModule'],
    ],

    'components' => [
        'db' => [
            'connectionString' => 'mysql:host=localhost;dbname=event_db;charset=utf8mb4',
            'username'         => '...',
            'password'         => '...',
            'charset'          => 'utf8mb4',
            'tablePrefix'      => '',
            'enableParamLogging' => false,
        ],
        'cache' => [
            'class' => 'CFileCache',
            'cachePath' => 'protected/runtime/cache',
        ],
        'user' => [
            'class'          => 'CWebUser',
            'loginUrl'       => ['/admin/auth/login'],
            'allowAutoLogin' => false,
        ],
        'urlManager' => [
            'urlFormat'  => 'path',
            'showScriptName' => false,
            'rules' => [
                'qr/<token:[a-zA-Z0-9\\-]+>' => 'public/qr/view',
                '<module:\\w+>/<controller:\\w+>/<action:\\w+>' => '<module>/<controller>/<action>',
            ],
        ],
        'log' => [
            'class'  => 'CLogRouter',
            'routes' => [[
                'class'   => 'CFileLogRoute',
                'levels'  => 'error, warning',
                'logFile' => 'app.log',
            ]],
        ],
    ],

    'params' => [
        'badgeDPI'           => 300,
        'badgeWidthPx'       => 1011,
        'badgeHeightPx'      => 638,
        'uploadMaxSizeMB'    => 2,
        'uploadPath'         => '/var/www/data/uploads/',
        'badgePath'          => '/var/www/data/badges/',
        'qrBaseUrl'          => 'https://event.domain.vn/qr/',
        'adminEmail'         => 'admin@domain.vn',
    ],
];
```

### 9.3 Server Requirements

| Thành phần        | Yêu cầu                                     |
| ----------------- | ------------------------------------------- |
| PHP               | 7.4+ với GD2, mbstring, pdo_mysql, fileinfo |
| MySQL             | 5.7+                                        |
| Nginx hoặc Apache | mod_rewrite bật                             |
| Disk              | 10GB+ (cho ảnh upload và badge generated)   |
| RAM               | 2GB+                                        |
| CPU               | 2 cores+                                    |

### 9.4 Biến môi trường

```
DB_HOST=localhost
DB_NAME=event_db
DB_USER=...
DB_PASS=...
APP_UPLOAD_PATH=/var/www/data/uploads/
APP_BADGE_PATH=/var/www/data/badges/
APP_QR_BASE_URL=https://event.domain.vn/qr/
APP_ADMIN_EMAIL=admin@domain.vn
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

| Phase                       | Nội dung                                                                                 | Thời gian              |
| --------------------------- | ---------------------------------------------------------------------------------------- | ---------------------- |
| **1 — Setup**               | Khởi tạo project Yii1, DB migration, Auth, phân module                                   | 3 ngày                 |
| **2 — Đăng ký**             | Organizations, Unit accounts, Registration periods, Registrations, Attendees, Upload ảnh | 5 ngày                 |
| **3 — Phê duyệt & Thẻ**     | HR approval flow, Badge generation, QR code, Xuất ảnh in                                 | 5 ngày                 |
| **4 — Vai trò & QR Public** | Assign roles, Public QR page, Agenda                                                     | 3 ngày                 |
| **5 — Thi nghiệp vụ**       | Competition, Rounds, Registration, Số báo danh, Export                                   | 4 ngày                 |
| **6 — Thể thao**            | Sports, Teams, Matches, Results, Standings                                               | 4 ngày                 |
| **7 — Tiệc**                | Banquet events, Tables canvas, Seat assignment                                           | 3 ngày                 |
| **8 — Bữa ăn**              | Meals, Meal cutoff, Team lead flow, Report                                               | 2 ngày                 |
| **9 — Polish & Testing**    | UAT, bug fixes, tối ưu, deployment                                                       | 5 ngày                 |
| **Tổng**                    |                                                                                          | **~34 ngày (~7 tuần)** |

---

## 12. Tóm tắt

Hệ thống quản lý sự kiện đại hội ~600 người có 2 nhóm actor chính: nội bộ HO (Admin/HR/BTC) và bên ngoài (đơn vị, trưởng đoàn, người tham dự qua QR).

### Điểm chính

- **26 bảng database** với đầy đủ constraints, indexes, và audit trail
- **Kiến trúc module Yii1** tách biệt 4 khu vực: admin, unit, teamlead, public
- **Badge generation** dùng PHP GD2 render ảnh PNG 300DPI (1011×638px) kèm QR token
- **QR public page** không yêu cầu đăng nhập, trả về thông tin tổng hợp
- **Edge cases** được xử lý: race condition số báo danh, deadline validation, capacity check

### Recommendation

Ưu tiên triển khai theo thứ tự: **Đăng ký → Phê duyệt → Badge → QR Public** trước (core flow), sau đó mới các module thi nghiệp vụ, thể thao, tiệc vì chúng ít phụ thuộc nhau và có thể phát triển song song.
