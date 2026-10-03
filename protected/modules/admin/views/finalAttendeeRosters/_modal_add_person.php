<?php
/**
 * Modal HO thêm người thủ công vào danh sách Vòng Chung Kết.
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
                <h5 class="modal-title"><i class="fa fa-user-plus me-1"></i>Thêm người vào danh sách</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-info py-2 small">
                    <i class="fa fa-info-circle me-1"></i>
                    Hệ thống sẽ tự cấp <strong>mã lucky</strong>, <strong>mã QR thẻ</strong> và
                    <strong>số thẻ</strong> cho người này. Người được thêm ở đây
                    <strong>không bị đồng bộ ghi đè hay xoá</strong> về sau.
                </div>

                <form id="form_add_person" method="post" action="<?php echo $this->createUrl('create'); ?>">
                    <input type="hidden" name="event_id" value="<?php echo (int) $eventId; ?>">
                    <input type="hidden" name="period_id" value="<?php echo (int) $periodId; ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="add_full_name">
                                Họ và tên <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="add_full_name" name="full_name" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="add_property_id">
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
                            <label class="form-label" for="add_role_id">Vai trò người tham dự</label>
                            <?php echo CHtml::dropDownList('role_id', $defaultRoleId, $roleList, array(
                                'class' => 'form-select',
                                'empty' => '-- Dùng vai trò mặc định --',
                                'id'    => 'add_role_id',
                            )); ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="add_staff_code">Mã nhân viên</label>
                            <input type="text" class="form-control" id="add_staff_code" name="staff_code">
                            <div class="form-text">
                                Nên nhập để hệ thống nhận ra trùng người khi đồng bộ.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="add_id_card">Số CCCD</label>
                            <input type="text" class="form-control" id="add_id_card" name="id_card">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="add_phone_number">Số điện thoại</label>
                            <input type="text" class="form-control" id="add_phone_number" name="phone_number">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="add_position">Chức danh</label>
                            <input type="text" class="form-control" id="add_position" name="position">
                            <div class="form-text">Hiện trên thẻ tham dự và email xác nhận.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="add_unit_label">Nhãn in thẻ</label>
                            <input type="text" class="form-control" id="add_unit_label" name="unit_label">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="add_division_name">Bộ phận</label>
                            <input type="text" class="form-control" id="add_division_name" name="division_name">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="add_department_name">Phòng ban</label>
                            <input type="text" class="form-control" id="add_department_name" name="department_name">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="add_shirt_size">Size áo</label>
                            <?php echo CHtml::dropDownList('shirt_size', '', FinalAttendeeRosters::getShirtSizeOptions(), array(
                                'class' => 'form-select',
                                'empty' => '-- Chưa chọn --',
                                'id'    => 'add_shirt_size',
                            )); ?>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="add_note">Ghi chú</label>
                            <textarea class="form-control" id="add_note" name="note" rows="2"></textarea>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" form="form_add_person" class="btn btn-primary" id="btn_add_person_save">
                    <i class="fa fa-save me-1"></i>Thêm người
                </button>
            </div>
        </div>
    </div>
</div>
