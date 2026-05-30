<?php
$this->breadcrumbs = array(
    'Quy trình duyệt' => array('admin'),
    $model->name => array('view', 'id' => $model->id),
    'Cập nhật',
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
    array(
        'label' => 'Chi tiết',
        'url' => $this->createUrl('view', array('id' => $model->id)),
        'color' => 'info',
        'icon' => 'fa-eye',
        'id' => 'btn_view',
    ),
);
$this->Tabletitle = 'Cập nhật: ' . CHtml::encode($model->name);
?>

<?php $this->renderPartial('_form', array('model' => $model)); ?>
