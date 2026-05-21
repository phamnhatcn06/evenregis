<!-- Modal Add Miss Registration -->
<div class="modal fade" id="addMissModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="post" action="<?php echo $this->createUrl('addMissRegistration'); ?>" id="add-miss-form">
                <input type="hidden" name="registration_id" value="<?php echo $model->id; ?>">
                <input type="hidden" name="property_id" value="<?php echo $model->property_id; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Đăng ký thi sắc đẹp</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Cuộc thi <span class="text-danger">*</span></label>
                                <select class="form-select" id="miss_contest_id" name="contest_id" required>
                                    <option value="">-- Chọn cuộc thi --</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Số lượng tối đa</label>
                                <input type="text" class="form-control" id="miss_max_per_org" readonly value="-">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ghi chú</label>
                                <textarea class="form-control" name="note" rows="2" placeholder="Ghi chú thêm..."></textarea>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Chọn thí sinh <span class="text-danger">*</span></label>
                            <div class="row" id="miss_dual_listbox_wrapper" style="display:none;">
                                <div class="col-md-5">
                                    <div class="card h-100">
                                        <div class="card-header py-2">
                                            <small class="fw-bold">Danh sách người tham dự</small>
                                            <input type="text" class="form-control form-control-sm mt-2" id="miss_search" placeholder="Tìm kiếm...">
                                        </div>
                                        <div class="card-body p-0" style="height:300px;overflow-y:auto;">
                                            <div class="list-group list-group-flush" id="miss_available_list">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary mb-2" id="miss_btn_add" title="Thêm">
                                        <i class="fa fa-chevron-right"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary mb-2" id="miss_btn_add_all" title="Thêm tất cả">
                                        <i class="fa fa-angle-double-right"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger mb-2" id="miss_btn_remove" title="Xóa">
                                        <i class="fa fa-chevron-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="miss_btn_remove_all" title="Xóa tất cả">
                                        <i class="fa fa-angle-double-left"></i>
                                    </button>
                                </div>
                                <div class="col-md-5">
                                    <div class="card h-100">
                                        <div class="card-header py-2">
                                            <small class="fw-bold">Đã chọn (<span id="miss_selected_count">0</span>/<span id="miss_max_count">0</span>)</small>
                                        </div>
                                        <div class="card-body p-0" style="height:340px;overflow-y:auto;">
                                            <div class="list-group list-group-flush" id="miss_selected_list">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="miss_placeholder" class="text-center text-muted py-5">
                                <i class="fa fa-star fa-3x mb-3"></i>
                                <p>Vui lòng chọn cuộc thi để hiển thị danh sách thí sinh</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success" id="btn_submit_miss">Đăng ký</button>
                </div>
            </form>
        </div>
    </div>
</div>
