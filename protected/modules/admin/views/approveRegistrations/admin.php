<?php
$this->breadcrumbs = array(
    'Phê duyệt đăng ký' => array('admin'),
    'Danh sách',
);

$this->Tabletitle = 'Phê duyệt đăng ký';

// Column config chung cho cả 3 bảng
$baseColumns = array(
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
);

// Cột thao tác cho tab Chờ duyệt
$actionColumnSubmitted = array(
    'header' => 'Thao tác',
    'width' => '120px',
    'type' => 'raw',
    'filter' => false,
    'sortable' => false,
    'value' => function ($data) {
        return '<a href="' . Yii::app()->createUrl('/admin/approveRegistrations/view', array('id' => $data->id)) . '" class="btn btn-sm btn-primary"><i class="fa fa-eye me-1"></i>Xem & Duyệt</a>';
    }
);

// Cột thao tác cho tab Trả về và Đã duyệt
$actionColumnOther = array(
    'header' => 'Thao tác',
    'width' => '100px',
    'type' => 'raw',
    'filter' => false,
    'sortable' => false,
    'value' => function ($data) {
        return '<a href="' . Yii::app()->createUrl('/admin/approveRegistrations/view', array('id' => $data->id)) . '" class="btn btn-sm btn-outline-secondary"><i class="fa fa-eye me-1"></i>Xem</a>';
    }
);

$columnsSubmitted = array_merge($baseColumns, array($actionColumnSubmitted));
$columnsRejected = array_merge($baseColumns, array($actionColumnOther));
$columnsApproved = array_merge($baseColumns, array($actionColumnOther));
?>

<div class="card">
    <div class="card-body">
        <ul class="nav nav-tabs" id="approvalTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="submitted-tab" data-bs-toggle="tab" data-bs-target="#submitted" type="button" role="tab">
                    <i class="fa fa-clock-o me-1"></i>Chờ duyệt
                    <?php if ($countSubmitted > 0): ?>
                        <span class="badge bg-warning text-dark ms-1"><?php echo $countSubmitted; ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button" role="tab">
                    <i class="fa fa-undo me-1"></i>Trả về
                    <?php if ($countRejected > 0): ?>
                        <span class="badge bg-danger ms-1"><?php echo $countRejected; ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab">
                    <i class="fa fa-check me-1"></i>Đã duyệt
                    <?php if ($countApproved > 0): ?>
                        <span class="badge bg-success ms-1"><?php echo $countApproved; ?></span>
                    <?php endif; ?>
                </button>
            </li>
        </ul>

        <div class="tab-content pt-3" id="approvalTabsContent">
            <!-- Tab Chờ duyệt -->
            <div class="tab-pane fade show active" id="submitted" role="tabpanel">
                <?php
                $this->widget('ext.edatatables.EDataTables', array(
                    'id' => 'submitted-grid',
                    'dataProvider' => $dpSubmitted,
                    'language' => 'vi',
                    'filter' => true,
                    'columns' => $columnsSubmitted,
                    'options' => array(
                        'pageLength' => 25,
                        'order' => array(array(4, 'desc')),
                    ),
                ));
                ?>
            </div>

            <!-- Tab Trả về -->
            <div class="tab-pane fade" id="rejected" role="tabpanel">
                <?php
                $this->widget('ext.edatatables.EDataTables', array(
                    'id' => 'rejected-grid',
                    'dataProvider' => $dpRejected,
                    'language' => 'vi',
                    'filter' => true,
                    'columns' => $columnsRejected,
                    'options' => array(
                        'pageLength' => 25,
                        'order' => array(array(4, 'desc')),
                    ),
                ));
                ?>
            </div>

            <!-- Tab Đã duyệt -->
            <div class="tab-pane fade" id="approved" role="tabpanel">
                <?php
                $this->widget('ext.edatatables.EDataTables', array(
                    'id' => 'approved-grid',
                    'dataProvider' => $dpApproved,
                    'language' => 'vi',
                    'filter' => true,
                    'columns' => $columnsApproved,
                    'options' => array(
                        'pageLength' => 25,
                        'order' => array(array(4, 'desc')),
                    ),
                ));
                ?>
            </div>
        </div>
    </div>
</div>
