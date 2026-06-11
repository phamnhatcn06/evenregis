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
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <h3 id="stat-reg-submitted" class="mb-0 text-primary">0</h3>
                <small>Đăng ký đã gửi</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-secondary">
            <div class="card-body text-center">
                <h3 id="stat-reg-not-submitted" class="mb-0 text-secondary">0</h3>
                <small>Đăng ký chưa gửi</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <h3 id="stat-reg-approved" class="mb-0 text-success">0</h3>
                <small>Đăng ký đã duyệt</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <h3 id="stat-reg-not-approved" class="mb-0 text-warning">0</h3>
                <small>Đăng ký chưa duyệt</small>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h3 id="stat-total" class="mb-0">0</h3>
                <small>Tổng thí sinh</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h3 id="stat-confirmed" class="mb-0">0</h3>
                <small>Đã xác nhận</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <h3 id="stat-pending" class="mb-0">0</h3>
                <small>Chờ xác nhận</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-secondary text-white">
            <div class="card-body text-center">
                <h3 id="stat-competitions" class="mb-0">0</h3>
                <small>Số nghiệp vụ</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
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
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fa fa-building me-2"></i>Thống kê theo đơn vị</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Chọn đơn vị</label>
                    <select id="filter-organization" class="form-select">
                        <option value="">-- Tất cả đơn vị --</option>
                        <?php foreach ($organizations as $id => $name): ?>
                            <option value="<?php echo $id; ?>"><?php echo CHtml::encode($name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="organization-stats-container">
                    <div class="text-center py-4">
                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Đang tải dữ liệu...</p>
                    </div>
                </div>
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
