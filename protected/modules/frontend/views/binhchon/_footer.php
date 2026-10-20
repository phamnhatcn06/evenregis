<?php
/**
 * Footer cho Cổng bình chọn Miss — cung cấp link điều hướng nhanh về các trang
 * công khai của Đại hội và thông tin Ban Tổ Chức.
 */
$base = Yii::app()->request->baseUrl;
$logo = Yii::app()->theme->baseUrl . '/logo_daihoi.png';
$year = date('Y');
?>
<footer class="bc-site-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-12 col-md-6 col-lg-5">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <img src="<?php echo CHtml::encode($logo); ?>" alt="Mường Thanh" class="bc-footer-logo">
                    <span class="bc-footer-brand">MƯỜNG THANH</span>
                </div>
                <p class="bc-footer-desc">
                    Cổng bình chọn Miss Mường Thanh 2026 — Vòng Chung Kết. Nơi tôn vinh nét đẹp
                    duyên dáng, trí tuệ và sự tự tin của các thí sinh xuất sắc nhất.
                </p>
            </div>

            <div class="col-6 col-md-3 col-lg-3">
                <h4 class="bc-footer-heading">Điều hướng</h4>
                <ul class="bc-footer-links">
                    <li><a href="<?php echo CHtml::encode($base); ?>/">Trang chủ Đại hội</a></li>
                    <li><a href="<?php echo CHtml::encode($base); ?>/agenda-tong-the">Lịch trình Đại hội</a></li>
                    <li><a href="<?php echo CHtml::encode($base); ?>/lich-thi-the-thao">Lịch thi đấu &amp; kết quả</a></li>
                    <li><a href="<?php echo CHtml::encode($base); ?>/tin-tuc">Tin tức hoạt động</a></li>
                </ul>
            </div>

            <div class="col-6 col-md-3 col-lg-4">
                <h4 class="bc-footer-heading">Hỗ trợ</h4>
                <ul class="bc-footer-links">
                    <li><a href="<?php echo CHtml::encode($base); ?>/ho-tro">Hỗ trợ kỹ thuật</a></li>
                    <li><a href="<?php echo CHtml::encode($base); ?>/so-do-dia-diem">Sơ đồ địa điểm</a></li>
                    <li><span class="bc-footer-contact"><i class="fa fa-envelope-o me-1"></i>daihoi@muongthanh.vn</span></li>
                </ul>
            </div>
        </div>

        <div class="bc-footer-bottom">
            <p class="mb-0">&copy; <?php echo $year; ?> Tập đoàn Mường Thanh — Đại hội Mường Thanh 2026</p>
        </div>
    </div>
</footer>
