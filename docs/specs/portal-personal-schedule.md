# Feature: Lịch trình cá nhân người tham dự trên Portal

> Trạng thái: Draft — chờ phê duyệt trước khi `/plan`
> Ngày tạo: 2026-10-14
> Nhánh: feature/daihoi-frontend-redesign

## Objective

Mỗi người tham dự sau khi đăng nhập Portal cá nhân nhìn thấy **một timeline duy nhất** gồm tất cả hoạt động liên quan đến mình (trận thể thao, vòng thi nghiệp vụ, nghi thức/agenda, bữa ăn, tiệc), và một thẻ **"Sự kiện tiếp theo"** nổi bật có đếm ngược thời gian thực — để không bỏ lỡ bất kỳ lịch nào.

## Target Users

- **Người tham dự (attendee)** đã đăng nhập Portal cá nhân qua session (`attendee_id` + `event_id`) bằng DHMT+lucky+PIN hoặc quét QR token.
- Đây là người thi đấu/thí sinh/khách mời, cần biết **khi nào, ở đâu, làm gì** trong đại hội.

## Core Features

### F1 — Timeline lịch trình cá nhân
Hiển thị toàn bộ item lịch của attendee, nhóm theo ngày, sắp xếp tăng dần theo thời gian.
- **AC1.1** Chỉ hiển thị item liên quan tới `currentAttendeeId()` trong `currentEventId()` của phiên đăng nhập.
- **AC1.2** Mỗi item hiện: loại (badge), tiêu đề, phụ đề, thời gian (giờ VN), địa điểm, trạng thái.
- **AC1.3** Nhóm theo ngày; mỗi ngày có header ngày (giờ VN).
- **AC1.4** Item **chưa có thời gian** (null/0) gom vào mục riêng "Chưa có lịch cụ thể", không tham gia sắp xếp theo giờ.
- **AC1.5** Loại trừ bản ghi `deleted_at` ở mọi bảng nguồn.

### F2 — Nguồn: Trận thể thao
`attendees` → `sport_team_members.attendee_id` → `sport_teams` → `sport_matches` (team_a_id HOẶC team_b_id = đội của tôi).
- **AC2.1** Hiển thị: bộ môn (`sports.name`), vòng/giai đoạn, "Đội A vs Đội B", thời gian (`match_time`), sân (`location`).
- **AC2.2** Đối thủ chưa xác định (team null) → hiển thị "Chờ xác định đối thủ"; vẫn hiện nếu đã có `match_time`.
- **AC2.3** Trận đã có `final_score`/status xong → trạng thái "Đã diễn ra" + tỉ số.
- **AC2.4** Người thuộc nhiều đội → gộp tất cả, không trùng lặp trận.

### F3 — Nguồn: Thi nghiệp vụ
`competition_registrations.attendee_id` → `competitions` → `competition_rounds`.
- **AC3.1** Hiển thị số báo danh (`candidate_number`) của attendee.
- **AC3.2** Liệt kê từng vòng của cuộc thi mình đăng ký: tên vòng, `start_time`/`end_time`, `location`.
- **AC3.3** Chỉ lấy đăng ký có trạng thái hợp lệ (đã xác nhận); loại `deleted_at`.

### F4 — Nguồn: Agenda chung
`event_agenda` với `is_public = 1`.
- **AC4.1** Hiển thị các mục nghi thức/phiên chung: tiêu đề, `start_time`/`end_time`, `location`, loại.
- **AC4.2** Agenda áp dụng cho mọi người (không lọc theo attendee).

### F5 — Nguồn: Bữa ăn & Tiệc
- Bữa ăn: `meal_attendees.attendee_id` → `meals` (ghép `meal_date` + `serving_time`).
  - **AC5.1** Hiển thị tên bữa, thời gian phục vụ, địa điểm.
  - **AC5.2** Nếu attendee đã báo cắt ăn (`meal_cutoffs`) cho bữa đó → đánh dấu "Đã báo cắt" hoặc ẩn (chốt khi plan).
- Tiệc: `banquet_seats.attendee_id` → `banquet_tables` → `banquet_events` (`event_time`, `location`).
  - **AC5.3** Hiển thị tên tiệc, thời gian, địa điểm, số bàn/ghế được phân.

### F6 — Thẻ "Sự kiện tiếp theo" + đếm ngược realtime
- **AC6.1** Gom mọi item có thời gian hợp lệ, lọc `startTime >= now()`, sort tăng dần, lấy item gần nhất.
- **AC6.2** Hiển thị nổi bật: loại, tiêu đề, thời gian, địa điểm + **đồng hồ đếm ngược chạy realtime** (JS).
- **AC6.3** Item có `startTime <= now()` được coi là **"Đang diễn ra"** (tính từ đúng `start_time`, không ngưỡng) — không còn là "tiếp theo".
- **AC6.4** Nhiều item cùng mốc gần nhất → hiển thị nhóm.
- **AC6.5** Không còn item tương lai → "Đã hoàn thành lịch trình".
- **AC6.6** Đếm ngược đồng bộ theo `serverTime` do API trả về (tránh lệch giờ thiết bị); toàn bộ theo Asia/Ho_Chi_Minh.

## Out of Scope (phiên này)

- Góc nhìn **đơn vị** (unit_account xem lịch cả đoàn) và **trưởng đoàn**.
- Thi **sắc đẹp** và **văn nghệ** trong timeline.
- Thông báo push / nhắc lịch qua email.
- Cờ "lịch vừa thay đổi" / lịch sử thay đổi.
- Phát hiện & cảnh báo **xung đột lịch** (2 item cùng giờ) — ghi nhận là nice-to-have, không bắt buộc.

## Technical Approach

### Kiến trúc dữ liệu
- Mọi nguồn chuẩn hóa về **item lịch chung**:
  ```
  { type, title, subtitle, startTime (unix|null), endTime, location, status, refId }
  ```
  `type` ∈ {sport, competition, agenda, meal, banquet}. `status` dùng hằng số trong model (Sắp tới / Đang diễn ra / Đã diễn ra / Chưa có giờ), KHÔNG magic string.
- Thời gian lưu Unix timestamp (INT). Format sang giờ VN ở tầng hiển thị; cố định `Asia/Ho_Chi_Minh`.

### Luồng MVC (tuân CLAUDE.md)
- **Model** `Schedule` (component/model mới): `getForAttendee($attendeeId, $eventId)` trả cấu trúc đã merge + sort; `getNextUp(...)` (hoặc tính trong cùng method). Mọi truy cập dữ liệu qua `ApiClient` + `ApiEndpoints` constants — KHÔNG gọi ApiClient trong controller/view.
- **Controller** `frontend/PortalController::actionSchedule()` — `requireLogin()`, gọi model, truyền data xuống view.
- **View** `portal/schedule.php` — chỉ hiển thị; tách partial `_next_up.php` và `_timeline.php` nếu dài.
- **Route**: thêm vào `protected/config/RewriteUrlManager.php` (đã có `portal/<action:\w+>` → có thể đủ; xác nhận khi plan).

### API contract (dự kiến — khai báo trong ApiEndpoints.php)
- `SCHEDULE_MY = '/api/schedule/my'` (param: `attendee_id`, `event_id`).
- Response:
  ```json
  {
    "success": true,
    "data": {
      "serverTime": 1760000000,
      "nextUp": [ { "type":"sport", "title":"...", "startTime":..., "location":"..." } ],
      "days": [ { "date":"2026-11-01", "items":[ {...} ] } ],
      "noTime": [ {...} ]
    }
  }
  ```
  Field body camelCase; thời gian trả cả Unix lẫn chuỗi đã format giờ VN.

### Tích hợp hệ thống hiện có
- Dùng `AttendeePortalController`: `currentAttendeeId()`, `currentEventId()`, `requireLogin()`.
- Thêm link vào dashboard `portal/index` (đã liệt kê mục "lịch thi đấu, lịch thi nghiệp vụ").
- Đếm ngược: file JS riêng `themes/hope-ui/assets/js/pages/schedule-my.js`, register qua `clientScript`; KHÔNG inline JS.

## Code Style
- Tuân toàn bộ `.claude/rules/`.
- Text hiển thị: tiếng Việt có dấu.
- Status: hằng số trong model; Toast cho thông báo; không CDN.

## Testing Strategy
- **Unit** (logic chuẩn hóa & next-up): item không có giờ bị loại khỏi next-up; chọn đúng item gần nhất tương lai; item quá khứ ở đúng thời điểm → "Đang diễn ra"; gộp nhiều đội không trùng trận; loại `deleted_at`.
- **Integration**: endpoint `SCHEDULE_MY` trả đúng cấu trúc cho attendee có/không có hoạt động.
- **E2E (thủ công)**: đăng nhập portal → xem timeline → card next-up đếm ngược đúng → mốc đến giờ chuyển "Đang diễn ra".

## Boundaries

### Always Do
- Lọc scope theo session `attendee_id` + `event_id`.
- Loại trừ `deleted_at` ở mọi nguồn.
- Giờ VN cố định Asia/Ho_Chi_Minh.
- Truy cập dữ liệu qua Model → ApiClient → ApiEndpoints.

### Quyết định đã chốt
- Bữa ăn đã báo cắt (`meal_cutoffs`) → **đánh dấu "Đã báo cắt"** (vẫn hiển thị, không ẩn).
- **Gộp tất cả nguồn vào MỘT timeline** chung (không tách tab).
- API **tách theo nguồn** (mỗi nguồn 1 endpoint); Model `Schedule` gọi song song các nguồn rồi merge + sort ở tầng Model (BE), view nhận 1 cấu trúc đã gộp.

### Never Do
- Không dùng ID nội bộ trong URL public.
- Không inline JS trong view.
- Không gọi ApiClient trực tiếp ở controller/view.
- Không magic number/string cho status.
