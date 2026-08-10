<?php
/**
 * Modal Thay thế người tham dự.
 * Cột trái: thông tin người bị thay + nội dung sẽ kế thừa (nạp động bằng JS).
 * Cột phải: chọn người thay (SMILE/thủ công) + ảnh/hồ sơ — hoàn thiện ở Slice 4.
 */
?>
<div class="modal fade" id="replaceAttendeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fa fa-exchange me-2"></i>Thay thế người tham dự</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <form id="replaceAttendeeForm">
                <div class="modal-body">
                    <input type="hidden" name="attendee_id" id="replace_attendee_id">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <strong><i class="fa fa-user me-1"></i>Người bị thay</strong>
                                </div>
                                <div class="card-body">
                                    <p class="mb-1"><strong id="replace_attendee_name">-</strong></p>
                                    <p class="text-muted small mb-3" id="replace_attendee_position">-</p>
                                    <span id="replace_badge_warning" class="d-none badge bg-danger mb-2">Đã in thẻ</span>
                                    <h6 class="text-muted">Nội dung sẽ chuyển sang người thay</h6>
                                    <div id="replace_summary_container">
                                        <div class="text-center text-muted py-3">
                                            <i class="fa fa-spinner fa-spin me-1"></i>Đang tải...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <strong><i class="fa fa-user-plus me-1"></i>Người thay thế</strong>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">Phần chọn người thay (SMILE / thủ công), ảnh và hồ sơ sẽ được bổ sung.</p>
                                    <div class="mb-2">
                                        <label for="replace_reason" class="form-label">Lý do thay thế <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="replace_reason" name="reason" rows="2"
                                                  placeholder="Nhập lý do thay thế..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary" id="btn_submit_replace" disabled>
                        <i class="fa fa-exchange me-1"></i>Thực hiện thay thế
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
