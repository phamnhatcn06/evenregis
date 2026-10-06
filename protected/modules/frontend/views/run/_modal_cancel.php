<?php
/**
 * Modal xin hủy đăng ký (dùng chung cho Fun Run và Tham quan).
 * Tham số: $modalId, $formId, $btnId, $cancelUrl, $title.
 */
?>
<div class="modal fade" id="<?php echo $modalId; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form id="<?php echo $formId; ?>" action="<?php echo $cancelUrl; ?>" method="post" class="form-cancel-portal" data-btn="<?php echo $btnId; ?>" data-modal="<?php echo $modalId; ?>">
                <div class="modal-header bg-light border-bottom py-3">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center" style="font-size: 1.05rem;">
                        <i class="bi bi-exclamation-triangle-fill text-danger me-2 fs-5"></i>
                        <?php echo CHtml::encode($title); ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="alert alert-warning d-flex align-items-start mb-3 border-0 bg-warning-subtle text-dark p-3 rounded-3">
                        <i class="bi bi-info-circle-fill text-warning fs-5 me-2 mt-1 flex-shrink-0"></i>
                        <div class="small">
                            <strong>Lưu ý:</strong> Yêu cầu hủy cần được Ban tổ chức phê duyệt. Sau khi duyệt, số BIB / chỗ của bạn sẽ được hoàn lại để bạn hoặc người khác có thể đăng ký mới.
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold text-dark small mb-1">
                            <i class="bi bi-pencil-square me-1 text-primary"></i> Lý do xin hủy <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control rounded-3" name="reason" rows="3" maxlength="255" required
                                  placeholder="Vui lòng nêu rõ lý do bạn muốn hủy đăng ký (VD: thay đổi cự ly, bận lịch công tác...)"
                                  style="font-size: 0.9rem;"></textarea>
                        <div class="form-text text-muted small mt-1">
                            Tối đa 255 ký tự. Ban tổ chức sẽ căn cứ lý do để xét duyệt.
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top py-2 px-4 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 rounded-pill" data-bs-dismiss="modal">
                        Đóng
                    </button>
                    <button type="submit" class="btn btn-danger btn-sm px-3 rounded-pill" id="<?php echo $btnId; ?>">
                        <i class="bi bi-send-fill me-1"></i> Gửi yêu cầu hủy
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
