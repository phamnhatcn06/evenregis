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
?>

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
    <!-- Card 4: Total Talent Participants -->
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm kpi-card bg-gradient-purple h-100 text-white">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <span class="text-white-50 small fw-bold text-uppercase tracking-wider">Diễn viên văn nghệ</span>
                    <h2 class="mb-0 mt-1 fw-bold text-white"><?php echo number_format($kpis['total_talent_participants']); ?></h2>
                </div>
                <div class="kpi-icon bg-white-20 rounded-3 p-3">
                    <i class="fa fa-user-circle fa-2x text-white"></i>
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
                        <button class="nav-link text-start text-md-center px-4 py-3 rounded-3 shadow-sm border-0 d-flex align-items-center justify-content-md-center gap-2" id="talent-stats-tab" data-bs-toggle="tab" data-bs-target="#talent-stats-pane" type="button" role="tab" aria-controls="talent-stats-pane" aria-selected="false">
                            <i class="fa fa-bar-chart fa-lg"></i>
                            <span>4. Thống kê số lượng tiết mục</span>
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
                                        <th class="text-center" width="90">Chi tiết</th>
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
                                        <?php $stt = 1; foreach ($submittedRegistrations as $reg): ?>
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
                                                        <a href="<?php echo Yii::app()->createUrl('/admin/registrations/view', array('id' => $regId)); ?>" class="btn btn-sm btn-icon btn-soft-primary" title="Xem chi tiết phiếu đăng ký">
                                                            <i class="fa fa-eye"></i>
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
                                            <td colspan="8" class="text-center py-5 text-muted">Không tìm thấy thông tin đơn vị nào.</td>
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
                                        <th>Hội diễn</th>
                                        <th width="150">Thể loại</th>
                                        <th class="fw-bold text-primary">Tên tiết mục</th>
                                        <th class="text-center" width="130">Số diễn viên</th>
                                        <th>Đạo diễn / Biên đạo</th>
                                        <th class="text-center" width="130">Trạng thái</th>
                                        <th class="text-center" width="90">Chi tiết</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $stt = 1; 
                                    $totalParticipants = 0;
                                    ?>
                                    <?php if (empty($talentEntries)): ?>
                                        <tr>
                                            <td colspan="9" class="text-center py-5 text-muted">
                                                <i class="fa fa-music fa-3x mb-3 text-white-50 d-block"></i>
                                                Chưa có tiết mục văn nghệ nào được đăng ký cho sự kiện này.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($talentEntries as $entry): ?>
                                            <?php 
                                            $entryId = isset($entry->id) ? $entry->id : (isset($entry['id']) ? $entry['id'] : null);
                                            $eRegId = isset($entry->registration_id) ? $entry->registration_id : (isset($entry['registration_id']) ? $entry['registration_id'] : null);
                                            $ePropName = isset($entry->property_name) ? $entry->property_name : (isset($entry['property_name']) ? $entry['property_name'] : 'Không xác định');
                                            $eShowName = isset($entry->show_name) ? $entry->show_name : (isset($entry['show_name']) ? $entry['show_name'] : '');
                                            $eCatName = isset($entry->category_name) ? $entry->category_name : (isset($entry['category_name']) ? $entry['category_name'] : '');
                                            $eTitle = isset($entry->title) ? $entry->title : (isset($entry['title']) ? $entry['title'] : '');
                                            $ePCount = isset($entry->participant_count) ? (int)$entry->participant_count : 0;
                                            $eDir = isset($entry->director) ? $entry->director : (isset($entry['director']) ? $entry['director'] : '');
                                            $eDirPhone = isset($entry->director_phone) ? $entry->director_phone : (isset($entry['director_phone']) ? $entry['director_phone'] : '');
                                            $status = isset($entry->status) ? (int)$entry->status : 0;
                                            
                                            $totalParticipants += $ePCount;
                                            ?>
                                            <tr>
                                                <td class="text-center fw-bold text-muted"><?php echo $stt++; ?></td>
                                                <td class="fw-bold text-dark"><?php echo CHtml::encode($ePropName); ?></td>
                                                <td><span class="text-secondary small"><?php echo CHtml::encode($eShowName); ?></span></td>
                                                <td><span class="badge bg-soft-info text-info rounded-3 px-3 py-2"><?php echo CHtml::encode($eCatName); ?></span></td>
                                                <td class="fw-bold text-primary fs-6"><?php echo CHtml::encode($eTitle); ?></td>
                                                <td class="text-center fw-bold fs-5 text-dark"><?php echo $ePCount; ?></td>
                                                <td>
                                                    <?php if ($eDir): ?>
                                                        <span class="text-dark d-block fw-bold small"><?php echo CHtml::encode($eDir); ?></span>
                                                        <?php if ($eDirPhone): ?>
                                                            <small class="text-muted"><i class="fa fa-phone me-1"></i><?php echo CHtml::encode($eDirPhone); ?></small>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted small">--</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php echo TalentEntries::getStatusLabel($status); ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($eRegId): ?>
                                                        <a href="<?php echo Yii::app()->createUrl('/admin/registrations/view', array('id' => $eRegId)); ?>" class="btn btn-sm btn-icon btn-soft-primary" title="Xem chi tiết phiếu đăng ký">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                                <?php if (!empty($talentEntries)): ?>
                                    <tfoot class="table-light fw-bold text-dark border-top-2">
                                        <tr>
                                            <td colspan="5" class="text-end">TỔNG SỐ DIỄN VIÊN VĂN NGHỆ:</td>
                                            <td class="text-center bg-primary text-white fs-4"><?php echo $totalParticipants; ?></td>
                                            <td colspan="3"><span class="text-muted small fw-normal">Tổng hợp diễn viên của tất cả tiết mục đăng ký.</span></td>
                                        </tr>
                                    </tfoot>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 4: THỐNG KÊ SỐ LƯỢNG TIẾT MỤC -->
                    <div class="tab-pane fade" id="talent-stats-pane" role="tabpanel" aria-labelledby="talent-stats-tab" tabindex="0">
                        <div class="row g-4">
                            
                            <!-- Bảng 4.1: Theo Hội diễn -->
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 bg-light h-100">
                                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="fa fa-star text-warning me-2"></i>Thống kê theo Hội diễn</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover align-middle bg-white border mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Tên Hội diễn</th>
                                                    <th class="text-center" width="120">Số tiết mục</th>
                                                    <th class="text-center" width="120">Số diễn viên</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $tShows = 0; $tShowP = 0;
                                                if (empty($statsByShow)): 
                                                ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center py-3 text-muted">Không có dữ liệu.</td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($statsByShow as $sId => $stats): ?>
                                                        <?php $tShows += $stats['count']; $tShowP += $stats['participants']; ?>
                                                        <tr>
                                                            <td class="fw-bold"><?php echo CHtml::encode($stats['name']); ?></td>
                                                            <td class="text-center fw-bold text-primary fs-5"><?php echo $stats['count']; ?></td>
                                                            <td class="text-center fw-bold text-dark fs-5"><?php echo $stats['participants']; ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                            <tfoot class="table-light fw-bold border-top">
                                                <tr>
                                                    <td>TỔNG CỘNG:</td>
                                                    <td class="text-center text-primary fs-5"><?php echo $tShows; ?></td>
                                                    <td class="text-center text-dark fs-5"><?php echo $tShowP; ?></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Bảng 4.2: Theo Thể loại -->
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 bg-light h-100">
                                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="fa fa-tags text-info me-2"></i>Thống kê theo Thể loại</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover align-middle bg-white border mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Tên Thể loại</th>
                                                    <th class="text-center" width="120">Số tiết mục</th>
                                                    <th class="text-center" width="120">Số diễn viên</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $tCats = 0; $tCatP = 0;
                                                if (empty($statsByCategory)): 
                                                ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center py-3 text-muted">Chưa có tiết mục đăng ký.</td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($statsByCategory as $cId => $stats): ?>
                                                        <?php $tCats += $stats['count']; $tCatP += $stats['participants']; ?>
                                                        <tr>
                                                            <td class="fw-bold"><?php echo CHtml::encode($stats['name']); ?></td>
                                                            <td class="text-center fw-bold text-primary fs-5"><?php echo $stats['count']; ?></td>
                                                            <td class="text-center fw-bold text-dark fs-5"><?php echo $stats['participants']; ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                            <tfoot class="table-light fw-bold border-top">
                                                <tr>
                                                    <td>TỔNG CỘNG:</td>
                                                    <td class="text-center text-primary fs-5"><?php echo $tCats; ?></td>
                                                    <td class="text-center text-dark fs-5"><?php echo $tCatP; ?></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Bảng 4.3: Theo Đơn vị -->
                            <div class="col-12 mt-4">
                                <div class="border rounded-3 p-3 bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                        <h6 class="fw-bold text-dark mb-0"><i class="fa fa-building text-primary me-2"></i>Thống kê số lượng tiết mục theo Đơn vị</h6>
                                        <div class="search-box" style="max-width: 250px;">
                                            <input type="text" id="searchTalentProp" class="form-control form-control-sm" placeholder="Lọc theo tên đơn vị...">
                                        </div>
                                    </div>
                                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                        <table class="table table-sm table-hover align-middle bg-white border mb-0" id="tableTalentProp">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th class="text-center" width="60">STT</th>
                                                    <th>Tên Đơn vị</th>
                                                    <th class="text-center" width="160">Số tiết mục đăng ký</th>
                                                    <th class="text-center" width="160">Tổng số diễn viên tham gia</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $tProps = 0; $tPropP = 0;
                                                $stt = 1;
                                                if (empty($statsByProperty)): 
                                                ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center py-3 text-muted">Chưa có đơn vị nào đăng ký văn nghệ.</td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($statsByProperty as $pId => $stats): ?>
                                                        <?php $tProps += $stats['count']; $tPropP += $stats['participants']; ?>
                                                        <tr>
                                                            <td class="text-center text-muted fw-bold"><?php echo $stt++; ?></td>
                                                            <td class="fw-bold text-dark"><?php echo CHtml::encode($stats['name']); ?></td>
                                                            <td class="text-center fw-bold text-primary fs-5"><?php echo $stats['count']; ?></td>
                                                            <td class="text-center fw-bold text-dark fs-5"><?php echo $stats['participants']; ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                            <?php if (!empty($statsByProperty)): ?>
                                                <tfoot class="table-light fw-bold sticky-bottom border-top">
                                                    <tr>
                                                        <td class="text-center">--</td>
                                                        <td>TỔNG CỘNG:</td>
                                                        <td class="text-center text-primary fs-5"><?php echo $tProps; ?></td>
                                                        <td class="text-center text-dark fs-5"><?php echo $tPropP; ?></td>
                                                    </tr>
                                                </tfoot>
                                            <?php endif; ?>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Inline Premium Styles -->
<style>
    .bg-gradient-blue {
        background: linear-gradient(135deg, #3a57e8 0%, #08b1c4 100%);
    }
    .bg-gradient-teal {
        background: linear-gradient(135deg, #1aa1b6 0%, #00cae3 100%);
    }
    .bg-gradient-orange {
        background: linear-gradient(135deg, #f16a1b 0%, #feb272 100%);
    }
    .bg-gradient-purple {
        background: linear-gradient(135deg, #8533ff 0%, #c299ff 100%);
    }
    
    .bg-white-20 {
        background-color: rgba(255, 255, 255, 0.2);
    }
    
    .tracking-wider {
        letter-spacing: 0.05em;
    }
    
    .kpi-card {
        border-radius: 12px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
    }
    
    /* Navigation Tab Styles */
    #reportsTab .nav-link {
        color: #6c757d;
        background-color: #f8f9fa;
        font-weight: 500;
        transition: all 0.2s ease-in-out;
        border: 1px solid #e9ecef;
    }
    #reportsTab .nav-link:hover {
        background-color: #e9ecef;
        color: #3f434a;
    }
    #reportsTab .nav-link.active {
        color: #fff;
        background: linear-gradient(135deg, #3a57e8 0%, #08b1c4 100%);
        border-color: transparent;
        box-shadow: 0 4px 12px rgba(58, 87, 232, 0.3);
    }
    
    .border-top-2 {
        border-top: 2px solid #3a57e8 !important;
    }
    
    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 5;
    }
    .sticky-bottom {
        position: sticky;
        bottom: 0;
        z-index: 5;
    }
    
    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
    }
    .btn-soft-primary {
        background-color: rgba(58, 87, 232, 0.1);
        color: #3a57e8;
        border-color: transparent;
    }
    .btn-soft-primary:hover {
        background-color: #3a57e8;
        color: #fff;
    }
</style>

<!-- Instant Interactive Client-Side Search Filters -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // TAB 1 Search: tableRegs
    const searchRegsInput = document.getElementById('searchRegs');
    if (searchRegsInput) {
        searchRegsInput.addEventListener('keyup', function() {
            filterTable('tableRegs', this.value);
        });
    }

    // TAB 2 Search: tableAttendees
    const searchAttendeesInput = document.getElementById('searchAttendees');
    if (searchAttendeesInput) {
        searchAttendeesInput.addEventListener('keyup', function() {
            filterTable('tableAttendees', this.value);
        });
    }

    // TAB 3 Search: tableTalentList
    const searchTalentListInput = document.getElementById('searchTalentList');
    if (searchTalentListInput) {
        searchTalentListInput.addEventListener('keyup', function() {
            filterTable('tableTalentList', this.value);
        });
    }

    // TAB 4 Search: tableTalentProp
    const searchTalentPropInput = document.getElementById('searchTalentProp');
    if (searchTalentPropInput) {
        searchTalentPropInput.addEventListener('keyup', function() {
            filterTable('tableTalentProp', this.value);
        });
    }

    // Common filter function
    function filterTable(tableId, query) {
        const table = document.getElementById(tableId);
        if (!table) return;
        
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        const filter = query.toLowerCase().trim();
        
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            // Skip empty placeholder row if exists
            if (row.cells.length === 1 && row.cells[0].classList.contains('text-center')) {
                continue;
            }
            
            let match = false;
            // Loop through all cells in row to see if query matches
            for (let j = 0; j < row.cells.length; j++) {
                const cellText = row.cells[j].textContent || row.cells[j].innerText;
                if (cellText.toLowerCase().indexOf(filter) > -1) {
                    match = true;
                    break;
                }
            }
            
            if (match) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    }
});
</script>
