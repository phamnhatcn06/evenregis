<?php
$this->breadcrumbs = array(
    'Quy trình duyệt' => array('admin'),
    $model->name => array('view', 'id' => $model->id),
    'Cập nhật',
);

$this->menu = array(
    array(
        'label' => 'Danh sách',
        'url' => $this->createUrl('admin'),
        'color' => 'secondary',
        'icon' => 'fa-list',
    ),
    array(
        'label' => 'Chi tiết',
        'url' => $this->createUrl('view', array('id' => $model->id)),
        'color' => 'info',
        'icon' => 'fa-eye',
    ),
);
$this->Tabletitle = 'Cập nhật: ' . CHtml::encode($model->name);
?>

<?php $this->renderPartial('_form', array('model' => $model)); ?>
