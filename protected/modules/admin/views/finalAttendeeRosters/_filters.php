<?php
/**
 * Bộ lọc màn Tổng hợp danh sách VCK — Giao diện hiện đại.
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

$departmentOptions = array();
foreach ($filterOptions['departments'] as $department) {
    $departmentOptions[$department['code']] = $department['name']
        . (isset($department['count']) ? ' (' . $department['count'] . ')' : '');
}

// Nội dung tham gia: BE đã trả đúng thứ tự sơ đồ (Thể thao → Nghiệp vụ → Văn nghệ → Miss),
// key là content_type (sport/competition/talent/beauty).
$contentOptions = array();
if (!empty($filterOptions['contents'])) {
    foreach ($filterOptions['contents'] as $content) {
        $contentOptions[$content['type']] = $content['name'];
    }
}

// Đếm số tiêu chí lọc đang được áp dụng
$activeCount = 0;
$activeTags  = array();

if (!empty($filters['property_id']) && isset($propertyOptions[$filters['property_id']])) {
    $activeCount++;
    $activeTags[] = 'Đơn vị: ' . $propertyOptions[$filters['property_id']];
}
if (!empty($filters['department_code'])) {
    $activeCount++;
    $deptName = isset($departmentOptions[$filters['department_code']]) ? $departmentOptions[$filters['department_code']] : $filters['department_code'];
    $activeTags[] = 'Phòng ban: ' . $deptName;
}
if ($filters['attendee_type'] !== null && $filters['attendee_type'] !== '') {
    $activeCount++;
    $typeOpts = FinalAttendeeRosters::getTypeOptions();
    $activeTags[] = 'Loại: ' . (isset($typeOpts[$filters['attendee_type']]) ? $typeOpts[$filters['attendee_type']] : $filters['attendee_type']);
}
if ($filters['has_lucky'] !== null && $filters['has_lucky'] !== '') {
    $activeCount++;
    $activeTags[] = $filters['has_lucky'] ? 'Đã có mã lucky' : 'Chưa có mã lucky';
}
if ($filters['has_override'] !== null && $filters['has_override'] !== '') {
    $activeCount++;
    $activeTags[] = $filters['has_override'] ? 'Đã sửa tay' : 'Chưa sửa tay';
}
if (!empty($filters['conflict_flag'])) {
    $activeCount++;
    $conflictOpts = FinalAttendeeRosters::getConflictFilterOptions();
    $activeTags[] = 'Xung đột: ' . (isset($conflictOpts[$filters['conflict_flag']]) ? $conflictOpts[$filters['conflict_flag']] : $filters['conflict_flag']);
}
if (!empty($filters['keyword'])) {
    $activeCount++;
    $activeTags[] = 'Từ khoá: "' . $filters['keyword'] . '"';
}
if (!empty($filters['with_trashed'])) {
    $activeCount++;
    $activeTags[] = 'Hiện người đã huỷ';
}

$hasAdvanced = !empty($filters['department_code'])
    || ($filters['attendee_type'] !== null && $filters['attendee_type'] !== '')
    || ($filters['has_lucky'] !== null && $filters['has_lucky'] !== '')
    || ($filters['has_override'] !== null && $filters['has_override'] !== '')
    || !empty($filters['conflict_flag'])
    || !empty($filters['with_trashed']);
?>

<div class="far-filter-card mb-3">
    <div class="far-filter-header">
        <h6 class="far-filter-title">
            <i class="fa fa-filter text-primary"></i>
            <span>Bộ lọc & Tìm kiếm</span>
            <?php if ($activeCount > 0): ?>
                <span class="badge bg-primary rounded-pill ms-1" style="font-size: 11px;">
                    <?php echo $activeCount; ?> tiêu chí
                </span>
            <?php endif; ?>
        </h6>

        <div class="d-flex align-items-center gap-2">
            <button type="button" class="far-advanced-toggle" data-bs-toggle="collapse"
                    data-bs-target="#advanced-filters-collapse"
                    aria-expanded="<?php echo $hasAdvanced ? 'true' : 'false'; ?>">
                <i class="fa fa-sliders me-1"></i>
                <span>Bộ lọc chi tiết</span>
                <i class="fa fa-chevron-down ms-1" style="font-size: 10px;"></i>
            </button>
            <?php if ($activeCount > 0): ?>
                <a href="<?php echo $this->createUrl('admin', array('event_id' => $eventId, 'period_id' => $periodId)); ?>"
                   class="btn btn-outline-secondary btn-sm py-1 px-2" style="font-size: 12px;" title="Xoá toàn bộ bộ lọc">
                    <i class="fa fa-times me-1"></i>Xoá lọc
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="far-filter-body">
        <form method="get" action="<?php echo $this->createUrl('admin'); ?>" id="form-filters">
            <input type="hidden" name="event_id" value="<?php echo (int) $eventId; ?>">
            <input type="hidden" name="period_id" value="<?php echo (int) $periodId; ?>">

            <!-- Hàng 1: Tìm kiếm nhanh & cơ cấu chính -->
            <div class="row g-2 align-items-end">
                <div class="col-lg-5 col-md-6">
                    <label class="form-label small fw-semibold text-muted mb-1">Từ khoá tìm kiếm</label>
                    <div class="far-input-with-icon">
                        <i class="fa fa-search"></i>
                        <?php echo CHtml::textField('keyword', $filters['keyword'], array(
                            'class'       => 'form-control',
                            'placeholder' => 'Họ tên, SĐT, mã NV, CCCD, mã lucky...',
                        )); ?>
                    </div>
                </div>

                <div class="col-lg-5 col-md-6">
                    <label class="form-label small fw-semibold text-muted mb-1">Đơn vị</label>
                    <?php echo CHtml::dropDownList('property_id', $filters['property_id'], $propertyOptions, array(
                        'class' => 'form-select js-select2',
                        'empty' => '-- Tất cả đơn vị --',
                        'id'    => 'filter-property',
                    )); ?>
                </div>

                <div class="col-lg-2 col-md-12">
                    <button type="submit" class="far-btn far-btn-primary w-100 justify-content-center">
                        <i class="fa fa-search"></i>
                        <span>Tìm kiếm</span>
                    </button>
                </div>
            </div>

            <!-- Hàng 2: Bộ lọc chi tiết (Collapse) -->
            <div class="collapse <?php echo $hasAdvanced ? 'show' : ''; ?> mt-3" id="advanced-filters-collapse">
                <div class="p-3 bg-light rounded-3 border">
                    <div class="row g-2">
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1">Phòng ban</label>
                            <?php echo CHtml::dropDownList('department_code', $filters['department_code'], $departmentOptions, array(
                                'class' => 'form-select form-select-sm',
                                'empty' => '-- Tất cả phòng ban --',
                                'id'    => 'filter-department',
                            )); ?>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <label class="form-label small fw-semibold text-muted mb-1">Loại người tham dự</label>
                            <?php echo CHtml::dropDownList('attendee_type', $filters['attendee_type'], FinalAttendeeRosters::getTypeOptions(), array(
                                'class' => 'form-select form-select-sm',
                                'empty' => '-- Tất cả loại --',
                            )); ?>
                        </div>

                        <div class="col-lg-2 col-md-4">
                            <label class="form-label small fw-semibold text-muted mb-1">Mã lucky</label>
                            <?php echo CHtml::dropDownList('has_lucky', $filters['has_lucky'], FinalAttendeeRosters::getLuckyFilterOptions(), array(
                                'class' => 'form-select form-select-sm',
                                'empty' => '-- Tất cả --',
                            )); ?>
                        </div>

                        <div class="col-lg-2 col-md-4">
                            <label class="form-label small fw-semibold text-muted mb-1">Sửa thủ công</label>
                            <?php echo CHtml::dropDownList('has_override', $filters['has_override'], FinalAttendeeRosters::getOverrideFilterOptions(), array(
                                'class' => 'form-select form-select-sm',
                                'empty' => '-- Tất cả --',
                            )); ?>
                        </div>

                        <div class="col-lg-2 col-md-4">
                            <label class="form-label small fw-semibold text-muted mb-1">Xung đột</label>
                            <?php echo CHtml::dropDownList('conflict_flag', $filters['conflict_flag'], FinalAttendeeRosters::getConflictFilterOptions(), array(
                                'class' => 'form-select form-select-sm',
                                'empty' => '-- Tất cả xung đột --',
                            )); ?>
                        </div>

                        <div class="col-lg-2 col-md-4 mt-2">
                            <label class="form-label small fw-semibold text-muted mb-1">Số dòng / trang</label>
                            <?php echo CHtml::dropDownList('per_page', $pageSize, array_combine($pageSizes, $pageSizes), array(
                                'class' => 'form-select form-select-sm',
                            )); ?>
                        </div>

                        <div class="col-lg-6 col-md-8 mt-2 d-flex align-items-center">
                            <div class="form-check mt-3">
                                <?php echo CHtml::checkBox('with_trashed', (string) $filters['with_trashed'] === '1', array(
                                    'class' => 'form-check-input',
                                    'id'    => 'filter-with-trashed',
                                    'value' => 1,
                                )); ?>
                                <label class="form-check-label small fw-medium text-secondary" for="filter-with-trashed">
                                    <i class="fa fa-user-times text-danger me-1"></i>Hiện cả người đã huỷ tư cách
                                </label>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-12 mt-2 text-end d-flex align-items-center justify-content-end gap-2">
                            <a href="<?php echo $this->createUrl('admin', array('event_id' => $eventId, 'period_id' => $periodId)); ?>"
                               class="btn btn-outline-secondary btn-sm">
                                <i class="fa fa-times me-1"></i>Xoá bộ lọc
                            </a>
                            <button type="submit" class="btn btn-primary btn-sm px-3">
                                <i class="fa fa-check me-1"></i>Áp dụng
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active filter tags -->
            <?php if (!empty($activeTags)): ?>
                <div class="far-active-filters-bar">
                    <span class="small text-muted me-1"><i class="fa fa-tags me-1"></i>Đang lọc:</span>
                    <?php foreach ($activeTags as $tag): ?>
                        <span class="far-filter-tag">
                            <?php echo CHtml::encode($tag); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>
