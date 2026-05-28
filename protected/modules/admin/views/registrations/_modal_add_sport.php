<!-- Modal Add Sports -->
<div class="modal fade" id="addDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="post" action="<?php echo $this->createUrl('addSportRegistration'); ?>" id="add-sport-form">
                <input type="hidden" name="registration_id" value="<?php echo $model->id; ?>">
                <input type="hidden" name="content_type" id="content_type" value="sports">
                <input type="hidden" name="content_id" id="sport_content_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-futbol-o me-2"></i>Đăng ký thể thao</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Môn thể thao <span class="text-danger">*</span></label>
                                <select class="form-select" id="sport_item_id" name="sport_id">
                                    <option value="">-- Chọn môn thể thao --</option>
                                </select>
                                <div id="sport_selected_name" class="form-control bg-light d-none" style="pointer-events:none;"></div>
                            </div>
                            <div class="mb-3" id="alliance_checkboxes_wrapper">
                                <label class="form-label">Đơn vị liên quân</label>
                                <div id="alliance_checkboxes" class="border rounded p-2" style="max-height:200px;overflow-y:auto;">
                                    <div class="text-muted small">Đang tải...</div>
                                </div>
                                <small class="text-muted">Chọn các đơn vị cùng thi đấu (nếu có)</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tên đội</label>
                                <input type="text" class="form-control" name="team_name" id="sport_team_name" placeholder="Nhập tên đội (nếu có)">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ghi chú</label>
                                <textarea class="form-control" name="note" id="sport_note" rows="2" placeholder="Ghi chú thêm..."></textarea>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Chọn người tham dự <span class="text-danger">*</span></label>
                            <small class="text-muted d-block mb-2">Danh sách người có vai trò "Thi đấu thể thao" của đơn vị</small>
                            <div class="row" id="sport_dual_listbox_wrapper">
                                <div class="col-md-5">
                                    <div class="card h-100">
                                        <div class="card-header py-2">
                                            <small class="fw-bold">Danh sách người tham dự</small>
                                            <input type="text" class="form-control form-control-sm mt-2" id="sport_attendee_search" placeholder="Tìm kiếm...">
                                        </div>
                                        <div class="card-body p-0" style="height:300px;overflow-y:auto;">
                                            <div class="list-group list-group-flush" id="sport_available_attendee_list">
                                                <div class="text-center text-muted p-3">Đang tải...</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary mb-2" id="btn_add_sport_attendee" title="Thêm">
                                        <i class="fa fa-chevron-right"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary mb-2" id="btn_add_all_sport_attendee" title="Thêm tất cả">
                                        <i class="fa fa-angle-double-right"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger mb-2" id="btn_remove_sport_attendee" title="Xóa">
                                        <i class="fa fa-chevron-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="btn_remove_all_sport_attendee" title="Xóa tất cả">
                                        <i class="fa fa-angle-double-left"></i>
                                    </button>
                                </div>
                                <div class="col-md-5">
                                    <div class="card h-100">
                                        <div class="card-header py-2">
                                            <small class="fw-bold">Đã chọn (<span id="sport_selected_count">0</span>)</small>
                                        </div>
                                        <div class="card-body p-0" style="height:340px;overflow-y:auto;">
                                            <div class="list-group list-group-flush" id="sport_selected_attendee_list">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-sm btn-primary" id="btn_add_to_preview">
                        <i class="fa fa-plus me-1"></i>Thêm vào danh sách
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
