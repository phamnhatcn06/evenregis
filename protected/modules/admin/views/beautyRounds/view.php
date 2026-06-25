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
        'label' => 'Gắn thí sinh',
        'url' => $this->createUrl('assignContestants', array('id' => $model->id)),
        'color' => 'info',
        'icon' => 'fa-user-plus',
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
