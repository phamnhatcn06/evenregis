<?php
$this->breadcrumbs = array(
    'Phê duyệt đăng ký' => array('admin'),
    'Danh sách',
);

$this->Tabletitle = 'Danh sách đăng ký chờ phê duyệt';
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fa fa-check-square-o me-2"></i>Danh sách đăng ký chờ phê duyệt</h5>
    </div>
    <div class="card-body">
        <?php $this->widget('booster.widgets.TbGridView', array(
            'id' => 'approve-registrations-grid',
            'dataProvider' => $dataProvider,
            'type' => 'striped bordered',
            'template' => "{items}\n{pager}",
            'columns' => array(
                array(
                    'header' => 'STT',
                    'value' => '$this->grid->dataProvider->pagination->currentPage * $this->grid->dataProvider->pagination->pageSize + ($row + 1)',
                    'htmlOptions' => array('style' => 'width:50px;text-align:center;'),
                ),
                array(
                    'name' => 'property_name',
                    'header' => 'Đơn vị',
                    'value' => 'isset($data["property_name"]) ? $data["property_name"] : ""',
                ),
                array(
                    'name' => 'event_name',
                    'header' => 'Sự kiện',
                    'value' => 'isset($data["event_name"]) ? $data["event_name"] : ""',
                ),
                array(
                    'name' => 'period_name',
                    'header' => 'Đợt đăng ký',
                    'value' => 'isset($data["period_name"]) ? $data["period_name"] : ""',
                ),
                array(
                    'name' => 'submitted_at',
                    'header' => 'Ngày nộp',
                    'value' => 'isset($data["submitted_at"]) && $data["submitted_at"] ? date("d/m/Y H:i", $data["submitted_at"]) : "-"',
                    'htmlOptions' => array('style' => 'width:130px;'),
                ),
                array(
                    'name' => 'status',
                    'header' => 'Trạng thái',
                    'type' => 'raw',
                    'value' => 'Registrations::getStatusLabel(isset($data["status"]) ? $data["status"] : 0)',
                    'htmlOptions' => array('style' => 'width:100px;text-align:center;'),
                ),
                array(
                    'header' => 'Thao tác',
                    'type' => 'raw',
                    'value' => function($data, $row, $grid) {
                        $id = isset($data['id']) ? $data['id'] : '';
                        return '<a href="' . Yii::app()->createUrl('/admin/approveRegistrations/view', array('id' => $id)) . '" class="btn btn-sm btn-primary"><i class="fa fa-eye me-1"></i>Xem & Duyệt</a>';
                    },
                    'htmlOptions' => array('style' => 'width:120px;text-align:center;'),
                ),
            ),
        )); ?>
    </div>
</div>
