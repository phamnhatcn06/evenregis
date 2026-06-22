<?php
$this->pageTitle = 'Đã gửi hồ sơ - ' . Yii::app()->name;
?>

<div class="row justify-content-center mt-5">
    <div class="col-lg-6 text-center">
        <div class="card shadow-sm border-info">
            <div class="card-body py-5">
                <div class="mb-4">
                    <i class="fa fa-info-circle text-info" style="font-size: 80px;"></i>
                </div>

                <h2 class="text-info mb-3">Hồ sơ đã được gửi</h2>

                <p class="lead">
                    Xin chào <strong><?php echo CHtml::encode($model->attendee_name); ?></strong>,
                </p>

                <p>Bạn đã gửi hồ sơ dự thi vào ngày
                    <strong><?php echo date('d/m/Y H:i', strtotime($model->submitted_at)); ?></strong>.
                </p>

                <div class="alert alert-info mt-4">
                    <i class="fa fa-envelope me-1"></i>
                    Mỗi thí sinh chỉ được gửi hồ sơ một lần.<br>
                    Nếu cần chỉnh sửa, vui lòng liên hệ Ban tổ chức.
                </div>
            </div>
        </div>
    </div>
</div>
