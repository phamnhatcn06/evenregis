<?php
$this->breadcrumbs = array(
    'Slideshow' => $this->createUrl('admin'),
    'Thêm slide',
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
$this->Tabletitle = 'Thêm slide';
?>
<div class="card"><div class="card-body">
    <?php $this->renderPartial('_form', array(
        'model' => $model,
        'eventList' => $eventList,
        'themeOptions' => $themeOptions,
    )); ?>
</div></div>
