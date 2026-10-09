<?php
/**
 * Modal xem lịch trình tham quan chi tiết (hiển thị file PDF trong iframe).
 * Tham số: $pdfUrl — đường dẫn tới file PDF (web-accessible).
 * iframe dùng data-src, chỉ nạp PDF khi modal được mở (xử lý trong run-portal.js).
 */
?>
<div class="modal fade" id="modalTourSchedule" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light border-bottom py-3">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center" style="font-size: 1.05rem;">
                    <i class="bi bi-map-fill text-success me-2 fs-5"></i>
                    Lịch trình tham quan chi tiết
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <a href="<?php echo $pdfUrl; ?>" target="_blank" rel="noopener"
                       class="btn btn-sm btn-outline-success rounded-pill px-3" title="Mở trong tab mới">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Mở tab mới
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
            </div>

            <div class="modal-body p-0" style="background: #525659;">
                <iframe id="tourScheduleFrame"
                        data-src="<?php echo $pdfUrl; ?>"
                        title="Lịch trình tham quan"
                        style="width: 100%; height: 75vh; border: 0;"></iframe>
            </div>

            <div class="modal-footer bg-light border-top py-2 px-4 d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i> Không xem được? Hãy
                    <a href="<?php echo $pdfUrl; ?>" target="_blank" rel="noopener" class="fw-semibold text-success text-decoration-none">tải / mở file PDF</a>.
                </small>
                <button type="button" class="btn btn-outline-secondary btn-sm px-3 rounded-pill" data-bs-dismiss="modal">
                    Đóng
                </button>
            </div>
        </div>
    </div>
</div>
