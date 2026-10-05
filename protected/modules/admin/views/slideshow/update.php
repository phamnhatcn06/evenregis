<?php
$this->breadcrumbs = array(
    'Slideshow' => $this->createUrl('admin'),
    'Cập nhật',
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
    array(
        'label' => 'Xem',
        'labelIcon' => 'Xem',
        'url' => $this->createUrl('view', array('id' => $model->id)),
        'color' => 'info',
        'icon' => 'fa-eye',
        'id' => 'btn_view',
    ),
);
$this->Tabletitle = 'Cập nhật slide: ' . CHtml::encode($model->title);
?>
<div class="card"><div class="card-body">
    <?php $this->renderPartial('_form', array(
        'model' => $model,
        'eventList' => $eventList,
        'themeOptions' => $themeOptions,
    )); ?>
</div></div>
