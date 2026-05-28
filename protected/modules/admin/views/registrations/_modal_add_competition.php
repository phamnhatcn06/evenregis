<!-- Modal Add Competition Registration -->
<div class="modal fade" id="addCompetitionModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="post" action="<?php echo $this->createUrl('addCompetitionRegistration'); ?>" id="add-competition-form">
                <input type="hidden" name="registration_id" value="<?php echo $model->id; ?>">
                <input type="hidden" name="content_id" id="comp_content_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title">Đăng ký thi nghiệp vụ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Cuộc thi <span class="text-danger">*</span></label>
                                <select class="form-select" id="comp_competition_id" name="competition_id" required>
                                    <option value="">-- Chọn cuộc thi --</option>
                                </select>
                            </div>
                            <input type="hidden" name="property_id" value="<?php echo $model->property_id; ?>">
                            <div class="mb-3">
                                <label class="form-label">Số lượng tối đa</label>
                                <input type="text" class="form-control" id="comp_max_per_org" readonly value="-">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ghi chú</label>
                                <textarea class="form-control" name="note" rows="2" placeholder="Ghi chú thêm..."></textarea>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Chọn nhân viên tham dự <span class="text-danger">*</span></label>
                            <div class="row" id="dual_listbox_wrapper" style="display:none;">
                                <div class="col-md-5">
                                    <div class="card h-100">
                                        <div class="card-header py-2">
                                            <small class="fw-bold">Danh sách nhân viên</small>
                                            <input type="text" class="form-control form-control-sm mt-2" id="staff_search" placeholder="Tìm kiếm...">
                                        </div>
                                        <div class="card-body p-0" style="height:300px;overflow-y:auto;">
                                            <div class="list-group list-group-flush" id="available_staff_list">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary mb-2" id="btn_add_staff" title="Thêm">
                                        <i class="fa fa-chevron-right"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary mb-2" id="btn_add_all_staff" title="Thêm tất cả">
                                        <i class="fa fa-angle-double-right"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger mb-2" id="btn_remove_staff" title="Xóa">
                                        <i class="fa fa-chevron-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="btn_remove_all_staff" title="Xóa tất cả">
                                        <i class="fa fa-angle-double-left"></i>
                                    </button>
                                </div>
                                <div class="col-md-5">
                                    <div class="card h-100">
                                        <div class="card-header py-2">
                                            <small class="fw-bold">Đã chọn (<span id="selected_count">0</span>/<span id="max_count">0</span>)</small>
                                        </div>
                                        <div class="card-body p-0" style="height:340px;overflow-y:auto;">
                                            <div class="list-group list-group-flush" id="selected_staff_list">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="staff_placeholder" class="text-center text-muted py-5">
                                <i class="fa fa-users fa-3x mb-3"></i>
                                <p>Vui lòng chọn cuộc thi và đơn vị để hiển thị danh sách nhân viên</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-success" id="btn_submit_competition">Đăng ký</button>
                </div>
            </form>
        </div>
    </div>
</div>
