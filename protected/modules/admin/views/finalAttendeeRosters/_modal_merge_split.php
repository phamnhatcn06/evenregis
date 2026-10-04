<?php
/**
 * Modal gộp dòng / tách người — Giao diện hiện đại.
 *
 * Hai ca đối nhau, dùng chung một modal với hai tab:
 * - Gộp: một người bị tách thành hai dòng (lần đầu đồng bộ thiếu mã nhân viên).
 * - Tách: hai người trùng tên bị gộp làm một (khoá gộp yếu, không có mã NV lẫn CCCD).
 *
 * @var FinalAttendeeRostersController $this
 */
?>
<div class="modal fade" id="modal_merge_split" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="far-kpi-icon-bubble far-kpi-bubble-info mb-0 me-2" style="width: 34px; height: 34px; font-size: 15px;">
                        <i class="fa fa-code-fork"></i>
                    </span>
                    <span>Gộp dòng / Tách người: <span id="ms_row_name" class="fw-bold text-primary"></span></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>

            <div class="modal-body">
                <ul class="nav nav-pills mb-3" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active px-3 py-2 fw-semibold" data-bs-toggle="tab" data-bs-target="#tab_merge" type="button">
                            <i class="fa fa-compress me-1"></i>Gộp dòng
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link px-3 py-2 fw-semibold" data-bs-toggle="tab" data-bs-target="#tab_split" type="button">
                            <i class="fa fa-expand me-1"></i>Tách người
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="tab_merge">
                        <div class="alert alert-info py-2 small mb-3 border-0 d-flex align-items-center" style="background: rgba(8, 177, 186, 0.1); color: #067c83;">
                            <i class="fa fa-info-circle fa-lg me-2 flex-shrink-0"></i>
                            <div>
                                Dùng khi <strong>một người bị tách thành hai dòng</strong>.
                                Dòng giữ lại giữ mã lucky của mình; nếu nó chưa có mã mà dòng kia đã có
                                thì <strong>mã được chuyển sang</strong>. Dòng bị gộp sẽ bị huỷ tư cách
                                và <strong>mã của nó không cấp lại cho ai</strong>.
                            </div>
                        </div>

                        <form id="form_merge" method="post" action="<?php echo $this->createUrl('merge'); ?>">
                            <input type="hidden" name="keep_id" id="merge_keep_id">

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted mb-1">Dòng giữ lại</label>
                                <input type="text" class="form-control bg-light" id="merge_keep_label" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted mb-1" for="merge_search">
                                    Tìm dòng cần gộp vào dòng trên
                                </label>
                                <div class="far-input-with-icon">
                                    <i class="fa fa-search"></i>
                                    <input type="text" class="form-control" id="merge_search"
                                           placeholder="Nhập tên, mã nhân viên hoặc mã lucky rồi chọn...">
                                </div>
                                <div class="list-group mt-2 d-none shadow-sm" id="merge_results"></div>
                            </div>

                            <div class="alert alert-warning py-2 small d-none border-0" id="merge_selected" style="background: rgba(241, 106, 27, 0.1); color: #92400e;">
                                <i class="fa fa-check me-1"></i>Sẽ gộp <strong id="merge_selected_name"></strong> vào dòng giữ lại.
                                <input type="hidden" name="merge_id" id="merge_merge_id">
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="tab_split">
                        <div class="alert alert-info py-2 small mb-3 border-0 d-flex align-items-center" style="background: rgba(8, 177, 186, 0.1); color: #067c83;">
                            <i class="fa fa-info-circle fa-lg me-2 flex-shrink-0"></i>
                            <div>
                                Dùng khi <strong>hai người khác nhau bị gộp thành một dòng</strong>.
                                Dòng cũ giữ nguyên mã; người được tách ra thành dòng mới và
                                <strong>được cấp mã mới</strong> ở lần cấp mã kế tiếp.
                            </div>
                        </div>

                        <form id="form_split" method="post" action="<?php echo $this->createUrl('split'); ?>">
                            <input type="hidden" name="id" id="split_row_id">

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted mb-1">Chọn bản ghi cần tách ra</label>
                                <div id="split_attendees" class="border rounded-3 p-3 bg-light">
                                    <div class="text-muted small">Dòng này chỉ có một bản ghi, không tách được.</div>
                                </div>
                            </div>

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-muted mb-1" for="split_staff_code">
                                        Mã nhân viên của người được tách
                                    </label>
                                    <input type="text" class="form-control" id="split_staff_code" name="staff_code" placeholder="Mã nhân viên mới">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-muted mb-1" for="split_id_card">
                                        Số CCCD của người được tách
                                    </label>
                                    <input type="text" class="form-control" id="split_id_card" name="id_card" placeholder="Số CCCD mới">
                                </div>
                            </div>
                            <div class="form-text small">
                                <i class="fa fa-lightbulb-o me-1"></i>Phải điền ít nhất một trong hai, nếu không lần đồng bộ sau sẽ gộp lại hai người.
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="far-btn far-btn-primary" id="btn_merge_submit">
                    <i class="fa fa-compress"></i>
                    <span>Gộp dòng</span>
                </button>
                <button type="button" class="far-btn far-btn-primary d-none" id="btn_split_submit">
                    <i class="fa fa-expand"></i>
                    <span>Tách người</span>
                </button>
            </div>
        </div>
    </div>
</div>
