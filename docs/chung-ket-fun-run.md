# Fun Run — Cổng đăng ký bộ môn CHẠY cho Finalist Vòng Chung Kết (VCK)

> Tài liệu phân tích nghiệp vụ, kiến trúc, quyết định thiết kế và tiến độ triển khai.
> Cập nhật: 2026-10-02.

---

## 1. Mục tiêu & phạm vi

Mở **một cổng tự phục vụ** để toàn bộ người đã qua Vòng Chung Kết (VCK) tự đăng ký tham gia
bộ môn chạy. Có nhiều nội dung (5km / 10km / 21km, nhóm tuổi nằm trong tên nội dung), mỗi nội
dung có **giới hạn số lượng**, theo cơ chế **ai đăng ký nhanh người đó được** (first-come-first-served, FCFS).

### Trong phạm vi
- Đăng nhập cá nhân (định danh + passcode), cấp số lucky làm định danh & ghép số BIB.
- Hiển thị nội dung còn mở + số suất còn lại, chọn & chiếm slot có kiểm soát cạnh tranh.
- Quản trị: cấu hình nội dung/quota/khung giờ, xem danh sách đăng ký, xuất Excel, cấp số lucky.

### Ngoài phạm vi (giai đoạn này)
- Lịch thi đấu / phân làn / kết quả / chấm giờ.
- Thanh toán phí (không thu phí).
- Mở passcode thành cơ chế đăng nhập **toàn hệ thống** (xem mục 6 — chỉ giới hạn ở cổng chạy).
- Gửi định danh tự động qua email/SMS (sẽ tính sau nếu cần).

---

## 2. Quyết định nghiệp vụ (đã chốt với chủ dự án)

| Vấn đề | Quyết định |
|--------|-----------|
| Số cự ly mỗi người | **1 người chỉ 1 cự ly** |
| Hủy / đổi đăng ký | **Không** (đã đăng ký là chốt) |
| Danh sách chờ (waitlist) | **Không** |
| Phí đăng ký | **Không** |
| Nhóm tuổi | **Nằm trong TÊN nội dung** (không bảng nhóm tuổi; không tự tính từ ngày sinh vì nhiều finalist thiếu `birthday`) |
| Quota | Đặt theo **từng nội dung** (mỗi dòng = cự ly × nhóm tuổi) |
| Nguồn người đủ điều kiện | Finalist VCK = attendee trên phiếu đợt `is_final` của sự kiện |
| Số BIB | **Ghép từ số lucky**: `run_events.code + lucky_number` (vd `5K123456`) |

---

## 3. Đăng nhập & định danh

### Định danh
- Mỗi người được cấp **số lucky** (hệ tự gen, duy nhất) → định danh đăng nhập = `DHMT` + số lucky
  (vd `DHMT123456`). Số lucky cũng dùng để ghép **số BIB**.
- **Giai đoạn hiện tại (chưa có thẻ):** mỗi người dùng định danh `DHMT`+lucky để đăng nhập.
- **Khi sự kiện diễn ra (đã có thẻ):** mỗi người có thẻ tham dự kèm **QR Code** → có thể quét QR
  thay cho việc gõ định danh (xem mục 5).

### Passcode
- **Toàn chữ số, độ dài do người dùng tự đặt** (bỏ phương án OTP vì tốn phí).
- Nên đặt **độ dài tối thiểu** (đề xuất ≥ 4 số) để tránh passcode quá yếu.
- Passcode được **hash** (bcrypt/`Hash::make`), không lưu/không log dạng thô.
- **Rate limit**: sai quá số lần cho phép (mặc định 5) → **khóa tạm 15 phút**.

> **Lưu ý triển khai:** hiện code đang khóa cứng đúng 6 số (`^\d{6}$`). Khi chốt độ dài tối thiểu,
> sửa `RunAuthService::setPin` (regex) và input form (bỏ `pattern="\d{6}"`, `maxlength="6"`).

---

## 4. Cơ chế FCFS & chống oversell (cạnh tranh giành slot)

Rủi ro: nhiều người bấm đăng ký slot cuối gần như đồng thời → nếu "kiểm tra còn slot" rồi mới "ghi"
theo 2 bước tách rời sẽ vượt quá quota.

**Giải pháp đã triển khai:** **atomic conditional UPDATE** ở backend, trong 1 transaction:

```sql
UPDATE run_events
   SET registered_count = registered_count + 1
 WHERE id = ? AND status = 'open'
   AND registered_count < quota
   AND (open_at IS NULL OR open_at <= now)
   AND (close_at IS NULL OR close_at >= now)
```

- `affected rows = 1` ⇒ chiếm slot thành công → tạo phiếu (BIB = code + lucky).
- `affected rows ≠ 1` ⇒ hết chỗ / đóng cổng / ngoài giờ → trả lỗi (409 / 422).
- `UNIQUE(attendee_id)` trên `run_registrations` ⇒ chặn 1 người đăng ký 2 lần (kể cả double-submit song song).
- Toàn bộ nằm trong transaction: nếu insert lỗi thì tăng count tự rollback → không lệch số.

---

## 5. Quét QR Code bằng camera trên web (khi đã có thẻ)

**Khả thi hoàn toàn trên trình duyệt điện thoại, miễn phí, không cần cài app.**

### Các phương án
| Phương án | Mô tả | Đánh giá |
|-----------|-------|----------|
| **html5-qrcode** (khuyến nghị) | Thư viện JS mở camera (`getUserMedia`) + decode QR trong trình duyệt. Tải file về `themes/hope-ui/assets/` (đúng rule không CDN). | ✅ Tốt nhất, chạy đa số trình duyệt mobile |
| **BarcodeDetector API** | API native (Chrome Android), nhanh | Hỗ trợ hạn chế → làm fast-path + fallback |
| **jsQR** | Tự vẽ video + decode từng frame | Nhẹ nhưng phải tự code nhiều hơn |

### Luồng
1. Người dùng bấm nút **"Quét QR thẻ"** trên trang đăng nhập.
2. Trình duyệt xin quyền camera (lần đầu) → mở khung quét.
3. Đưa QR thẻ vào khung → đọc được `qr_token`.
4. Gọi BE resolve attendee theo `qr_token` → vào bước nhập passcode.

### Điều kiện bắt buộc
- **PHẢI chạy HTTPS** — `getUserMedia` chỉ hoạt động ở secure context (https hoặc `localhost`).
  Nếu cổng chạy http thuần, nút camera sẽ bị trình duyệt chặn.
- QR trên thẻ dùng `qr_token` — **đã có sẵn** trên bảng `attendees` (trang `frontend/attendee/view?token=`
  đang dùng), không cần sinh thêm dữ liệu.

### Việc cần làm khi triển khai QR (chưa làm)
- (FE) Thêm thư viện quét QR (local) + nút "Quét QR thẻ" ở trang login.
- (BE) Thêm endpoint `run-auth/identify-by-qr` resolve attendee theo `qr_token`.

---

## 6. Bảo mật — khuyến nghị giữ nguyên

- Passcode **chỉ dùng cho cổng chạy**, **KHÔNG** mở thành đăng nhập toàn hệ thống. Khi cần "một tài
  khoản cá nhân cho toàn hệ thống" trong tương lai → thiết kế riêng như dự án định danh (magic link /
  tích hợp SSO Portal), không dùng passcode số đơn độc.
- Passcode hash + rate limit + khóa tạm (đã có).
- Định danh (`DHMT`+lucky) nên phân phát có kiểm soát; khi có thẻ thì ưu tiên quét QR.

---

## 7. Kiến trúc & vị trí code

**Hai repo** (xem thêm tài liệu VCK):
- **Backend Laravel** `E:\even_API\MTRegistrationPortal` (DB `lrv_even`) — module nwidart mới **`Run`**.
- **Frontend Yii 1.x** `e:\eventregis` — gọi BE qua `ApiClient` + `ApiEndpoints` (không có DB trực tiếp).

### Backend — module `Run` (ĐÃ XONG, verified)
- **Migrations:** `run_events` (name, code, quota, registered_count, open_at/close_at unix, status, softDeletes);
  `run_registrations` (run_event_id, attendee_id **UNIQUE**, bib_number, age_group_label, registered_at);
  cột mới trên `attendees`: `lucky_number` (unique), `login_pin` (hash), `pin_set_at`,
  `login_failed_attempts`, `login_locked_until`.
- **Services:** `RunEventService` (CRUD + listOpen + chặn hạ quota < đã đăng ký);
  `RunRegistrationService::claim` (lõi FCFS atomic); `RunAuthService` (identify/setPin/login + lockout + provisionLucky).
- **Command:** `run:gen-lucky {event_id}`; **endpoint** `run-auth/gen-lucky` (cho nút admin).
- **Routes** (prefix `api`, middleware `auth.token` = API key): `run-events` CRUD + `list-open`;
  `run-registrations/claim|mine|` (index); `run-auth/identify|set-pin|login|gen-lucky`.
- **Quan trọng:** controller claim/auth trả **HTTP status thật** (200/409/422/401/429) vì base
  `handleResponse` luôn trả HTTP 200 (code nằm trong body); FE `ApiClient.success` dựa trên HTTP code.

### Frontend — Yii (ĐÃ XONG, lint sạch)
- **Models:** `RunEvents`, `RunRegistrations`, `RunAuth` (đều `CFormModel` — FE không có DB).
- **ApiEndpoints:** nhóm `RUN_*`.
- **Admin:** `RunEventsController` + views `runEvents/{admin,create,update,view,_form}.php` (CRUD nội dung);
  `RunRegistrationsController` + view `runRegistrations/admin.php` (danh sách theo sự kiện + xuất Excel + nút cấp số lucky).
- **Cổng public:** `modules/frontend/controllers/RunController.php` (layout `//layouts/frontend`, session
  `run_attendee_id/run_full_name/run_event_id`): login nhiều bước (identify → đặt PIN | nhập PIN),
  index (nếu đã đăng ký → phiếu + BIB; chưa → danh sách nội dung), register (AJAX), logout.
  Views `frontend/views/run/{login,index,result}.php`. JS `themes/hope-ui/assets/js/pages/run-portal.js`.
  URL: `/run`, `/run/<action>`.

---

## 8. Tiến độ

| Hạng mục | Trạng thái |
|----------|-----------|
| BE: schema + CRUD nội dung + FCFS claim + auth + gen-lucky | ✅ Xong, verified (tinker + HTTP) |
| FE: models + admin CRUD + admin danh sách/Excel/gen-lucky | ✅ Xong, lint sạch |
| FE: cổng public login/đăng ký/kết quả | ✅ Xong, verified HTTP end-to-end |
| Passcode toàn số, **tối thiểu 6 số**, độ dài tự do | ✅ Xong (BE regex `^\d{6,}$`, form `minlength=6`) |
| Nút quét QR thẻ + endpoint `run-auth/identify-by-qr` | ✅ Xong, verified HTTP (thư viện html5-qrcode local, trích token từ URL/chuỗi thuần) |
| Gửi định danh cho người tham dự | ⏳ Ngoài phạm vi hiện tại |

### Thao tác cấu hình phía người dùng
1. Chạy `run:gen-lucky <event_id>` (hoặc nút "Cấp số lucky" ở admin) sau khi chốt danh sách VCK, rồi phân phát định danh.
2. Thêm controller `runevents` / `runregistrations` vào bảng `MControllers` + `roles.controllers`
   để hiện menu sidebar và cấp quyền create/update/delete (các action đọc đã mở sẵn; admin toàn
   quyền `*` vào được ngay qua URL `/admin/runEvents/admin`).

---

## 9. Kiểm thử đã thực hiện (2026-10-02)

- **Tinker (service layer):** claim không oversell (quota=2, 3 người → 2 thành công + 1 báo hết chỗ);
  chặn đăng ký lần 2; auth set-pin/login đúng-sai + khóa sau 5 lần sai.
- **HTTP end-to-end:** identify 200 → set-pin 200 → login sai 401 → login đúng 200 → claim 200 (BIB) →
  claim lại 409 → list-open phản ánh remaining/is_full. Dữ liệu test đã dọn sạch.

### Gotcha chạy BE dev server để test
`artisan serve` spawn worker php KHÔNG kèm cờ extension → lỗi 500 "could not find driver". Chạy trực tiếp:

```
/c/MAMP/bin/php/php8.1.0/php.exe -d extension_dir=/c/MAMP/bin/php/php8.1.0/ext \
  -d extension=pdo_mysql -d extension=mysqli -S 127.0.0.1:8000 server.php
```

`pkill -f` không kill được process Windows — dùng `taskkill //F //IM php.exe`.
