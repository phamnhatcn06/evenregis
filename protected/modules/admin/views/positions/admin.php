<?php
$this->breadcrumbs = array(
    Positions::label(2),
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
<div class="card">
    <div class="card-body">
        <?php
        $this->widget('ext.edatatables.EDataTables', array(
            'id' => 'positions-grid',
            'dataProvider' => $dataProvider,
            'language' => 'vi',
            'filter' => true,
            'columns' => array(
                array('name' => 'id', 'header' => 'ID', 'width' => '60px', 'filter' => false),
                array('name' => 'unique_code', 'header' => 'Unique Code'),
                array('name' => 'property_code', 'header' => 'Property Code'),
                array('name' => 'division_code', 'header' => 'Division Code'),
                array('name' => 'department_code', 'header' => 'Department Code'),
                array('name' => 'level', 'header' => 'Level'),
                array('name' => 'code', 'header' => 'Code'),
                array('name' => 'name', 'header' => 'Name'),
                array('name' => 'amount', 'header' => 'Amount'),
                array('name' => 'notes', 'header' => 'Notes'),
                array('name' => 'status', 'header' => 'Status'),
                array(
                    'header' => 'Thao tác',
                    'width' => '100px',
                    'type' => 'raw',
                    'filter' => false,
                    'sortable' => false,
                    'value' => function ($data) {
                        return IconHelper::actionButtons($data, array('view', 'update'), '/admin/positions');
                    }
                ),
            ),
            'options' => array(
                'pageLength' => 25,
                'order' => array(array(0, 'desc')),
            ),
        ));
        ?>
    </div>
</div>
