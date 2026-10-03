# Tổng hợp danh sách Vòng Chung Kết & cấp mã Lucky

> Tài liệu phân tích nghiệp vụ (PRD) — bảng riêng `final_attendee_rosters` chứa toàn bộ người vào
> Vòng Chung Kết (VCK) của tất cả đơn vị; đồng bộ (migrate) lặp lại được từ `attendees`; sửa thủ
> công mọi trường với bảo vệ chống ghi đè theo từng trường; cấp mã lucky draw **duy nhất, một lần,
> đi theo người suốt sự kiện**.
> Cập nhật: 2026-10-03 (bản revise 4 — **CHỐT**).
> Liên quan: `docs/chung-ket-fun-run.md`, `docs/system-design.md`, memory `vck-final-aggregation`,
> memory `replace-withdraw-attendee`.
>
> ## ✅ TRẠNG THÁI: TOÀN BỘ CÂU HỎI CHẶN ĐÃ ĐƯỢC CHỐT — **slice S0 sẵn sàng triển khai**
>
> | | |
> |---|---|
> | Quyết định đã chốt | **18/18** (xem §0) |
> | Câu hỏi còn tồn | **12 câu, KHÔNG câu nào chặn** (xem §14) |
> | Slice bắt đầu được ngay | **S0** — migration `final_attendee_rosters` + `final_attendee_roster_sync_logs` + Entity + index/unique (§7) |
> | Tổng ước lượng chốt | **≈ 22.0 ngày công** (+1.0 nếu làm S14 tuỳ chọn) |
>
> Thứ tự slice: `S0 → S1 → (S2 ∥ S3) → S4 → (S5 ∥ S6 ∥ S7 ∥ S8) → S9 → S10 → S11 → S12 → S13 → [S14]`
> — chi tiết ở **§13**.

---

## 0. Các quyết định đã chốt với chủ dự án

| # | Vấn đề | Quyết định |
|---|--------|-----------|
| 1 | Một người có nhiều bản ghi `attendees` | **GỘP thành 1 người, cấp 1 mã lucky duy nhất** |
| 2 | Phạm vi cấp mã | **Toàn bộ người vào Vòng Chung Kết** (không giới hạn chỉ `approved`) |
| 3 | Lọc theo bộ phận (division) | **Có** — cần lọc riêng |
| 4 | Kiến trúc | **TÁCH HẲN RA MỘT BẢNG RIÊNG**, chỉ chứa người vào Chung Kết |
| 5 | Đồng bộ | Có **tính năng migrate từ attendees VCK**, **chạy lại nhiều lần** được |
| 6 | Sửa thủ công | **Mọi trường** trong bảng mới sửa được |
| 7 | Chống ghi đè | Trường nào đã sửa thủ công thì lần migrate sau **KHÔNG ghi đè trường đó** (bảo vệ **theo từng trường**) |
| 8 | **Vòng đời mã lucky** | **Gen một lần duy nhất cho một người, KHÔNG BAO GIỜ đổi, đi theo người suốt sự kiện.** Không có chức năng "cấp lại mã". Không cho sửa tay `lucky_number` |
| 9 | **Người HO thêm thủ công** | **PHẢI đăng nhập được** ⇒ hệ thống tạo kèm **bản ghi `attendees` tối thiểu** trong cùng transaction |
| 10 | **Chức danh sửa tay** | Áp dụng cho **TẤT CẢ**: màn tổng hợp, Excel, bốc thăm, **in thẻ**, **email xác nhận** |
| 11 | **Người bị huỷ tư cách sau migrate** | **XOÁ MỀM hẳn** (không dùng trạng thái `WITHDRAWN` cho ca này). **Mã lucky bỏ luôn — không tái sử dụng cho người khác** |
| 12 | Tên bảng | **`final_attendee_rosters`** (log: **`final_attendee_roster_sync_logs`**) |
| 13 | **Vai trò người HO thêm tay** | **`btc` — "Ban tổ chức"** (id 8 ở môi trường hiện tại). **KHÔNG hardcode id**: resolve theo `code = 'btc'`, fallback param `finalRosterManualRoleId` / `FINAL_ROSTER_MANUAL_ROLE_ID` (§9.4) |
| 14 | **Số thẻ người HO thêm tay** | **`MT` + 3 số** (`MT001`…`MT999`), **duy nhất toàn hệ thống** (không đánh lại theo sự kiện), max hiện có + 1, lock + retry, **tràn 999 ⇒ 422** báo lỗi rõ ràng (§9.5) |
| 15 | **Nút "Cấp số lucky" cũ** | **ẨN** ở `admin/runRegistrations/admin` ⇒ đường cấp mã duy nhất là qua bảng mới (§9.6) |
| 16 | **Đối soát dải `MT%`** | Làm **qua API, KHÔNG query DB tay** ⇒ endpoint chỉ-đọc `GET /api/final-attendee-rosters/audit` (§8.12) |
| 17 | **Scope đánh số thẻ** | **Toàn hệ thống** (phương án B). Báo cáo/lọc vẫn theo `event_id`, **không** dựa vào tiền tố số thẻ |
| 18 | **Endpoint audit** | **MỘT endpoint audit tổng hợp** cho cả `lucky` lẫn `badge` (§8.12) |

---

## 1. Mục tiêu & phạm vi

### Mục tiêu
Sau khi các đơn vị đã đăng ký và kết quả vòng loại đã chốt, HO cần **một bảng tổng hợp duy nhất**
chứa **toàn bộ người vào Vòng Chung Kết của mọi đơn vị**, để:

1. Tra cứu / lọc theo **đơn vị (property)**, **bộ phận (division)**, **phòng ban (department)**.
2. **Cấp mã lucky draw** duy nhất **theo người** — mã này đồng thời là **mã đăng nhập**
   (`DHMT` + lucky) và gốc ghép **số BIB** của cổng chạy (xem `chung-ket-fun-run.md` §3).
3. **Sửa thủ công mọi thông tin** của từng người, độc lập với dữ liệu đồng bộ từ SMILE / phiếu đăng ký.
4. **Đồng bộ lại** từ danh sách VCK bất cứ lúc nào mà **không mất** những gì HO đã sửa tay.
5. Xuất Excel danh sách tổng hợp (kèm mã lucky) để phân phát định danh và phục vụ bốc thăm.
6. Đảm bảo **chức danh HO sửa tay xuất hiện ở mọi nơi**: thẻ tham dự, email xác nhận, Excel, bốc thăm.

### Trong phạm vi
- Bảng DB mới + bảng log + đồng bộ idempotent, có **dry-run/preview**.
- Màn hình admin: bộ lọc + bảng phân trang + tìm kiếm + thống kê.
- Inline edit **nhiều trường**, badge đánh dấu trường đã sửa tay + nút khôi phục dữ liệu gốc.
- Cấp mã lucky theo người (gộp trùng), **idempotent tuyệt đối**.
- **Write-back** chức danh/nhãn thẻ về `attendees` để thẻ & email hưởng (§5.4).
- **Thêm người thủ công** kèm tạo bản ghi `attendees` tối thiểu để người đó đăng nhập được.
- Xuất Excel; phân quyền controller mới.

### Ngoài phạm vi (giai đoạn này)
- Cơ chế **bốc thăm** (quay số, giải thưởng, màn hình trình diễn) — tài liệu riêng.
- Gửi mã lucky/PIN tự động qua email/SMS/Zalo.
- Thiết kế lại module in thẻ (chỉ bảo đảm thẻ **đọc đúng** chức danh override).

---

## 2. Người dùng & quyền

| Actor | Quyền trên màn hình này |
|-------|------------------------|
| **Admin HO** | Toàn quyền: xem, lọc, đồng bộ, cấp mã lucky, sửa mọi trường, khôi phục gốc, thêm/gộp/tách/xoá, xuất Excel |
| **Nhân sự HO (HR)** | Xem + lọc + xuất Excel; sửa dữ liệu nếu được cấp `update` |
| **BTC các ban** | Chỉ xem (read) |
| **Đại diện đơn vị** | **Không** truy cập |

### Permission mới
Controller: **`finalattendeerosters`** (key chữ thường trong `MControllers`).

| Action | Quyền yêu cầu |
|--------|---------------|
| `admin`, `export`, `syncPreview` | `finalattendeerosters.read` |
| `updateField`, `resetField` | `finalattendeerosters.update` |
| `sync`, `genLucky`, `create` (thêm thủ công), `merge`, `split` | `finalattendeerosters.create` |
| `delete` (xoá mềm / huỷ tư cách) | `finalattendeerosters.delete` |

Check `PermissionHelper::can('finalattendeerosters', 'update')` trong **controller** *và* ẩn/hiện nút
trong **view**. Cấu hình: thêm bản ghi vào `MControllers` + cột `roles.controllers`.

---

## 3. Quyết định thiết kế then chốt

### 3.1 Bảng riêng — ranh giới nguồn sự thật

| Việc | Nguồn sự thật |
|------|---------------|
| Đăng ký / duyệt / thay thế / huỷ tư cách | `attendees` (không đổi) |
| Danh sách VCK tổng hợp, mã lucky, dữ liệu HO chỉnh tay, bốc thăm, in danh sách | **`final_attendee_rosters`** |
| Đăng nhập cổng chạy + số BIB | `attendees.lucky_number` (giữ nguyên — §6.3) |
| Chức danh / nhãn thẻ in trên badge + email | `attendees.position` / `unit_label` — **được write-back từ bảng mới** (§5.4) |

Hệ quả bắt buộc: quy trình đồng bộ **một chiều có kiểm soát** (`attendees` → bảng mới), chạy lại
được, không ghi đè trường HO đã sửa; **cộng** một luồng write-back **hẹp, có chủ đích** cho đúng
vài trường in thẻ/email.

### 3.2 Cơ chế theo dõi "trường nào đã sửa thủ công"

| Phương án | Ưu | Nhược |
|-----------|-----|------|
| **A. Cột JSON `overridden_fields`** | Giá trị sống ở **cột thật** ⇒ **lọc/sort/index chạy bình thường**; 1 query đủ dòng + cờ override; migrate chỉ cần `in_array()`; thêm trường editable mới **không cần migration** | Không ràng buộc tên trường ở cấp DB; lọc "dòng nào override trường X" phải `JSON_CONTAINS` |
| **B. Cột `*_override` song song** | Kiểu dữ liệu rõ, giữ cả gốc lẫn sửa | ~20 trường ⇒ ~20 cột; mọi filter/sort phải `COALESCE` ⇒ **index vô dụng**; thêm trường phải migration |
| **C. Bảng `*_overrides` key-value** | Mềm dẻo nhất, có lịch sử theo trường | JOIN/aggregate mỗi lần render 600–5.000 dòng × 20 trường; lọc/sort rất khó |

#### ✅ CHỌN PHƯƠNG ÁN A — cột JSON `overridden_fields`

**Lý do:**
1. **Hiệu năng & đơn giản của query:** màn hình này chủ yếu là **lọc + sort**; giá trị nằm ở cột
   thật nên dùng **index trực tiếp**, không `COALESCE`, không JOIN. Đây là ưu điểm quyết định.
2. **Migrate an toàn:** `if (!in_array($f, $overridden)) { $row->$f = $src->$f; }` — per-field, dễ test.
3. **Mở rộng không tốn migration:** khai báo `EDITABLE_FIELDS` ở Entity.
4. **Giữ giá trị gốc không nhân đôi cột:** `source_snapshot` (JSON ảnh dữ liệu gốc lần migrate cuối)
   đủ cho tooltip "Gốc: …" và nút **"Khôi phục gốc"**.

**Ảnh hưởng query / filter / index:**
- Filter & sort thông thường: **không ảnh hưởng** (cột thật, có index).
- Lọc "đã sửa tay": cột phụ **`has_override` TINYINT(1)** + index `(event_id, has_override)`;
  chi tiết theo trường dùng `JSON_CONTAINS(overridden_fields, '"position"')` (truy vấn phụ, tần suất thấp).
- MySQL ≥ 5.7. Nếu production là 5.6 ⇒ `TEXT` + cast `array` ở Laravel.

**Quy ước lưu:**
- `overridden_fields`: JSON array tên cột đã override, mặc định `[]` (không dùng `NULL`).
- Sửa trường `X` ⇒ ghi cột `X` **và** push `"X"` vào `overridden_fields`, set `has_override = 1`.
- **"Khôi phục gốc"** trường `X` ⇒ gỡ `"X"` khỏi `overridden_fields` **và** ghi lại giá trị từ
  `source_snapshot.X`; lần migrate sau cập nhật trường đó bình thường.
- `lucky_number` **không bao giờ** nằm trong `overridden_fields` (không sửa tay — §6).

---

## 4. Nguồn dữ liệu & phạm vi "người vào Chung Kết"

### 4.1 Nguồn đồng bộ (đã kiểm chứng trong code)
Người vào Chung Kết = attendee trên phiếu của đợt `registration_periods.is_final = 1`. Cơ chế này
**đã có sẵn** (memory `vck-final-aggregation`):
- `registration_periods.is_final`, `attendees.attendee_type` (`finalist`/`director`/`driver`),
  bảng `final_attendee_contents`.
- `FinalAggregationService::listFinalAttendees` (**đã lọc `is_active = 1`**, **đã trả
  `source_attendee_ids`**), `buildFinal`, `seedRegistration`, `ensureUnitRegistrations`.
- Endpoint `GET /api/registration-finals/attendees`; command `vck:build`, `vck:sync-unit`.

⇒ **Nguồn đồng bộ = `FinalAggregationService::listFinalAttendees`** — tái dùng, **không** viết lại
logic xác định finalist.

### 4.2 Cây tổ chức
```
properties (ĐƠN VỊ)              ← attendees.property_id
   └── divisions (BỘ PHẬN)        ← divisions: property_code, code, name
         └── departments (PHÒNG BAN) ← departments: property_code, division_code, code, name
               └── staffs         ← staffs: property_code, division_code, department_code, position_code
```

| Khái niệm | Trên `attendees` | Trạng thái |
|-----------|------------------|-----------|
| Đơn vị | `property_id` (+ `unit_label` = **nhãn in thẻ**) | ✅ Có |
| Phòng ban | `department_code`, `department_name` | ✅ Có (set ở `AttendeeService::syncWithStaffData`) |
| Bộ phận | **KHÔNG CÓ cột** | ❌ Thiếu |
| Chức danh SMILE | `position_code`, `position_name` | ✅ Có |
| Chức danh đơn vị nhập | `position` | ✅ Có |

> `AttendeeResource` có trả `division_code`/`division_name` nhưng bằng fallback
> `$this->staff->division_code` / `$this->staff->division->name` ⇒ **chỉ có khi `staff_id` tồn tại**,
> và **không lọc/sort được**.

### 4.3 Giải quyết bộ phận
Bảng mới có cột `division_code`/`division_name` riêng ⇒ trình đồng bộ giải theo thứ tự:
1. `staffs.division_code` + `divisions.name` — nếu có `staff_id`/`staff_code`.
2. Tra `departments` theo (`property_code`, `department_code`) → `division_code` → tên từ `divisions`.
3. Không tra được ⇒ rỗng; UI gom vào **"Chưa xác định"** (badge vàng) + filter riêng để HO **sửa tay**
   (sửa rồi migrate sau không ghi đè).

> Thêm `division_*` vào `attendees` là **slice tuỳ chọn S13**, không chặn tính năng này.

---

## 5. Sửa thủ công — trường, quy tắc, và lan toả sang thẻ/email

### 5.1 Trường được sửa (`EDITABLE_FIELDS`)
| Nhóm | Trường |
|------|--------|
| Định danh | `full_name`, `staff_code`, `id_card`, `birthday`, `gender`, `phone_number`, `email` |
| Tổ chức | `property_id`, `property_name`, `unit_label`, `division_code`, `division_name`, `department_code`, `department_name` |
| Chức danh | `position` (chức danh **hiển thị**), `position_name` (gốc SMILE — chỉ sửa khi thật cần) |
| Khác | `shirt_size`, `attendee_type`, `note`, `sort_order` |

### 5.2 Trường **KHÔNG** được sửa
`id`, `event_id`, `attendee_id`, `source_attendee_ids`, `dedup_key`, **`lucky_number`**,
`lucky_provisioned_at`, `source_snapshot`, `overridden_fields`, `has_override`, `status`,
các cột `*_by` / `*_at`, `deleted_at`.

> `lucky_number` **không sửa tay** (quyết định #8): mã sinh một lần, không đổi, nên không có đường
> sửa thủ công — tránh phá UNIQUE, phá BIB đã in và phá định danh đã phát.

### 5.3 Chức danh: 3 lớp
| Lớp | Nơi lưu | Bị migrate ghi đè? |
|-----|---------|--------------------|
| Gốc SMILE | `final_attendee_rosters.position_name` ← `attendees.position_name` | ✅ Có (trừ khi override) |
| Đơn vị nhập | `final_attendee_rosters.position` ← `attendees.position` | ✅ Có (trừ khi override) |
| **HO sửa tay** | `position` + `"position"` trong `overridden_fields` | ❌ **Không bao giờ** |

Chức danh hiển thị: **`position_display`** = `position` ?: `position_name` ?: `''` — accessor duy
nhất trên Entity, mọi nơi dùng chung.

### 5.4 ⭐ Lan toả chức danh sửa tay sang **thẻ** và **email** (quyết định #10)

#### Hai phương án
| | **P1 — Write-back về `attendees`** | **P2 — Sửa luồng badge/email đọc từ bảng mới** |
|---|---|---|
| Cách làm | Khi HO sửa `position`/`unit_label`/`shirt_size`/`full_name`, ghi luôn vào `attendees` của bản ghi đại diện | Sửa module badge + `EmailHelper` để join `final_attendee_rosters` theo `attendee_id` và lấy `position_display` |
| Số điểm phải sửa | **1 điểm ghi** (service update) | **N điểm đọc**: module badge, `EmailHelper::sendRegistrationConfirmation`, `registration_confirmation_pdf.php`, `PdfHelper`, các màn duyệt/báo cáo nếu muốn nhất quán |
| Nơi khác tự hưởng | ✅ Màn duyệt, báo cáo, Excel cũ, cổng chạy… tất cả đọc `attendees` nên tự đúng | ❌ Chỉ đúng ở những chỗ đã sửa; các chỗ còn lại vẫn hiện chức danh cũ ⇒ **lệch trong cùng một kỳ** |
| Rủi ro | Sync SMILE / update attendee có thể ghi đè lại (phải chặn) | Nhiều điểm sửa ⇒ nhiều chỗ có thể quên; phải xử lý attendee không có dòng roster (người vòng loại) |
| Ảnh hưởng người liên quan | Attendee thực sự "đổi chức danh" trong toàn hệ thống — **đúng ý chủ dự án** | Chức danh chỉ "đẹp" ở thẻ/email, dữ liệu gốc vẫn sai |

#### ✅ CHỌN **P1 — Write-back về `attendees`**
**Lý do:** quyết định #10 là "áp dụng cho **TẤT CẢ**". Chỉ P1 đạt được điều đó bằng **một điểm
ghi**; P2 phải sửa rải rác và vẫn để lại chỗ lệch (màn duyệt, báo cáo, thẻ in lại từ luồng khác).
P1 cũng rẻ hơn về công sức và ít rủi ro bỏ sót.

#### Phạm vi write-back (chỉ các trường có trên `attendees`)
| Trường roster | Ghi về `attendees` | Ghi chú |
|---------------|--------------------|---------|
| `position` | `position` | Chức danh in thẻ & email |
| `unit_label` | `unit_label` | Nhãn đơn vị in thẻ |
| `shirt_size` | `shirt_size` | |
| `full_name` | `full_name` | ⚠️ Xem rủi ro bên dưới |
| `phone_number` | `phone_number` | |
| `note` | `note` | |
| `division_*`, `department_*`, `property_name`, `staff_code`, `id_card`, `birthday`, `gender`, `email` | **KHÔNG** write-back | Chỉ sống ở bảng mới (không phục vụ thẻ/email) |

#### ⚠️ Rủi ro & cách chặn ghi đè
1. **`AttendeeService::syncWithStaffData` có ghi đè `position` không?** — **KHÔNG.** Đã kiểm chứng:
   hàm này chỉ set `staff_code`, `position_code`, `position_name`, `department_code`,
   `department_name`, `end_starting_date`, `birthday`, `phone_number`, `phone`. **Không chạm
   `position`, không chạm `unit_label`.** ⇒ sync SMILE **an toàn** với write-back chức danh.
   ⚠️ Nhưng **có ghi đè `phone_number` và `birthday`** ⇒ hai trường này nếu write-back sẽ bị SMILE
   ghi lại; chấp nhận được (tự chữa, xem điểm 4).
2. **`AttendeeService::convertData` có dòng `'position' => $input['position'] ?? null`** ⇒ nếu một
   luồng nào gọi `attendees/update` mà **không gửi** `position` thì `position` bị set `NULL`.
   Đây là rủi ro ghi đè thật. **Cách chặn:** điểm 4 (tự chữa) + khuyến nghị kiểm tra các form
   update attendee đều gửi đủ `position` (hiện form đăng ký/duyệt đang gửi đủ).
3. **Whitelist finalist chặn một phần write-back:** `AttendeeService::update` có whitelist **cứng**
   cho `attendee_type = finalist` — chỉ cho `photo_path`, `photo_full_path`, `position`,
   `shirt_size`, `badge_org_name`, `is_active`, `note`. ⇒ `position`/`shirt_size`/`note` qua được,
   nhưng **`unit_label` và `full_name` bị lọc mất**.
   **Cách giải:** write-back **không đi qua** `attendees/update`. Thực hiện **bên trong BE**
   (`FinalAttendeeRosterService::writeBackToAttendee()` ghi thẳng Entity `Attendee` trong cùng
   transaction với update roster) ⇒ không bị whitelist, không thêm endpoint public.
4. **Tự chữa (self-healing):** cuối **mỗi lần đồng bộ** (`mode = apply`), service **re-apply**
   write-back cho tất cả dòng có trường write-back nằm trong `overridden_fields`. Nhờ vậy, nếu một
   luồng khác tình cờ ghi đè, lần đồng bộ sau sẽ đưa về đúng. Idempotent, rẻ.
5. **`full_name` write-back:** đổi tên trên `attendees` ảnh hưởng mail/phiếu/đội thể thao.
   Khuyến nghị **bật cờ cấu hình** `WRITE_BACK_FULL_NAME = false` ở giai đoạn đầu (chỉ sửa tên trong
   bảng mới + Excel + bốc thăm), bật sau khi chủ dự án xác nhận — xem §13.
6. **Audit:** mọi write-back ghi `audit_logs` (`action = final_roster.write_back`) để truy vết khi
   dữ liệu attendee "tự đổi".

---

## 6. Mã Lucky Draw

### 6.1 Hiện trạng (đã kiểm chứng trong code)
- `attendees.lucky_number` (string 20, **UNIQUE cấp bảng ⇒ unique toàn hệ thống**), `login_pin`
  (hash), `pin_set_at`, `login_failed_attempts`, `login_locked_until` — migration
  `Modules/Run/Database/Migrations/2026_10_02_100002_add_run_login_columns_to_attendees_table.php`.
- `RunAuthService::provisionLucky($eventId)` sinh `random_int(100000, 999999)` = **6 chữ số**, chỉ
  cấp cho `lucky_number IS NULL` ⇒ **đã idempotent**; **đã quét đúng phạm vi finalist VCK**
  (`registration_periods.is_final = 1`) ⇒ khớp quyết định #2.
- `RunAuthService::resolve()` tra `Attendee::where('lucky_number', $lucky)->where('is_active', 1)`
  ⇒ **cổng chạy đọc thẳng `attendees`**.
- `AttendeeResource` **chưa trả** `lucky_number` ⇒ phải bổ sung.
- Command `run:gen-lucky {event_id}` + endpoint `POST /api/run-auth/gen-lucky` + nút ở
  `admin/runRegistrations/admin`. **Nhược:** cấp mã **theo từng bản ghi attendee** ⇒ một người nhiều
  bản ghi sẽ nhận **nhiều mã** (sai quyết định #1 và #8).

### 6.2 ⭐ Vòng đời mã: idempotent **tuyệt đối** (quyết định #8)

**Nguyên tắc bất biến — áp dụng cho mọi luồng:**

> Một người (một `dedup_key` trong một `event_id`) có **đúng một** `lucky_number`, được sinh **một
> lần duy nhất**, và **không bao giờ** thay đổi cho tới hết sự kiện.

| Luồng | Hành vi với `lucky_number` |
|-------|----------------------------|
| **Đồng bộ (sync)** | **Tuyệt đối không ghi** `lucky_number`. Dòng mới tạo ra có `lucky_number = NULL`, chờ bước cấp mã |
| **Khôi phục dòng đã xoá mềm** (người được đưa lại vào VCK) | **Giữ nguyên** mã cũ của dòng đó — đúng tinh thần "mã đi theo người" |
| **Sửa tay bất kỳ trường nào** | Không chạm `lucky_number`; `lucky_number` không nằm trong `EDITABLE_FIELDS` |
| **Gộp dòng (merge)** | Dòng giữ lại **giữ mã của mình**; nếu dòng giữ lại chưa có mã mà dòng bị gộp đã có ⇒ **chuyển mã sang** dòng giữ lại. Mã của dòng bị gộp (nếu khác) **không bị tái sử dụng** |
| **Tách người (split)** | Dòng cũ **giữ nguyên mã**; dòng mới được cấp mã **mới** ở lần cấp mã kế tiếp |
| **Cấp mã (provision)** | Chỉ xử lý dòng `lucky_number IS NULL`. Dòng đã có mã ⇒ **bỏ qua hoàn toàn** |
| **"Cấp lại mã"** | **KHÔNG TỒN TẠI** — không có action, không có endpoint, không có nút |
| **Xoá mềm (huỷ tư cách)** | **Giữ mã trên dòng đã xoá** (không SET NULL) ⇒ mã bị "khoá chết", không ai khác nhận được (§6.5) |

**Kiểm chứng idempotent:** chạy `provision-lucky` nhiều lần liên tiếp ⇒ lần thứ hai trở đi
`provisioned = 0`, không có `UPDATE` nào lên `lucky_number`.

### 6.3 Giữ tương thích cổng chạy Fun Run (đã verified — không được phá)

**Ràng buộc:** `attendees.lucky_number` UNIQUE **toàn bảng** ⇒ **không thể** ghi cùng một mã vào
nhiều bản ghi attendee của cùng một người.

**Phương án: "một mã, ghi vào bản ghi đại diện":**
1. Mỗi dòng roster có `attendee_id` = **bản ghi attendee đại diện** (chọn theo §7.4) và
   `source_attendee_ids` (JSON) = tất cả bản ghi attendee cùng người.
2. `FinalAttendeeRosterService::provisionLucky`, với mỗi dòng `lucky_number IS NULL`:
   - **a) Tái sử dụng trước:** nếu bất kỳ attendee trong `source_attendee_ids` **đã có**
     `lucky_number` ⇒ **lấy mã đó** (§6.4 xử lý ca nhiều mã).
   - **b) Nếu chưa ai có** ⇒ sinh 6 số mới, kiểm tra trùng trên **cả** `attendees.lucky_number`
     **và** `final_attendee_rosters.lucky_number` **kể cả dòng đã xoá mềm (`withTrashed()`)**.
   - Ghi `final_attendee_rosters.lucky_number` + `lucky_provisioned_at = time()`.
   - **Ghi ngược** mã vào `attendees.lucky_number` của **đúng bản đại diện**.
3. Cổng chạy **không sửa một dòng code nào**: `resolve` vẫn tra `attendees.lucky_number` +
   `is_active = 1` ⇒ ra **đúng một** attendee. `identifyByQr`, `setPin`, `login`, BIB
   (`run_events.code + lucky_number`) giữ nguyên.
4. `login_pin` / `pin_set_at` / lockout **vẫn ở `attendees`**, **không** nhân bản sang bảng mới.
   Bảng mới chỉ **đọc** để hiện cờ `pin_is_set` (join theo `attendee_id`).

> `attendees.lucky_number` vẫn là **nguồn sự thật cho đăng nhập**;
> `final_attendee_rosters.lucky_number` là bản sao cho bốc thăm/tổng hợp, ghi trong **cùng transaction**.

### 6.4 Ca "một người đang giữ 2 mã" (do `run:gen-lucky` cũ cấp theo bản ghi)

Vì mã **không được đổi** nhưng một người **không thể giữ hai định danh**, phải chọn một.

**Mặc định đã thống nhất:**
- **Giữ mã cấp sớm nhất:** so sánh theo `lucky_provisioned_at` (nếu có) → `pin_set_at` →
  tie-break `attendees.id` **nhỏ nhất**.
- Mã còn lại: `attendees.lucky_number = NULL` trên bản ghi trùng người đó.
- Ghi bản ghi vào `final_attendee_roster_sync_logs.conflicts` với
  `type = duplicate_lucky`, kèm `full_name`, `staff_code`, `kept_lucky`, `dropped_lucky`,
  `attendee_ids` ⇒ **HO biết chính xác ai bị ảnh hưởng để thông báo thu hồi** định danh đã phát.
- Màn hình hiển thị badge đỏ "Xung đột mã" trên dòng đó cho tới khi HO bấm "Đã xử lý".

> 🔸 **Ghi chú:** đây là **mặc định đã thống nhất**, là **điểm có thể đổi** nếu sau này chủ dự án
> muốn ưu tiên khác (vd giữ mã đã phát ra ngoài / đã đặt PIN thay vì mã sớm nhất).

### 6.5 Mã của người bị huỷ tư cách (quyết định #11)
- Dòng roster **xoá mềm** (`deleted_at = time()`), **giữ nguyên `lucky_number`** trên dòng đó.
- UNIQUE `(lucky_number)` trên bảng mới ⇒ mã bị **khoá chết**, cơ chế sinh mã (`withTrashed()`)
  **không bao giờ** cấp lại cho người khác. Đúng yêu cầu "mã gắn với người".
- **`attendees.lucky_number` của người đó: KHUYẾN NGHỊ GIỮ (không NULL).** Lý do:
  1. `is_active = 0` (quy trình huỷ tư cách đã set) ⇒ `RunAuthService::resolve` lọc `is_active = 1`
     ⇒ người đó **không đăng nhập được** cổng chạy. An toàn mà không cần NULL.
  2. Giữ mã ⇒ nếu người đó được đưa lại vào VCK, khôi phục dòng roster là xong, **mã không đổi**
     (đúng quyết định #8).
  3. NULL mã sẽ **giải phóng** nó khỏi UNIQUE trên `attendees` ⇒ nguy cơ cấp lại cho người khác, vi
     phạm quyết định #11.
- Nếu một ngày cần tuyệt đối vô hiệu mã trên `attendees`, dùng cách **không giải phóng mã**: giữ
  `lucky_number` và dựa vào `is_active = 0` (đã đủ) — tránh NULL.

### 6.6 Quy tắc tổng hợp

| Vấn đề | Quyết định |
|--------|-----------|
| Format | **6 chữ số**, `100000`–`999999` (ràng buộc BIB đã verified) |
| Unique scope | **Toàn hệ thống**, kiểm tra trên `attendees` + `final_attendee_rosters` **kể cả trashed** |
| Đơn vị cấp mã | **Theo người đã gộp** (1 dòng = 1 người = 1 mã) |
| Idempotent | ✅ **Tuyệt đối** — chỉ dòng `lucky_number IS NULL`; không có "cấp lại" |
| Người thêm sau | Đồng bộ ⇒ dòng mới `NULL` ⇒ bấm "Cấp mã lucky" cấp bù (khuyến nghị gộp 2 bước vào 1 nút) |
| Người thêm thủ công | Cấp mã **ngay trong transaction tạo** (§9) |
| Người bị huỷ | Giữ mã trên dòng trashed, không tái sử dụng (§6.5) |
| Người thay thế | Người **khác** ⇒ dòng mới ⇒ mã mới; mã người bị thay giữ nguyên trên dòng trashed |
| Cạn mã | 900.000 mã cho vài nghìn người ⇒ an toàn |
| Cạnh tranh | Retry 3 lần bắt duplicate key (SQLSTATE 23000) + cache lock theo `event_id` |
| Endpoint cũ | `run:gen-lucky` / `run-auth/gen-lucky` **giữ nguyên, không sửa**; khuyến nghị **ẩn nút cũ** ở `admin/runRegistrations/admin` (§13) |

---

## 7. Thiết kế DB

### 7.1 Bảng `final_attendee_rosters`
Migration: `Modules/Registration/Database/Migrations/2026_10_03_110000_create_final_attendee_rosters_table.php`

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| `id` | BIGINT UNSIGNED AI | PK |
| `event_id` | INT UNSIGNED, NOT NULL | Sự kiện |
| `period_id` | INT UNSIGNED, NULL | Đợt VCK (`is_final = 1`) nguồn |
| `attendee_id` | INT UNSIGNED, NULL | **Bản ghi attendee đại diện** (ghi ngược lucky, write-back chức danh, đọc `pin_is_set`) |
| `source_attendee_ids` | JSON, NULL | Tất cả bản ghi attendee cùng người |
| `registration_id` | INT UNSIGNED, NULL | Phiếu VCK nguồn |
| `dedup_key` | VARCHAR(190), NOT NULL | Khoá gộp người (§7.4) |
| `dedup_source` | VARCHAR(20), NULL | `staff_code` / `id_card` / `name_birthday` |
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
| `position` | VARCHAR(255), NULL | Chức danh hiển thị (write-back về `attendees.position`) |
| `position_code` | VARCHAR(100), NULL | |
| `position_name` | VARCHAR(255), NULL | Chức danh gốc SMILE |
| **Khác** | | |
| `attendee_type` | VARCHAR(20), NULL | `finalist` / `director` / `driver` / `manual` |
| `shirt_size` | VARCHAR(10), NULL | |
| `note` | TEXT, NULL | |
| `sort_order` | INT, DEFAULT 0 | |
| **Lucky** | | |
| `lucky_number` | VARCHAR(20), NULL, **UNIQUE** | Mã lucky duy nhất theo người, **không bao giờ đổi** |
| `lucky_provisioned_at` | INT UNSIGNED, NULL | Unix timestamp lần cấp (dùng tie-break §6.4) |
| **Trạng thái & override** | | |
| `status` | TINYINT UNSIGNED, DEFAULT 1 | `1` = ACTIVE, `3` = MANUAL (HO tự thêm). **Không còn giá trị WITHDRAWN** — huỷ tư cách dùng **xoá mềm** (quyết định #11) |
| `overridden_fields` | JSON, NOT NULL DEFAULT `'[]'` | Danh sách trường đã sửa tay (§3.2) |
| `has_override` | TINYINT(1), DEFAULT 0 | Cờ lọc nhanh |
| `source_snapshot` | JSON, NULL | Ảnh dữ liệu gốc lần migrate cuối (tooltip "Gốc: …" + khôi phục) |
| `conflict_flag` | VARCHAR(30), NULL | `duplicate_lucky` / `possible_wrong_merge` / `duplicate_person` — badge đỏ trên UI, HO bấm "Đã xử lý" để xoá cờ |
| `last_synced_at` | INT UNSIGNED, NULL | Unix timestamp đồng bộ cuối |
| **Audit** | | |
| `created_by` | VARCHAR(190), NULL | Email, từ `AuthHandler::getUser()['email']` |
| `updated_by` | VARCHAR(190), NULL | |
| `deleted_by` | VARCHAR(190), NULL | Người huỷ tư cách |
| `created_at` / `updated_at` | INT UNSIGNED, NULL | **Unix timestamp** |
| `deleted_at` | INT UNSIGNED, NULL | **Soft delete** |

### 7.2 ⭐ Index / Unique — đã rà lại theo quyết định #11

```
UNIQUE uq_final_attendee_rosters_lucky         (lucky_number)
UNIQUE uq_final_attendee_rosters_event_dedup   (event_id, dedup_key)
INDEX  idx_far_event_property                  (event_id, property_id, status)
INDEX  idx_far_event_division                  (event_id, division_code)
INDEX  idx_far_event_department                (event_id, department_code)
INDEX  idx_far_attendee                        (attendee_id)
INDEX  idx_far_full_name                       (full_name)
INDEX  idx_far_event_override                  (event_id, has_override)
INDEX  idx_far_conflict                        (event_id, conflict_flag)
```

**Thay đổi so với bản trước và lý do:**

1. **`UNIQUE (lucky_number)` — GIỮ nguyên, phạm vi toàn bảng kể cả dòng đã xoá mềm.**
   Vì quyết định #11 nói **mã bỏ luôn, không tái sử dụng**, nên dòng trashed **vẫn giữ mã** và
   UNIQUE này **chính là cơ chế khoá mã**. ⇒ **Tuyệt đối không SET NULL** mã khi xoá mềm.
   Hệ quả bắt buộc trong code: mọi truy vấn kiểm tra trùng mã **phải dùng `withTrashed()`**
   (Eloquent mặc định loại dòng trashed ⇒ nếu quên, insert sẽ ăn lỗi duplicate key bất ngờ).
2. **`UNIQUE (event_id, dedup_key)` — BỎ `deleted_at` khỏi khoá** (bản trước là
   `(event_id, dedup_key, deleted_at)`).
   Lý do: trong MySQL, `NULL` trong unique index **không bị coi là trùng nhau** ⇒ khoá
   `(event_id, dedup_key, deleted_at)` **không chặn** được hai dòng active cùng người (cả hai
   `deleted_at = NULL`) — tức là khoá đó **sai mục đích**.
   Khoá mới `(event_id, dedup_key)` chặn triệt để, và ca "người bị huỷ rồi được đưa lại" được xử lý
   bằng **khôi phục dòng cũ** chứ không insert dòng mới:
   > Sync tìm dòng bằng `withTrashed()->where(event_id, dedup_key)`. Thấy dòng trashed mà người đó
   > lại có trong nguồn VCK ⇒ **restore** (`deleted_at = NULL`, `deleted_by = NULL`), **giữ nguyên
   > `lucky_number`**, cập nhật các trường không bị override. Đây đúng là hành vi mong muốn của
   > quyết định #8 ("mã đi theo người").
3. Thêm `INDEX (event_id, conflict_flag)` cho màn lọc "dòng có xung đột".

### 7.3 Bảng `final_attendee_roster_sync_logs`
Migration: `...2026_10_03_110100_create_final_attendee_roster_sync_logs_table.php`

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| `id` | BIGINT UNSIGNED AI | |
| `event_id` | INT UNSIGNED | |
| `period_id` | INT UNSIGNED, NULL | |
| `property_id` | INT UNSIGNED, NULL | Nếu chạy 1 đơn vị |
| `mode` | VARCHAR(10) | `preview` / `apply` |
| `inserted` / `restored` / `updated` / `skipped_override` / `soft_deleted` / `unchanged` / `conflicts` | INT UNSIGNED DEFAULT 0 | Số liệu báo cáo |
| `detail` | JSON, NULL | Chi tiết: dòng thêm/khôi phục/xoá mềm, trường bị bỏ qua, cảnh báo gộp, xung đột mã |
| `run_by` | VARCHAR(190), NULL | Email người chạy |
| `created_at` | INT UNSIGNED | |

### 7.4 Quy tắc gộp người (dedup)

**Khoá gộp** — theo thứ tự, dừng ở cái đầu tiên có giá trị:

| Ưu tiên | Điều kiện | `dedup_key` | `dedup_source` |
|---------|-----------|-------------|----------------|
| 1 | `staff_code` khác rỗng | `SC:` + upper(trim(staff_code)) | `staff_code` |
| 2 | `id_card` khác rỗng | `IC:` + chỉ giữ chữ số | `id_card` |
| 3 | còn lại | `NB:` + slug(họ tên bỏ dấu, lowercase) + `\|` + `birthday` (`Ymd`, rỗng ⇒ `00000000`) | `name_birthday` |

- Dùng **một hàm duy nhất** `FinalAttendeeRosterService::buildDedupKey()` cho cả preview và apply.
- Phạm vi gộp: **trong cùng `event_id`**.

**Chọn bản ghi đại diện** khi nhiều attendee cùng khoá:
1. `attendee_type = 'finalist'` (bản VCK) — ưu tiên cao nhất.
2. `is_active = 1` và `approval_status = approved`.
3. Nhiều trường khác rỗng nhất (đầy đủ dữ liệu nhất).
4. `id` **nhỏ nhất** (ổn định giữa các lần chạy).

**Gộp dữ liệu:** trường rỗng ở bản đại diện mà bản khác có giá trị ⇒ fill-in. Hai bản khác giá trị
⇒ giữ bản đại diện + ghi `detail.conflicts` (không tự quyết).

**Khi migrate lại phát hiện gộp sai:**
- **A — hai người bị gộp thành một** (trùng tên, thiếu ngày sinh): preview báo
  `possible_wrong_merge`. HO dùng **"Tách người"**: điền `staff_code`/`id_card` cho một bên (sửa tay
  ⇒ vào `overridden_fields`) ⇒ đồng bộ sau sinh `dedup_key` mới ⇒ dòng mới. Dòng cũ **giữ mã**,
  dòng mới được cấp **mã mới**.
- **B — một người bị tách thành hai dòng** (lần đầu thiếu `staff_code`): preview báo
  `duplicate_person`. HO dùng **"Gộp dòng"**: chọn dòng giữ lại (**ưu tiên dòng đã có mã; nếu cả hai
  có mã thì giữ mã cấp sớm hơn** theo §6.4), dòng kia **xoá mềm** (giữ mã, khoá chết),
  `source_attendee_ids` hợp nhất.
- Cả hai chỉ thao tác trên bảng mới, **không** sửa `attendees` (trừ write-back chức danh và ghi
  ngược lucky).

### 7.5 Thay đổi trên `attendees`
- **Bắt buộc:** bổ sung `lucky_number` + accessor `pin_is_set` vào
  `Modules/Registration/Http/Resources/AttendeeResource.php` (hiện chưa trả).
- **Không** cần migration trên `attendees` cho phạm vi này.
- *(Tuỳ chọn, slice S13)* thêm `division_code`/`division_name` vào `attendees`.

---

## 8. API endpoints

Chuẩn `.claude/rules/api-conventions.md`. Prefix `api`, middleware `auth.token`.
Module `Registration`, service `FinalAttendeeRosterService`.

### 8.1 `GET /api/final-attendee-rosters` — danh sách
| Param | Kiểu | Mô tả |
|-------|------|-------|
| `event_id` | int, **bắt buộc** | |
| `period_id` | int | Đợt VCK |
| `property_id` | int | Lọc đơn vị |
| `division_code` | string | Bộ phận (`__none__` = chưa xác định) |
| `department_code` | string | Phòng ban (`__none__` = chưa xác định) |
| `attendee_type` | string | `finalist` / `director` / `driver` / `manual` |
| `has_lucky` | 0/1 | |
| `pin_is_set` | 0/1 | Join `attendees` theo `attendee_id` |
| `has_override` | 0/1 | |
| `conflict_flag` | string | Lọc dòng có xung đột |
| `status` | int | `1` ACTIVE (mặc định) / `3` MANUAL |
| `with_trashed` | 0/1 | Mặc định `0`. `1` ⇒ hiện cả người **đã huỷ tư cách** (dòng xoá mềm) |
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
      "lucky_provisioned_at": 1759400000,
      "pin_is_set": true,
      "overridden_fields": ["position", "division_name"],
      "has_override": 1,
      "source_snapshot": { "position": "Trưởng ca", "division_name": null },
      "conflict_flag": null,
      "status": 1, "last_synced_at": 1759460000,
      "deleted_at": null, "updated_by": "hr@muongthanh.vn"
    }
  ],
  "pagination": { "page": 1, "limit": 25, "total": 612, "totalPages": 25 }
}
```
> `login_pin` **không bao giờ** xuất hiện trong response.

### 8.2 `GET /api/final-attendee-rosters/filters`
`event_id` bắt buộc. Trả đơn vị / bộ phận / phòng ban **thực sự có người**, phục vụ dropdown phụ thuộc.

### 8.3 `GET /api/final-attendee-rosters/stats`
`{ total, with_lucky, without_lucky, pin_set, with_override, conflicts, withdrawn }`
(`withdrawn` = số dòng đã xoá mềm).

### 8.4 `POST /api/final-attendee-rosters/sync`
Body: `{ "event_id": 3, "period_id": 4, "property_id": null, "mode": "preview|apply", "run_by": "hr@..." }`

- `preview` ⇒ **dry-run**, không ghi bảng chính, chỉ ghi log (`mode=preview`).
- `apply` ⇒ ghi thật, trong transaction + cache lock.

```json
{ "success": true, "data": {
  "mode": "preview",
  "summary": { "inserted": 57, "restored": 3, "updated": 480, "skipped_override": 23,
               "soft_deleted": 5, "unchanged": 52, "conflicts": 2 },
  "inserted_rows": [{ "full_name": "...", "property_name": "...", "dedup_key": "SC:..." }],
  "restored_rows": [{ "full_name": "...", "lucky_number": "123456" }],
  "soft_deleted_rows": [{ "full_name": "...", "lucky_number": "654321", "reason": "Không còn trong danh sách VCK" }],
  "skipped_fields": [{ "roster_id": 1021, "full_name": "...", "fields": ["position", "division_name"] }],
  "conflicts": [
    { "type": "duplicate_lucky", "full_name": "...", "staff_code": "HN0123",
      "kept_lucky": "123456", "dropped_lucky": "778899", "attendee_ids": [1003, 4821] },
    { "type": "possible_wrong_merge", "dedup_key": "NB:nguyen-van-a|00000000", "attendee_ids": [1003, 2117] }
  ],
  "write_back_applied": 480,
  "log_id": 88
}, "message": "Xem trước: 57 thêm mới, 3 khôi phục, 480 cập nhật, 23 trường bỏ qua do đã sửa tay, 5 huỷ tư cách, 2 xung đột." }
```
- 422 thiếu `event_id`/`period_id`, hoặc đợt không phải `is_final`.
- 409 đang có tiến trình đồng bộ khác (lock `far:sync:{event_id}`, TTL 300s).

### 8.5 `POST /api/final-attendee-rosters/provision-lucky`
Body: `{ "event_id": 3, "property_id": null }`
- Chỉ xử lý dòng `lucky_number IS NULL`; **không có** tham số nào cho phép cấp lại.
- Kiểm tra trùng trên `attendees` + bảng mới **kể cả trashed**.
- Ghi ngược vào `attendees` của bản đại diện; xử lý ca 2 mã theo §6.4.
- 200: `{ "success": true, "data": { "provisioned": 57, "reused": 12, "conflicts": [...], "total_with_lucky": 612 }, "message": "Đã cấp mã lucky cho 57 người (tái dùng 12 mã có sẵn)." }`
- 422 thiếu `event_id`; 409 đang chạy.

> **Không có** endpoint `regenerate-lucky` / `reset-lucky` — theo quyết định #8.

### 8.6 `POST /api/final-attendee-rosters/update/{id}` — sửa thủ công
Body: `{ "fields": { "position": "Trưởng bộ phận Nhà hàng" }, "updated_by": "hr@..." }`
- Chỉ nhận trường trong `EDITABLE_FIELDS`; trường ngoài danh sách (đặc biệt `lucky_number`) ⇒ **bỏ
  qua**, ghi log, **không** báo lỗi.
- Mỗi trường sửa ⇒ push vào `overridden_fields`, `has_override = 1`, `updated_by`, `updated_at`.
- **Write-back** sang `attendees` cho các trường thuộc danh sách §5.4, **trong cùng transaction**.
- Ghi `audit_logs` (`final_roster.update`, `final_roster.write_back`).
- 200 trả dòng đã cập nhật; 404; 422 validate; 409 nếu sửa khiến `dedup_key` trùng dòng khác
  (gợi ý dùng "Gộp dòng").

### 8.7 `POST /api/final-attendee-rosters/reset-field/{id}`
Body: `{ "fields": ["position"], "updated_by": "hr@..." }`
Gỡ khỏi `overridden_fields`, ghi lại từ `source_snapshot`, cập nhật `has_override`, **và write-back
lại giá trị gốc** sang `attendees` nếu trường đó thuộc danh sách write-back.

### 8.8 `POST /api/final-attendee-rosters/store` — ⭐ HO thêm người thủ công (quyết định #9)
Xem thiết kế chi tiết ở **§9**. Body:
```json
{ "event_id": 3, "period_id": 4, "property_id": 66,
  "full_name": "Trần Thị B", "position": "Khách mời",
  "attendee_type": "manual", "role_id": 7,
  "staff_code": null, "id_card": "001234567890",
  "division_code": null, "department_code": null,
  "unit_label": "MT Grand Hà Nội", "shirt_size": "M",
  "created_by": "hr@..." }
```
Response 201:
```json
{ "success": true, "data": {
  "roster": { "id": 1500, "status": 3, "lucky_number": "445566", "attendee_id": 4999 },
  "attendee": { "id": 4999, "qr_token": "9f2c…", "badge_number": "MT001", "is_active": 1 }
}, "message": "Đã thêm người và cấp mã lucky 445566 (định danh DHMT445566)." }
```
- 422 thiếu trường bắt buộc (§9.1) hoặc đơn vị chưa có phiếu VCK và không tạo được.
- 409 `dedup_key` đã tồn tại (gợi ý mở dòng đã có thay vì tạo mới).

### 8.9 `POST /api/final-attendee-rosters/merge` / `/split`
`merge`: `{ "keep_id": 1021, "merge_id": 1455 }` — dòng giữ lại theo §6.2; dòng bị gộp xoá mềm, giữ mã.
`split`: `{ "id": 1021, "attendee_ids_to_split": [2117], "new_dedup_hint": { "id_card": "..." } }`.

### 8.10 `DELETE /api/final-attendee-rosters/destroy/{id}` — huỷ tư cách
- **Xoá mềm** (`deleted_at = time()`, `deleted_by = email`), **GIỮ `lucky_number`** (quyết định #11).
- Khuyến nghị song song: set `attendees.is_active = 0` cho bản đại diện (chặn đăng nhập), **giữ**
  `attendees.lucky_number` (§6.5). Tham số `also_deactivate_attendee` (mặc định `true`).
- 200 `{ "success": true, "message": "Đã huỷ tư cách. Mã lucky 123456 được giữ lại và không tái sử dụng." }`

### 8.11 `POST /api/final-attendee-rosters/clear-conflict/{id}`
Xoá `conflict_flag` sau khi HO đã xử lý (vd đã thông báo thu hồi định danh).

### 8.12 ⭐ `GET /api/final-attendee-rosters/audit` — đối soát (chỉ đọc, quyết định #16 & #18)

#### Một endpoint hay hai?
✅ **MỘT endpoint audit tổng hợp** (`scope = lucky | badge | all`), không tách hai.
**Lý do:** cùng một bản chất (*rà soát tính toàn vẹn dải định danh trước khi phát hành*), cùng
permission, cùng người dùng, cùng nơi hiển thị (header màn tổng hợp cần **cả hai** badge "Đã dùng
MT: 12/999" và "Xung đột mã lucky: 2") ⇒ một lần gọi lấy đủ, tránh 2 request cho một màn. Tách hai
endpoint chỉ làm trùng lặp khung response và tăng số chỗ phải phân quyền.

#### Đặc tả
| Mục | Giá trị |
|-----|---------|
| Method / Path | `GET /api/final-attendee-rosters/audit` |
| Tính chất | **CHỈ ĐỌC** — không `INSERT`/`UPDATE`/`DELETE` bất kỳ bảng nào, không sinh mã |
| Permission | **Cùng quyền với màn tổng hợp**: `finalattendeerosters.read`. FE gọi qua action `actionAudit` có `PermissionHelper::can('finalattendeerosters', 'read')` |
| Params | `event_id` (bắt buộc), `scope` = `lucky` / `badge` / `all` (mặc định `all`), `limit` (số mẫu `MT*` trả về, mặc định 50, tối đa 500) |

Response 200:
```json
{ "success": true, "data": {
  "badge": {
    "prefix": "MT",
    "pad": 3,
    "capacity": 999,
    "used": 12,
    "remaining": 987,
    "max_number": 12,
    "max_badge_number": "MT012",
    "next_badge_number": "MT013",
    "samples": ["MT001", "MT002", "MT012"],
    "invalid_format": [
      { "badge_number": "MT1", "attendee_id": 3301, "full_name": "...", "reason": "Thiếu zero-pad (không khớp ^MT\\d{3}$)" },
      { "badge_number": "MT12A", "attendee_id": 3410, "full_name": "...", "reason": "Có ký tự không phải chữ số sau tiền tố" }
    ],
    "warning": "Phát hiện 2 số thẻ bắt đầu bằng MT nhưng sai format — có thể làm lệch bộ sinh max. Cần xử lý trước khi bật tính năng."
  },
  "lucky": {
    "roster_total": 612,
    "with_lucky": 600,
    "without_lucky": 12,
    "duplicate_person_multi_lucky": [
      { "full_name": "...", "staff_code": "HN0123", "attendee_ids": [1003, 4821],
        "lucky_numbers": ["123456", "778899"], "suggest_keep": "123456" }
    ],
    "orphan_lucky_on_attendees": [
      { "attendee_id": 2200, "lucky_number": "334455",
        "reason": "attendees có mã nhưng không thuộc dòng roster nào (do run:gen-lucky cũ)" }
    ],
    "lucky_only_on_roster": [
      { "roster_id": 1455, "lucky_number": "556677",
        "reason": "roster có mã nhưng attendees đại diện chưa được ghi ngược" }
    ],
    "trashed_locked": 5,
    "warning": "Có 1 người đang giữ 2 mã lucky. Xem §6.4 trước khi phát định danh."
  },
  "checked_at": 1759500000
}}
```
- 422 thiếu `event_id`.
- Trường `invalid_format` là lý do chính của quyết định #16: một giá trị `MT*` nhập tay sai format
  (vd `MT1`, `MT12A`) sẽ làm `MAX(CAST(SUBSTRING(...)))` trả số sai ⇒ bộ sinh cấp trùng. Endpoint
  phát hiện trước, **không tự sửa**.

#### Hai chỗ sử dụng
1. **Chạy một lần trước khi bật tính năng** (checklist slice S10): gọi `scope=badge`, xác nhận
   `invalid_format` rỗng và dải sạch. Có thể gọi từ trình duyệt/Postman, hoặc qua command
   `final-attendee-roster:audit` (§8.14) cho người thạo CLI.
2. **Hiển thị thường trực trên UI** (§10 header): badge **"Đã dùng MT: 12/999"**
   (đỏ nếu `remaining < 50`) và badge **"Xung đột mã lucky: N"** (đỏ nếu `N > 0`, click mở danh
   sách). Nhờ vậy HO biết **trước khi tràn**, không chờ tới lúc gặp 422.

> Endpoint này **thay thế hoàn toàn** việc query DB tay. Tài liệu không còn bất kỳ bước nào yêu cầu
> chạy SQL trực tiếp trên môi trường thật.

### 8.13 Xuất Excel
**FE tự sinh** bằng PHPExcel (pattern có ở `RunRegistrationsController`, `ReportsController`)
⇒ **không thêm endpoint BE**.

### 8.14 Command artisan
```
php artisan final-attendee-roster:sync {event_id} {period_id} [--property=] [--dry-run]
php artisan final-attendee-roster:gen-lucky {event_id} [--property=]
php artisan final-attendee-roster:audit {event_id} [--scope=lucky|badge|all]   # đối soát mã lucky + dải số thẻ MT (chỉ đọc, in báo cáo)
```
> Chạy bằng MAMP php8.1 kèm cờ extension (xem `chung-ket-fun-run.md` §9).

### 8.15 Endpoint cũ — không sửa
`POST /api/run-auth/gen-lucky` và `run:gen-lucky` **giữ nguyên** (cổng chạy đã verified).
Khuyến nghị ẩn nút cũ ở `admin/runRegistrations/admin` (§13).

---

## 9. ⭐ Thêm người thủ công + tạo bản ghi `attendees` tối thiểu (quyết định #9)

Người HO thêm tay **phải đăng nhập được cổng chạy**. Vì cổng chạy tra
`attendees.lucky_number` + `is_active = 1`, nên **buộc phải có một dòng `attendees` thật**.

### 9.1 Trường bắt buộc để bản ghi `attendees` hợp lệ
Đã đối chiếu `AttendeeRequest`, `AttendeeService::convertData` và ghi chú đã verified
("`/api/attendees/store` **BẮT BUỘC `role_id`** — lỗi 422 *Vai trò không được để trống*"):

| Trường | Giá trị | Bắt buộc | Nguồn |
|--------|---------|:--------:|-------|
| `event_id` | từ request | ✅ | HO chọn |
| `registration_id` | **phiếu VCK của đơn vị đó** | ✅ | Tra phiếu theo (`period_id`, `property_id`); **nếu chưa có ⇒ tái dùng `FinalAggregationService::ensureUnitRegistrations`** để tạo phiếu rỗng |
| `property_id` | từ request | ✅ | HO chọn |
| `role_id` | từ request; mặc định đọc từ **param cấu hình** `finalRosterManualRoleId` | ✅ | **Bắt buộc bởi BE** — xem §9.4 (⚠️ chưa chốt được giá trị) |
| `full_name` | từ request | ✅ | |
| `attendee_type` | `'manual'` | ✅ | Phân biệt với finalist/director/driver |
| `is_active` | `1` | ✅ | Để cổng chạy cho đăng nhập |
| `approval_status` | `APPROVED (1)` | ✅ | Người do HO thêm coi như đã duyệt |
| `qr_token` | **sinh unique** `bin2hex(random_bytes(16))`, retry nếu trùng | ✅ | Để quét QR thẻ (`identifyByQr`) |
| `lucky_number` | **cấp ngay trong transaction** (6 số, unique, check `withTrashed()`) | ✅ | Để đăng nhập `DHMT`+lucky |
| `badge_number` | sinh theo quy ước **`MT` + 3 số** (`MT001`…), unique | ✅ | Phục vụ in thẻ — thiết kế ở §9.5 |
| `position`, `unit_label`, `shirt_size`, `id_card`, `staff_code`, `phone_number`, `gender`, `birthday` | từ request nếu có | ⬜ | |
| `staff_id` | `NULL` | ⬜ | Người ngoài SMILE |
| `created_by` | email HO | ⬜ | `AuthHandler::getUser()['email']` |

### 9.2 Luồng tạo (một transaction duy nhất, thực hiện **ở BE**)
```
BEGIN TRANSACTION
  1. Validate request; buildDedupKey() → kiểm tra trùng (withTrashed) ⇒ 409 nếu đã có
  2. Resolve registration_id:
       tìm phiếu (period_id, property_id)
       nếu không có ⇒ ensureUnitRegistrations(event_id, period_id) rồi tìm lại
       vẫn không có ⇒ ROLLBACK, 422
  3. Sinh qr_token unique (retry 3 lần)
  4. Sinh lucky_number unique 6 số — check attendees + final_attendee_rosters (withTrashed), retry 3 lần
  4b. Sinh badge_number "MT" + 3 số theo §9.5 (trong cùng lock, retry nếu duplicate)
  5. INSERT attendees {...§9.1, lucky_number, qr_token, badge_number}
  6. INSERT final_attendee_rosters {
        status = MANUAL (3),
        attendee_id = <id vừa tạo>,
        source_attendee_ids = [<id vừa tạo>],
        lucky_number = <mã vừa sinh>, lucky_provisioned_at = time(),
        overridden_fields = TẤT CẢ trường có giá trị  ← để đồng bộ KHÔNG BAO GIỜ ghi đè
        source_snapshot = {} , created_by = email
     }
  7. Ghi audit_logs (final_roster.create_manual)
COMMIT
```

### 9.3 Ảnh hưởng & lưu ý
- **Cổng chạy:** người này **đăng nhập bình thường** — `resolve('DHMT' + lucky)` tìm thấy dòng
  `attendees` có `is_active = 1`; tự đặt PIN ở lần đầu (`setPin`), quét QR cũng được vì đã có
  `qr_token`. **Không cần sửa code cổng chạy.**
- **Đồng bộ:** dòng `status = MANUAL` **không có trong nguồn VCK** ⇒ trình đồng bộ **phải loại trừ**
  `status = MANUAL` khỏi bước "xoá mềm người không còn trong nguồn", nếu không người thủ công sẽ bị
  xoá ngay lần đồng bộ kế tiếp. ⚠️ Đây là bug dễ mắc — ghi rõ trong tiêu chí verify của slice.
- **`overridden_fields` = tất cả trường** ⇒ đồng bộ không chạm dòng này; HO vẫn sửa tay bình thường.
- **Thẻ & email:** người này nằm trên phiếu VCK của đơn vị ⇒ luồng badge/email hiện có tự nhận
  (vì đọc `attendees` theo `registration_id`).
- **Xoá:** xoá mềm dòng roster + `attendees.is_active = 0`; mã lucky giữ lại, không tái sử dụng.

### 9.4 `role_id` = vai trò `btc` (Ban tổ chức) — quyết định #13

**Đã chốt: dùng vai trò `btc` — "Ban tổ chức".**

Bối cảnh: ban đầu chủ dự án nêu "admin", nhưng đã kiểm chứng code thật và **không tìm thấy** record
nào như vậy, nên đã chọn lại trong danh mục hiện có:

- Migration `Modules/Registration/Database/Migrations/2026_05_05_100101_create_roles_table.php`:
  bảng `roles` có `name`, `code`, `color`, `icon`, `sort_order`, `description`, `event_id` —
  **không có seeder** nào trong repo (`Modules/*/Database/Seeders/`, `database/seeders/` đều không
  chèn `roles`).
- Dữ liệu thực tế lấy từ dump `docs/mt_registration_portal_struct.sql` (`INSERT INTO roles`) có
  **đúng 10 record**, và **không có `admin`**:

| id | `name` | `code` | Ghi chú |
|----|--------|--------|---------|
| 1 | Hỗ trợ đại hội | `support` | Nhân sự hỗ trợ |
| 2 | Thi thể thao | `sports` | Vai trò thi đấu |
| 3 | Thi nghiệp vụ | `competition` | Vai trò thi đấu |
| 4 | Giám đốc | `director` | |
| 5 | Phó Giám đốc | `deputy_director` | |
| 6 | Khách mời | `guest` | |
| 7 | Trưởng đoàn | `team_lead` | |
| 8 | Ban tổ chức | `btc` | Gần nghĩa "BTC/HO" nhất |
| 9 | Thi Miss | `miss` | Vai trò thi đấu |
| 10 | Thi văn nghệ | `talent` | Vai trò thi đấu |

**Nguyên nhân lệch kỳ vọng:** `roles` ở hệ này là **danh mục vai trò NGƯỜI THAM DỰ** (in trên thẻ,
phân loại người dự đại hội), **không phải** vai trò tài khoản đăng nhập. Vai trò tài khoản
(Admin HO / HR / BTC các ban) nằm ở `users.role` + permission JWT — **khác bảng**.

> ✅ **Vai trò được chọn: `btc` — "Ban tổ chức"** (id **8** ở môi trường hiện tại) — gần nghĩa
> "BTC / HO" nhất trong 10 vai trò đang có. **Không tạo role mới.**

#### ⚠️ KHÔNG hardcode `id = 8` — cách resolve an toàn

`roles.id` là auto-increment, **có thể khác nhau giữa các môi trường** (dev / staging / production),
và bảng này còn có cột `event_id` (migration `2026_05_28_145239_add_event_id_to_roles_table.php`)
nên cùng một `code` có thể tồn tại nhiều bản ghi theo sự kiện. Hardcode `8` là bẫy lỗi.

**Thứ tự resolve (thực hiện ở BE, dừng ở bước đầu tiên thành công):**

```
0. Request có gửi role_id  → ưu tiên cao nhất (HO chọn tay ở modal), bỏ qua 1–3
1. Theo code + đúng sự kiện: roles WHERE code = MANUAL_ROLE_CODE ('btc') AND event_id = {event_id}
2. Theo code, bản dùng chung: roles WHERE code = MANUAL_ROLE_CODE AND event_id IS NULL
3. Fallback param cấu hình: config('registration.final_roster_manual_role_id')
      → verify id này có tồn tại trong `roles` (chưa xoá mềm) trước khi dùng
```

**Hằng & param cần khai báo:**
- `MANUAL_ROLE_CODE = 'btc'` — hằng trên service (bản chất nghiệp vụ, ít đổi), override được bằng
  `.env FINAL_ROSTER_MANUAL_ROLE_CODE`.
- `FINAL_ROSTER_MANUAL_ROLE_ID` — **fallback id trực tiếp**, dùng khi môi trường nào không có `code`
  chuẩn. BE đọc qua `config('registration.final_roster_manual_role_id')`.
- FE Yii khai thêm `Yii::app()->params['finalRosterManualRoleId']` (trong
  `protected/config/params.php`) để **chọn sẵn** giá trị trong dropdown; nếu rỗng thì FE tự tìm
  option có `code = 'btc'` trong danh sách `roles` do controller truyền vào.

**Hành vi UI & lỗi:**
- Modal `_modal_add_person` giữ **dropdown vai trò** (nạp từ `roles`, controller truyền qua
  `render()`), **chọn sẵn `btc`** ⇒ HO đổi được từng ca.
- Không resolve được (cả 3 bước thất bại) ⇒ BE trả **422** với thông điệp tiếng Việt
  *"Không xác định được vai trò mặc định (mã `btc`). Vui lòng chọn vai trò hoặc cấu hình
  `FINAL_ROSTER_MANUAL_ROLE_ID`."* — **không** tạo nửa vời (đã trong transaction).
- Ghi log cảnh báo khi phải dùng tới bước 3 (fallback) để phát hiện môi trường thiếu danh mục.

### 9.5 ⭐ Sinh `badge_number` theo quy ước `MT` + 3 số (quyết định #14)

#### Hiện trạng `badge_number` (đã kiểm chứng)
- `attendees.badge_number`: `string(20)`, **`->unique()`**, `nullable` — migration
  `2026_05_05_100105_create_attendees_table.php`.
- `AttendeeRequest` có `Rule::unique('attendees', 'badge_number')`; `AttendeeService::convertData`
  chỉ gán `$input['badge_number'] ?? null` ⇒ **hệ thống chưa có bất kỳ bộ sinh số thẻ tự động nào**,
  giá trị hiện do người dùng nhập tay.
- ⇒ Tiền tố **`MT` chưa bị quy ước nào chiếm**. Các mã dạng `HNO028`, `EVE000001` thấy trong dump là
  `staff_code` / `code` của bảng khác, **không phải** `badge_number`.
- ⚠️ **Việc cần làm trước khi bật tính năng:** đối soát dải `MT%` — làm **qua API, KHÔNG query DB
  tay** (quyết định #16): gọi `GET /api/final-attendee-rosters/audit?scope=badge` (§8.12) để xác
  nhận dải sạch. Nếu phát hiện giá trị `MT*` cũ **sai format** ⇒ báo lại để xử lý trước, vì nó làm
  lệch bộ sinh max.

#### Thiết kế sinh số

| Hạng mục | Quyết định | Lý do |
|----------|-----------|-------|
| Format | `MT` + **3 chữ số, zero-pad** (`MT001` … `MT999`) | Theo chốt của chủ dự án |
| Cách lấy số | **max hiện có (toàn hệ thống) + 1** | Không cần thêm bảng sequence; tự phục hồi nếu có bản ghi bị xoá; đọc 1 query |
| **Scope đánh số** | ✅ **Duy nhất TOÀN HỆ THỐNG** — `MT001` → `MT999`, **không** đánh lại theo sự kiện | `attendees.badge_number` UNIQUE **toàn bảng** ⇒ đánh số theo event sẽ sinh lại `MT001` ở sự kiện thứ hai và **ăn lỗi duplicate**. Số thẻ chỉ cần **duy nhất và in được**; việc báo cáo/lọc theo sự kiện dùng `event_id`, **không** dựa vào tiền tố số thẻ |
| Chống race (2 HO thêm cùng lúc) | **Cache lock** `far:badge:global` (TTL 10s) bao quanh bước "đọc max → insert", **cộng** retry 3 lần bắt duplicate key (SQLSTATE 23000) rồi tính lại max | Lock giảm va chạm; retry là lưới an toàn khi lock hết hạn/đa worker |
| Tràn 999 | **Báo lỗi rõ ràng** — BE trả **422** `"Đã dùng hết dải số thẻ MT001–MT999. Vui lòng mở rộng quy ước số thẻ (MT + 4 số)."`; FE hiện Toast đỏ. **Tuyệt đối không** âm thầm trùng số, không quay vòng về `MT001` | Số thẻ trùng ⇒ hai người cùng số thẻ in ra, không thể sửa sau khi in |
| Đường nâng cấp | Chuyển sang **`MT` + 4 số** (`MT0001`…`MT9999`): chỉ sửa hằng `BADGE_NUMBER_PAD = 3 → 4` (cột `string(20)` thừa chỗ). Số cũ 3 chữ số **vẫn hợp lệ**, bộ sinh đọc max bằng regex chấp nhận cả hai độ dài | Nâng cấp không cần migration dữ liệu |

#### Ghi chú: phương án "đánh lại từ `MT001` mỗi sự kiện" đã bị LOẠI

Phương án đó buộc phải thêm hậu tố sự kiện vào chuỗi lưu (vd `MT001-4`) để không vi phạm UNIQUE
toàn bảng ⇒ chuỗi số thẻ không nhất quán, in thẻ nhìn lạ, logic max phức tạp hơn. **Đã loại.**
Người HO thêm tay là ngoại lệ số lượng nhỏ (vài chục người/kỳ) nên dải 999 thừa sức cho nhiều kỳ
đại hội.

#### Giả mã
```
LOCK far:badge:global   (TTL 10s)   // lock toàn hệ thống, vì dải số là toàn hệ thống
  $max = SELECT MAX(CAST(SUBSTRING(badge_number, 3) AS UNSIGNED))
           FROM attendees
          WHERE badge_number REGEXP '^MT[0-9]+$';
  $next = (int) $max + 1;
  if ($next > 999) {
      throw 422 "Đã dùng hết dải số thẻ MT001–MT999 ... Vui lòng mở rộng quy ước số thẻ.";
  }
  $badge = 'MT' . str_pad($next, BADGE_NUMBER_PAD, '0', STR_PAD_LEFT);
  // INSERT attendees ... (retry 3 lần: nếu duplicate key thì tính lại $max)
UNLOCK
```
> Chỉ áp dụng cho người **HO thêm tay** (`attendee_type = 'manual'`). Người đến từ luồng đăng ký
> **không** bị bộ sinh này chạm tới — giữ nguyên hành vi hiện tại (`badge_number` do người dùng nhập
> hoặc để rỗng).

### 9.6 Ẩn nút "Cấp số lucky" cũ (quyết định #15)

**Task thực thi (thuộc slice S8):**

| Việc | File |
|------|------|
| Ẩn nút + form POST `genLucky` | `protected/modules/admin/views/runRegistrations/admin.php` (khối `<form method="post" action="<?php echo $this->createUrl('genLucky'); ?>">` quanh dòng 32) |
| Chặn cả đường vào trực tiếp URL | `protected/modules/admin/controllers/RunRegistrationsController.php` — `actionGenLucky` (dòng ~23) trả `CHttpException(410)` kèm thông điệp tiếng Việt chỉ sang màn mới, **giữ lại code** (không xoá) để rollback nhanh nếu cần |
| Thêm dòng hướng dẫn thay thế | Cùng view: ghi chú *"Việc cấp mã lucky đã chuyển sang màn **Tổng hợp danh sách Vòng Chung Kết**"* + link `/admin/finalAttendeeRosters/admin` |

**Tác dụng:** sau khi ẩn, **đường cấp mã duy nhất** là `POST /api/final-attendee-rosters/provision-lucky`
— luồng này **cấp mã theo người đã gộp** ⇒ **triệt tiêu nguồn gốc ca "một người 2 mã"** về sau. Ca
còn lại chỉ là dữ liệu **lịch sử** do nút cũ đã cấp, xử lý một lần bằng
`final-attendee-roster:audit` + quy trình thu hồi (§6.4).

**Command `run:gen-lucky` cũ — khuyến nghị:**
- **KHÔNG xoá** (cổng chạy đã verified với nó; xoá làm mất đường cứu hộ khi bảng mới gặp sự cố).
- **Thêm cảnh báo bắt buộc xác nhận:** in cảnh báo tiếng Việt *"Lệnh này cấp mã theo TỪNG BẢN GHI
  attendee, có thể khiến một người nhận nhiều mã. Hãy dùng `final-attendee-roster:gen-lucky`. Tiếp
  tục?"* + `$this->confirm()` (bỏ qua bằng `--force` cho script).
- **Không** để lệnh này trong bất kỳ cron/script tự động nào.

---

## 10. Frontend Yii

### Files cần tạo
| File | Nội dung |
|------|----------|
| `protected/models/FinalAttendeeRosters.php` | `CFormModel`. Static: `getApiDataProvider($params)`, `getFilterOptions($eventId)`, `getStats($eventId)`, `syncViaApi($params)`, `provisionLuckyViaApi($eventId,$propertyId)`, `updateFieldsViaApi($id,$fields)`, `resetFieldsViaApi($id,$fields)`, `storeViaApi($data)`, `mergeViaApi()`, `splitViaApi()`, `deleteViaApi($id)`, `clearConflictViaApi($id)`, `getAudit($eventId, $scope)`. Hằng `STATUS_ACTIVE = 1`, `STATUS_MANUAL = 3`, `getStatusLabel()`, `EDITABLE_FIELDS`, `WRITE_BACK_FIELDS`. **Toàn bộ** `ApiClient` nằm ở đây |
| `protected/components/ApiEndpoints.php` (sửa) | Nhóm `FINAL_ATTENDEE_ROSTER_*`: `LIST`, `FILTERS`, `STATS`, `SYNC`, `PROVISION_LUCKY`, `UPDATE`, `RESET_FIELD`, `STORE`, `MERGE`, `SPLIT`, `DESTROY`, `CLEAR_CONFLICT`, `AUDIT` |
| `protected/modules/admin/controllers/FinalAttendeeRostersController.php` | `actionAdmin`, `actionSyncPreview` (JSON), `actionSync`, `actionGenLucky`, `actionUpdateField` (JSON), `actionResetField` (JSON), `actionCreate`, `actionMerge`, `actionSplit`, `actionDelete`, `actionClearConflict`, `actionAudit` (JSON, chỉ đọc), `actionExport` |
| `.../views/finalAttendeeRosters/admin.php` | View chính |
| `.../views/finalAttendeeRosters/_filters.php` | Partial bộ lọc |
| `.../views/finalAttendeeRosters/_modal_sync.php` | Modal đồng bộ (chọn phạm vi + **bảng kết quả dry-run** + nút "Ghi thật") |
| `.../views/finalAttendeeRosters/_modal_edit_row.php` | Modal sửa **toàn bộ trường** của 1 người |
| `.../views/finalAttendeeRosters/_modal_gen_lucky.php` | Modal xác nhận cấp mã lucky |
| `.../views/finalAttendeeRosters/_modal_add_person.php` | Modal **thêm người thủ công** (§9) |
| `.../views/finalAttendeeRosters/_modal_merge_split.php` | Modal gộp / tách người |
| `themes/hope-ui/assets/js/pages/finalattendeerosters-admin.js` | Toàn bộ JS |

> 5 modal ⇒ **bắt buộc** tách `_modal_*.php` riêng.
> **Không** inline `<script>`; register bằng
> `Yii::app()->clientScript->registerScriptFile(Yii::app()->theme->baseUrl . '/assets/js/pages/finalattendeerosters-admin.js', CClientScript::POS_END)`.
> Cấu hình (URL action, danh sách trường editable, nhãn tiếng Việt) truyền qua `data-*` trên
> `<div id="final-attendee-roster-config">`.
> View **không gọi Model** — mọi dropdown truyền từ controller qua `render()`.

### Mô tả UI

**Header card**
- Tiêu đề "Tổng hợp danh sách Vòng Chung Kết".
- Dropdown **Sự kiện** + **Đợt VCK** (bắt buộc; chọn xong mới hiện bảng).
- Nút **"Đồng bộ từ danh sách VCK"** (cần `create`) → `_modal_sync`:
  1. Chọn phạm vi (toàn sự kiện / một đơn vị).
  2. **"Xem trước"** → bảng số liệu *Thêm mới / Khôi phục / Cập nhật / Bỏ qua do đã sửa tay / Huỷ tư
     cách / Không đổi / Xung đột*, kèm chi tiết (tên người, trường bị bỏ qua, mã bị xung đột).
  3. **"Ghi thật"** → `mode=apply` → Toast + reload. Tuân `modal-submit.md` (disable + spinner).
- Nút **"Cấp mã lucky"** (cần `create`) — tooltip nhắc rõ: *"Chỉ cấp cho người chưa có mã. Mã đã cấp
  không bao giờ đổi."*
- Nút **"Thêm người"** (cần `create`) → `_modal_add_person`; sau khi lưu, Toast hiện **mã lucky +
  định danh `DHMT…`** vừa cấp để HO ghi lại ngay.
- Nút **"Xuất Excel"**.
- Dòng phụ: *"Đồng bộ lần cuối: 03/10/2026 14:22"*; nếu > 24h ⇒ badge vàng "Dữ liệu có thể đã cũ".
- **Hai badge đối soát** (nguồn: `GET /api/final-attendee-rosters/audit`, §8.12 — gọi cùng lúc với
  `stats` khi nạp trang):
  - **"Đã dùng MT: 12/999"** — số thẻ `MT` đã dùng / dung lượng dải; **đỏ** nếu còn < 50 suất;
    tooltip hiện `next_badge_number`. Nếu `invalid_format` không rỗng ⇒ badge đỏ kèm icon ⚠ và
    tooltip *"Có N số thẻ MT sai format, có thể làm lệch bộ sinh — cần xử lý"*.
  - **"Xung đột mã lucky: N"** — **đỏ** nếu `N > 0`; click mở danh sách người bị ảnh hưởng (§6.4).

**Dải thống kê** (6 thẻ): Tổng số người · Đã có mã lucky · Chưa có mã lucky · Đã đặt PIN ·
Đã sửa tay · **Xung đột mã** (đỏ nếu > 0).
Nếu `without_lucky > 0` ⇒ badge đỏ "Còn N người chưa có mã lucky" cạnh nút cấp mã.

**Bộ lọc** (`_filters.php`, form GET): Đơn vị → **Bộ phận** (dropdown phụ thuộc, AJAX theo pattern
`Dependent Dropdown`) → **Phòng ban** (phụ thuộc Bộ phận) · Loại người tham dự · Trạng thái mã lucky ·
Đã sửa tay · Có xung đột · **Hiện cả người đã huỷ tư cách** (checkbox → `with_trashed=1`) ·
Từ khoá · nút "Tìm kiếm" / "Xoá lọc".

**Bảng dữ liệu** (phân trang 25/50/100)

| # | Cột | Ghi chú |
|---|-----|---------|
| 1 | STT | |
| 2 | Mã lucky | in đậm + `DHMT123456` + icon copy; rỗng ⇒ badge xám "Chưa cấp"; **không có nút sửa/cấp lại** |
| 3 | Họ và tên | inline edit; link phụ sang `admin/attendees/view` theo `attendee_id` |
| 4 | Mã NV | inline edit |
| 5 | Đơn vị | `property_name`, inline edit |
| 6 | Bộ phận | `division_name`, rỗng ⇒ badge vàng "Chưa xác định", inline edit |
| 7 | Phòng ban | `department_name`, inline edit |
| 8 | Chức danh | `position_display`, inline edit — **tooltip nhắc: "Sửa ở đây sẽ áp dụng cả trên thẻ và email"** |
| 9 | Size áo | inline edit (dropdown) |
| 10 | Loại | badge `finalist`/`director`/`driver`/**`manual`** |
| 11 | PIN | badge "Đã đặt" / "Chưa đặt" |
| 12 | Trạng thái | badge; dòng đã xoá mềm ⇒ badge đỏ **"Đã huỷ tư cách"** + dòng làm mờ; có `conflict_flag` ⇒ badge đỏ "Xung đột mã" + nút "Đã xử lý" |
| 13 | Thao tác | "Sửa" (`_modal_edit_row`) · "Gộp/Tách" · "Huỷ tư cách" (SweetAlert) |

**Đánh dấu trường đã sửa thủ công**
- Ô thuộc `overridden_fields` ⇒ viền trái cam + icon ✎.
- Tooltip: **"Đã sửa tay — Gốc: {source_snapshot.field}"**.
- Icon ↺ **"Khôi phục gốc"** trong ô → `resetField`.
- Dòng `has_override = 1` ⇒ badge "Đã sửa tay (N trường)".
- Filter nhanh ở header: "Chỉ xem dòng đã sửa tay".

**Inline edit (JS)**
- Click ✎ / double-click ô ⇒ input/select tại chỗ. Enter/blur ⇒ `POST actionUpdateField` → cập nhật
  ô + dấu override + `Toast.success('Đã cập nhật.')`. Esc ⇒ huỷ. Lỗi ⇒ **giữ giá trị cũ** +
  `Toast.error`, **không reload**.
- Cột **Mã lucky không có đường inline edit** (quyết định #8).
- **Toast** thay Alert ở mọi nơi; **SweetAlert2** cho huỷ tư cách / khôi phục hàng loạt
  (`confirmDelete(formId)` / `MyHelper::renderDeleteButton()`).
- Thông báo huỷ tư cách (SweetAlert): *"Huỷ tư cách người này? Mã lucky sẽ được giữ lại và không
  cấp cho người khác."*

### Xuất Excel
`actionExport` lấy toàn bộ dòng theo bộ lọc (`per_page` lớn, giới hạn 10.000, chunk).
Cột: Mã lucky · Định danh (`DHMT`+lucky) · Họ tên · Mã NV · CCCD · Ngày sinh · Đơn vị · Nhãn in thẻ ·
Bộ phận · Phòng ban · Chức danh hiển thị · Chức danh gốc (SMILE) · Size áo · Loại · Trạng thái ·
Đã sửa tay (các trường) · Xung đột.
Tên file: `TongHop_VCK_Lucky_{event_id}_{Ymd_His}.xlsx`.

---

## 11. Luồng nghiệp vụ

### 11.1 Đồng bộ (migrate) — có dry-run, có restore, có write-back
```mermaid
sequenceDiagram
  participant HO as Admin HO
  participant JS as finalattendeerosters-admin.js
  participant C as FinalAttendeeRostersController
  participant M as Model FinalAttendeeRosters
  participant API as BE /api/final-attendee-rosters/sync
  participant SVC as FinalAttendeeRosterService
  participant SRC as FinalAggregationService

  HO->>JS: "Đồng bộ từ danh sách VCK" → chọn phạm vi → "Xem trước"
  JS->>C: POST /admin/finalAttendeeRosters/syncPreview
  C->>M: syncViaApi({mode:'preview'})
  M->>API: POST /api/final-attendee-rosters/sync
  API->>SVC: sync(eventId, periodId, property, 'preview')
  SVC->>SRC: listFinalAttendees(periodId, property)
  SRC-->>SVC: finalist (+ source_attendee_ids)
  SVC->>SVC: buildDedupKey → gộp người → chọn bản đại diện
  SVC->>SVC: tìm dòng bằng withTrashed(event_id, dedup_key)
  SVC->>SVC: mỗi field: nếu in overridden_fields → SKIP; trashed+còn trong nguồn → RESTORE (giữ mã)
  SVC->>SVC: dòng ACTIVE không còn trong nguồn (bỏ qua MANUAL) → SOFT DELETE (giữ mã)
  SVC->>SVC: ghi final_attendee_roster_sync_logs(mode=preview)
  SVC-->>API: summary + detail
  API-->>JS: JSON
  JS-->>HO: Bảng xem trước + cảnh báo
  HO->>JS: "Ghi thật"
  JS->>C: POST actionSync (mode=apply)
  C->>API: mode=apply
  API->>SVC: sync(..., 'apply') trong transaction + lock
  SVC->>SVC: insert / restore / update / soft delete; set last_synced_at + source_snapshot
  SVC->>SVC: KHÔNG chạm lucky_number; re-apply WRITE-BACK chức danh/nhãn thẻ sang attendees (tự chữa)
  SVC-->>API: summary thật
  API-->>JS: {success, message}
  JS->>JS: đóng modal + Toast.success + reload
```

**Giả mã lõi (per-field + restore + write-back):**
```
foreach (người đã gộp as $src) {
    $key = buildDedupKey($src);
    $row = FinalAttendeeRoster::withTrashed()
             ->where('event_id', $eventId)->where('dedup_key', $key)->first();

    if ($row === null) {
        $row = new FinalAttendeeRoster(['status' => STATUS_ACTIVE]);   // lucky_number = NULL
        $report->inserted++;
    } elseif ($row->trashed()) {
        $row->restore();                 // GIỮ NGUYÊN lucky_number — mã đi theo người
        $row->deleted_by = null;
        $report->restored++;
    }

    $overridden = $row->overridden_fields ?: [];
    foreach (SYNCABLE_FIELDS as $f) {
        if (in_array($f, $overridden)) { $report->skipped_override[] = [$row, $f]; continue; }
        $row->$f = $src->$f;
    }
    $row->attendee_id         = $src->primary_attendee_id;   // luôn cập nhật (hệ thống)
    $row->source_attendee_ids = $src->attendee_ids;
    $row->source_snapshot     = snapshot($src);
    $row->last_synced_at      = time();
    // lucky_number: KHÔNG BAO GIỜ ghi ở bước đồng bộ
    $row->save();

    // Tự chữa write-back (quyết định #10)
    writeBackToAttendee($row);           // chỉ các trường trong WRITE_BACK_FIELDS đang bị override
}

// Người có trong bảng nhưng KHÔNG còn trong nguồn VCK:
//   BỎ QUA dòng status = MANUAL  ← quan trọng, nếu không người HO thêm tay sẽ bị xoá
//   còn lại: SOFT DELETE, GIỮ lucky_number, ghi soft_deleted_rows
```

### 11.2 Cấp mã lucky — idempotent tuyệt đối
```mermaid
sequenceDiagram
  participant HO as Admin HO
  participant C as Controller
  participant API as BE /provision-lucky
  participant SVC as FinalAttendeeRosterService
  participant DB as MySQL

  HO->>C: "Cấp mã lucky" → xác nhận
  C->>C: can('finalattendeerosters','create')
  C->>API: POST {event_id, property_id}
  API->>SVC: provisionLucky()
  loop CHỈ dòng lucky_number IS NULL
    SVC->>DB: tìm lucky_number sẵn có trong source_attendee_ids
    alt đã có (1 mã)
      SVC->>DB: TÁI DÙNG mã đó (reused++)
    else đã có (nhiều mã khác nhau)
      SVC->>DB: giữ mã cấp sớm nhất; SET NULL mã kia trên attendees
      SVC->>DB: ghi conflict_flag='duplicate_lucky' + log conflicts
    else chưa ai có
      SVC->>DB: sinh 6 số; check trùng attendees + rosters (withTrashed); retry 3 lần
    end
    SVC->>DB: UPDATE rosters SET lucky_number, lucky_provisioned_at
    SVC->>DB: UPDATE attendees SET lucky_number WHERE id = rosters.attendee_id
  end
  Note over SVC: Dòng ĐÃ CÓ mã → bỏ qua hoàn toàn, không UPDATE
  SVC-->>API: {provisioned, reused, conflicts}
  API-->>HO: Toast.success + reload
```

### 11.3 Sửa thủ công → write-back → thẻ & email
```mermaid
sequenceDiagram
  participant HO as Admin HO
  participant JS as Inline edit
  participant C as Controller
  participant API as BE
  participant DB as MySQL

  HO->>JS: Click ✎ ô "Chức danh" → nhập → Enter
  JS->>C: POST actionUpdateField {id, fields:{position:"Trưởng bộ phận Nhà hàng"}}
  C->>C: can('...','update'); email = AuthHandler::getUser()['email']
  C->>API: POST /api/final-attendee-rosters/update/{id}
  API->>DB: BEGIN
  API->>DB: UPDATE rosters SET position, overridden_fields += "position", has_override=1, updated_by, updated_at
  API->>DB: UPDATE attendees SET position WHERE id = rosters.attendee_id   ← WRITE-BACK (ghi thẳng Entity, không qua whitelist)
  API->>DB: INSERT audit_logs (final_roster.update + final_roster.write_back)
  API->>DB: COMMIT
  API-->>JS: {success, data}
  JS->>JS: cập nhật ô + dấu ✎ cam + Toast.success
  Note over DB: Module in thẻ & EmailHelper đọc attendees.position → tự hiển thị chức danh mới
```

### 11.4 Thêm người thủ công (§9)
```mermaid
sequenceDiagram
  participant HO as Admin HO
  participant C as Controller
  participant API as BE /store
  participant SVC as FinalAttendeeRosterService
  participant AGG as FinalAggregationService

  HO->>C: "Thêm người" → điền form → Lưu
  C->>API: POST {event_id, period_id, property_id, role_id, full_name, ...}
  API->>SVC: createManual()
  SVC->>SVC: BEGIN; buildDedupKey → check trùng (withTrashed) ⇒ 409 nếu có
  SVC->>AGG: tìm phiếu VCK (period, property); chưa có ⇒ ensureUnitRegistrations
  SVC->>SVC: sinh qr_token unique + lucky_number unique (check cả trashed)
  SVC->>SVC: INSERT attendees (is_active=1, approval_status=APPROVED, attendee_type='manual', lucky_number, qr_token)
  SVC->>SVC: INSERT rosters (status=MANUAL, attendee_id, lucky_number, overridden_fields = TẤT CẢ trường)
  SVC->>SVC: audit_logs; COMMIT
  SVC-->>API: {roster, attendee}
  API-->>HO: Toast "Đã thêm. Mã lucky 445566 — định danh DHMT445566"
  Note over HO: Người này đăng nhập cổng chạy bình thường (có row attendees + is_active=1)
```

---

## 12. Rủi ro & edge case

| # | Tình huống | Xử lý |
|---|-----------|-------|
| 1 | **Hai nguồn sự thật lệch nhau** | Hiện `last_synced_at` trên header + badge "Dữ liệu có thể đã cũ" nếu > 24h; nút đồng bộ luôn sẵn; mọi báo cáo bốc thăm **chỉ** đọc bảng mới |
| 2 | ⚠️ **Quên `withTrashed()` khi check trùng mã** | Dòng trashed vẫn giữ mã (quyết định #11) ⇒ nếu sinh mã mà chỉ check dòng active sẽ **ăn lỗi duplicate key bất ngờ**. Bắt buộc `withTrashed()` ở **mọi** truy vấn kiểm tra trùng; đưa vào tiêu chí verify slice S8 |
| 3 | ⚠️ **Đồng bộ xoá mất người HO thêm tay** | Bước "soft delete người không còn trong nguồn" **phải loại trừ `status = MANUAL`**. Bug dễ mắc ⇒ đưa vào tiêu chí verify slice S2 & S11 |
| 4 | **Một người giữ 2 mã** (do `run:gen-lucky` cũ) | Giữ mã cấp sớm nhất (`lucky_provisioned_at` → `pin_set_at` → `attendees.id`), NULL mã kia, ghi `conflict_flag` + log ⇒ HO thông báo thu hồi. Chạy `final-attendee-roster:audit` **trước khi phát định danh** |
| 5 | **Mã đã phát ra ngoài rồi bị NULL** | Người đó đăng nhập thất bại ⇒ quy trình thông báo thu hồi là **bắt buộc**; màn hình giữ badge đỏ tới khi HO bấm "Đã xử lý" |
| 6 | **Người bị huỷ tư cách rồi được đưa lại** | Đồng bộ **restore** dòng cũ, **giữ nguyên mã** ⇒ định danh đã phát vẫn dùng được. Cần set lại `attendees.is_active = 1` (quy trình huỷ/thay người lo) |
| 7 | **Người thay thế** | Người khác ⇒ dòng mới ⇒ mã mới. Phải chạy **đồng bộ + cấp mã** sau mỗi lần thay người, nếu không người thay **không đăng nhập được** |
| 8 | **Đơn vị nộp muộn** | Đồng bộ ⇒ dòng mới; badge "Còn N người chưa có mã" nhắc cấp bù |
| 9 | **Gộp sai / tách sai** | Preview báo `possible_wrong_merge` / `duplicate_person`; HO dùng Gộp/Tách (§7.4). Không auto-merge theo họ tên khi thiếu ngày sinh |
| 10 | **Sửa tay làm `dedup_key` đổi** | Lần đồng bộ sau sinh khoá mới ⇒ dòng mới (mã mới) và dòng cũ bị xoá mềm (giữ mã). Service phải phát hiện (`source_attendee_ids` giao nhau) và báo `duplicate_person` thay vì âm thầm tách. `dedup_key` **không** nằm trong `EDITABLE_FIELDS` |
| 11 | **Write-back bị ghi đè** bởi `attendees/update` không gửi `position` (`convertData` set NULL) | **Tự chữa** ở cuối mỗi lần đồng bộ (re-apply write-back); kiểm tra các form update attendee gửi đủ `position` |
| 12 | **Write-back `unit_label`/`full_name` bị whitelist finalist lọc** | Write-back **không đi qua** `attendees/update` — ghi thẳng Entity trong BE service (§5.4 điểm 3) |
| 13 | **`full_name` write-back gây ảnh hưởng lan rộng** (mail/phiếu/đội) | Cờ cấu hình `WRITE_BACK_FULL_NAME = false` ở giai đoạn đầu; bật khi chủ dự án xác nhận (§13) |
| 14 | **Override "khoá chết" dữ liệu sai** | Bắt buộc có nút "Khôi phục gốc" từng trường + báo cáo "các trường đang override" trong kết quả đồng bộ |
| 15 | **`overridden_fields` chứa tên trường không còn tồn tại** | Khi đọc, lọc theo `EDITABLE_FIELDS` hiện hành; bỏ qua tên lạ, không lỗi |
| 16 | **JSON không hỗ trợ** (MySQL 5.6) | `TEXT` + cast `array`; thay `JSON_CONTAINS` bằng `has_override` + lọc ở tầng ứng dụng |
| 17 | **Người `manual` không có phiếu VCK của đơn vị** | Tái dùng `ensureUnitRegistrations` tạo phiếu rỗng; nếu vẫn thất bại ⇒ 422, không tạo nửa vời (đã trong transaction) |
| 18 | **`qr_token` trùng** | Sinh `bin2hex(random_bytes(16))` + retry 3 lần; `qr_token` nên có UNIQUE trên `attendees` (kiểm tra, nếu chưa có ⇒ đề xuất bổ sung) |
| 19 | **Rò rỉ danh sách mã lucky** (Excel) | Mã chỉ là *định danh*, vẫn cần PIN. `login_pin` **không bao giờ** trả qua API/Excel/log; mã không đưa vào URL công khai |
| 20 | **Hiệu năng** | Chunk 500 dòng; export ≤ 10.000 dòng/lần; đồng bộ trong transaction + lock; chạy được qua artisan |
| 21 | **Đồng bộ chạy chồng** | Cache lock `far:sync:{event_id}` TTL 300s ⇒ 409 "Đang có tiến trình đồng bộ khác" |
| 22 | **Sai `period_id`** | BE validate ⇒ 422 "Đợt không phải Vòng Chung Kết" |

---

## 13. Phân rã công việc (vertical slice)

| Slice | Nội dung | Verify được bằng | Ước lượng | Phụ thuộc |
|-------|----------|------------------|-----------|-----------|
| **S0** | Migration `final_attendee_rosters` + `final_attendee_roster_sync_logs`; Entity (SoftDeletes, cast JSON = array, timestamp unix, hằng `EDITABLE_FIELDS`/`SYNCABLE_FIELDS`/`WRITE_BACK_FIELDS`/`STATUS_*`, accessor `position_display`); **index/unique theo §7.2**. Bổ sung `lucky_number` + `pin_is_set` vào `AttendeeResource` | `artisan migrate` + tinker tạo/đọc 1 dòng, cast JSON đúng; thử insert 2 dòng cùng `(event_id, dedup_key)` ⇒ **bị chặn** | **M (1d)** | — |
| **S1** | `buildDedupKey()` + logic **gộp người** + chọn bản đại diện + fill-in + phát hiện conflict. Unit test | 3 bản ghi 1 người ⇒ 1 kết quả; 2 người trùng tên ⇒ báo conflict | **M (1d)** | S0 |
| **S2** | `sync()` + **dry-run/preview** + **bảo vệ per-field** + **restore dòng trashed giữ mã** + **soft delete loại trừ MANUAL** + ghi log. Endpoint `sync`. Command `...:sync --dry-run` | Preview ⇒ số liệu; apply ⇒ dữ liệu vào bảng; **sửa 1 trường rồi apply lại ⇒ trường đó KHÔNG đổi** + báo `skipped_override`; **xoá mềm 1 người rồi apply lại ⇒ RESTORE và `lucky_number` KHÔNG đổi**; **dòng MANUAL không bị xoá** | **L (3d)** | S1 |
| **S3** | BE: `GET` list (filter đầy đủ, `with_trashed`, phân trang, sort) + `/filters` + `/stats` | Postman: lọc theo đơn vị/bộ phận/phòng ban/`has_override`/`with_trashed` ra đúng | **M (1d)** | S0 |
| **S4** | FE: `ApiEndpoints` + model + `actionAdmin` + `admin.php` + `_filters.php` (bảng + lọc + phân trang + thống kê, **chỉ đọc**) | Mở `/admin/finalAttendeeRosters/admin?event_id=3&period_id=4`, lọc ra đúng người | **L (3d)** | S3 |
| **S5** | FE: dropdown phụ thuộc Đơn vị → Bộ phận → Phòng ban (AJAX) | Chọn đơn vị ⇒ bộ phận tự nạp đúng | **S (4h)** | S4 |
| **S6** | FE: `_modal_sync` (xem trước ⇒ ghi thật) + `actionSyncPreview`/`actionSync` + hiển thị `skipped_override`/`restored`/`soft_deleted`/`conflicts` | Bấm xem trước thấy số liệu; ghi thật dữ liệu vào bảng; trường đã sửa tay nằm trong danh sách bỏ qua | **M (1d)** | S2, S4 |
| **S7** | BE+FE: `update/{id}` + `reset-field/{id}` + inline edit nhiều trường + `_modal_edit_row` + badge ✎ + tooltip "Gốc: …" + nút ↺ | Sửa 3 trường ⇒ `overridden_fields` đúng; khôi phục 1 trường ⇒ gỡ đúng tên; audit log có bản ghi; **không có đường sửa `lucky_number`** | **L (3d)** | S0, S4 |
| **S8** | BE: `provisionLucky` theo người đã gộp + **ghi ngược `attendees`** + **idempotent tuyệt đối** + xử lý ca 2 mã (§6.4) + check trùng `withTrashed()` + lock/retry. Command `...:gen-lucky`. **+ endpoint audit tổng hợp `GET /audit` (§8.12, chỉ đọc) + command `...:audit` + 2 badge đối soát trên header FE**. FE: `_modal_gen_lucky` + `actionGenLucky` + `actionAudit`. **+ Ẩn nút "Cấp số lucky" cũ (§9.6)** + cảnh báo `confirm()` cho `run:gen-lucky` | Chạy 2 lần ⇒ lần 2 `provisioned=0` và **không UPDATE nào lên `lucky_number`**; người 2 bản ghi ⇒ **1 mã**; `attendees.lucky_number` bản đại diện đúng; **test cổng chạy: `DHMT`+mã login được**; ca 2 mã ⇒ ghi `conflict_flag` + log đúng người; **nút cũ không còn hiện và URL `runRegistrations/genLucky` trả 410**; **`GET /audit` trả đúng `used/remaining/next_badge_number`, phát hiện được giá trị `MT*` sai format, và KHÔNG ghi gì vào DB** | **L (3.5d)** | S0, S1 |
| **S9** | ⭐ **Write-back chức danh/nhãn thẻ** (quyết định #10): `writeBackToAttendee()` ghi thẳng Entity trong transaction + **re-apply tự chữa cuối mỗi lần sync** + audit log + cờ `WRITE_BACK_FULL_NAME`. Kiểm chứng thẻ & email | Sửa chức danh trên bảng mới ⇒ **`attendees.position` đổi** ⇒ **PDF/email xác nhận hiện chức danh mới**; chạy `syncWithStaffData` sau đó ⇒ `position` **không bị ghi đè**; `unit_label` ghi được dù attendee là `finalist` (bypass whitelist) | **M (1.5d)** | S7 |
| **S10** | ⭐ **Thêm người thủ công + tạo `attendees` tối thiểu** (quyết định #9): `createManual()` một transaction, `ensureUnitRegistrations`, sinh `qr_token`+`lucky_number` unique, **`badge_number` `MT`+3 số (§9.5) có lock + retry + chặn tràn 999**, `role_id` **resolve 3 bước theo `code = 'btc'` + fallback param, KHÔNG hardcode id (§9.4)**, `status=MANUAL`, `overridden_fields` = tất cả. FE `_modal_add_person` (có dropdown vai trò chọn sẵn mặc định) + `actionCreate` | Thêm 1 người ⇒ có **cả** dòng roster lẫn dòng `attendees`; **đăng nhập cổng chạy `DHMT`+mã thành công** + đặt PIN được; **chạy sync sau đó ⇒ người này KHÔNG bị xoá, KHÔNG bị ghi đè**; đơn vị chưa có phiếu VCK ⇒ phiếu được tạo; **thêm 2 người liên tiếp ⇒ `MT001`, `MT002` không trùng**; **`role_id` resolve ra `btc` dù id khác môi trường; xoá mềm record `btc` ⇒ rơi về fallback param và ghi log cảnh báo**; **giả lập max = 999 ⇒ trả 422 với thông điệp rõ ràng, không tạo bản ghi nào** | **M (1d)** | S8 |
| **S11** | FE: xuất Excel theo bộ lọc (PHPExcel) | Tải file, kiểm đủ cột + đúng bộ lọc + cột "Đã sửa tay"/"Xung đột" | **M (1d)** | S4 |
| **S12** | BE+FE: `merge` / `split` + `_modal_merge_split` + `destroy` (**xoá mềm giữ mã** + `also_deactivate_attendee`) + `clear-conflict` | Gộp 2 dòng ⇒ 1 dòng, mã theo §6.2; huỷ tư cách ⇒ dòng trashed **vẫn giữ mã**, cấp mã lại **không** cấp mã đó cho ai; cổng chạy chặn đăng nhập người đó | **M (1d)** | S7, S8 |
| **S13** | Phân quyền: thêm `finalattendeerosters` vào `MControllers` + `roles.controllers`, gate controller + view, menu sidebar | HR không quyền update ⇒ không thấy nút sửa; URL trực tiếp ⇒ 403 | **S (4h)** | S4, S6, S7, S8, S10 |
| **S14** *(tuỳ chọn)* | Thêm `division_code`/`division_name` vào `attendees` + điền ở `syncWithStaffData` + command backfill | Chạy backfill, đếm số người còn "Chưa xác định" | **M (1d)** | S0 |

**Thứ tự thực thi:**
`S0 → S1 → (S2 ∥ S3) → S4 → (S5 ∥ S6 ∥ S7 ∥ S8) → S9 → S10 → S11 → S12 → S13 → [S14]`

### Tổng ước lượng — CHỐT

| Nhóm | Ngày công |
|------|-----------|
| S0–S8 (nền + đồng bộ + danh sách + sửa tay + cấp mã + **audit**) | 17.0 |
| **S9 — write-back thẻ/email (quyết định #10)** | **+1.5** |
| **S10 — thêm người thủ công + attendees tối thiểu (quyết định #9)** | **+1.0** |
| S11–S13 (Excel, gộp/tách/huỷ, phân quyền) | 2.5 |
| **TỔNG CHỐT** | **≈ 22.0 ngày công** |
| S14 tuỳ chọn (`division_*` trên `attendees`) | +1.0 |

> So với bản revise 3 (≈21.5d): **+0.5 ngày** cho **endpoint audit tổng hợp + command + 2 badge đối
> soát trên UI** (quyết định #16 & #18), gộp vào **S8 (3d → 3.5d)**.
>
> Ba quyết định #13/#14/#17 **không làm tăng ngày công**: bộ sinh `badge_number` + resolve vai trò nằm
> trong **S10 (1d đã có)**; chọn scope **toàn hệ thống** (phương án B) đã **loại bỏ** khoản **+2h** của
> phương án hậu tố sự kiện. Việc ẩn nút cũ nằm trong S8.

**Mốc giao hàng gợi ý:**
- **Mốc 1 (S0–S4, ~8d):** xem được danh sách VCK tổng hợp; đồng bộ chạy qua artisan.
- **Mốc 2 (S5–S8, ~8d):** đồng bộ + sửa tay + cấp mã lucky hoạt động đủ trên UI.
- **Mốc 3 (S9–S10, ~2.5d):** chức danh lan toả sang thẻ/email; thêm người thủ công đăng nhập được.
- **Mốc 4 (S11–S13, ~2.5d):** Excel, gộp/tách/huỷ, phân quyền — sẵn sàng production.

---

## 14. Câu hỏi còn tồn — **KHÔNG câu nào chặn triển khai**

> ✅ **Toàn bộ câu hỏi chặn đã được chốt** (18 quyết định ở §0). **Slice S0 có thể bắt đầu ngay.**
>
> Đã chốt và xoá khỏi danh sách: gộp người · phạm vi cấp mã · lọc bộ phận · kiến trúc bảng riêng ·
> dirty-field per-field · vòng đời mã lucky bất biến · người thủ công đăng nhập được · phạm vi chức
> danh + write-back · xoá mềm giữ mã · tên bảng · **vai trò `btc`** · **`badge_number` `MT`+3 số** ·
> **scope số thẻ toàn hệ thống** · **ẩn nút cấp mã cũ** · **đối soát qua API** · **một endpoint audit**.
>
> 12 câu dưới đây là **tinh chỉnh phạm vi / vận hành**, trả lời được **trong lúc build** mà không
> chặn slice nào. Cột "Chặn slice" ghi rõ thời điểm muộn nhất cần câu trả lời.

### Nhóm A — tinh chỉnh tính năng (trả lời trước slice tương ứng là đủ)

| # | Câu hỏi | Đề xuất của tài liệu | Cần trả lời trước |
|---|---------|----------------------|-------------------|
| 1 | **`full_name` có write-back về `attendees`** không? Đổi tên trên `attendees` ảnh hưởng email/phiếu/đội thể thao | **Tạm KHÔNG** — cờ `WRITE_BACK_FULL_NAME = false`; bật sau không phát sinh ngày công | **S9** |
| 2 | Xác nhận cần nút **"Khôi phục về dữ liệu gốc"** (từng trường + cả dòng)? | **Có** — đã nằm trong S7 | **S7** |
| 3 | Có cần **UI lịch sử thay đổi theo trường** (ai sửa gì, lúc nào, giá trị cũ)? | **Chưa cần** — hiện đã có `audit_logs` + `updated_by`/`updated_at`. Nếu cần ⇒ **+1 ngày** | Sau S7 (tăng phạm vi) |
| 4 | **Đồng bộ tự động theo cron** hay chỉ bấm tay? | **Chỉ bấm tay** — vì cần HO xem preview trước khi ghi | Sau S6 (tăng phạm vi) |
| 5 | Huỷ tư cách có **đồng thời set `attendees.is_active = 0`**? | **Có** — tham số `also_deactivate_attendee = true`, để cổng chạy chặn đăng nhập ngay | **S12** |
| 6 | Có cần **reset PIN** cho người quên PIN trên màn này? | Chưa có; nếu cần ⇒ **+4h** | Sau S7 (tăng phạm vi) |
| 7 | Có cần **đóng băng danh sách** tại thời điểm bốc thăm (cột `locked_at` + chặn sửa/đồng bộ)? | Chưa cần; nếu cần ⇒ **+4h** | Trước ngày bốc thăm |

### Nhóm B — vận hành & quy trình (không ảnh hưởng code)

| # | Câu hỏi | Đề xuất của tài liệu |
|---|---------|----------------------|
| 8 | **Giữ 6 chữ số** cho mã lucky (ràng buộc BIB `run_events.code + lucky_number`) — xác nhận? | Giữ 6 số, không đổi |
| 9 | **Quy trình thông báo thu hồi định danh** cho người bị NULL mã (ca §6.4): ai thông báo, kênh nào? | Màn hình đã có badge + báo cáo; cần chỉ định người chịu trách nhiệm |
| 10 | Command `run:gen-lucky` cũ: **giữ lại + thêm cảnh báo `confirm()`** (không xoá, để còn đường cứu hộ) — xác nhận? | Giữ + cảnh báo (§9.6) |
| 11 | Ai được sửa dữ liệu: **Admin HO + HR**? Đơn vị **không** — xác nhận? | HO + HR; đơn vị không truy cập |
| 12 | **Excel chứa mã lucky** phát cho ai? Cần thêm cột nào (SĐT, email) để phân phát định danh? | Hiện đã có đủ cột định danh; thêm SĐT/email nếu cần phân phát qua tin nhắn |
