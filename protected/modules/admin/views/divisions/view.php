<?php
$this->menu = array(
    array(
        'label' => Yii::t('app', 'Manage') . ' ' . $model->label(2),
        'labelIcon' => Yii::t('app', 'Manage'),
        'url' => $this->createUrl('admin'),
        'color' => 'primary',
        'icon' => 'fa-th',
        'id' => 'btn_manage',
    ),
    array(
        'label' => Yii::t('app', 'Create') . ' ' . $model->label(),
        'labelIcon' => Yii::t('app', 'Create'),
        'url' => $this->createUrl('create'),
        'color' => 'success',
        'icon' => 'fa-plus',
        'id' => 'btn_create',
    ),
    array(
        'label' => Yii::t('app', 'Update') . ' ' . $model->label(),
        'labelIcon' => Yii::t('app', 'Update'),
        'url' => $this->createUrl('update', array('id' => $model->id)),
        'color' => 'warning',
        'icon' => 'fa-pencil',
        'id' => 'btn_update',
    ),
);

$this->breadcrumbs = array(
    Divisions::label(2),
    Yii::t('app', 'View') . ' ' . $model->label(),
);

$this->Tabletitle = Yii::t('app', 'View') . ' ' . $model->label() . ': ' . GxHtml::valueEx($model);
?>
<div class="card"><div class="card-body">
                <div class="table-responsive">
                    <?php $this->widget('zii.widgets.CDetailView', array(
                        'htmlOptions' => array('class' => 'table table-hover table-bordered'),
                        'data' => $model,
                        'attributes' => array(
                            'id',
                            'property_code',
                            'unique_code',
                            'code',
                            'name',
                            'total_staff',
                            'notes',
                            'status',
                            'created_at',
                            'updated_at',
                            'deleted_at',
                        ),
                    )); ?>
                </div></div>
</div>

