<?php
$this->breadcrumbs = array(
    'Đợt đăng ký' => array('admin'),
    Yii::t('app', 'Create'),
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
);
$this->Tabletitle = 'Tạo đợt đăng ký mới';
?>

<div class="card">
    <div class="card-body">
        <?php echo $this->renderPartial('_form', array(
            'model' => $model,
            'events' => $events,
        )); ?>
    </div>
</div>
