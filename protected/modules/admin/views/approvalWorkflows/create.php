<?php
$this->breadcrumbs = array(
    'Quy trình duyệt' => array('admin'),
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
$this->Tabletitle = 'Thêm quy trình duyệt mới';
?>

<?php $this->renderPartial('_form', array('model' => $model)); ?>
