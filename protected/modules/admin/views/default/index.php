<?php
$user = AuthHandler::getUser();
$userPropertyCode = isset($user['property_code']) ? $user['property_code'] : '';
$isHO = ($userPropertyCode === '9999' || $userPropertyCode === 9999);

$registrationsByStatus = isset($stats['registrations_by_status']) ? $stats['registrations_by_status'] : array(
    'draft' => 5,
    'submitted' => 12,
    'approved' => 28,
    'rejected' => 2,
);
$unregisteredProperties = isset($stats['unregistered_properties']) ? $stats['unregistered_properties'] : array(
    array('code' => 'HN01', 'name' => 'Khách sạn Mường Thanh Grand Hà Nội', 'regional_name' => 'Miền Bắc'),
    array('code' => 'HN02', 'name' => 'Khách sạn Mường Thanh Luxury Quảng Ninh', 'regional_name' => 'Miền Bắc'),
    array('code' => 'DN01', 'name' => 'Khách sạn Mường Thanh Luxury Đà Nẵng', 'regional_name' => 'Miền Trung'),
    array('code' => 'SG01', 'name' => 'Khách sạn Mường Thanh Luxury Sài Gòn', 'regional_name' => 'Miền Nam'),
    array('code' => 'HP01', 'name' => 'Khách sạn Mường Thanh Grand Hải Phòng', 'regional_name' => 'Miền Bắc'),
);

// Default values nếu API chưa trả về
$totalProperties = isset($stats['total_properties']) ? $stats['total_properties'] : 52;
$registeredProperties = isset($stats['registered_properties']) ? $stats['registered_properties'] : 47;
$totalAttendees = isset($stats['total_attendees']) ? $stats['total_attendees'] : 586;
$sportTeams = isset($stats['sport_teams']) ? $stats['sport_teams'] : 24;
$competitionParticipants = isset($stats['competition_participants']) ? $stats['competition_participants'] : 156;
$beautyContestants = isset($stats['beauty_contestants']) ? $stats['beauty_contestants'] : 32;
$talentEntries = isset($stats['talent_entries']) ? $stats['talent_entries'] : 18;

$sportTeamsBySport = isset($stats['sport_teams_by_sport']) ? $stats['sport_teams_by_sport'] : array(
    array('name' => 'Bóng đá nam', 'count' => 12),
    array('name' => 'Bóng đá nữ', 'count' => 8),
    array('name' => 'Cầu lông đơn nam', 'count' => 24),
    array('name' => 'Cầu lông đơn nữ', 'count' => 16),
    array('name' => 'Cầu lông đôi nam', 'count' => 12),
    array('name' => 'Cầu lông đôi nữ', 'count' => 8),
    array('name' => 'Bóng bàn đơn nam', 'count' => 20),
    array('name' => 'Bóng bàn đơn nữ', 'count' => 14),
);
?>

<?php if ($isHO): ?>

    <div class="row">
        <div class="col-12">
            <h4 class="mb-3">Tổng quan đăng ký</h4>
        </div>
    </div>

    <!-- Row 1: Summary Cards -->
    <div class="row">
        <div class="col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Tổng đơn vị</span>
                            <h3 class="mb-0"><?php echo isset($stats['total_properties']) ? $stats['total_properties'] : 0; ?></h3>
                        </div>
                        <div class="bg-soft-primary rounded-3 p-3">
                            <i class="fa fa-building fa-lg text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Đã gửi đăng ký</span>
                            <h3 class="mb-0"><?php echo isset($stats['registered_properties']) ? $stats['registered_properties'] : 0; ?></h3>
                        </div>
                        <div class="bg-soft-success rounded-3 p-3">
                            <i class="fa fa-check-circle fa-lg text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Chưa gửi đăng ký</span>
                            <h3 class="mb-0"><?php echo (isset($stats['total_properties']) ? $stats['total_properties'] : 0) - (isset($stats['registered_properties']) ? $stats['registered_properties'] : 0); ?></h3>
                        </div>
                        <div class="bg-soft-warning rounded-3 p-3">
                            <i class="fa fa-exclamation-triangle fa-lg text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Tổng người tham dự</span>
                            <h3 class="mb-0"><?php echo isset($stats['total_attendees']) ? $stats['total_attendees'] : 0; ?></h3>
                        </div>
                        <div class="bg-soft-info rounded-3 p-3">
                            <i class="fa fa-users fa-lg text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Row 3: Registration Status & Unregistered Properties -->
    <div class="row">
        <!-- Registration by Status -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Trạng thái đăng ký</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fa fa-circle text-secondary me-2"></i> Nháp</span>
                            <span class="badge bg-secondary rounded-pill"><?php echo isset($registrationsByStatus['draft']) ? $registrationsByStatus['draft'] : 0; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fa fa-circle text-info me-2"></i> Đã nộp</span>
                            <span class="badge bg-info rounded-pill"><?php echo isset($registrationsByStatus['submitted']) ? $registrationsByStatus['submitted'] : 0; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fa fa-circle text-success me-2"></i> Đã duyệt</span>
                            <span class="badge bg-success rounded-pill"><?php echo isset($registrationsByStatus['approved']) ? $registrationsByStatus['approved'] : 0; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fa fa-circle text-danger me-2"></i> Từ chối</span>
                            <span class="badge bg-danger rounded-pill"><?php echo isset($registrationsByStatus['rejected']) ? $registrationsByStatus['rejected'] : 0; ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    </div>

    <!-- Row: 3 cột trạng thái đăng ký đơn vị -->
    <?php
    $notStarted = isset($stats['properties_not_started']) ? $stats['properties_not_started'] : array();
    $draft = isset($stats['properties_draft']) ? $stats['properties_draft'] : array();
    $submitted = isset($stats['properties_submitted']) ? $stats['properties_submitted'] : array();
    ?>
    <div class="row">
        <!-- Chưa khởi tạo đăng ký -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0"><i class="fa fa-times-circle text-danger me-2"></i>Chưa khởi tạo</h6>
                    <span class="badge bg-danger"><?php echo count($notStarted); ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($notStarted)): ?>
                        <div class="p-3 text-center text-muted small">Không có</div>
                    <?php else: ?>
                        <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Mã</th>
                                        <th>Tên đơn vị</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($notStarted as $p): ?>
                                        <tr>
                                            <td><code><?php echo CHtml::encode(isset($p['code']) ? $p['code'] : ''); ?></code></td>
                                            <td class="small"><?php echo CHtml::encode(isset($p['name']) ? $p['name'] : ''); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Đã tạo nhưng chưa gửi (Nháp) -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0"><i class="fa fa-edit text-secondary me-2"></i>Nháp (chưa gửi)</h6>
                    <span class="badge bg-secondary"><?php echo count($draft); ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($draft)): ?>
                        <div class="p-3 text-center text-muted small">Không có</div>
                    <?php else: ?>
                        <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Mã</th>
                                        <th>Tên đơn vị</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($draft as $p): ?>
                                        <tr>
                                            <td><code><?php echo CHtml::encode(isset($p['code']) ? $p['code'] : ''); ?></code></td>
                                            <td class="small"><?php echo CHtml::encode(isset($p['name']) ? $p['name'] : ''); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Đã gửi đăng ký -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0"><i class="fa fa-paper-plane text-info me-2"></i>Đã gửi đăng ký</h6>
                    <span class="badge bg-info"><?php echo count($submitted); ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($submitted)): ?>
                        <div class="p-3 text-center text-muted small">Không có</div>
                    <?php else: ?>
                        <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Mã</th>
                                        <th>Tên đơn vị</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($submitted as $p): ?>
                                        <tr>
                                            <td><code><?php echo CHtml::encode(isset($p['code']) ? $p['code'] : ''); ?></code></td>
                                            <td class="small"><?php echo CHtml::encode(isset($p['name']) ? $p['name'] : ''); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 4: Sport Teams by Sport -->
    <?php if (!empty($stats['sport_teams_by_sport'])): ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Đội thi đấu theo môn</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($stats['sport_teams_by_sport'] as $sport): ?>
                                <div class="col-md-4 col-lg-3 mb-3">
                                    <div class="border rounded p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span><?php echo CHtml::encode($sport['name']); ?></span>
                                            <span class="badge bg-primary"><?php echo $sport['count']; ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm welcome-card text-center py-5 px-4" style="background: linear-gradient(135deg, #3a57e8 0%, #08b1c4 100%); border-radius: 16px; color: white;">
                <div class="card-body py-5">
                    <div class="mb-4">
                        <div class="welcome-icon-box mx-auto d-flex align-items-center justify-content-center" style="width: 90px; height: 90px; background-color: rgba(255, 255, 255, 0.2); border-radius: 50%; backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.2);">
                            <i class="fa fa-handshake-o fa-3x text-white"></i>
                        </div>
                    </div>
                    <h1 class="text-white fw-bold mb-3" style="font-size: 2.25rem;">Chào mừng đến với hệ thống đăng ký sự kiện Mường Thanh</h1>
                    <p class="lead text-white-50 mb-0" style="max-width: 600px; margin: 0 auto;">Hệ thống quản lý và đăng ký các hoạt động, sự kiện, chương trình nghiệp vụ, thể thao, văn nghệ và sắc đẹp.</p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<style>
    .bg-soft-primary {
        background-color: rgba(13, 110, 253, 0.1);
    }

    .bg-soft-success {
        background-color: rgba(25, 135, 84, 0.1);
    }

    .bg-soft-warning {
        background-color: rgba(255, 193, 7, 0.1);
    }

    .bg-soft-danger {
        background-color: rgba(220, 53, 69, 0.1);
    }

    .bg-soft-info {
        background-color: rgba(13, 202, 240, 0.1);
    }

    .bg-soft-secondary {
        background-color: rgba(108, 117, 125, 0.1);
    }

    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .row {
        margin-bottom: 1.5rem;
    }

    .card {
        margin-bottom: 0;
    }
</style>