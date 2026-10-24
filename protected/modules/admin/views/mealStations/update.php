<?php
$this->breadcrumbs = array(
    'Trạm chấm ăn' => $this->createUrl('admin'),
    'Cập nhật',
);
$this->menu = array(
    array(
        'label' => 'Danh sách',
        'url' => $this->createUrl('admin', array('event_id' => $model->event_id)),
        'color' => 'primary',
        'icon' => 'fa-th',
        'id' => 'btn_manage',
    ),
);
$this->Tabletitle = 'Cập nhật trạm: ' . $model->name;
?>
<div class="card">
    <div class="card-body">
        <?php $this->renderPartial('_form', array('model' => $model, 'eventList' => $eventList)); ?>
    </div>
</div>
