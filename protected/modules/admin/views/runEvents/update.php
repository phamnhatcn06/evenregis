<?php
$this->breadcrumbs = array(
    'Nội dung chạy' => $this->createUrl('admin'),
    'Cập nhật',
);
$this->menu = array(
    array(
        'label' => 'Danh sách',
        'url' => $this->createUrl('admin'),
        'color' => 'primary',
        'icon' => 'fa-th',
        'id' => 'btn_manage',
    ),
    array(
        'label' => 'Xem',
        'url' => $this->createUrl('view', array('id' => $model->id)),
        'color' => 'info',
        'icon' => 'fa-eye',
        'id' => 'btn_view',
    ),
);
$this->Tabletitle = 'Cập nhật nội dung chạy';
?>
<div class="card">
    <div class="card-body">
        <?php $this->renderPartial('_form', array('model' => $model, 'eventList' => $eventList)); ?>
    </div>
</div>
