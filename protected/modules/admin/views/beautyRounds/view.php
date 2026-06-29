<?php
$this->breadcrumbs = array(
    'Cuộc thi sắc đẹp' => array('/admin/beautyContests/admin'),
    'Vòng thi' => array('admin'),
    $model->name,
);

$this->menu = array(
    array(
        'label' => 'Danh sách',
        'url' => $this->createUrl('admin'),
        'color' => 'secondary',
        'icon' => 'fa-list',
    ),
    array(
        'label' => 'Thêm thí sinh',
        'url' => '#',
        'color' => 'info',
        'icon' => 'fa-user-plus',
        'htmlOptions' => array('data-bs-toggle' => 'modal', 'data-bs-target' => '#modal_assign_contestant'),
    ),
    array(
        'label' => 'Chấm điểm',
        'url' => $this->createUrl('scoring', array('id' => $model->id)),
        'color' => 'warning',
        'icon' => 'fa-star',
    ),
    array(
        'label' => 'Chọn đi tiếp',
        'url' => $this->createUrl('qualify', array('id' => $model->id)),
        'color' => 'success',
        'icon' => 'fa-check-circle',
    ),
    array(
        'label' => 'Cập nhật',
        'url' => $this->createUrl('update', array('id' => $model->id)),
        'color' => 'primary',
        'icon' => 'fa-edit',
    ),
    array(
        'label' => 'Xóa',
        'url' => $this->createUrl('delete', array('id' => $model->id)),
        'color' => 'danger',
        'icon' => 'fa-trash',
        'id' => 'btn_delete'
    ),
);
$this->Tabletitle = 'Chi tiết vòng thi: ' . CHtml::encode($model->name);
?>

<div class="card">
    <div class="card-body">
        <?php
        $attributes = array(
            array('label' => $model->getAttributeLabel('id'), 'value' => $model->id),
            array('label' => $model->getAttributeLabel('contest_id'), 'value' => $model->contest_name),
            array('label' => $model->getAttributeLabel('name'), 'value' => $model->name),
            array(
                'label' => $model->getAttributeLabel('round_type'),
                'value' => BeautyRounds::getRoundTypeLabel($model->round_type)
            ),
            array('label' => $model->getAttributeLabel('round_order'), 'value' => $model->round_order),
            array('label' => $model->getAttributeLabel('max_score'), 'value' => $model->max_score),
            array('label' => $model->getAttributeLabel('weight'), 'value' => $model->weight),
            array(
                'label' => $model->getAttributeLabel('start_time'),
                'value' => $model->start_time ? date('d/m/Y H:i', strtotime($model->start_time)) : ''
            ),
            array(
                'label' => $model->getAttributeLabel('end_time'),
                'value' => $model->end_time ? date('d/m/Y H:i', strtotime($model->end_time)) : ''
            ),
            array('label' => $model->getAttributeLabel('note'), 'value' => $model->note),
            array(
                'label' => $model->getAttributeLabel('created_at'),
                'value' => $model->created_at ? date('d/m/Y H:i', strtotime($model->created_at)) : ''
            ),
        );

        $totalAttrs = count($attributes);
        $colClass = 'col-md-6';
        $columns = 2;
        $perColumn = ceil($totalAttrs / $columns);
        ?>

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

<!-- Danh sách thí sinh trong vòng -->
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fa fa-users me-2"></i>Thí sinh trong vòng (<?php echo count($contestants); ?>)</h5>
        <a href="<?php echo $this->createUrl('assignContestants', array('id' => $model->id)); ?>" class="btn btn-sm btn-info">
            <i class="fa fa-user-plus me-1"></i>Gắn thí sinh
        </a>
    </div>
    <div class="card-body">
        <?php if (empty($contestants)): ?>
            <div class="alert alert-info mb-0">
                <i class="fa fa-info-circle me-2"></i>Chưa có thí sinh nào trong vòng này.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width:60px">STT</th>
                            <th style="width:80px">Ảnh</th>
                            <th>SBD</th>
                            <th>Họ tên</th>
                            <th>Đơn vị</th>
                            <th style="width:100px">Điểm</th>
                            <th style="width:120px">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contestants as $index => $c): ?>
                        <tr>
                            <td class="text-center"><?php echo $index + 1; ?></td>
                            <td class="text-center">
                                <?php if (!empty($c['photo_portrait'])): ?>
                                    <img src="<?php echo CHtml::encode($c['photo_portrait']); ?>"
                                         alt="Ảnh" class="rounded" style="width:50px;height:50px;object-fit:cover;">
                                <?php else: ?>
                                    <span class="text-muted"><i class="fa fa-user fa-2x"></i></span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo CHtml::encode($c['contestant_number']); ?></strong></td>
                            <td><?php echo CHtml::encode($c['contestant_name']); ?></td>
                            <td><?php echo CHtml::encode($c['property_name']); ?></td>
                            <td class="text-center">
                                <span class="badge bg-primary fs-6"><?php echo $c['score'] !== null ? number_format($c['score'], 1) : '-'; ?></span>
                            </td>
                            <td><?php echo BeautyRoundResults::getStatusLabel(isset($c['status']) ? $c['status'] : 0); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
