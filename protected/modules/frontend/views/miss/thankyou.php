<?php
$this->pageTitle = 'Cảm ơn - ' . Yii::app()->name;
$baseUrl = Yii::app()->theme->baseUrl;

Yii::app()->clientScript->registerCssFile($baseUrl . '/assets/css/pages/miss-frontend.css');
?>

<div class="miss-thankyou-page" style="background-image: url('<?php echo $baseUrl; ?>/assets/images/background-miss.jpg');">
    <div class="thankyou-card">
        <div class="decorative-icons">
            <i class="fa fa-star"></i>
            <i class="fa fa-heart"></i>
            <i class="fa fa-diamond"></i>
            <i class="fa fa-heart"></i>
            <i class="fa fa-star"></i>
        </div>

        <div class="success-icon">
            <i class="fa fa-check"></i>
        </div>

        <h2 class="thankyou-title">
            <i class="fa fa-heart"></i>
            Gửi hồ sơ thành công!
        </h2>

        <?php if ($model): ?>
            <p class="thankyou-text">
                <i class="fa fa-user"></i>
                Cảm ơn <strong><?php echo CHtml::encode($model->attendee_name); ?></strong>
                đã gửi hồ sơ dự thi.
            </p>
        <?php else: ?>
            <p class="thankyou-text">
                <i class="fa fa-smile-o"></i>
                Cảm ơn bạn đã gửi hồ sơ dự thi.
            </p>
        <?php endif; ?>

        <div class="info-box">
            <i class="fa fa-envelope"></i>
            <span>Email xác nhận đã được gửi đến địa chỉ email của bạn.</span>
        </div>

        <div class="heart-divider">
            &#9829; &#9829; &#9829;
        </div>

        <p class="contact-text">
            <i class="fa fa-phone"></i>
            Nếu có thắc mắc, vui lòng liên hệ Ban tổ chức.
        </p>

        <div class="flower-decoration">
            &#127800; &#127802; &#127800;
        </div>
    </div>
</div>
