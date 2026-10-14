# Plan: Lịch trình cá nhân người tham dự trên Portal

> Spec: `docs/specs/portal-personal-schedule.md`
> Nhánh: feature/daihoi-frontend-redesign
> Ngày: 2026-10-14

## Bối cảnh codebase (đã khảo sát)

| Thành phần | Trạng thái | Ghi chú |
|-----------|-----------|---------|
| `AttendeePortalController` | ✅ có | `currentAttendeeId()`, `currentEventId()`, `requireLogin()` |
| `PortalController` (frontend) | ✅ có | login + dashboard `actionIndex`; thêm `actionSchedule` vào đây |
| Route `portal/<action:\w+>` | ✅ có | action mới tự có URL; thêm slug đẹp `lich-trinh-cua-toi` nếu muốn |
| `Daihoi::getTournamentSchedule()` | ✅ có | tham chiếu pattern lấy lịch thể thao công khai |
| ApiEndpoints list endpoints | ✅ có | `SPORT_MATCH_LIST`, `SPORT_TEAM_MEMBER_LIST`, `SPORT_TEAM_MEMBER_COUNT_BY_ATTENDEE`, `COMPETITION_REGISTRATION_LIST`, `MEAL_ATTENDEE_LIST`, `BANQUET_SEAT_LIST` |
| Model `Schedule` | ❌ chưa có | tạo mới — nơi merge/sort/next-up (BE) |

## ⚠️ Quyết định kiến trúc backend cần chốt (RISK — chốt trước Slice 1)

Backend API là **repo riêng** (theo ghi nhớ dự án). "API tách theo nguồn" có 2 cách hiện thực:

- **Phương án A — Endpoint chuyên dụng mới** (`/api/schedule/sport`, `/api/schedule/competition`, ...): BE tự join nhiều bảng, trả item đã chuẩn hóa. Response gọn, ít round-trip. **Cần sửa repo backend.**
- **Phương án B — Tái dùng list endpoint sẵn có**: Model `Schedule` (Yii) gọi các list endpoint hiện có kèm filter param rồi tự join/chuẩn hóa trong PHP. **Không đụng repo backend**, nhưng nhiều round-trip và join phía client (vd: thể thao = lấy team của attendee → lấy match theo team).

→ **Khuyến nghị:** Phương án A cho `sport` + `competition` (join phức tạp), còn `agenda`/`meal`/`banquet` có thể A hoặc B. Plan dưới viết theo **Phương án A**; nếu chọn B thì task "BE endpoint" đổi thành "mở rộng filter cho list endpoint + join trong Model".

---

## Phase 0: Nền tảng dùng chung

### Task 0.1 — Model `Schedule` + hằng số item/status
**Objective:** Khung chuẩn hóa item lịch + logic gộp/sort/chia ngày/next-up (giờ VN).
**Files:**
- `protected/models/Schedule.php` (mới)
**Nội dung:**
- Hằng số loại: `TYPE_SPORT, TYPE_COMPETITION, TYPE_AGENDA, TYPE_MEAL, TYPE_BANQUET`.
- Hằng số trạng thái: `STATUS_UPCOMING, STATUS_ONGOING, STATUS_DONE, STATUS_NO_TIME` + `getStatusLabel()`.
- `normalizeItem($raw, $type)` → `{type,title,subtitle,startTime,endTime,location,status,refId}`.
- `buildTimeline(array $items)` → `{serverTime, nextUp[], days[], noTime[]}`: loại item không giờ ra `noTime`; sort tăng dần; chia nhóm theo ngày (giờ VN `Asia/Ho_Chi_Minh`); tính `status` theo `now` (>= start → ONGOING); `nextUp` = item gần nhất có `startTime >= now`.
- `getForAttendee($attendeeId, $eventId)` — gọi các nguồn (ở các slice sau sẽ nối dần), hiện trả rỗng.
**AC:** AC1.3, AC1.4, AC6.1, AC6.3, AC6.5; giờ VN cố định.
**Verify:** Unit test `buildTimeline` (item no-time bị loại khỏi nextUp; chọn đúng item tương lai gần nhất; item quá start → ONGOING; không còn tương lai → nextUp rỗng).

### Task 0.2 — Route + action + view khung + đếm ngược
**Objective:** Trang `portal/schedule` render được timeline rỗng + card next-up; đồng hồ đếm ngược JS chạy.
**Files:**
- `protected/config/RewriteUrlManager.php` (thêm slug `lich-trinh-cua-toi` → `frontend/portal/schedule`)
- `protected/modules/frontend/controllers/PortalController.php` (`actionSchedule`, `requireLogin`, gọi `Schedule::getForAttendee`)
- `protected/modules/frontend/views/portal/schedule.php` (mới)
- `protected/modules/frontend/views/portal/_next_up.php`, `_timeline.php` (partial)
- `themes/hope-ui/assets/js/pages/schedule-my.js` (mới — đếm ngược đồng bộ `serverTime`)
- link vào `portal/index` dashboard
**AC:** AC1.1, AC1.2, AC6.2, AC6.6; JS tách file; text tiếng Việt.
**Verify thủ công:** đăng nhập → vào trang → thấy khung + đồng hồ (dữ liệu rỗng).

---
## Checkpoint A: Nền tảng xong
- [ ] `buildTimeline` có unit test xanh
- [ ] Trang render, đếm ngược chạy với dữ liệu giả
- [ ] Chốt Phương án A/B cho backend

---

## Phase 1: Các nguồn (mỗi slice đi dọc hết: endpoint → Model → hiển thị)

### Task 1.1 — Nguồn Thể thao (ưu tiên cao nhất, khó nhất)
**Files:** ApiEndpoints `SCHEDULE_SPORT` (A) · `Schedule::getSportItems()` · (BE repo nếu A)
**Logic:** attendee → team(s) → `sport_matches` (team_a/b) → chuẩn hóa (bộ môn, vòng, "A vs B", match_time, location, final_score/status).
**AC:** AC2.1–AC2.4 (đối thủ TBD, nhiều đội không trùng, loại `deleted_at`).
**Verify:** attendee có ≥1 trận hiện đúng; đối thủ null → "Chờ xác định đối thủ".

### Task 1.2 — Nguồn Thi nghiệp vụ
**Files:** ApiEndpoints `SCHEDULE_COMPETITION` · `Schedule::getCompetitionItems()`
**Logic:** `competition_registrations` (attendee, candidate_number) → `competition_rounds` (start/end/location).
**AC:** AC3.1–AC3.3 (hiện số báo danh, từng vòng, chỉ đăng ký hợp lệ).

### Task 1.3 — Nguồn Agenda chung
**Files:** ApiEndpoints `SCHEDULE_AGENDA` · `Schedule::getAgendaItems()`
**Logic:** `event_agenda` `is_public=1` → chuẩn hóa.
**AC:** AC4.1, AC4.2.

### Task 1.4 — Nguồn Bữa ăn (+ cờ báo cắt)
**Files:** ApiEndpoints `SCHEDULE_MEAL` · `Schedule::getMealItems()`
**Logic:** `meal_attendees` → `meals` (ghép `meal_date`+`serving_time`); đối chiếu `meal_cutoffs` → đánh dấu "Đã báo cắt".
**AC:** AC5.1, AC5.2.

### Task 1.5 — Nguồn Tiệc
**Files:** ApiEndpoints `SCHEDULE_BANQUET` · `Schedule::getBanquetItems()`
**Logic:** `banquet_seats` → `banquet_tables` → `banquet_events` (event_time, location, số bàn/ghế).
**AC:** AC5.3.

### Task 1.6 — Gộp tất cả vào `getForAttendee` + next-up đa nguồn
**Objective:** Nối 5 nguồn vào `buildTimeline`; card next-up tính trên toàn bộ.
**AC:** AC6.1, AC6.4 (nhiều item cùng mốc → nhóm), timeline một dòng gộp.

---
## Checkpoint B: Timeline đầy đủ
- [ ] 5 nguồn hiện đúng trong một timeline nhóm theo ngày
- [ ] Next-up đúng item gần nhất xuyên nguồn
- [ ] Item không giờ nằm "Chưa có lịch cụ thể"

---

## Phase 2: Hoàn thiện
### Task 2.1 — Trạng thái rỗng & lỗi thân thiện
Người không có hoạt động → hiện agenda chung + thông báo thân thiện; lỗi API → Toast + không vỡ trang.
### Task 2.2 — Badge trạng thái & định dạng giờ VN cuối
Badge màu theo status constants; format ngày/giờ VN nhất quán; mốc "Đang diễn ra" nổi bật.
### Task 2.3 — Rà soát tuân thủ CLAUDE.md
Không ApiClient ở controller/view; không inline JS; không magic string; route ở RewriteUrlManager; partial modal/timeline.

## Thứ tự & phụ thuộc
0.1 → 0.2 → (Checkpoint A, chốt A/B) → 1.1 → 1.2 → 1.3 → 1.4 → 1.5 → 1.6 → (Checkpoint B) → 2.x
1.1–1.5 độc lập nhau (chỉ phụ thuộc 0.1); làm 1.1 trước vì rủi ro cao nhất.
