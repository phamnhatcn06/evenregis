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
                <div>
                    <a id="btn-export-teams" href="#" class="btn btn-primary btn-sm me-2">
                        <i class="fa fa-file-excel-o me-1"></i> Xuất số đội
                    </a>
                    <a id="btn-export-excel" href="#" class="btn btn-success btn-sm">
                        <i class="fa fa-file-excel-o me-1"></i> Xuất Excel
                    </a>
                </div>
            </div>
            
            <div class="sports-summary-wrapper" style="border-radius: 8px; border: 1px solid #e9ecef;">
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
    Yii::app()->theme->baseUrl . '/assets/js/pages/sport-teams-overview.js?v=1.0.3',
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

    /* Sports Summary Table Container */
    .sports-summary-wrapper {
        width: 100% !important;
        max-width: 100% !important;
        display: block !important;
        overflow: auto !important;
        max-height: 70vh;
        position: relative;
        border: 1px solid #dee2e6;
        border-radius: 8px;
    }

    /* Sports Summary Table */
    #sports-summary-table {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        margin: 0 !important;
    }

    #sports-summary-table th,
    #sports-summary-table td {
        border: 1px solid #dee2e6 !important;
        vertical-align: middle !important;
        white-space: nowrap;
    }

    /* Column widths - Số đội, Số VĐV */
    #sports-summary-table .col-num {
        width: 50px !important;
        min-width: 50px !important;
        padding: 4px 2px !important;
        font-size: 12px !important;
        text-align: center !important;
    }

    /* Column widths - Ghi chú */
    #sports-summary-table .col-note {
        width: 80px !important;
        min-width: 80px !important;
        max-width: 80px !important;
        padding: 4px 4px !important;
        font-size: 11px !important;
        text-align: left !important;
        white-space: normal !important;
        word-wrap: break-word !important;
    }

    /* Sticky Column Classes & Fallback Selectors */
    #sports-summary-table .col-sticky-stt,
    #sports-summary-table thead tr:first-child th:nth-child(1),
    #sports-summary-table tbody tr:not(.table-warning):not(.table-success) td:first-child {
        position: sticky !important;
        left: 0 !important;
        z-index: 10 !important;
        width: 50px !important;
        min-width: 50px !important;
        text-align: center;
    }
    #sports-summary-table .col-sticky-region,
    #sports-summary-table thead tr:first-child th:nth-child(2),
    #sports-summary-table tbody tr:not(.table-warning):not(.table-success) td[rowspan] {
        position: sticky !important;
        left: 50px !important;
        z-index: 10 !important;
        width: 120px !important;
        min-width: 120px !important;
    }
    #sports-summary-table .col-sticky-property,
    #sports-summary-table thead tr:first-child th:nth-child(3),
    #sports-summary-table tbody tr:not(.table-warning):not(.table-success) td:nth-child(3),
    #sports-summary-table tbody tr:not(.table-warning):not(.table-success) td:nth-child(2):not([rowspan]) {
        position: sticky !important;
        left: 170px !important;
        z-index: 10 !important;
        width: 180px !important;
        min-width: 180px !important;
    }
    #sports-summary-table .col-sticky-total,
    #sports-summary-table tbody tr.table-warning td:first-child,
    #sports-summary-table tbody tr.table-success td:first-child {
        position: sticky !important;
        left: 0 !important;
        z-index: 10 !important;
    }

    /* Header Sticky Cells Background */
    #sports-summary-table thead tr:first-child th.col-sticky-stt,
    #sports-summary-table thead tr:first-child th.col-sticky-region,
    #sports-summary-table thead tr:first-child th.col-sticky-property,
    #sports-summary-table thead tr:first-child th:nth-child(1),
    #sports-summary-table thead tr:first-child th:nth-child(2),
    #sports-summary-table thead tr:first-child th:nth-child(3) {
        background: #3a57e8 !important;
        color: #fff !important;
        z-index: 15 !important;
    }
    #sports-summary-table thead tr:first-child th.col-sticky-property,
    #sports-summary-table thead tr:first-child th:nth-child(3) {
        border-right: 2px solid #1e3a8a !important;
    }

    /* Header row 2 */
    #sports-summary-table thead tr:nth-child(2) th {
        background: #e9ecef !important;
        color: #495057 !important;
        font-size: 11px !important;
        padding: 4px 2px !important;
    }

    /* Sport group headers */
    #sports-summary-table thead tr:first-child th.sport-header {
        background: #0dcaf0 !important;
        color: #000 !important;
        font-weight: 600 !important;
        font-size: 12px !important;
        padding: 6px 4px !important;
    }

    /* Body Sticky Cells Background */
    #sports-summary-table tbody td.col-sticky-stt,
    #sports-summary-table tbody tr:not(.table-warning):not(.table-success) td:first-child {
        background: #fff !important;
    }
    #sports-summary-table tbody td.col-sticky-region,
    #sports-summary-table tbody tr:not(.table-warning):not(.table-success) td[rowspan] {
        background: #f8f9fa !important;
        font-weight: 600;
        vertical-align: middle !important;
    }
    #sports-summary-table tbody td.col-sticky-property,
    #sports-summary-table tbody tr:not(.table-warning):not(.table-success) td:nth-child(3),
    #sports-summary-table tbody tr:not(.table-warning):not(.table-success) td:nth-child(2):not([rowspan]) {
        background: #fff !important;
        border-right: 2px solid #dee2e6 !important;
    }

    /* Subtotal & Grand Total Sticky Background */
    #sports-summary-table tbody tr.table-warning td {
        background: #fff3cd !important;
        font-weight: 600 !important;
    }
    #sports-summary-table tbody tr.table-warning td.col-sticky-total,
    #sports-summary-table tbody tr.table-warning td:first-child {
        z-index: 11 !important;
    }

    #sports-summary-table tbody tr.table-success td {
        background: #198754 !important;
        color: #fff !important;
    }
    #sports-summary-table tbody tr.table-success td.col-sticky-total,
    #sports-summary-table tbody tr.table-success td:first-child {
        z-index: 11 !important;
    }

    /* Hover effect */
    #sports-summary-table tbody tr:not(.table-warning):not(.table-success):hover td {
        background: #e3f2fd !important;
    }

    /* Hover effect for sticky cells to avoid transparent background revealing underlying content */
    #sports-summary-table tbody tr:not(.table-warning):not(.table-success):hover td.col-sticky-stt,
    #sports-summary-table tbody tr:not(.table-warning):not(.table-success):hover td.col-sticky-region,
    #sports-summary-table tbody tr:not(.table-warning):not(.table-success):hover td.col-sticky-property,
    #sports-summary-table tbody tr:not(.table-warning):not(.table-success):hover td:first-child,
    #sports-summary-table tbody tr:not(.table-warning):not(.table-success):hover td[rowspan],
    #sports-summary-table tbody tr:not(.table-warning):not(.table-success):hover td:nth-child(3),
    #sports-summary-table tbody tr:not(.table-warning):not(.table-success):hover td:nth-child(2):not([rowspan]) {
        background: #e3f2fd !important;
    }

    /* Alternating row colors */
    #sports-summary-table tbody tr:not(.table-warning):not(.table-success):nth-child(even) td:nth-child(n+4) {
        background: #f8f9fa;
    }
</style>