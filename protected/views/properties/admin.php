
<?php
$this->breadcrumbs = array(
    Properties::label(2),
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
?>
<?php
$this->widget('ext.edatatables.EDataTables', array(
    'id' => 'properties-grid',
    'dataProvider' => $model->search(),
    'language' => 'vi',
    'filter' => true,
    'columns' => array(
        array('name' => 'id', 'header' => 'ID', 'width' => '60px', 'filter' => false),
        array('name' => 'prefix', 'header' => 'Prefix'),
        array('name' => 'code', 'header' => 'Code'),
        array('name' => 'smile_code', 'header' => 'Smile Code'),
        array('name' => 'name', 'header' => 'Name'),
        array('name' => 'active_date', 'header' => 'Active Date', 'type' => 'date'),
        array('name' => 'status', 'header' => 'Status'),
        array(
            'header' => 'Thao tác',
            'width' => '100px',
            'type' => 'raw',
            'filter' => false,
            'sortable' => false,
            'value' => function ($data) {
                return IconHelper::actionButtons($data, array('view', 'update'), '/admin/properties');
            }
        ),
    ),
    'options' => array(
        'pageLength' => 25,
        'order' => array(array(0, 'desc')),
    ),
));
?>