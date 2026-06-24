<?php
$this->pageTitle = 'Đã gửi hồ sơ - ' . Yii::app()->name;
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

.miss-submitted-page {
    min-height: 100vh;
    width: 100vw;
    background: url('<?php echo $baseUrl; ?>/assets/images/background-miss.jpg') center center / cover no-repeat fixed;
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

.submitted-card {
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

.submitted-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, #17a2b8, #20c997, #17a2b8);
}

.info-icon {
    width: 110px;
    height: 110px;
    background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    box-shadow: 0 15px 40px rgba(23,162,184,0.35);
    position: relative;
}

.info-icon::after {
    content: '';
    position: absolute;
    width: 130px;
    height: 130px;
    border: 3px dashed rgba(23,162,184,0.3);
    border-radius: 50%;
    animation: spin 20s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.info-icon i {
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

.decorative-icons i:nth-child(1) { color: #17a2b8; }
.decorative-icons i:nth-child(2) { color: #20c997; }
.decorative-icons i:nth-child(3) { color: #17a2b8; }
.decorative-icons i:nth-child(4) { color: #20c997; }
.decorative-icons i:nth-child(5) { color: #17a2b8; }

.submitted-title {
    font-family: 'Montserrat', sans-serif;
    font-size: 1.8rem;
    font-weight: 700;
    background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 20px;
}

.submitted-text {
    font-size: 1.1rem;
    color: #555;
    margin-bottom: 10px;
}

.submitted-text strong {
    color: #d4145a;
}

.submitted-date {
    color: #666;
    font-size: 0.95rem;
    margin-bottom: 25px;
}

.submitted-date strong {
    color: #17a2b8;
}

.info-box {
    background: linear-gradient(135deg, #e8f7f9 0%, #e3f9f5 100%);
    border-radius: 16px;
    padding: 20px 25px;
    margin: 25px 0;
    border: 1px solid rgba(23,162,184,0.3);
    text-align: left;
}

.info-box i {
    color: #17a2b8;
    margin-right: 10px;
}

.info-box p {
    margin: 0;
    color: #555;
    line-height: 1.6;
}

.waiting-text {
    background: linear-gradient(135deg, #fff8e1 0%, #fff3cd 100%);
    border-radius: 16px;
    padding: 20px 25px;
    margin: 20px 0;
    border: 1px solid rgba(255,193,7,0.3);
}

.waiting-text i {
    color: #ffc107;
    margin-right: 10px;
    font-size: 1.2rem;
}

.waiting-text span {
    color: #856404;
}

.heart-divider {
    margin: 25px 0;
    color: #b8e0e5;
    font-size: 0.9rem;
    letter-spacing: 5px;
}

.contact-text {
    color: #888;
    font-size: 0.9rem;
    margin-top: 10px;
}

.contact-text i {
    color: #17a2b8;
    margin-right: 5px;
}

@media (max-width: 768px) {
    .submitted-card {
        padding: 40px 25px;
    }

    .submitted-title {
        font-size: 1.5rem;
    }

    .info-icon {
        width: 90px;
        height: 90px;
    }

    .info-icon::after {
        width: 110px;
        height: 110px;
    }

    .info-icon i {
        font-size: 40px;
    }

    .decorative-icons i {
        font-size: 1.2rem;
    }
}
</style>

<div class="miss-submitted-page">
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
            <i class="fa fa-folder-open" style="font-size: 0.8em; margin-right: 8px;"></i>
            Hồ sơ đã được gửi
        </h2>

        <p class="submitted-text">
            <i class="fa fa-user" style="color: #17a2b8; margin-right: 5px;"></i>
            Xin chào <strong><?php echo CHtml::encode($model->attendee_name); ?></strong>
        </p>

        <p class="submitted-date">
            <i class="fa fa-calendar" style="color: #20c997; margin-right: 5px;"></i>
            Bạn đã gửi hồ sơ vào ngày <strong><?php echo date('d/m/Y H:i', strtotime($model->submitted_at)); ?></strong>
        </p>

        <div class="info-box">
            <p>
                <i class="fa fa-info-circle"></i>
                Bạn đã gửi các nội dung dự thi thành công. Vui lòng chờ Ban tổ chức đánh giá và phản hồi lại ngay khi có kết quả.
            </p>
        </div>

        <div class="waiting-text">
            <i class="fa fa-hourglass-half"></i>
            <span>Mỗi thí sinh chỉ được gửi hồ sơ một lần.</span>
        </div>

        <div class="heart-divider">
            ♥ ♥ ♥
        </div>

        <p class="contact-text">
            <i class="fa fa-phone"></i>
            Nếu cần hỗ trợ, vui lòng liên hệ Ban tổ chức.
        </p>
    </div>
</div>
