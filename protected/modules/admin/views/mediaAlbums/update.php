<?php
$this->breadcrumbs = array(
    'Thư viện' => $this->createUrl('admin'),
    'Cập nhật',
);
$this->menu = array(
    array('label' => 'Danh sách', 'labelIcon' => 'Danh sách', 'url' => $this->createUrl('admin'), 'color' => 'primary', 'icon' => 'fa-th', 'id' => 'btn_manage'),
    array('label' => 'Quản lý mục', 'labelIcon' => 'Quản lý mục', 'url' => $this->createUrl('/admin/mediaItems/admin', array('album_id' => $model->id)), 'color' => 'info', 'icon' => 'fa-images', 'id' => 'btn_items'),
);
$this->Tabletitle = 'Cập nhật album: ' . CHtml::encode($model->title);
?>
<div class="card"><div class="card-body">
    <?php $this->renderPartial('_form', array(
        'model' => $model,
        'eventList' => $eventList,
        'typeOptions' => $typeOptions,
    )); ?>
</div></div>
