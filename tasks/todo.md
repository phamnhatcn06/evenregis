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
- [x] S6 — BE: module `Modules/Tour` + migration `tour_sessions`/`tour_registrations` + seed 3 đợt ×86 ✅ verified
- [x] S7 — BE: TourSessionService + TourRegistrationService (claim FCFS + hủy), không BIB ✅ verified (claim/409/hủy/duyệt+hoàn suất/đăng ký lại)
- [x] S8 — FE cổng public: endpoint + model Tour; portal gộp 2 khối (Fun Run + Tham quan độc lập); đăng ký + xin hủy + thông báo duyệt/từ chối cả 2 ✅ (lint sạch; cần smoke test)
- [x] S9 — FE admin: CRUD đợt (tourSessions) + danh sách theo đợt + export + duyệt hủy (tourRegistrations) ✅ lint sạch, routes verified. Phân quyền: key SSO `toursessions`/`tourregistrations` (admin `*` pass; role khác cần Portal cấp)

### Checkpoint B — 2 module song song hoàn chỉnh
- [x] Module Tour BE + routes verified
- [x] Cổng public gộp 2 khối độc lập
- [x] Admin Tour CRUD + export + duyệt hủy
- [ ] Smoke test trình duyệt (chưa chạy)

## Phase 3 — Hoàn thiện
- [x] S11 — Test tải đồng thời FCFS ✅ Tour: 20 claim song song / quota 5 → đúng 5×200 + 15×409, registered_count=5. Run: 12 claim / quota 3 → đúng 3×200 + 9×409. KHÔNG oversell. Audit log đã ghi ở mọi claim/cancel.
- [ ] S10 — (nên có) Dashboard mức lấp đầy realtime — CHƯA làm (tùy chọn)

## Còn lại
- [ ] Smoke test trình duyệt: cổng public (2 khối, đăng ký, xin hủy) + admin (CRUD, duyệt hủy, export) — cần chạy app thực
- [ ] Portal cấp quyền SSO `toursessions`/`tourregistrations` cho role không phải admin (nếu cần)
