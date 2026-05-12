<?php
$this->pageTitle = 'Trang chủ - ' . Yii::app()->name;
?>

<div class="jumbotron bg-light p-5 rounded-3 mb-4">
    <h1 class="display-4">Chào mừng đến với <?php echo Yii::app()->name; ?></h1>
    <p class="lead">Hệ thống đăng ký sự kiện trực tuyến.</p>
    <hr class="my-4">
    <p>Đăng ký tài khoản để tham gia các sự kiện hấp dẫn.</p>
    <?php if (Yii::app()->user->isGuest): ?>
        <a class="btn btn-primary btn-lg" href="<?php echo $this->createUrl('register'); ?>">Đăng ký ngay</a>
    <?php endif; ?>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-calendar-check"></i> Sự kiện sắp tới</h5>
                <p class="card-text">Khám phá các sự kiện đang diễn ra và sắp tới.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-person-plus"></i> Đăng ký dễ dàng</h5>
                <p class="card-text">Chỉ cần vài bước đơn giản để đăng ký tham gia.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-bell"></i> Thông báo</h5>
                <p class="card-text">Nhận thông báo về các sự kiện mới.</p>
            </div>
        </div>
    </div>
</div>
