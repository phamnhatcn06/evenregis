<?php
$this->breadcrumbs = array(
    'Cuộc thi sắc đẹp' => array('admin'),
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
        'label' => 'Cập nhật',
        'url' => $this->createUrl('update', array('id' => $model->id)),
        'color' => 'primary',
        'icon' => 'fa-edit',
    ),
    array(
        'label' => 'Thí sinh',
        'url' => $this->createUrl('/admin/beautyContestants/admin', array('BeautyContestants[contest_id]' => $model->id)),
        'color' => 'info',
        'icon' => 'fa-users',
    ),
    array(
        'label' => 'Xóa',
        'url' => $this->createUrl('delete', array('id' => $model->id)),
        'color' => 'danger',
        'icon' => 'fa-trash',
        'id' => 'btn_delete'
    ),
);
$this->Tabletitle = 'Chi tiết cuộc thi: ' . CHtml::encode($model->name);
?>

<div class="card">
    <div class="card-body">
        <?php
        $attributes = array(
            array('label' => $model->getAttributeLabel('id'), 'value' => $model->id),
            array('label' => $model->getAttributeLabel('name'), 'value' => $model->name),
            array('label' => $model->getAttributeLabel('event_id'), 'value' => $model->event_name),
            array('label' => $model->getAttributeLabel('gender'), 'value' => BeautyContests::getGenderLabel($model->gender)),
            array('label' => $model->getAttributeLabel('age_min'), 'value' => $model->age_min),
            array('label' => $model->getAttributeLabel('age_max'), 'value' => $model->age_max),
            array(
                'label' => $model->getAttributeLabel('registration_open_at'),
                'value' => $model->registration_open_at ? date('d/m/Y H:i', strtotime($model->registration_open_at)) : ''
            ),
            array(
                'label' => $model->getAttributeLabel('registration_close_at'),
                'value' => $model->registration_close_at ? date('d/m/Y H:i', strtotime($model->registration_close_at)) : ''
            ),
            array(
                'label' => $model->getAttributeLabel('contest_date'),
                'value' => $model->contest_date ? date('d/m/Y', strtotime($model->contest_date)) : ''
            ),
            array('label' => $model->getAttributeLabel('location'), 'value' => $model->location),
            array('label' => $model->getAttributeLabel('candidate_prefix'), 'value' => $model->candidate_prefix),
            array('label' => $model->getAttributeLabel('candidate_start'), 'value' => $model->candidate_start),
            array('label' => $model->getAttributeLabel('max_per_org'), 'value' => $model->max_per_org),
            array('label' => $model->getAttributeLabel('description'), 'value' => $model->description),
            array(
                'label' => $model->getAttributeLabel('is_active'),
                'value' => BeautyContests::getActiveLabel($model->is_active),
                'raw' => true
            ),
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
