<?php
$this->breadcrumbs = array(
    'Quy trình duyệt' => array('admin'),
    'Thêm mới',
);

$this->menu = array(
    array(
        'label' => Yii::t('app', 'List') . ' ',
        'labelIcon' => Yii::t('app', 'Manage'),
        'url' => $this->createUrl('admin'),
        'color' => 'primary',
        'icon' => 'fa-th',
        'id' => 'btn_manage',
    ),
);
$this->Tabletitle = 'Thêm quy trình duyệt mới';
?>


<?php $this->renderPartial('_form', array('model' => $model)); ?>
