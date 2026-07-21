<?php
$this->breadcrumbs = array(
    'Cuộc thi văn nghệ' => array('/admin/talentShows/admin'),
    'Vòng thi' => array('admin'),
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
        'label' => 'Xem chi tiết',
        'url' => $this->createUrl('view', array('id' => $model->id)),
        'color' => 'info',
        'icon' => 'fa-eye',
    ),
);
$this->Tabletitle = 'Cập nhật: ' . CHtml::encode($model->name);
?>

<div class="card">
    <div class="card-body">
        <?php $this->renderPartial('_form', array('model' => $model, 'talentShows' => $talentShows)); ?>
    </div>
</div>
