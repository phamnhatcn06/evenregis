<?php
$this->breadcrumbs = array(
    'Đợt tham quan' => $this->createUrl('admin'),
    'Thêm mới',
);
$this->menu = array(
    array(
        'label' => 'Danh sách',
        'url' => $this->createUrl('admin'),
        'color' => 'primary',
        'icon' => 'fa-th',
        'id' => 'btn_manage',
    ),
);
$this->Tabletitle = 'Thêm đợt tham quan';
?>
<div class="card">
    <div class="card-body">
        <?php $this->renderPartial('_form', array('model' => $model, 'eventList' => $eventList)); ?>
    </div>
</div>
