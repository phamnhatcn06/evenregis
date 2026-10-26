<?php
$this->breadcrumbs = array(
    'Khảo sát ý kiến' => $this->createUrl('admin'),
    'Thêm khảo sát',
);
$this->menu = array(
    array(
        'label' => 'Danh sách',
        'labelIcon' => 'Danh sách',
        'url' => $this->createUrl('admin'),
        'color' => 'primary',
        'icon' => 'fa-th',
        'id' => 'btn_manage',
    ),
);
$this->Tabletitle = 'Thêm khảo sát';
?>
<div class="card"><div class="card-body">
    <?php $this->renderPartial('_form', array(
        'model' => $model,
        'eventList' => $eventList,
    )); ?>
</div></div>
