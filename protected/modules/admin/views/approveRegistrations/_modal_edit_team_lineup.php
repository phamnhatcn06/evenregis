<?php
/**
 * Modal "Sửa đội hình" (chỉ admin toàn quyền) — màn hình duyệt.
 * Dual listbox: trái = người của đơn vị chưa trong đội, phải = thành viên đang trong đội.
 * Thành viên liên quân (đơn vị khác) được giữ nguyên, chỉ hiển thị (khoá).
 *
 * @var Registrations $model
 * @var array $attendees Danh sách người tham dự của phiếu (mảng thô)
 */

$lineupAttendees = array();
foreach ($attendees as $att) {
    $aid = isset($att['id']) ? $att['id'] : null;
    if (!$aid) {
        continue;
    }
    $isActive = isset($att['is_active']) ? (int)$att['is_active'] : 1;
    if ($isActive === 0) {
        continue;
    }
    $lineupAttendees[] = array(
        'id' => (string)$aid,
        'name' => isset($att['full_name']) ? $att['full_name'] : '',
        'position' => isset($att['position_name']) ? $att['position_name'] : (isset($att['position']) ? $att['position'] : ''),
        'approval_status' => isset($att['approval_status']) ? (int)$att['approval_status'] : 0,
    );
}
?>
<div class="modal fade" id="editLineupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fa fa-pencil me-2"></i>Sửa đội hình: <span id="lineup_team_label">-</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <form id="editLineupForm">
                <div class="modal-body">
                    <input type="hidden" name="team_id" id="lineup_team_id">
                    <input type="hidden" name="registration_id" value="<?php echo $model->id; ?>">

                    <div id="lineup_alliance_box" class="alert alert-info py-2 px-3 small d-none">
                        <strong><i class="fa fa-handshake-o me-1"></i>Thành viên liên quân (giữ nguyên):</strong>
                        <span id="lineup_alliance_names"></span>
                    </div>

                    <p class="small text-muted mb-2">Chuyển người của đơn vị sang cột phải để đưa vào đội. Thành viên liên quân từ đơn vị khác không bị ảnh hưởng.</p>

                    <div id="lineup_loading" class="text-center text-muted py-3 d-none">
                        <i class="fa fa-spinner fa-spin me-1"></i>Đang tải đội hình...
                    </div>

                    <div class="row" id="lineup_dual_wrapper">
                        <div class="col-md-5">
                            <div class="card h-100">
                                <div class="card-header py-2">
                                    <small class="fw-bold">Người của đơn vị</small>
                                    <input type="text" class="form-control form-control-sm mt-2" id="lineup_search" placeholder="Tìm theo tên...">
                                </div>
                                <div class="card-body p-0" style="height:320px;overflow-y:auto;">
                                    <div class="list-group list-group-flush" id="lineup_available_list"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-primary mb-2" id="lineup_btn_add" title="Thêm vào đội">
                                <i class="fa fa-chevron-right"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary mb-2" id="lineup_btn_add_all" title="Thêm tất cả">
                                <i class="fa fa-angle-double-right"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger mb-2" id="lineup_btn_remove" title="Gỡ khỏi đội">
                                <i class="fa fa-chevron-left"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="lineup_btn_remove_all" title="Gỡ tất cả">
                                <i class="fa fa-angle-double-left"></i>
                            </button>
                        </div>
                        <div class="col-md-5">
                            <div class="card h-100">
                                <div class="card-header py-2">
                                    <small class="fw-bold">Trong đội (<span id="lineup_count">0</span> người của đơn vị)</small>
                                </div>
                                <div class="card-body p-0" style="height:359px;overflow-y:auto;">
                                    <div class="list-group list-group-flush" id="lineup_selected_list"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary" id="btn_submit_lineup">
                        <i class="fa fa-save me-1"></i>Lưu đội hình
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="application/json" id="lineup_attendees_list"><?php echo CJSON::encode($lineupAttendees); ?></script>
