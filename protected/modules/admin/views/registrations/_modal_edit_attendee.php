<!-- Modal Edit Attendee -->
<div class="modal fade" id="editAttendeeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" id="edit-attendee-form" enctype="multipart/form-data">
                <input type="hidden" name="attendee_id" id="edit_attendee_id">
                <input type="hidden" name="registration_id" value="<?php echo $model->id; ?>">
                <input type="hidden" id="edit_staff_id">
                <style>
                    #edit_role_id option:checked {
                        background-color: #0d6efd !important;
                        color: #fff !important;
                    }
                </style>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-pencil me-2"></i>Sửa thông tin người tham dự</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Thông báo cho người dùng biết không thể sửa nếu là nhân viên -->
                    <div class="alert alert-info py-2 mb-3 d-none" id="edit_staff_notice">
                        <i class="fa fa-info-circle me-1"></i>Họ tên, chức danh, phòng ban được đồng bộ từ hệ thống nhân sự và không thể chỉnh sửa.
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="full_name" id="edit_full_name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Chức danh</label>
                                <input type="text" class="form-control" name="position" id="edit_position">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phòng ban</label>
                                <input type="text" class="form-control" name="department" id="edit_department">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Vai trò <span class="text-danger">*</span></label>
                                <select class="form-select" name="role_id[]" id="edit_role_id" multiple="multiple" style="height: 120px;" required>
                                    <?php foreach ($roles as $rId => $rName): ?>
                                        <option value="<?php echo $rId; ?>"><?php echo CHtml::encode($rName); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted d-block mt-1">Giữ Ctrl để chọn nhiều vai trò</small>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Ngày vào làm</label>
                                        <input type="text" class="form-control bg-light" id="edit_start_date" readonly>
                                        <small class="text-muted">Dữ liệu từ hệ thống nhân sự</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Phương tiện</label>
                                        <select class="form-select" name="transport_id" id="edit_transport_id">
                                            <option value="">-- Chọn --</option>
                                            <?php foreach ($transports as $tId => $tName): ?>
                                                <option value="<?php echo $tId; ?>"><?php echo CHtml::encode($tName); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Ngày đến</label>
                                        <input type="text" class="form-control datepicker" name="check_in_date" id="edit_check_in_date" placeholder="dd/mm/yyyy" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Ngày đi</label>
                                        <input type="text" class="form-control datepicker" name="check_out_date" id="edit_check_out_date" placeholder="dd/mm/yyyy" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ghi chú</label>
                                <textarea class="form-control" name="note" id="edit_note" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Ảnh chân dung (530x530px)</label>
                                <div id="edit_portrait_preview" class="mb-2"></div>
                                <input type="file" class="form-control" name="portrait_file" accept="image/*">
                                <small class="text-muted">Để trống nếu không thay đổi</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ảnh CCCD mặt trước</label>
                                <div id="edit_cccd_front_preview" class="mb-2"></div>
                                <input type="file" class="form-control" name="cccd_front_file" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ảnh CCCD mặt sau</label>
                                <div id="edit_cccd_back_preview" class="mb-2"></div>
                                <input type="file" class="form-control" name="cccd_back_file" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Hợp đồng lao động</label>
                                <div id="edit_contract_preview" class="mb-2"></div>
                                <input type="file" class="form-control" name="contract_file" accept="image/*,.pdf">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-primary" id="btn_save_attendee">
                        <i class="fa fa-save me-1"></i>Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('btn_save_attendee').addEventListener('click', function(e) {
        const form = document.getElementById('edit-attendee-form');
        const maxFileSize = 5 * 1024 * 1024; // 5MB
        let isValid = true;
        let errorMessage = '';

        const validateFileInput = (inputName, label, previewId, isRequired, allowPdf) => {
            const input = form.querySelector(`input[name="${inputName}"]`);
            if (!input) return;
            const file = input.files[0];
            const preview = document.getElementById(previewId);
            const hasExisting = preview && (preview.innerHTML.trim() !== '');

            if (isRequired && !file && !hasExisting) {
                isValid = false;
                errorMessage += `Vui lòng chọn ${label}.\n`;
                return;
            }

            if (file) {
                let fileName = file.name.toLowerCase();
                let isValidType = fileName.match(/\.(jpg|jpeg|png)$/);
                if (allowPdf && (file.type === 'application/pdf' || fileName.endsWith('.pdf'))) {
                    isValidType = true;
                }

                if (!isValidType) {
                    isValid = false;
                    errorMessage += `${label} không đúng định dạng (chỉ hỗ trợ png, jpg, jpeg${allowPdf ? ', pdf' : ''}).\n`;
                }
                if (file.size > maxFileSize) {
                    isValid = false;
                    errorMessage += `${label} vượt quá kích thước cho phép (tối đa 5MB).\n`;
                }
            }
        };

        validateFileInput('portrait_file', 'Ảnh chân dung', 'edit_portrait_preview', true, false);
        validateFileInput('cccd_front_file', 'Ảnh CCCD mặt trước', 'edit_cccd_front_preview', true, false);
        validateFileInput('cccd_back_file', 'Ảnh CCCD mặt sau', 'edit_cccd_back_preview', true, false);
        validateFileInput('contract_file', 'Hợp đồng lao động', 'edit_contract_preview', false, true);

        if (!isValid) {
            e.preventDefault();
            e.stopImmediatePropagation();
            alert(errorMessage);
        }
    });
</script>