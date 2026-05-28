<!-- Modal Add Attendee from Staff (for Hotels) -->
<div class="modal fade" id="addAttendeeFromStaffModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="post" action="<?php echo $this->createUrl('addAttendeesFromStaff'); ?>" id="add-attendees-staff-form">
                <input type="hidden" name="registration_id" value="<?php echo $model->id; ?>">
                <input type="hidden" name="event_id" value="<?php echo $model->event_id; ?>">
                <input type="hidden" name="property_id" value="<?php echo $model->property_id; ?>">
                <style>
                    #staff_role_id option:checked, #edit_role_id option:checked {
                        background-color: #0d6efd !important;
                        color: #fff !important;
                    }
                </style>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-users me-2"></i>Chọn nhân viên tham dự</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Vai trò <span class="text-danger">*</span></label>
                            <select class="form-select" name="role_id[]" id="staff_role_id" multiple="multiple" style="height: 120px;" required>
                                <?php foreach ($roles as $rId => $rName): ?>
                                    <option value="<?php echo $rId; ?>"><?php echo CHtml::encode($rName); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted d-block mt-1">Giữ Ctrl để chọn nhiều vai trò</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Ngày đến</label>
                            <input type="text" class="form-control datepicker" name="check_in_date" id="staff_check_in_date" placeholder="dd/mm/yyyy" autocomplete="off">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Ngày đi</label>
                            <input type="text" class="form-control datepicker" name="check_out_date" id="staff_check_out_date" placeholder="-- Chọn ngày đến trước --" autocomplete="off" disabled>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Phương tiện</label>
                            <select class="form-select" name="transport_id" id="staff_transport_id">
                                <option value="">-- Chọn --</option>
                                <?php foreach ($transports as $tId => $tName): ?>
                                    <option value="<?php echo $tId; ?>"><?php echo CHtml::encode($tName); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-5">
                            <div class="card h-100">
                                <div class="card-header py-2">
                                    <small class="fw-bold">Danh sách nhân viên</small>
                                    <p style="color: red;font-size: 12px;font-weight: bold;">Lưu ý: Danh sách chỉ hiển thị những nhân viên có ngày gia nhập trước ngày 01/06/2026</p>
                                    <p style="color: red;font-size: 12px;font-weight: bold;">*Lựa chọn nhưng nhân viên có cùng vài trò, ngày đến, ngày đi, phương tiện</p>
                                    <input type="text" class="form-control form-control-sm mt-2" id="attendee_staff_search" placeholder="Tìm kiếm theo tên, mã NV...">
                                </div>
                                <div class="card-body p-0" style="height:300px;overflow-y:auto;">
                                    <div class="list-group list-group-flush" id="attendee_available_staff_list">
                                        <div class="text-center text-muted py-5">Đang tải danh sách nhân viên...</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-primary mb-2" id="btn_add_attendee_staff" title="Thêm">
                                <i class="fa fa-chevron-right"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary mb-2" id="btn_add_all_attendee_staff" title="Thêm tất cả">
                                <i class="fa fa-angle-double-right"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger mb-2" id="btn_remove_attendee_staff" title="Xóa">
                                <i class="fa fa-chevron-left"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="btn_remove_all_attendee_staff" title="Xóa tất cả">
                                <i class="fa fa-angle-double-left"></i>
                            </button>
                        </div>
                        <div class="col-md-5">
                            <div class="card h-100">
                                <div class="card-header py-2">
                                    <small class="fw-bold">Đã chọn (<span id="attendee_selected_count">0</span>)</small>
                                </div>
                                <div class="card-body p-0" style="height:340px;overflow-y:auto;">
                                    <div class="list-group list-group-flush" id="attendee_selected_staff_list">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-success" id="btn_submit_attendees_staff">Thêm người tham dự</button>
                </div>
            </form>
        </div>
    </div>
</div>