<?php
/**
 * Modal gửi thông tin tài khoản theo đơn vị: liệt kê các đơn vị (dưới bộ lọc hiện tại),
 * cho tick chọn rồi gửi lần lượt từng đơn vị một email kèm PDF danh sách tài khoản.
 *
 * @var FinalAttendeeRostersController $this
 * @var int $eventId
 * @var int $periodId
 */
?>
<div class="modal fade" id="modal_send_accounts" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="far-kpi-icon-bubble far-kpi-bubble-primary mb-0 me-2" style="width: 34px; height: 34px; font-size: 15px;">
                        <i class="fa fa-paper-plane"></i>
                    </span>
                    <span>Gửi thông tin tài khoản theo đơn vị</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="send_accounts_event_id" value="<?php echo (int) $eventId; ?>">
                <input type="hidden" id="send_accounts_period_id" value="<?php echo (int) $periodId; ?>">

                <!-- Banner test mode: hiện khi EmailHelper bật DEBUG_MODE -->
                <div id="send_accounts_debug_banner" class="alert alert-warning py-2 small mb-3 border-0 d-none">
                    <i class="fa fa-flask me-1"></i>
                    <strong>Đang bật test mode:</strong> mọi email sẽ chỉ gửi tới
                    <strong><span id="send_accounts_debug_email"></span></strong>, không gửi tới email thật của đơn vị.
                </div>

                <div class="alert alert-info py-2 small mb-3 border-0 d-flex align-items-center" style="background: rgba(8, 177, 186, 0.1); color: #067c83;">
                    <i class="fa fa-info-circle fa-lg me-2 flex-shrink-0"></i>
                    <div>
                        Mỗi đơn vị nhận <strong>một email riêng</strong> kèm file PDF danh sách thành viên vào vòng chung kết
                        và <strong>định danh đăng nhập</strong> của từng người. Danh sách lấy theo đúng bộ lọc đang áp dụng.
                    </div>
                </div>

                <!-- Trạng thái tải -->
                <div id="send_accounts_loading" class="far-empty-state py-4">
                    <div class="far-empty-icon" style="width: 56px; height: 56px; font-size: 24px;">
                        <i class="fa fa-spinner fa-spin"></i>
                    </div>
                    <div class="text-muted small">Đang tải danh sách đơn vị...</div>
                </div>

                <!-- Rỗng -->
                <div id="send_accounts_empty" class="far-empty-state py-4 d-none">
                    <div class="far-empty-icon" style="width: 56px; height: 56px; font-size: 24px;">
                        <i class="fa fa-inbox"></i>
                    </div>
                    <div class="text-muted small">Không có đơn vị nào trong danh sách hiện tại.</div>
                </div>

                <!-- Danh sách đơn vị -->
                <div id="send_accounts_list_wrap" class="d-none">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="send_accounts_check_all" checked>
                            <label class="form-check-label fw-semibold" for="send_accounts_check_all">Chọn tất cả</label>
                        </div>
                        <span class="small text-muted"><span id="send_accounts_selected_count">0</span> đơn vị được chọn</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;"></th>
                                    <th>Đơn vị</th>
                                    <th style="width: 90px; text-align: center;">Số người</th>
                                    <th style="width: 230px;">Email nhận</th>
                                </tr>
                            </thead>
                            <tbody id="send_accounts_tbody"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Kết quả gửi -->
                <div id="send_accounts_result" class="d-none mt-3">
                    <h6 class="mb-2 fw-bold text-dark"><i class="fa fa-list-check text-primary me-2"></i>Kết quả gửi</h6>
                    <ul class="small mb-0" id="send_accounts_result_list"></ul>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" id="btn_send_accounts" disabled>
                    <i class="fa fa-paper-plane me-1"></i>Gửi cho đơn vị đã chọn
                </button>
            </div>
        </div>
    </div>
</div>
