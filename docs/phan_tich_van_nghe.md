# Phân tích tính năng đăng ký tiết mục văn nghệ

## 1. Tổng quan

**Mục tiêu**: Cho phép đơn vị đăng ký danh sách thành viên biểu diễn tiết mục văn nghệ, bổ sung nội dung chi tiết (mô tả, kịch bản, video/audio demo), và gửi duyệt.

**Điều kiện truy cập**: Chỉ đơn vị đã có `talent_entries` (đăng ký tiết mục văn nghệ) trong sự kiện hoặc tham gia liên quân văn nghệ.

---

## 2. Flow tổng thể

```
┌─────────────────────────────────────────────────────────────────┐
│  ĐỢT 1: ĐĂNG KÝ CHÍNH (period_id=1, type=general)               │
│  ├── Registration → approved                                    │
│  ├── Attendees: người tham dự đại hội                           │
│  └── Talent entry: thông tin sơ bộ (tên, thể loại)              │
│      → ⚠ Sẽ được CHUYỂN sang registration đợt 3                 │
├─────────────────────────────────────────────────────────────────┤
│  ĐỢT 3: ĐĂNG KÝ VĂN NGHỆ (period_id=3, type=talent)             │
│  ├── Registration MỚI: tạo riêng cho văn nghệ                   │
│  ├── Talent entries: chuyển từ đợt 1 sang đây                   │
│  └── Attendees biểu diễn:                                       │
│      • Link attendee có sẵn (đợt 1) + thêm vai trò              │
│      • HOẶC tạo attendee mới trong registration đợt 3           │
├─────────────────────────────────────────────────────────────────┤
│  GIAI ĐOẠN 3: GẮN VÀO TIẾT MỤC                                  │
│  ├── Chọn attendees (từ đợt 1 hoặc đợt 3, đã approved)          │
│  ├── Tự động gán vai trò "Thi văn nghệ" khi gắn vào tiết mục    │
│  ├── Bổ sung nội dung: mô tả, kịch bản, video/audio             │
│  └── Gửi duyệt nội dung chi tiết                                │
└─────────────────────────────────────────────────────────────────┘
```

### 2.1 Migration dữ liệu (xem chi tiết: `migration_talent_registration.md`)

```
┌─────────────────────────────────────────────────────────────────┐
│  TRƯỚC MIGRATION                                                │
│  talent_entries.registration_id → registration đợt 1            │
├─────────────────────────────────────────────────────────────────┤
│  SAU MIGRATION                                                  │
│  1. Tạo registration MỚI (period_id=3) cho mỗi đơn vị           │
│  2. Cập nhật talent_entries.registration_id → registration đợt 3│
└─────────────────────────────────────────────────────────────────┘
```

---

## 3. Quy tắc nghiệp vụ

### 3.1 Điều kiện đăng ký đợt văn nghệ
- Đợt đăng ký có `type = 'talent'`
- Đơn vị phải có `talent_entries` trong event (trực tiếp hoặc qua liên quân)
- Nếu không có → từ chối truy cập

### 3.2 Attendee và tiết mục
- Attendee phải được **phê duyệt (approved)** mới gắn vào tiết mục
- Attendee có thể từ **đợt 1 hoặc đợt 3** (cùng property_id, event_id)
- Nếu link attendee có sẵn → **tự động thêm vai trò "Thi văn nghệ"** vào `attendee_roles`
- Nếu thêm người mới → tạo attendee trong registration đợt 3
- Mỗi đơn vị chỉ thêm attendee **của đơn vị mình**
- Mỗi đơn vị chỉ có **1 tiết mục** (hoặc 1 tiết mục liên quân)
- Attendee có thể tham gia **nhiều tiết mục** (không giới hạn)

### 3.3 Liên quân văn nghệ
- Nếu có liên quân: nhiều đơn vị cùng 1 tiết mục
- Mỗi đơn vị chỉ thêm attendee của mình vào tiết mục chung
- Khi gửi duyệt → kiểm tra tất cả đơn vị trong liên quân đã thêm người chưa
- Nếu thiếu → cảnh báo (soft warning), vẫn cho lưu

### 3.4 Gửi duyệt tiết mục
- Điều kiện: đủ số thành viên (theo `talent_categories.min_members`)
- Điều kiện: đã nhập mô tả ý nghĩa + nội dung dàn dựng
- Sau khi gửi → không được sửa (readonly)
- Admin có thể reject → đơn vị được sửa lại

---

## 4. Database Changes

### 4.1 Thêm cột `type` vào `registration_periods`

```sql
ALTER TABLE `registration_periods`
  ADD COLUMN `type` VARCHAR(50) NOT NULL DEFAULT 'general' 
  COMMENT 'general: đăng ký chính | talent: đăng ký văn nghệ | sport: đăng ký thể thao'
  AFTER `status`;
```

| Value | Mô tả |
|-------|-------|
| `general` | Đợt đăng ký chính (mặc định) |
| `talent` | Đợt đăng ký thành viên văn nghệ |
| `sport` | Đợt đăng ký thành viên thể thao (tương lai) |

### 4.2 Thêm cột vào `talent_entries`

```sql
ALTER TABLE `talent_entries` 
  ADD COLUMN `meaning_description` TEXT 
    COMMENT 'Mô tả ý nghĩa tiết mục (nguồn gốc, ý nghĩa)' 
    AFTER `description`,
  ADD COLUMN `staging_content` TEXT 
    COMMENT 'Nội dung chi tiết dàn dựng (kịch bản, ý đồ nghệ thuật)' 
    AFTER `meaning_description`,
  ADD COLUMN `video_demo_path` VARCHAR(500) 
    COMMENT 'Đường dẫn video demo (MP4/MOV, max 1.5GB)' 
    AFTER `music_path`,
  ADD COLUMN `audio_demo_path` VARCHAR(500) 
    COMMENT 'Đường dẫn audio demo (MP3)' 
    AFTER `video_demo_path`,
  ADD COLUMN `content_status` TINYINT(1) NOT NULL DEFAULT 0 
    COMMENT '0: draft | 1: submitted | 2: approved | 3: rejected' 
    AFTER `status`,
  ADD COLUMN `content_submitted_at` INT UNSIGNED NULL 
    COMMENT 'Thời điểm gửi duyệt nội dung' 
    AFTER `content_status`,
  ADD COLUMN `content_submitted_by` VARCHAR(255) NULL 
    COMMENT 'Email người gửi duyệt nội dung' 
    AFTER `content_submitted_at`;
```

| Column | Type | Mô tả |
|--------|------|-------|
| `meaning_description` | TEXT | Mô tả ý nghĩa, nguồn gốc tiết mục |
| `staging_content` | TEXT | Kịch bản, ý đồ nghệ thuật |
| `video_demo_path` | VARCHAR(500) | Đường dẫn video demo |
| `audio_demo_path` | VARCHAR(500) | Đường dẫn audio demo |
| `content_status` | TINYINT | Trạng thái nội dung chi tiết |
| `content_submitted_at` | INT UNSIGNED | Thời điểm gửi duyệt |
| `content_submitted_by` | VARCHAR(255) | Email người gửi |

---

## 5. User Stories

### US-TE-01: Xem danh sách tiết mục đã đăng ký
**As a** Đại diện đơn vị  
**I want to** xem danh sách tiết mục văn nghệ đơn vị mình đã đăng ký  
**So that** biết cần bổ sung thông tin cho tiết mục nào

**Acceptance Criteria:**
- [ ] Hiển thị `talent_entries` theo `organization_id` của đơn vị
- [ ] Nếu tham gia liên quân → hiển thị cả tiết mục liên quân
- [ ] Mỗi row: Tên tiết mục, Thể loại, Số thành viên, Trạng thái, Actions

---

### US-TE-02: Đăng ký attendee trong đợt văn nghệ
**As a** Đại diện đơn vị  
**I want to** đăng ký danh sách người thi văn nghệ trong đợt đăng ký riêng  
**So that** họ có thẻ tham dự và được quản lý bữa ăn

**Acceptance Criteria:**
- [ ] Chỉ đơn vị có `talent_entries` mới truy cập được đợt này
- [ ] Thêm attendee từ danh sách staff hoặc thủ công
- [ ] Attendee được gán vai trò "Thi văn nghệ" tự động
- [ ] Quy trình duyệt như đợt đăng ký chính

---

### US-TE-03: Gắn attendee vào tiết mục
**As a** Đại diện đơn vị  
**I want to** chọn attendee (đã approved) gắn vào tiết mục  
**So that** xác định ai sẽ biểu diễn

**Acceptance Criteria:**
- [ ] Chỉ hiển thị attendee đã approved từ đợt văn nghệ
- [ ] Mỗi đơn vị chỉ thêm attendee của mình
- [ ] Gán vai diễn (lead vocal, dancer, actor...)
- [ ] Đánh dấu `is_lead = 1` cho người đại diện
- [ ] Validation số lượng theo `talent_categories.min_members/max_members`

---

### US-TE-04: Upload nội dung tiết mục
**As a** Đại diện đơn vị  
**I want to** upload mô tả và media demo cho tiết mục  
**So that** BTC có thể xem xét

**Acceptance Criteria:**
- [ ] Nhập mô tả ý nghĩa tiết mục (required)
- [ ] Nhập nội dung chi tiết dàn dựng (required)
- [ ] Upload video demo: MP4/MOV, tối đa 1.5GB
- [ ] Upload audio demo: MP3
- [ ] Preview sau khi upload

---

### US-TE-05: Kiểm tra liên quân trước khi lưu
**As a** Hệ thống  
**I want to** kiểm tra đơn vị trong liên quân đã thêm thành viên chưa  
**So that** cảnh báo nếu còn thiếu

**Acceptance Criteria:**
- [ ] Không có liên quân → lưu ngay
- [ ] Có liên quân → kiểm tra mỗi đơn vị có ít nhất 1 member
- [ ] Thiếu → cảnh báo, vẫn cho lưu (soft warning)

---

### US-TE-06: Gửi duyệt tiết mục
**As a** Đại diện đơn vị  
**I want to** gửi tiết mục để BTC duyệt  
**So that** tiết mục được xác nhận biểu diễn

**Acceptance Criteria:**
- [ ] Nút "Gửi duyệt" active khi: đủ thành viên + đã nhập mô tả + dàn dựng
- [ ] Click → `content_status = 1`, `content_submitted_at = NOW()`
- [ ] Sau khi gửi → readonly, không được sửa
- [ ] Admin reject → `content_status = 3` → cho sửa lại

---

## 6. API Endpoints

### 6.1 Đợt đăng ký văn nghệ (dùng controller hiện có)

Sử dụng `RegistrationsController` hiện có, thêm logic check `type = 'talent'`.

### 6.2 Quản lý tiết mục (controller mới)

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| GET | `/admin/talentEntries` | Danh sách tiết mục của đơn vị |
| GET | `/admin/talentEntries/view/{id}` | Chi tiết tiết mục |
| GET | `/admin/talentEntries/update/{id}` | Form sửa tiết mục |
| POST | `/admin/talentEntries/update/{id}` | Cập nhật tiết mục |
| POST | `/admin/talentEntries/addMember` | Thêm thành viên (AJAX) |
| POST | `/admin/talentEntries/removeMember` | Xóa thành viên (AJAX) |
| POST | `/admin/talentEntries/submit/{id}` | Gửi duyệt |
| POST | `/admin/talentEntries/uploadMedia/{id}` | Upload video/audio |
| GET | `/admin/talentEntries/getApprovedAttendees` | Lấy attendees đã approved |

---

## 7. UI Wireframe

### 7.1 Danh sách tiết mục

```
┌─────────────────────────────────────────────────────────────────┐
│  DANH SÁCH TIẾT MỤC VĂN NGHỆ                                    │
├─────────────────────────────────────────────────────────────────┤
│  Đơn vị: KS Mường Thanh Hà Nội                                  │
│  Liên quân: KS Grand Thanh Hóa, KS Mường Thanh Sầm Sơn          │
├───────┬──────────────┬──────────┬────────┬─────────┬────────────┤
│ STT   │ Tên tiết mục │ Thể loại │ Thành  │ Trạng   │ Thao tác   │
│       │              │          │ viên   │ thái    │            │
├───────┼──────────────┼──────────┼────────┼─────────┼────────────┤
│ 1     │ Mùa xuân ơi  │ Tốp ca   │ 0/10   │ Nháp    │ [Sửa]      │
│ 2     │ Điệu múa...  │ Múa nhóm │ 5/20   │ Đã gửi  │ [Xem]      │
└───────┴──────────────┴──────────┴────────┴─────────┴────────────┘
```

### 7.2 Form sửa tiết mục

```
┌─────────────────────────────────────────────────────────────────┐
│  CHỈNH SỬA TIẾT MỤC: Mùa xuân ơi                                │
├─────────────────────────────────────────────────────────────────┤
│  THÔNG TIN CHUNG                                                │
│  ├── Tên tiết mục: Mùa xuân ơi                                  │
│  ├── Thể loại: Tốp ca (2-10 người)                              │
│  └── Thời lượng: 3:30 phút                                      │
│                                                                 │
│  DANH SÁCH THÀNH VIÊN (0/10)                 [+ Thêm thành viên]│
│  ┌───────────────────────────────────────────────────────────┐  │
│  │ (Chưa có thành viên nào)                                  │  │
│  └───────────────────────────────────────────────────────────┘  │
│  ⚠ Đơn vị "KS Grand Thanh Hóa" chưa có thành viên               │
│                                                                 │
│  MÔ TẢ Ý NGHĨA TIẾT MỤC (*)                                     │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │ Mô tả nguồn gốc, ý nghĩa của tiết mục...                  │  │
│  └───────────────────────────────────────────────────────────┘  │
│                                                                 │
│  NỘI DUNG DÀN DỰNG (*)                                          │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │ Kịch bản chi tiết, ý đồ nghệ thuật...                     │  │
│  └───────────────────────────────────────────────────────────┘  │
│                                                                 │
│  FILE DEMO                                                      │
│  ├── Video demo: [Chọn file...] (MP4/MOV, max 1.5GB)            │
│  └── Audio demo: [Chọn file...] (MP3)                           │
│                                                                 │
│  [Lưu nháp]                               [Gửi duyệt] (disabled)│
└─────────────────────────────────────────────────────────────────┘
```

### 7.3 Modal thêm thành viên

```
┌─────────────────────────────────────────────────────────────────┐
│  THÊM THÀNH VIÊN TIẾT MỤC                              [X]      │
├─────────────────────────────────────────────────────────────────┤
│  Chọn từ danh sách người đã đăng ký (đợt văn nghệ, đã duyệt):   │
│                                                                 │
│  Tìm kiếm: [________________] 🔍                                │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │ ☐ Nguyễn Văn A - KS Mường Thanh Hà Nội                    │  │
│  │ ☐ Trần Thị B - KS Mường Thanh Hà Nội                      │  │
│  │ ☐ Lê Văn C - KS Mường Thanh Hà Nội                        │  │
│  └───────────────────────────────────────────────────────────┘  │
│                                                                 │
│  Vai diễn: [Lead vocal ▼]                                       │
│  ☐ Là người đại diện tiết mục                                   │
│                                                                 │
│                                        [Hủy]    [Thêm]          │
└─────────────────────────────────────────────────────────────────┘
```

---

## 8. Logic Check đơn vị có quyền đăng ký

### 8.1 Check khi vào đợt đăng ký `type = 'talent'`

```php
// Trong RegistrationsController::actionCreate()
if ($period->type === 'talent') {
    $hasTalentEntry = TalentEntries::model()->exists(
        'event_id = :event_id AND (
            organization_id = :org_id 
            OR id IN (
                SELECT talent_entry_id FROM content_alliances ca
                JOIN content_alliance_members cam ON ca.id = cam.alliance_id
                WHERE cam.organization_id = :org_id
                AND ca.content_code = "talent"
            )
        )',
        array(
            ':event_id' => $period->event_id,
            ':org_id' => $currentOrgId
        )
    );
    
    if (!$hasTalentEntry) {
        throw new CHttpException(403, 'Đơn vị chưa đăng ký tiết mục văn nghệ');
    }
}
```

### 8.2 Check khi vào quản lý tiết mục

```php
// Trong TalentEntriesController
// Chỉ hiển thị tiết mục của đơn vị hoặc liên quân có đơn vị
```

---

## 9. Dependencies

| Dependency | Mô tả |
|------------|-------|
| `registration_periods` | Thêm trường `type` |
| `talent_entries` | Thêm các trường nội dung |
| `talent_entry_members` | Liên kết attendee với tiết mục |
| `content_alliances` | Kiểm tra liên quân |
| `attendees` | Danh sách người đã approved |
| `roles` | Vai trò "Thi văn nghệ" |

---

## 10. Risk Assessment

| Risk | Likelihood | Impact | Mitigation |
|------|------------|--------|------------|
| Upload video 1.5GB timeout | High | High | Chunked upload, resumable |
| Đơn vị liên quân không bổ sung người | Medium | Medium | Soft warning, không block |
| Conflict nhiều đơn vị cùng sửa tiết mục | Medium | Medium | Mỗi đơn vị sửa phần của mình |

---

## 11. Task Breakdown

### Backend
- [ ] Migration: thêm `type` vào `registration_periods` (1h)
- [ ] Migration: thêm cột `talent_entries` (1h)
- [ ] Logic check quyền đăng ký đợt văn nghệ (2h)
- [ ] Controller `TalentEntriesController` (1 ngày)
- [ ] AJAX add/remove member (4h)
- [ ] Upload handler video/audio (1 ngày)
- [ ] Validation liên quân (4h)

### Frontend
- [ ] View danh sách tiết mục (4h)
- [ ] View form sửa tiết mục (1 ngày)
- [ ] Modal thêm thành viên (4h)
- [ ] Upload progress, preview (4h)

**Tổng estimate: ~5-6 ngày**

---

## 12. Yêu cầu bổ sung

### 12.1 Player video/audio sau khi upload

Sau khi upload thành công, khi load lại trang đăng ký tiết mục phải hiển thị player để xem/nghe file đã upload:

```
┌─────────────────────────────────────────────────────────────────┐
│  FILE DEMO                                                      │
│                                                                 │
│  Video demo:                                                    │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │  ▶ [==============|--------] 1:23 / 3:30                  │  │
│  │     (HTML5 video player)                                  │  │
│  └───────────────────────────────────────────────────────────┘  │
│  [Xóa video] [Thay video khác]                                  │
│                                                                 │
│  Audio demo:                                                    │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │  ▶ [==============|--------] 0:45 / 2:10                  │  │
│  │     (HTML5 audio player)                                  │  │
│  └───────────────────────────────────────────────────────────┘  │
│  [Xóa audio] [Thay audio khác]                                  │
└─────────────────────────────────────────────────────────────────┘
```

**Implementation:**
```html
<!-- Video player -->
<?php if ($model->video_demo_path): ?>
<video controls style="width:100%; max-height:400px;">
    <source src="<?php echo $model->getVideoDemoUrl(); ?>" type="video/mp4">
    Trình duyệt không hỗ trợ video.
</video>
<?php endif; ?>

<!-- Audio player -->
<?php if ($model->audio_demo_path): ?>
<audio controls style="width:100%;">
    <source src="<?php echo $model->getAudioDemoUrl(); ?>" type="audio/mpeg">
    Trình duyệt không hỗ trợ audio.
</audio>
<?php endif; ?>
```

### 12.2 Các yêu cầu đã xác nhận KHÔNG cần

- ❌ Không cần validation định dạng video (resolution, codec)
- ❌ Không cần tính năng "copy tiết mục từ năm trước"
