<?php
$this->pageTitle = 'Cảm ơn - ' . Yii::app()->name;
?>

<div class="row justify-content-center mt-5">
    <div class="col-lg-6 text-center">
        <div class="card shadow-sm">
            <div class="card-body py-5">
                <div class="mb-4">
                    <i class="fa fa-check-circle text-success" style="font-size: 80px;"></i>
                </div>

                <h2 class="text-success mb-3">Gửi hồ sơ thành công!</h2>

                <?php if ($model): ?>
                    <p class="lead">
                        Cảm ơn <strong><?php echo CHtml::encode($model->attendee_name); ?></strong>
                        đã gửi hồ sơ dự thi.
                    </p>
                    <p class="text-muted">
                        Cuộc thi: <strong><?php echo CHtml::encode($model->contest_name); ?></strong>
                    </p>
                <?php else: ?>
                    <p class="lead">Cảm ơn bạn đã gửi hồ sơ dự thi.</p>
                <?php endif; ?>

                <div class="alert alert-info mt-4">
                    <i class="fa fa-envelope me-1"></i>
                    Email xác nhận đã được gửi đến địa chỉ email của bạn.
                </div>

                <p class="mt-4">
                    Nếu có thắc mắc, vui lòng liên hệ Ban tổ chức.
                </p>
            </div>
        </div>
    </div>
</div>
