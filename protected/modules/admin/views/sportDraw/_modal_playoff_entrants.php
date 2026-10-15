<?php
/**
 * Modal chọn VĐV/đội từ vòng chung kết để chuyển sang giai đoạn play-off.
 *
 * @var SportDrawController $this
 */
?>
<div class="modal fade" id="modal-playoff-entrants" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-user-plus me-1"></i>Chọn VĐV vào play-off
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-2">
                    Các VĐV/đội được chọn sẽ được <strong>chuyển khỏi vòng chung kết</strong> và đưa vào
                    <strong id="pe-stage-name">play-off</strong>. Sau khi đánh play-off, người thắng sẽ được
                    đưa trở lại vòng chung kết.
                </p>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="pe-check-all">
                        <label class="form-check-label small" for="pe-check-all">Chọn tất cả</label>
                    </div>
                    <span class="badge bg-secondary" id="pe-selected-count">0 đã chọn</span>
                </div>
                <div id="pe-list" class="border rounded p-2" style="min-height:120px;">
                    <div class="text-center text-muted py-3">
                        <i class="fa fa-spinner fa-spin me-1"></i>Đang tải danh sách...
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" id="pe-submit">
                    <i class="fa fa-save me-1"></i>Chuyển vào play-off
                </button>
            </div>
        </div>
    </div>
</div>
