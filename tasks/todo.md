# TODO: Thay thế / Huỷ tư cách người đăng ký

## Phase 0: Xác nhận phụ thuộc backend (trước khi code Slice 2+)
- [ ] Xác nhận field đánh dấu huỷ tư cách trên attendee (participation_status hay is_active)
- [ ] Xác nhận cách cấp số báo danh mới cho 1 competition_registration lẻ
- [ ] Xác nhận cờ revoke/destroy badge

## Phase 1: Bản kê nội dung + nút thao tác (Slice 1)
- [ ] Model `Attendees::getParticipationSummary()` gom đội/cuộc thi/vai trò
- [ ] Controller `actionParticipationSummary($attendee_id)` trả JSON
- [ ] view.php: thêm nút "Thay thế" + "Huỷ tư cách" mỗi dòng (theo quyền)
- [ ] Tạo khung `_modal_replace_attendee.php`, `_modal_withdraw_attendee.php`
- [ ] Tạo `approveregistrations-view.js`, register POS_END
- [ ] Verify: mở attendee có đội + cuộc thi → summary đúng

## Checkpoint: Slice 1 xong

## Phase 2: Huỷ tư cách cơ bản (Slice 2)
- [ ] `actionWithdrawAttendee` gỡ competition_registrations + attendee_roles
- [ ] Đánh dấu attendee huỷ tư cách + log
- [ ] Modal huỷ: hiển thị tác động + lý do bắt buộc + SweetAlert + Toast

## Phase 3: Xử lý đội khi huỷ (Slice 3)
- [ ] Checkbox "huỷ cả đội" trong modal huỷ
- [ ] Đội tích → destroy team; đội không tích → chỉ gỡ member
- [ ] Captain bị gỡ ở đội giữ lại → chọn captain mới/để trống
- [ ] Xử lý đội liên quân theo tích/không tích

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
