<?php
/**
 * Trang chủ Cổng cá nhân đại biểu - Đại hội Mường Thanh 2026 Ninh Bình.
 * Giao diện chuẩn Bootstrap 5, đồng bộ layout với toàn hệ thống.
 */
$this->pageTitle = 'Cổng Cá Nhân Đại Biểu';

Yii::app()->clientScript->registerCssFile(
    Yii::app()->theme->baseUrl . '/assets/css/pages/run-portal.css'
);
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/portal-profile.js',
    CClientScript::POS_END
);

$logoutUrl = $this->createUrl('/frontend/portal/logout');

// Danh mục tính năng (dùng Bootstrap color & icon chuẩn)
$features = array(
    array(
        'icon'  => 'bi-calendar-week-fill',
        'color' => 'primary',
        'title' => 'Lịch trình của tôi',
        'desc'  => 'Xem toàn bộ lịch trình liên quan đến anh/chị: trận thi đấu, vòng thi nghiệp vụ, nghi thức, bữa ăn và tiệc.',
        'url'   => $this->createUrl('/frontend/portal/schedule'),
    ),
    array(
        'icon'  => 'bi-calendar2-check-fill',
        'color' => 'success',
        'title' => 'Đăng ký hoạt động',
        'desc'  => 'Đăng ký cự ly chạy Fun Run (5km/10km/15km) và đợt đi tour tham quan Quần thể Di sản Tràng An.',
        'url'   => $this->createUrl('/frontend/run/index'),
    ),
    array(
        'icon'  => 'bi-trophy-fill',
        'color' => 'warning',
        'title' => 'Lịch thi đấu của tôi',
        'desc'  => 'Xem lịch thi đấu, bảng đấu, địa điểm sân bãi và kết quả các môn thể thao bạn tham gia tranh tài.',
        'url'   => null,
    ),
    array(
        'icon'  => 'bi-mortarboard-fill',
        'color' => 'info',
        'title' => 'Lịch thi nghiệp vụ',
        'desc'  => 'Tra cứu số báo danh (SBD), phòng thi, ca thi và lịch các vòng thi nghiệp vụ Vòng Chung Kết.',
        'url'   => null,
    ),
    array(
        'icon'  => 'bi-images',
        'color' => 'secondary',
        'title' => 'Kho hình ảnh kỷ niệm',
        'desc'  => 'Tìm kiếm và tải về bộ ảnh chất lượng cao ghi lại những khoảnh khắc đáng nhớ của bạn trong suốt kỳ Đại hội.',
        'url'   => null,
    ),
);
?>

<div class="container py-3 py-md-4">
    <!-- Header: Thông tin đại biểu & Thao tác -->
    <div class="run-portal-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="user-avatar-circle">
                <i class="bi bi-person-fill"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h4 class="mb-0 fw-bold text-dark">Xin chào, <?php echo CHtml::encode($fullName); ?></h4>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 small">
                        <i class="bi bi-shield-check me-1"></i>Đại biểu VCK
                    </span>
                </div>
                <div class="text-muted small mt-1">
                    Cổng cá nhân • Đại hội Mường Thanh 2026
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-md-end">
            <a href="<?php echo Yii::app()->request->baseUrl; ?>/" class="btn btn-outline-secondary btn-sm rounded-pill px-3" title="Về trang chủ Đại hội">
                <i class="bi bi-house-door me-1"></i> Trang chủ
            </a>
            <a href="<?php echo $logoutUrl; ?>" class="btn btn-outline-danger btn-sm rounded-pill px-3" id="btn-portal-logout" title="Đăng xuất khỏi cổng">
                <i class="bi bi-box-arrow-right me-1"></i> Thoát
            </a>
        </div>
    </div>

    <!-- Lưới tính năng -->
    <div class="row g-3 g-md-4 mb-4">
        <?php foreach ($features as $f): ?>
            <?php $available = !empty($f['url']); ?>
            <div class="col-12 col-sm-6 col-lg-4">
                <?php if ($available): ?>
                    <a href="<?php echo $f['url']; ?>" class="text-decoration-none">
                <?php else: ?>
                    <div class="position-relative">
                <?php endif; ?>

                <div class="card h-100 border-0 shadow-sm rounded-4 <?php echo $available ? 'portal-card-available' : 'opacity-75 bg-light'; ?>" style="transition: all 0.25s ease;">
                    <div class="card-body p-4 text-center d-flex flex-column align-items-center">
                        <div class="user-avatar-circle mx-auto mb-3 bg-<?php echo $f['color']; ?>-subtle text-<?php echo $f['color']; ?>"
                             style="width: 60px; height: 60px; font-size: 1.75rem;">
                            <i class="bi <?php echo $f['icon']; ?>"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2"><?php echo CHtml::encode($f['title']); ?></h5>
                        <p class="text-muted small mb-4 flex-grow-1"><?php echo CHtml::encode($f['desc']); ?></p>

                        <?php if ($available): ?>
                            <span class="badge bg-<?php echo $f['color']; ?> px-3 py-2 rounded-pill">
                                Truy cập <i class="bi bi-arrow-right ms-1"></i>
                            </span>
                        <?php else: ?>
                            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill">
                                <i class="bi bi-clock-history me-1"></i> Sắp ra mắt
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php echo $available ? '</a>' : '</div>'; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Lưu ý hỗ trợ -->
    <div class="alert alert-info d-flex align-items-center gap-2 border-0 shadow-sm rounded-3 mb-4" role="alert">
        <i class="bi bi-info-circle-fill text-primary fs-5 flex-shrink-0"></i>
        <div class="small">
            <strong>Lưu ý:</strong> Cổng đăng ký hoạt động tự phục vụ có giới hạn số lượng theo từng cự ly và đợt tham quan. Quý đại biểu vui lòng đăng ký sớm để chọn khung giờ phù hợp.
        </div>
    </div>
</div>

<style>
.portal-card-available:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08) !important;
}
</style>

<?php
// Popup xác nhận hồ sơ (chỉ có khi chưa xác nhận trong phiên đăng nhập này).
if (!empty($profileModal)) {
    $this->renderPartial('_modal_profile', $profileModal);
}
?>

<!-- Script xác nhận đăng xuất với SweetAlert2 -->
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
                    confirmButtonText: 'Đăng xuất',
                    cancelButtonText: 'Ở lại',
                    reverseButtons: true
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