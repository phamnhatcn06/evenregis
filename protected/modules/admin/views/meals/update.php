<?php
$this->breadcrumbs = array(
    'Bữa ăn' => $this->createUrl('admin'),
    'Cấu hình chấm ăn',
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
$this->Tabletitle = 'Cấu hình chấm ăn: ' . $model->name;
?>
<div class="card">
    <div class="card-body">
        <?php $this->renderPartial('_form', array('model' => $model, 'eventList' => $eventList)); ?>
    </div>
</div>
