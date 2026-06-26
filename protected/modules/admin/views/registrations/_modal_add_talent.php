<!-- Modal Add Talent Registration -->
<div class="modal fade" id="addTalentModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="post" action="<?php echo $this->createUrl('addTalentRegistration'); ?>" id="add-talent-form">
                <input type="hidden" name="registration_id" value="<?php echo $model->id; ?>">
                <input type="hidden" name="property_id" value="<?php echo $model->property_id; ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-music me-2"></i>Đăng ký văn nghệ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Thể loại <span class="text-danger">*</span></label>
                                <select class="form-select" name="category_id" id="talent_category_select" required>
                                    <option value="">-- Chọn thể loại --</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tên tiết mục <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="title" id="talent_title" required placeholder="Nhập tên tiết mục...">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nguồn gốc/Xuất xứ</label>
                                <input type="text" class="form-control" name="origin" id="talent_origin" placeholder="VD: Dân ca Bắc Bộ...">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-success" id="btn_submit_talent">
                        <i class="fa fa-check me-1"></i>Đăng ký
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal chọn đơn vị liên quân cho Văn nghệ -->
<div class="modal fade" id="talentAlliancePropertyModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-users me-2"></i>Chọn đơn vị liên quân</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Chọn các đơn vị cùng biểu diễn tiết mục văn nghệ:</p>
                <div id="talent_alliance_modal_list" style="max-height:300px;overflow-y:auto;">
                    <div class="text-muted small">Đang tải...</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-sm btn-primary" id="btn_confirm_talent_alliance">Xác nhận</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Thêm/Xoá người tham gia (Dual Listbox) -->
<div class="modal fade" id="addTalentMemberModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-users me-2"></i>Thêm người tham gia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="talent_member_entry_id">
                <div class="row" id="talent_member_dual_listbox_wrapper">
                    <!-- Cột trái: Người tham dự có sẵn -->
                    <div class="col-md-5">
                        <div class="card h-100">
                            <div class="card-header py-2 bg-light">
                                <small class="fw-bold">Danh sách người có sẵn</small>
                                <input type="text" class="form-control form-control-sm mt-2" id="talent_member_available_search" placeholder="Tìm kiếm...">
                            </div>
                            <div class="card-body p-0" style="height:300px;overflow-y:auto;">
                                <div class="list-group list-group-flush" id="talent_member_available_list">
                                    <div class="text-center text-muted p-3"><i class="fa fa-spinner fa-spin"></i> Đang tải...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Cột giữa: Nút chuyển đổi -->
                    <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                        <button type="button" class="btn btn-sm btn-outline-primary mb-2" id="btn_add_talent_member_item" title="Thêm">
                            <i class="fa fa-chevron-right"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary mb-2" id="btn_add_all_talent_member_item" title="Thêm tất cả">
                            <i class="fa fa-angle-double-right"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger mb-2" id="btn_remove_talent_member_item" title="Xóa">
                            <i class="fa fa-chevron-left"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" id="btn_remove_all_talent_member_item" title="Xóa tất cả">
                            <i class="fa fa-angle-double-left"></i>
                        </button>
                    </div>
                    <!-- Cột phải: Người được chọn -->
                    <div class="col-md-5">
                        <div class="card h-100">
                            <div class="card-header py-2 bg-light">
                                <small class="fw-bold">Đã chọn tham gia</small>
                                <input type="text" class="form-control form-control-sm mt-2" id="talent_member_selected_search" placeholder="Tìm kiếm...">
                            </div>
                            <div class="card-body p-0" style="height:300px;overflow-y:auto;">
                                <div class="list-group list-group-flush" id="talent_member_selected_list">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-sm btn-primary" id="btn_save_talent_members">
                    <i class="fa fa-save me-1"></i>Xác nhận
                </button>
            </div>
        </div>
    </div>
</div>