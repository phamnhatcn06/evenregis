<?php
$this->breadcrumbs = array(
    Regionals::label(2) => array('admin'),
    Yii::t('app', 'Create'),
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
$this->Tabletitle = Yii::t('app', 'Create') . ' ' . $model->label();
?>
<div class="card">
    <div class="card-body">
        <?php $this->renderPartial('_form', array('model' => $model)); ?>
    </div>
</div>
