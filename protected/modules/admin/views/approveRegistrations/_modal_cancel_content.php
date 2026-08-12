<?php
/**
 * Modal Huỷ nội dung thi đấu (thể thao) của người tham dự.
 * Dùng khi VĐV đổi ý, muốn bỏ 1 nội dung nhưng vẫn giữ tư cách + các nội dung còn lại.
 * Danh sách đội được nạp động bằng JS từ endpoint participationSummary (chỉ hiển thị thể thao).
 */
?>
<div class="modal fade" id="cancelContentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fa fa-minus-circle me-2"></i>Huỷ nội dung thi đấu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <form id="cancelContentForm">
                <div class="modal-body">
                    <input type="hidden" name="attendee_id" id="cancel_content_attendee_id">

                    <div class="alert alert-warning d-flex align-items-start" role="alert">
                        <i class="fa fa-exclamation-triangle fa-lg me-2 mt-1"></i>
                        <div>
                            Chọn (các) đội thi đấu muốn huỷ cho <strong id="cancel_content_attendee_name">người này</strong>.
                            VĐV vẫn giữ tư cách và các nội dung còn lại.
                            <span id="cancel_content_badge_warning" class="d-none text-danger fw-bold"><br>Lưu ý: người này đã được in thẻ.</span>
                            <br><small class="text-muted">Nếu đây là nội dung duy nhất còn lại, hệ thống sẽ tự động huỷ tư cách và thu hồi thẻ.</small>
                        </div>
                    </div>

                    <div id="cancel_content_container">
                        <div class="text-center text-muted py-4">
                            <i class="fa fa-spinner fa-spin me-1"></i>Đang tải nội dung tham gia...
                        </div>
                    </div>

                    <div class="mb-2 mt-3">
                        <label for="cancel_content_reason" class="form-label">Lý do huỷ nội dung <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="cancel_content_reason" name="reason" rows="2" required
                                  placeholder="Nhập lý do huỷ nội dung..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-warning" id="btn_submit_cancel_content">
                        <i class="fa fa-minus-circle me-1"></i>Huỷ nội dung
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
