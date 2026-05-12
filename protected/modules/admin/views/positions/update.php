<?php
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
$this->breadcrumbs = array(
    Positions::label(2),
    Yii::t('app', 'Update'),
);

$this->Tabletitle = Yii::t('app', 'Update') . ' ' . $model->label() . ': ' . GxHtml::valueEx($model);
?>
<div class="card"><div class="card-body">
                <?php
                $this->renderPartial('_form', array(
                    'model' => $model
                ));
                ?>            </div></div>

