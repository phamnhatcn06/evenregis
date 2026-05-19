<?php
$this->breadcrumbs = array(
    'Tiết mục văn nghệ' => array('admin'),
    $model->title => array('view', 'id' => $model->id),
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
$this->Tabletitle = 'Cập nhật tiết mục: ' . CHtml::encode($model->title);
?>

<div class="card">
    <div class="card-body">
        <?php echo $this->renderPartial('_form', array(
            'model' => $model,
            'shows' => $shows,
            'categories' => $categories,
            'properties' => $properties,
        )); ?>
    </div>
</div>
