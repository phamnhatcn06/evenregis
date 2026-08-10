# Kế hoạch triển khai — Thay thế / Huỷ tư cách người đăng ký

> Màn hình: `admin/approveRegistrations/view` (mở từ `admin/approveRegistrations/admin`)
> Ngày lập: 2026-08-10 · Nguồn quyết định: [[replace-withdraw-attendee]]

---

## 1. Bối cảnh & ràng buộc kiến trúc

- Yii chỉ là **frontend**; mọi dữ liệu đi qua **External API** (`ApiClient` + `ApiEndpoints`). Không có DB trực tiếp, **không có transaction đa-bước** phía frontend.
- Quyết định: **orchestrate phía controller** bằng các endpoint sẵn có. Để giảm rủi ro dữ liệu nửa vời, áp dụng **thứ tự thao tác an toàn** (tạo mới trước, gỡ cũ sau; nếu bước tạo lỗi thì dừng, không đụng dữ liệu người bị thay) + ghi log mỗi bước.
- Endpoint tái sử dụng (đã tồn tại): `ATTENDEE_STORE/UPDATE/DETAIL/UPLOAD_DOCUMENTS/BULK_STORE`, `SPORT_TEAM_LIST_BY_PROPERTY`, `SPORT_TEAM_MEMBER_LIST/STORE/DESTROY/COUNT_BY_ATTENDEE`, `SPORT_TEAM_UPDATE/DESTROY`, `COMPETITION_REGISTRATION_LIST/STORE/DESTROY`, `COMPETITION_ASSIGN_NUMBERS`, `ATTENDEE_ROLE_LIST/STORE/DESTROY`, `BADGE_LIST/UPDATE/DESTROY`, `STAFF_LIST/DETAIL/BEFORE_JUNE_2026`.
- **Huỷ tư cách** (đã chốt) = ghi `is_active = 0` **và** set `deleted_at` (thời điểm huỷ) trên attendee. Không thêm cột `participation_status`.

## 2. Quyết định nghiệp vụ đã chốt

| # | Nội dung |
|---|----------|
| Thể thao (thay thế) | Hiện toàn bộ đội của người bị thay, checkbox. Tích → người thay vào đúng vị trí (kế thừa jersey_number/position/is_captain). Không tích → huỷ cả đội. Không kiểm tra sĩ số min/max. |
| Nghiệp vụ (thay thế) | Kế thừa **toàn bộ** tự động, **cấp số báo danh mới**. |
| Trạng thái duyệt | Người thay **kế thừa approved luôn**. |
| Huỷ tư cách | Hiện toàn bộ đội, checkbox: tích → huỷ cả đội; không tích → chỉ gỡ người (giữ đội). Captain bị gỡ → cảnh báo chọn captain mới/để trống. Gỡ luôn competition_registrations + attendee_roles. |

## 3. Còn mở (không chặn Slice 1–5)

Q1 văn nghệ/sắc đẹp · Q2 bàn ăn/tiệc · Q6 người thay đã tham dự (chặn/cảnh báo) · Q9 phân quyền + cấm khi sự kiện đã bắt đầu · Xử lý badge đã in (Slice 5).

## 4. File tác động

**Controller:** `protected/modules/admin/controllers/ApproveRegistrationsController.php` (thêm actions)
**Model:** `protected/models/Attendees.php`, `SportTeams.php`, `SportTeamMembers.php`, `CompetitionRegistrations.php`, `AttendeeRoles.php`, `Badges.php` (bổ sung method orchestrate qua API)
**View chính:** `protected/modules/admin/views/approveRegistrations/view.php` (thêm nút + include partial)
**Partial mới:** `_modal_replace_attendee.php`, `_modal_withdraw_attendee.php`
**JS mới:** `themes/hope-ui/assets/js/pages/approveregistrations-view.js`
**Tái sử dụng UI:** `protected/modules/admin/views/registrations/_modal_edit_attendee.php`

---

## 5. Vertical slices

### Slice 1 — Bản kê nội dung (read-only) + nút thao tác
**Mục tiêu:** Trên mỗi dòng attendee ở `view.php`, thêm nút "Thay thế" và "Huỷ tư cách" (ẩn theo quyền `attendee:update`). Thêm endpoint controller `actionParticipationSummary($attendee_id)` trả JSON: danh sách đội (kèm team_id, sport_name, jersey_number, position, is_captain, sĩ số hiện tại, is_alliance), danh sách cuộc thi, danh sách vai trò.
**Model:** `Attendees::getParticipationSummary($attendeeId, $eventId, $propertyId)` gom dữ liệu từ `SPORT_TEAM_LIST_BY_PROPERTY` + `SPORT_TEAM_MEMBER_LIST` + `COMPETITION_REGISTRATION_LIST` + `ATTENDEE_ROLE_LIST`.
**Acceptance:**
- [ ] Nút hiển thị đúng theo quyền, mọi trạng thái phiếu (submitted/approved/rejected).
- [ ] Gọi summary trả đúng đội/cuộc thi/vai trò của attendee đó.
- [ ] Modal (rỗng) mở được, JS tách file, không inline JS.
**Verify thủ công:** mở 1 attendee có ≥1 đội + ≥1 cuộc thi → thấy đúng danh sách.

### Slice 2 — Huỷ tư cách cơ bản (không xử lý đội đặc biệt)
**Mục tiêu:** `actionWithdrawAttendee` — nhận `attendee_id`, `reason`. Gỡ toàn bộ `competition_registrations` + `attendee_roles` của người đó (DESTROY), đánh dấu attendee huỷ tư cách (`participation_status`/`is_active=0` + note + who/when), ghi log `APPROVE_ATTENDEE_LOG_STORE`. Chưa xử lý team (Slice 3).
**Modal:** `_modal_withdraw_attendee.php` — hiển thị tác động (từ summary) + ô lý do bắt buộc + nút submit chuẩn `modal-submit.md`. Xác nhận cuối bằng SweetAlert2.
**Acceptance:**
- [ ] Given người có cuộc thi/vai trò, When huỷ tư cách, Then các bản ghi đó bị gỡ, attendee bị đánh dấu huỷ, log ghi email người thực hiện.
- [ ] Lý do rỗng → chặn.
- [ ] Toast báo kết quả, không dùng Bootstrap Alert.

### Slice 3 — Xử lý đội khi huỷ tư cách
**Mục tiêu:** Trong modal huỷ, liệt kê các đội với checkbox "huỷ cả đội". Khi submit: đội được tích → `SPORT_TEAM_DESTROY` (+ gỡ members); đội không tích → chỉ `SPORT_TEAM_MEMBER_DESTROY` cho người đó. Nếu người bị gỡ là captain của đội không tích → yêu cầu chọn: gán captain mới (dropdown thành viên còn lại → `SPORT_TEAM_MEMBER_UPDATE is_captain=1`) / để trống.
**Acceptance:**
- [ ] Đội tích huỷ → team `status=CANCELLED`/destroy, các member soft delete.
- [ ] Đội không tích → chỉ gỡ đúng người, đội giữ nguyên.
- [ ] Captain bị gỡ ở đội giữ lại → cảnh báo + áp dụng lựa chọn captain mới/để trống.
- [ ] Đội liên quân: xử lý theo cùng lựa chọn tích/không tích (cảnh báo ảnh hưởng đơn vị khác, không tự huỷ nếu không tích).

### Slice 4 — Thay thế + kế thừa
**Mục tiêu:** `actionReplaceAttendee` — nhận thông tin người thay (SMILE `staff_id` hoặc nhập thủ công), ảnh + hồ sơ, danh sách `team_ids` được tích, `reason`.
Luồng: (1) validate; (2) `ATTENDEE_STORE` tạo B cùng registration_id/event_id/property_id, `approval_status=APPROVED` (kế thừa); (3) upload ảnh/hồ sơ `ATTENDEE_UPLOAD_DOCUMENTS`; (4) với mỗi team tích → `SPORT_TEAM_MEMBER_STORE` cho B (copy jersey/position/is_captain) rồi `SPORT_TEAM_MEMBER_DESTROY` của A; team không tích → `SPORT_TEAM_DESTROY`; (5) với mỗi competition của A → `COMPETITION_REGISTRATION_STORE` cho B rồi gọi cấp số mới (`COMPETITION_ASSIGN_NUMBERS` hoặc để backend cấp), destroy đăng ký của A; (6) copy `attendee_roles`; (7) đánh dấu A huỷ tư cách + ghi bảng/log lịch sử thay thế.
**Modal:** `_modal_replace_attendee.php` — cột trái thông tin A + checkbox đội kế thừa; cột phải tab SMILE / thủ công + upload ảnh/hồ sơ (tái sử dụng `_modal_edit_attendee.php`); ô lý do.
**Acceptance:**
- [ ] B được tạo, approved, có ảnh + hồ sơ.
- [ ] B vào đúng đội tích với cùng jersey/position/is_captain; đội không tích bị huỷ.
- [ ] B có đăng ký đủ cuộc thi của A với **số báo danh mới**.
- [ ] B nhận đúng vai trò của A; A bị đánh dấu huỷ tư cách; có log lịch sử thay thế.
- [ ] Nếu bước tạo B lỗi → dừng, không đụng dữ liệu A.

### Slice 5 — Xử lý thẻ / QR (badge)
**Mục tiêu:** Khi A đã có badge (`badge_printed=1`/`print_count>0`): cảnh báo trong cả 2 modal; khi thực thi → vô hiệu QR/badge của A (`BADGE_UPDATE` cờ revoked hoặc `BADGE_DESTROY`), và với thay thế → đánh dấu B cần sinh badge.
**Acceptance:**
- [ ] Cảnh báo "thẻ đã in" hiển thị khi phù hợp.
- [ ] Badge của A bị vô hiệu; B được đánh dấu cần in.

### Slice 6 — Phân quyền, thống kê, hoàn thiện
**Mục tiêu:** Kiểm tra `PermissionHelper::can('attendee','update')` ở đầu các action; cập nhật logic đếm (loại người huỷ/thay khỏi thống kê `countUniqueRegistered`); QA hồi quy; xử lý các câu hỏi mở đã chốt.

---

## 6. Thứ tự & phụ thuộc

1 → 2 → 3 → 4 → 5 → 6. Slice 4 phụ thuộc 1 (summary) và tái sử dụng logic team của 3.

## 7. Rủi ro & phụ thuộc backend

- **R1 (Cao) Không nguyên tử:** giảm thiểu bằng thứ tự "tạo trước, gỡ sau" + log; cân nhắc đề nghị backend bổ sung endpoint `attendees/replace` & `attendees/withdraw` chạy transaction (nice-to-have).
- **PHỤ THUỘC:** cần backend xác nhận (a) attendee có field đánh dấu huỷ tư cách (`participation_status` hay dùng `is_active`); (b) cơ chế cấp số báo danh mới cho 1 đăng ký lẻ; (c) cờ revoke badge. → Xác nhận trước khi bắt đầu Slice 2 & 4 & 5.

## 8. Checkpoint

- **Sau Slice 1:** summary chính xác, UI nút/modal khung hoạt động.
- **Sau Slice 3:** luồng huỷ tư cách hoàn chỉnh, xử lý team đúng.
- **Sau Slice 4:** luồng thay thế hoàn chỉnh + kế thừa đúng.
- **Sau Slice 6:** phân quyền + thống kê + QA hồi quy xong.
