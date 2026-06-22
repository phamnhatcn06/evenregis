<?php
$this->pageTitle = 'Lỗi - ' . Yii::app()->name;
?>

<div class="row justify-content-center mt-5">
    <div class="col-lg-6 text-center">
        <div class="card shadow-sm border-danger">
            <div class="card-body py-5">
                <div class="mb-4">
                    <i class="fa fa-exclamation-triangle text-danger" style="font-size: 80px;"></i>
                </div>

                <h2 class="text-danger mb-3">Có lỗi xảy ra</h2>

                <p class="lead"><?php echo CHtml::encode($message); ?></p>

                <div class="mt-4">
                    <p class="text-muted">
                        Nếu bạn cần hỗ trợ, vui lòng liên hệ Ban tổ chức.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
