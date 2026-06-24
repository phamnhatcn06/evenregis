<?php
$this->pageTitle = 'Đã gửi hồ sơ - ' . Yii::app()->name;
$baseUrl = Yii::app()->theme->baseUrl;

Yii::app()->clientScript->registerCssFile($baseUrl . '/assets/css/pages/miss-frontend.css');
?>

<div class="miss-submitted-page" style="background-image: url('<?php echo $baseUrl; ?>/assets/images/background-miss.jpg');">
    <div class="submitted-card">
        <div class="decorative-icons">
            <i class="fa fa-star"></i>
            <i class="fa fa-heart"></i>
            <i class="fa fa-diamond"></i>
            <i class="fa fa-heart"></i>
            <i class="fa fa-star"></i>
        </div>

        <div class="info-icon">
            <i class="fa fa-check"></i>
        </div>

        <h2 class="submitted-title">
            <i class="fa fa-folder-open"></i>
            Hồ sơ đã được gửi
        </h2>

        <div class="info-box">
            <p>
                <i class="fa fa-info-circle"></i>
                Bạn đã gửi hồ sơ. Vui lòng chờ Ban tổ chức đánh giá và phản hồi lại ngay khi có kết quả.
            </p>
        </div>

        <div class="waiting-text">
            <i class="fa fa-hourglass-half"></i>
            <span>Mỗi thí sinh chỉ được gửi hồ sơ một lần.</span>
        </div>

        <div class="heart-divider">
            &#9829; &#9829; &#9829;
        </div>

        <p class="contact-text">
            <i class="fa fa-phone"></i>
            Nếu cần hỗ trợ, vui lòng liên hệ Ban tổ chức.
        </p>
    </div>
</div>
