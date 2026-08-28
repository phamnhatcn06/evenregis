<?php
$this->breadcrumbs = array(
    'Tin tức' => $this->createUrl('admin'),
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
$this->Tabletitle = 'Cập nhật tin tức: ' . CHtml::encode($model->title);
?>
<div class="card"><div class="card-body">
    <?php $this->renderPartial('_form', array(
        'model' => $model,
        'eventList' => $eventList,
        'categoryOptions' => $categoryOptions,
        'newsCategories' => $newsCategories,
    )); ?>
</div></div>
