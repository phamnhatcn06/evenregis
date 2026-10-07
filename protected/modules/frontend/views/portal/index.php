<?php
/**
 * Trang chủ cổng cá nhân: lưới các tính năng dành cho đại biểu sau khi đăng nhập.
 * Mỗi tính năng là một thẻ (card); tính năng chưa mở hiển thị trạng thái "Sắp ra mắt".
 */
$this->pageTitle = 'Cổng Cá Nhân Đại Hội';

Yii::app()->clientScript->registerCssFile(
    Yii::app()->theme->baseUrl . '/assets/vendor/bootstrap-icons/bootstrap-icons.css'
);
Yii::app()->clientScript->registerCssFile(
    Yii::app()->theme->baseUrl . '/assets/css/pages/run-portal.css'
);

$logoutUrl = $this->createUrl('/frontend/portal/logout');

// Danh mục tính năng. 'url' = null -> hiển thị "Sắp ra mắt" (disabled).
$features = array(
    array(
        'icon'  => 'bi-calendar2-check-fill',
        'color' => 'primary',
        'title' => 'Đăng ký hoạt động',
        'desc'  => 'Đăng ký cự ly Fun Run và đợt đi tham quan (giới hạn số lượng).',
        'url'   => $this->createUrl('/frontend/run/index'),
    ),
    array(
        'icon'  => 'bi-trophy-fill',
        'color' => 'success',
        'title' => 'Lịch thi đấu của tôi',
        'desc'  => 'Xem lịch, bảng đấu và kết quả các môn thể thao bạn tham gia.',
        'url'   => null,
    ),
    array(
        'icon'  => 'bi-mortarboard-fill',
        'color' => 'info',
        'title' => 'Lịch thi nghiệp vụ',
        'desc'  => 'Xem số báo danh, phòng thi và lịch các vòng thi nghiệp vụ.',
        'url'   => null,
    ),
    array(
        'icon'  => 'bi-images',
        'color' => 'warning',
        'title' => 'Hình ảnh của tôi',
        'desc'  => 'Xem và tải về những hình ảnh của bạn tại Đại hội.',
        'url'   => null,
    ),
);
?>

<!-- Header -->
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
                Cổng cá nhân • Chọn một tính năng bên dưới để bắt đầu
            </div>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2 justify-content-md-end">
        <a href="<?php echo $logoutUrl; ?>" class="btn btn-outline-danger btn-sm rounded-pill px-3" title="Đăng xuất khỏi cổng">
            <i class="bi bi-box-arrow-right me-1"></i> Thoát
        </a>
    </div>
</div>

<!-- Lưới tính năng -->
<div class="row g-4 mb-4">
    <?php foreach ($features as $f): ?>
        <?php $available = !empty($f['url']); ?>
        <div class="col-lg-3 col-md-6">
            <?php if ($available): ?>
                <a href="<?php echo $f['url']; ?>" class="text-decoration-none">
            <?php else: ?>
                <div class="position-relative">
            <?php endif; ?>

            <div class="card h-100 border-0 shadow-sm rounded-4 portal-feature-card <?php echo $available ? '' : 'opacity-75'; ?>">
                <div class="card-body p-4 text-center">
                    <div class="user-avatar-circle mx-auto mb-3 bg-<?php echo $f['color']; ?>-subtle text-<?php echo $f['color']; ?>"
                         style="width: 64px; height: 64px; font-size: 1.8rem;">
                        <i class="bi <?php echo $f['icon']; ?>"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2"><?php echo CHtml::encode($f['title']); ?></h5>
                    <p class="text-muted small mb-3"><?php echo CHtml::encode($f['desc']); ?></p>
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
