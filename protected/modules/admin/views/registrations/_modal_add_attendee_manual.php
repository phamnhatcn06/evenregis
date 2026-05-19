<!-- Modal Add Attendee Manual (for non-Hotels) -->
<div class="modal fade" id="addAttendeeManualModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="<?php echo $this->createUrl('addAttendeeManual'); ?>" id="add-attendee-manual-form" enctype="multipart/form-data">
                <input type="hidden" name="registration_id" value="<?php echo $model->id; ?>">
                <input type="hidden" name="event_id" value="<?php echo $model->event_id; ?>">
                <input type="hidden" name="property_id" value="<?php echo $model->property_id; ?>">
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
                                <label class="form-label">Chức danh</label>
                                <input type="text" class="form-control" name="position" placeholder="Nhập chức danh">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Vai trò <span class="text-danger">*</span></label>
                                <select class="form-select" name="role_id" required>
                                    <option value="">-- Chọn vai trò --</option>
                                    <?php foreach ($roles as $rId => $rName): ?>
                                        <option value="<?php echo $rId; ?>"><?php echo CHtml::encode($rName); ?></option>
                                    <?php endforeach; ?>
                                </select>
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
                                        <label class="form-label">Phương tiện <span class="text-danger">*</span></label>
                                        <select class="form-select" name="transport_id" required>
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
                                        <label class="form-label">Ngày đến <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control datepicker" name="check_in_date" id="add_check_in_date" required placeholder="dd/mm/yyyy" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Ngày đi <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control datepicker" name="check_out_date" id="add_check_out_date" required placeholder="dd/mm/yyyy" autocomplete="off">
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
                                <label class="form-label">Ảnh chân dung (530x530px) <span class="text-danger">*</span></label>
                                <div id="add_portrait_preview" class="mb-2"></div>
                                <input type="file" class="form-control" name="portrait_file" accept="image/*" required>
                                <small class="text-muted">Ảnh chân dung dùng để in thẻ</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ảnh CCCD mặt trước <span class="text-danger">*</span></label>
                                <div id="add_cccd_front_preview" class="mb-2"></div>
                                <input type="file" class="form-control" name="cccd_front_file" accept="image/*" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ảnh CCCD mặt sau <span class="text-danger">*</span></label>
                                <div id="add_cccd_back_preview" class="mb-2"></div>
                                <input type="file" class="form-control" name="cccd_back_file" accept="image/*" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Hợp đồng lao động</label>
                                <div id="add_contract_preview" class="mb-2"></div>
                                <input type="file" class="form-control" name="contract_file" accept="image/*,.pdf">
                                <small class="text-muted">File ảnh hoặc PDF</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Thêm người tham dự</button>
                </div>
            </form>
        </div>
    </div>
</div>
