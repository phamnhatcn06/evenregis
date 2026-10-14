# TODO: Lịch trình cá nhân người tham dự

Spec: `docs/specs/portal-personal-schedule.md` · Plan: `tasks/portal-personal-schedule-plan.md`

## Phase 0: Nền tảng
- [x] 0.1 Model `Schedule` + hằng số item/status + `buildTimeline`/next-up (giờ VN) + unit test (14/14 pass qua harness CLI)
- [x] 0.2 Route (`lich-trinh-cua-toi`) + `PortalController::actionSchedule` + view `schedule.php` + partial `_next_up`/`_timeline` + `schedule-my.js` đếm ngược + `schedule-my.css` + link dashboard

## ⛳ Checkpoint A: Nền tảng xong ✅ (Phương án A đã chốt) — ĐANG DỪNG CHỜ XÁC NHẬN

## Phase 1: Nguồn dữ liệu (vertical slices)
- [ ] 1.1 Nguồn Thể thao (ưu tiên nhất)
- [ ] 1.2 Nguồn Thi nghiệp vụ (+ số báo danh)
- [ ] 1.3 Nguồn Agenda chung (is_public=1)
- [ ] 1.4 Nguồn Bữa ăn (+ cờ "Đã báo cắt")
- [ ] 1.5 Nguồn Tiệc
- [ ] 1.6 Gộp 5 nguồn + next-up đa nguồn

## ⛳ Checkpoint B: Timeline đầy đủ

## Phase 2: Hoàn thiện
- [ ] 2.1 Trạng thái rỗng & lỗi thân thiện
- [ ] 2.2 Badge trạng thái & định dạng giờ VN
- [ ] 2.3 Rà soát tuân thủ CLAUDE.md
