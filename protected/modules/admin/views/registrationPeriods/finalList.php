<?php
$this->menu = array(
    array(
        'label' => 'Chi tiết đợt',
        'url' => $this->createUrl('view', array('id' => $model->id)),
        'color' => 'info',
        'icon' => 'fa-eye',
        'id' => 'btn_view',
    ),
    array(
        'label' => 'Quản lý',
        'url' => $this->createUrl('admin'),
        'color' => 'primary',
        'icon' => 'fa-th',
        'id' => 'btn_manage',
    ),
);

$this->breadcrumbs = array(
    'Đợt đăng ký' => array('admin'),
    $model->name => array('view', 'id' => $model->id),
    'Danh sách VCK',
);

$this->Tabletitle = 'Danh sách Vòng chung kết - ' . $model->name;

// Nhóm attendee theo đơn vị
$grouped = array();
foreach ($attendees as $a) {
    $pid = isset($a['property_id']) ? $a['property_id'] : 0;
    $grouped[$pid][] = $a;
}

$typeBadge = function ($type) {
    switch ($type) {
        case 'director':
            return '<span class="badge bg-primary"><i class="fa fa-user-tie me-1"></i>Giám đốc</span>';
        case 'driver':
            return '<span class="badge bg-info text-dark"><i class="fa fa-car me-1"></i>Lái xe</span>';
        default:
            return '<span class="badge bg-success"><i class="fa fa-trophy me-1"></i>Finalist</span>';
    }
};

$contentBadges = function ($contents) {
    if (empty($contents)) {
        return '<span class="text-muted">-</span>';
    }
    $html = '';
    foreach ($contents as $c) {
        $name = isset($c['ref_name']) ? $c['ref_name'] : (isset($c['content_type']) ? $c['content_type'] : '');
        $html .= '<span class="badge bg-light text-dark border me-1">' . CHtml::encode($name) . '</span>';
    }
    return $html;
};
?>

<div class="alert alert-warning">
    <i class="fa fa-lock me-1"></i>
    Thông tin finalist đã chốt <strong>không được chỉnh sửa</strong>, chỉ được cập nhật <strong>ảnh</strong> và <strong>chức danh</strong>.
    Mỗi đơn vị có thể bổ sung <strong>1 giám đốc</strong> (phòng ban 610) và <strong>1 lái xe</strong>.
</div>

<?php if (empty($grouped)): ?>
    <div class="card"><div class="card-body">
        <p class="text-muted mb-0"><i class="fa fa-info-circle me-1"></i>Chưa có người tham dự VCK. Hãy dùng nút "Tổng hợp finalist" ở trang chi tiết đợt.</p>
    </div></div>
<?php else: ?>
    <?php foreach ($grouped as $pid => $rows):
        $propName = isset($propertyNames[$pid]) ? $propertyNames[$pid] : ('Đơn vị #' . $pid);
    ?>
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fa fa-building me-2"></i><?php echo CHtml::encode($propName); ?>
                <span class="badge bg-secondary ms-2"><?php echo count($rows); ?> người</span>
            </h6>
            <div>
                <button type="button" class="btn btn-sm btn-primary btn-add-director"
                    data-property-id="<?php echo $pid; ?>" data-property-name="<?php echo CHtml::encode($propName); ?>">
                    <i class="fa fa-user-tie me-1"></i>Thêm giám đốc
                </button>
                <button type="button" class="btn btn-sm btn-info btn-add-driver"
                    data-property-id="<?php echo $pid; ?>" data-property-name="<?php echo CHtml::encode($propName); ?>">
                    <i class="fa fa-car me-1"></i>Thêm lái xe
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-striped mb-0">
                <thead>
                    <tr>
                        <th style="width:50px">#</th>
                        <th>Họ tên</th>
                        <th>Chức danh</th>
                        <th>Loại</th>
                        <th>Nội dung VCK</th>
                        <th style="width:90px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $i => $a): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td>
                            <?php echo CHtml::encode(isset($a['full_name']) ? $a['full_name'] : ''); ?>
                            <?php if (!empty($a['is_locked'])): ?>
                                <i class="fa fa-lock text-warning ms-1" title="Đã chốt - chỉ sửa ảnh/chức danh"></i>
                            <?php endif; ?>
                        </td>
                        <td><?php echo CHtml::encode(isset($a['position']) ? $a['position'] : ''); ?></td>
                        <td><?php echo $typeBadge(isset($a['attendee_type']) ? $a['attendee_type'] : ''); ?></td>
                        <td><?php echo $contentBadges(isset($a['contents']) ? $a['contents'] : array()); ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-warning btn-edit-attendee"
                                data-id="<?php echo $a['id']; ?>"
                                data-position="<?php echo CHtml::encode(isset($a['position']) ? $a['position'] : ''); ?>"
                                data-photo="<?php echo CHtml::encode(isset($a['photo_path']) ? $a['photo_path'] : ''); ?>"
                                title="Sửa ảnh / chức danh">
                                <i class="fa fa-pencil"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php
$this->renderPartial('_modal_edit_final_attendee', array('model' => $model));
$this->renderPartial('_modal_add_director', array('model' => $model));
$this->renderPartial('_modal_add_driver', array('model' => $model));

Yii::app()->clientScript->registerScript('final-list-config', 'window.FINAL_LIST = ' . CJSON::encode(array(
    'updateUrl' => $this->createUrl('finalUpdateAttendee', array('id' => '__ID__')),
    'candidatesUrl' => $this->createUrl('finalDirectorCandidates'),
    'addSupportUrl' => $this->createUrl('finalAddSupport', array('id' => $model->id)),
)) . ';', CClientScript::POS_HEAD);

Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/registrationPeriods-finalList.js',
    CClientScript::POS_END
);
?>
