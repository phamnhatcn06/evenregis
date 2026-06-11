<?php
$this->breadcrumbs = array(
    'Thi nghiệp vụ' => array('admin'),
    'Tổng quan',
);

$this->menu = array(
    array(
        'label' => 'Danh sách đăng ký',
        'url' => $this->createUrl('admin'),
        'color' => 'secondary',
        'icon' => 'fa-list',
    ),
);
$this->Tabletitle = 'Tổng quan thí sinh thi nghiệp vụ';
?>

<div class="card mb-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fa fa-filter me-2"></i>Bộ lọc</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Sự kiện</label>
                <select id="filter-event" class="form-select">
                    <?php foreach ($events as $id => $name): ?>
                        <option value="<?php echo $id; ?>"><?php echo CHtml::encode($name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h3 id="stat-total" class="mb-0">0</h3>
                <small>Tổng thí sinh</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h3 id="stat-confirmed" class="mb-0">0</h3>
                <small>Đã xác nhận</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <h3 id="stat-pending" class="mb-0">0</h3>
                <small>Chờ xác nhận</small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="fa fa-trophy me-2"></i>Thống kê theo nghiệp vụ</h5>
    </div>
    <div class="card-body">
        <div id="competitions-stats-container">
            <div class="text-center py-4">
                <i class="fa fa-spinner fa-spin fa-2x"></i>
                <p class="mt-2">Đang tải dữ liệu...</p>
            </div>
        </div>
    </div>
</div>

<?php
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/competition-registrations-overview.js',
    CClientScript::POS_END
);
?>
