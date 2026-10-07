<?php
/**
 * Modal thiết lập thủ công mã Lucky Number (hỗ trợ hoán đổi/toggle khi trùng).
 *
 * @var FinalAttendeeRostersController $this
 */
?>
<div class="modal fade" id="modal_set_lucky" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                <h5 class="modal-title text-white d-flex align-items-center gap-2" style="font-size: 16px; font-weight: 700;">
                    <span class="d-inline-flex align-items-center justify-content-center bg-white text-primary rounded-circle" style="width: 32px; height: 32px; font-size: 14px;">
                        <i class="fa fa-ticket"></i>
                    </span>
                    <span>Thiết Lập Mã Lucky Number</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>

            <div class="modal-body p-4">
                <!-- Thông tin người nhận -->
                <div class="p-3 mb-3 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">Người nhận:</span>
                        <span class="fw-bold text-dark fs-6" id="set_lucky_target_name">-</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">Đơn vị:</span>
                        <span class="text-secondary small fw-medium" id="set_lucky_target_unit">-</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-muted small fw-semibold">Mã lucky hiện tại:</span>
                        <span id="set_lucky_target_current">
                            <span class="badge bg-secondary">Chưa cấp</span>
                        </span>
                    </div>
                </div>

                <form id="form_set_lucky" onsubmit="return false;">
                    <input type="hidden" id="set_lucky_id" value="">
                    <input type="hidden" id="set_lucky_current_val" value="">

                    <!-- Input nhập mã lucky -->
                    <div class="mb-3">
                        <label for="set_lucky_input" class="form-label fw-bold text-dark mb-1">
                            Nhập mã lucky muốn gán <span class="text-danger">*</span>
                        </label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light text-muted border-end-0">
                                <i class="fa fa-ticket"></i>
                            </span>
                            <input type="text"
                                   id="set_lucky_input"
                                   class="form-control text-center fw-bold fs-4 border-start-0"
                                   style="letter-spacing: 2px; color: #0d6efd;"
                                   placeholder="VD: 0088"
                                   maxlength="10"
                                   autocomplete="off"
                                   autofocus>
                        </div>
                        <div class="form-text small text-muted mt-1">
                            <i class="fa fa-info-circle me-1"></i>Hệ thống tự động chuẩn hoá thành 4 chữ số (VD: <code>88</code> &rarr; <code>0088</code>).
                        </div>
                    </div>

                    <!-- Hộp trạng thái kiểm tra (Live status indicator) -->
                    <div id="set_lucky_status_box" class="mb-3 d-none">
                        <!-- Content rendered dynamically via JS -->
                    </div>

                    <!-- Hướng dẫn toggle -->
                    <div class="p-2 px-3 rounded-2 small text-muted d-flex align-items-start gap-2" style="background: rgba(13, 110, 253, 0.05); border: 1px dashed rgba(13, 110, 253, 0.2);">
                        <i class="fa fa-exchange text-primary mt-1"></i>
                        <span>
                            <strong>Quy tắc hoán đổi (Toggle):</strong> Nếu mã nhập trùng với người khác, hệ thống sẽ tự động gán mã này cho người được chọn và chuyển mã cũ của người này sang cho người bị trùng.
                        </span>
                    </div>
                </form>
            </div>

            <div class="modal-footer bg-light px-4 py-3 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">
                    <i class="fa fa-times me-1"></i> Đóng
                </button>
                <button type="button" class="btn btn-primary px-4 fw-semibold" id="btn_submit_lucky">
                    <i class="fa fa-check me-1"></i> <span id="btn_submit_lucky_text">Lưu mã lucky</span>
                </button>
            </div>
        </div>
    </div>
</div>
