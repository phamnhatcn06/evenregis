<?php
$this->breadcrumbs = array(
    'Thư viện' => $this->createUrl('admin'),
    'Thêm album',
);
$this->menu = array(
    array('label' => 'Danh sách', 'labelIcon' => 'Danh sách', 'url' => $this->createUrl('admin'), 'color' => 'primary', 'icon' => 'fa-th', 'id' => 'btn_manage'),
);
$this->Tabletitle = 'Thêm album';
?>
<div class="card"><div class="card-body">
    <?php $this->renderPartial('_form', array(
        'model' => $model,
        'eventList' => $eventList,
        'typeOptions' => $typeOptions,
    )); ?>
</div></div>
