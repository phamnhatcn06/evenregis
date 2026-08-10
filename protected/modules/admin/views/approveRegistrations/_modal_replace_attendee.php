<?php
/**
 * Modal Thay thế người tham dự.
 * Cột trái: thông tin người bị thay + đội (checkbox kế thừa) + cuộc thi/vai trò (nạp động).
 * Cột phải: chọn người thay (SMILE / thủ công) + ảnh + hồ sơ.
 */
/** @var array $roles */
/** @var array $transports */
/** @var array $staffList */
?>
<div class="modal fade" id="replaceAttendeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fa fa-exchange me-2"></i>Thay thế người tham dự</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <form id="replaceAttendeeForm" enctype="multipart/form-data" class="d-flex flex-column overflow-hidden" style="min-height:0;">
                <div class="modal-body">
                    <input type="hidden" name="attendee_id" id="replace_attendee_id">
                    <input type="hidden" name="registration_id" value="<?php echo $model->id; ?>">
                    <input type="hidden" name="event_id" value="<?php echo $model->event_id; ?>">
                    <input type="hidden" name="property_id" value="<?php echo $model->property_id; ?>">
                    <input type="hidden" name="staff_id" id="replace_staff_id">

                    <div class="row g-3">
                        <!-- Cột trái: người bị thay + nội dung kế thừa -->
                        <div class="col-md-5">
                            <div class="card h-100">
                                <div class="card-header bg-light"><strong><i class="fa fa-user me-1"></i>Người bị thay</strong></div>
                                <div class="card-body">
                                    <p class="mb-1"><strong id="replace_attendee_name">-</strong></p>
                                    <p class="text-muted small mb-2" id="replace_attendee_position">-</p>
                                    <span id="replace_badge_warning" class="d-none badge bg-danger mb-2">Đã in thẻ</span>
                                    <h6 class="text-muted">Nội dung sẽ chuyển sang người thay</h6>
                                    <p class="small text-muted mb-2">Đội <strong>được tích</strong> sẽ chuyển người thay vào; đội <strong>không tích</strong> sẽ bị huỷ.</p>
                                    <div id="replace_summary_container">
                                        <div class="text-center text-muted py-3"><i class="fa fa-spinner fa-spin me-1"></i>Đang tải...</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cột phải: người thay -->
                        <div class="col-md-7">
                            <div class="card h-100">
                                <div class="card-header bg-light"><strong><i class="fa fa-user-plus me-1"></i>Người thay thế</strong></div>
                                <div class="card-body">
                                    <ul class="nav nav-tabs mb-3" role="tablist">
                                        <li class="nav-item"><button class="nav-link active" type="button" data-bs-toggle="tab" data-bs-target="#replace_tab_smile">Chọn từ SMILE</button></li>
                                        <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#replace_tab_manual">Nhập thủ công</button></li>
                                    </ul>
                                    <div class="tab-content mb-3">
                                        <div class="tab-pane fade show active" id="replace_tab_smile">
                                            <label class="form-label">Nhân sự <span class="text-danger">*</span></label>
                                            <select class="form-select" id="replace_staff_select">
                                                <option value="">-- Chọn nhân sự --</option>
                                                <?php foreach ($staffList as $st): ?>
                                                    <option value="<?php echo $st['id']; ?>"
                                                            data-name="<?php echo CHtml::encode($st['name']); ?>"
                                                            data-position="<?php echo CHtml::encode($st['position']); ?>">
                                                        <?php echo CHtml::encode($st['name'] . ($st['code'] ? ' (' . $st['code'] . ')' : '')); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small class="text-muted">Danh sách nhân sự của đơn vị.</small>
                                        </div>
                                        <div class="tab-pane fade" id="replace_tab_manual">
                                            <div class="mb-2">
                                                <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="full_name" id="replace_full_name">
                                            </div>
                                            <div class="row">
                                                <div class="col-6 mb-2">
                                                    <label class="form-label">Chức danh</label>
                                                    <input type="text" class="form-control" name="position" id="replace_position">
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <label class="form-label">Số CCCD/CMND</label>
                                                    <input type="text" class="form-control" name="id_card" id="replace_id_card">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label">Vai trò</label>
                                        <select class="form-select" name="role_id[]" id="replace_role_id" multiple style="height:90px;">
                                            <?php foreach ($roles as $rId => $rName): ?>
                                                <option value="<?php echo $rId; ?>"><?php echo CHtml::encode($rName); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted">Giữ Ctrl để chọn nhiều. Để trống sẽ kế thừa vai trò của người bị thay.</small>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label fw-bold">Ảnh chân dung <span class="text-danger">*</span></label>
                                            <div id="replace_portrait_preview" class="border rounded text-center p-2 mb-1" style="min-height:60px;"></div>
                                            <input type="file" class="form-control form-control-sm" name="portrait_file" accept="image/*"
                                                   onchange="replacePreviewFile(this, 'replace_portrait_preview')">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label fw-bold">Hợp đồng lao động</label>
                                            <div id="replace_contract_preview" class="border rounded text-center p-2 mb-1" style="min-height:60px;"></div>
                                            <input type="file" class="form-control form-control-sm" name="contract_file" accept="image/*,.pdf"
                                                   onchange="replacePreviewFile(this, 'replace_contract_preview')">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label fw-bold">CCCD mặt trước</label>
                                            <div id="replace_cccd_front_preview" class="border rounded text-center p-2 mb-1" style="min-height:60px;"></div>
                                            <input type="file" class="form-control form-control-sm" name="cccd_front_file" accept="image/*"
                                                   onchange="replacePreviewFile(this, 'replace_cccd_front_preview')">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label fw-bold">CCCD mặt sau</label>
                                            <div id="replace_cccd_back_preview" class="border rounded text-center p-2 mb-1" style="min-height:60px;"></div>
                                            <input type="file" class="form-control form-control-sm" name="cccd_back_file" accept="image/*"
                                                   onchange="replacePreviewFile(this, 'replace_cccd_back_preview')">
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label">Lý do thay thế <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="replace_reason" name="reason" rows="2" placeholder="Nhập lý do thay thế..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary" id="btn_submit_replace">
                        <i class="fa fa-exchange me-1"></i>Thực hiện thay thế
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
