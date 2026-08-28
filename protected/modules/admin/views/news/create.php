<?php
$this->breadcrumbs = array(
    'Tin tức' => $this->createUrl('admin'),
    'Thêm tin tức',
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
$this->Tabletitle = 'Thêm tin tức';
?>
<div class="card"><div class="card-body">
    <?php $this->renderPartial('_form', array(
        'model' => $model,
        'eventList' => $eventList,
        'categoryOptions' => $categoryOptions,
        'newsCategories' => $newsCategories,
    )); ?>
</div></div>
