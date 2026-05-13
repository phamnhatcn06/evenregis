<?php
$this->breadcrumbs = array(
    Registrations::label(2) => array('admin'),
    Yii::t('app', 'Update'),
);

$this->menu = array(
    array(
        'label' => Yii::t('app', 'Manage'),
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
$this->Tabletitle = 'Cập nhật phiếu đăng ký #' . $model->id;
?>

<div class="card">
    <div class="card-body">
        <?php echo $this->renderPartial('_form', array(
            'model' => $model,
            'events' => $events,
            'periods' => $periods,
            'properties' => $properties,
        )); ?>
    </div>
</div>
