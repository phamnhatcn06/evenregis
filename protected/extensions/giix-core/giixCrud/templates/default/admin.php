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
    Yii::t('app', 'Admin'),
);

$this->menu = array(
    array(
        'label' => Yii::t('app', 'Create') . ' ',
        'labelIcon' => Yii::t('app', 'Create'),
        'url' => $this->createUrl('create'),
        'color' => 'primary',
        'icon' => 'fa-plus',
        'id' => 'btn_create',
    ),
);
$this->Tabletitle = Yii::t('app', 'List') . ' ' . $model->label();
<?php echo '?>'; ?>

<?php echo "<?php\n"; ?>
$this->widget('ext.edatatables.EDataTables', array(
    'id' => '<?php echo $this->class2id($this->modelClass); ?>-grid',
    'dataProvider' => $model->search(),
    'language' => 'vi',
    'filter' => true,
    'columns' => array(
        array('name' => 'id', 'header' => 'ID', 'width' => '60px', 'filter' => false),
<?php
foreach ($this->tableSchema->columns as $column) {
    if ($column->name === 'id') continue;
    if (in_array($column->name, array('created_at', 'updated_at', 'deleted_at'))) continue;

    $type = '';
    if (stripos($column->name, '_at') !== false || $column->dbType === 'datetime' || $column->dbType === 'timestamp') {
        $type = ", 'type' => 'datetime'";
    } elseif ($column->dbType === 'date') {
        $type = ", 'type' => 'date'";
    }

    echo "        array('name' => '{$column->name}', 'header' => '{$this->generateAttributeLabel($column->name)}'{$type}),\n";
}
?>
        array(
            'header' => 'Thao tác',
            'width' => '100px',
            'type' => 'raw',
            'filter' => false,
            'sortable' => false,
            'value' => function ($data) {
                return IconHelper::actionButtons($data, array('view', 'update'), '/admin/<?php echo $this->controller; ?>');
            }
        ),
    ),
    'options' => array(
        'pageLength' => 25,
        'order' => array(array(0, 'desc')),
    ),
));
<?php echo '?>'; ?>
