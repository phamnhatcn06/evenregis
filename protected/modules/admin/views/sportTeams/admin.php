<?php
$this->breadcrumbs = array(
    'Đội thể thao' => array('admin'),
    'Tổng quan',
);

$this->menu = array(
    array(
        'label' => 'Danh sách',
        'url' => $this->createUrl('admin'),
        'color' => 'secondary',
        'icon' => 'fa-list',
    ),
);
$this->Tabletitle = 'Tổng quan đội thể thao';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body text-center py-5">
                <i class="fa fa-building fa-3x text-primary mb-3"></i>
                <h4>Xem theo đơn vị</h4>
                <p class="text-muted">Hiển thị tất cả môn thể thao và đội thi của một đơn vị</p>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalSelectProperty">
                    <i class="fa fa-search me-2"></i>Chọn đơn vị
                </button>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body text-center py-5">
                <i class="fa fa-futbol-o fa-3x text-success mb-3"></i>
                <h4>Xem theo bộ môn</h4>
                <p class="text-muted">Hiển thị tất cả đội thi đấu của một bộ môn</p>
                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalSelectSport">
                    <i class="fa fa-search me-2"></i>Chọn bộ môn
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center bg-transparent border-bottom">
        <h4 class="card-title mb-0 text-dark">
            <i class="fa fa-bar-chart text-primary me-2"></i>Thống kê đăng ký thi đấu
        </h4>
        <div class="d-flex align-items-center" style="min-width: 280px;">
            <label for="select_event_stats" class="me-2 text-nowrap text-muted mb-0">Sự kiện:</label>
            <select id="select_event_stats" class="form-select form-select-sm">
                <?php foreach ($events as $id => $name): ?>
                    <option value="<?php echo $id; ?>"><?php echo CHtml::encode($name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="card-body">
        <!-- Loader -->
        <div id="stats-loader" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Đang tải...</span>
            </div>
            <p class="text-muted mt-2 mb-0">Đang tính toán dữ liệu thống kê...</p>
        </div>

        <div id="stats-content" class="d-none">
            <!-- KPI Row -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm" style="background: rgba(58, 87, 232, 0.08); border-radius: 12px;">
                        <div class="card-body d-flex align-items-center py-4">
                            <div class="flex-shrink-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; box-shadow: 0 4px 10px rgba(58, 87, 232, 0.3);">
                                <i class="fa fa-users fa-lg"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="text-muted mb-1 fw-bold">Tổng số VĐV</h6>
                                <h2 class="mb-0 text-primary fw-extrabold" id="stat-total-athletes">0</h2>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm" style="background: rgba(26, 186, 130, 0.08); border-radius: 12px;">
                        <div class="card-body d-flex align-items-center py-4">
                            <div class="flex-shrink-0 bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; box-shadow: 0 4px 10px rgba(26, 186, 130, 0.3);">
                                <i class="fa fa-shield fa-lg"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="text-muted mb-1 fw-bold">Tổng số đội</h6>
                                <h2 class="mb-0 text-success fw-extrabold" id="stat-total-teams">0</h2>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm" style="background: rgba(255, 159, 67, 0.08); border-radius: 12px;">
                        <div class="card-body d-flex align-items-center py-4">
                            <div class="flex-shrink-0 text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; background: #ff9f43; box-shadow: 0 4px 10px rgba(255, 159, 67, 0.3);">
                                <i class="fa fa-user fa-lg"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="text-muted mb-1 fw-bold">Số đội đơn</h6>
                                <h2 class="mb-0 fw-extrabold" style="color: #ff9f43;" id="stat-single-teams">0</h2>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm" style="background: rgba(115, 103, 240, 0.08); border-radius: 12px;">
                        <div class="card-body d-flex align-items-center py-4">
                            <div class="flex-shrink-0 text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; background: #7367f0; box-shadow: 0 4px 10px rgba(115, 103, 240, 0.3);">
                                <i class="fa fa-handshake-o fa-lg"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="text-muted mb-1 fw-bold">Số đội liên quân</h6>
                                <h2 class="mb-0 fw-extrabold" style="color: #7367f0;" id="stat-alliance-teams">0</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table of sports -->
            <h5 class="mb-3 text-dark"><i class="fa fa-list-ol text-muted me-2"></i>Số đội và VĐV theo từng nội dung</h5>
            <div class="table-responsive" style="border-radius: 8px; border: 1px solid #e9ecef;">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light text-nowrap">
                        <tr>
                            <th class="ps-3" style="width: 8%;">STT</th>
                            <th>Nội dung / Bộ môn thi đấu</th>
                            <th class="text-center" style="width: 25%;">Số đội</th>
                            <th class="text-center pe-3" style="width: 25%;">Số VĐV đăng ký</th>
                        </tr>
                    </thead>
                    <tbody id="stats-table-body">
                        <!-- Content will be injected by JS -->
                    </tbody>
                </table>
            </div>

            <!-- New Sports Summary by Cluster Section -->
            <hr class="my-4">
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 text-dark"><i class="fa fa-table text-primary me-2"></i>Tổng hợp đăng ký thể thao theo cụm và đơn vị</h5>
                <a id="btn-export-excel" href="#" class="btn btn-success btn-sm">
                    <i class="fa fa-file-excel-o me-1"></i> Xuất Excel
                </a>
            </div>
            
            <div class="table-responsive" style="border-radius: 8px; border: 1px solid #e9ecef;">
                <table class="table table-bordered table-hover align-middle mb-0" id="sports-summary-table">
                    <!-- Dynamic header and body will be injected by JS -->
                </table>
            </div>
        </div>
    </div>
</div>

<div id="result-container"></div>

<?php $this->renderPartial('_modal_select_property', array('properties' => $properties, 'events' => $events)); ?>
<?php $this->renderPartial('_modal_select_sport', array('sports' => $sports, 'events' => $events)); ?>

<?php
$booster = Yii::app()->booster;
$assetsUrl = $booster->getAssetsUrl();
Yii::app()->clientScript->registerCssFile($assetsUrl . '/select2/select2.css');
Yii::app()->clientScript->registerScriptFile($assetsUrl . '/select2/select2.min.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScript('sport-teams-init', '
    window.BASE_URL = "' . Yii::app()->createUrl('/') . '";
', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/sport-teams-overview.js',
    CClientScript::POS_END
);
?>

<style>
    .select2-container {
        z-index: 99999 !important;
    }

    .select2-drop {
        z-index: 99999 !important;
    }

    .select2-drop-mask {
        z-index: 99998 !important;
    }

    /* Sticky/Frozen columns for sports summary tables */
    #sports-summary-table, #tableSportsSummary {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        border-left: 1px solid #dee2e6 !important;
        border-top: 1px solid #dee2e6 !important;
        width: max-content !important;
        min-width: 100% !important;
    }

    #sports-summary-table th, #sports-summary-table td,
    #tableSportsSummary th, #tableSportsSummary td {
        border-right: 1px solid #dee2e6 !important;
        border-bottom: 1px solid #dee2e6 !important;
    }

    /* STT column */
    #sports-summary-table thead tr:first-child th:nth-child(1),
    #tableSportsSummary thead tr:first-child th:nth-child(1) {
        position: sticky !important;
        left: 0 !important;
        z-index: 10 !important;
        background-color: #3a57e8 !important;
        width: 60px !important;
        min-width: 60px !important;
        max-width: 60px !important;
    }
    #sports-summary-table tbody tr:not(.table-warning) td:nth-child(1),
    #tableSportsSummary tbody tr:not(.table-warning) td:nth-child(1) {
        position: sticky !important;
        left: 0 !important;
        z-index: 8 !important;
        background-color: #ffffff !important;
        width: 60px !important;
        min-width: 60px !important;
        max-width: 60px !important;
    }

    /* Cụm column */
    #sports-summary-table thead tr:first-child th:nth-child(2),
    #tableSportsSummary thead tr:first-child th:nth-child(2) {
        position: sticky !important;
        left: 60px !important;
        z-index: 10 !important;
        background-color: #3a57e8 !important;
        width: 150px !important;
        min-width: 150px !important;
        max-width: 150px !important;
    }
    #sports-summary-table tbody tr:not(.table-warning) td:nth-child(2),
    #tableSportsSummary tbody tr:not(.table-warning) td:nth-child(2) {
        position: sticky !important;
        left: 60px !important;
        z-index: 8 !important;
        background-color: #ffffff !important;
        width: 150px !important;
        min-width: 150px !important;
        max-width: 150px !important;
    }

    /* Tên ĐV column */
    #sports-summary-table thead tr:first-child th:nth-child(3),
    #tableSportsSummary thead tr:first-child th:nth-child(3) {
        position: sticky !important;
        left: 210px !important;
        z-index: 10 !important;
        background-color: #3a57e8 !important;
        width: 200px !important;
        min-width: 200px !important;
        max-width: 200px !important;
    }
    #sports-summary-table tbody tr:not(.table-warning) td:nth-child(3),
    #tableSportsSummary tbody tr:not(.table-warning) td:nth-child(3) {
        position: sticky !important;
        left: 210px !important;
        z-index: 8 !important;
        background-color: #ffffff !important;
        width: 200px !important;
        min-width: 200px !important;
        max-width: 200px !important;
    }

    /* Subtotal row (colspan="3") */
    #sports-summary-table tbody tr.table-warning td:first-child,
    #tableSportsSummary tbody tr.table-warning td:first-child {
        position: sticky !important;
        left: 0 !important;
        z-index: 8 !important;
        background-color: #fff3cd !important;
        width: 410px !important;
        min-width: 410px !important;
        max-width: 410px !important;
    }

    /* Grand Total row (colspan="3") */
    #sports-summary-table tfoot tr td:first-child,
    #tableSportsSummary tfoot tr td:first-child {
        position: sticky !important;
        left: 0 !important;
        z-index: 8 !important;
        background-color: #d1e7dd !important;
        width: 410px !important;
        min-width: 410px !important;
        max-width: 410px !important;
    }

    /* Hover effect for row with sticky cells */
    #sports-summary-table tbody tr:not(.table-warning):hover td:nth-child(1),
    #sports-summary-table tbody tr:not(.table-warning):hover td:nth-child(2),
    #sports-summary-table tbody tr:not(.table-warning):hover td:nth-child(3),
    #tableSportsSummary tbody tr:not(.table-warning):hover td:nth-child(1),
    #tableSportsSummary tbody tr:not(.table-warning):hover td:nth-child(2),
    #tableSportsSummary tbody tr:not(.table-warning):hover td:nth-child(3) {
        background-color: #f1f3f5 !important;
    }
</style>