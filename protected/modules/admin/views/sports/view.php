<?php
$this->menu = array(
    array(
        'label' => 'Quản lý',
        'url' => $this->createUrl('admin'),
        'color' => 'primary',
        'icon' => 'fa-th',
        'id' => 'btn_manage',
    ),
    array(
        'label' => 'Thêm mới',
        'url' => $this->createUrl('create'),
        'color' => 'success',
        'icon' => 'fa-plus',
        'id' => 'btn_create',
    ),
    array(
        'label' => 'DS Đội thi đấu',
        'url' => $this->createUrl('/admin/sportTeams/admin', array('SportTeams[sport_id]' => $model->id)),
        'color' => 'info',
        'icon' => 'fa-users',
    ),
    array(
        'label' => 'Cập nhật',
        'url' => $this->createUrl('update', array('id' => $model->id)),
        'color' => 'warning',
        'icon' => 'fa-pencil',
        'id' => 'btn_update',
    ),
    array(
        'label' => 'Xóa',
        'url' => $this->createUrl('delete', array('id' => $model->id)),
        'color' => 'danger',
        'icon' => 'fa-trash',
        'id' => 'btn_delete'
    ),
);

$this->breadcrumbs = array(
    Sports::label(2),
    Yii::t('app', 'View') . ' ' . $model->label(),
);

$this->Tabletitle = Yii::t('app', 'View') . ' ' . $model->label() . ': ' . $model->name;
?>

<?php
$attributes = array(
    array('label' => $model->getAttributeLabel('code'), 'value' => $model->code),
    array('label' => $model->getAttributeLabel('name'), 'value' => $model->name),
    array('label' => $model->getAttributeLabel('type'), 'value' => $model->type),
    array('label' => $model->getAttributeLabel('description'), 'value' => $model->description),
    array('label' => $model->getAttributeLabel('document'), 'value' => $model->document),
    array(
        'label' => $model->getAttributeLabel('is_active'),
        'value' => $model->is_active
            ? '<span class="badge bg-success">Hoạt động</span>'
            : '<span class="badge bg-secondary">Không hoạt động</span>',
        'raw' => true
    ),
    array('label' => $model->getAttributeLabel('sort_order'), 'value' => $model->sort_order),
    array('label' => $model->getAttributeLabel('created_at'), 'value' => MyHelper::formatDateTime($model->created_at)),
    array('label' => $model->getAttributeLabel('updated_at'), 'value' => MyHelper::formatDateTime($model->updated_at)),
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
        <h5 class="mb-0"><i class="fa fa-info-circle me-2"></i>Thông tin môn thể thao</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <?php for ($col = 0; $col < $columns; $col++): ?>
            <div class="<?php echo $colClass; ?>">
                <table class="table table-bordered table-striped">
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

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fa fa-users me-2"></i>Danh sách đội thi đấu (<?php echo $teams->getTotalItemCount(); ?>)</h5>
        <a href="<?php echo $this->createUrl('/admin/sportTeams/admin', array('SportTeams[sport_id]' => $model->id)); ?>" class="btn btn-sm btn-outline-primary">
            <i class="fa fa-list me-1"></i>Xem tất cả
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tên đội</th>
                        <th>Đơn vị</th>
                        <th style="width:100px">Liên quân</th>
                        <th style="width:120px">Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $items = $teams->getData();
                    if (empty($items)):
                    ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">Chưa có đội đăng ký</td></tr>
                    <?php else: foreach ($items as $team): ?>
                    <tr>
                        <td>
                            <a href="<?php echo $this->createUrl('/admin/sportTeams/view', array('id' => $team->id)); ?>">
                                <?php echo CHtml::encode($team->team_name); ?>
                            </a>
                        </td>
                        <td><?php echo CHtml::encode(isset($team->property_name) ? $team->property_name : ''); ?></td>
                        <td><?php echo $team->is_alliance ? '<span class="badge bg-info">Liên quân</span>' : '<span class="badge bg-secondary">Đơn lẻ</span>'; ?></td>
                        <td><?php echo SportTeams::getStatusLabel($team->status); ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
