# Nhật ký thực thi — Tổng hợp danh sách VCK & cấp mã Lucky

> Ghi lại toàn bộ công việc đã làm theo bản thiết kế [`tong-hop-danh-sach-lucky.md`](tong-hop-danh-sach-lucky.md).
> Cập nhật: 2026-10-04. Phạm vi: slice **S0 → S13** (hết phạm vi thiết kế) + vòng kiểm thử trước deploy (§11).

---

## 1. Tình trạng hiện tại

| Hạng mục | Trạng thái |
|----------|-----------|
| S0 — Migration + Entity + index/unique | ✅ Xong |
| S1 — Gộp người (dedup) + unit test | ✅ Xong |
| S2 — Đồng bộ + xem trước + bảo vệ per-field | ✅ Xong |
| S3 — API danh sách + bộ lọc + thống kê | ✅ Xong |
| S4 — FE danh sách (chỉ đọc) | ✅ Xong |
| S5 — Dropdown phụ thuộc Đơn vị → Bộ phận → Phòng ban | ✅ Xong |
| S6 — Modal đồng bộ (Xem trước → Ghi thật) | ✅ Xong |
| S7 — Sửa thủ công + khôi phục gốc | ✅ Xong |
| S8 — Cấp mã lucky + ghi ngược + đối soát + ẩn nút cũ | ✅ Xong |
| S9 — Write-back chức danh sang thẻ/email | ✅ Xong (xem §5.2) |
| S10 — HO thêm người thủ công | ✅ Xong |
| S11 — Xuất Excel theo bộ lọc | ✅ Xong |
| S12 — Gộp dòng / Tách người / Huỷ tư cách | ✅ Xong |
| S13 — Phân quyền + menu sidebar | ✅ Xong (xem §6.3) |
| Kiểm thử trước deploy | ✅ Xong (xem §11) |

**Dữ liệu trên môi trường local sau cùng:** 619 người, 619 mã lucky, 0 xung đột,
0 mã bị khoá, dải số thẻ `MT` còn nguyên 0/999.

---

## 2. Việc còn phải làm trước khi dùng thật

1. **Deploy BE.** 13 endpoint `final-attendee-rosters/*` hiện **chỉ có ở local**. FE trỏ vào
   `externalApiUrl` nào thì endpoint phải tồn tại ở đó.
2. **Chạy đối soát một lần trên môi trường thật** trước khi cho HO thêm người thủ công:
   `php artisan final-attendee-roster:audit <event_id>` — xác nhận `Sai format = 0`.
3. **Chưa kiểm được trên trình duyệt thật.** Toàn bộ verify làm bằng cách render view với dữ liệu
   API thật (bắt được lỗi PHP, biến thiếu, HTML sai) nhưng **chưa kiểm CSS/layout và JS chạy thực
   tế**. Cần mở thử một lần ở môi trường có đăng nhập.

---

## 3. Danh sách file

### Backend — `E:\even_API\MTRegistrationPortal`

**Tạo mới**

| File | Nội dung |
|------|----------|
| `Modules/Registration/Database/Migrations/2026_10_03_110000_create_final_attendee_rosters_table.php` | 44 cột, 2 UNIQUE + 7 INDEX |
| `.../2026_10_03_110100_create_final_attendee_roster_sync_logs_table.php` | Lịch sử đồng bộ |
| `Modules/Registration/Entities/FinalAttendeeRoster.php` | Entity + hằng + accessor `position_display` |
| `Modules/Registration/Entities/FinalAttendeeRosterSyncLog.php` | Entity log |
| `Modules/Registration/Services/FinalAttendeeRosterService.php` | Toàn bộ nghiệp vụ (~1.100 dòng) |
| `Modules/Registration/Services/Interfaces/IFinalAttendeeRosterService.php` | Interface |
| `Modules/Registration/Repositories/FinalAttendeeRosterRepository.php` | Danh sách + bộ lọc + thống kê |
| `Modules/Registration/Repositories/Interfaces/IFinalAttendeeRosterInterface.php` | Interface |
| `Modules/Registration/Http/Controllers/FinalAttendeeRosterController.php` | 9 action, trả HTTP status thật |
| `Modules/Registration/Http/Resources/FinalAttendeeRosterResource.php` | Resource (không bao giờ trả `login_pin`) |
| `Modules/Registration/Exceptions/DedupKeyClashException.php` | Lỗi trùng khoá gộp → map 409 |
| `Modules/Registration/Console/SyncFinalAttendeeRosterCommand.php` | `final-attendee-roster:sync` |
| `Modules/Registration/Console/GenLuckyFinalAttendeeRosterCommand.php` | `final-attendee-roster:gen-lucky` |
| `Modules/Registration/Console/AuditFinalAttendeeRosterCommand.php` | `final-attendee-roster:audit` |
| `tests/Unit/FinalAttendeeRosterDedupTest.php` | 18 unit test, thuần, chạy 0.02s |

**Sửa**

| File | Thay đổi |
|------|----------|
| `Modules/Registration/Http/Resources/AttendeeResource.php` | Thêm `lucky_number` + `pin_is_set` |
| `Modules/Registration/Providers/RegistrationServiceProvider.php` | 2 binding + 3 command |
| `Modules/Registration/Routes/api.php` | Nhóm 13 route `final-attendee-rosters/*` |
| `Modules/Registration/Config/config.php` | `write_back_full_name`, `final_roster_manual_role_code/_id` |
| `Modules/Run/Console/GenLuckyCommand.php` | Thêm cảnh báo + `confirm()` + cờ `--force` |

### Frontend — `e:\eventregis`

**Tạo mới**

| File | Nội dung |
|------|----------|
| `protected/models/FinalAttendeeRosters.php` | Model `CFormModel`, **toàn bộ `ApiClient` nằm ở đây** |
| `protected/modules/admin/controllers/FinalAttendeeRostersController.php` | 12 action |
| `protected/modules/admin/views/finalAttendeeRosters/admin.php` | View chính |
| `.../_filters.php` | Bộ lọc |
| `.../_cell.php` | Ô dữ liệu có dấu "đã sửa tay" |
| `.../_modal_sync.php` | Đồng bộ (xem trước → ghi thật) |
| `.../_modal_edit_row.php` | Sửa 13 trường của một người |
| `.../_modal_gen_lucky.php` | Cấp mã lucky |
| `.../_modal_add_person.php` | HO thêm người thủ công |
| `.../_modal_merge_split.php` | Gộp dòng / Tách người (2 tab) |
| `themes/hope-ui/assets/js/pages/finalattendeerosters-admin.js` | Toàn bộ JS, không inline |

**Sửa**

| File | Thay đổi |
|------|----------|
| `protected/components/ApiEndpoints.php` | 13 hằng `FINAL_ATTENDEE_ROSTER_*` |
| `protected/components/EmailHelper.php` | Thêm `resolveAttendeePosition()`, dùng ở 7 chỗ — xem §5.2 |
| `protected/modules/admin/views/runRegistrations/admin.php` | Ẩn nút "Cấp số lucky" cũ |
| `protected/modules/admin/controllers/RunRegistrationsController.php` | `actionGenLucky` trả 410 |

---

## 4. Kiểm chứng đã thực hiện

Tất cả chạy trên **dữ liệu VCK thật** (event 3, period 4): 626 bản ghi attendee → 619 người.

| Slice | Số ca PASS | Điểm kiểm chứng đáng chú ý |
|-------|-----------:|----------------------------|
| S0 | 19 | UNIQUE `(event_id, dedup_key)` chặn thật; xoá mềm giữ mã; `withTrashed()` thấy dòng trashed |
| S1 | 18 unit | 3 bản ghi 1 người → 1 dòng; 2 người trùng tên → gắn cờ |
| S2 | 33 | Dry-run **không ghi gì**; chạy lại `unchanged=619`; sửa tay **không bị ghi đè**; `MANUAL` không bị xoá; 409 khi chạy chồng |
| S3 | — | 13 bộ lọc đúng số; `login_pin` không lọt response; `sort_by` có whitelist |
| S4 | 57 | Lọc đúng; badge trạng thái; không inline script |
| S5 | 16 | Phòng ban thu hẹp 26 → 9 → 1; **API key không lọt ra HTML** |
| S6 | 40 | Xem trước không ghi; ghi thật 619 dòng; 409 tới được FE |
| S7 | 65 | Sửa 3 trường → `overridden_fields` đúng; khôi phục gỡ đúng tên; 409 trùng khoá |
| S8 | 90 | **Idempotent thật** (bắt query: 0 `UPDATE` lên `lucky_number`); **cổng chạy login được**; mã bị khoá không cấp lại (thử 50 lần) |
| S9 | 41 | `unit_label` ghi được dù attendee là `finalist`; `syncWithStaffData` không chạm `position`; tự chữa khi bị ghi đè |
| S10 | 88 | Login cổng chạy + quét QR ngay; `MT001` → `MT002`; tràn 999 → 422 và **không tạo bản ghi nào** |
| S11 | 26 | Đúng 619 dòng; đọc lại file xlsx giữ dấu và không mất số 0 đầu |
| S12 | 70 | Huỷ tư cách giữ mã; khôi phục mã không đổi; gộp chuyển mã sang; tách → mã mới |

---

## 5. Lỗi thật tìm ra trong lúc làm

Đây là phần quan trọng nhất của nhật ký: những lỗi **chỉ phát hiện được khi chạy dữ liệu thật**,
không nhìn code mà thấy.

### 5.1 Lỗi trong code của tính năng này

| # | Lỗi | Hệ quả nếu để lọt | Cách sửa |
|---|-----|-------------------|----------|
| 1 | **`sort_order` nằm trong `SYNCABLE_FIELDS`** nhưng không có ở nguồn | Mỗi lần đồng bộ báo "cập nhật 619 người" dù không có gì đổi, và **thứ tự HO sắp tay bị reset về 0** | Tách `SYNCABLE_FIELDS` thành danh sách tường minh các trường **có nguồn**; service dùng chung hằng đó cho bước gộp |
| 2 | **Xung đột dương tính giả**: 3 ca báo lệch `id_card` nhưng chỉ chênh một ký tự xuống dòng (dữ liệu nhập từ Excel) | HO bị bắt soát 3 ca không tồn tại ngay lần đồng bộ đầu | So sánh sau khi chuẩn hoá khoảng trắng; trim dữ liệu nguồn khi map |
| 3 | **Dropdown phòng ban hiện nhãn trùng**: SMILE có nhiều mã cùng tên (650 và 810 đều là "An ninh - Kỹ thuật - CNTT") | HO không phân biệt được, chọn một cái sẽ **âm thầm bỏ sót người của mã kia** | Gộp option theo tên, `code` trả về là tập mã (`"650,810"`), bộ lọc nhận cả tập |
| 4 | **`full_name` thiếu trong `WRITE_BACK_FIELDS`** | Cờ `write_back_full_name` bật lên **cũng vô tác dụng** | Thêm vào danh sách, cờ vẫn mặc định tắt |
| 5 | **Xuất Excel lấy mãi một trang** → 619 người ra 10.000 dòng trùng lặp | HO nhận file rác và chờ rất lâu | Không dùng `ApiDataProvider` cho export (xem §6.1); thêm `fetchPage()` truyền `page` tường minh |
| 6 | **Cấp mã sau khi tách người vỡ UNIQUE** với lỗi `duplicate key` thô | HO bấm Cấp mã lucky sau khi tách → lỗi 500 không hiểu nổi | Khi tái dùng mã, loại các mã **đã thuộc dòng tổng hợp khác** (kiểm cả trashed) và ghi log |
| 7 | **Ghi ngược mã thiếu thì không có đường tự chữa** — cấp mã bỏ qua hoàn toàn dòng đã có mã | Người đó **âm thầm không đăng nhập được cổng chạy** dù màn hình trông như đã có mã | Thêm `repairWriteBack()` — đồng bộ `attendees` theo mã sẵn có, **không đổi mã đã cấp** |

### 5.2 Lỗi/giới hạn trong code sẵn có — cần bạn biết

| # | Phát hiện | Xử lý |
|---|-----------|-------|
| 8 | **Khoá `(event_id, dedup_key, deleted_at)` trong bản thiết kế là sai**: trong MySQL hai `NULL` không coi là trùng nhau, nên khoá đó **không chặn** được 2 dòng active cùng người | Đổi thành `(event_id, dedup_key)`; ca "huỷ rồi đưa lại" xử lý bằng **restore dòng cũ** |
| 9 | ⚠️ **Email đọc `position_name` trước `position`** — đây là chỗ khiến quyết định #10 không thành: chức danh HO sửa tay **không bao giờ hiện trong email** | Thêm `EmailHelper::resolveAttendeePosition()` ưu tiên `position` → `position_name`, dùng ở cả 7 chỗ. **Thay đổi này ảnh hưởng mọi luồng email**, không chỉ VCK — xem §7 |
| 10 | **Luồng in thẻ chưa tồn tại** ở cả hai repo (`BadgeService` chỉ CRUD bảng `badges`) | Không kiểm chứng được phần "thẻ in". Khi xây, luồng đó cần đọc `attendees.position` là tự hưởng write-back |
| 11 | `FinalAggregationService::listFinalAttendees` **không dùng được** làm nguồn đồng bộ: chỉ trả 10 trường, thiếu đúng `staff_code`/`id_card`/`birthday` cần để gộp; `source_attendee_ids` của nó mang nghĩa khác | Viết `fetchSourceRows()` riêng nhưng **dùng đúng cùng định nghĩa nguồn** (registrations của đợt `is_final` + `is_active=1`) |
| 12 | `listFinalAttendees` gán `'division_name' => $a->department_name` — **lẫn bộ phận với phòng ban** | Không sửa (ngoài phạm vi, màn khác đang dùng). Cần dọn khi làm S14 |

---

## 6. Chỗ làm khác bản thiết kế — và lý do

### 6.1 Kỹ thuật

| Điểm | Thiết kế nói | Đã làm | Lý do |
|------|--------------|--------|-------|
| `created_at`/`updated_at`/`deleted_at` | INT UNSIGNED unix | `timestamps()` datetime chuẩn Laravel | Toàn bộ bảng hiện có trong BE dùng datetime; làm khác sẽ vỡ cast mặc định của Eloquent và `SoftDeletes`. Cột thời gian **nghiệp vụ** (`lucky_provisioned_at`, `last_synced_at`) vẫn là unix INT, khớp `run_events.open_at` |
| Khoá ngoại | INT UNSIGNED | `unsignedBigInteger` | Khớp `attendees.id` / `events.id` thật, nếu không thì không join/FK được |
| `department_code` | Một string | Chấp nhận **tập mã** ngăn cách dấu phẩy | Lỗi #3 ở trên |
| Nguồn xuất Excel | `per_page` lớn + chunk qua DataProvider | `fetchPage()` riêng | `CDataProvider::getPagination()` gọi `getTotalItemCount()` nên **nạp luôn trang 1 và cache lại**, khiến `setCurrentPage()` sau đó vô tác dụng |

### 6.2 Bảo mật — không dùng `data-api-key`

`CLAUDE.md` (mục *Dependent Dropdown*) hướng dẫn JS **gọi thẳng External API** với
`data-api-key` nhúng trong HTML. **Tôi không làm vậy** — AJAX đi qua action `filterOptions` của Yii.

Lý do: nhúng `data-api-key` là đưa API key của hệ thống ra trình duyệt; ai mở DevTools trên trang
admin đều đọc được, và key đó gọi được *toàn bộ* External API chứ không riêng endpoint lọc. Trái
`rules/security.md`. Đi qua controller thì key nằm lại server, lại thêm được gate `PermissionHelper`.

Đã verify: API key, URL External API và `data-api-key` **đều không xuất hiện** trong HTML.

> ⚠️ Pattern cũ này đang được dùng ở chỗ khác trong dự án (vd form đăng ký event → period).
> Nếu cần, có thể rà và chuyển sang proxy tương tự — việc riêng, không thuộc phạm vi này.

---

### 6.3 S13 — phân quyền đi theo SSO, không qua `MControllers`

Bản thiết kế nói thêm controller vào bảng `m_controllers` + `roles.controllers`. **Làm vậy không
có tác dụng.** Đọc lại code thì bảng đó chỉ được `AdminController::dataTree()` dùng, mà `dataTree()`
chỉ được `themes/hope-ui/views/layouts/column2.php` gọi — layout **không được màn hình nào dùng**
(các controller admin kế thừa `Controller` nên chạy `//layouts/column1` → `//layouts/main`). Đây là
code cũ còn sót. Menu thật được `MenuHelper::buildMenuTree()` dựng từ danh sách quyền SSO.

Nên S13 làm ở hai chỗ:

| File | Thay đổi |
|------|----------|
| `protected/components/AuthHandler.php` | thêm luật kế thừa `finalattendeerosters` ← `approveregistrations`, dự phòng `registrations`; `inheritRelatedPermissions` nay nhận **danh sách** parent theo thứ tự ưu tiên |
| `protected/components/MenuHelper.php` | `appendFinalRosterItem()` dựng mục **"Tổng hợp VCK"**, đặt cùng nhóm với quyền nó kế thừa; `getIcon()` thêm bảng alias để dùng lại icon `attendee` |

Cách này **không cần sửa Portal**. Hệ quả nghiệp vụ cần biết: **ai duyệt được đăng ký thì vào được
màn hình này với đúng mức quyền đó** (duyệt `1 1 1 0` → sửa được, không xoá được). Nếu muốn siết
riêng, Portal phát quyền `finalAttendeeRosters` của chính nó là luật kế thừa tự nhường chỗ —
đã có test cho ca này.

Mục menu **không hiện ngay** cho người đang đăng nhập: `CacheHelper::getMenu()` cache theo token,
TTL 1 giờ. Đăng nhập lại là thấy, hoặc chờ tối đa 1 giờ.

**Kiểm chứng:** harness tạm (không commit, chạy bằng `php` CLI) — **17/17 PASS**, phủ: 3 nhánh kế thừa, không ghi đè
quyền Portal phát, không tự sinh quyền khi không có parent, wildcard `*` giữ nguyên, 2 luật cũ
không vỡ, mục menu hiện/ẩn đúng theo quyền đọc, nhãn tiếng Việt, URL, icon riêng, nằm đúng nhóm,
và không nhân đôi khi Portal đã phát mục này.

---

## 7. Quyết định thiết kế tự chốt trong lúc làm

Những điểm bản thiết kế không nói rõ, đã chọn và ghi lý do:

1. **Nút "Ghi thật" khoá đến khi đã xem trước**, và khoá lại nếu HO đổi phạm vi — thao tác chạm
   619 dòng, không nên cho ghi mù.
2. **Xem trước cũng cần quyền `create`**, không chỉ `read` — preview trả về toàn bộ danh sách VCK
   kèm chi tiết, là cửa đọc rộng hơn bảng.
3. **Danh sách chi tiết trong modal chỉ hiện 20 dòng đầu** — lần đồng bộ đầu có 619 dòng
   `inserted_rows`, đổ hết vào modal sẽ treo trình duyệt.
4. **Chỉ gửi trường thực sự đổi** khi sửa tay — modal có 13 input; gửi hết thì mọi trường bị đánh
   dấu "đã sửa tay" dù HO chỉ đổi một ô, và từ đó đồng bộ không bao giờ cập nhật chúng nữa.
5. **Nút ↺ chỉ hiện khi có giá trị gốc** — dòng HO tự thêm không có `source_snapshot`.
6. **Khôi phục gốc dùng SweetAlert xác nhận** — nó xoá giá trị HO đã sửa và mở lại cho đồng bộ ghi đè.
7. **Sửa mã NV/CCCD làm đổi khoá gộp → tính lại khoá**; nếu đụng dòng khác thì chặn 409 kèm tên
   người bị trùng và gợi ý Gộp dòng, thay vì để lỗi duplicate key thô. Không tự gộp.
8. **Một lock chung `far:create-manual`** cho cả mã lucky và số thẻ — hai HO thêm người cùng lúc
   mà không khoá sẽ cùng đọc một số lớn nhất rồi sinh số thẻ trùng.
9. **Sau khi thêm người, SweetAlert chờ HO bấm "Tôi đã ghi lại"** rồi mới reload — mã lucky và
   định danh `DHMT…` là thứ HO phải phát cho người đó.
10. **Write-back chọn ghi thẳng Entity**, không qua `attendees/update` — endpoint đó có whitelist
    cứng cho finalist nên `unit_label` và `full_name` sẽ bị lọc mất.
11. **Email: sửa thứ tự đọc thay vì ghi đè `position_name`** — phương án kia làm **mất vĩnh viễn**
    chức danh gốc SMILE (lần đồng bộ sau đọc giá trị đã bị ghi đè làm "gốc").

---

## 8. Ghi chú vận hành

### Lệnh artisan

```bash
# Đồng bộ danh sách VCK vào bảng tổng hợp
php artisan final-attendee-roster:sync <event_id> <period_id> [--property=] [--dry-run] [--run-by=]

# Cấp mã lucky theo NGƯỜI đã gộp (idempotent tuyệt đối)
php artisan final-attendee-roster:gen-lucky <event_id> [--property=] [--run-by=]

# Đối soát dải số thẻ MT + mã lucky (chỉ đọc)
php artisan final-attendee-roster:audit <event_id> [--scope=lucky|badge|all]
```

Lệnh cũ `run:gen-lucky` **vẫn còn** nhưng đã thêm cảnh báo + `confirm()`: nó cấp mã theo **từng bản
ghi attendee** nên một người có thể nhận nhiều mã. Giữ lại làm đường cứu hộ, dùng `--force` cho script.

### Gotcha khi chạy BE dev server

`artisan serve` spawn worker php không kèm cờ extension → lỗi 500 "could not find driver". Chạy trực tiếp:

```bash
/c/MAMP/bin/php/php8.1.0/php.exe -d extension_dir=/c/MAMP/bin/php/php8.1.0/ext \
  -d extension=mbstring -d extension=pdo_mysql -d extension=mysqli \
  -S 127.0.0.1:8000 server.php
```

`pkill -f` không kill được process Windows — dùng `taskkill //F //IM php.exe`.

### Quy tắc bất di bất dịch (đừng phá khi sửa về sau)

- **`lucky_number` không bao giờ bị ghi ở bước đồng bộ**, không có chức năng cấp lại mã.
- **Dòng xoá mềm GIỮ `lucky_number`** — chính `UNIQUE(lucky_number)` là cơ chế khoá mã. Mọi truy
  vấn check trùng mã **phải dùng `withTrashed()`**.
- **Không NULL `attendees.lucky_number`** của người bị huỷ tư cách — làm vậy giải phóng mã khỏi
  UNIQUE và có thể cấp cho người khác. `is_active = 0` đã đủ chặn đăng nhập.
- **Bước xoá mềm người không còn trong nguồn phải loại trừ `status = MANUAL`** — nếu không người
  HO thêm tay bị xoá ngay lần đồng bộ kế tiếp.
- **`attendees.lucky_number` vẫn là nguồn sự thật cho đăng nhập cổng chạy.** Cổng chạy Fun Run
  không sửa một dòng code nào trong toàn bộ công việc này.

---

## 9. Câu hỏi còn tồn

Xem §Câu hỏi của [`tong-hop-danh-sach-lucky.md`](tong-hop-danh-sach-lucky.md). Những câu đã được
trả lời bằng chính việc thực thi:

| Câu hỏi cũ | Trả lời từ thực tế |
|------------|--------------------|
| Ai chạy đối soát `badge_number LIKE 'MT%'` trên DB thật? | Không cần ai mở DB — có endpoint + command đối soát. Trên local: dải sạch **0/999** |
| `full_name` có write-back không? | Cờ đã có, **mặc định tắt**. Bật bằng `FINAL_ROSTER_WRITE_BACK_FULL_NAME=true` |
| Nút "Khôi phục về dữ liệu gốc" có cần không? | Đã làm (từng trường + cả dòng) |
| Huỷ tư cách có set `attendees.is_active = 0`? | Có, tham số `also_deactivate_attendee`, mặc định bật |

**Còn chờ bạn quyết:**

- Quy trình thông báo thu hồi định danh cho người bị NULL mã (ai làm, kênh nào) — hệ thống đã ghi
  đủ dữ liệu (badge đỏ + log + SweetAlert liệt kê), chỉ thiếu người chịu trách nhiệm.
- Đồng bộ theo cron hay chỉ bấm tay (hiện **chỉ bấm tay**).
- Có cần UI lịch sử thay đổi theo trường (hiện có `audit_logs` + `updated_by`).
- Có cần reset PIN và đóng băng danh sách khi bốc thăm.
- **Việc dữ liệu:** 66/619 người không có cả `staff_code`, `id_card` lẫn `birthday` → phải gộp bằng
  khoá yếu nhất `NB:<tên>|00000000`. Hiện không ai trùng tên nên chưa gộp sai, nhưng nếu có hai
  người trùng tên trong nhóm này thì họ **sẽ bị gộp thành một và nhận cùng một mã lucky**. Cách
  giảm rủi ro thật nhất là bổ sung `id_card` hoặc `staff_code` cho 66 người đó trước khi phát định danh.

---

## 10. Lưu ý về git

- **Repo BE** (`E:\even_API\MTRegistrationPortal`): 2 migration đã nằm trong commit
  `953478a Update VCK`; các file khác **chưa commit**.
- **Repo FE** (`e:\eventregis`): có **job auto-commit** đang commit mọi thứ theo chu kỳ. Nó đã
  commit kèm vài file tạm của quá trình test (`s4_render_tmp.php`, `fe.html`, …) — đã xoá khỏi
  working tree nhưng **còn trong git history**. Nếu muốn dọn history thì cần làm riêng.
- `protected/config/params.php` **không được git track** — an toàn, nhưng nhớ là file này quyết định
  FE gọi API ở đâu (`externalApiUrl`).

---

## 11. Vòng kiểm thử trước deploy (2026-10-04)

Chạy một vòng kiểm thử riêng trước khi lên production: 3 agent QA đọc độc lập (BE / FE / tích hợp)
cộng với kiểm thử **chạy thật qua HTTP** — việc chưa từng làm ở các slice trước.

### 11.1 Cách kiểm mới: dựng web server thật

Trước đây toàn bộ verify làm bằng render view trong CLI. Lần này dựng `php -S` trỏ vào repo, gieo
session đăng nhập giả (chỉ cấp quyền `approveregistrations`, **không** cấp `finalattendeerosters`,
để luật kế thừa của S13 phải tự chạy) rồi gọi bằng `curl`. Kết quả:

| Ca | Kết quả |
|----|---------|
| Trang danh sách | HTTP 200, 4,0s, 619 người, có mã DHMT |
| Mục menu "Tổng hợp VCK" | Hiện — chứng minh kế thừa quyền chạy thật, không cần sửa Portal |
| Toàn bộ CSS/JS (kể cả `finalattendeerosters-admin.js`, toast, sweetalert) | 200 hết |
| API key / URL External API / `login_pin` trong HTML | Không có |
| Tài khoản không có quyền | HTTP **403**, menu **không** hiện |
| Quyền `1 1 1 0` gọi `delete` | HTTP **403** (đúng: không có quyền xoá) |
| Quyền `1 1 1 0` gọi `updateField` | Qua cổng quyền, dừng ở kiểm dữ liệu (422) — đúng mức quyền |
| Xuất Excel | 200, 622 dòng = 3 dòng tiêu đề + **619 người**, 619 định danh DHMT, không lộ PIN |

Bug cũ "Excel xuất trùng trang 1" coi như đã chốt: file xuất ra khớp đúng số người.

### 11.2 Hai ca chưa ai kiểm được, nay đã kiểm — 25/26 PASS

DB local không có người HO thêm tay lẫn người bị huỷ tư cách, nên hai nhánh này trước giờ chỉ được
đọc code. Lần này tạo người thật qua API, kiểm, rồi dọn sạch:

**Ca người HO thêm tay:** status MANUAL ✓, cấp mã ngay ✓, số thẻ `MT001` đúng dạng ✓, chức danh gõ
tay được giữ ✓, ghi ngược sang `attendees` ✓, **đăng nhập cổng Fun Run bằng `DHMT`+mã ✓**, **quét QR
✓**, response không chứa `login_pin` ✓, đồng bộ lại không xoá mềm dòng này ✓ và không đổi mã ✓.

**Ca huỷ tư cách:** xoá mềm ✓, **mã lucky vẫn nằm trên dòng đã xoá ✓**, `attendees.lucky_number`
**không** bị NULL ✓, attendee bị khoá ✓, **người bị huỷ không đăng nhập được nữa ✓**, quét QR cũng bị
chặn ✓, cấp mã lại **không** trả mã bị khoá cho ai khác ✓, đối soát đếm đúng 1 mã bị khoá ✓ và
**không** báo nhầm mã đó là "mã lạc" ✓.

Idempotency kiểm lại riêng: chạy đồng bộ 2 lần liền → `updated: 0, unchanged: 619` cả hai lần.

### 11.3 Lỗi thật tìm ra và đã sửa trong vòng này

| # | Lỗi | Mức | Sửa |
|---|-----|-----|-----|
| 1 | **18/18 unit test BE lỗi hết.** `FinalAttendeeRosterService::__construct` nhận thêm `IAuditService` khi làm audit log, nhưng test vẫn gọi `new FinalAttendeeRosterService()` → `ArgumentCountError`. §4 của tài liệu này từng ghi "18 test PASS" — **điều đó đã sai kể từ lúc thêm audit log**. | Nghiêm trọng | Đưa bản giả `IAuditService` vào `setUp()`. Nay **18/18 PASS, 35 assertion**. |
| 2 | **`auditLucky` báo nhầm mã bị khoá là "mã lạc trên attendees".** Truy vấn không `withTrashed()` nên mã của người đã huỷ tư cách không có trong tập đối chiếu → đối soát khuyên HO đi xoá mã đó khỏi `attendees`, mà làm vậy chính là giải phóng mã — đúng điều thiết kế cấm. Đây lại là lệnh được khuyến nghị chạy trước khi dùng thật. | Cao | Lấy thêm danh sách mã trên dòng đã xoá mềm, loại khỏi phép kiểm "mã lạc". |
| 3 | **Cấp mã lỗi một người làm dừng cả lượt.** `generateUniqueLucky` ném exception thoát khỏi vòng lặp → người sau không được cấp, người trước đã cấp rồi, không ai biết dừng ở đâu. | Trung bình | Bọc từng người trong try/catch, ghi vào `conflicts` rồi đi tiếp. |
| 4 | `Resource` đọc `$this->attendee->login_pin` khi `attendee_id` là NULL (cột cho phép NULL) → cảnh báo PHP, Laravel có thể biến thành lỗi 500. | Trung bình | Đọc null-safe. |
| 5 | Cờ `also_deactivate_attendee` kiểm bằng 3 điều kiện lồng nhau, `"no"`/`"off"` lọt qua. | Thấp | Thay bằng `$request->boolean()`. |
| 6 | Nút lưu trong modal "Sửa thông tin" và "Thêm người" là `type="submit"`, trái `rules/modal-submit.md`. | Thấp | Đổi sang `type="button"` + `wireSubmitButton()` nối nút với form (vẫn bấm Enter được). |
| 7 | Xuất Excel chạm trần 10.000 dòng thì **cắt im lặng**, tiêu đề vẫn ghi như đủ. | Thấp | Thêm cảnh báo vào dòng tiêu đề khi chạm trần. |
| 8 | JS ghép `attendee_id` thẳng vào `innerHTML`. | Thấp | Dựng bằng DOM API, không ghép chuỗi HTML. |

### 11.4 Báo cáo của agent mà tôi kiểm lại và kết luận là SAI

Ghi lại để sau này không ai đi sửa theo:

- **"`merge()` làm mất mã vì set `lucky_number = null`"** — Sai. Chỉ xảy ra ở nhánh chuyển mã sang
  dòng giữ lại, và **buộc** phải làm vậy vì `UNIQUE(lucky_number)` không cho hai dòng giữ cùng mã.
  Mã không mất, nó nằm trên dòng giữ lại và vẫn được UNIQUE bảo vệ.
- **"Đồng bộ khôi phục dòng mà không bật lại `attendees.is_active`"** — Sai. Nguồn VCK lọc
  `is_active = 1`, nên một dòng chỉ được khôi phục khi attendee đã active sẵn.
- **"View gọi `getData()` không try/catch → API chết là trang vỡ"** — Sai. `ApiDataProvider` bắt lỗi,
  ghi log và trả mảng rỗng, không ném exception. Trang hiện bảng rỗng.
- **"View gọi static method của Model là vi phạm MVC"** — Sai. Quy ước cấm View gọi Model để **lấy
  dữ liệu**; đây là hàm nhãn/option thuần. Chính `CLAUDE.md` làm mẫu `getStatusLabel()` trong View.
- **"`DELETE /destroy/{id}` trả 405"** — Lỗi của bài test, không phải của code. Route là
  `POST destroy/{id}`, đúng quy ước các module khác, và FE gọi đúng POST.

### 11.5 Hai thứ phát hiện thêm, KHÔNG thuộc tính năng này

1. **CSRF tắt trên toàn ứng dụng.** `CHttpRequest::enableCsrfValidation` mặc định `false` và không
   chỗ nào bật. Tôi đã POST thật vào `delete`/`updateField` chỉ với cookie session, **không** token —
   và request được nhận. Nghĩa là mọi POST của **cả admin** (không riêng màn này) có thể bị site thứ
   ba kích hoạt qua trình duyệt của người đang đăng nhập. Nguy hiểm nhất với `delete`.
   Đáng lo thêm: `protected/tests/security/SecurityTest.php::testCsrfProtection` **không kiểm gì
   thật** — nó assert trên hai biến hằng viết sẵn trong hàm, nên vẫn xanh và tạo cảm giác an toàn sai.
   Bật CSRF là việc toàn ứng dụng (phải thêm token vào mọi form), nên tôi **không tự bật**.
2. **Entry point `admin.php` chết.** Nó `require protected/config/admin.php`, file đó không tồn tại →
   fatal error. Mọi thứ đang chạy qua `index.php`. Vô hại hiện tại, nhưng ai mở `admin.php` sẽ thấy
   đường dẫn tuyệt đối của server trong thông báo lỗi.

### 11.6 Rào chắn lớn nhất trước deploy

**Toàn bộ code BE của tính năng chưa được commit.** `git status` còn 11 file `??` và 5 file `M`.
Bảng DB local đã migrate và có dữ liệu, nên ở máy này mọi thứ chạy — nhưng deploy bằng `git pull`
thì server **không có** `FinalAttendeeRosterService` trong khi provider vẫn bind vào nó. Ở FE thì
job auto-commit đã commit sẵn.

### 11.7 Tinh chỉnh giao diện & Cột "Nội dung tham gia" (2026-10-04)

- **Bỏ filter Bộ phận:** Xoá dropdown Bộ phận trên thanh bộ lọc hàng 1; phân bổ lại tỉ lệ lưới: Từ khoá (`col-lg-5 col-md-6`), Đơn vị (`col-lg-5 col-md-6`), Nút tìm kiếm (`col-lg-2 col-md-12`).
- **Filter Đơn vị Select2:** Tích hợp thư viện Select2 trên dropdown Đơn vị (`#filter-property`) với ô tìm kiếm nhanh, nút xoá chọn (`allowClear: true`), styled đồng bộ chuẩn giao diện Hope UI Bootstrap 5. Cập nhật `bindDependentFilters` để reload danh sách Phòng ban theo Đơn vị vừa chọn.
- **Thêm cột "Nội dung tham gia":**
  - **BE:** Batch eager loading 1 SQL query duy nhất cho toàn bộ `final_attendee_contents` của các thí sinh trên trang (`FinalAttendeeRosterRepository.php`), bổ sung accessor `participations` (`type` và `name`) cùng `content_names` vào payload của `FinalAttendeeRosterResource.php`.
  - **FE Model:** Thêm thuộc tính `$participations` và `$content_names` vào [`FinalAttendeeRosters.php`](file:///e:/eventregis/protected/models/FinalAttendeeRosters.php).
  - **FE Table View:** Thêm cột "Nội dung tham gia" sau "Chức danh" trong [`admin.php`](file:///e:/eventregis/protected/modules/admin/views/finalAttendeeRosters/admin.php) với các pill badge màu sắc phong phú, micro-animation hover và icon tương ứng:
    - Thể thao (Emerald / `fa-trophy`)
    - Hội diễn văn nghệ (Purple / `fa-music`)
    - Người đẹp / Miss (Rose / `fa-star`)
    - Hội thi chung (Blue / `fa-flag-checkered`)
    - Vai trò / Ghi chú (Amber / `fa-briefcase` / `fa-user-circle-o`)
  - **Excel Export:** Bổ sung cột "Nội dung tham gia" vào file xuất Excel trong [`FinalAttendeeRostersController.php`](file:///e:/eventregis/protected/modules/admin/controllers/FinalAttendeeRostersController.php).
  - **Empty state:** Cập nhật `colspan` bảng rỗng từ `8 : 7` lên `9 : 8`.

