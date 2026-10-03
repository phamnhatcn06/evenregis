# Tổng hợp danh sách Vòng Chung Kết & cấp mã Lucky

> Tài liệu phân tích nghiệp vụ (PRD) — bảng tổng hợp riêng chứa toàn bộ người vào Vòng Chung Kết
> (VCK) của tất cả đơn vị; đồng bộ (migrate) lặp lại được từ `attendees`; sửa thủ công mọi trường
> với bảo vệ chống ghi đè theo từng trường; cấp mã lucky draw duy nhất theo người.
> Cập nhật: 2026-10-03 (bản revise 2 — theo quyết định của chủ dự án).
> Liên quan: `docs/chung-ket-fun-run.md`, `docs/system-design.md`, memory `vck-final-aggregation`,
> memory `replace-withdraw-attendee`.

---

## 0. Các quyết định đã chốt với chủ dự án

| # | Vấn đề | Quyết định |
|---|--------|-----------|
| 1 | Một người có nhiều bản ghi `attendees` (nhiều đợt / bản finalist) | **GỘP thành 1 người, cấp 1 mã lucky duy nhất** |
| 2 | Phạm vi cấp mã | **Toàn bộ người vào Vòng Chung Kết** (không giới hạn chỉ `approved`) |
| 3 | Lọc theo bộ phận (division) | **Có** — cần lọc riêng |
| 4 | Kiến trúc | **TÁCH HẲN RA MỘT BẢNG RIÊNG**, chỉ chứa người vào Chung Kết |
| 5 | Đồng bộ | Có **tính năng migrate từ attendees VCK**, **chạy lại nhiều lần** được |
| 6 | Sửa thủ công | **Mọi trường** trong bảng mới sửa được |
| 7 | Chống ghi đè | Trường nào đã sửa thủ công thì lần migrate sau **KHÔNG ghi đè trường đó** (bảo vệ **theo từng trường**, không theo từng dòng) |

---

## 1. Mục tiêu & phạm vi

### Mục tiêu
Sau khi các đơn vị đã đăng ký và kết quả vòng loại đã chốt, HO cần **một bảng tổng hợp duy nhất**
chứa **toàn bộ người vào Vòng Chung Kết của mọi đơn vị**, để:

1. Tra cứu / lọc theo **đơn vị (property)**, **bộ phận (division)**, **phòng ban (department)**.
2. **Cấp mã lucky draw** duy nhất **theo người** (không theo bản ghi đăng ký) — mã này đồng thời là
   **mã đăng nhập** (`DHMT` + lucky) và gốc ghép **số BIB** của cổng chạy
   (xem `chung-ket-fun-run.md` §3).
3. **Sửa thủ công mọi thông tin** của từng người (chức danh, họ tên, đơn vị hiển thị, bộ phận,
   phòng ban, size áo, SĐT…) độc lập với dữ liệu đồng bộ từ SMILE / từ phiếu đăng ký.
4. **Đồng bộ lại** từ danh sách VCK bất cứ lúc nào (đơn vị nộp muộn, thay người, bổ sung người)
   mà **không mất** những gì HO đã sửa tay.
5. Xuất Excel danh sách tổng hợp (kèm mã lucky) để phân phát định danh và phục vụ bốc thăm.

### Trong phạm vi
- Bảng DB mới + đồng bộ (migrate) idempotent, có **dry-run/preview**.
- Màn hình admin: bộ lọc + bảng phân trang + tìm kiếm + thống kê.
- Inline edit **nhiều trường**, có badge đánh dấu trường đã sửa tay + nút khôi phục dữ liệu gốc.
- Cấp mã lucky theo người (gộp trùng), idempotent, cấp bù cho người thêm sau.
- Xuất Excel theo bộ lọc hiện tại.
- Phân quyền theo controller mới.

### Ngoài phạm vi (giai đoạn này)
- Cơ chế **bốc thăm** (quay số, giải thưởng, màn hình trình diễn) — tài liệu riêng.
- Gửi mã lucky/PIN tự động qua email/SMS/Zalo.
- In thẻ / QR (module badge riêng) — chỉ bàn tới việc **lấy dữ liệu** từ bảng mới.
- Ghi ngược (write-back) các trường đã sửa tay về `attendees` — xem §13 câu hỏi.

---

## 2. Người dùng & quyền

| Actor | Quyền trên màn hình này |
|-------|------------------------|
| **Admin HO** (`users.role=admin`, hoặc permission `*`) | Toàn quyền: xem, lọc, đồng bộ, cấp mã lucky, sửa mọi trường, khôi phục gốc, xuất Excel |
| **Nhân sự HO (HR)** | Xem + lọc + xuất Excel; sửa dữ liệu nếu được cấp `update` |
| **BTC các ban** | Chỉ xem (read) — phục vụ tra cứu |
| **Đại diện đơn vị** | **Không** truy cập |

### Permission mới
- Controller mới: **`finalroster`** (key chữ thường trong `MControllers`).

| Action | Quyền yêu cầu |
|--------|---------------|
| `admin` (danh sách), `export`, `syncPreview` | `finalroster.read` |
| `updateField`, `resetField` | `finalroster.update` |
| `sync` (đồng bộ ghi thật), `genLucky` | `finalroster.create` |
| `destroy` (loại người khỏi bảng tổng hợp) | `finalroster.delete` |

- Check bằng `PermissionHelper::can('finalroster', 'update')` trong **controller** *và* ẩn/hiện nút
  trong **view**.
- Cấu hình: thêm bản ghi vào `MControllers` + cột `roles.controllers` để hiện menu sidebar.

---

## 3. Quyết định thiết kế then chốt

### 3.1 Bảng riêng (đã chốt) — hệ quả phải xử lý

Chủ dự án chốt **tách hẳn một bảng riêng**. Bảng này trở thành **nguồn sự thật cho danh sách VCK
và cho bốc thăm**, còn `attendees` vẫn là nguồn sự thật của **quy trình đăng ký**. Hai nguồn song
song nên tài liệu phải định nghĩa rõ ranh giới:

| Việc | Nguồn sự thật |
|------|---------------|
| Đăng ký / duyệt / thay thế / huỷ tư cách | `attendees` (không đổi) |
| Danh sách VCK tổng hợp, mã lucky, dữ liệu đã HO chỉnh tay, bốc thăm, in danh sách | **Bảng mới** |
| Đăng nhập cổng chạy + số BIB | `attendees.lucky_number` (giữ nguyên — xem §6.3) |

Hệ quả bắt buộc: **phải có quy trình đồng bộ một chiều có kiểm soát**
(`attendees` → bảng mới), chạy lại được, và **không ghi đè** trường HO đã sửa.

### 3.2 Cơ chế theo dõi "trường nào đã sửa thủ công"

Ba phương án đã cân nhắc:

| Phương án | Cách làm | Ưu | Nhược |
|-----------|----------|-----|------|
| **A. Cột JSON `overridden_fields`** | Một cột JSON chứa mảng tên trường đã override, vd `["position","division_name"]`. Giá trị sửa **ghi thẳng vào cột thật** | Giá trị sống ở cột thật ⇒ **lọc/sort/index chạy bình thường**; 1 query lấy đủ dòng + cờ override; migrate chỉ cần `in_array()`; thêm trường editable mới **không cần migration** | Không ràng buộc được tên trường ở cấp DB; lọc "dòng nào đã override trường X" phải dùng `JSON_CONTAINS` |
| **B. Cột `*_override` song song cho từng trường** | `position` + `position_override`, `division_name` + `division_name_override`, … | Kiểu dữ liệu rõ ràng, giữ được cả giá trị gốc lẫn giá trị sửa | **~20 trường ⇒ ~20 cột thêm**; mọi filter/sort/export phải `COALESCE(x_override, x)` ⇒ **index vô dụng**, query phình; thêm trường editable mới phải migration |
| **C. Bảng `*_overrides` key-value** | Bảng con `(roster_id, field_name, value)` | Mềm dẻo nhất, có lịch sử theo trường | Phải JOIN/aggregate mỗi lần hiển thị danh sách (600–5.000 dòng × 20 trường); lọc/sort theo trường đã override rất khó; tăng độ phức tạp code hiển thị |

#### ✅ CHỌN PHƯƠNG ÁN A — cột JSON `overridden_fields`

**Lý do:**
1. **Hiệu năng & đơn giản của query:** giá trị hiển thị luôn nằm ở **cột thật**, nên lọc theo đơn vị
   / bộ phận / phòng ban, sort theo tên, tìm kiếm từ khoá đều **dùng index trực tiếp** — không
   `COALESCE`, không JOIN. Đây là ưu điểm quyết định so với B và C, vì màn hình này **chủ yếu là
   lọc và sort**.
2. **Migrate đơn giản và an toàn:** vòng lặp đồng bộ chỉ cần
   `if (!in_array($field, $overridden)) { $row->$field = $src->$field; }` — logic per-field, dễ
   đọc, dễ test, khó sai.
3. **Mở rộng không tốn migration:** chủ dự án yêu cầu "mọi thông tin sửa được", danh sách trường
   sẽ còn thay đổi. A cho phép thêm trường editable bằng cách khai báo trong hằng số
   `EDITABLE_FIELDS` của Entity, không phải thêm cột.
4. **Giữ được giá trị gốc mà không nhân đôi cột:** cột `attendee_id` + `source_snapshot` (JSON ảnh
   dữ liệu gốc lần migrate gần nhất) đủ để hiện tooltip "Gốc: …" và để nút **"Khôi phục gốc"** hoạt
   động — rẻ hơn 20 cột `*_override`.

**Ảnh hưởng tới query / filter / index:**
- Filter & sort thông thường: **không ảnh hưởng** (cột thật, có index).
- Cần đếm/lọc "các dòng đã sửa tay": dùng
  `WHERE JSON_LENGTH(overridden_fields) > 0`, hoặc theo trường cụ thể
  `WHERE JSON_CONTAINS(overridden_fields, '"position"')`. Đây là **truy vấn phụ, tần suất thấp**,
  chấp nhận full scan ở quy mô vài nghìn dòng.
- Nếu về sau cần lọc nhanh "đã sửa tay/chưa": thêm cột **`has_override` TINYINT(1)** (ghi kèm mỗi
  lần update) + index — rẻ, không phá thiết kế.
- MySQL cần ≥ 5.7 (đã dùng InnoDB/utf8mb4 nên OK). Nếu môi trường production là MySQL 5.6, dùng
  `TEXT` lưu JSON + xử lý ở tầng ứng dụng (Laravel cast `array` vẫn chạy).

**Quy ước lưu:**
- `overridden_fields`: JSON array tên cột đã override, vd `["position","division_name","phone_number"]`.
  Mặc định `[]` (không dùng `NULL` để tránh phân nhánh logic).
- Mỗi lần HO sửa trường `X` ⇒ ghi giá trị vào cột `X` **và** thêm `"X"` vào `overridden_fields`.
- Nút **"Khôi phục gốc"** cho trường `X` ⇒ gỡ `"X"` khỏi `overridden_fields` **và** ghi lại giá trị
  từ `source_snapshot.X` (lần migrate sau cũng sẽ cập nhật bình thường).
- `lucky_number` **không bao giờ** nằm trong `overridden_fields` (xem §6).

---

## 4. Nguồn dữ liệu & phạm vi "người vào Chung Kết"

### 4.1 Xác định nguồn (đã kiểm chứng trong code)
Người vào Chung Kết = attendee trên phiếu của đợt `registration_periods.is_final = 1` của sự kiện.
Cơ chế này **đã có sẵn** (memory `vck-final-aggregation`):
- Cột `registration_periods.is_final`, `attendees.attendee_type` (`finalist` / `director` / `driver`).
- Bảng `final_attendee_contents` (nội dung VCK của từng finalist).
- `FinalAggregationService::buildFinal` / `seedRegistration` / `listFinalAttendees`
  (`listFinalAttendees` đã lọc `is_active = 1` và trả thêm `source_attendee_ids`).
- Endpoint `GET /api/registration-finals/attendees`, command `vck:build`, `vck:sync-unit`.

⇒ **Nguồn đồng bộ của bảng mới = `FinalAggregationService::listFinalAttendees`** (tái dùng, không
viết lại logic xác định finalist).

### 4.2 Cây tổ chức "đơn vị / bộ phận / phòng ban"
```
properties (ĐƠN VỊ)              ← attendees.property_id
   └── divisions (BỘ PHẬN)        ← divisions: property_code, code, name
         └── departments (PHÒNG BAN) ← departments: property_code, division_code, code, name
               └── staffs         ← staffs: property_code, division_code, department_code, position_code
```

| Khái niệm | Trên `attendees` | Trạng thái |
|-----------|------------------|-----------|
| Đơn vị | `property_id` (+ `unit_label` là **nhãn in thẻ**, không phải mã đơn vị) | ✅ Có |
| Phòng ban | `department_code`, `department_name` | ✅ Có (set ở `AttendeeService::syncWithStaffData`) |
| Bộ phận | **KHÔNG CÓ cột** | ❌ Thiếu |
| Chức danh SMILE | `position_code`, `position_name` | ✅ Có |
| Chức danh đơn vị nhập | `position` | ✅ Có |

> `Modules/Registration/Http/Resources/AttendeeResource.php` có trả `division_code`/`division_name`
> nhưng bằng fallback `$this->staff->division_code` / `$this->staff->division->name` ⇒ **chỉ có khi
> `staff_id` tồn tại**, và **không lọc/sort được**.

### 4.3 Xử lý bộ phận (đã chốt: cần lọc riêng)
Vì bảng mới **có cột `division_code`/`division_name` riêng**, ta **không bắt buộc** phải thêm cột
vào `attendees`. Trình đồng bộ giải quyết bộ phận theo thứ tự:

1. `staffs.division_code` + `divisions.name` — nếu attendee có `staff_id` hoặc `staff_code`.
2. Nếu không có staff: tra `departments` theo (`property_code`, `department_code`) → lấy
   `division_code` → lấy tên từ `divisions`.
3. Không tra được ⇒ để rỗng, màn hình gom vào nhóm **"Chưa xác định"** (badge vàng) + có filter
   riêng để HO rà soát và **sửa tay** (trường này editable, sửa rồi migrate sau không ghi đè).

> **Khuyến nghị phụ (không bắt buộc, không chặn):** vẫn nên thêm `division_code`/`division_name`
> vào `attendees` để các màn khác (duyệt, báo cáo, email) cũng lọc được. Nhưng với phạm vi tài liệu
> này, bước 1–2 ở trên đã đủ ⇒ **tách thành slice tuỳ chọn S11**.

---

## 5. Sửa thủ công — danh sách trường & quy tắc

### 5.1 Trường được sửa (`EDITABLE_FIELDS`)
| Nhóm | Trường |
|------|--------|
| Định danh | `full_name`, `staff_code`, `id_card`, `birthday`, `gender`, `phone_number`, `email` |
| Tổ chức | `property_id`, `property_name`, `unit_label`, `division_code`, `division_name`, `department_code`, `department_name` |
| Chức danh | `position` (chức danh **hiển thị**), `position_name` (gốc SMILE — chỉ sửa khi thật cần) |
| Khác | `shirt_size`, `attendee_type`, `note`, `sort_order` |

### 5.2 Trường **KHÔNG** được sửa
`id`, `event_id`, `attendee_id`, `dedup_key`, `lucky_number`, `source_snapshot`,
`overridden_fields`, các cột `*_by` / `*_at`, `deleted_at`.

> `lucky_number` do hệ thống sinh, **không cho sửa tay** để tránh phá UNIQUE và phá BIB đã in.
> Nếu chủ dự án cần đổi mã cho một người → xem §13 câu hỏi.

### 5.3 Chức danh: 3 lớp (giữ nguyên tinh thần bản trước)
| Lớp | Nơi lưu | Ghi đè bởi migrate? |
|-----|---------|---------------------|
| Gốc SMILE | `final_rosters.position_name` ← `attendees.position_name` | ✅ Có (trừ khi được override) |
| Đơn vị nhập | `attendees.position` → `final_rosters.position` | ✅ Có (trừ khi được override) |
| **HO sửa tay** | `final_rosters.position` + `"position"` trong `overridden_fields` | ❌ **Không bao giờ** |

Chức danh hiển thị trên UI/Excel/thẻ: **`position_display`** = `position` ?: `position_name` ?: `''`
— đặt thành accessor duy nhất trên Entity, mọi nơi dùng chung.

---

## 6. Mã Lucky Draw

### 6.1 Hiện trạng (đã kiểm chứng trong code)
- `attendees.lucky_number` (string 20, **UNIQUE cấp bảng ⇒ unique toàn hệ thống**), `login_pin`
  (hash), `pin_set_at`, `login_failed_attempts`, `login_locked_until` — migration
  `Modules/Run/Database/Migrations/2026_10_02_100002_add_run_login_columns_to_attendees_table.php`.
- `RunAuthService::provisionLucky($eventId)` sinh `random_int(100000, 999999)` = **6 chữ số**, chỉ
  cấp cho `lucky_number IS NULL` ⇒ **đã idempotent**.
- `provisionLucky` **đã quét đúng phạm vi finalist VCK** (lọc `registration_periods.is_final = 1`)
  ⇒ **khớp với quyết định #2** của chủ dự án.
- Command `run:gen-lucky {event_id}` + endpoint `POST /api/run-auth/gen-lucky`; FE đã có nút ở
  `admin/runRegistrations/admin`.
- `RunAuthService::resolve()` tra `Attendee::where('lucky_number', $lucky)->where('is_active', 1)`
  ⇒ **cổng chạy đọc thẳng `attendees`**.
- `AttendeeResource` **chưa trả** `lucky_number`.

### 6.2 Đánh giá lại: có cần endpoint mới?
**Không cần endpoint mới cho việc "sinh mã theo event"** — phạm vi `provisionLucky` đã đúng.
Nhưng **vẫn phải bổ sung logic gộp người** (quyết định #1), vì `provisionLucky` hiện cấp mã
**theo từng bản ghi attendee** ⇒ một người có 2 bản ghi sẽ nhận **2 mã khác nhau** (sai nghiệp vụ).

⇒ **Phương án: giữ nguyên endpoint/command cũ cho cổng chạy, bổ sung bước cấp mã ở bảng mới.**

| Việc | Thực hiện ở đâu |
|------|-----------------|
| Sinh mã **theo người đã gộp** | Service mới `FinalRosterService::provisionLucky($eventId)` — chạy trên `final_rosters` (1 dòng = 1 người) |
| Ghi ngược mã về `attendees` để cổng chạy chạy được | Cùng service, xem §6.3 |
| `run:gen-lucky` / `run-auth/gen-lucky` cũ | **Giữ nguyên, không sửa**. Khuyến nghị **ngừng dùng** nút cũ khi bảng mới lên production (nêu ở §13) |

### 6.3 ⭐ Giữ tương thích cổng chạy Fun Run (đã verified — không được phá)

**Ràng buộc:** `attendees.lucky_number` là UNIQUE **toàn bảng** ⇒ **không thể** ghi cùng một mã vào
nhiều bản ghi attendee của cùng một người.

**Phương án chốt — "một mã, ghi vào bản ghi đại diện":**

1. Mỗi dòng `final_rosters` có `attendee_id` = **bản ghi attendee đại diện** của người đó
   (chọn theo §7.3), và `source_attendee_ids` (JSON) = tất cả bản ghi attendee cùng người.
2. `FinalRosterService::provisionLucky`:
   - Với mỗi dòng chưa có `lucky_number`:
     a) **Tái sử dụng trước:** nếu bất kỳ attendee nào trong `source_attendee_ids` **đã có**
        `lucky_number` ⇒ **lấy mã đó** (ưu tiên mã của `attendee_id` đại diện; nếu nhiều mã khác
        nhau ⇒ chọn mã **nhỏ nhất / cấp sớm nhất** và ghi cảnh báo xung đột).
     b) Nếu chưa ai có ⇒ sinh mã 6 số mới, kiểm tra trùng trên **cả** `attendees.lucky_number` và
        `final_rosters.lucky_number`.
   - Ghi mã vào `final_rosters.lucky_number`.
   - **Ghi ngược** mã vào `attendees.lucky_number` của **đúng bản ghi đại diện**
     (`final_rosters.attendee_id`).
   - Với các bản ghi attendee trùng người còn lại: nếu đang giữ **mã khác** ⇒ **set `NULL`** để
     tránh một người hai định danh, và **ghi log xung đột** để HO rà soát. ⚠️ Nếu mã đó **đã phát ra
     ngoài**, phải báo thu hồi (xem §11, và §13 câu hỏi).
3. Cổng chạy **không sửa một dòng code nào**: `RunAuthService::resolve` vẫn tra
   `attendees.lucky_number` + `is_active = 1` và luôn ra **đúng một** attendee đại diện.
   `identifyByQr`, `setPin`, `login`, BIB (`run_events.code + lucky_number`) giữ nguyên.
4. `login_pin` / `pin_set_at` / lockout **vẫn ở `attendees`**, **không** nhân bản sang bảng mới.
   Bảng mới chỉ **đọc** để hiển thị cờ `pin_is_set` (join theo `attendee_id`).

> Tóm lại: **`attendees.lucky_number` vẫn là nguồn sự thật cho đăng nhập**; `final_rosters.lucky_number`
> là bản sao phục vụ bốc thăm/tổng hợp, luôn được ghi đồng thời trong cùng transaction.

### 6.4 Quy tắc mã lucky

| Vấn đề | Quyết định |
|--------|-----------|
| Format | **6 chữ số**, `100000`–`999999` (giữ nguyên — ràng buộc BIB đã verified) |
| Unique scope | **Toàn hệ thống** (UNIQUE trên cả `attendees` và `final_rosters`) |
| Đơn vị cấp mã | **Theo người đã gộp** (1 dòng `final_rosters` = 1 người = 1 mã) |
| Idempotent | ✅ Chỉ cấp cho dòng `lucky_number IS NULL`; **không bao giờ** sinh lại mã đã cấp |
| Người thêm sau | Đồng bộ lại → dòng mới `lucky_number = NULL` → bấm "Cấp mã lucky" cấp bù. Khuyến nghị **tự động cấp mã ngay cuối bước đồng bộ** (một nút làm cả hai) |
| Người bị huỷ tư cách | **Giữ mã**, không tái sử dụng; đánh dấu dòng `status = withdrawn` (xem §11) |
| Người thay thế | Là người **khác** ⇒ dòng mới ⇒ **mã mới**; mã người bị thay giữ nguyên nhưng vô hiệu |
| Cạn mã | 900.000 mã cho vài nghìn người ⇒ an toàn |
| Cạnh tranh | Bọc retry 3 lần bắt duplicate key + cache lock theo `event_id` |

---

## 7. Thiết kế DB

### 7.1 Bảng mới `final_rosters`
> Tên đề xuất: **`final_rosters`** (snake_case, số nhiều — đúng rule). Phương án tên khác:
> `final_attendee_rosters`, `vck_rosters`. Chốt tên trước khi viết migration.

Migration: `Modules/Registration/Database/Migrations/2026_10_03_110000_create_final_rosters_table.php`

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| `id` | BIGINT UNSIGNED AI | PK |
| `event_id` | INT UNSIGNED, NOT NULL | Sự kiện |
| `period_id` | INT UNSIGNED, NULL | Đợt VCK (`is_final = 1`) nguồn |
| `attendee_id` | INT UNSIGNED, NULL | **Bản ghi attendee đại diện** (tham chiếu nguồn, dùng để ghi ngược lucky & đọc `pin_is_set`) |
| `source_attendee_ids` | JSON, NULL | Tất cả bản ghi attendee cùng người (phục vụ gộp & truy vết) |
| `registration_id` | INT UNSIGNED, NULL | Phiếu VCK nguồn |
| `dedup_key` | VARCHAR(190), NOT NULL | Khoá gộp người (§7.3) |
| `dedup_source` | VARCHAR(20), NULL | `staff_code` / `id_card` / `name_birthday` — nguồn sinh khoá |
| **Định danh** | | |
| `full_name` | VARCHAR(255) | |
| `staff_code` | VARCHAR(100), NULL | |
| `id_card` | VARCHAR(50), NULL | |
| `birthday` | DATE, NULL | |
| `gender` | TINYINT, NULL | |
| `phone_number` | VARCHAR(50), NULL | |
| `email` | VARCHAR(190), NULL | |
| **Tổ chức** | | |
| `property_id` | INT UNSIGNED, NULL | Đơn vị |
| `property_code` | VARCHAR(50), NULL | |
| `property_name` | VARCHAR(255), NULL | Phi chuẩn hoá để lọc/sort/export không join |
| `unit_label` | VARCHAR(255), NULL | Nhãn in thẻ |
| `division_code` | VARCHAR(50), NULL | **Bộ phận** |
| `division_name` | VARCHAR(255), NULL | |
| `department_code` | VARCHAR(50), NULL | **Phòng ban** |
| `department_name` | VARCHAR(255), NULL | |
| **Chức danh** | | |
| `position` | VARCHAR(255), NULL | Chức danh hiển thị |
| `position_code` | VARCHAR(100), NULL | |
| `position_name` | VARCHAR(255), NULL | Chức danh gốc SMILE |
| **Khác** | | |
| `attendee_type` | VARCHAR(20), NULL | `finalist` / `director` / `driver` |
| `shirt_size` | VARCHAR(10), NULL | |
| `note` | TEXT, NULL | |
| `sort_order` | INT, DEFAULT 0 | |
| **Lucky** | | |
| `lucky_number` | VARCHAR(20), NULL, **UNIQUE** | Mã lucky duy nhất theo người |
| `lucky_provisioned_at` | INT UNSIGNED, NULL | Unix timestamp |
| **Trạng thái & override** | | |
| `status` | TINYINT UNSIGNED, DEFAULT 1 | `1` active / `2` withdrawn (đã huỷ tư cách) / `3` manual (HO tự thêm, không có nguồn) |
| `overridden_fields` | JSON, NOT NULL DEFAULT `'[]'` | **Danh sách trường đã sửa tay** (§3.2) |
| `has_override` | TINYINT(1), DEFAULT 0 | Cờ phụ để lọc nhanh |
| `source_snapshot` | JSON, NULL | Ảnh dữ liệu gốc lần migrate gần nhất (phục vụ tooltip "Gốc: …" + nút khôi phục) |
| `last_synced_at` | INT UNSIGNED, NULL | Unix timestamp lần đồng bộ cuối |
| **Audit** | | |
| `created_by` | VARCHAR(190), NULL | Email, lấy từ `AuthHandler::getUser()['email']` |
| `updated_by` | VARCHAR(190), NULL | |
| `created_at` / `updated_at` | INT UNSIGNED, NULL | **Unix timestamp** (đúng rule dự án) |
| `deleted_at` | INT UNSIGNED, NULL | **Soft delete** |

**Index / Unique**
```
UNIQUE uq_final_rosters_lucky_number        (lucky_number)
UNIQUE uq_final_rosters_event_dedup         (event_id, dedup_key, deleted_at)
INDEX  idx_final_rosters_event_property     (event_id, property_id, status)
INDEX  idx_final_rosters_event_division     (event_id, division_code)
INDEX  idx_final_rosters_event_department   (event_id, department_code)
INDEX  idx_final_rosters_attendee           (attendee_id)
INDEX  idx_final_rosters_full_name          (full_name)
INDEX  idx_final_rosters_event_override     (event_id, has_override)
```
> `deleted_at` trong unique key để người đã xoá mềm không chặn việc đồng bộ lại cùng một người.
> Nếu MySQL không chấp nhận NULL trong unique theo kỳ vọng, dùng unique
> `(event_id, dedup_key)` + xoá cứng khi cần đồng bộ lại (nêu ở §13).

### 7.2 Bảng log đồng bộ `final_roster_sync_logs`
Migration: `...2026_10_03_110100_create_final_roster_sync_logs_table.php`

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| `id` | BIGINT UNSIGNED AI | |
| `event_id` | INT UNSIGNED | |
| `period_id` | INT UNSIGNED, NULL | |
| `property_id` | INT UNSIGNED, NULL | Nếu chạy 1 đơn vị |
| `mode` | VARCHAR(10) | `preview` / `apply` |
| `inserted` / `updated` / `skipped_override` / `conflicts` / `unchanged` | INT UNSIGNED DEFAULT 0 | Số liệu báo cáo |
| `detail` | JSON, NULL | Chi tiết: danh sách dòng thêm/sửa, field bị bỏ qua, cảnh báo gộp |
| `run_by` | VARCHAR(190), NULL | Email người chạy |
| `created_at` | INT UNSIGNED | |

### 7.3 Quy tắc gộp người (dedup)

**Khoá gộp** — tính theo thứ tự, dừng ở cái đầu tiên có giá trị:

| Ưu tiên | Điều kiện | `dedup_key` | `dedup_source` |
|---------|-----------|-------------|----------------|
| 1 | `staff_code` khác rỗng | `SC:` + upper(trim(staff_code)) | `staff_code` |
| 2 | `id_card` khác rỗng | `IC:` + chỉ giữ chữ số của id_card | `id_card` |
| 3 | còn lại | `NB:` + slug(họ tên bỏ dấu, lowercase, 1 space) + `|` + `birthday` (`Ymd`, rỗng ⇒ `00000000`) | `name_birthday` |

- Khoá chuẩn hoá: bỏ dấu, lowercase, gộp khoảng trắng — **phải dùng một hàm duy nhất**
  `FinalRosterService::buildDedupKey()` để preview và apply luôn giống nhau.
- Phạm vi gộp: **trong cùng `event_id`** (không gộp xuyên sự kiện).

**Chọn bản ghi gốc (đại diện)** khi nhiều attendee cùng khoá — theo thứ tự:
1. Bản ghi có `attendee_type = 'finalist'` (bản VCK) — **ưu tiên cao nhất**, vì đây là bản đang nằm
   trên phiếu VCK và các màn VCK đều tham chiếu nó.
2. Nếu vẫn nhiều: bản có `is_active = 1` và `approval_status = approved`.
3. Nếu vẫn nhiều: bản có **nhiều trường khác rỗng nhất** (đầy đủ dữ liệu nhất).
4. Nếu vẫn nhiều: bản có `id` **nhỏ nhất** (cũ nhất, ổn định giữa các lần chạy).

**Gộp dữ liệu từ các bản còn lại:** với mỗi trường, nếu bản gốc **rỗng** mà bản khác **có giá trị**
⇒ lấy giá trị đó (fill-in, không ghi đè). Nếu hai bản **đều có giá trị khác nhau** ⇒ giữ bản gốc và
**ghi vào `detail.conflicts`** để HO xem ở báo cáo đồng bộ (không tự quyết).

**Khi migrate lại phát hiện gộp sai:**
- *Trường hợp A — hai người bị gộp thành một* (vd dùng chung `name_birthday` do trùng tên, thiếu
  ngày sinh): preview báo `conflict_type = possible_wrong_merge` (cùng `dedup_key` nhưng
  `staff_code`/`id_card` khác nhau). HO xử lý bằng chức năng **"Tách người"**: nhập `staff_code`/
  `id_card` cho một bên (sửa tay ⇒ vào `overridden_fields`), đồng bộ lại sẽ sinh `dedup_key` mới và
  **tạo dòng mới**; dòng cũ giữ mã lucky, dòng mới được cấp mã mới.
- *Trường hợp B — một người bị tách thành hai dòng* (vd lần đầu thiếu `staff_code`): preview báo
  `conflict_type = duplicate_person` khi phát hiện hai dòng cùng `staff_code`/`id_card` nhưng khác
  `dedup_key`. HO xử lý bằng chức năng **"Gộp dòng"**: chọn dòng giữ lại (giữ `lucky_number` cũ hơn),
  dòng kia **xoá mềm**, `source_attendee_ids` được hợp nhất, mã lucky dòng bị xoá **không** tái sử dụng.
- Cả hai chức năng chỉ thao tác trên bảng mới, **không** sửa `attendees`.

### 7.4 Có sửa `attendees` không?
**Chỉ một thay đổi bắt buộc, rất nhỏ:**
- Bổ sung `lucky_number` (+ `pin_is_set` dạng accessor) vào `Modules/Registration/Http/Resources/AttendeeResource.php`
  để FE/đồng bộ đọc được (hiện chưa trả).
- **Không** cần migration trên `attendees` cho phạm vi này.
- (Tuỳ chọn, slice S11) thêm `division_code`/`division_name` vào `attendees` để các màn khác cũng
  lọc được — không chặn tính năng này.

---

## 8. API endpoints

Chuẩn `.claude/rules/api-conventions.md`: `{success, data, message}` / `{success, error}`,
list có `pagination`. Prefix `api`, middleware `auth.token` (API key).
Module: `Registration` (cùng chỗ với `FinalAggregationService`).

### 8.1 `GET /api/final-rosters` — danh sách tổng hợp
| Param | Kiểu | Mô tả |
|-------|------|-------|
| `event_id` | int, **bắt buộc** | Sự kiện |
| `period_id` | int | Đợt VCK |
| `property_id` | int | Lọc đơn vị |
| `division_code` | string | Lọc bộ phận (`__none__` = chưa xác định) |
| `department_code` | string | Lọc phòng ban (`__none__` = chưa xác định) |
| `attendee_type` | string | `finalist` / `director` / `driver` |
| `has_lucky` | 0/1 | Đã / chưa có mã lucky |
| `pin_is_set` | 0/1 | Đã / chưa đặt PIN (join `attendees` theo `attendee_id`) |
| `has_override` | 0/1 | Đã / chưa sửa tay |
| `status` | int | `1` active (mặc định) / `2` withdrawn / `3` manual |
| `keyword` | string | `full_name`, `staff_code`, `id_card`, `lucky_number` |
| `page`, `per_page` | int | Mặc định 25 |
| `sort_by`, `order` | string | `full_name` / `property_name` / `division_name` / `lucky_number` / `sort_order` |

Response 200:
```json
{
  "success": true,
  "data": [
    {
      "id": 1021, "event_id": 3, "period_id": 4,
      "attendee_id": 4821, "source_attendee_ids": [1003, 4821],
      "dedup_key": "SC:HN0123", "dedup_source": "staff_code",
      "full_name": "Nguyễn Văn A", "staff_code": "HN0123",
      "property_id": 66, "property_name": "Mường Thanh Grand Hà Nội",
      "unit_label": "MT Grand Hà Nội",
      "division_code": "FB", "division_name": "Bộ phận Nhà hàng",
      "department_code": "610", "department_name": "Phòng Giám đốc",
      "position": "Trưởng bộ phận Nhà hàng",
      "position_name": "Trưởng ca",
      "position_display": "Trưởng bộ phận Nhà hàng",
      "attendee_type": "finalist", "shirt_size": "L",
      "lucky_number": "123456", "login_identifier": "DHMT123456",
      "pin_is_set": true,
      "overridden_fields": ["position", "division_name"],
      "has_override": 1,
      "source_snapshot": { "position": "Trưởng ca", "division_name": null },
      "status": 1, "last_synced_at": 1759460000,
      "updated_by": "hr@muongthanh.vn"
    }
  ],
  "pagination": { "page": 1, "limit": 25, "total": 612, "totalPages": 25 }
}
```
> `login_pin` **không bao giờ** xuất hiện trong response.

### 8.2 `GET /api/final-rosters/filters`
`event_id` bắt buộc. Trả về các đơn vị / bộ phận / phòng ban **thực sự có người** trong bảng, phục
vụ dropdown phụ thuộc:
```json
{ "success": true, "data": {
  "properties":  [{ "id": 66, "name": "...", "total": 23 }],
  "divisions":   [{ "property_id": 66, "code": "FB", "name": "...", "total": 9 }],
  "departments": [{ "property_id": 66, "division_code": "FB", "code": "610", "name": "...", "total": 3 }]
}}
```

### 8.3 `GET /api/final-rosters/stats`
`{ total, with_lucky, without_lucky, pin_set, with_override, withdrawn, by_property: [...] }`

### 8.4 `POST /api/final-rosters/sync` — đồng bộ từ danh sách VCK
Body: `{ "event_id": 3, "period_id": 4, "property_id": null, "mode": "preview|apply", "run_by": "hr@..." }`

- `mode = preview` ⇒ **dry-run**, không ghi `final_rosters`, chỉ ghi `final_roster_sync_logs`
  (`mode=preview`).
- `mode = apply` ⇒ ghi thật, trong transaction.

Response 200:
```json
{ "success": true, "data": {
  "mode": "preview",
  "summary": { "inserted": 57, "updated": 480, "skipped_override": 23, "unchanged": 52, "conflicts": 2 },
  "inserted_rows": [{ "full_name": "...", "property_name": "...", "dedup_key": "SC:..." }],
  "skipped_fields": [{ "roster_id": 1021, "full_name": "...", "fields": ["position", "division_name"] }],
  "conflicts": [
    { "type": "possible_wrong_merge", "dedup_key": "NB:nguyen-van-a|00000000", "attendee_ids": [1003, 2117] },
    { "type": "duplicate_person", "staff_code": "HN0123", "roster_ids": [1021, 1455] }
  ],
  "log_id": 88
}, "message": "Xem trước: 57 thêm mới, 480 cập nhật, 23 trường bị bỏ qua do đã sửa tay, 2 cảnh báo." }
```
- 422 thiếu `event_id`/`period_id` hoặc đợt không phải `is_final`.
- 409 đang có tiến trình đồng bộ khác (cache lock `final-rosters:sync:{event_id}`, TTL 300s).

### 8.5 `POST /api/final-rosters/provision-lucky`
Body: `{ "event_id": 3, "property_id": null }`
- Sinh mã theo **người đã gộp**, ghi ngược vào `attendees` của bản đại diện (§6.3).
- 200: `{ "success": true, "data": { "provisioned": 57, "reused": 12, "conflicts": [...], "total_with_lucky": 612 }, "message": "Đã cấp mã lucky cho 57 người (tái dùng 12 mã có sẵn)." }`
- 422 thiếu `event_id`; 409 đang chạy.

### 8.6 `POST /api/final-rosters/update/{id}` — sửa thủ công
Body: `{ "fields": { "position": "Trưởng bộ phận Nhà hàng", "division_name": "Bộ phận Nhà hàng" }, "updated_by": "hr@..." }`
- Chỉ nhận trường trong `EDITABLE_FIELDS`; trường ngoài danh sách ⇒ **bỏ qua** (không lỗi) và ghi log.
- Mỗi trường được sửa ⇒ thêm tên vào `overridden_fields`, set `has_override = 1`,
  `updated_by`, `updated_at = time()`.
- Ghi `audit_logs` (`action = final_roster.update`, `old_data` / `new_data`).
- 200 trả về dòng đã cập nhật (kèm `position_display`, `overridden_fields` mới).
- 404 không tìm thấy; 422 validate (độ dài, định dạng ngày, gender…); 409 nếu sửa khiến
  `dedup_key` trùng dòng khác (trả cảnh báo, gợi ý dùng "Gộp dòng").

### 8.7 `POST /api/final-rosters/reset-field/{id}` — khôi phục trường về gốc
Body: `{ "fields": ["position"], "updated_by": "hr@..." }`
- Gỡ tên trường khỏi `overridden_fields`, ghi lại giá trị từ `source_snapshot`, cập nhật
  `has_override`. Lần đồng bộ sau sẽ cập nhật trường đó bình thường.
- 200 trả dòng đã cập nhật. 422 nếu trường không nằm trong `overridden_fields`.

### 8.8 `POST /api/final-rosters/store` — HO thêm người thủ công
Dành cho người không có trong danh sách VCK (ngoại lệ). Tạo dòng `status = 3 (manual)`,
`attendee_id = NULL`, **tất cả trường** vào `overridden_fields` (để đồng bộ không chạm).
⚠️ Người `manual` **không ghi ngược được lucky về `attendees`** ⇒ **không đăng nhập được cổng chạy**.
Phải cảnh báo rõ trên UI (xem §13 câu hỏi).

### 8.9 `POST /api/final-rosters/merge` và `POST /api/final-rosters/split`
Xử lý gộp sai / tách sai (§7.3). Body `merge`: `{ "keep_id": 1021, "merge_id": 1455 }`.
Body `split`: `{ "id": 1021, "attendee_ids_to_split": [2117], "new_dedup_hint": { "id_card": "..." } }`.

### 8.10 `DELETE /api/final-rosters/destroy/{id}`
Xoá mềm (`deleted_at = time()`), giữ `lucky_number` (không tái sử dụng).

### 8.11 Xuất Excel
**FE tự sinh** bằng PHPExcel (pattern đã có ở `RunRegistrationsController`, `ReportsController`)
⇒ **không thêm endpoint BE**.

### 8.12 Command artisan
```
php artisan final-roster:sync {event_id} {period_id} [--property=] [--dry-run]
php artisan final-roster:gen-lucky {event_id} [--property=]
```
> Chạy bằng MAMP php8.1 kèm cờ extension (xem `chung-ket-fun-run.md` §9).

### 8.13 Endpoint cũ — không sửa
`POST /api/run-auth/gen-lucky` và `run:gen-lucky` **giữ nguyên** (cổng chạy đã verified).
Khuyến nghị ngừng dùng nút cũ ở `admin/runRegistrations/admin` khi bảng mới lên production, vì nút
cũ cấp mã **theo bản ghi** (không gộp người).

---

## 9. Frontend Yii

### Files cần tạo
| File | Nội dung |
|------|----------|
| `protected/models/FinalRosters.php` | `CFormModel`. Static: `getApiDataProvider($params)`, `getFilterOptions($eventId)`, `getStats($eventId)`, `syncViaApi($params)`, `provisionLuckyViaApi($eventId,$propertyId)`, `updateFieldsViaApi($id,$fields)`, `resetFieldsViaApi($id,$fields)`, `mergeViaApi()`, `splitViaApi()`, `deleteViaApi($id)`. Hằng số `STATUS_ACTIVE=1`, `STATUS_WITHDRAWN=2`, `STATUS_MANUAL=3`, `getStatusLabel()`, `EDITABLE_FIELDS`. **Toàn bộ** `ApiClient` nằm ở đây |
| `protected/components/ApiEndpoints.php` (sửa) | Thêm nhóm `FINAL_ROSTER_*`: `LIST`, `FILTERS`, `STATS`, `SYNC`, `PROVISION_LUCKY`, `UPDATE`, `RESET_FIELD`, `STORE`, `MERGE`, `SPLIT`, `DESTROY` |
| `protected/modules/admin/controllers/FinalRosterController.php` | `actionAdmin`, `actionSyncPreview` (JSON), `actionSync` (POST), `actionGenLucky` (POST), `actionUpdateField` (POST JSON), `actionResetField` (POST JSON), `actionMerge`, `actionSplit`, `actionDelete`, `actionExport` |
| `.../views/finalRoster/admin.php` | View chính |
| `.../views/finalRoster/_filters.php` | Partial bộ lọc |
| `.../views/finalRoster/_modal_sync.php` | Modal đồng bộ (chọn phạm vi + **bảng kết quả dry-run** + nút "Ghi thật") |
| `.../views/finalRoster/_modal_edit_row.php` | Modal sửa **toàn bộ trường** của 1 người |
| `.../views/finalRoster/_modal_gen_lucky.php` | Modal xác nhận cấp mã lucky |
| `.../views/finalRoster/_modal_merge_split.php` | Modal gộp / tách người |
| `themes/hope-ui/assets/js/pages/finalroster-admin.js` | Toàn bộ JS |

> ≥ 2 modal ⇒ **bắt buộc** tách `_modal_*.php` riêng.
> **Không** inline `<script>`; register bằng
> `Yii::app()->clientScript->registerScriptFile(Yii::app()->theme->baseUrl . '/assets/js/pages/finalroster-admin.js', CClientScript::POS_END)`.
> Cấu hình (URL các action, danh sách trường editable, nhãn tiếng Việt) truyền qua `data-*` trên
> `<div id="final-roster-config">`.
> View **không gọi Model** — mọi dropdown/dữ liệu truyền từ controller qua `render()`.

### Mô tả UI

**Header card**
- Tiêu đề "Tổng hợp danh sách Vòng Chung Kết".
- Dropdown **Sự kiện** + **Đợt VCK** (bắt buộc; chọn xong mới hiện bảng).
- Nút **"Đồng bộ từ danh sách VCK"** (primary, cần quyền `create`) → `_modal_sync`:
  1. Chọn phạm vi (toàn sự kiện / một đơn vị).
  2. Bấm **"Xem trước"** → gọi `syncPreview` → hiện bảng số liệu:
     *Thêm mới / Cập nhật / Bỏ qua do đã sửa tay / Không đổi / Cảnh báo*, kèm danh sách chi tiết
     (tên người, trường bị bỏ qua, cảnh báo gộp).
  3. Bấm **"Ghi thật"** → gọi `sync` (`mode=apply`) → Toast + reload.
  Nút submit tuân thủ `modal-submit.md`: disable + spinner + Toast + đóng modal.
- Nút **"Cấp mã lucky"** (cần `create`).
- Nút **"Xuất Excel"** (giữ bộ lọc hiện tại).

**Dải thống kê** (6 thẻ): Tổng số người · Đã có mã lucky · Chưa có mã lucky · Đã đặt PIN ·
Đã sửa tay · Đã huỷ tư cách.
Nếu `without_lucky > 0` ⇒ badge cảnh báo đỏ "Còn N người chưa có mã lucky" ngay cạnh nút cấp mã.

**Bộ lọc** (`_filters.php`, form GET): Đơn vị → **Bộ phận** (dropdown phụ thuộc, AJAX theo pattern
`Dependent Dropdown`) → **Phòng ban** (phụ thuộc Bộ phận) · Loại người tham dự · Trạng thái mã lucky ·
Đã sửa tay · Trạng thái · Từ khoá · nút "Tìm kiếm" / "Xoá lọc".

**Bảng dữ liệu** (phân trang 25/50/100)

| # | Cột | Ghi chú |
|---|-----|---------|
| 1 | STT | |
| 2 | Mã lucky | in đậm + `DHMT123456` + icon copy; rỗng ⇒ badge xám "Chưa cấp" |
| 3 | Họ và tên | **inline edit**; link phụ sang `admin/attendees/view` theo `attendee_id` |
| 4 | Mã NV | inline edit |
| 5 | Đơn vị | `property_name`, inline edit |
| 6 | Bộ phận | `division_name`, rỗng ⇒ badge vàng "Chưa xác định", inline edit |
| 7 | Phòng ban | `department_name`, inline edit |
| 8 | Chức danh | `position_display`, inline edit |
| 9 | Size áo | inline edit (dropdown) |
| 10 | Loại | badge `finalist` / `director` / `driver` |
| 11 | PIN | badge "Đã đặt" / "Chưa đặt" |
| 12 | Trạng thái | badge theo `getStatusLabel()` |
| 13 | Thao tác | "Sửa" (mở `_modal_edit_row` — sửa đủ trường, tốt cho mobile) · "Gộp/Tách" · "Xoá" |

**Đánh dấu trường đã sửa thủ công** (yêu cầu cốt lõi)
- Ô thuộc `overridden_fields` ⇒ viền trái màu cam + icon ✎ nhỏ.
- Hover/tooltip: **"Đã sửa tay — Gốc: {source_snapshot.field}"**.
- Icon ↺ **"Khôi phục gốc"** ngay trong ô → gọi `resetField`.
- Dòng có `has_override = 1` ⇒ badge "Đã sửa tay (N trường)" ở cột Trạng thái.
- Có nút lọc nhanh ở header: "Chỉ xem dòng đã sửa tay".

**Inline edit (JS)**
- Click icon ✎ hoặc double-click ô ⇒ biến thành input/select tại chỗ.
- Enter hoặc blur ⇒ `POST actionUpdateField` (JSON) → cập nhật ô + thêm dấu override +
  `Toast.success('Đã cập nhật.')`.
- Esc ⇒ huỷ. Lỗi ⇒ **giữ giá trị cũ** + `Toast.error(message)`, **không reload**.
- Không dùng Bootstrap Alert ở bất kỳ đâu; xoá dòng / khôi phục hàng loạt dùng **SweetAlert2** qua
  `confirmDelete(formId)` / `MyHelper::renderDeleteButton()`.

### Xuất Excel
`actionExport` lấy toàn bộ dòng theo bộ lọc (`per_page` lớn, giới hạn 10.000, đọc theo chunk).
Cột: Mã lucky · Định danh (`DHMT`+lucky) · Họ tên · Mã NV · CCCD · Ngày sinh · Đơn vị · Nhãn in thẻ ·
Bộ phận · Phòng ban · Chức danh hiển thị · Chức danh gốc (SMILE) · Size áo · Loại · Trạng thái ·
Đã sửa tay (các trường).
Tên file: `TongHop_VCK_Lucky_{event_id}_{Ymd_His}.xlsx`.

---

## 10. Luồng nghiệp vụ

### 10.1 Đồng bộ (migrate) từ danh sách VCK — có dry-run
```mermaid
sequenceDiagram
  participant HO as Admin HO
  participant JS as finalroster-admin.js
  participant C as FinalRosterController
  participant M as Model FinalRosters
  participant API as BE /api/final-rosters/sync
  participant SVC as FinalRosterService
  participant SRC as FinalAggregationService

  HO->>JS: Bấm "Đồng bộ từ danh sách VCK" → chọn phạm vi → "Xem trước"
  JS->>C: POST /admin/finalRoster/syncPreview
  C->>M: FinalRosters::syncViaApi({mode:'preview'})
  M->>API: POST /api/final-rosters/sync
  API->>SVC: sync(eventId, periodId, property, 'preview')
  SVC->>SRC: listFinalAttendees(periodId, property)
  SRC-->>SVC: danh sách finalist (+ source_attendee_ids)
  SVC->>SVC: buildDedupKey → gộp người → chọn bản gốc
  SVC->>SVC: đối chiếu final_rosters; mỗi field: nếu nằm trong overridden_fields → SKIP
  SVC->>SVC: ghi final_roster_sync_logs(mode=preview)
  SVC-->>API: {inserted, updated, skipped_override, unchanged, conflicts, detail}
  API-->>JS: JSON summary
  JS-->>HO: Bảng kết quả xem trước + cảnh báo gộp
  HO->>JS: Bấm "Ghi thật"
  JS->>C: POST /admin/finalRoster/sync (mode=apply)
  C->>API: POST /api/final-rosters/sync {mode:'apply'}
  API->>SVC: sync(..., 'apply') trong transaction + cache lock
  SVC->>SVC: insert/update; set last_synced_at + source_snapshot; KHÔNG chạm trường override
  SVC-->>API: summary thật
  API-->>JS: {success, message}
  JS->>JS: đóng modal + Toast.success + reload
```

**Giả mã lõi đồng bộ (per-field):**
```
foreach (người đã gộp as $src) {
    $key = buildDedupKey($src);
    $row = find(event_id, dedup_key = $key) ?? new FinalRoster(status = ACTIVE);
    $overridden = $row->overridden_fields ?: [];
    foreach (SYNCABLE_FIELDS as $f) {
        if (in_array($f, $overridden)) { $report->skipped_override[] = [$row, $f]; continue; }
        $row->$f = $src->$f;
    }
    $row->attendee_id         = $src->primary_attendee_id;   // luôn cập nhật (hệ thống)
    $row->source_attendee_ids = $src->attendee_ids;          // luôn cập nhật
    $row->source_snapshot     = snapshot($src);              // luôn cập nhật
    $row->last_synced_at      = time();
    // lucky_number: KHÔNG BAO GIỜ ghi ở bước đồng bộ
    $row->save();
}
// Người có trong final_rosters nhưng không còn trong nguồn VCK:
//   status = WITHDRAWN (không xoá, giữ mã lucky) + báo cáo
```

### 10.2 Cấp mã lucky (gộp người + ghi ngược `attendees`)
```mermaid
sequenceDiagram
  participant HO as Admin HO
  participant C as Controller
  participant API as BE /api/final-rosters/provision-lucky
  participant SVC as FinalRosterService
  participant DB as MySQL

  HO->>C: Bấm "Cấp mã lucky" → xác nhận
  C->>C: PermissionHelper::can('finalroster','create')
  C->>API: POST {event_id, property_id}
  API->>SVC: provisionLucky()
  loop mỗi dòng lucky_number IS NULL
    SVC->>DB: tìm lucky_number sẵn có trong source_attendee_ids
    alt đã có mã
      SVC->>DB: tái dùng mã đó (reused++); nếu nhiều mã khác nhau → chọn mã cấp sớm nhất + log conflict
    else chưa có
      SVC->>DB: sinh 6 số, kiểm tra trùng trên attendees + final_rosters (retry 3 lần nếu dup key)
    end
    SVC->>DB: UPDATE final_rosters.lucky_number
    SVC->>DB: UPDATE attendees.lucky_number WHERE id = final_rosters.attendee_id
    SVC->>DB: SET NULL các attendee trùng người đang giữ mã KHÁC + log conflict
  end
  SVC-->>API: {provisioned, reused, conflicts}
  API-->>C: JSON
  C-->>HO: Toast.success + reload
```

### 10.3 Sửa thủ công & khôi phục gốc
```mermaid
sequenceDiagram
  participant HO as Admin HO
  participant JS as Inline edit
  participant C as Controller
  participant API as BE

  HO->>JS: Click ✎ ô "Bộ phận" → nhập → Enter
  JS->>C: POST /admin/finalRoster/updateField {id, fields:{division_name:"..."}}
  C->>C: can('finalroster','update'); email = AuthHandler::getUser()['email']
  C->>API: POST /api/final-rosters/update/{id} {fields, updated_by}
  API->>API: ghi cột thật + push "division_name" vào overridden_fields + has_override=1 + updated_at=time() + audit_logs
  API-->>C: {success, data}
  C-->>JS: JSON
  JS->>JS: cập nhật ô + hiện dấu ✎ cam + Toast.success

  HO->>JS: Bấm ↺ "Khôi phục gốc"
  JS->>C: POST /admin/finalRoster/resetField {id, fields:["division_name"]}
  C->>API: POST /api/final-rosters/reset-field/{id}
  API->>API: gỡ khỏi overridden_fields + ghi lại từ source_snapshot
  API-->>JS: {success}
  JS->>JS: bỏ dấu ✎ + Toast.success('Đã khôi phục dữ liệu gốc.')
```

---

## 11. Rủi ro & edge case

| # | Tình huống | Xử lý |
|---|-----------|-------|
| 1 | **Hai nguồn sự thật lệch nhau** (`attendees` vs `final_rosters`) | Đây là cái giá của kiến trúc bảng riêng. Giảm thiểu: hiện `last_synced_at` ngay trên header; badge "Dữ liệu có thể đã cũ" nếu > 24h; nút đồng bộ luôn sẵn; mọi báo cáo bốc thăm **chỉ** đọc bảng mới |
| 2 | **Trùng mã lucky** khi cấp song song | UNIQUE trên cả 2 bảng; retry 3 lần bắt duplicate key (SQLSTATE 23000); cache lock theo `event_id` ⇒ 409 |
| 3 | **Một người giữ 2 mã khác nhau trên `attendees`** (do `run:gen-lucky` cũ đã cấp theo bản ghi) | Bước cấp mã **tái dùng mã cấp sớm nhất** và `SET NULL` mã còn lại + ghi `conflicts`. ⚠️ Nếu mã bị NULL **đã phát ra ngoài** ⇒ người đó đăng nhập thất bại. **Phải chạy đối soát trước khi phát định danh** (slice S8) |
| 4 | **Người bị huỷ tư cách sau khi đã migrate** | Không còn trong nguồn VCK ⇒ đồng bộ set `status = WITHDRAWN`, **không xoá**, **giữ mã lucky** (không tái sử dụng). Mặc định ẩn khỏi danh sách (filter `status=1`), có filter riêng để xem. `attendees.is_active = 0` ⇒ cổng chạy tự chặn đăng nhập. Xem §13 câu hỏi |
| 5 | **Người thay thế** | Là người khác ⇒ dòng mới ⇒ mã mới. Phải chạy **đồng bộ + cấp mã** sau mỗi lần thay người, nếu không người thay **không đăng nhập được** cổng chạy. Khuyến nghị gộp hai bước vào một nút |
| 6 | **Đơn vị nộp muộn** | Đồng bộ lại ⇒ dòng mới; `skipped_override` không ảnh hưởng. Badge "Còn N người chưa có mã" nhắc HO cấp bù |
| 7 | **Gộp sai / tách sai** (trùng tên, thiếu `staff_code`) | Preview báo `possible_wrong_merge` / `duplicate_person`; HO dùng chức năng Tách/Gộp (§7.3). Khuyến nghị **không** auto-merge theo họ tên khi thiếu ngày sinh |
| 8 | **Sửa tay làm `dedup_key` đổi** (vd điền `staff_code` mới) | Lần đồng bộ sau sinh khoá mới ⇒ **tạo dòng mới** và dòng cũ thành `WITHDRAWN`. Service phải phát hiện (so `source_attendee_ids` giao nhau) và báo `duplicate_person` thay vì âm thầm tách. `dedup_key` **không** nằm trong `EDITABLE_FIELDS` |
| 9 | **Override "khoá chết" dữ liệu sai** | Nếu HO sửa sai rồi quên, migrate sau không sửa được. Bắt buộc có nút **"Khôi phục gốc"** từng trường + báo cáo "Các trường đang bị override" trong kết quả đồng bộ |
| 10 | **`overridden_fields` chứa tên trường không còn tồn tại** (đổi schema) | Khi đọc, lọc theo `EDITABLE_FIELDS` hiện hành; bỏ qua tên lạ, không lỗi |
| 11 | **JSON không hỗ trợ** (MySQL 5.6) | Dùng `TEXT` + cast `array` ở Laravel; thay `JSON_LENGTH/JSON_CONTAINS` bằng cột `has_override` + lọc ở tầng ứng dụng |
| 12 | **Người `manual` (HO tự thêm)** | `attendee_id = NULL` ⇒ **không ghi ngược lucky về `attendees`** ⇒ **không đăng nhập cổng chạy được**. UI phải cảnh báo đỏ; §13 câu hỏi |
| 13 | **Rò rỉ danh sách mã lucky** (Excel) | Mã chỉ là *định danh*, vẫn cần PIN. `login_pin` **không bao giờ** trả qua API/Excel/log; mã không đưa vào URL công khai |
| 14 | **Hiệu năng export / đồng bộ** | Chunk 500 dòng; export tối đa 10.000 dòng/lần; đồng bộ bọc transaction + lock, chạy được qua artisan nếu dữ liệu lớn |
| 15 | **Đồng bộ chạy chồng** (nút + cron + artisan) | Cache lock `final-rosters:sync:{event_id}` TTL 300s ⇒ 409 "Đang có tiến trình đồng bộ khác" |
| 16 | **Sai `period_id`** (đợt không `is_final`) | BE validate, trả 422 "Đợt không phải Vòng Chung Kết" |

---

## 12. Phân rã công việc (vertical slice)

| Slice | Nội dung | Verify được bằng | Ước lượng | Phụ thuộc |
|-------|----------|------------------|-----------|-----------|
| **S0** | Migration `final_rosters` + `final_roster_sync_logs`; Entity `FinalRoster` (SoftDeletes, cast `overridden_fields`/`source_attendee_ids`/`source_snapshot` = array, timestamp unix, hằng `EDITABLE_FIELDS`/`SYNCABLE_FIELDS`/`STATUS_*`, accessor `position_display`). Bổ sung `lucky_number` + `pin_is_set` vào `AttendeeResource` | `artisan migrate` + tinker tạo/đọc 1 dòng, cast JSON đúng | **M (1d)** | — |
| **S1** | `FinalRosterService::buildDedupKey()` + logic **gộp người** + chọn bản gốc + fill-in + phát hiện conflict. Unit test với bộ dữ liệu giả (cùng `staff_code`, cùng `id_card`, trùng tên thiếu ngày sinh) | Test: 3 bản ghi 1 người ⇒ 1 kết quả; 2 người trùng tên ⇒ báo conflict | **M (1d)** | S0 |
| **S2** | `FinalRosterService::sync()` + **dry-run/preview** + **bảo vệ per-field** + ghi `final_roster_sync_logs`. Endpoint `POST /api/final-rosters/sync`. Command `final-roster:sync --dry-run` | Tinker/artisan: chạy preview ⇒ số liệu; apply ⇒ dữ liệu vào bảng; **sửa 1 trường rồi apply lại ⇒ trường đó KHÔNG đổi, báo `skipped_override`** | **L (3d)** | S1 |
| **S3** | BE: `GET /api/final-rosters` (filter đầy đủ, phân trang, sort) + `/filters` + `/stats` | Postman: lọc theo đơn vị/bộ phận/phòng ban/`has_override` ra đúng | **M (1d)** | S0 |
| **S4** | FE: `ApiEndpoints` + model `FinalRosters` + `FinalRosterController::actionAdmin` + `admin.php` + `_filters.php` (bảng + lọc + phân trang + thống kê, **chỉ đọc**) | Mở `/admin/finalRoster/admin?event_id=3&period_id=4`, lọc ra đúng người | **L (3d)** | S3 |
| **S5** | FE: dropdown phụ thuộc Đơn vị → Bộ phận → Phòng ban (AJAX) | Chọn đơn vị ⇒ bộ phận tự nạp đúng danh sách | **S (4h)** | S4 |
| **S6** | FE: `_modal_sync` (xem trước ⇒ ghi thật) + `actionSyncPreview`/`actionSync` + hiển thị báo cáo `skipped_override`/`conflicts` | Bấm xem trước thấy số liệu; ghi thật dữ liệu vào bảng; trường đã sửa tay nằm trong danh sách bỏ qua | **M (1d)** | S2, S4 |
| **S7** | BE+FE: `update/{id}` + `reset-field/{id}` + inline edit nhiều trường + `_modal_edit_row` + badge ✎ + tooltip "Gốc: …" + nút ↺ | Sửa 3 trường khác nhau ⇒ `overridden_fields` đúng; khôi phục 1 trường ⇒ gỡ đúng tên; audit log có bản ghi | **L (3d)** | S0, S4 |
| **S8** | BE: `provisionLucky` theo người đã gộp + **ghi ngược `attendees`** + xử lý mã trùng người + lock/retry. Command `final-roster:gen-lucky`. FE: `_modal_gen_lucky` + `actionGenLucky`. **Kèm báo cáo đối soát mã cũ** | Tinker: chạy 2 lần ⇒ lần 2 `provisioned=0`; người có 2 bản ghi ⇒ **1 mã**; `attendees.lucky_number` của bản đại diện đúng; **test cổng chạy: `DHMT`+mã login được** | **L (3d)** | S0, S1 |
| **S9** | FE: xuất Excel theo bộ lọc (PHPExcel) | Tải file, kiểm đủ cột + đúng bộ lọc + cột "Đã sửa tay" | **M (1d)** | S4 |
| **S10** | BE+FE: `merge` / `split` + `_modal_merge_split` + `store` (thêm thủ công) + `destroy` (xoá mềm) | Gộp 2 dòng ⇒ 1 dòng giữ mã cũ hơn; tách ⇒ dòng mới có mã mới | **M (1d)** | S7, S8 |
| **S11** | Phân quyền: thêm `finalroster` vào `MControllers` + `roles.controllers`, gate controller + view, menu sidebar | HR không quyền update ⇒ không thấy nút sửa; URL trực tiếp ⇒ 403 | **S (4h)** | S4, S6, S7, S8 |
| **S12** *(tuỳ chọn)* | Thêm `division_code`/`division_name` vào `attendees` + điền ở `syncWithStaffData` + command backfill (để các màn khác cũng lọc được) | Chạy backfill, đếm số người còn "Chưa xác định" | **M (1d)** | S0 |

**Thứ tự thực thi:**
`S0 → S1 → (S2 ∥ S3) → S4 → (S5 ∥ S6 ∥ S7 ∥ S8) → S9 → S10 → S11 → [S12]`

**Tổng ước lượng:** **~19 ngày công** (không gồm S12 tuỳ chọn ⇒ +1d).
> Tăng ~7 ngày so với phương án query trực tiếp, chủ yếu do S2 (đồng bộ + dry-run + bảo vệ
> per-field: 3d), S7 (sửa tay nhiều trường + khôi phục: 3d) và S8 (gộp người + ghi ngược + đối
> soát: 3d).

**Mốc giao hàng gợi ý:**
- **Mốc 1 (S0–S4, ~8d):** xem được danh sách VCK tổng hợp, đồng bộ qua artisan.
- **Mốc 2 (S5–S8, ~7.5d):** đồng bộ + sửa tay + cấp mã lucky hoạt động đủ trên UI.
- **Mốc 3 (S9–S11, ~2.5d):** Excel, gộp/tách, phân quyền — sẵn sàng production.

---

## 13. Câu hỏi cần chủ dự án chốt

> 3 câu chặn trước đây (gộp người / phạm vi cấp mã / lọc bộ phận) **đã chốt** và nằm ở §0.

### Nhóm A — ảnh hưởng tới vận hành mã lucky
1. **Mã lucky đã cấp theo bản ghi trước đây** (qua `run:gen-lucky`): nếu đối soát phát hiện một
   người đang giữ 2 mã và **cả hai đã phát ra ngoài**, xử lý thế nào? (Đề xuất: giữ mã cấp sớm nhất,
   `SET NULL` mã kia, HO thông báo thu hồi. Cần xác nhận quy trình thông báo.)
2. **Người HO thêm thủ công** (không có trong danh sách VCK, `attendee_id = NULL`): có cần **đăng
   nhập cổng chạy** không? Nếu **có** ⇒ phải tạo kèm một bản ghi `attendees` tối thiểu để ghi ngược
   mã (+1 ngày công). Nếu **không** ⇒ UI chỉ cảnh báo.
3. **Có cho phép sửa tay `lucky_number`** không? (Đề xuất: **không** — tránh phá UNIQUE và BIB đã in.)
4. **Giữ 6 chữ số** cho mã lucky (ràng buộc BIB `run_events.code + lucky_number`) — xác nhận?
5. Sau khi bảng mới lên production, có **ẩn/vô hiệu nút "Cấp số lucky"** cũ ở
   `admin/runRegistrations/admin` không? (Đề xuất: ẩn, vì nút cũ cấp mã theo bản ghi, không gộp người.)

### Nhóm B — vòng đời dữ liệu bảng mới
6. **Người bị huỷ tư cách sau khi đã migrate:** đánh dấu `WITHDRAWN` và **giữ** trong bảng (mặc định
   ẩn, giữ mã) — xác nhận? Hay **xoá mềm hẳn** khỏi bảng tổng hợp?
7. **Trường đã override có cần nút "Khôi phục về dữ liệu gốc"** không? (Đề xuất: **có**, từng trường
   + có cả "khôi phục toàn bộ dòng" — đã đưa vào slice S7. Xác nhận để không làm thừa/thiếu.)
8. Có cần **ghi ngược (write-back)** các trường HO sửa tay về `attendees` không? (Đề xuất: **không** —
   giữ một chiều cho đơn giản; nhưng nếu in thẻ/email vẫn đọc `attendees` thì chức danh sửa tay sẽ
   **không** xuất hiện trên thẻ. **Câu này ảnh hưởng trực tiếp tới câu 9.**)
9. **Chức danh sửa tay áp dụng tới đâu?** Chỉ màn tổng hợp + Excel + bốc thăm, hay cả **in thẻ** và
   **email xác nhận**? Nếu cả thẻ/email ⇒ các module đó phải đọc từ `final_rosters` (hoặc bật
   write-back ở câu 8) — **+1.5 ngày công**.
10. **Có cần lịch sử thay đổi theo trường** (ai sửa gì, lúc nào, giá trị cũ) hiển thị ngay trên UI
    không? (Hiện chỉ ghi `audit_logs` + `updated_by`/`updated_at`. Nếu cần UI lịch sử ⇒ +1 ngày.)
11. **Đồng bộ tự động theo lịch** (cron hằng ngày) hay **chỉ bấm tay**? (Đề xuất: chỉ bấm tay ở giai
    đoạn này, vì cần HO xem preview trước khi ghi.)

### Nhóm C — còn tồn từ bản trước
12. Ai được sửa dữ liệu: Admin HO + HR? Đơn vị **không** — xác nhận?
13. Có cần chức năng **reset PIN** cho người quên PIN trên màn này không? (~4h)
14. **Có cần đóng băng danh sách** tại thời điểm bốc thăm không? (Bảng mới đã gần như là snapshot;
    nếu cần bất biến tuyệt đối thì thêm cột `locked_at` + chặn mọi sửa/đồng bộ sau khi khoá — ~4h.)
15. **Excel chứa mã lucky** phát cho ai? Cần thêm cột nào (SĐT, email) để phân phát định danh?
16. **Tên bảng** chốt là `final_rosters` hay `final_attendee_rosters` / `vck_rosters`?
