# Phân tích thay đổi Schema — Đưa danh sách VÀO CHUNG KẾT lên hệ thống

> Tài liệu này dành cho **lập trình viên project API** chạy migration.
> Áp dụng cho 4 hạng mục: Thể thao, Thi nghiệp vụ, Thi sắc đẹp (Miss), Văn nghệ.
> Quy ước DB: MySQL InnoDB, `utf8mb4`, khóa chính `INT UNSIGNED AUTO_INCREMENT`, cột thời gian là **Unix timestamp** `INT UNSIGNED`, soft delete bằng `deleted_at`.

## Chốt quyết định nghiệp vụ (từ stakeholder)

| # | Quyết định |
|---|-----------|
| Q1 | Dùng dữ liệu đăng ký **hiện có trên hệ thống** (không nhập lại attendee). |
| Q3 | **Miss**: `beauty_contestants` là danh sách thí sinh; dùng `beauty_round_results` để đánh dấu/chấm thí sinh vào vòng chung kết. |
| Q4 | **Văn nghệ**: thêm **bảng liên kết tiết mục ↔ vòng thi**. |
| Q5 | Số báo danh (SBD) **giữ nguyên** từ vòng loại, không cấp lại. |
| Q7 | Người nạp danh sách chung kết: **Admin**. |

## Nguyên tắc chung

"Đưa vào chung kết" = tạo/đánh dấu bản ghi ở **vòng/giai đoạn cuối (final)** của mỗi module, trỏ tới bản ghi đăng ký **đã có sẵn**. Điều kiện tiên quyết: attendee đã được duyệt và đã có bản ghi đăng ký hạng mục tương ứng.

---

## 1. THỂ THAO — ✅ KHÔNG cần đổi schema

`sport_stages.stage_type = 'final'` + `sport_stage_teams` đã đủ trường (`entry_type`, `seed`, `final_rank`, `status`, `qualified_from`).

**Luồng dữ liệu:**
1. Đảm bảo mỗi môn (`event_id` + `sport_id`) có 1 stage `stage_type='final'`. Nếu chưa có → tạo trong `sport_stages`.
2. Mỗi đội vào chung kết → INSERT `sport_stage_teams` (`stage_id` = stage final, `team_id`, `entry_type='promoted'`, `status='active'`, có thể set `qualified_from` = stage vòng trước).
3. Ràng buộc sẵn có `uq_stage_team (stage_id, team_id)` đã chống trùng đội.

> Không có DDL thay đổi cho module này.

---

## 2. THI NGHIỆP VỤ — cần bổ sung 1 cột

`competition_round_results` đã có `entry_type ENUM('qualification','direct')` để ghi thẳng chung kết. **Nhưng** `competition_rounds` **không có** cột nào để xác định đâu là vòng chung kết (chỉ có `round_order`). Để nhất quán với các module khác (đều có `round_type` với giá trị `final`), bổ sung cột `round_type`.

```sql
-- 2.1 Thêm cột phân loại vòng thi để xác định vòng chung kết
ALTER TABLE `competition_rounds`
  ADD COLUMN `round_type` ENUM('qualification','semifinal','final')
    NOT NULL DEFAULT 'qualification'
    COMMENT 'Loại vòng thi; final = vòng chung kết'
  AFTER `name`;

-- (Tùy chọn) đánh dấu vòng có round_order lớn nhất của mỗi cuộc thi là 'final'
-- Chạy nếu dữ liệu vòng thi đã có sẵn và muốn tự động gán:
UPDATE `competition_rounds` cr
JOIN (
  SELECT competition_id, MAX(round_order) AS max_order
  FROM `competition_rounds`
  WHERE deleted_at IS NULL
  GROUP BY competition_id
) t ON cr.competition_id = t.competition_id AND cr.round_order = t.max_order
SET cr.round_type = 'final';
```

**Luồng dữ liệu:**
1. Xác định vòng chung kết: `competition_rounds.round_type = 'final'` theo từng `competition_id`.
2. Mỗi thí sinh vào chung kết → INSERT `competition_round_results` (`round_id` = vòng final, `registration_id` = `competition_registrations.id`, `entry_type='direct'`, `passed=1`).
3. SBD giữ nguyên trong `competition_registrations.candidate_number` — **không đụng tới**.
4. Ràng buộc `uq_round_registration (round_id, registration_id)` đã chống trùng.

---

## 3. THI SẮC ĐẸP (MISS) — sửa bảng `beauty_round_results`

**Vấn đề hiện tại:** `beauty_round_results.registration_id` đang trỏ tới `beauty_registrations`. Theo Q3, danh sách thí sinh dùng `beauty_contestants`, nên bảng kết quả vòng phải trỏ tới **`beauty_contestants`** (qua `contestant_id`), không phải `beauty_registrations`.

```sql
-- 3.1 Gỡ FK cũ trỏ tới beauty_registrations (nếu tồn tại — kiểm tra tên FK thực tế)
--     Xem tên constraint: 
--     SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
--     WHERE TABLE_NAME='beauty_round_results' AND REFERENCED_TABLE_NAME IS NOT NULL;
ALTER TABLE `beauty_round_results`
  DROP FOREIGN KEY `fk_brr_registration`; -- đổi đúng tên FK thực tế nếu khác

-- 3.2 Đổi cột registration_id -> contestant_id (trỏ beauty_contestants)
ALTER TABLE `beauty_round_results`
  CHANGE COLUMN `registration_id` `contestant_id` INT UNSIGNED NOT NULL
    COMMENT 'beauty_contestants.id';

-- 3.3 Bổ sung trường phục vụ đánh dấu vào chung kết (đồng bộ với các module khác)
ALTER TABLE `beauty_round_results`
  ADD COLUMN `rank`   INT        NULL COMMENT 'Thứ hạng vòng này'    AFTER `score`,
  ADD COLUMN `passed` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Vào vòng sau / đạt' AFTER `rank`;

-- 3.4 Ràng buộc + FK mới
ALTER TABLE `beauty_round_results`
  ADD UNIQUE KEY `uq_brr_round_contestant` (`round_id`, `contestant_id`),
  ADD CONSTRAINT `fk_brr_contestant`
    FOREIGN KEY (`contestant_id`) REFERENCES `beauty_contestants`(`id`) ON DELETE CASCADE;
```

> ⚠️ Nếu bảng `beauty_round_results` đã có dữ liệu trỏ theo `registration_id`, cần script chuyển đổi `registration_id → contestant_id` trước khi đổi cột (map qua `attendee_id`). Nếu đang rỗng thì chạy trực tiếp như trên.

**Luồng dữ liệu:**
1. Xác định vòng chung kết: `beauty_rounds.round_type = 'final'` theo `contest_id`.
2. Mỗi thí sinh vào chung kết → INSERT `beauty_round_results` (`round_id` = vòng final, `contestant_id`, `passed=1`).
3. SBD giữ nguyên trong `beauty_contestants.candidate_number`.

---

## 4. VĂN NGHỆ — thêm bảng liên kết `talent_round_entries`

Hiện `talent_entries` không có bảng liên kết với `talent_rounds`, nên không thể biểu diễn "tiết mục nào vào vòng nào". Theo Q4, thêm bảng pivot **`talent_round_entries`** (tiết mục ↔ vòng thi).

```sql
-- 4.1 Bảng liên kết tiết mục ↔ vòng thi văn nghệ
CREATE TABLE `talent_round_entries` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `round_id`   INT UNSIGNED NOT NULL COMMENT 'talent_rounds.id',
  `entry_id`   INT UNSIGNED NOT NULL COMMENT 'talent_entries.id',
  `entry_type` ENUM('qualification','direct') NOT NULL DEFAULT 'qualification'
               COMMENT 'qualification=qua vòng loại, direct=ghi thẳng vào vòng',
  `performance_order` INT      NULL COMMENT 'Thứ tự biểu diễn trong vòng',
  `rank`       INT        NULL COMMENT 'Thứ hạng vòng này',
  `passed`     TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Vào vòng sau / đạt',
  `note`       TEXT       NULL,
  `created_at` INT UNSIGNED NULL,
  `updated_at` INT UNSIGNED NULL,
  `deleted_at` INT UNSIGNED NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tre_round_entry` (`round_id`, `entry_id`),
  KEY `idx_tre_passed` (`passed`),
  CONSTRAINT `fk_tre_round`
    FOREIGN KEY (`round_id`) REFERENCES `talent_rounds`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tre_entry`
    FOREIGN KEY (`entry_id`) REFERENCES `talent_entries`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tiết mục tham gia từng vòng thi văn nghệ';
```

**Luồng dữ liệu:**
1. Đảm bảo mỗi show có vòng `talent_rounds.round_type = 'final'`.
2. Mỗi tiết mục vào chung kết → INSERT `talent_round_entries` (`round_id` = vòng final, `entry_id`, `entry_type='direct'`, `passed=1`).
3. Chỉ nhận tiết mục đã duyệt (`talent_entries.status` hợp lệ).

---

## 5. Tổng hợp thay đổi

| Module | Bảng | Thay đổi |
|--------|------|----------|
| Thể thao | `sport_stages`, `sport_stage_teams` | ✅ Không đổi |
| Nghiệp vụ | `competition_rounds` | ➕ Thêm cột `round_type` |
| Miss | `beauty_round_results` | 🔧 Đổi `registration_id`→`contestant_id`, +`rank`, +`passed`, đổi FK + unique |
| Văn nghệ | `talent_round_entries` | 🆕 Bảng mới (pivot) |

## 6. Việc cần làm ở project API (sau migration)

- Regenerate base model (giix) cho: `CompetitionRounds`, `BeautyRoundResults`, và model mới `TalentRoundEntries`.
- Cập nhật relations: `TalentRounds hasMany talentRoundEntries`, `TalentEntries hasMany talentRoundEntries`; `BeautyRoundResults belongsTo contestant (BeautyContestants)`.
- Bổ sung endpoint nạp danh sách chung kết cho 4 module (nguồn: danh sách đăng ký đã có; đầu ra: các bảng vòng final ở trên).
- Validate: attendee/đăng ký phải tồn tại & đã duyệt; chống trùng theo unique key; ghi `audit_logs`.
