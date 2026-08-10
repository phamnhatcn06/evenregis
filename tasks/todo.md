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

## Phase 4: Thay thế + kế thừa (Slice 4)
- [ ] `actionReplaceAttendee`: tạo B (SMILE/thủ công) approved + ảnh + hồ sơ
- [ ] Kế thừa đội tích (jersey/position/is_captain), huỷ đội không tích
- [ ] Kế thừa toàn bộ cuộc thi + cấp số báo danh mới
- [ ] Copy vai trò; đánh dấu A huỷ; log lịch sử thay thế
- [ ] Guard: tạo B lỗi → dừng, không đụng A
- [ ] Modal thay thế đầy đủ (tái sử dụng _modal_edit_attendee)

## Checkpoint: Luồng thay thế hoàn chỉnh

## Phase 5: Thẻ / QR (Slice 5)
- [ ] Cảnh báo "thẻ đã in" trong 2 modal
- [ ] Vô hiệu badge A; đánh dấu B cần sinh badge

## Phase 6: Hoàn thiện (Slice 6)
- [ ] PermissionHelper check đầu mỗi action
- [ ] Cập nhật thống kê loại người huỷ/thay
- [ ] QA hồi quy toàn luồng
