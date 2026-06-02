<?php
$this->breadcrumbs = array(
    'Phê duyệt đăng ký' => array('admin'),
    'Danh sách',
);

$this->Tabletitle = 'Phê duyệt đăng ký';
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
                    'filter' => CHtml::activeDropDownList(
                        $model, 'status', $statusList,
                        array('class' => 'form-select form-select-sm', 'id' => '')
                    ),
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
                        $btnClass = ((int)$data->status === Registrations::STATUS_SUBMITTED) ? 'btn-primary' : 'btn-outline-secondary';
                        $label = ((int)$data->status === Registrations::STATUS_SUBMITTED) ? 'Xem & Duyệt' : 'Xem';
                        return '<a href="' . Yii::app()->createUrl('/admin/approveRegistrations/view', array('id' => $data->id)) . '" class="btn btn-sm ' . $btnClass . '"><i class="fa fa-eye me-1"></i>' . $label . '</a>';
                    }
                ),
            ),
            'options' => array(
                'pageLength' => 25,
                'order' => array(array(4, 'desc')),
            ),
        ));
        ?>
    </div>
</div>
