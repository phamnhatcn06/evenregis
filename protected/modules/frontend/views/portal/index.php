<?php
/**
 * Trang chủ Cổng cá nhân đại biểu - Đại hội Mường Thanh 2026 Ninh Bình.
 * Giao diện hiện đại, sang trọng, đẳng cấp doanh nghiệp với đầy đủ Header, Hero banner, Lưới tiện ích & Chân trang.
 */
$this->pageTitle = 'Cổng Cá Nhân Đại Biểu';

Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/portal-profile.js',
    CClientScript::POS_END
);

$profile = isset($profile) ? $profile : (method_exists($this, 'loadProfile') ? $this->loadProfile() : null);
$unitName = !empty($profile['unit_name']) ? $profile['unit_name'] : 'Tập đoàn Khách sạn Mường Thanh';
$position = !empty($profile['position']) ? $profile['position'] : '';

$scheduleUrl = $this->createUrl('/frontend/portal/schedule');
$runUrl = $this->createUrl('/frontend/run/index');
$sportScheduleUrl = Yii::app()->request->baseUrl . '/lich-thi-the-thao';

// 6 Danh mục tính năng chuẩn mực
$features = array(
    array(
        'category'    => 'LỊCH TRÌNH & SỰ KIỆN',
        'title'       => 'Lịch trình của tôi',
        'desc'        => 'Theo dõi toàn bộ lịch trình liên quan đến anh/chị: trận thi đấu, vòng thi nghiệp vụ, nghi thức, bữa ăn và tiệc gala.',
        'icon'        => 'bi-calendar-week-fill',
        'gradient'    => 'icon-gradient-blue',
        'status'      => 'Sẵn sàng',
        'status_type' => 'active',
        'action'      => 'Xem lịch trình ngay',
        'action_type' => 'primary',
        'url'         => $scheduleUrl,
        'chips'       => array(
            array('icon' => 'bi-clock-fill', 'label' => 'Timeline chi tiết'),
            array('icon' => 'bi-geo-alt-fill', 'label' => 'Địa điểm & ca'),
            array('icon' => 'bi-cup-hot-fill', 'label' => 'Tiệc & Gala'),
        ),
    ),
    array(
        'category'    => 'HOẠT ĐỘNG TỰ PHỤC VỤ',
        'title'       => 'Đăng ký hoạt động',
        'desc'        => 'Đăng ký cự ly chạy Fun Run (5km / 10km / 15km), đăng ký size áo thi đấu và các đợt đi tour tham quan Quần thể Di sản Tràng An.',
        'icon'        => 'bi-flag-fill',
        'gradient'    => 'icon-gradient-green',
        'status'      => 'Đang mở đăng ký',
        'status_type' => 'active_pulse',
        'action'      => 'Đăng ký ngay',
        'action_type' => 'green',
        'url'         => $runUrl,
        'chips'       => array(
            array('icon' => 'bi-person-running', 'label' => 'Fun Run 2026'),
            array('icon' => 'bi-compass-fill', 'label' => 'Tour Tràng An'),
            array('icon' => 'bi-check2-circle', 'label' => 'Chọn size áo'),
        ),
    ),
    array(
        'category'    => 'TRANH TÀI THỂ THAO',
        'title'       => 'Lịch thi đấu của tôi',
        'desc'        => 'Tra cứu bảng đấu, đối thủ, sân bãi thi đấu và theo dõi kết quả, bảng tổng sắp huy chương các môn thể thao bạn tranh tài.',
        'icon'        => 'bi-trophy-fill',
        'gradient'    => 'icon-gradient-amber',
        'status'      => 'Sắp diễn ra',
        'status_type' => 'warning',
        'action'      => 'Sắp mở tra cứu',
        'action_type' => 'locked',
        'url'         => null,
        'chips'       => array(
            array('icon' => 'bi-dribbble', 'label' => 'Bóng đá nam'),
            array('icon' => 'bi-circle-fill', 'label' => 'Tennis & Cầu lông'),
            array('icon' => 'bi-water', 'label' => 'Bơi lội'),
        ),
    ),
    array(
        'category'    => 'NGHIỆP VỤ KHÁCH SẠN',
        'title'       => 'Lịch thi nghiệp vụ',
        'desc'        => 'Tra cứu số báo danh (SBD), phòng thi, ca thi và danh sách ban giám khảo các vòng thi tay nghề nghiệp vụ Vòng Chung Kết toàn quốc.',
        'icon'        => 'bi-award-fill',
        'gradient'    => 'icon-gradient-purple',
        'status'      => 'VCK Toàn quốc',
        'status_type' => 'purple',
        'action'      => 'Sắp mở tra cứu',
        'action_type' => 'locked',
        'url'         => null,
        'chips'       => array(
            array('icon' => 'bi-person-badge-fill', 'label' => 'Tra cứu SBD'),
            array('icon' => 'bi-door-open-fill', 'label' => 'Phòng & Ca thi'),
            array('icon' => 'bi-stars', 'label' => '4 Bộ môn thi'),
        ),
    ),
    array(
        'category'    => 'TRUYỀN THÔNG & MEDIA',
        'title'       => 'Kho hình ảnh kỷ niệm',
        'desc'        => 'Tìm kiếm và tải về bộ ảnh chất lượng cao ghi lại những khoảnh khắc đẹp, thăng hoa và tự hào của bạn trong suốt kỳ Đại hội.',
        'icon'        => 'bi-camera-fill',
        'gradient'    => 'icon-gradient-rose',
        'status'      => 'Cập nhật liên tục',
        'status_type' => 'rose',
        'action'      => 'Sắp mở kho ảnh',
        'action_type' => 'locked',
        'url'         => null,
        'chips'       => array(
            array('icon' => 'bi-image-fill', 'label' => 'Ảnh độ phân giải cao'),
            array('icon' => 'bi-magic', 'label' => 'Tìm kiếm AI'),
            array('icon' => 'bi-download', 'label' => 'Tải ảnh gốc HD'),
        ),
    ),
    array(
        'category'    => 'TIỆN ÍCH & CHỈ DẪN',
        'title'       => 'Sơ đồ & Cẩm nang đại biểu',
        'desc'        => 'Sơ đồ toàn khu khách sạn Mường Thanh Ninh Bình, vị trí các cụm sân bãi thi đấu, lịch trình xe đưa đón và quy chế Đại hội.',
        'icon'        => 'bi-geo-alt-fill',
        'gradient'    => 'icon-gradient-cyan',
        'status'      => 'Tiện ích chỉ dẫn',
        'status_type' => 'cyan',
        'action'      => 'Xem cẩm nang',
        'action_type' => 'locked',
        'url'         => null,
        'chips'       => array(
            array('icon' => 'bi-map-fill', 'label' => 'Sơ đồ địa điểm'),
            array('icon' => 'bi-bus-front-fill', 'label' => 'Shuttle bus'),
            array('icon' => 'bi-journal-bookmark-fill', 'label' => 'Quy chế VCK'),
        ),
    ),
);
?>

<div class="container py-4">
    <!-- Hero Welcome Banner -->
    <div class="portal-hero-banner">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <div class="portal-hero-pill">
                    <span class="pulse-dot-green"></span>
                    <span>ĐẠI HỘI MƯỜNG THANH 2026 • VÒNG CHUNG KẾT</span>
                </div>
                <h2 class="portal-hero-title">
                    Xin chào, <span class="hero-name-highlight"><?php echo CHtml::encode($fullName); ?></span> 👋
                </h2>
                <p class="portal-hero-subtitle">
                    Chào mừng Quý đại biểu đến với Cổng thông tin & Tiện ích tự phục vụ Đại hội Mường Thanh 2026 tại Ninh Bình. Quý đại biểu có thể theo dõi lịch trình, đăng ký hoạt động và cập nhật mọi thông tin cá nhân tại đây.
                </p>
                <div class="portal-hero-badges">
                    <div class="hero-badge-item">
                        <i class="bi bi-shield-check text-success"></i>
                        <span>Tài khoản đã xác thực</span>
                    </div>
                    <?php if (!empty($unitName)): ?>
                        <div class="hero-badge-item">
                            <i class="bi bi-building text-info"></i>
                            <span>Đơn vị: <?php echo CHtml::encode($unitName); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($position)): ?>
                        <div class="hero-badge-item">
                            <i class="bi bi-person-badge text-warning"></i>
                            <span>Chức vụ: <?php echo CHtml::encode($position); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="hero-badge-item">
                        <i class="bi bi-geo-alt-fill text-danger"></i>
                        <span>Địa điểm: Ninh Bình 2026</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="d-flex flex-column flex-sm-row flex-lg-column gap-2 justify-content-lg-end">
                    <a href="<?php echo $scheduleUrl; ?>" class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold shadow-sm text-nowrap">
                        <i class="bi bi-calendar2-week me-1"></i> Lịch trình của tôi
                    </a>
                    <a href="<?php echo $runUrl; ?>" class="btn btn-emerald rounded-pill px-4 py-2.5 fw-bold shadow-sm text-nowrap">
                        <i class="bi bi-flag-fill me-1"></i> Đăng ký hoạt động
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Heading -->
    <div class="portal-section-header">
        <div>
            <h3 class="portal-section-title">
                <i class="bi bi-grid-fill text-primary"></i>
                Danh mục tiện ích
            </h3>
            <p class="portal-section-subtitle">Chạm hoặc chọn tiện ích bạn muốn truy cập</p>
        </div>
        <div class="d-none d-md-block text-muted small">
            <span class="badge bg-white border text-secondary px-3 py-2 rounded-pill shadow-sm">
                <i class="bi bi-check2-all text-success me-1"></i> 6 Tiện ích tích hợp
            </span>
        </div>
    </div>

    <!-- Features Grid -->
    <div class="row g-3 g-md-4 mb-4">
        <?php foreach ($features as $f): ?>
            <?php $available = !empty($f['url']); ?>
            <div class="col-12 col-md-6 col-lg-4">
                <?php if ($available): ?>
                    <a href="<?php echo $f['url']; ?>" class="portal-card-link">
                <?php else: ?>
                    <div class="portal-card-link">
                <?php endif; ?>

                    <div class="portal-feature-card <?php echo $available ? 'card-available' : 'card-disabled'; ?>">
                        <!-- Card Top: Category & Status -->
                        <div class="portal-card-top">
                            <span class="portal-badge-category"><?php echo CHtml::encode($f['category']); ?></span>
                            <?php if ($f['status_type'] === 'active_pulse'): ?>
                                <span class="status-pill pill-active">
                                    <span class="pulse-dot-green"></span>
                                    <?php echo CHtml::encode($f['status']); ?>
                                </span>
                            <?php elseif ($f['status_type'] === 'active'): ?>
                                <span class="status-pill pill-active">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <?php echo CHtml::encode($f['status']); ?>
                                </span>
                            <?php elseif ($f['status_type'] === 'warning'): ?>
                                <span class="status-pill pill-warning">
                                    <i class="bi bi-clock-history"></i>
                                    <?php echo CHtml::encode($f['status']); ?>
                                </span>
                            <?php elseif ($f['status_type'] === 'purple'): ?>
                                <span class="status-pill pill-purple">
                                    <i class="bi bi-mortarboard"></i>
                                    <?php echo CHtml::encode($f['status']); ?>
                                </span>
                            <?php elseif ($f['status_type'] === 'rose'): ?>
                                <span class="status-pill pill-rose">
                                    <i class="bi bi-stars"></i>
                                    <?php echo CHtml::encode($f['status']); ?>
                                </span>
                            <?php elseif ($f['status_type'] === 'cyan'): ?>
                                <span class="status-pill pill-cyan">
                                    <i class="bi bi-compass"></i>
                                    <?php echo CHtml::encode($f['status']); ?>
                                </span>
                            <?php else: ?>
                                <span class="status-pill pill-locked">
                                    <i class="bi bi-lock-fill"></i>
                                    <?php echo CHtml::encode($f['status']); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Card Icon -->
                        <div class="portal-card-icon <?php echo $f['gradient']; ?>">
                            <i class="bi <?php echo $f['icon']; ?>"></i>
                        </div>

                        <!-- Card Body -->
                        <h4 class="portal-card-title"><?php echo CHtml::encode($f['title']); ?></h4>
                        <p class="portal-card-desc"><?php echo CHtml::encode($f['desc']); ?></p>

                        <!-- Feature Mini Chips -->
                        <div class="portal-card-chips">
                            <?php foreach ($f['chips'] as $chip): ?>
                                <span class="mini-chip">
                                    <i class="bi <?php echo $chip['icon']; ?>"></i>
                                    <span><?php echo CHtml::encode($chip['label']); ?></span>
                                </span>
                            <?php endforeach; ?>
                        </div>

                        <!-- Card Bottom Action -->
                        <div class="portal-card-footer">
                            <?php if ($available): ?>
                                <?php if ($f['action_type'] === 'green'): ?>
                                    <div class="btn-card-action btn-card-green">
                                        <span><?php echo CHtml::encode($f['action']); ?></span>
                                        <i class="bi bi-arrow-right"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="btn-card-action btn-card-primary">
                                        <span><?php echo CHtml::encode($f['action']); ?></span>
                                        <i class="bi bi-arrow-right"></i>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="btn-card-action btn-card-locked">
                                    <i class="bi bi-hourglass-split"></i>
                                    <span><?php echo CHtml::encode($f['action']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php echo $available ? '</a>' : '</div>'; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Quick Info & Security Assistance Strip -->
    <div class="portal-info-bar">
        <div class="row align-items-center g-3">
            <div class="col-lg-8">
                <div class="portal-info-item">
                    <i class="bi bi-info-circle-fill text-primary"></i>
                    <span>
                        <strong>Lưu ý quan trọng:</strong> Cổng đăng ký hoạt động tự phục vụ có giới hạn số lượng theo từng cự ly chạy Fun Run và đợt tham quan Quần thể Di sản Tràng An. Quý đại biểu vui lòng đăng ký sớm để chọn khung giờ và cự ly phù hợp.
                    </span>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="portal-info-item justify-content-lg-end text-muted small">
                    <i class="bi bi-shield-lock-fill text-success"></i>
                    <span>Bảo mật 2 lớp • Mã định danh & PIN • Hỗ trợ: 1900 1833</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Popup xác nhận hồ sơ (chỉ có khi chưa xác nhận trong phiên đăng nhập này).
if (!empty($profileModal)) {
    $this->renderPartial('_modal_profile', $profileModal);
}
?>