<?php
$this->breadcrumbs = array(
    'Phê duyệt đăng ký' => array('admin'),
    'Danh sách',
);

$this->Tabletitle = 'Danh sách đăng ký chờ phê duyệt';
?>

<div class="card">
    <div class="card-body">
        <?php
        $this->widget('ext.edatatables.EDataTables', array(
            'id' => 'approve-registrations-grid',
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
                    'name' => 'submitted_at',
                    'header' => 'Ngày nộp',
                    'value' => function ($data) {
                        return $data->submitted_at ? MyHelper::formatDateTime($data->submitted_at) : '-';
                    }
                ),
                array(
                    'name' => 'status',
                    'header' => 'Trạng thái',
                    'type' => 'raw',
                    'filter' => false,
                    'value' => function ($data) {
                        return Registrations::getStatusLabel($data->status);
                    }
                ),
                array(
                    'header' => 'Thao tác',
                    'width' => '120px',
                    'type' => 'raw',
                    'filter' => false,
                    'sortable' => false,
                    'value' => function ($data) {
                        return '<a href="' . Yii::app()->createUrl('/admin/approveRegistrations/view', array('id' => $data->id)) . '" class="btn btn-sm btn-primary"><i class="fa fa-eye me-1"></i>Xem & Duyệt</a>';
                    }
                ),
            ),
            'options' => array(
                'pageLength' => 25,
                'order' => array(array(4, 'desc')), // Order by submitted_at desc
            ),
        ));
        ?>
    </div>
</div>
