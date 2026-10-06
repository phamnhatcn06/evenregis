<?php
/** Modal xin hủy đăng ký chạy — người dùng nhập lý do. */
?>
<div class="modal fade" id="modalCancelRun" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-cancel-run" action="<?php echo $cancelUrl; ?>" method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Xin hủy đăng ký</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-2">
                        Yêu cầu hủy cần ban tổ chức duyệt. Sau khi được duyệt, suất của bạn sẽ được hoàn lại và bạn có thể đăng ký nội dung khác.
                    </p>
                    <div class="mb-2">
                        <label for="cancel-reason" class="form-label">Lý do hủy <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="cancel-reason" name="reason" rows="3" maxlength="255" required
                                  placeholder="Nhập lý do bạn muốn hủy đăng ký..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger" id="btn-submit-cancel-run">
                        <i class="fa fa-paper-plane me-1"></i>Gửi yêu cầu hủy
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
