<?php

/**
 * Layout Cổng Cá Nhân Đại Biểu (Attendee Portal) - Đại Hội Mường Thanh 2026.
 * Chuẩn giao diện cao cấp, chuyên nghiệp với Header, Navigation, Footer chuẩn mực và đồng bộ.
 */
$isLoggedIn = method_exists($this, 'isLoggedIn') ? $this->isLoggedIn() : false;
$fullName = method_exists($this, 'currentFullName') ? $this->currentFullName() : '';
$profile = method_exists($this, 'currentProfile') ? $this->currentProfile() : null;
$unitName = !empty($profile['unit_name']) ? $profile['unit_name'] : '';

$portalIndexUrl = $this->createUrl('/frontend/portal/index');
$portalScheduleUrl = $this->createUrl('/frontend/portal/schedule');
$runIndexUrl = $this->createUrl('/frontend/run/index');
$mainWebsiteUrl = Yii::app()->request->baseUrl . '/';
$logoutUrl = $this->createUrl('/frontend/portal/logout');

$controllerId = isset(Yii::app()->controller) ? Yii::app()->controller->id : '';
$actionId = (isset(Yii::app()->controller) && isset(Yii::app()->controller->action)) ? Yii::app()->controller->action->id : '';
$isIndexActive = ($controllerId === 'portal' && $actionId === 'index');
$isScheduleActive = ($controllerId === 'portal' && $actionId === 'schedule');
$isRunActive = ($controllerId === 'run');
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo CHtml::encode($this->pageTitle ? $this->pageTitle . ' - Đại Hội Mường Thanh 2026' : 'Cổng Cá Nhân Đại Biểu - Đại Hội Mường Thanh 2026'); ?></title>
    <link rel="shortcut icon" href="<?php echo Yii::app()->theme->baseUrl; ?>/assets/images/favicon.ico">

    <!-- Google Fonts: Inter (Full Vietnamese support) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/public/vendor/fonts/inter/inter.css">

    <!-- Bootstrap 5, FontAwesome & Bootstrap Icons -->
    <link href="<?php echo Yii::app()->theme->baseUrl; ?>/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo Yii::app()->theme->baseUrl; ?>/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo Yii::app()->theme->baseUrl; ?>/assets/css/font-awesome/font-awesome.min.css" rel="stylesheet">

    <!-- Portal Base Stylesheet -->
    <link href="<?php echo Yii::app()->theme->baseUrl; ?>/assets/css/pages/portal-index.css?v=20261014_v2" rel="stylesheet">
</head>

<body class="portal-body">
    <!-- Portal Main Wrapper -->
    <div class="portal-wrapper">
        <!-- Topbar Header -->
        <header class="portal-topbar">
            <div class="container">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <!-- Brand & Logo -->
                    <a href="<?php echo $portalIndexUrl; ?>" class="portal-brand-link">
                        <img src="<?php echo Yii::app()->theme->baseUrl; ?>/logo_daihoi.png" alt="Logo Đại hội Mường Thanh" class="portal-brand-logo">
                        <div class="d-none d-sm-block">
                            <h1 class="portal-brand-title">ĐẠI HỘI MƯỜNG THANH 2026</h1>
                            <span class="portal-brand-sub">CỔNG THÔNG TIN ĐẠI BIỂU • NINH BÌNH</span>
                        </div>
                    </a>

                    <!-- Desktop Navigation Links -->
                    <nav class="d-none d-lg-flex align-items-center portal-nav-menu">
                        <a href="<?php echo $portalIndexUrl; ?>" class="portal-nav-link <?php echo $isIndexActive ? 'active' : ''; ?>">
                            <i class="bi bi-grid-fill"></i>
                            <span>Tổng quan</span>
                        </a>
                        <a href="<?php echo $portalScheduleUrl; ?>" class="portal-nav-link <?php echo $isScheduleActive ? 'active' : ''; ?>">
                            <i class="bi bi-calendar-week"></i>
                            <span>Lịch trình</span>
                        </a>
                        <a href="<?php echo $runIndexUrl; ?>" class="portal-nav-link <?php echo $isRunActive ? 'active' : ''; ?>">
                            <i class="bi bi-lightning-charge-fill"></i>
                            <span>Đăng ký hoạt động</span>
                        </a>
                        <a href="<?php echo $mainWebsiteUrl; ?>" class="portal-nav-link portal-nav-external" target="_blank" title="Mở Website Đại hội">
                            <i class="bi bi-box-arrow-up-right"></i>
                            <span>Website chính</span>
                        </a>
                    </nav>

                    <!-- User Info & Logout Button -->
                    <div class="d-flex align-items-center gap-2">
                        <?php if ($fullName): ?>
                            <div class="portal-user-chip d-none d-md-flex">
                                <div class="portal-user-avatar">
                                    <?php
                                    $initial = mb_substr(trim($fullName), 0, 1, 'UTF-8');
                                    echo CHtml::encode($initial ?: 'Đ');
                                    ?>
                                </div>
                                <div class="portal-user-meta text-start">
                                    <span class="portal-user-name" title="<?php echo CHtml::encode($fullName); ?>">
                                        <?php echo CHtml::encode($fullName); ?>
                                    </span>
                                    <span class="portal-user-role">
                                        <i class="bi bi-shield-check text-primary"></i> Đại biểu VCK
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <a href="<?php echo $logoutUrl; ?>" class="portal-btn-logout" id="btn-topbar-logout" title="Đăng xuất khỏi Cổng">
                            <i class="bi bi-box-arrow-right"></i>
                            <span class="d-none d-sm-inline">Thoát</span>
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Mobile Sub-Navigation Bar for Quick Access -->
        <div class="d-lg-none portal-mobile-nav">
            <div class="container">
                <div class="d-flex align-items-center justify-content-around gap-1 text-center py-2">
                    <a href="<?php echo $portalIndexUrl; ?>" class="portal-mobile-tab <?php echo $isIndexActive ? 'active' : ''; ?>">
                        <i class="bi bi-grid-fill"></i>
                        <span>Tổng quan</span>
                    </a>
                    <a href="<?php echo $portalScheduleUrl; ?>" class="portal-mobile-tab <?php echo $isScheduleActive ? 'active' : ''; ?>">
                        <i class="bi bi-calendar-week"></i>
                        <span>Lịch trình</span>
                    </a>
                    <a href="<?php echo $runIndexUrl; ?>" class="portal-mobile-tab <?php echo $isRunActive ? 'active' : ''; ?>">
                        <i class="bi bi-lightning-charge-fill"></i>
                        <span>Đăng ký</span>
                    </a>
                    <a href="<?php echo $mainWebsiteUrl; ?>" class="portal-mobile-tab">
                        <i class="bi bi-house-door"></i>
                        <span>Website</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <main class="portal-content">
            <?php echo $content; ?>
        </main>

        <!-- Portal Comprehensive Footer -->
        <footer class="portal-main-footer">
            <div class="container">
                <div class="row g-4 justify-content-between mb-2">
                    <!-- Col 1: Brand & Identity -->
                    <div class="col-12 col-md-6 col-lg-5">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <img src="<?php echo Yii::app()->theme->baseUrl; ?>/logo_daihoi.png" alt="Logo Đại hội Mường Thanh" style="height: 48px; width: auto; object-fit: contain;">
                            <div>
                                <h5 class="text-white fw-bold mb-0" style="letter-spacing: -0.01em;">ĐẠI HỘI MƯỜNG THANH 2026</h5>
                                <div class="text-primary-light small fw-semibold">HÀNH TRÌNH DI SẢN - RỰC CHÁY ĐAM MÊ</div>
                            </div>
                        </div>
                        <p class="portal-footer-desc mb-3">
                            Ngày hội hội tụ tinh hoa tay nghề nghiệp vụ khách sạn và tinh thần thể thao bứt phá lớn nhất toàn Tập đoàn Khách sạn Mường Thanh tại Ninh Bình.
                        </p>
                        <div class="portal-footer-tags d-flex flex-wrap gap-2">
                            <span class="badge bg-dark-subtle text-light border border-secondary border-opacity-25 px-2 py-1 small">
                                <i class="bi bi-geo-alt-fill text-warning me-1"></i> Ninh Bình 2026
                            </span>
                            <span class="badge bg-dark-subtle text-light border border-secondary border-opacity-25 px-2 py-1 small">
                                <i class="bi bi-shield-check text-success me-1"></i> Cổng Xác Thực 2 Lớp
                            </span>
                            <span class="badge bg-dark-subtle text-light border border-secondary border-opacity-25 px-2 py-1 small">
                                <i class="bi bi-lightning-charge-fill text-info me-1"></i> Tự Phục Vụ 24/7
                            </span>
                        </div>
                    </div>

                    <!-- Col 2: Navigation Links -->
                    <div class="col-6 col-md-3 col-lg-3">
                        <h6 class="portal-footer-heading">Liên kết tiện ích</h6>
                        <ul class="portal-footer-links">
                            <li><a href="<?php echo $portalIndexUrl; ?>"><i class="bi bi-chevron-right small"></i> Tổng quan cá nhân</a></li>
                            <li><a href="<?php echo $portalScheduleUrl; ?>"><i class="bi bi-chevron-right small"></i> Lịch trình chi tiết</a></li>
                            <li><a href="<?php echo $runIndexUrl; ?>"><i class="bi bi-chevron-right small"></i> Đăng ký Fun Run & Tour</a></li>
                            <li><a href="<?php echo $mainWebsiteUrl; ?>"><i class="bi bi-chevron-right small"></i> Website Đại hội</a></li>
                        </ul>
                    </div>

                    <!-- Col 3: Support & Contact -->
                    <div class="col-6 col-md-3 col-lg-4">
                        <h6 class="portal-footer-heading">Hỗ trợ đại biểu</h6>
                        <ul class="portal-footer-contacts">
                            <li class="d-flex align-items-start gap-2 mb-2">
                                <i class="bi bi-buildings text-primary mt-1"></i>
                                <span>Tập đoàn Khách sạn Mường Thanh</span>
                            </li>
                            <li class="d-flex align-items-start gap-2 mb-2">
                                <i class="bi bi-geo-alt text-warning mt-1"></i>
                                <span>Mường Thanh Luxury Ninh Bình</span>
                            </li>
                            <li class="d-flex align-items-start gap-2 mb-2">
                                <i class="bi bi-headset text-success mt-1"></i>
                                <span>Kỹ thuật CNTT: <strong>itsupport@muongthanh.vn</strong></span>
                            </li>
                            <li class="d-flex align-items-start gap-2">
                                <i class="bi bi-shield-lock text-info mt-1"></i>
                                <span>Bảo mật hệ thống • Trực tuyến 24/7</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Footer Bottom Bar -->
                <div class="portal-footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 pt-3 border-top border-secondary border-opacity-25 text-muted small">
                    <div>
                        &copy; <?php echo date('Y'); ?> <strong>Đại hội Mường Thanh</strong>. Tất cả quyền được bảo lưu.
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <span class="d-inline-flex align-items-center gap-1 text-success">
                            <span class="pulse-dot-green"></span>
                            <span>Máy chủ hoạt động bình thường</span>
                        </span>
                        <span>•</span>
                        <span>Cổng cá nhân v2.6</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Vendor Scripts -->
    <script src="<?php echo Yii::app()->theme->baseUrl; ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo Yii::app()->theme->baseUrl; ?>/assets/js/plugins/toast.js"></script>
    <script src="<?php echo Yii::app()->theme->baseUrl; ?>/assets/vendor/sweetalert2/sweetalert2.all.min.js"></script>

    <!-- Logout handler with SweetAlert2 confirmation -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var logoutBtns = document.querySelectorAll('.portal-btn-logout, #btn-portal-logout, #btn-topbar-logout');
            logoutBtns.forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var targetUrl = this.getAttribute('href');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Đăng xuất?',
                            text: 'Bạn có chắc chắn muốn đăng xuất khỏi Cổng Cá Nhân Đại Biểu?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#dc2626',
                            cancelButtonColor: '#64748b',
                            confirmButtonText: '<i class="bi bi-box-arrow-right me-1"></i> Đăng xuất',
                            cancelButtonText: 'Ở lại',
                            reverseButtons: true,
                            customClass: {
                                popup: 'rounded-4'
                            }
                        }).then(function(result) {
                            if (result.isConfirmed) {
                                window.location.href = targetUrl;
                            }
                        });
                    } else {
                        if (confirm('Bạn có chắc chắn muốn đăng xuất?')) {
                            window.location.href = targetUrl;
                        }
                    }
                });
            });
        });
    </script>

    <?php
    $flashSuccess = Yii::app()->user->getFlash('success');
    $flashError = Yii::app()->user->getFlash('error');
    $flashWarning = Yii::app()->user->getFlash('warning');
    $flashInfo = Yii::app()->user->getFlash('info');
    if ($flashSuccess || $flashError || $flashWarning || $flashInfo):
    ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                <?php if ($flashSuccess): ?>
                    Toast.success('<?php echo addslashes($flashSuccess); ?>');
                <?php endif; ?>
                <?php if ($flashError): ?>
                    Toast.error('<?php echo addslashes($flashError); ?>');
                <?php endif; ?>
                <?php if ($flashWarning): ?>
                    Toast.warning('<?php echo addslashes($flashWarning); ?>');
                <?php endif; ?>
                <?php if ($flashInfo): ?>
                    Toast.info('<?php echo addslashes($flashInfo); ?>');
                <?php endif; ?>
            });
        </script>
    <?php endif; ?>
</body>

</html>