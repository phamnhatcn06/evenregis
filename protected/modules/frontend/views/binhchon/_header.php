<?php
/**
 * Header điều hướng cho Cổng bình chọn Miss.
 * Dùng Bootstrap 5 + Font Awesome (đã nạp trong layout //layouts/frontend).
 * Các link trỏ về những trang công khai của Đại hội để người dùng dễ điều hướng.
 */
$base = Yii::app()->request->baseUrl;
$logo = Yii::app()->theme->baseUrl . '/logo_daihoi.png';

// Danh sách link điều hướng chính (đồng bộ với trang Đại hội).
$navItems = array(
    array($base . '/', 'Trang chủ', 'fa-home'),
    array($base . '/agenda-tong-the', 'Lịch trình', 'fa-calendar'),
    array($base . '/lich-thi-the-thao', 'Lịch thi đấu', 'fa-trophy'),
    array($base . '/tin-tuc', 'Tin tức', 'fa-newspaper-o'),
    array($base . '/ho-tro', 'Hỗ trợ', 'fa-life-ring'),
);
?>
<header class="bc-site-header">
    <nav class="navbar navbar-expand-lg bc-navbar">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo CHtml::encode($base); ?>/">
                <img src="<?php echo CHtml::encode($logo); ?>" alt="Đại hội Mường Thanh" class="bc-navbar-logo">
                <span class="bc-navbar-brand-text">
                    <span class="bc-navbar-brand-main">ĐẠI HỘI MƯỜNG THANH</span>
                    <span class="bc-navbar-brand-sub">Cổng bình chọn Miss 2026</span>
                </span>
            </a>

            <button class="navbar-toggler bc-navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#bcNavbarMenu" aria-controls="bcNavbarMenu"
                    aria-expanded="false" aria-label="Mở menu">
                <i class="fa fa-bars"></i>
            </button>

            <div class="collapse navbar-collapse" id="bcNavbarMenu">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                    <?php foreach ($navItems as $it): ?>
                        <li class="nav-item">
                            <a class="nav-link bc-nav-link" href="<?php echo CHtml::encode($it[0]); ?>">
                                <i class="fa <?php echo $it[2]; ?> me-1"></i><?php echo CHtml::encode($it[1]); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>
