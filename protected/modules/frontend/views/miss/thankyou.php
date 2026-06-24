<?php
$this->pageTitle = 'Cảm ơn - ' . Yii::app()->name;
$baseUrl = Yii::app()->theme->baseUrl;
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap');

.miss-thankyou-page {
    min-height: 100vh;
    background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(255,250,245,0.95) 100%);
    padding: 30px 15px;
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
        radial-gradient(ellipse at bottom left, rgba(255,107,107,0.15) 0%, transparent 50%),
        radial-gradient(ellipse at top right, rgba(255,159,67,0.15) 0%, transparent 50%),
        radial-gradient(ellipse at bottom right, rgba(0,206,201,0.1) 0%, transparent 50%);
    pointer-events: none;
    z-index: 0;
}

.thankyou-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
    border: 1px solid rgba(255,255,255,0.8);
    backdrop-filter: blur(10px);
    overflow: hidden;
    position: relative;
    z-index: 1;
    max-width: 600px;
    width: 100%;
    text-align: center;
    padding: 50px 40px;
}

.success-icon {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 30px;
    box-shadow: 0 10px 30px rgba(40,167,69,0.3);
}

.success-icon i {
    font-size: 50px;
    color: #fff;
}

.thankyou-title {
    font-family: 'Montserrat', sans-serif;
    font-size: 2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
    background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
    border-radius: 12px;
    padding: 20px;
    margin: 25px 0;
    border-left: 4px solid #d4145a;
}

.info-box i {
    color: #d4145a;
    margin-right: 8px;
}

.contact-text {
    color: #888;
    font-size: 0.9rem;
    margin-top: 20px;
}

@media (max-width: 768px) {
    .thankyou-card {
        padding: 40px 25px;
    }

    .thankyou-title {
        font-size: 1.6rem;
    }

    .success-icon {
        width: 80px;
        height: 80px;
    }

    .success-icon i {
        font-size: 40px;
    }
}
</style>

<div class="miss-thankyou-page">
    <div class="thankyou-card">
        <div class="success-icon">
            <i class="fa fa-check"></i>
        </div>

        <h2 class="thankyou-title">Gửi hồ sơ thành công!</h2>

        <?php if ($model): ?>
            <p class="thankyou-text">
                Cảm ơn <strong><?php echo CHtml::encode($model->attendee_name); ?></strong>
                đã gửi hồ sơ dự thi.
            </p>
            <p class="contest-name">
                Cuộc thi: <strong><?php echo CHtml::encode($model->contest_name); ?></strong>
            </p>
        <?php else: ?>
            <p class="thankyou-text">Cảm ơn bạn đã gửi hồ sơ dự thi.</p>
        <?php endif; ?>

        <div class="info-box">
            <i class="fa fa-envelope"></i>
            Email xác nhận đã được gửi đến địa chỉ email của bạn.
        </div>

        <p class="contact-text">
            Nếu có thắc mắc, vui lòng liên hệ Ban tổ chức.
        </p>
    </div>
</div>
