<?php
$this->breadcrumbs = array(
    'Cuộc thi văn nghệ' => array('admin'),
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

$this->Tabletitle = 'Thêm cuộc thi văn nghệ';
?>

<div class="card">
    <div class="card-body">
        <?php $this->renderPartial('_form', array('model' => $model, 'events' => $events)); ?>
    </div>
</div>
