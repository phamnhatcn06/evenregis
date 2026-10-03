<?php
/**
 * Bộ lọc màn Tổng hợp danh sách VCK.
 *
 * @var FinalAttendeeRostersController $this
 * @var int $eventId
 * @var int $periodId
 * @var array $filterOptions  properties / divisions / departments (BE đã gộp mã cùng tên)
 * @var array $filters        giá trị đang lọc
 * @var int $pageSize
 * @var array $pageSizes
 */

$propertyOptions = array();
foreach ($filterOptions['properties'] as $property) {
    $propertyOptions[$property['id']] = $property['name'];
}

$divisionOptions = array();
foreach ($filterOptions['divisions'] as $division) {
    $divisionOptions[$division['code']] = $division['name']
        . (isset($division['count']) ? ' (' . $division['count'] . ')' : '');
}

$departmentOptions = array();
foreach ($filterOptions['departments'] as $department) {
    $departmentOptions[$department['code']] = $department['name']
        . (isset($department['count']) ? ' (' . $department['count'] . ')' : '');
}
?>
<div class="card mb-3">
    <div class="card-body">
        <form method="get" action="<?php echo $this->createUrl('admin'); ?>" class="row g-2 align-items-end">
            <input type="hidden" name="event_id" value="<?php echo (int) $eventId; ?>">
            <input type="hidden" name="period_id" value="<?php echo (int) $periodId; ?>">

            <div class="col-md-3">
                <label class="form-label">Đơn vị</label>
                <?php echo CHtml::dropDownList('property_id', $filters['property_id'], $propertyOptions, array(
                    'class'  => 'form-select',
                    'empty'  => '-- Tất cả đơn vị --',
                    'id'     => 'filter-property',
                )); ?>
            </div>

            <div class="col-md-3">
                <label class="form-label">Bộ phận</label>
                <?php echo CHtml::dropDownList('division_code', $filters['division_code'], $divisionOptions, array(
                    'class'  => 'form-select',
                    'empty'  => '-- Tất cả bộ phận --',
                    'id'     => 'filter-division',
                )); ?>
            </div>

            <div class="col-md-3">
                <label class="form-label">Phòng ban</label>
                <?php echo CHtml::dropDownList('department_code', $filters['department_code'], $departmentOptions, array(
                    'class'  => 'form-select',
                    'empty'  => '-- Tất cả phòng ban --',
                    'id'     => 'filter-department',
                )); ?>
            </div>

            <div class="col-md-3">
                <label class="form-label">Loại người tham dự</label>
                <?php echo CHtml::dropDownList('attendee_type', $filters['attendee_type'], FinalAttendeeRosters::getTypeOptions(), array(
                    'class' => 'form-select',
                    'empty' => '-- Tất cả --',
                )); ?>
            </div>

            <div class="col-md-2">
                <label class="form-label">Mã lucky</label>
                <?php echo CHtml::dropDownList('has_lucky', $filters['has_lucky'], FinalAttendeeRosters::getLuckyFilterOptions(), array(
                    'class' => 'form-select',
                    'empty' => '-- Tất cả --',
                )); ?>
            </div>

            <div class="col-md-2">
                <label class="form-label">Sửa thủ công</label>
                <?php echo CHtml::dropDownList('has_override', $filters['has_override'], FinalAttendeeRosters::getOverrideFilterOptions(), array(
                    'class' => 'form-select',
                    'empty' => '-- Tất cả --',
                )); ?>
            </div>

            <div class="col-md-3">
                <label class="form-label">Xung đột</label>
                <?php echo CHtml::dropDownList('conflict_flag', $filters['conflict_flag'], FinalAttendeeRosters::getConflictFilterOptions(), array(
                    'class' => 'form-select',
                    'empty' => '-- Tất cả --',
                )); ?>
            </div>

            <div class="col-md-3">
                <label class="form-label">Từ khoá</label>
                <?php echo CHtml::textField('keyword', $filters['keyword'], array(
                    'class'       => 'form-control',
                    'placeholder' => 'Tên, mã nhân viên, CCCD, mã lucky',
                )); ?>
            </div>

            <div class="col-md-2">
                <label class="form-label">Số dòng/trang</label>
                <?php echo CHtml::dropDownList('per_page', $pageSize, array_combine($pageSizes, $pageSizes), array(
                    'class' => 'form-select',
                )); ?>
            </div>

            <div class="col-12">
                <div class="form-check form-check-inline">
                    <?php echo CHtml::checkBox('with_trashed', (string) $filters['with_trashed'] === '1', array(
                        'class' => 'form-check-input',
                        'id'    => 'filter-with-trashed',
                        'value' => 1,
                    )); ?>
                    <label class="form-check-label" for="filter-with-trashed">
                        Hiện cả người đã huỷ tư cách
                    </label>
                </div>
            </div>

            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-search me-1"></i>Tìm kiếm
                </button>
                <a href="<?php echo $this->createUrl('admin', array('event_id' => $eventId, 'period_id' => $periodId)); ?>"
                   class="btn btn-outline-secondary">
                    <i class="fa fa-times me-1"></i>Xoá lọc
                </a>
            </div>
        </form>
    </div>
</div>
