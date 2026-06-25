<?php
$this->breadcrumbs = array(
    'Cuộc thi sắc đẹp' => array('/admin/beautyContests/admin'),
    'Vòng thi' => array('admin'),
    'Thêm mới',
);

$this->menu = array(
    array(
        'label' => 'Danh sách',
        'url' => $this->createUrl('admin'),
        'color' => 'secondary',
        'icon' => 'fa-list',
    ),
);
$this->Tabletitle = 'Thêm vòng thi mới';
?>

<div class="card">
    <div class="card-body">
        <?php $this->renderPartial('_form', array('model' => $model, 'contests' => $contests)); ?>
    </div>
</div>
