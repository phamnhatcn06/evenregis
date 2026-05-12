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
    Staffs::label(2),
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
                            'unique_code',
                            'department_code',
                            'id_card',
                            'rank_id',
                            'position_code',
                            'property_code',
                            'division_code',
                            'code',
                            'is_lecturer',
                            'curren_job_id',
                            'lecturer_type',
                            'first_name',
                            'last_name',
                            'full_name',
                            'email',
                            'phone',
                            'birthday',
                            'terminate_date',
                            'join_hotel_date',
                            'end_testing_date',
                            'married',
                            'gender',
                            'address',
                            'notes',
                            'status',
                            'staff_type',
                            'created_at',
                            'updated_at',
                            'deleted_at',
                        ),
                    )); ?>
                </div></div>
</div>

