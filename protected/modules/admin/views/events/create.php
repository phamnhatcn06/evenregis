<?php
$this->breadcrumbs = array(
    Events::label(2),
    Yii::t('app', 'Create') . ' ' . $model->label(2),
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
        <?php
        $this->renderPartial('_form', array(
            'model' => $model
        ));
        ?>
    </div>
</div>