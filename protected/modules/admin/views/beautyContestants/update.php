<?php
$this->breadcrumbs = array(
    'Thí sinh Miss' => array('admin'),
    $model->attendee_name => array('view', 'id' => $model->id),
    'Cập nhật',
);

$this->menu = array(
    array(
        'label' => 'Xem chi tiết',
        'url' => $this->createUrl('view', array('id' => $model->id)),
        'color' => 'info',
        'icon' => 'fa-eye',
    ),
    array(
        'label' => 'Danh sách',
        'url' => $this->createUrl('admin'),
        'color' => 'secondary',
        'icon' => 'fa-list',
    ),
);
$this->Tabletitle = 'Cập nhật thí sinh: ' . CHtml::encode($model->attendee_name);
?>

<div class="card">
    <div class="card-body">
        <?php echo $this->renderPartial('_form', array(
            'model' => $model,
            'contests' => $contests,
            'properties' => isset($properties) ? $properties : array(),
        )); ?>
    </div>
</div>
