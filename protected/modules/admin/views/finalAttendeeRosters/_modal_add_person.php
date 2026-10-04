<?php
/**
 * Modal HO thêm người thủ công vào danh sách Vòng Chung Kết — Giao diện hiện đại.
 *
 * BE tạo kèm bản ghi attendee tối thiểu, cấp luôn mã lucky, mã QR và số thẻ — nên người này
 * đăng nhập được cổng chạy ngay. Không có input mã lucky: mã do hệ thống sinh.
 *
 * @var FinalAttendeeRostersController $this
 * @var int $eventId
 * @var int $periodId
 * @var array $filterOptions
 * @var array $roleList
 */

$propertyOptions = array();
foreach ($filterOptions['properties'] as $property) {
    $propertyOptions[$property['id']] = $property['name'];
}

$defaultRoleId = isset(Yii::app()->params['finalRosterManualRoleId'])
    ? Yii::app()->params['finalRosterManualRoleId']
    : null;

// Chưa cấu hình thì chọn sẵn vai trò Ban tổ chức theo tên, để HO không phải tự tìm.
if (empty($defaultRoleId)) {
    foreach ($roleList as $id => $name) {
        if (mb_stripos($name, 'Ban tổ chức') !== false) {
            $defaultRoleId = $id;
            break;
        }
    }
}
?>
<div class="modal fade" id="modal_add_person" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="far-kpi-icon-bubble far-kpi-bubble-success mb-0 me-2" style="width: 34px; height: 34px; font-size: 15px;">
                        <i class="fa fa-user-plus"></i>
                    </span>
                    <span>Thêm người vào danh sách Vòng Chung Kết</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-info py-2 small mb-3 border-0 d-flex align-items-center" style="background: rgba(8, 177, 186, 0.1); color: #067c83;">
                    <i class="fa fa-info-circle fa-lg me-2 flex-shrink-0"></i>
                    <div>
                        Hệ thống sẽ <strong>tự động cấp mã lucky, mã QR và số thẻ</strong>. Người được thêm tại đây <strong>không bị đồng bộ ghi đè hay xoá</strong> về sau.
                    </div>
                </div>

                <form id="form_add_person" method="post" action="<?php echo $this->createUrl('create'); ?>">
                    <input type="hidden" name="event_id" value="<?php echo (int) $eventId; ?>">
                    <input type="hidden" name="period_id" value="<?php echo (int) $periodId; ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1" for="add_full_name">
                                Họ và tên <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="add_full_name" name="full_name" required placeholder="Nhập họ và tên...">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1" for="add_property_id">
                                Đơn vị <span class="text-danger">*</span>
                            </label>
                            <?php echo CHtml::dropDownList('property_id', '', $propertyOptions, array(
                                'class'    => 'form-select',
                                'empty'    => '-- Chọn đơn vị --',
                                'id'       => 'add_property_id',
                                'required' => true,
                            )); ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1" for="add_role_id">Vai trò người tham dự</label>
                            <?php echo CHtml::dropDownList('role_id', $defaultRoleId, $roleList, array(
                                'class' => 'form-select',
                                'empty' => '-- Dùng vai trò mặc định --',
                                'id'    => 'add_role_id',
                            )); ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1" for="add_staff_code">Mã nhân viên</label>
                            <input type="text" class="form-control" id="add_staff_code" name="staff_code" placeholder="Ví dụ: MT12345">
                            <div class="form-text small">
                                <i class="fa fa-lightbulb-o me-1"></i>Nên nhập để hệ thống tự nhận ra trùng người khi đồng bộ.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1" for="add_id_card">Số CCCD</label>
                            <input type="text" class="form-control" id="add_id_card" name="id_card" placeholder="Số CCCD 12 chữ số">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1" for="add_phone_number">Số điện thoại</label>
                            <input type="text" class="form-control" id="add_phone_number" name="phone_number" placeholder="Số điện thoại di động">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1" for="add_position">Chức danh</label>
                            <input type="text" class="form-control" id="add_position" name="position" placeholder="Ví dụ: Giám đốc, Nhân viên...">
                            <div class="form-text small">Hiện trên thẻ tham dự và email xác nhận.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1" for="add_unit_label">Nhãn in thẻ</label>
                            <input type="text" class="form-control" id="add_unit_label" name="unit_label" placeholder="Tên đơn vị ngắn gọn in thẻ">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1" for="add_division_name">Bộ phận</label>
                            <input type="text" class="form-control" id="add_division_name" name="division_name" placeholder="Tên bộ phận">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1" for="add_department_name">Phòng ban</label>
                            <input type="text" class="form-control" id="add_department_name" name="department_name" placeholder="Tên phòng ban">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1" for="add_shirt_size">Size áo</label>
                            <?php echo CHtml::dropDownList('shirt_size', '', FinalAttendeeRosters::getShirtSizeOptions(), array(
                                'class' => 'form-select',
                                'empty' => '-- Chưa chọn --',
                                'id'    => 'add_shirt_size',
                            )); ?>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted mb-1" for="add_note">Ghi chú</label>
                            <textarea class="form-control" id="add_note" name="note" rows="2" placeholder="Ghi chú thêm nếu có..."></textarea>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" form="form_add_person" class="far-btn far-btn-success" id="btn_add_person_save">
                    <i class="fa fa-save"></i>
                    <span>Thêm người</span>
                </button>
            </div>
        </div>
    </div>
</div>
