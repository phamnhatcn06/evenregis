<?php
$this->breadcrumbs = array(
    Regionals::label(2) => array('admin'),
    $model->name => array('view', 'id' => $model->id),
    Yii::t('app', 'Update'),
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
        'label' => Yii::t('app', 'View'),
        'labelIcon' => Yii::t('app', 'View'),
        'url' => $this->createUrl('view', array('id' => $model->id)),
        'color' => 'info',
        'icon' => 'fa-eye',
        'id' => 'btn_view',
    ),
);
$this->Tabletitle = Yii::t('app', 'Update') . ' ' . $model->label() . ': ' . CHtml::encode($model->name);
?>
<div class="card">
    <div class="card-body">
        <?php $this->renderPartial('_form', array('model' => $model)); ?>
    </div>
</div>
