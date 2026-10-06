<?php
$this->pageTitle = 'Đăng ký chạy - Kết quả';

Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/run-result.js',
    CClientScript::POS_END
);

$status = isset($mine['status']) ? $mine['status'] : 'active';
$canCancel = !empty($mine['can_request_cancel']);
$isPending = ($status === 'cancel_requested');
// Bị từ chối hủy: đang active trở lại nhưng đã có mốc xử lý + còn lý do.
$wasRejected = ($status === 'active' && !empty($mine['cancel_reviewed_at']) && !empty($mine['cancel_reason']));
$cancelUrl = $this->createUrl('/frontend/run/cancelRequest');
?>
<div class="row justify-content-center mt-4">
    <div class="col-md-6 col-sm-9">
        <div class="card shadow-sm text-center">
            <div class="card-body p-4">
                <div class="mb-3">
                    <i class="fa fa-check-circle text-success" style="font-size:56px;"></i>
                </div>
                <h4 class="mb-1">Bạn đã đăng ký thành công!</h4>
                <p class="text-muted"><?php echo CHtml::encode($fullName); ?></p>

                <table class="table table-bordered mt-3">
                    <tr>
                        <th style="width:40%;background:#f8f9fa;">Nội dung</th>
                        <td><?php echo CHtml::encode(isset($mine['run_event_name']) ? $mine['run_event_name'] : ''); ?></td>
                    </tr>
                    <tr>
                        <th style="background:#f8f9fa;">Số BIB</th>
                        <td><span class="badge bg-primary fs-5"><?php echo CHtml::encode(isset($mine['bib_number']) ? $mine['bib_number'] : ''); ?></span></td>
                    </tr>
                </table>

                <?php if ($isPending): ?>
                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="fa fa-clock-o"></i> Yêu cầu hủy của bạn đang chờ ban tổ chức duyệt.
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mt-3 mb-0">Mỗi người chỉ được đăng ký 1 nội dung. Vui lòng lưu lại số BIB của bạn.</div>
                <?php endif; ?>

                <div class="mt-3 d-flex gap-2 justify-content-center">
                    <?php if ($canCancel && !$isPending): ?>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalCancelRun">
                            <i class="fa fa-times-circle"></i> Xin hủy đăng ký
                        </button>
                    <?php endif; ?>
                    <a href="<?php echo $this->createUrl('/frontend/run/logout'); ?>" class="btn btn-outline-secondary">
                        <i class="fa fa-sign-out"></i> Thoát
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($canCancel && !$isPending): ?>
    <?php $this->renderPartial('_modal_cancel', array('cancelUrl' => $cancelUrl)); ?>
<?php endif; ?>

<div id="run-result-config"
     data-was-rejected="<?php echo $wasRejected ? '1' : '0'; ?>"
     data-reject-reason="<?php echo CHtml::encode(isset($mine['cancel_reason']) ? $mine['cancel_reason'] : ''); ?>"
     data-reviewed-at="<?php echo (int) (isset($mine['cancel_reviewed_at']) ? $mine['cancel_reviewed_at'] : 0); ?>"></div>
