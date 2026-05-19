<?php
$this->breadcrumbs = array(
    'Đội thể thao' => array('admin'),
    $model->team_name => array('view', 'id' => $model->id),
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
$this->Tabletitle = 'Cập nhật đội: ' . CHtml::encode($model->team_name);
?>

<div class="card">
    <div class="card-body">
        <?php echo $this->renderPartial('_form', array(
            'model' => $model,
            'events' => $events,
            'sports' => $sports,
            'properties' => $properties,
        )); ?>
    </div>
</div>
