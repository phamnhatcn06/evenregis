<?php
/**
 * Modal xin hủy đăng ký (dùng chung cho Fun Run và Tham quan).
 * Tham số: $modalId, $formId, $btnId, $cancelUrl, $title.
 */
?>
<div class="modal fade" id="<?php echo $modalId; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="<?php echo $formId; ?>" action="<?php echo $cancelUrl; ?>" method="post" class="form-cancel-portal" data-btn="<?php echo $btnId; ?>" data-modal="<?php echo $modalId; ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo CHtml::encode($title); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-2">
                        Yêu cầu hủy cần ban tổ chức duyệt. Sau khi được duyệt, suất của bạn sẽ được hoàn lại.
                    </p>
                    <div class="mb-2">
                        <label class="form-label">Lý do hủy <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="reason" rows="3" maxlength="255" required
                                  placeholder="Nhập lý do bạn muốn hủy đăng ký..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger" id="<?php echo $btnId; ?>">
                        <i class="fa fa-paper-plane me-1"></i>Gửi yêu cầu hủy
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
