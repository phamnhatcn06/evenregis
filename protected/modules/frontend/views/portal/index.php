<?php
/**
 * Trang chủ Cổng Cá Nhân Đại Biểu - Đại Hội Mường Thanh 2026 Ninh Bình.
 * Giao diện hiện đại, sang trọng, đẳng cấp sự kiện với trải nghiệm tương tác mượt mà.
 */
$this->pageTitle = 'Cổng Cá Nhân Đại Biểu';

$logoutUrl = $this->createUrl('/frontend/portal/logout');

// Danh mục tính năng
$features = array(
    array(
        'category' => 'Thể thao & Tham quan',
        'icon'     => 'bi-calendar2-check-fill',
        'gradient' => 'icon-gradient-blue',
        'title'    => 'Đăng ký hoạt động',
        'desc'     => 'Đăng ký cự ly chạy Fun Run (3km & 5km) và đợt đi tour tham quan Quần thể Di sản Tràng An (số lượng có hạn).',
        'url'      => $this->createUrl('/frontend/run/index'),
        'chips'    => array(
            array('icon' => 'bi-person-walking text-primary', 'label' => 'Fun Run 3km/5km'),
            array('icon' => 'bi-compass text-info', 'label' => 'Tour Tràng An'),
            array('icon' => 'bi-ticket-perforated text-success', 'label' => 'Nhận số BIB'),
        ),
    ),
    array(
        'category' => 'Tranh tài thể thao',
        'icon'     => 'bi-trophy-fill',
        'gradient' => 'icon-gradient-green',
        'title'    => 'Lịch thi đấu của tôi',
        'desc'     => 'Xem lịch thi đấu, bảng đấu, địa điểm sân bãi và theo dõi kết quả các môn thể thao bạn tham gia tranh tài.',
        'url'      => null,
        'chips'    => array(
            array('icon' => 'bi-dribbble text-success', 'label' => 'Thể thao VCK'),
            array('icon' => 'bi-diagram-3 text-primary', 'label' => 'Bảng thi đấu'),
            array('icon' => 'bi-award text-warning', 'label' => 'Tỷ số trực tiếp'),
        ),
    ),
    array(
        'category' => 'Hội thi tay nghề',
        'icon'     => 'bi-mortarboard-fill',
        'gradient' => 'icon-gradient-purple',
        'title'    => 'Lịch thi nghiệp vụ',
        'desc'     => 'Tra cứu số báo danh (SBD), phòng thi, ca thi và lịch các vòng thi nghiệp vụ Vòng Chung Kết toàn tập đoàn.',
        'url'      => null,
        'chips'    => array(
            array('icon' => 'bi-person-badge text-purple', 'label' => 'Tra cứu SBD'),
            array('icon' => 'bi-door-open text-primary', 'label' => 'Phòng & Ca thi'),
            array('icon' => 'bi-card-checklist text-success', 'label' => 'Quy chế thi'),
        ),
    ),
    array(
        'category' => 'Khoảnh khắc đại hội',
        'icon'     => 'bi-images',
        'gradient' => 'icon-gradient-amber',
        'title'    => 'Kho hình ảnh kỷ niệm',
        'desc'     => 'Tìm kiếm và tải về bộ ảnh chất lượng cao ghi lại những khoảnh khắc đáng nhớ của bạn trong suốt kỳ Đại hội.',
        'url'      => null,
        'chips'    => array(
            array('icon' => 'bi-camera text-warning', 'label' => 'Ảnh Vòng CK'),
            array('icon' => 'bi-stars text-danger', 'label' => 'AI Nhận diện'),
            array('icon' => 'bi-download text-primary', 'label' => 'Tải ảnh gốc HD'),
        ),
    ),
);
?>

<!-- Portal Navigation Topbar -->
<header class="portal-topbar mb-4">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between">
            <!-- Brand Logo & Name -->
            <a href="<?php echo $this->createUrl('/frontend/portal/index'); ?>" class="portal-brand-link">
                <img src="<?php echo Yii::app()->theme->baseUrl; ?>/logo_daihoi.png" 
                     alt="Đại hội Mường Thanh 2026" 
                     class="portal-brand-logo">
                <div>
                    <h1 class="portal-brand-title">ĐẠI HỘI MƯỜNG THANH 2026</h1>
                    <span class="portal-brand-sub">Cổng Tiện Ích Đại Biểu • Ninh Bình</span>
                </div>
            </a>

            <!-- Delegate Info & Logout Action -->
            <div class="d-flex align-items-center gap-2 gap-sm-3">
                <div class="portal-user-chip d-none d-sm-flex">
                    <div class="portal-user-avatar">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div>
                        <div class="portal-user-name"><?php echo CHtml::encode($fullName); ?></div>
                        <span class="portal-user-role">Đại biểu VCK</span>
                    </div>
                </div>

                <a href="<?php echo $logoutUrl; ?>" 
                   class="portal-btn-logout" 
                   id="btn-portal-logout"
                   title="Đăng xuất khỏi cổng cá nhân">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Thoát</span>
                </a>
            </div>
        </div>
    </div>
</header>

<!-- Main Dashboard Container -->
<div class="container">
    <!-- Hero Welcome Banner -->
    <div class="portal-hero-banner">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <div class="portal-hero-pill">
                    <span class="pulse-dot-green"></span>
                    Vòng Chung Kết • Ninh Bình 2026
                </div>
                <h2 class="portal-hero-title">
                    Xin chào, <span class="hero-name-highlight"><?php echo CHtml::encode($fullName); ?></span> 👋
                </h2>
                <p class="portal-hero-subtitle">
                    Chào mừng Quý Đại biểu đến với Cổng tiện ích cá nhân Đại hội Mường Thanh 2026. Lựa chọn các dịch vụ bên dưới để bắt đầu trải nghiệm tiện ích tự phục vụ.
                </p>
                <div class="portal-hero-badges">
                    <div class="hero-badge-item">
                        <i class="bi bi-shield-check text-success"></i>
                        <span>Tài khoản đã xác thực</span>
                    </div>
                    <div class="hero-badge-item">
                        <i class="bi bi-geo-alt-fill text-warning"></i>
                        <span>Địa điểm: Ninh Bình</span>
                    </div>
                    <div class="hero-badge-item">
                        <i class="bi bi-lightning-charge-fill text-info"></i>
                        <span>Tự phục vụ 24/7</span>
                    </div>
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
    </div>

    <!-- Features Grid -->
    <div class="row g-3 g-md-4 mb-4">
        <?php foreach ($features as $f): ?>
            <?php $available = !empty($f['url']); ?>
            <div class="col-12 col-sm-6 col-lg-3">
                <?php if ($available): ?>
                    <a href="<?php echo $f['url']; ?>" class="portal-card-link">
                <?php else: ?>
                    <div class="portal-card-link">
                <?php endif; ?>

                <div class="portal-feature-card <?php echo $available ? 'card-available' : 'card-disabled'; ?>">
                    <!-- Card Top: Category & Status -->
                    <div class="portal-card-top">
                        <span class="portal-badge-category"><?php echo CHtml::encode($f['category']); ?></span>
                        <?php if ($available): ?>
                            <span class="status-pill pill-active">
                                <span class="pulse-dot-green"></span>
                                Đang mở
                            </span>
                        <?php else: ?>
                            <span class="status-pill pill-locked">
                                <i class="bi bi-clock-history"></i>
                                Sắp ra mắt
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
                            <div class="btn-card-action btn-card-primary">
                                <span>Truy cập ngay</span>
                                <i class="bi bi-arrow-right"></i>
                            </div>
                        <?php else: ?>
                            <div class="btn-card-action btn-card-locked">
                                <i class="bi bi-hourglass-split"></i>
                                <span>Sắp ra mắt</span>
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
        <div class="row align-items-center g-2 g-md-3">
            <div class="col-lg-8">
                <div class="portal-info-item">
                    <i class="bi bi-info-circle-fill text-primary"></i>
                    <span>
                        <strong>Lưu ý:</strong> Cổng đăng ký hoạt động tự phục vụ có giới hạn số lượng theo từng cự ly và đợt tham quan. Quý đại biểu vui lòng đăng ký sớm để chọn khung giờ phù hợp.
                    </span>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="portal-info-item justify-content-lg-end text-muted small">
                    <i class="bi bi-shield-lock-fill text-success"></i>
                    <span>Bảo mật 2 lớp qua mã PIN & QR thẻ đeo</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Logout confirmation script with SweetAlert2 -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var logoutBtn = document.getElementById('btn-portal-logout');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
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
    }
});
</script>
