<?php

/**
 * Reports Index View
 * @var ReportsController $this
 * @var bool $isHO
 * @var array $user
 * @var array $eventsList
 * @var string $selectedEventId
 * @var string $selectedEventName
 * @var array $submittedRegistrations
 * @var array $attendeeStats
 * @var array $talentEntries
 * @var array $statsByShow
 * @var array $statsByCategory
 * @var array $statsByProperty
 * @var array $kpis
 */
$this->breadcrumbs = array(
    Registrations::label(2) => array('admin'),
    Yii::t('app', 'Report'),
);
$tabtile = 'Báo cáo chung sự kiện';

Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl . '/assets/css/pages/reports-index.css');
Yii::app()->clientScript->registerScriptFile(Yii::app()->theme->baseUrl . '/assets/js/pages/reports-index.js', CClientScript::POS_END);
?>
<div class="card">
    <div class="card-body">
        <!-- Filter & Title Section -->
        <div class="row align-items-center mb-4">
            <div class="col-md-7">
                <h3 class="mb-1 text-primary fw-bold">Báo cáo tổng hợp Sự kiện</h3>
                <p class="text-muted mb-0">Thống kê số liệu đăng ký tham gia, nhân sự và các tiết mục văn nghệ của sự kiện: <strong class="text-secondary"><?php echo CHtml::encode($selectedEventName); ?></strong></p>
            </div>
            <div class="col-md-5 mt-3 mt-md-0">
                <form method="get" id="eventFilterForm" class="d-flex justify-content-md-end gap-2">
                    <div class="flex-grow-1" style="max-width: 320px;">
                        <select name="event_id" id="eventIdFilter" class="form-select border-2 border-primary shadow-sm" onchange="document.getElementById('eventFilterForm').submit();">
                            <?php foreach ($eventsList as $eId => $event): ?>
                                <?php
                                $eName = isset($event->name) ? $event->name : (isset($event['name']) ? $event['name'] : '');
                                $selected = ($eId == $selectedEventId) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $eId; ?>" <?php echo $selected; ?>>
                                    <?php echo CHtml::encode($eName); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary shadow-sm">
                        <i class="fa fa-refresh me-1"></i> Tải lại
                    </button>
                </form>
            </div>
        </div>

        <!-- KPI Cards Section -->
        <div class="row g-3 mb-4">
            <!-- Card 1: Registered Units -->
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm kpi-card bg-gradient-blue h-100 text-white">
                    <div class="card-body d-flex align-items-center justify-content-between p-4">
                        <div>
                            <span class="text-white-50 small fw-bold text-uppercase tracking-wider">Đơn vị đã đăng ký</span>
                            <h2 class="mb-0 mt-1 fw-bold text-white"><?php echo $kpis['registered_units']; ?> <span class="fs-6 text-white-50">/ <?php echo $kpis['total_units']; ?></span></h2>
                        </div>
                        <div class="kpi-icon bg-white-20 rounded-3 p-3">
                            <i class="fa fa-building fa-2x text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Card 2: Total Attendees -->
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm kpi-card bg-gradient-teal h-100 text-white">
                    <div class="card-body d-flex align-items-center justify-content-between p-4">
                        <div>
                            <span class="text-white-50 small fw-bold text-uppercase tracking-wider">Tổng người tham dự</span>
                            <h2 class="mb-0 mt-1 fw-bold text-white"><?php echo number_format($kpis['total_attendees']); ?></h2>
                        </div>
                        <div class="kpi-icon bg-white-20 rounded-3 p-3">
                            <i class="fa fa-users fa-2x text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Card 3: Total Talent Entries -->
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm kpi-card bg-gradient-orange h-100 text-white">
                    <div class="card-body d-flex align-items-center justify-content-between p-4">
                        <div>
                            <span class="text-white-50 small fw-bold text-uppercase tracking-wider">Tiết mục văn nghệ</span>
                            <h2 class="mb-0 mt-1 fw-bold text-white"><?php echo $kpis['total_talent_entries']; ?></h2>
                        </div>
                        <div class="kpi-icon bg-white-20 rounded-3 p-3">
                            <i class="fa fa-music fa-2x text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Card 4: Total Beauty Contestants -->
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm kpi-card bg-gradient-purple h-100 text-white">
                    <div class="card-body d-flex align-items-center justify-content-between p-4">
                        <div>
                            <span class="text-white-50 small fw-bold text-uppercase tracking-wider">Thí sinh thi Miss</span>
                            <h2 class="mb-0 mt-1 fw-bold text-white"><?php echo number_format($kpis['total_beauty_contestants']); ?></h2>
                        </div>
                        <div class="kpi-icon bg-white-20 rounded-3 p-3">
                            <i class="fa fa-female fa-2x text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs & Detailed Tables -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                        <!-- Navigation Tabs -->
                        <ul class="nav nav-tabs nav-fill flex-column flex-md-row gap-2 border-bottom-0 pb-3" id="reportsTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active text-start text-md-center px-4 py-3 rounded-3 shadow-sm border-0 d-flex align-items-center justify-content-md-center gap-2" id="registrations-tab" data-bs-toggle="tab" data-bs-target="#registrations-pane" type="button" role="tab" aria-controls="registrations-pane" aria-selected="true">
                                    <i class="fa fa-clipboard fa-lg"></i>
                                    <span>1. Đơn vị đã gửi đăng ký</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link text-start text-md-center px-4 py-3 rounded-3 shadow-sm border-0 d-flex align-items-center justify-content-md-center gap-2" id="attendees-tab" data-bs-toggle="tab" data-bs-target="#attendees-pane" type="button" role="tab" aria-controls="attendees-pane" aria-selected="false">
                                    <i class="fa fa-users fa-lg"></i>
                                    <span>2. Số người theo đơn vị</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link text-start text-md-center px-4 py-3 rounded-3 shadow-sm border-0 d-flex align-items-center justify-content-md-center gap-2" id="talent-list-tab" data-bs-toggle="tab" data-bs-target="#talent-list-pane" type="button" role="tab" aria-controls="talent-list-pane" aria-selected="false">
                                    <i class="fa fa-music fa-lg"></i>
                                    <span>3. Chi tiết tiết mục văn nghệ</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link text-start text-md-center px-4 py-3 rounded-3 shadow-sm border-0 d-flex align-items-center justify-content-md-center gap-2" id="beauty-contestants-tab" data-bs-toggle="tab" data-bs-target="#beauty-contestants-pane" type="button" role="tab" aria-controls="beauty-contestants-pane" aria-selected="false">
                                    <i class="fa fa-female fa-lg"></i>
                                    <span>4. Danh sách thí sinh thi Miss</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link text-start text-md-center px-4 py-3 rounded-3 shadow-sm border-0 d-flex align-items-center justify-content-md-center gap-2" id="sports-teams-tab" data-bs-toggle="tab" data-bs-target="#sports-teams-pane" type="button" role="tab" aria-controls="sports-teams-pane" aria-selected="false">
                                    <i class="fa fa-trophy fa-lg"></i>
                                    <span>5. Đội thể thao theo môn</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link text-start text-md-center px-4 py-3 rounded-3 shadow-sm border-0 d-flex align-items-center justify-content-md-center gap-2" id="sports-summary-tab" data-bs-toggle="tab" data-bs-target="#sports-summary-pane" type="button" role="tab" aria-controls="sports-summary-pane" aria-selected="false">
                                    <i class="fa fa-table fa-lg"></i>
                                    <span>6. Tổng hợp thể thao theo cụm</span>
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body p-4 pt-2">
                        <div class="tab-content" id="reportsTabContent">

                            <!-- TAB 1: ĐƠN VỊ ĐÃ GỬI ĐĂNG KÝ -->
                            <div class="tab-pane fade show active" id="registrations-pane" role="tabpanel" aria-labelledby="registrations-tab" tabindex="0">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                                    <h5 class="fw-bold mb-0 text-dark"><i class="fa fa-check-square-o text-primary me-2"></i>Danh sách đơn vị gửi đăng ký</h5>
                                    <div class="search-box" style="max-width: 300px;">
                                        <input type="text" id="searchRegs" class="form-control form-control-sm" placeholder="Tìm kiếm nhanh đơn vị...">
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover align-middle border mb-0" id="tableRegs">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" width="60">STT</th>
                                                <th width="120">Mã đơn vị</th>
                                                <th>Tên đơn vị</th>
                                                <th>Đợt đăng ký</th>
                                                <th class="text-center" width="160">Ngày gửi</th>
                                                <th>Người gửi</th>
                                                <th class="text-center" width="130">Trạng thái</th>
                                                <th>Người duyệt/Lý do</th>
                                                <th class="text-center" width="120">Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($submittedRegistrations)): ?>
                                                <tr>
                                                    <td colspan="9" class="text-center py-5 text-muted">
                                                        <i class="fa fa-folder-open-o fa-3x mb-3 text-white-50 d-block"></i>
                                                        Chưa có đơn vị nào gửi phiếu đăng ký cho sự kiện này.
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php $stt = 1;
                                                foreach ($submittedRegistrations as $reg): ?>
                                                    <?php
                                                    $regId = isset($reg->id) ? $reg->id : (isset($reg['id']) ? $reg['id'] : null);
                                                    $pCode = isset($reg->property_code) ? $reg->property_code : (isset($reg['property_code']) ? $reg['property_code'] : '');
                                                    $pName = isset($reg->property_name) ? $reg->property_name : (isset($reg['property_name']) ? $reg['property_name'] : '');
                                                    $periodName = isset($reg->period_name) ? $reg->period_name : (isset($reg['period_name']) ? $reg['period_name'] : '');
                                                    $subAt = isset($reg->submitted_at) ? date('H:i d/m/Y', strtotime($reg->submitted_at)) : '';
                                                    $subBy = isset($reg->submitted_by) ? $reg->submitted_by : (isset($reg['submitted_by']) ? $reg['submitted_by'] : '');
                                                    $revBy = isset($reg->reviewed_by_name) ? $reg->reviewed_by_name : (isset($reg['reviewed_by_name']) ? $reg['reviewed_by_name'] : '');
                                                    $reason = isset($reg->rejection_reason) ? $reg->rejection_reason : (isset($reg['rejection_reason']) ? $reg['rejection_reason'] : '');
                                                    $status = isset($reg->status) ? (int)$reg->status : 0;
                                                    ?>
                                                    <tr>
                                                        <td class="text-center fw-bold text-muted"><?php echo $stt++; ?></td>
                                                        <td><code><?php echo CHtml::encode($pCode); ?></code></td>
                                                        <td class="fw-bold text-dark"><?php echo CHtml::encode($pName); ?></td>
                                                        <td><span class="text-secondary small"><?php echo CHtml::encode($periodName); ?></span></td>
                                                        <td class="text-center small"><?php echo $subAt; ?></td>
                                                        <td><span class="small text-muted"><?php echo CHtml::encode($subBy); ?></span></td>
                                                        <td class="text-center"><?php echo Registrations::getStatusLabel($status); ?></td>
                                                        <td>
                                                            <?php if ($status === Registrations::STATUS_APPROVED): ?>
                                                                <span class="small text-success"><i class="fa fa-user-circle me-1"></i><?php echo CHtml::encode($revBy); ?></span>
                                                            <?php elseif ($status === Registrations::STATUS_REJECTED): ?>
                                                                <span class="small text-danger d-block"><i class="fa fa-exclamation-circle me-1"></i><?php echo CHtml::encode($revBy ?: 'Từ chối'); ?></span>
                                                                <?php if ($reason): ?>
                                                                    <small class="text-muted bg-light px-2 py-1 rounded d-inline-block mt-1 border-start border-danger border-2"><?php echo CHtml::encode($reason); ?></small>
                                                                <?php endif; ?>
                                                            <?php else: ?>
                                                                <span class="text-muted small">Chờ duyệt</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <?php if ($regId): ?>
                                                                <a href="<?php echo Yii::app()->createUrl('/admin/registrations/view', array('id' => $regId)); ?>" class="btn btn-sm btn-icon btn-soft-primary me-1" title="Xem chi tiết phiếu đăng ký">
                                                                    <i class="fa fa-eye"></i>
                                                                </a>
                                                                <a href="<?php echo Yii::app()->createUrl('/admin/reports/exportUnit', array('id' => $regId)); ?>" class="btn btn-sm btn-icon btn-soft-success" title="Xuất báo cáo chi tiết đơn vị">
                                                                    <i class="fa fa-file-excel-o"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- TAB 2: SỐ NGƯỜI THAM DỰ THEO ĐƠN VỊ -->
                            <div class="tab-pane fade" id="attendees-pane" role="tabpanel" aria-labelledby="attendees-tab" tabindex="0">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                                    <h5 class="fw-bold mb-0 text-dark"><i class="fa fa-users text-primary me-2"></i>Thống kê người tham dự theo đơn vị</h5>
                                    <div class="search-box" style="max-width: 300px;">
                                        <input type="text" id="searchAttendees" class="form-control form-control-sm" placeholder="Tìm kiếm nhanh đơn vị...">
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover align-middle border mb-0" id="tableAttendees">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" width="60">STT</th>
                                                <th width="120">Mã đơn vị</th>
                                                <th>Tên đơn vị</th>
                                                <th class="text-center bg-soft-primary fw-bold" width="130">Tổng người</th>
                                                <th class="text-center text-success" width="120">Đã duyệt</th>
                                                <th class="text-center text-warning" width="120">Chờ duyệt</th>
                                                <th class="text-center text-danger" width="120">Từ chối</th>
                                                <th>Cơ cấu thành phần (Vai trò / Chức danh)</th>
                                                <th class="text-center" width="100">Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $totalAll = 0;
                                            $totalApproved = 0;
                                            $totalPending = 0;
                                            $totalRejected = 0;
                                            $stt = 1;
                                            ?>
                                            <?php if (empty($attendeeStats)): ?>
                                                <tr>
                                                    <td colspan="9" class="text-center py-5 text-muted">Không tìm thấy thông tin đơn vị nào.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($attendeeStats as $pId => $stats): ?>
                                                    <?php
                                                    $totalAll += $stats['total'];
                                                    $totalApproved += $stats['approved'];
                                                    $totalPending += $stats['pending'];
                                                    $totalRejected += $stats['rejected'];
                                                    ?>
                                                    <tr class="<?php echo ($stats['total'] > 0) ? '' : 'text-muted'; ?>">
                                                        <td class="text-center fw-bold text-muted"><?php echo $stt++; ?></td>
                                                        <td><code><?php echo CHtml::encode($stats['property_code']); ?></code></td>
                                                        <td class="fw-bold text-dark"><?php echo CHtml::encode($stats['property_name']); ?></td>
                                                        <td class="text-center bg-soft-primary fw-bold text-primary fs-5"><?php echo $stats['total']; ?></td>
                                                        <td class="text-center text-success fw-bold"><?php echo $stats['approved']; ?></td>
                                                        <td class="text-center text-warning fw-bold"><?php echo $stats['pending']; ?></td>
                                                        <td class="text-center text-danger fw-bold"><?php echo $stats['rejected']; ?></td>
                                                        <td>
                                                            <div class="d-flex flex-wrap gap-1">
                                                                <?php if (empty($stats['roles'])): ?>
                                                                    <span class="text-muted small italic">Chưa có người tham dự</span>
                                                                <?php else: ?>
                                                                    <?php foreach ($stats['roles'] as $roleName => $count): ?>
                                                                        <span class="badge badge-pill rounded-3 <?php echo Attendees::getRoleBadgeClass($roleName); ?>">
                                                                            <?php echo CHtml::encode($roleName); ?>: <?php echo $count; ?>
                                                                        </span>
                                                                    <?php endforeach; ?>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <?php 
                                                            $reg = isset($propertyRegistrationMap[$pId]) ? $propertyRegistrationMap[$pId] : null;
                                                            $regId = $reg ? (isset($reg->id) ? $reg->id : (isset($reg['id']) ? $reg['id'] : null)) : null;
                                                            if ($regId): 
                                                            ?>
                                                                <a href="<?php echo Yii::app()->createUrl('/admin/reports/exportUnit', array('id' => $regId)); ?>" class="btn btn-sm btn-icon btn-soft-success" title="Xuất báo cáo chi tiết đơn vị">
                                                                    <i class="fa fa-file-excel-o"></i>
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="text-muted small italic">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                        <tfoot class="table-light fw-bold text-dark fs-6 border-top-2">
                                            <tr>
                                                <td colspan="3" class="text-end">TỔNG CỘNG:</td>
                                                <td class="text-center bg-primary text-white fs-4"><?php echo $totalAll; ?></td>
                                                <td class="text-center text-success fs-5"><?php echo $totalApproved; ?></td>
                                                <td class="text-center text-warning fs-5"><?php echo $totalPending; ?></td>
                                                <td class="text-center text-danger fs-5"><?php echo $totalRejected; ?></td>
                                                <td><span class="text-muted small fw-normal">Tổng hợp tất cả nhân sự tham gia sự kiện của các đơn vị.</span></td>
                                                <td class="text-center">--</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <!-- TAB 3: DANH SÁCH CHI TIẾT TIẾT MỤC VĂN NGHỆ -->
                            <div class="tab-pane fade" id="talent-list-pane" role="tabpanel" aria-labelledby="talent-list-tab" tabindex="0">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                                    <h5 class="fw-bold mb-0 text-dark"><i class="fa fa-music text-primary me-2"></i>Danh sách tiết mục văn nghệ đã đăng ký</h5>
                                    <div class="search-box" style="max-width: 300px;">
                                        <input type="text" id="searchTalentList" class="form-control form-control-sm" placeholder="Tìm tên tiết mục, thể loại, đơn vị...">
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover align-middle border mb-0" id="tableTalentList">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" width="60">STT</th>
                                                <th>Đơn vị biểu diễn</th>
                                                <th width="200">Thể loại</th>
                                                <th class="fw-bold text-primary">Tên tiết mục</th>
                                                <th class="text-center" width="180">Trạng thái</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($talentEntries)): ?>
                                                <tr>
                                                    <td colspan="5" class="text-center py-5 text-muted">
                                                        <i class="fa fa-music fa-3x mb-3 text-white-50 d-block"></i>
                                                        Chưa có tiết mục văn nghệ nào được đăng ký cho sự kiện này.
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php $stt = 1; foreach ($talentEntries as $entry): ?>
                                                    <?php
                                                    $ePropName = isset($entry->property_name) ? $entry->property_name : (isset($entry['property_name']) ? $entry['property_name'] : 'Không xác định');
                                                    $eCatName = isset($entry->category_name) ? $entry->category_name : (isset($entry['category_name']) ? $entry['category_name'] : '');
                                                    $eTitle = isset($entry->title) ? $entry->title : (isset($entry['title']) ? $entry['title'] : '');
                                                    $status = isset($entry->status) ? (int)$entry->status : 0;
                                                    ?>
                                                    <tr>
                                                        <td class="text-center fw-bold text-muted"><?php echo $stt++; ?></td>
                                                        <td class="fw-bold text-dark"><?php echo CHtml::encode($ePropName); ?></td>
                                                        <td><span class="badge bg-soft-info text-info rounded-3 px-3 py-2"><?php echo CHtml::encode($eCatName); ?></span></td>
                                                        <td class="fw-bold text-primary fs-6"><?php echo CHtml::encode($eTitle); ?></td>
                                                        <td class="text-center">
                                                            <?php echo TalentEntries::getStatusLabel($status); ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- TAB 4: DANH SÁCH THÍ SINH THI MISS -->
                            <div class="tab-pane fade" id="beauty-contestants-pane" role="tabpanel" aria-labelledby="beauty-contestants-tab" tabindex="0">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                                    <h5 class="fw-bold mb-0 text-dark"><i class="fa fa-female text-primary me-2"></i>Danh sách thí sinh thi Miss sắc đẹp</h5>
                                    <div class="search-box" style="max-width: 300px;">
                                        <input type="text" id="searchBeauty" class="form-control form-control-sm" placeholder="Tìm tên thí sinh, đơn vị, cuộc thi...">
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover align-middle border mb-0" id="tableBeauty">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" width="60">STT</th>
                                                <th>Đơn vị</th>
                                                <th class="fw-bold text-primary">Họ tên thí sinh</th>
                                                <th class="text-center" width="180">Trạng thái</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($beautyContestantsList)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center py-5 text-muted">
                                                        <i class="fa fa-female fa-3x mb-3 text-white-50 d-block"></i>
                                                        Chưa có thí sinh nào đăng ký tham gia thi Miss sắc đẹp.
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php $stt = 1; foreach ($beautyContestantsList as $c): ?>
                                                    <?php
                                                    $cPropCode = isset($c->property_code) ? $c->property_code : '';
                                                    $cPropName = isset($c->property_name) ? $c->property_name : '';
                                                    $cName = isset($c->attendee_name) ? $c->attendee_name : '';
                                                    $cStatus = isset($c->status) ? (int)$c->status : 0;
                                                    ?>
                                                    <tr>
                                                        <td class="text-center fw-bold text-muted"><?php echo $stt++; ?></td>
                                                        <td>
                                                            <code><?php echo CHtml::encode($cPropCode); ?></code> - 
                                                            <span class="fw-bold text-dark"><?php echo CHtml::encode($cPropName); ?></span>
                                                        </td>
                                                        <td class="fw-bold text-primary fs-6"><?php echo CHtml::encode($cName); ?></td>
                                                        <td class="text-center"><?php echo BeautyContestants::getStatusLabel($cStatus); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- TAB 5: ĐỘI THỂ THAO THEO MÔN -->
                            <div class="tab-pane fade" id="sports-teams-pane" role="tabpanel" aria-labelledby="sports-teams-tab" tabindex="0">
                                <div class="row g-4 mb-4">
                                    <!-- Left side: Summary Table -->
                                    <div class="col-lg-4">
                                        <div class="card border border-2 shadow-none h-100">
                                            <div class="card-header bg-light py-3">
                                                <h6 class="fw-bold mb-0 text-dark"><i class="fa fa-pie-chart text-primary me-2"></i>Tổng hợp theo môn</h6>
                                            </div>
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table align-middle mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Môn thi đấu</th>
                                                                <th class="text-center" width="100">Số đội</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php if (empty($statsBySport)): ?>
                                                                <tr>
                                                                    <td colspan="2" class="text-center py-4 text-muted small italic">Chưa có đội đăng ký môn nào</td>
                                                                </tr>
                                                            <?php else: ?>
                                                                <?php foreach ($statsBySport as $spId => $spStat): ?>
                                                                    <tr>
                                                                        <td class="fw-bold text-dark"><?php echo CHtml::encode($spStat['name']); ?></td>
                                                                        <td class="text-center"><span class="badge bg-soft-primary text-primary rounded-3 px-3 py-2 fw-bold fs-6"><?php echo $spStat['team_count']; ?></span></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right side: Detail List -->
                                    <div class="col-lg-8">
                                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                                            <h5 class="fw-bold mb-0 text-dark"><i class="fa fa-trophy text-primary me-2"></i>Chi tiết các đội thể thao</h5>
                                            <div class="search-box" style="max-width: 300px;">
                                                <input type="text" id="searchSportsTeams" class="form-control form-control-sm" placeholder="Tìm tên môn, tên đội, đơn vị...">
                                            </div>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle border mb-0" id="tableSportsTeams">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="text-center" width="60">STT</th>
                                                        <th>Môn thi đấu</th>
                                                        <th class="fw-bold text-primary">Tên đội thể thao</th>
                                                        <th>Đơn vị đăng ký</th>
                                                        <th class="text-center" width="150">Trạng thái</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($sportTeams)): ?>
                                                        <tr>
                                                            <td colspan="5" class="text-center py-5 text-muted">
                                                                <i class="fa fa-trophy fa-3x mb-3 text-white-50 d-block"></i>
                                                                Chưa có đội thể thao nào được đăng ký cho sự kiện này.
                                                            </td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php $stt = 1; foreach ($sportTeams as $team): ?>
                                                            <?php
                                                            $spName = isset($team->sport_name) ? $team->sport_name : '';
                                                            $tName = isset($team->team_name) ? $team->team_name : (isset($team->name) ? $team->name : (isset($team['name']) ? $team['name'] : ''));
                                                            $pCode = isset($team->property_code) ? $team->property_code : '';
                                                            $pName = isset($team->property_name) ? $team->property_name : '';
                                                            $status = isset($team->status) ? (int)$team->status : 0;
                                                            ?>
                                                            <tr>
                                                                <td class="text-center fw-bold text-muted"><?php echo $stt++; ?></td>
                                                                <td><span class="badge bg-soft-info text-info rounded-3 px-3 py-2"><?php echo CHtml::encode($spName); ?></span></td>
                                                                <td class="fw-bold text-dark fs-6"><?php echo CHtml::encode($tName); ?></td>
                                                                <td>
                                                                    <?php if ($pCode): ?>
                                                                        <code><?php echo CHtml::encode($pCode); ?></code> - 
                                                                    <?php endif; ?>
                                                                    <span class="small text-muted"><?php echo CHtml::encode($pName); ?></span>
                                                                </td>
                                                                <td class="text-center"><?php echo SportTeams::getStatusLabel($status); ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 6: TỔNG HỢP THỂ THAO THEO CỤM -->
                            <div class="tab-pane fade" id="sports-summary-pane" role="tabpanel" aria-labelledby="sports-summary-tab" tabindex="0">
                                <?php
                                // Ensure variables exist
                                if (!isset($activeSportsForReport)) $activeSportsForReport = array();
                                if (!isset($sportsReportData)) $sportsReportData = array();
                                if (!isset($regionalMap)) $regionalMap = array();
                                if (!isset($propertyRegionalMap)) $propertyRegionalMap = array();
                                ?>
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                                    <h5 class="fw-bold mb-0 text-dark"><i class="fa fa-table text-primary me-2"></i>Tổng hợp đăng ký thể thao theo cụm và đơn vị</h5>
                                    <a href="<?php echo Yii::app()->createUrl('/admin/reports/exportSports', array('event_id' => $selectedEventId)); ?>" class="btn btn-success">
                                        <i class="fa fa-file-excel-o me-1"></i> Xuất báo cáo Excel
                                    </a>
                                </div>

                                <?php if (empty($activeSportsForReport)): ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="fa fa-trophy fa-3x mb-3 text-white-50 d-block"></i>
                                        Chưa có môn thể thao nào được đăng ký cho sự kiện này.
                                    </div>
                                <?php else: ?>
                                    <div class="sports-summary-wrapper">
                                        <table class="table table-bordered table-hover align-middle mb-0" id="tableSportsSummary">
                                            <thead>
                                                <tr>
                                                    <th rowspan="2" class="text-center align-middle">STT</th>
                                                    <th rowspan="2" class="text-center align-middle">Cụm</th>
                                                    <th rowspan="2" class="align-middle">Tên ĐV</th>
                                                    <?php foreach ($activeSportsForReport as $spId => $spName): ?>
                                                        <th colspan="3" class="text-center bg-soft-info"><?php echo CHtml::encode($spName); ?></th>
                                                    <?php endforeach; ?>
                                                </tr>
                                                <tr>
                                                    <?php foreach ($activeSportsForReport as $spId => $spName): ?>
                                                        <th class="text-center col-num">Đội</th>
                                                        <th class="text-center col-num">VĐV</th>
                                                        <th class="text-center col-note">Ghi chú</th>
                                                    <?php endforeach; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $stt = 1;
                                                $grandTotals = array();
                                                foreach ($activeSportsForReport as $spId => $spName) {
                                                    $grandTotals[$spId] = array('team_count' => 0, 'member_count' => 0);
                                                }

                                                // Sort regions
                                                ksort($sportsReportData);

                                                foreach ($sportsReportData as $regionId => $propData):
                                                    $regionName = ($regionId && isset($regionalMap[$regionId])) ? $regionalMap[$regionId] : 'Chưa phân cụm';
                                                    $regionTotals = array();
                                                    foreach ($activeSportsForReport as $spId => $spName) {
                                                        $regionTotals[$spId] = array('team_count' => 0, 'member_count' => 0);
                                                    }

                                                    // Sort properties by code
                                                    uksort($propData, function($a, $b) use ($propertyRegionalMap) {
                                                        $codeA = isset($propertyRegionalMap[$a]) ? $propertyRegionalMap[$a]['code'] : '';
                                                        $codeB = isset($propertyRegionalMap[$b]) ? $propertyRegionalMap[$b]['code'] : '';
                                                        return strnatcasecmp($codeA, $codeB);
                                                    });

                                                    foreach ($propData as $propId => $sportsData):
                                                        $propInfo = isset($propertyRegionalMap[$propId]) ? $propertyRegionalMap[$propId] : null;
                                                        $propName = $propInfo ? $propInfo['name'] : 'Không xác định';
                                                ?>
                                                    <tr>
                                                        <td class="text-center fw-bold text-muted"><?php echo $stt++; ?></td>
                                                        <td class="small"><?php echo CHtml::encode($regionName); ?></td>
                                                        <td class="fw-bold text-dark"><?php echo CHtml::encode($propName); ?></td>
                                                        <?php foreach ($activeSportsForReport as $spId => $spName):
                                                            $teamCount = isset($sportsData[$spId]) ? $sportsData[$spId]['team_count'] : 0;
                                                            $memberCount = isset($sportsData[$spId]) ? $sportsData[$spId]['member_count'] : 0;
                                                            $note = isset($sportsData[$spId]['note']) ? $sportsData[$spId]['note'] : '';
                                                            $regionTotals[$spId]['team_count'] += $teamCount;
                                                            $regionTotals[$spId]['member_count'] += $memberCount;
                                                            $grandTotals[$spId]['team_count'] += $teamCount;
                                                            $grandTotals[$spId]['member_count'] += $memberCount;
                                                        ?>
                                                            <td class="col-num <?php echo $teamCount ? 'text-primary fw-bold' : 'text-muted'; ?>"><?php echo $teamCount ?: '-'; ?></td>
                                                            <td class="col-num <?php echo $memberCount ? 'text-success fw-bold' : 'text-muted'; ?>"><?php echo $memberCount ?: '-'; ?></td>
                                                            <td class="col-note text-muted"><?php echo $note ? CHtml::encode($note) : '-'; ?></td>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                                    <!-- Region subtotal -->
                                                    <tr class="table-warning">
                                                        <td colspan="3" class="text-end fw-bold">Tổng <?php echo CHtml::encode($regionName); ?>:</td>
                                                        <?php foreach ($activeSportsForReport as $spId => $spName): ?>
                                                            <td class="col-num fw-bold"><?php echo $regionTotals[$spId]['team_count']; ?></td>
                                                            <td class="col-num fw-bold"><?php echo $regionTotals[$spId]['member_count']; ?></td>
                                                            <td class="col-note"></td>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot class="table-success">
                                                <tr>
                                                    <td colspan="3" class="text-end fw-bold fs-6">TỔNG CỘNG:</td>
                                                    <?php foreach ($activeSportsForReport as $spId => $spName): ?>
                                                        <td class="col-num fw-bold fs-6"><?php echo $grandTotals[$spId]['team_count']; ?></td>
                                                        <td class="col-num fw-bold fs-6"><?php echo $grandTotals[$spId]['member_count']; ?></td>
                                                        <td class="col-note"></td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Sports Summary Table Container */
    .sports-summary-wrapper {
        overflow: auto;
        max-height: 70vh;
        position: relative;
    }

    /* Sports Summary Table */
    #tableSportsSummary {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        min-width: max-content;
    }

    #tableSportsSummary th,
    #tableSportsSummary td {
        border: 1px solid #dee2e6 !important;
        vertical-align: middle !important;
    }

    /* Column widths - Số đội, Số VĐV */
    #tableSportsSummary .col-num {
        width: 50px !important;
        min-width: 50px !important;
        max-width: 50px !important;
        padding: 4px 2px !important;
        font-size: 12px !important;
        text-align: center !important;
    }

    /* Column widths - Ghi chú (wider, wrap text) */
    #tableSportsSummary .col-note {
        width: 80px !important;
        min-width: 80px !important;
        max-width: 80px !important;
        padding: 4px 4px !important;
        font-size: 11px !important;
        text-align: left !important;
        white-space: normal !important;
        word-wrap: break-word !important;
    }

    /* Sticky header rows */
    #tableSportsSummary thead tr:first-child th {
        position: sticky !important;
        top: 0 !important;
        z-index: 10 !important;
    }
    #tableSportsSummary thead tr:nth-child(2) th {
        position: sticky !important;
        top: 38px !important;
        z-index: 10 !important;
        background: #e9ecef !important;
        color: #495057 !important;
        font-size: 11px !important;
        padding: 4px 2px !important;
    }

    /* Frozen columns - STT */
    #tableSportsSummary thead tr:first-child th:nth-child(1) {
        left: 0 !important;
        z-index: 13 !important;
        background: #3a57e8 !important;
        color: #fff !important;
        width: 50px !important;
        min-width: 50px !important;
        max-width: 50px !important;
    }
    #tableSportsSummary tbody td:nth-child(1) {
        position: sticky !important;
        left: 0 !important;
        z-index: 8 !important;
        background: #fff !important;
        width: 50px !important;
        min-width: 50px !important;
        max-width: 50px !important;
    }

    /* Frozen columns - Cụm */
    #tableSportsSummary thead tr:first-child th:nth-child(2) {
        left: 50px !important;
        z-index: 13 !important;
        background: #3a57e8 !important;
        color: #fff !important;
        width: 120px !important;
        min-width: 120px !important;
        max-width: 120px !important;
    }
    #tableSportsSummary tbody td:nth-child(2) {
        position: sticky !important;
        left: 50px !important;
        z-index: 8 !important;
        background: #f8f9fa !important;
        width: 120px !important;
        min-width: 120px !important;
        max-width: 120px !important;
    }

    /* Frozen columns - Tên ĐV */
    #tableSportsSummary thead tr:first-child th:nth-child(3) {
        left: 170px !important;
        z-index: 13 !important;
        background: #3a57e8 !important;
        color: #fff !important;
        width: 180px !important;
        min-width: 180px !important;
        max-width: 180px !important;
        border-right: 2px solid #1e3a8a !important;
    }
    #tableSportsSummary tbody td:nth-child(3) {
        position: sticky !important;
        left: 170px !important;
        z-index: 8 !important;
        background: #fff !important;
        width: 180px !important;
        min-width: 180px !important;
        max-width: 180px !important;
        border-right: 2px solid #adb5bd !important;
        box-shadow: 2px 0 4px rgba(0,0,0,0.08);
    }

    /* Sport group headers */
    #tableSportsSummary thead tr:first-child th.bg-soft-info {
        background: #0dcaf0 !important;
        color: #000 !important;
        font-weight: 600 !important;
        font-size: 12px !important;
        padding: 6px 4px !important;
    }

    /* Subtotal row - override sticky bg */
    #tableSportsSummary tbody tr.table-warning td {
        background: #fff3cd !important;
        font-weight: 600 !important;
    }
    #tableSportsSummary tbody tr.table-warning td:nth-child(1),
    #tableSportsSummary tbody tr.table-warning td:nth-child(2),
    #tableSportsSummary tbody tr.table-warning td:nth-child(3) {
        background: #fff3cd !important;
    }

    /* Grand Total row - override sticky bg */
    #tableSportsSummary tfoot tr td {
        background: #198754 !important;
        color: #fff !important;
    }
    #tableSportsSummary tfoot tr td:nth-child(1),
    #tableSportsSummary tfoot tr td:nth-child(2),
    #tableSportsSummary tfoot tr td:nth-child(3) {
        position: sticky !important;
        left: 0 !important;
        background: #198754 !important;
        color: #fff !important;
    }
    #tableSportsSummary tfoot tr td:nth-child(2) {
        left: 50px !important;
    }
    #tableSportsSummary tfoot tr td:nth-child(3) {
        left: 170px !important;
    }

    /* Hover effect */
    #tableSportsSummary tbody tr:not(.table-warning):hover td {
        background: #e3f2fd !important;
    }
    #tableSportsSummary tbody tr:not(.table-warning):hover td:nth-child(1),
    #tableSportsSummary tbody tr:not(.table-warning):hover td:nth-child(2),
    #tableSportsSummary tbody tr:not(.table-warning):hover td:nth-child(3) {
        background: #bbdefb !important;
    }

    /* Alternating row colors */
    #tableSportsSummary tbody tr:not(.table-warning):nth-child(even) td:nth-child(n+4) {
        background: #f8f9fa;
    }
</style>