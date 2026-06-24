<?php
$this->pageTitle = 'Cảm ơn - ' . Yii::app()->name;
$baseUrl = Yii::app()->theme->baseUrl;
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap');

body {
    padding-top: 0 !important;
    margin: 0 !important;
}

.container {
    max-width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
    width: 100% !important;
}

.miss-thankyou-page {
    min-height: 100vh;
    width: 100vw;
    background: linear-gradient(135deg, rgba(255,245,248,0.92) 0%, rgba(255,238,242,0.92) 100%),
                url('<?php echo $baseUrl; ?>/assets/images/background-miss.jpg') center center / cover no-repeat fixed;
    padding: 30px 15px;
    margin: 0;
    position: fixed;
    top: 0;
    left: 0;
    font-family: 'Montserrat', sans-serif;
    display: flex;
    align-items: center;
    justify-content: center;
}

.miss-thankyou-page::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background:
        radial-gradient(ellipse at bottom left, rgba(255,182,193,0.3) 0%, transparent 50%),
        radial-gradient(ellipse at top right, rgba(255,105,180,0.15) 0%, transparent 50%),
        radial-gradient(ellipse at bottom right, rgba(255,192,203,0.2) 0%, transparent 50%);
    pointer-events: none;
    z-index: 0;
}

.thankyou-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 24px;
    box-shadow: 0 15px 50px rgba(212,20,90,0.1);
    border: 1px solid rgba(255,182,193,0.5);
    backdrop-filter: blur(10px);
    overflow: hidden;
    position: relative;
    z-index: 1;
    max-width: 550px;
    width: 100%;
    text-align: center;
    padding: 50px 40px;
}

.thankyou-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, #ff6b9d, #fbb03b, #ff6b9d);
}

.confetti {
    position: absolute;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 2rem;
    opacity: 0.8;
}

.success-icon {
    width: 110px;
    height: 110px;
    background: linear-gradient(135deg, #ff6b9d 0%, #ff8fab 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    box-shadow: 0 15px 40px rgba(255,107,157,0.35);
    position: relative;
}

.success-icon::after {
    content: '';
    position: absolute;
    width: 130px;
    height: 130px;
    border: 3px dashed rgba(255,107,157,0.3);
    border-radius: 50%;
    animation: spin 20s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.success-icon i {
    font-size: 50px;
    color: #fff;
}

.decorative-icons {
    margin-bottom: 15px;
}

.decorative-icons i {
    font-size: 1.5rem;
    margin: 0 8px;
    opacity: 0.7;
}

.decorative-icons i:nth-child(1) { color: #ff6b9d; }
.decorative-icons i:nth-child(2) { color: #fbb03b; }
.decorative-icons i:nth-child(3) { color: #ff8fab; }
.decorative-icons i:nth-child(4) { color: #ffb6c1; }
.decorative-icons i:nth-child(5) { color: #ff6b9d; }

.thankyou-title {
    font-family: 'Montserrat', sans-serif;
    font-size: 2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #ff6b9d 0%, #d4145a 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 20px;
}

.thankyou-text {
    font-size: 1.1rem;
    color: #555;
    margin-bottom: 10px;
}

.thankyou-text strong {
    color: #d4145a;
}

.contest-name {
    color: #666;
    font-size: 0.95rem;
    margin-bottom: 25px;
}

.contest-name strong {
    color: #333;
}

.info-box {
    background: linear-gradient(135deg, #fff0f5 0%, #ffe4ec 100%);
    border-radius: 16px;
    padding: 20px 25px;
    margin: 25px 0;
    border: 1px solid rgba(255,182,193,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
}

.info-box i {
    color: #ff6b9d;
    font-size: 1.3rem;
}

.info-box span {
    color: #666;
}

.heart-divider {
    margin: 25px 0;
    color: #ffb6c1;
    font-size: 0.9rem;
    letter-spacing: 5px;
}

.contact-text {
    color: #888;
    font-size: 0.9rem;
    margin-top: 10px;
}

.contact-text i {
    color: #ff6b9d;
    margin-right: 5px;
}

.flower-decoration {
    margin-top: 25px;
    font-size: 1.8rem;
    opacity: 0.6;
}

@media (max-width: 768px) {
    .thankyou-card {
        padding: 40px 25px;
    }

    .thankyou-title {
        font-size: 1.6rem;
    }

    .success-icon {
        width: 90px;
        height: 90px;
    }

    .success-icon::after {
        width: 110px;
        height: 110px;
    }

    .success-icon i {
        font-size: 40px;
    }

    .decorative-icons i {
        font-size: 1.2rem;
    }
}
</style>

<div class="miss-thankyou-page">
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
            <i class="fa fa-heart" style="font-size: 0.8em; margin-right: 8px;"></i>
            Gửi hồ sơ thành công!
        </h2>

        <?php if ($model): ?>
            <p class="thankyou-text">
                <i class="fa fa-user" style="color: #ff6b9d; margin-right: 5px;"></i>
                Cảm ơn <strong><?php echo CHtml::encode($model->attendee_name); ?></strong>
                đã gửi hồ sơ dự thi.
            </p>
            <p class="contest-name">
                <i class="fa fa-trophy" style="color: #fbb03b; margin-right: 5px;"></i>
                Cuộc thi: <strong><?php echo CHtml::encode($model->contest_name); ?></strong>
            </p>
        <?php else: ?>
            <p class="thankyou-text">
                <i class="fa fa-smile-o" style="color: #ff6b9d; margin-right: 5px;"></i>
                Cảm ơn bạn đã gửi hồ sơ dự thi.
            </p>
        <?php endif; ?>

        <div class="info-box">
            <i class="fa fa-envelope"></i>
            <span>Email xác nhận đã được gửi đến địa chỉ email của bạn.</span>
        </div>

        <div class="heart-divider">
            ♥ ♥ ♥
        </div>

        <p class="contact-text">
            <i class="fa fa-phone"></i>
            Nếu có thắc mắc, vui lòng liên hệ Ban tổ chức.
        </p>

        <div class="flower-decoration">
            🌸 🌺 🌸
        </div>
    </div>
</div>
