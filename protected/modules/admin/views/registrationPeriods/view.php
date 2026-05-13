<?php
$this->menu = array(
    array(
        'label' => Yii::t('app', 'Manage'),
        'labelIcon' => Yii::t('app', 'Manage'),
        'url' => $this->createUrl('admin'),
        'color' => 'primary',
        'icon' => 'fa-th',
        'id' => 'btn_manage',
    ),
    array(
        'label' => Yii::t('app', 'Create'),
        'labelIcon' => Yii::t('app', 'Create'),
        'url' => $this->createUrl('create'),
        'color' => 'success',
        'icon' => 'fa-plus',
        'id' => 'btn_create',
    ),
    array(
        'label' => Yii::t('app', 'Update'),
        'labelIcon' => Yii::t('app', 'Update'),
        'url' => $this->createUrl('update', array('id' => $model->id)),
        'color' => 'warning',
        'icon' => 'fa-pencil',
        'id' => 'btn_update',
    ),
    array(
        'label' => Yii::t('app', 'Delete'),
        'labelIcon' => Yii::t('app', 'Delete'),
        'url' => $this->createUrl('delete', array('id' => $model->id)),
        'color' => 'danger',
        'icon' => 'fa-trash',
        'id' => 'btn_delete'
    ),
);

$this->breadcrumbs = array(
    'Đợt đăng ký' => array('admin'),
    Yii::t('app', 'View'),
);

$this->Tabletitle = 'Chi tiết đợt đăng ký #' . $model->id;

$now = time();
$isActive = $model->is_active;
$startTime = is_numeric($model->start_time) ? $model->start_time : strtotime($model->start_time);
$endTime = is_numeric($model->end_time) ? $model->end_time : strtotime($model->end_time);
$inPeriod = ($startTime <= $now && $endTime >= $now);
if ($isActive && $inPeriod) {
    $periodStatus = '<span class="badge bg-success"><i class="fa fa-check me-1"></i>Đang mở</span>';
} elseif ($isActive && $startTime > $now) {
    $periodStatus = '<span class="badge bg-info"><i class="fa fa-clock-o me-1"></i>Sắp mở</span>';
} elseif ($isActive && $endTime < $now) {
    $periodStatus = '<span class="badge bg-secondary"><i class="fa fa-times me-1"></i>Đã đóng</span>';
} else {
    $periodStatus = '<span class="badge bg-secondary"><i class="fa fa-ban me-1"></i>Tắt</span>';
}
?>

<?php
$attributes = array(
    array('label' => 'ID', 'value' => $model->id),
    array('label' => 'Sự kiện', 'value' => isset($model->event_name) ? $model->event_name : ''),
    array('label' => 'Tên đợt', 'value' => $model->name),
    array('label' => 'Thời gian bắt đầu', 'value' => $model->start_time ? MyHelper::formatDateTime($model->start_time) : '-'),
    array('label' => 'Thời gian kết thúc', 'value' => $model->end_time ? MyHelper::formatDateTime($model->end_time) : '-'),
    array('label' => 'Tối đa/đơn vị', 'value' => $model->max_per_org ?: 'Không giới hạn'),
    array('label' => 'Trạng thái', 'value' => $periodStatus, 'raw' => true),
    array('label' => 'Ghi chú', 'value' => $model->note ?: '-'),
    array('label' => 'Ngày tạo', 'value' => MyHelper::formatDateTime($model->created_at)),
);

$totalAttrs = count($attributes);
if ($totalAttrs <= 4) {
    $colClass = 'col-12';
    $columns = 1;
} elseif ($totalAttrs <= 8) {
    $colClass = 'col-md-6';
    $columns = 2;
} else {
    $colClass = 'col-md-4';
    $columns = 3;
}
$perColumn = ceil($totalAttrs / $columns);
?>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0"><i class="fa fa-info-circle me-2"></i>Thông tin đợt đăng ký</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <?php for ($col = 0; $col < $columns; $col++): ?>
                <div class="<?php echo $colClass; ?>">
                    <table class="table table-bordered table-striped mb-0">
                        <tbody>
                            <?php
                            $start = $col * $perColumn;
                            $end = min($start + $perColumn, $totalAttrs);
                            for ($i = $start; $i < $end; $i++):
                                $attr = $attributes[$i];
                            ?>
                                <tr>
                                    <th style="width:40%;background:#f8f9fa;"><?php echo CHtml::encode($attr['label']); ?></th>
                                    <td><?php echo isset($attr['raw']) && $attr['raw'] ? $attr['value'] : CHtml::encode($attr['value']); ?></td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</div>
