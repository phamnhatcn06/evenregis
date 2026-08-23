<?php
/**
 * Modal Thay thế người tham dự (đa nội dung).
 * Cột trái: người bị thay + bảng gán nội dung (mỗi nội dung → chọn người thay hoặc huỷ).
 * Cột phải: danh sách người thay (thêm/xoá được), mỗi người là 1 hồ sơ SMILE/thủ công + ảnh.
 *
 * Card người thay được dựng động bằng JS (approveregistrations-view.js). Danh sách nhân sự
 * và vai trò được nhúng dưới dạng JSON để JS build option cho từng card.
 *
 * @var Registrations $model
 * @var array $roles      role_id => role_name
 * @var array $transports
 * @var array $staffList  [ [id,name,position,code], ... ]
 * @var array $otherAttendees [ [id,name,position,id_card], ... ] người tham dự ở các đăng ký khác của đơn vị
 */
?>
<div class="modal fade" id="replaceAttendeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fa fa-exchange me-2"></i>Thay thế người tham dự</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <form id="replaceAttendeeForm" enctype="multipart/form-data" class="d-flex flex-column overflow-hidden" style="min-height:0;">
                <div class="modal-body flex-grow-1 overflow-auto" style="min-height:0;">
                    <input type="hidden" name="attendee_id" id="replace_attendee_id">
                    <input type="hidden" name="registration_id" value="<?php echo $model->id; ?>">
                    <input type="hidden" name="event_id" value="<?php echo $model->event_id; ?>">
                    <input type="hidden" name="property_id" value="<?php echo $model->property_id; ?>">

                    <div class="row g-3">
                        <!-- Cột trái: người bị thay + gán nội dung -->
                        <div class="col-md-5">
                            <div class="card h-100">
                                <div class="card-header bg-light"><strong><i class="fa fa-user me-1"></i>Người bị thay</strong></div>
                                <div class="card-body">
                                    <p class="mb-1"><strong id="replace_attendee_name">-</strong></p>
                                    <p class="text-muted small mb-2" id="replace_attendee_position">-</p>
                                    <span id="replace_badge_warning" class="d-none badge bg-danger mb-2">Đã in thẻ</span>
                                    <hr class="my-2">
                                    <h6 class="text-muted mb-1">Gán nội dung cho người thay</h6>
                                    <p class="small text-muted mb-2">Mỗi nội dung phải chọn một người thay hoặc đánh dấu huỷ. Riêng thể thao khi huỷ, mặc định chỉ gỡ người này khỏi đội; tích "Huỷ cả đội" mới xoá toàn đội.</p>
                                    <div id="replace_assign_container">
                                        <div class="text-center text-muted py-3"><i class="fa fa-spinner fa-spin me-1"></i>Đang tải...</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cột phải: danh sách người thay -->
                        <div class="col-md-7">
                            <div class="card h-100">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <strong><i class="fa fa-user-plus me-1"></i>Người thay thế</strong>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0" id="replace_add_sub">
                                        <i class="fa fa-plus me-1"></i>Thêm người thay thế
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div id="replace_subs_container"></div>

                                    <div class="mt-2">
                                        <label class="form-label">Lý do thay thế <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="replace_reason" name="reason" rows="2" placeholder="Nhập lý do thay thế..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <span class="text-warning small me-auto d-none" id="replace_cover_hint"></span>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary" id="btn_submit_replace" disabled>
                        <i class="fa fa-exchange me-1"></i>Thực hiện thay thế
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="application/json" id="replace_staff_list"><?php
    $staffJson = array();
    foreach ($staffList as $st) {
        $staffJson[] = array(
            'id' => isset($st['id']) ? $st['id'] : '',
            'name' => isset($st['name']) ? $st['name'] : '',
            'position' => isset($st['position']) ? $st['position'] : '',
            'code' => isset($st['code']) ? $st['code'] : '',
        );
    }
    echo CJSON::encode($staffJson);
?></script>
<script type="application/json" id="replace_other_attendees_list"><?php
    echo CJSON::encode(isset($otherAttendees) && is_array($otherAttendees) ? array_values($otherAttendees) : array());
?></script>
<script type="application/json" id="replace_roles_list"><?php echo CJSON::encode($roles); ?></script>
