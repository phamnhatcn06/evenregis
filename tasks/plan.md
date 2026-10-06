# Kế hoạch triển khai — Cổng đăng ký Fun Run & Đi tham quan

> Spec: [docs/specs/funrun-tour-registration.md](../docs/specs/funrun-tour-registration.md)
> Ngày: 2026-10-06 · Nhánh đề xuất: `feature/funrun-tour-registration`

## 1. Bối cảnh codebase (đã khảo sát)

**2 repo:**
- **FE Yii** `e:\eventregis` — carrier model (CFormModel) gọi External API; không có DB trực tiếp.
- **BE Laravel** `E:\even_API\MTRegistrationPortal` — module hóa (`Modules/Run/...`), DB thật, trả `['code','message','data']`.

**Module Run hiện có (TÁI DÙNG cho Fun Run):**

| Lớp | FE Yii | BE Laravel |
|-----|--------|------------|
| Auth | `models/RunAuth.php` | `Modules/Run/Services/RunAuthService.php`, `Http/Controllers/RunAuthController.php` |
| Nội dung (suất) | `models/RunEvents.php`, `modules/admin/controllers/RunEventsController.php`, `views/runEvents/*` | `Entities/RunEvent.php`, `Services/RunEventService.php`, `Http/Controllers/RunEventController.php`, migration `...create_run_events_table.php` |
| Đăng ký | `models/RunRegistrations.php`, `modules/frontend/controllers/RunController.php`, `views/run/*`, `modules/admin/controllers/RunRegistrationsController.php`, `views/runRegistrations/admin.php` | `Entities/RunRegistration.php`, `Services/RunRegistrationService.php` (lõi FCFS atomic), `Http/Controllers/RunRegistrationController.php`, migration `...create_run_registrations_table.php` |
| Endpoint | `components/ApiEndpoints.php` dòng 619–635 | `Modules/Run/Routes/api.php` |

**Lõi FCFS (đã verify, `RunRegistrationService::claim`):** `DB::transaction` → `UPDATE run_events SET registered_count+1 WHERE id=? AND deleted_at IS NULL AND status='open' AND registered_count<quota AND open_at<=now AND close_at>=now` → affected!=1 ⇒ phân nhánh 409/422 → INSERT → `catch QueryException` bắt trùng UNIQUE(attendee_id) ⇒ 409. **Nhân bản y nguyên cho Tour.**

**Khác biệt cần thêm so với Run hiện tại:**
1. Bỏ ràng buộc 1-người-1-nội-dung-toàn-cục → giữ riêng từng module (Fun Run 1, Tour 1, độc lập) — vốn đã tách bảng nên tự nhiên đúng.
2. Thêm **luồng hủy** (xin hủy + lý do → admin duyệt → hoàn suất) cho CẢ Run và Tour.
3. Thêm cột **`cancel_until`** (cấp nội dung) + đổi UNIQUE → `(attendee_id, deleted_at)` để đăng ký lại được sau hủy.
4. Fun Run: xóa 21km, seed 5/10/15km.

## 2. Nguyên tắc cắt lát
- Vertical slice: mỗi lát chạy xuyên BE (migration+service+controller+route) → FE (model+controller+view+JS) → verify.
- Risk-first: lõi hủy + UNIQUE mới + hoàn suất là rủi ro cao nhất → làm sớm, có test đồng thời.
- Tour mirror Run: sau khi Run có đủ tính năng hủy, nhân bản sang Tour rẻ hơn.

## 3. Các lát cắt (vertical slices)

### Phase 1 — Nền tảng & điều chỉnh Fun Run

**S1 — Migration cột hủy + cancel_until cho Run (BE)**
- Objective: Run hỗ trợ hủy & chặn-hủy-theo-giờ, cho đăng ký lại sau hủy.
- Files (BE): migration mới `..._add_cancel_columns_to_run_tables.php` — thêm `cancel_until` vào `run_events`; thêm `status`(default 'active'), `cancel_reason`, `cancel_requested_at`, `cancelled_by`, `cancelled_at`, `deleted_at` vào `run_registrations`; đổi index `uq_run_registrations_attendee` → `(attendee_id, deleted_at)`. Cập nhật `Entities/RunEvent.php`, `RunRegistration.php` ($fillable, casts, SoftDeletes/scope).
- AC: migrate up/down chạy sạch; UNIQUE mới cho phép 1 active + nhiều cancelled; `cancel_until` nullable.
- Verify: `php artisan migrate`; thử insert 2 active cùng attendee → lỗi; 1 active + 1 cancelled (deleted_at set) → OK.

**S2 — Seed lại nội dung Fun Run (BE)**
- Objective: bỏ 21km, có 5km(320)/10km(90)/15km(40).
- Files (BE): `Database/Seeders/RunDatabaseSeeder.php` (hoặc migration data) — xóa bản ghi 21km, seed 3 cự ly mới với code prefix BIB.
- AC: `run_events` chỉ còn 3 cự ly đúng quota; không còn 21km.
- Verify: query DB.
- Ask-first khi chạy: xác nhận quota tổng 450 so với số finalist.

**S3 — BE: xin hủy + duyệt hủy + hoàn suất cho Run**
- Objective: API luồng hủy hoàn chỉnh.
- Files (BE): `RunRegistrationService` thêm `requestCancel($attendeeId,$reason)` (check `cancel_until`, set `cancel_requested`), `approveCancel($id,$authEmail)` (transaction: soft-delete + `registered_count-1` atomic, audit), `rejectCancel($id)`; `listCancelRequests($eventId)`. Controller + routes `api.php`: `cancel-request`, `cancel/approve`, `cancel/reject`, `cancel-requests`. Trả HTTP status thật.
- AC: xin hủy sau `cancel_until` → 422; duyệt hủy giảm đúng registered_count trong transaction; sau duyệt đăng ký lại được.
- Verify: unit test service các nhánh; test đồng thời approve + claim slot vừa hoàn.

**S4 — FE Fun Run: hiển thị hủy + đăng ký lại (cổng public)**
- Objective: người dùng xin hủy từ màn kết quả.
- Files (FE): `RunRegistrations.php` thêm `requestCancelViaApi`; `RunController::actionCancelRequest` (AJAX JSON); `views/run/result.php` thêm nút "Xin hủy" (ẩn/hiện theo `cancel_until` truyền từ controller) + modal nhập lý do (tách partial `_modal_cancel.php`); JS `run-result.js` (submit modal có loading, SweetAlert xác nhận, Toast). `ApiEndpoints` thêm hằng cancel Run.
- AC: nút chỉ hiện khi còn trong hạn hủy; gửi lý do → trạng thái "đang chờ duyệt", nút khóa.
- Verify: thủ công trên cổng.

**S5 — FE admin Fun Run: duyệt hủy + cột cancel_until**
- Objective: BTC duyệt/từ chối hủy; cấu hình mốc chặn hủy.
- Files (FE): `runEvents/_form.php` thêm field `cancel_until` (datetime→unix); `RunEvents.php` map `cancel_until`; `RunRegistrationsController` + view: tab/danh sách "Yêu cầu hủy" với nút Duyệt/Từ chối (POST, SweetAlert), hiển thị lý do. `ApiEndpoints` + model methods tương ứng.
- AC: hạ quota < registered_count bị chặn; duyệt hủy → suất hoàn phản ánh ngay.
- Verify: thủ công.

**— Checkpoint A: Fun Run đầy đủ (đăng ký + hủy + hoàn suất + cancel_until) —**
- [ ] FCFS không oversell (test đồng thời)
- [ ] Hủy hoàn suất đúng, đăng ký lại được
- [ ] cancel_until chặn đúng cả FE lẫn BE

### Phase 2 — Module Tham quan (mirror Run)

**S6 — BE: migration + entity tour_sessions & tour_registrations**
- Files (BE): module `Modules/Tour` (hoặc trong Run nếu muốn gọn — nhưng spec chốt tách): migrations tạo 2 bảng theo spec (có `cancel_until`, cột hủy, UNIQUE(attendee_id, deleted_at)); Entities; ServiceProvider/Routes đăng ký module; seeder 3 đợt ×86.
- AC: migrate sạch; seed 3 đợt.
- Verify: query DB.

**S7 — BE: TourSessionService + TourRegistrationService (claim FCFS + hủy)**
- Files (BE): nhân bản lõi từ Run (claim atomic, requestCancel/approveCancel/reject, listByEvent, listCancelRequests); controllers + `Routes/api.php`; audit. Không cấp BIB.
- AC: tương đương S3 cho Tour; không có bib_number.
- Verify: unit test + test đồng thời.

**S8 — FE Tham quan: endpoint + model + cổng public**
- Files (FE): `ApiEndpoints` thêm nhóm `TOUR_*`; models `TourSessions.php`, `TourRegistrations.php` (mirror Run); mở rộng cổng public — thêm khối "Đi tham quan" vào `views/run/index.php` & `result.php` (hiển thị cả 2 nội dung độc lập), hoặc action/route tour trong cùng `RunController`. JS cập nhật. Dùng chung session login `run_attendee_id`.
- AC: 1 người đăng ký được cả Fun Run lẫn Tham quan; mỗi nội dung 1 lựa chọn; xin hủy độc lập.
- Verify: thủ công toàn luồng.

**S9 — FE admin Tham quan: CRUD đợt + danh sách + export + duyệt hủy**
- Files (FE): `admin/controllers/TourSessionsController.php` + views (mirror runEvents); `TourRegistrationsController.php` + view danh sách theo đợt + export PHPExcel (tái dùng helper); tab yêu cầu hủy. Phân quyền: thêm controller vào `MControllers` + `roles.controllers`; cấu hình menu.
- AC: export danh sách theo đợt; phân quyền hiển thị menu.
- Verify: thủ công + kiểm tra quyền.

**— Checkpoint B: Tham quan đầy đủ, 2 module song song —**

### Phase 3 — Hoàn thiện

**S10 — Dashboard mức lấp đầy + rà soát edge case + audit**
- Files: trang/tab thống kê thanh tiến độ mỗi cự ly & đợt; rà double-tab, hết giờ, đóng cổng; đảm bảo audit_logs đủ cho claim & cancel.
- Verify: checklist edge case.

**S11 — Test tải đồng thời FCFS (bắt buộc)**
- Objective: mô phỏng nhiều request claim cùng lúc trên 1 cự ly/đợt sắp đầy.
- AC: tổng đăng ký active ≤ quota tuyệt đối; hoàn suất khi hủy không gây lệch số.
- Verify: script test đồng thời (BE).

## 4. Thứ tự & phụ thuộc
```
S1 → S2 → S3 → S4 → S5 → [Checkpoint A]
                         → S6 → S7 → S8 → S9 → [Checkpoint B]
                                              → S10 → S11
```
S4/S5 (FE) có thể làm song song sau S3. S9 phụ thuộc S7. Test tải S11 cần S3 & S7.

## 5. Rủi ro
- **UNIQUE(attendee_id, deleted_at)**: nhiều hàng cancelled đều NULL ở deleted_at nếu set sai → phải set deleted_at = timestamp thực khi hủy (không để NULL). Test kỹ.
- **Hoàn suất đồng thời**: approveCancel phải `registered_count-1` atomic trong transaction, tránh âm.
- **Tách module Tour**: chi phí nhân bản — chấp nhận theo quyết định user; giữ code mirror để dễ bảo trì.

## 6. Điểm chờ xác nhận (không chặn bắt đầu S1)
- Quota tổng vs số finalist (xác nhận khi seed S2/S6).
- Khi admin từ chối hủy có cần báo lại người dùng (Toast/trạng thái) — xử lý ở S4/S5.
