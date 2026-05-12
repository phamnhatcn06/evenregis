<?php

/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
?>
<?php
echo "<?php\n";
?>
$this->breadcrumbs = array(
    <?php echo $this->modelClass; ?>::label(2),
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
<?php echo '?>'; ?>

<div class="row">
    <div class="col-lg-12">
        <div class="ibox">
            <div class="ibox-content">
                <?php echo "<?php\n"; ?>
                $this->renderPartial('_form', array(
                    'model' => $model
                ));
                <?php echo '?>'; ?>
            </div>
        </div>
    </div>
</div>
