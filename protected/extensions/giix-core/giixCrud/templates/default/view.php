<?php

/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
?>
<?php
echo "<?php\n";
?>
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
        'url' => $this->createUrl('update', array('id' => $model-><?php echo $this->tableSchema->primaryKey; ?>)),
        'color' => 'warning',
        'icon' => 'fa-pencil',
        'id' => 'btn_update',
    ),
);

$this->breadcrumbs = array(
    <?php echo $this->modelClass; ?>::label(2),
    Yii::t('app', 'View') . ' ' . $model->label(),
);

$this->Tabletitle = Yii::t('app', 'View') . ' ' . $model->label() . ': ' . GxHtml::valueEx($model);
<?php echo '?>'; ?>

<div class="row">
    <div class="col-lg-12">
        <div class="ibox">
            <div class="ibox-content">
                <div class="table-responsive">
                    <?php echo '<?php'; ?> $this->widget('zii.widgets.CDetailView', array(
                        'htmlOptions' => array('class' => 'table table-hover table-bordered'),
                        'data' => $model,
                        'attributes' => array(
<?php
foreach ($this->tableSchema->columns as $column) {
    echo "                            '{$column->name}',\n";
}
?>
                        ),
                    )); ?>
                </div>
            </div>
        </div>
    </div>
</div>
