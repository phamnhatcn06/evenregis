# TODO: Lịch trình cá nhân người tham dự

Spec: `docs/specs/portal-personal-schedule.md` · Plan: `tasks/portal-personal-schedule-plan.md`

## Phase 0: Nền tảng
- [x] 0.1 Model `Schedule` + hằng số item/status + `buildTimeline`/next-up (giờ VN) + unit test (14/14 pass qua harness CLI)
- [x] 0.2 Route (`lich-trinh-cua-toi`) + `PortalController::actionSchedule` + view `schedule.php` + partial `_next_up`/`_timeline` + `schedule-my.js` đếm ngược + `schedule-my.css` + link dashboard

## ⛳ Checkpoint A: Nền tảng xong ✅ (Phương án A đã chốt) — ĐANG DỪNG CHỜ XÁC NHẬN

## Phase 1: Nguồn dữ liệu (vertical slices)
- [x] 1.1 Nguồn Thể thao — BE `/api/schedule/sport` (Sport module) + `Schedule::getSportItems` + ApiEndpoints; verified HTTP + giờ VN round-trip
- [x] 1.2 Nguồn Thi nghiệp vụ — BE `/api/schedule/competition` (Competition module) + `Schedule::getCompetitionItems`; verified HTTP. competitions KHÔNG có event_id (scope theo attendee); không lọc status (chỉ non-deleted); SBD rỗng → subtitle rỗng
- [x] 1.3 Nguồn Agenda chung — BE `/api/schedule/agenda` (Event module), lọc is_public=1; verified HTTP (loại đúng dòng nội bộ). event_agenda KHÔNG có event_id/day_number (endpoint cũ mySchedule tham chiếu sai cột, bỏ qua)
- [x] 1.4 Nguồn Bữa ăn — BE `/api/schedule/meal` (Meal module), leftJoin meal_cutoffs is_cutoff=1 → cờ "Đã báo cắt"; verified HTTP. serving_time là cột DATETIME → combineDateTime lấy date(meal_date)+time(serving_time)
- [x] 1.5 Nguồn Tiệc — BE `/api/schedule/banquet` (Banquet module). PHÁT HIỆN: banquet_tables.event_id là FK→banquet_events.id → join seat→table→banquet_events trực tiếp; verified HTTP
- [x] 1.6 Gộp 5 nguồn — `Schedule::getForAttendee` merge sport+competition+agenda+meal+banquet → buildTimeline; next-up đa nguồn

## ⛳ Checkpoint B: Timeline đầy đủ ✅ (Phase 1 xong)

## Phase 2: Hoàn thiện
- [ ] 2.1 Trạng thái rỗng & lỗi thân thiện
- [ ] 2.2 Badge trạng thái & định dạng giờ VN
- [ ] 2.3 Rà soát tuân thủ CLAUDE.md
