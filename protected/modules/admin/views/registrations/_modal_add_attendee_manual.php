<!-- Modal Add Attendee Manual (for non-Hotels) -->
<div class="modal fade" id="addAttendeeManualModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="<?php echo $this->createUrl('addAttendeeManual'); ?>" id="add-attendee-manual-form" enctype="multipart/form-data">
                <input type="hidden" name="registration_id" value="<?php echo $model->id; ?>">
                <input type="hidden" name="event_id" value="<?php echo $model->event_id; ?>">
                <input type="hidden" name="property_id" value="<?php echo $model->property_id; ?>">
                <style>
                    #addAttendeeManualModal select[multiple] option:checked {
                        background-color: #0d6efd !important;
                        color: #fff !important;
                    }
                </style>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-user-plus me-2"></i>Thêm người tham dự</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="full_name" required placeholder="Nhập họ và tên">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phòng ban <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="department" required id="edit_department">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Chức danh <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="position" required placeholder="Nhập chức danh">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Vai trò <span class="text-danger">*</span></label>
                                <select class="form-select" name="role_id[]" multiple="multiple" style="height: 120px;" required>
                                    <?php foreach ($roles as $rId => $rName): ?>
                                        <option value="<?php echo $rId; ?>"><?php echo CHtml::encode($rName); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted d-block mt-1">Giữ Ctrl để chọn nhiều vai trò</small>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Ngày vào làm <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control datepicker" name="start_date" id="add_start_date" required placeholder="dd/mm/yyyy" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Phương tiện</label>
                                        <select class="form-select" name="transport_id">
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
                                        <input type="text" class="form-control datepicker" name="check_in_date" id="add_check_in_date" placeholder="dd/mm/yyyy" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Ngày đi</label>
                                        <input type="text" class="form-control datepicker" name="check_out_date" id="add_check_out_date" placeholder="dd/mm/yyyy" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ghi chú</label>
                                <textarea class="form-control" name="note" rows="2" placeholder="Ghi chú thêm..."></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Ảnh chân dung (530x530px) <span class="text-danger">*</span></label>
                                <div id="add_portrait_preview" class="premium-preview-box"></div>
                                <div class="premium-upload-zone">
                                    <input type="file" name="portrait_file" accept="image/*" required>
                                    <div class="upload-info">
                                        <i class="fa fa-user-circle-o upload-icon"></i>
                                        <div class="upload-title">Kéo thả ảnh hoặc click để chọn</div>
                                        <div class="upload-hint">Định dạng ảnh (Tối đa 5MB)</div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Ảnh CCCD mặt trước <span class="text-danger">*</span></label>
                                <div id="add_cccd_front_preview" class="premium-preview-box"></div>
                                <div class="premium-upload-zone">
                                    <input type="file" name="cccd_front_file" accept="image/*" required>
                                    <div class="upload-info">
                                        <i class="fa fa-id-card upload-icon"></i>
                                        <div class="upload-title">Kéo thả ảnh hoặc click để chọn</div>
                                        <div class="upload-hint">Mặt trước CCCD (Tối đa 5MB)</div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Ảnh CCCD mặt sau <span class="text-danger">*</span></label>
                                <div id="add_cccd_back_preview" class="premium-preview-box"></div>
                                <div class="premium-upload-zone">
                                    <input type="file" name="cccd_back_file" accept="image/*" required>
                                    <div class="upload-info">
                                        <i class="fa fa-id-card upload-icon"></i>
                                        <div class="upload-title">Kéo thả ảnh hoặc click để chọn</div>
                                        <div class="upload-hint">Mặt sau CCCD (Tối đa 5MB)</div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Hợp đồng lao động</label>
                                <div id="add_contract_preview" class="premium-preview-box"></div>
                                <div class="premium-upload-zone">
                                    <input type="file" name="contract_file" accept="image/*,.pdf">
                                    <div class="upload-info">
                                        <i class="fa fa-file-text-o upload-icon"></i>
                                        <div class="upload-title">Kéo thả file hoặc click để chọn</div>
                                        <div class="upload-hint">Hỗ trợ JPG, PNG hoặc PDF (Tối đa 5MB)</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-success" id="btn_submit_attendee_manual">
                        <i class="fa fa-plus me-1"></i>Thêm người tham dự
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('btn_submit_attendee_manual').addEventListener('click', function(e) {
    const form = document.getElementById('add-attendee-manual-form');
    const maxFileSize = 5 * 1024 * 1024; // 5MB
    let isValid = true;
    let errorMessage = '';

    const validateFileInput = (inputName, label, isRequired, allowPdf) => {
        const input = form.querySelector(`input[name="${inputName}"]`);
        if (!input) return;
        const file = input.files[0];
        
        if (isRequired && !file) {
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

    validateFileInput('portrait_file', 'Ảnh chân dung', true, false);
    validateFileInput('cccd_front_file', 'Ảnh CCCD mặt trước', true, false);
    validateFileInput('cccd_back_file', 'Ảnh CCCD mặt sau', true, false);
    validateFileInput('contract_file', 'Hợp đồng lao động', false, true);

    if (!isValid) {
        e.preventDefault();
        e.stopImmediatePropagation();
        alert(errorMessage);
    }
});
</script>