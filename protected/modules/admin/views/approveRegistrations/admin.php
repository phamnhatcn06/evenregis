<?php
$this->breadcrumbs = array(
    'Phê duyệt đăng ký' => array('admin'),
    'Danh sách',
);

$this->Tabletitle = 'Phê duyệt đăng ký';

// Column config chung cho cả 3 bảng
$baseColumns = array(
    array('name' => 'id', 'header' => 'ID', 'width' => '60px'),
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

// Cột lý do trả về
$rejectionReasonColumn = array(
    'name' => 'rejection_reason',
    'header' => 'Lý do trả về',
    'value' => function ($data) {
        $reason = isset($data->rejection_reason) ? $data->rejection_reason : '';
        if (empty($reason)) return '-';
        if (mb_strlen($reason) > 50) {
            return '<span title="' . CHtml::encode($reason) . '">' . CHtml::encode(mb_substr($reason, 0, 50)) . '...</span>';
        }
        return CHtml::encode($reason);
    },
    'type' => 'raw',
);

$columnsSubmitted = array_merge($baseColumns, array($actionColumnSubmitted));
$columnsRejected = array_merge($baseColumns, array($rejectionReasonColumn, $actionColumnOther));
$columnsApproved = array_merge($baseColumns, array($actionColumnOther));
?>

<style>
.approval-tabs {
    display: flex;
    gap: 12px;
    padding: 0;
    margin-bottom: 24px;
    border: none;
}
.approval-tabs .nav-item {
    flex: 1;
}
.approval-tabs .nav-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    background: #fff;
    color: #6c757d;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.2s ease;
    width: 100%;
}
.approval-tabs .nav-link:hover {
    border-color: #dee2e6;
    background: #f8f9fa;
}
.approval-tabs .nav-link.active {
    border-color: transparent;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.approval-tabs .nav-link .tab-info {
    display: flex;
    align-items: center;
    gap: 10px;
}
.approval-tabs .nav-link .tab-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}
.approval-tabs .nav-link .tab-count {
    font-size: 20px;
    font-weight: 700;
    line-height: 1;
}
/* Tab Chờ duyệt - Warning/Orange */
.approval-tabs .tab-submitted .tab-icon { background: #fff3cd; color: #856404; }
.approval-tabs .tab-submitted.active { background: linear-gradient(135deg, #ffc107 0%, #ffca2c 100%); color: #000; }
.approval-tabs .tab-submitted.active .tab-icon { background: rgba(255,255,255,0.3); color: #000; }
/* Tab Trả về - Danger/Red */
.approval-tabs .tab-rejected .tab-icon { background: #f8d7da; color: #721c24; }
.approval-tabs .tab-rejected.active { background: linear-gradient(135deg, #dc3545 0%, #e4606d 100%); color: #fff; }
.approval-tabs .tab-rejected.active .tab-icon { background: rgba(255,255,255,0.2); color: #fff; }
/* Tab Đã duyệt - Success/Green */
.approval-tabs .tab-approved .tab-icon { background: #d4edda; color: #155724; }
.approval-tabs .tab-approved.active { background: linear-gradient(135deg, #28a745 0%, #48c774 100%); color: #fff; }
.approval-tabs .tab-approved.active .tab-icon { background: rgba(255,255,255,0.2); color: #fff; }

@media (max-width: 768px) {
    .approval-tabs {
        flex-direction: column;
        gap: 8px;
    }
    .approval-tabs .nav-link {
        padding: 8px 12px;
    }
    .approval-tabs .nav-link .tab-count {
        font-size: 16px;
    }
}
</style>

<div class="card mb-3">
    <div class="card-body">
        <form method="get" action="<?php echo Yii::app()->createUrl('/admin/approveRegistrations/admin'); ?>" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Sự kiện</label>
                <?php echo CHtml::dropDownList('event_id', $filterEventId, $eventList, array('class' => 'form-select', 'id' => 'filter-event')); ?>
            </div>
            <div class="col-md-3">
                <label class="form-label">Đơn vị</label>
                <?php echo CHtml::dropDownList('property_id', $filterPropertyId, $propertyList, array('class' => 'form-select')); ?>
            </div>
            <div class="col-md-3">
                <label class="form-label">Đợt đăng ký</label>
                <?php echo CHtml::dropDownList('period_id', $filterPeriodId, $periodList, array('class' => 'form-select', 'id' => 'filter-period')); ?>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary"><i class="fa fa-filter me-1"></i>Lọc</button>
                <a href="<?php echo Yii::app()->createUrl('/admin/approveRegistrations/admin'); ?>" class="btn btn-outline-secondary"><i class="fa fa-refresh me-1"></i>Xóa lọc</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <ul class="nav approval-tabs" id="approvalTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link tab-submitted active" id="submitted-tab" data-bs-toggle="tab" data-bs-target="#submitted" type="button" role="tab">
                    <div class="tab-info">
                        <div class="tab-icon"><i class="fa fa-clock-o"></i></div>
                        <span>Chờ duyệt</span>
                    </div>
                    <span class="tab-count"><?php echo $countSubmitted; ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link tab-rejected" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button" role="tab">
                    <div class="tab-info">
                        <div class="tab-icon"><i class="fa fa-undo"></i></div>
                        <span>Trả về</span>
                    </div>
                    <span class="tab-count"><?php echo $countRejected; ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link tab-approved" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab">
                    <div class="tab-info">
                        <div class="tab-icon"><i class="fa fa-check"></i></div>
                        <span>Đã duyệt</span>
                    </div>
                    <span class="tab-count"><?php echo $countApproved; ?></span>
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
                    'filter' => false,
                    'columns' => $columnsSubmitted,
                    'options' => array(
                        'pageLength' => 25,
                        'order' => array(array(4, 'desc')),
                        'responsive' => true,
                        'scrollX' => true,
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
                    'filter' => false,
                    'columns' => $columnsRejected,
                    'options' => array(
                        'pageLength' => 25,
                        'order' => array(array(4, 'desc')),
                        'responsive' => true,
                        'scrollX' => true,
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
                    'filter' => false,
                    'columns' => $columnsApproved,
                    'options' => array(
                        'pageLength' => 25,
                        'order' => array(array(4, 'desc')),
                        'responsive' => true,
                        'scrollX' => true,
                    ),
                ));
                ?>
            </div>
        </div>
    </div>
</div>

<?php
Yii::app()->clientScript->registerScript('filter-period-load', "
document.getElementById('filter-event').addEventListener('change', function() {
    var eventId = this.value;
    var periodSelect = document.getElementById('filter-period');
    periodSelect.innerHTML = '<option value=\"\">-- Đang tải... --</option>';
    if (!eventId) {
        periodSelect.innerHTML = '<option value=\"\">-- Tất cả --</option>';
        return;
    }
    fetch('" . Yii::app()->params['externalApiUrl'] . "/api/registration-periods?event_id=' + eventId, {
        headers: { 'Authorization': 'Bearer " . Yii::app()->params['externalApiKey'] . "' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        periodSelect.innerHTML = '<option value=\"\">-- Tất cả --</option>';
        var items = data.data || data;
        if (Array.isArray(items)) {
            items.forEach(function(p) {
                var opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.name;
                periodSelect.appendChild(opt);
            });
        }
    })
    .catch(function() {
        periodSelect.innerHTML = '<option value=\"\">-- Tất cả --</option>';
    });
});
", CClientScript::POS_END);
?>
