<?php
$this->breadcrumbs = array(
    'Yêu cầu liên quân' => array('admin'),
    'Chờ xác nhận',
);

$this->menu = array(
    array(
        'label' => 'Tất cả yêu cầu',
        'url' => $this->createUrl('admin'),
        'color' => 'secondary',
        'icon' => 'fa-list',
    ),
);
$this->Tabletitle = 'Yêu cầu liên quân chờ xác nhận';
?>

<div class="card">
    <div class="card-body">
        <?php
        $this->widget('ext.edatatables.EDataTables', array(
            'id' => 'alliance-pending-grid',
            'dataProvider' => $dataProvider,
            'language' => 'vi',
            'filter' => false,
            'columns' => array(
                array('name' => 'id', 'header' => 'ID', 'width' => '60px'),
                array(
                    'header' => 'Đơn vị yêu cầu',
                    'type' => 'raw',
                    'value' => function ($data) {
                        return isset($data->requester_org_name) ? CHtml::encode($data->requester_org_name) : $data->requester_org_id;
                    }
                ),
                array(
                    'name' => 'requested_at',
                    'header' => 'Ngày yêu cầu',
                    'width' => '140px',
                    'type' => 'raw',
                    'value' => function ($data) {
                        return MyHelper::formatDateTime($data->requested_at);
                    }
                ),
                array(
                    'header' => 'Thao tác',
                    'width' => '200px',
                    'type' => 'raw',
                    'value' => function ($data) {
                        $viewBtn = '<a href="' . Yii::app()->createUrl('/admin/allianceRequests/view', array('id' => $data->id)) . '" class="btn btn-sm btn-info me-1"><i class="fa fa-eye"></i></a>';
                        $approveForm = CHtml::beginForm(Yii::app()->createUrl('/admin/allianceRequests/approve', array('id' => $data->id)), 'post', array('style' => 'display:inline'));
                        $approveBtn = '<button type="submit" class="btn btn-sm btn-success me-1" onclick="return confirm(\'Xác nhận liên quân?\')"><i class="fa fa-check"></i> Đồng ý</button>';
                        $approveForm .= $approveBtn . CHtml::endForm();
                        $rejectBtn = '<a href="' . Yii::app()->createUrl('/admin/allianceRequests/reject', array('id' => $data->id)) . '" class="btn btn-sm btn-danger"><i class="fa fa-times"></i> Từ chối</a>';
                        return $viewBtn . $approveForm . $rejectBtn;
                    }
                ),
            ),
            'options' => array(
                'pageLength' => 25,
            ),
        ));
        ?>
    </div>
</div>
