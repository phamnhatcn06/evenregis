<?php
$this->pageTitle = 'Đăng ký hoạt động Đại hội';

Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/run-portal.js',
    CClientScript::POS_END
);

$registerRunUrl = $this->createUrl('/frontend/run/register');
$registerTourUrl = $this->createUrl('/frontend/run/registerTour');
$cancelRunUrl = $this->createUrl('/frontend/run/cancelRequest');
$cancelTourUrl = $this->createUrl('/frontend/run/cancelRequestTour');

// Dữ liệu thông báo hủy-đã-duyệt cho JS (báo 1 lần qua localStorage).
$runNoticeAt = (!empty($runCancelledNotice) && !empty($runCancelledNotice['cancel_reviewed_at'])) ? (int) $runCancelledNotice['cancel_reviewed_at'] : 0;
$runNoticeName = (!empty($runCancelledNotice) && !empty($runCancelledNotice['run_event_name'])) ? $runCancelledNotice['run_event_name'] : '';
$tourNoticeAt = (!empty($tourCancelledNotice) && !empty($tourCancelledNotice['cancel_reviewed_at'])) ? (int) $tourCancelledNotice['cancel_reviewed_at'] : 0;
$tourNoticeName = (!empty($tourCancelledNotice) && !empty($tourCancelledNotice['tour_session_name'])) ? $tourCancelledNotice['tour_session_name'] : '';

// Yêu cầu hủy bị TỪ CHỐI: đăng ký đang active trở lại nhưng đã có mốc xử lý + còn lý do.
$runRejectAt = (!empty($runMine) && isset($runMine['status']) && $runMine['status'] === 'active' && !empty($runMine['cancel_reviewed_at']) && !empty($runMine['cancel_reason'])) ? (int) $runMine['cancel_reviewed_at'] : 0;
$tourRejectAt = (!empty($tourMine) && isset($tourMine['status']) && $tourMine['status'] === 'active' && !empty($tourMine['cancel_reviewed_at']) && !empty($tourMine['cancel_reason'])) ? (int) $tourMine['cancel_reviewed_at'] : 0;
?>
<div class="d-flex justify-content-between align-items-center mt-3 mb-3">
    <div>
        <h4 class="mb-0">Đăng ký hoạt động Đại hội</h4>
        <small class="text-muted">Xin chào <?php echo CHtml::encode($fullName); ?> — mỗi nội dung chỉ được chọn <strong>1 lựa chọn</strong> và không thể đổi.</small>
    </div>
    <a href="<?php echo $this->createUrl('/frontend/run/logout'); ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-sign-out"></i> Thoát
    </a>
</div>

<div id="run-config"
     data-register-run-url="<?php echo $registerRunUrl; ?>"
     data-register-tour-url="<?php echo $registerTourUrl; ?>"
     data-run-cancel-approved="<?php echo $runNoticeAt > 0 ? '1' : '0'; ?>"
     data-run-cancel-reviewed-at="<?php echo $runNoticeAt; ?>"
     data-run-cancel-name="<?php echo CHtml::encode($runNoticeName); ?>"
     data-tour-cancel-approved="<?php echo $tourNoticeAt > 0 ? '1' : '0'; ?>"
     data-tour-cancel-reviewed-at="<?php echo $tourNoticeAt; ?>"
     data-tour-cancel-name="<?php echo CHtml::encode($tourNoticeName); ?>"
     data-run-cancel-rejected="<?php echo $runRejectAt > 0 ? '1' : '0'; ?>"
     data-run-reject-reviewed-at="<?php echo $runRejectAt; ?>"
     data-tour-cancel-rejected="<?php echo $tourRejectAt > 0 ? '1' : '0'; ?>"
     data-tour-reject-reviewed-at="<?php echo $tourRejectAt; ?>"></div>

<div class="row g-4">
    <div class="col-lg-6">
        <?php $this->renderPartial('_section_run', array('runMine' => $runMine, 'runEvents' => $runEvents)); ?>
    </div>
    <div class="col-lg-6">
        <?php $this->renderPartial('_section_tour', array('tourMine' => $tourMine, 'tourSessions' => $tourSessions)); ?>
    </div>
</div>

<?php
// Modal xin hủy cho từng nội dung (chỉ render khi có đăng ký còn cho hủy).
if (!empty($runMine) && !empty($runMine['can_request_cancel']) && $runMine['status'] !== 'cancel_requested') {
    $this->renderPartial('_modal_cancel', array(
        'modalId' => 'modalCancelRun', 'formId' => 'form-cancel-run',
        'btnId' => 'btn-submit-cancel-run', 'cancelUrl' => $cancelRunUrl,
        'title' => 'Xin hủy đăng ký chạy',
    ));
}
if (!empty($tourMine) && !empty($tourMine['can_request_cancel']) && $tourMine['status'] !== 'cancel_requested') {
    $this->renderPartial('_modal_cancel', array(
        'modalId' => 'modalCancelTour', 'formId' => 'form-cancel-tour',
        'btnId' => 'btn-submit-cancel-tour', 'cancelUrl' => $cancelTourUrl,
        'title' => 'Xin hủy đăng ký tham quan',
    ));
}
?>
