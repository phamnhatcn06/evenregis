<?php
$this->breadcrumbs = array(
    'Cuộc thi sắc đẹp' => array('admin'),
    'Thêm mới',
);

$this->menu = array(
    array(
        'label' => 'Danh sách',
        'url' => $this->createUrl('admin'),
        'color' => 'secondary',
        'icon' => 'fa-list',
    ),
);
$this->Tabletitle = 'Thêm cuộc thi sắc đẹp';
?>

<div class="card">
    <div class="card-body">
        <?php $this->renderPartial('_form', array('model' => $model, 'events' => $events)); ?>
    </div>
</div>
