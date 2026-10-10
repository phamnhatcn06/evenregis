<?php
/**
 * Modal sửa thủ công toàn bộ trường của một người — Giao diện hiện đại.
 *
 * Form rỗng khi render; JS nạp dữ liệu dòng đang sửa vào các input khi mở modal.
 * Cố ý KHÔNG có input cho `lucky_number` — mã đã cấp không bao giờ đổi.
 *
 * @var FinalAttendeeRostersController $this
 */

$textFields = array(
    'full_name'       => 'Họ và tên',
    'staff_code'      => 'Mã nhân viên',
    'id_card'         => 'Số CCCD',
    'phone_number'    => 'Số điện thoại',
    'email'           => 'Email',
    'badge_org_name'  => 'Đơn vị',
    'unit_label'      => 'Nhãn in thẻ',
    'division_name'   => 'Bộ phận',
    'department_name' => 'Phòng ban',
    'position'        => 'Chức danh',
);
?>
<div class="modal fade" id="modal_edit_row" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="far-kpi-icon-bubble far-kpi-bubble-primary mb-0 me-2" style="width: 34px; height: 34px; font-size: 15px;">
                        <i class="fa fa-pencil"></i>
                    </span>
                    <span>Sửa thông tin: <span id="edit_row_name" class="fw-bold text-primary"></span></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-warning py-2 small mb-3 border-0 d-flex align-items-center" style="background: rgba(241, 106, 27, 0.1); color: #92400e;">
                    <i class="fa fa-exclamation-triangle fa-lg me-2 flex-shrink-0"></i>
                    <div>
                        Trường bạn sửa ở đây sẽ <strong>không bị đồng bộ ghi đè</strong> về sau.
                        Riêng <strong>Chức danh</strong> và <strong>Nhãn in thẻ</strong> còn áp dụng
                        cả trên <strong>thẻ tham dự</strong> và <strong>email xác nhận</strong>.
                    </div>
                </div>

                <form id="form_edit_row" method="post" action="<?php echo $this->createUrl('updateField'); ?>">
                    <input type="hidden" name="id" id="edit_row_id" value="">

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted mb-1">Ảnh chân dung (ảnh thẻ)</label>
                            <div class="d-flex align-items-center gap-3 p-2 border rounded-3 bg-light">
                                <img id="edit_portrait_preview" src="" alt="Ảnh chân dung"
                                     class="rounded-3 border bg-white"
                                     style="width: 72px; height: 96px; object-fit: cover; display: none;">
                                <span id="edit_portrait_placeholder"
                                      class="d-inline-flex align-items-center justify-content-center rounded-3 border bg-white text-muted"
                                      style="width: 72px; height: 96px;">
                                    <i class="fa fa-user fa-2x"></i>
                                </span>
                                <div class="flex-grow-1">
                                    <input type="file" class="form-control form-control-sm" id="edit_portrait_file"
                                           name="portrait_file" accept="image/jpeg,image/png">
                                    <div class="form-text small mb-0">
                                        Chọn ảnh JPG/PNG để thay ảnh thẻ. Để trống nếu không đổi ảnh.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php foreach ($textFields as $field => $label): ?>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1" for="edit_<?php echo $field; ?>">
                                <?php echo $label; ?>
                                <span class="badge badge-subtle-warning ms-1 d-none"
                                      id="badge_<?php echo $field; ?>">đã sửa tay</span>
                            </label>
                            <div class="input-group">
                                <input type="text" class="form-control js-edit-field"
                                       id="edit_<?php echo $field; ?>"
                                       name="fields[<?php echo $field; ?>]"
                                       data-field="<?php echo $field; ?>">
                                <button type="button"
                                        class="btn btn-outline-secondary js-reset-field d-none"
                                        data-field="<?php echo $field; ?>"
                                        title="Khôi phục về giá trị gốc">
                                    <i class="fa fa-undo"></i>
                                </button>
                            </div>
                            <div class="form-text d-none text-muted small" id="origin_<?php echo $field; ?>"></div>
                        </div>
                        <?php endforeach; ?>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1" for="edit_shirt_size">
                                Size áo
                                <span class="badge badge-subtle-warning ms-1 d-none" id="badge_shirt_size">đã sửa tay</span>
                            </label>
                            <div class="input-group">
                                <?php echo CHtml::dropDownList('fields[shirt_size]', '', FinalAttendeeRosters::getShirtSizeOptions(), array(
                                    'class'      => 'form-select js-edit-field',
                                    'id'         => 'edit_shirt_size',
                                    'empty'      => '-- Chưa chọn --',
                                    'data-field' => 'shirt_size',
                                )); ?>
                                <button type="button" class="btn btn-outline-secondary js-reset-field d-none"
                                        data-field="shirt_size" title="Khôi phục về giá trị gốc">
                                    <i class="fa fa-undo"></i>
                                </button>
                            </div>
                            <div class="form-text d-none text-muted small" id="origin_shirt_size"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1" for="edit_attendee_type">
                                Loại người tham dự
                                <span class="badge badge-subtle-warning ms-1 d-none" id="badge_attendee_type">đã sửa tay</span>
                            </label>
                            <div class="input-group">
                                <?php echo CHtml::dropDownList('fields[attendee_type]', '', FinalAttendeeRosters::getTypeOptions(), array(
                                    'class'      => 'form-select js-edit-field',
                                    'id'         => 'edit_attendee_type',
                                    'empty'      => '-- Chưa chọn --',
                                    'data-field' => 'attendee_type',
                                )); ?>
                                <button type="button" class="btn btn-outline-secondary js-reset-field d-none"
                                        data-field="attendee_type" title="Khôi phục về giá trị gốc">
                                    <i class="fa fa-undo"></i>
                                </button>
                            </div>
                            <div class="form-text d-none text-muted small" id="origin_attendee_type"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1" for="edit_is_btc">
                                Ban tổ chức
                                <span class="badge badge-subtle-warning ms-1 d-none" id="badge_is_btc">đã sửa tay</span>
                            </label>
                            <div class="input-group">
                                <?php echo CHtml::dropDownList('fields[is_btc]', '', array(0 => 'Không', 1 => 'Ban tổ chức'), array(
                                    'class'      => 'form-select js-edit-field',
                                    'id'         => 'edit_is_btc',
                                    'data-field' => 'is_btc',
                                )); ?>
                                <button type="button" class="btn btn-outline-secondary js-reset-field d-none"
                                        data-field="is_btc" title="Khôi phục về giá trị gốc">
                                    <i class="fa fa-undo"></i>
                                </button>
                            </div>
                            <div class="form-text text-muted small">Quyết định dùng phôi thẻ BTC hay KS khi xuất ảnh.</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted mb-1" for="edit_note">
                                Ghi chú
                                <span class="badge badge-subtle-warning ms-1 d-none" id="badge_note">đã sửa tay</span>
                            </label>
                            <div class="input-group">
                                <textarea class="form-control js-edit-field" id="edit_note"
                                          name="fields[note]" data-field="note" rows="2"></textarea>
                                <button type="button" class="btn btn-outline-secondary js-reset-field d-none"
                                        data-field="note" title="Khôi phục về giá trị gốc">
                                    <i class="fa fa-undo"></i>
                                </button>
                            </div>
                            <div class="form-text d-none text-muted small" id="origin_note"></div>
                        </div>
                    </div>
                </form>

                <div class="mt-3 small text-muted p-2 bg-light rounded-3 border" id="edit_row_meta"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger me-auto d-none" id="btn_reset_all">
                    <i class="fa fa-undo me-1"></i>Khôi phục toàn bộ về gốc
                </button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" form="form_edit_row" class="far-btn far-btn-primary" id="btn_edit_row_save">
                    <i class="fa fa-save"></i>
                    <span>Lưu thay đổi</span>
                </button>
            </div>
        </div>
    </div>
</div>
