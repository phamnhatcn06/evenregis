<?php
$this->breadcrumbs = array(
    'Yêu cầu liên quân',
    'Quản lý',
);

$this->menu = array(
    array(
        'label' => 'Chờ xác nhận',
        'url' => $this->createUrl('pending'),
        'color' => 'warning',
        'icon' => 'fa-clock-o',
    ),
);
$this->Tabletitle = 'Danh sách yêu cầu liên quân';
?>

<div class="card">
    <div class="card-body">
        <?php
        $this->widget('ext.edatatables.EDataTables', array(
            'id' => 'alliance-requests-grid',
            'dataProvider' => $dataProvider,
            'language' => 'vi',
            'filter' => true,
            'columns' => array(
                array('name' => 'id', 'header' => 'ID', 'width' => '60px', 'filter' => false),
                array(
                    'name' => 'event_id',
                    'header' => 'Sự kiện',
                    'type' => 'raw',
                    'filter' => $events,
                    'value' => function ($data) use ($events) {
                        return isset($events[$data->event_id]) ? CHtml::encode($events[$data->event_id]) : $data->event_id;
                    }
                ),
                array(
                    'header' => 'Đơn vị yêu cầu',
                    'type' => 'raw',
                    'filter' => false,
                    'value' => function ($data) {
                        return isset($data->requester_org_name) ? CHtml::encode($data->requester_org_name) : $data->requester_org_id;
                    }
                ),
                array(
                    'header' => 'Đơn vị liên quân',
                    'type' => 'raw',
                    'filter' => false,
                    'value' => function ($data) {
                        return isset($data->target_org_name) ? CHtml::encode($data->target_org_name) : $data->target_org_id;
                    }
                ),
                array(
                    'name' => 'status',
                    'header' => 'Trạng thái',
                    'width' => '130px',
                    'type' => 'raw',
                    'filter' => array(
                        AllianceRequests::STATUS_PENDING => 'Chờ xác nhận',
                        AllianceRequests::STATUS_APPROVED => 'Đã xác nhận',
                        AllianceRequests::STATUS_REJECTED => 'Từ chối',
                    ),
                    'value' => function ($data) {
                        return AllianceRequests::getStatusLabel($data->status);
                    }
                ),
                array(
                    'name' => 'requested_at',
                    'header' => 'Ngày yêu cầu',
                    'width' => '140px',
                    'type' => 'raw',
                    'filter' => false,
                    'value' => function ($data) {
                        return MyHelper::formatDateTime($data->requested_at);
                    }
                ),
                array(
                    'header' => 'Thao tác',
                    'width' => '100px',
                    'type' => 'raw',
                    'filter' => false,
                    'sortable' => false,
                    'value' => function ($data) {
                        return IconHelper::actionButtons($data, array('view', 'delete'), '/admin/allianceRequests');
                    }
                ),
            ),
            'options' => array(
                'pageLength' => 25,
                'responsive' => true,
                'scrollX' => true,
            ),
        ));
        ?>
    </div>
</div>
