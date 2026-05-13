<?php
$this->breadcrumbs = array(
    Registrations::label(2),
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
$this->Tabletitle = 'Quản lý phiếu đăng ký';
?>

<div class="card">
    <div class="card-body">
        <?php
        $this->widget('ext.edatatables.EDataTables', array(
            'id' => 'registrations-grid',
            'dataProvider' => $dataProvider,
            'language' => 'vi',
            'filter' => true,
            'columns' => array(
                array('name' => 'id', 'header' => 'ID', 'width' => '60px', 'filter' => false),
                array(
                    'name' => 'event_id',
                    'header' => 'Sự kiện',
                    'value' => function ($data) {
                        return isset($data->event_name) ? $data->event_name : '';
                    }
                ),
                array(
                    'name' => 'property_id',
                    'header' => 'Đơn vị',
                    'value' => function ($data) {
                        return isset($data->property_name) ? $data->property_name : '';
                    }
                ),
                array(
                    'name' => 'period_id',
                    'header' => 'Đợt đăng ký',
                    'value' => function ($data) {
                        return isset($data->period_name) ? $data->period_name : '';
                    }
                ),
                array(
                    'name' => 'status',
                    'header' => 'Trạng thái',
                    'type' => 'raw',
                    'filter' => Registrations::getStatusList(),
                    'value' => function ($data) {
                        return Registrations::getStatusLabel($data->status);
                    }
                ),
                array(
                    'name' => 'submitted_at',
                    'header' => 'Ngày nộp',
                    'value' => function ($data) {
                        return $data->submitted_at ? MyHelper::formatDateTime($data->submitted_at) : '-';
                    }
                ),
                array(
                    'name' => 'created_at',
                    'header' => 'Ngày tạo',
                    'value' => function ($data) {
                        return MyHelper::formatDateTime($data->created_at);
                    }
                ),
                array(
                    'header' => 'Thao tác',
                    'width' => '100px',
                    'type' => 'raw',
                    'filter' => false,
                    'sortable' => false,
                    'value' => function ($data) {
                        return IconHelper::actionButtons($data, array('view', 'update', 'delete'), '/admin/registrations');
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
