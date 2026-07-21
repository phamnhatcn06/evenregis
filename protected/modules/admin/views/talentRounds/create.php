<?php
$this->breadcrumbs = array(
    'Cuộc thi văn nghệ' => array('/admin/talentShows/admin'),
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
        <?php $this->renderPartial('_form', array('model' => $model, 'talentShows' => $talentShows)); ?>
    </div>
</div>
