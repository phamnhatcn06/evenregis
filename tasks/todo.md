# TODO: Thay thế / Huỷ tư cách người đăng ký

## Phase 0: Xác nhận phụ thuộc backend
- [x] Field đánh dấu huỷ tư cách = `is_active = 0` + set `deleted_at` (thời điểm huỷ)
- [ ] Cách cấp số báo danh mới cho 1 competition_registration lẻ (xác nhận ở Slice 4)
- [ ] Cờ revoke/destroy badge (xác nhận ở Slice 5)

## Phase 1: Bản kê nội dung + nút thao tác (Slice 1) — XONG
- [x] Model `Attendees::getParticipationSummary()` gom đội/cuộc thi/vai trò (+ `SportTeamMembers::countTeamMembers`)
- [x] Controller `actionParticipationSummary($attendee_id)` trả JSON (check quyền)
- [x] view.php: thêm nút "Thay thế" + "Huỷ tư cách" mỗi dòng (cột theo quyền, mọi trạng thái)
- [x] Tạo khung `_modal_replace_attendee.php`, `_modal_withdraw_attendee.php`
- [x] Tạo `approveregistrations-view.js`, register POS_END
- [ ] Verify thủ công trên trình duyệt: mở attendee có đội + cuộc thi → summary đúng

## Checkpoint: Slice 1 xong

## Phase 2: Huỷ tư cách cơ bản (Slice 2) — XONG
- [x] `actionWithdrawAttendee` gỡ competition_registrations + attendee_roles
- [x] Đánh dấu attendee huỷ tư cách (is_active=0 + deleted_at + note) + Yii::log
- [x] Modal huỷ: hiển thị tác động + lý do bắt buộc + SweetAlert + Toast
- [ ] Verify trình duyệt: huỷ 1 người → cuộc thi/vai trò bị gỡ, người bị đánh dấu huỷ

## Phase 3: Xử lý đội khi huỷ (Slice 3) — XONG
- [x] Checkbox "huỷ cả đội" trong modal huỷ (render tương tác)
- [x] Đội tích → destroy team (+ members); đội không tích → chỉ gỡ member
- [x] Captain bị gỡ ở đội giữ lại → dropdown chọn captain mới/để trống
- [x] Đội liên quân hiển thị badge; xử lý theo cùng cơ chế tích/không tích
- [ ] Verify trình duyệt: huỷ có tích đội → đội mất; không tích → chỉ mất người; captain → gán mới

## Checkpoint: Luồng huỷ tư cách hoàn chỉnh

## Phase 4: Thay thế + kế thừa (Slice 4) — XONG (trừ log→4b)
- [x] `actionReplaceAttendee`: tạo B (SMILE/thủ công) approved + ảnh + hồ sơ (upload helper)
- [x] Kế thừa đội tích (jersey/position/is_captain), huỷ đội không tích
- [x] Kế thừa toàn bộ cuộc thi; candidate_number để trống → backend cấp số mới
- [x] Copy vai trò (khi admin không chọn); đánh dấu A huỷ tư cách
- [x] Guard: tạo B lỗi → dừng, không đụng nội dung A
- [x] Modal thay thế đầy đủ: tab SMILE / thủ công + vai trò + 4 upload + checkbox kế thừa đội
- [ ] Ghi lịch sử thay thế → chuyển sang Slice 4b (chờ endpoint backend)
- [ ] Verify trình duyệt: thay 1 người có đội/cuộc thi → B kế thừa đúng, A bị huỷ

## Checkpoint: Luồng thay thế hoàn chỉnh

## Phase 4b: Lịch sử thay đổi + email (Slice 4b) — XONG (trừ verify)
- [~] Backend: bảng + endpoint `attendee-replacements` (store + list-by-registration) — FRONTEND đã gọi theo hợp đồng schema; cần backend hiện thực `/api/attendee-replacements/store` + list
- [x] Model `AttendeeReplacements` (storeViaApi, record, getByRegistrationId) + ApiEndpoints const
- [x] Ghi log mỗi lần thay thế / huỷ (affected_contents + cancelled_teams + old↔new snapshot + lý do + người thực hiện) trong actionReplaceAttendee & actionWithdrawAttendee
- [x] `AttendeeReplacements::getFormattedByRegistrationId` — parse JSON snapshot → dòng hiển thị
- [x] `EmailHelper::sendRegistrationConfirmation` truyền `personnelChanges`; email view render mục "🔄 Thay đổi nhân sự" (bảng loại/nhân sự/nội dung+lý do). PDF (biểu mẫu chính thức) giữ nguyên.
- [ ] Verify: gửi mail xác nhận → thấy đúng danh sách thay/huỷ (chờ backend store/list live)

## Phase 5: Thẻ / QR (Slice 5) — XONG (trừ verify)
- [x] Cảnh báo "thẻ đã in" trong 2 modal (badge_printed từ summary; controller suy ra từ Badges khi API thiếu cờ)
- [x] Vô hiệu badge A: `Badges::revokeByAttendee` (soft delete BADGE_DESTROY) gọi trong actionWithdrawAttendee & actionReplaceAttendee. B là bản ghi mới chưa có badge → tự nằm trong danh sách "cần in".
- [ ] Verify trình duyệt: huỷ/thay người đã in thẻ → thẻ cũ bị vô hiệu

## Phase 6: Hoàn thiện (Slice 6) — XONG (trừ QA)
- [x] PermissionHelper check đầu mỗi action mutating (approve/reject/approveAll/rejectAll/return/sendEmail + withdraw/replace/summary đã có)
- [x] Thống kê đã tự loại người huỷ/thay: `Attendees::countUniqueRegistered` bỏ qua `is_active=0` và `deleted_at` (huỷ tư cách set cả hai)
- [ ] QA hồi quy toàn luồng
