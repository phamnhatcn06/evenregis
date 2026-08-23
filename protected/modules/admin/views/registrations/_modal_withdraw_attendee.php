<?php
/**
 * Modal Huỷ tư cách người tham dự.
 * Nội dung tác động (đội/cuộc thi/vai trò) được nạp động bằng JS từ endpoint participationSummary.
 * Xử lý huỷ đội / captain sẽ bổ sung ở Slice 3.
 */
?>
<div class="modal fade" id="withdrawAttendeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fa fa-user-times me-2"></i>Huỷ tư cách người tham dự</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <form id="withdrawAttendeeForm">
                <div class="modal-body">
                    <input type="hidden" name="attendee_id" id="withdraw_attendee_id">

                    <div class="alert alert-warning d-flex align-items-start" role="alert">
                        <i class="fa fa-exclamation-triangle fa-lg me-2 mt-1"></i>
                        <div>
                            Huỷ tư cách <strong id="withdraw_attendee_name">người này</strong> sẽ gỡ họ khỏi các nội dung dưới đây.
                            <span id="withdraw_badge_warning" class="d-none text-danger fw-bold"><br>Lưu ý: người này đã được in thẻ.</span>
                        </div>
                    </div>

                    <div id="withdraw_summary_container">
                        <div class="text-center text-muted py-4">
                            <i class="fa fa-spinner fa-spin me-1"></i>Đang tải nội dung tham gia...
                        </div>
                    </div>

                    <div class="mb-2 mt-3">
                        <label for="withdraw_reason" class="form-label">Lý do huỷ tư cách <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="withdraw_reason" name="reason" rows="2" required
                                  placeholder="Nhập lý do huỷ tư cách..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger" id="btn_submit_withdraw">
                        <i class="fa fa-user-times me-1"></i>Huỷ tư cách
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
