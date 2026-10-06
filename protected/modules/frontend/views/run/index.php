<?php
$this->pageTitle = 'Đăng ký chạy - Chọn nội dung';

Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/run-portal.js',
    CClientScript::POS_END
);
$registerUrl = $this->createUrl('/frontend/run/register');
?>
<div class="d-flex justify-content-between align-items-center mt-3 mb-3">
    <div>
        <h4 class="mb-0">Chọn nội dung chạy</h4>
        <small class="text-muted">Xin chào <?php echo CHtml::encode($fullName); ?> — mỗi người chỉ được đăng ký <strong>1 nội dung</strong>.</small>
    </div>
    <a href="<?php echo $this->createUrl('/frontend/run/logout'); ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-sign-out"></i> Thoát
    </a>
</div>

<?php
$noticeReviewedAt = (!empty($cancelledNotice) && !empty($cancelledNotice['cancel_reviewed_at'])) ? (int) $cancelledNotice['cancel_reviewed_at'] : 0;
$noticeName = (!empty($cancelledNotice) && !empty($cancelledNotice['run_event_name'])) ? $cancelledNotice['run_event_name'] : '';
?>
<div id="run-config"
     data-register-url="<?php echo $registerUrl; ?>"
     data-cancel-approved="<?php echo $noticeReviewedAt > 0 ? '1' : '0'; ?>"
     data-cancel-reviewed-at="<?php echo $noticeReviewedAt; ?>"
     data-cancel-event-name="<?php echo CHtml::encode($noticeName); ?>"></div>

<?php if (empty($events)): ?>
    <div class="alert alert-warning">Hiện chưa có nội dung chạy nào được mở đăng ký.</div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($events as $e):
            $remaining = (int) $e['remaining'];
            $full = $remaining <= 0 || empty($e['is_open_now']);
            $badgeCls = $remaining <= 0 ? 'bg-danger' : ($remaining <= 5 ? 'bg-warning text-dark' : 'bg-success');
        ?>
            <div class="col-md-4 col-sm-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo CHtml::encode($e['name']); ?></h5>
                        <p class="mb-2">
                            <span class="badge <?php echo $badgeCls; ?>">Còn <?php echo $remaining; ?> suất</span>
                            <span class="text-muted small">(<?php echo (int) $e['registered_count']; ?>/<?php echo (int) $e['quota']; ?>)</span>
                        </p>
                        <?php if (!empty($e['not_started'])): ?>
                            <button class="btn btn-secondary w-100" disabled>Chưa mở</button>
                        <?php elseif (!empty($e['ended'])): ?>
                            <button class="btn btn-secondary w-100" disabled>Đã hết hạn</button>
                        <?php elseif ($remaining <= 0): ?>
                            <button class="btn btn-secondary w-100" disabled>Hết chỗ</button>
                        <?php else: ?>
                            <button type="button" class="btn btn-primary w-100 btn-run-register"
                                    data-id="<?php echo (int) $e['id']; ?>"
                                    data-name="<?php echo CHtml::encode($e['name']); ?>">
                                Đăng ký
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
