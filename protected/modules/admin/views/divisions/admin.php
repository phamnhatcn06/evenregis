<?php
$this->breadcrumbs = array(
    Divisions::label(2),
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
            'id' => 'divisions-grid',
            'dataProvider' => $dataProvider,
            'language' => 'vi',
            'filter' => true,
            'columns' => array(
                array('name' => 'id', 'header' => 'ID', 'width' => '60px', 'filter' => false),
                array('name' => 'property_name', 'header' => 'Đơn vị'),
                array('name' => 'unique_code', 'header' => 'Unique Code'),
                array('name' => 'code', 'header' => 'Code'),
                array('name' => 'name', 'header' => 'Name'),
                array('name' => 'total_staff', 'header' => 'Total Staff'),
                array('name' => 'notes', 'header' => 'Notes'),
                array('name' => 'status', 'header' => 'Status'),
                array(
                    'header' => 'Thao tác',
                    'width' => '100px',
                    'type' => 'raw',
                    'filter' => false,
                    'sortable' => false,
                    'value' => function ($data) {
                        return IconHelper::actionButtons($data, array('view', 'update'), '/admin/divisions');
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
