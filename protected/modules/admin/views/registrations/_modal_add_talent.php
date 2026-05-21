<!-- Modal Add Talent Registration -->
<div class="modal fade" id="addTalentModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="post" action="<?php echo $this->createUrl('addTalentRegistration'); ?>" id="add-talent-form">
                <input type="hidden" name="registration_id" value="<?php echo $model->id; ?>">
                <input type="hidden" name="property_id" value="<?php echo $model->property_id; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Đăng ký văn nghệ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Thể loại <span class="text-danger">*</span></label>
                                <select class="form-select" id="talent_category_id" name="category_id" required>
                                    <option value="">-- Chọn thể loại --</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tên tiết mục <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="title" id="talent_title" required placeholder="Nhập tên tiết mục...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Thời lượng (phút)</label>
                                <input type="number" class="form-control" name="duration" id="talent_duration" min="1" max="30" placeholder="VD: 5">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ghi chú</label>
                                <textarea class="form-control" name="note" rows="2" placeholder="Ghi chú thêm..."></textarea>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Chọn người biểu diễn <span class="text-danger">*</span></label>
                            <div class="row" id="talent_dual_listbox_wrapper" style="display:none;">
                                <div class="col-md-5">
                                    <div class="card h-100">
                                        <div class="card-header py-2">
                                            <small class="fw-bold">Danh sách người tham dự</small>
                                            <input type="text" class="form-control form-control-sm mt-2" id="talent_search" placeholder="Tìm kiếm...">
                                        </div>
                                        <div class="card-body p-0" style="height:300px;overflow-y:auto;">
                                            <div class="list-group list-group-flush" id="talent_available_list">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary mb-2" id="talent_btn_add" title="Thêm">
                                        <i class="fa fa-chevron-right"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary mb-2" id="talent_btn_add_all" title="Thêm tất cả">
                                        <i class="fa fa-angle-double-right"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger mb-2" id="talent_btn_remove" title="Xóa">
                                        <i class="fa fa-chevron-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="talent_btn_remove_all" title="Xóa tất cả">
                                        <i class="fa fa-angle-double-left"></i>
                                    </button>
                                </div>
                                <div class="col-md-5">
                                    <div class="card h-100">
                                        <div class="card-header py-2">
                                            <small class="fw-bold">Đã chọn (<span id="talent_selected_count">0</span>)</small>
                                        </div>
                                        <div class="card-body p-0" style="height:340px;overflow-y:auto;">
                                            <div class="list-group list-group-flush" id="talent_selected_list">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="talent_placeholder" class="text-center text-muted py-5">
                                <i class="fa fa-music fa-3x mb-3"></i>
                                <p>Vui lòng chọn thể loại để hiển thị danh sách người biểu diễn</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success" id="btn_submit_talent">Đăng ký</button>
                </div>
            </form>
        </div>
    </div>
</div>
