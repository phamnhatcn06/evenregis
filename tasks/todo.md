# TODO — Cổng đăng ký Fun Run & Đi tham quan

> Chi tiết: [tasks/plan.md](plan.md) · Spec: [docs/specs/funrun-tour-registration.md](../docs/specs/funrun-tour-registration.md)

## Phase 1 — Nền tảng & Fun Run
- [x] S1 — BE: migration cột hủy + `cancel_until` cho `run_events`/`run_registrations`; đổi UNIQUE → (attendee_id, deleted_at) ✅ verified
- [x] S2 — BE: seed lại Fun Run — 5km(320)/10km(90)/15km(40), event_id=3 ✅ (run_events trước đó rỗng, không có 21km)
- [x] S3 — BE: API xin hủy + duyệt/từ chối hủy + hoàn suất (atomic) cho Run ✅ verified (claim/409/xin hủy/duyệt+hoàn suất/đăng ký lại/cancel_until 422)
- [x] S4 — FE cổng public: nút "Xin hủy" + modal lý do, ẩn/hiện theo `cancel_until`, thông báo duyệt/từ chối (Toast 1 lần), đăng ký lại sau hủy ✅ (lint sạch; cần smoke test trình duyệt)
- [x] S5 — FE admin: field `cancel_until` + màn "Yêu cầu hủy" (Duyệt/Từ chối, SweetAlert) ✅ (lint sạch; cần smoke test trình duyệt)

### Checkpoint A — Fun Run đầy đủ
- [~] FCFS không oversell — lõi verified qua service test; test tải đồng thời để ở S11
- [x] Hủy hoàn suất đúng, đăng ký lại được — verified S3
- [x] `cancel_until` chặn đúng — BE verified (422); FE ẩn nút theo mốc
- [ ] Smoke test trình duyệt cổng public + admin (chưa chạy)

## Phase 2 — Module Tham quan (mirror Run)
- [ ] S6 — BE: migration + entity `tour_sessions` & `tour_registrations` + seed 3 đợt ×86
- [ ] S7 — BE: TourSessionService + TourRegistrationService (claim FCFS + hủy), không BIB
- [ ] S8 — FE cổng public: endpoint + model Tour; thêm khối "Đi tham quan" (độc lập Fun Run)
- [ ] S9 — FE admin: CRUD đợt + danh sách theo đợt + export + duyệt hủy + phân quyền

### ✅ Checkpoint B — 2 module song song hoàn chỉnh

## Phase 3 — Hoàn thiện
- [ ] S10 — Dashboard mức lấp đầy + rà edge case + audit log
- [ ] S11 — Test tải đồng thời FCFS (bắt buộc): tổng active ≤ quota; hoàn suất không lệch số
