# Feature: Cổng đăng ký Fun Run & Đi tham quan (danh sách finalist VCK)

> Phiên bản: 1.0 — Ngày: 2026-10-06
> Trạng thái: Chờ phê duyệt

## Objective

Cho những người trong danh sách Vòng Chung Kết (VCK) tự đăng ký online 2 hoạt động phụ trợ — **Chạy bộ (Fun Run)** và **Đi tham quan** — theo cơ chế "ai đăng ký trước được trước" (FCFS) với số suất giới hạn, đăng ký xong thì chốt không cho người dùng tự sửa.

## Target Users

| Actor | Vai trò |
|-------|---------|
| **Finalist VCK** | Người dùng cổng công khai, tự đăng ký. Đăng nhập bằng DHMT + lucky number + PIN (hoặc quét QR thẻ) — luồng hiện có. |
| **Admin HO / BTC** | Quản lý danh mục suất, mở/đóng cổng, xem & export danh sách, sửa/hủy ca đặc biệt. |

## Core Features

### F1 — Đăng nhập cổng (tái dùng 100%)
Finalist đăng nhập qua `RunAuth` hiện có: identify (DHMT+lucky) → đặt PIN lần đầu / nhập PIN, hoặc quét QR thẻ.
**AC:** Sai PIN 5 lần → khóa 15 phút (429). Lần đầu buộc đặt PIN. Thành công → tạo session → vào cổng.

### F2 — Đăng ký Fun Run (1 trong 3 cự ly)
Hiển thị 3 cự ly với suất còn lại realtime: **5km (320)**, **10km (90)**, **15km (40)**. Chọn 1.
**AC:**
- Mỗi finalist chỉ đăng ký **1 cự ly** (UNIQUE theo attendee trong bảng đăng ký Fun Run).
- Đăng ký thành công → cấp **BIB = code cự ly + lucky_number** → khóa, không đổi được.
- Hết suất → card disable, hiện "Hết chỗ" (409). Ngoài giờ mở → 422.

### F3 — Đăng ký Tham quan (1 trong 3 đợt)
Hiển thị 3 đợt cùng địa điểm, khác khung giờ, mỗi đợt **86 suất**. Chọn 1.
**AC:**
- Mỗi finalist chỉ đăng ký **1 đợt** (UNIQUE theo attendee trong bảng đăng ký Tham quan).
- **Không cấp BIB/vé**, chỉ ghi nhận đợt.
- Độc lập với Fun Run: 1 người được đăng ký cả Fun Run lẫn Tham quan.

### F4 — Màn kết quả / đã đăng ký + Xin hủy
Hiển thị nội dung đã đăng ký: Fun Run (cự ly + BIB), Tham quan (đợt + khung giờ). Cho đăng ký nốt nội dung còn lại nếu chưa.
**AC:**
- Người dùng **không tự sửa** lựa chọn (không đổi cự ly/đợt trực tiếp).
- Nút **"Xin hủy đăng ký"** chỉ hiển thị khi **hiện tại ≤ `cancel_until`** (mốc chặn hủy, set ở cấp nội dung — F5). Quá mốc này nút ẩn, không cho xin hủy. Nếu `cancel_until` không set → mặc định theo cửa sổ đăng ký đang mở.
- Click → bắt buộc nhập **lý do hủy** → gửi yêu cầu. Trạng thái đăng ký chuyển `cancel_requested`, hiển thị "Đang chờ duyệt hủy", nút bị khóa.
- Chưa được admin duyệt thì **chưa hoàn suất** và **chưa được đăng ký lại**.

### F5 — Admin: quản lý danh mục suất
CRUD cự ly Fun Run và đợt Tham quan: tên, quota, trạng thái, cửa sổ thời gian.
**AC:**
- Cửa sổ `open_at/close_at` set ở **cấp nội dung** (1 mốc chung cho toàn Fun Run, 1 cho toàn Tham quan) — không set riêng từng cự ly/đợt.
- **`cancel_until`** (mốc chặn hủy) cũng set ở cấp nội dung: sau mốc này không cho người dùng xin hủy nữa. Phải nằm trong khoảng cửa sổ đăng ký.
- Chặn hạ quota xuống dưới số đã đăng ký (`registered_count`).

### F6 — Admin: danh sách & export
Bảng người đăng ký theo từng cự ly / đợt, lọc, export Excel (PHPExcel). Tham quan xuất danh sách theo đợt.
**AC:** Cột gồm BIB (Fun Run), đơn vị, SĐT, thời điểm đăng ký.

### F7 — Admin: duyệt yêu cầu hủy
BTC xem danh sách các yêu cầu hủy (`cancel_requested`) kèm lý do → **xác nhận hủy** hoặc **từ chối**.
**AC:**
- Chỉ admin có quyền; ghi audit log (ai duyệt, thời điểm, lý do).
- Xác nhận hủy → đăng ký chuyển `cancelled` (soft-delete bản ghi: set `deleted_at`, lưu `cancel_reason`, `cancelled_by`, `cancelled_at`) và **hoàn suất** (giảm `registered_count` của cự ly/đợt tương ứng, trong transaction) → suất mở lại cho người khác.
- Sau khi hủy, finalist đó được đăng ký lại nội dung đó (vì bản ghi active cũ đã soft-delete, UNIQUE không còn chặn).
- Từ chối → quay về `active`, nút khóa lại như cũ.
- **Thông báo cho người đăng ký ở cả 2 chiều** (duyệt hủy / từ chối hủy): hiển thị trạng thái + kết quả trên màn kết quả của người dùng khi họ đăng nhập lại (Toast/nhãn trạng thái + lý do nếu có). Lưu `cancel_reviewed_at` để phân biệt đã xử lý.
- Nếu **đã qua đợt đăng ký** (cổng đóng) mà vẫn hoàn suất: BTC có thể mở lại một đợt đăng ký mới (điều chỉnh `open_at/close_at` ở F5) để suất hoàn được dùng tiếp.

### F8 — Dashboard mức lấp đầy (nên có)
Thanh tiến độ đã đăng ký / tổng cho mỗi cự ly & đợt.

## Out of Scope

- Tổng quát hóa 2 nội dung thành 1 cơ chế chung → **đã chốt tách 2 module riêng**.
- Cự ly 21km cũ → **loại bỏ**, thay bằng 5/10/15km.
- Vé/mã check-in cho Tham quan.
- Cho người dùng tự sửa/hủy đăng ký.
- Thanh toán, danh sách chờ (waitlist), chuyển nhượng suất.

## Technical Approach

### Kiến trúc: 2 module tách riêng
- **Fun Run** = tái dùng module Run sẵn có (FE `RunController`, models `RunAuth`/`RunRegistrations`/`RunEvents`, admin `RunEventsController`/`RunRegistrationsController`; BE Laravel module `Run`, lõi `RunRegistrationService::claim`). Chỉ thay dữ liệu cự ly (bỏ 21km, seed 5/10/15km).
- **Tham quan** = module mới song song mirror Run: `tour_sessions` + `tour_registrations`, controller/model/view tương tự. Tái dùng `RunAuth` cho đăng nhập (chung session finalist).

### Data models (BE Laravel, quy ước CLAUDE.md: snake_case, unix timestamp INT UNSIGNED, soft delete danh mục)

**`run_events`** (điều chỉnh): **XÓA hẳn** bản ghi 21km (chưa từng mở đăng ký nên không có dữ liệu cần giữ); seed 5km(320)/10km(90)/15km(40). `open_at/close_at` dùng chung cấp nội dung. Thêm cột **`cancel_until`** INT UNSIGNED NULL (mốc chặn xin hủy).

**`run_registrations`** (bổ sung cột cho luồng hủy): thêm `status` (active/cancel_requested/cancelled), `cancel_reason` VARCHAR NULL, `cancel_requested_at` INT NULL, `cancelled_by` VARCHAR NULL, `cancelled_at` INT NULL, `deleted_at` INT UNSIGNED NULL. Đổi UNIQUE sang `UNIQUE(attendee_id, deleted_at)` (xem ghi chú UNIQUE bên dưới).

**`tour_sessions`** (mới):
| Cột | Kiểu | Ghi chú |
|-----|------|---------|
| id | BIGINT PK | |
| event_id | BIGINT | FK sự kiện VCK |
| name | VARCHAR(255) | "Tham quan đợt 1" |
| start_time | VARCHAR / INT | khung giờ |
| quota | INT UNSIGNED | 86 |
| registered_count | INT UNSIGNED | default 0 |
| open_at / close_at | INT UNSIGNED | cấp nội dung (chung 3 đợt) |
| cancel_until | INT UNSIGNED NULL | mốc chặn xin hủy (cấp nội dung) |
| status | VARCHAR | open/closed |
| sort_order, is_active | INT | |
| created_at/updated_at/deleted_at | INT UNSIGNED | soft delete |

**`tour_registrations`** (mới):
| Cột | Kiểu | Ghi chú |
|-----|------|---------|
| id | BIGINT PK | |
| tour_session_id | BIGINT | FK tour_sessions |
| attendee_id | BIGINT | FK attendees |
| status | VARCHAR(20) | active / cancel_requested / cancelled |
| cancel_reason | VARCHAR(255) NULL | lý do người dùng xin hủy |
| cancel_requested_at | INT UNSIGNED NULL | |
| cancelled_by | VARCHAR NULL | email admin duyệt hủy |
| cancelled_at | INT UNSIGNED NULL | |
| registered_at | INT UNSIGNED | |
| created_at/updated_at | INT UNSIGNED | |
| deleted_at | INT UNSIGNED NULL | set khi hủy được duyệt |

Ràng buộc: `UNIQUE(attendee_id, deleted_at)` tên `uq_tour_registrations_attendee` (1 bản ghi active/người); FK `fk_tour_registrations_session`, `fk_tour_registrations_attendee`.

**Ghi chú UNIQUE + cho đăng ký lại sau hủy:** dùng cặp `(attendee_id, deleted_at)` — bản ghi active có `deleted_at = NULL` nên 2 bản active của cùng người sẽ trùng (chặn đăng ký 2 lần). Khi hủy được duyệt, set `deleted_at = timestamp` → bản ghi cũ hết trùng → finalist được tạo bản active mới. *(Lưu ý MySQL: nhiều NULL trong cột UNIQUE không bị coi là trùng, nên pattern này chỉ enforce "1 active" khi có đúng 1 hàng NULL — là mong muốn ở đây. Áp dụng tương tự cho `run_registrations`.)*

### API contracts (ApiEndpoints + Model methods, controller trả HTTP status thật)
- Fun Run: tái dùng `RUN_EVENT_*`, `RUN_REGISTRATION_*`, `RUN_AUTH_*` + thêm `RUN_REGISTRATION_CANCEL_REQUEST` (người dùng xin hủy), `RUN_REGISTRATION_CANCEL_APPROVE`/`CANCEL_REJECT` (admin), `RUN_REGISTRATION_CANCEL_LIST` (danh sách chờ duyệt).
- Tham quan (mới): `TOUR_SESSION_LIST/LIST_OPEN/STORE/DETAIL/UPDATE/DESTROY`, `TOUR_REGISTRATION_CLAIM/MINE/LIST`, `TOUR_REGISTRATION_CANCEL_REQUEST/CANCEL_APPROVE/CANCEL_REJECT/CANCEL_LIST`.
- Mã HTTP: 200 OK, 409 hết chỗ / đã đăng ký nội dung đó, 422 ngoài giờ/đóng cổng, 401 PIN sai, 429 khóa.
- Xin hủy: người dùng gửi `attendee_id` + `reason` → BE kiểm tra **hiện tại ≤ `cancel_until`** (quá mốc → 422), rồi đánh dấu `cancel_requested` (chưa hoàn suất). Admin approve → transaction: soft-delete bản ghi + `registered_count - 1`.

### FCFS an toàn (điểm sống còn)
Atomic update trong transaction, KHÔNG SELECT-rồi-UPDATE:
```sql
UPDATE tour_sessions
   SET registered_count = registered_count + 1
 WHERE id = ? AND status='open' AND registered_count < quota
   AND (open_at IS NULL OR now>=open_at) AND (close_at IS NULL OR now<=close_at);
-- affectedRows != 1 → rollback → 409/422
-- affectedRows == 1 → INSERT tour_registrations (UNIQUE chặn double-submit)
```

### Integration points
- `attendees.lucky_number` (UNIQUE toàn bảng) — nguồn danh tính + BIB; không phá, kiểm tra trùng phải `withTrashed()`.
- `final_attendee_rosters` — nguồn danh sách finalist đủ điều kiện.
- Phân quyền: thêm controller tour vào `MControllers` + `roles.controllers`.

## Code Style

- Tuân thủ toàn bộ `.claude/rules/` và CLAUDE.md.
- Text tiếng Việt có dấu; không CDN; JS tách file `{controller}-{action}.js`; modal tách partial; Toast thay alert; SweetAlert xác nhận claim; modal submit có loading state.
- Model gọi qua ApiClient/ApiEndpoints; Controller không gọi ApiClient trực tiếp; View không gọi Model.
- `created_by`/các trường actor lấy từ `AuthHandler::getUser()`; status dùng constants.

## Testing Strategy

- **Unit (BE):** `TourRegistrationService::claim` — các nhánh: thành công, hết chỗ (409), ngoài giờ (422), double-submit (UNIQUE/409).
- **Integration:** endpoint claim trả đúng HTTP status; list-open lọc đúng; admin hạ quota < registered_count bị chặn.
- **Tải/đồng thời (bắt buộc):** mô phỏng nhiều request claim cùng lúc trên 1 đợt sắp đầy → tổng đăng ký không bao giờ > quota.
- **E2E:** login finalist → đăng ký Fun Run → đăng ký Tham quan → màn kết quả khóa; đăng ký lại bị chặn.
- **E2E hủy:** xin hủy + lý do → trạng thái `cancel_requested`, suất chưa hoàn → admin duyệt → suất hoàn (registered_count -1) → finalist đăng ký lại được; admin từ chối → quay về `active`.

## Boundaries

### Always Do
- Dùng atomic update có điều kiện trong transaction cho mọi claim (chống vượt quota).
- Giữ UNIQUE(attendee_id) mỗi bảng đăng ký.
- Controller trả HTTP status thật để FE phân biệt lỗi.
- Ghi audit_logs cho claim và cho thao tác sửa/hủy của BTC.

### Đã chốt
- **Hủy CÓ hoàn suất** (giảm registered_count trong transaction). Quy trình: người dùng xin hủy + lý do → admin duyệt → hoàn suất. Nếu đã qua đợt, BTC mở lại đợt đăng ký mới để dùng suất hoàn.
- **Xóa hẳn** cự ly 21km (chưa mở đăng ký, không có dữ liệu cần giữ).

### Ask First
- Tổng suất Fun Run 450 và Tham quan 258 so với số finalist thực tế — nếu thiếu suất là đúng ý đồ FCFS (xác nhận khi seed).
- Khi admin **từ chối** yêu cầu hủy, có cần thông báo lại cho người dùng (hiển thị trạng thái/Toast) không.

### Never Do
- Không mở endpoint sửa/hủy cho người dùng cuối.
- Không SELECT-rồi-UPDATE khi trừ suất.
- Không phá UNIQUE `lucky_number`; không dùng CDN; không inline JS trong view.

## Next Step
Sau khi phê duyệt spec → chạy `/plan` để phân rã thành các vertical slice có thứ tự.
