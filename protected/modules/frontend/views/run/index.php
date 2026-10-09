<?php
/**
 * Cổng đăng ký hoạt động Đại hội (Fun Run & Đi tham quan).
 * Giao diện chuyên nghiệp tích hợp icon, thẻ VĐV/vé tham quan & Countdown thời gian đăng ký.
 */
$this->pageTitle = 'Cổng Đăng Ký Hoạt Động Đại Hội';

// Đăng ký thư viện CSS & JS
Yii::app()->clientScript->registerCssFile(
    Yii::app()->theme->baseUrl . '/assets/vendor/bootstrap-icons/bootstrap-icons.css'
);
Yii::app()->clientScript->registerCssFile(
    Yii::app()->theme->baseUrl . '/assets/css/pages/run-portal.css'
);
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/run-portal.js',
    CClientScript::POS_END
);
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/portal-profile.js',
    CClientScript::POS_END
);

$registerRunUrl = $this->createUrl('/frontend/run/register');
$registerTourUrl = $this->createUrl('/frontend/run/registerTour');
$cancelRunUrl = $this->createUrl('/frontend/run/cancelRequest');
$cancelTourUrl = $this->createUrl('/frontend/run/cancelRequestTour');
$saveEmergencyUrl = $this->createUrl('/frontend/run/saveEmergency');
$saveShirtSizeUrl = $this->createUrl('/frontend/run/saveShirtSize');
$sizeGuideImg = Yii::app()->theme->baseUrl . '/assets/images/size_ao_fun_run.jpg';

// Đã đăng ký chạy nhưng CHƯA chọn size áo -> bắt buộc chọn (tự bật popup, không cho bỏ qua).
$needsShirtSize = !empty($runMine) && empty($runMine['shirt_size']);

// Cổng đã có thông tin khẩn cấp hay chưa (để JS quyết định tự bật popup sau khi đăng ký).
$hasEmergencyInfo = !empty($runMine) && (
    !empty($runMine['emergency_contact_name'])
    || !empty($runMine['emergency_contact_phone'])
    || !empty($runMine['medical_conditions'])
    || !empty($runMine['medications'])
);

// Dữ liệu thông báo hủy-đã-duyệt cho JS (báo 1 lần qua localStorage).
$runNoticeAt = (!empty($runCancelledNotice) && !empty($runCancelledNotice['cancel_reviewed_at'])) ? (int) $runCancelledNotice['cancel_reviewed_at'] : 0;
$runNoticeName = (!empty($runCancelledNotice) && !empty($runCancelledNotice['run_event_name'])) ? $runCancelledNotice['run_event_name'] : '';
$tourNoticeAt = (!empty($tourCancelledNotice) && !empty($tourCancelledNotice['cancel_reviewed_at'])) ? (int) $tourCancelledNotice['cancel_reviewed_at'] : 0;
$tourNoticeName = (!empty($tourCancelledNotice) && !empty($tourCancelledNotice['tour_session_name'])) ? $tourCancelledNotice['tour_session_name'] : '';

// Yêu cầu hủy bị TỪ CHỐI
$runRejectAt = (!empty($runMine) && isset($runMine['status']) && $runMine['status'] === 'active' && !empty($runMine['cancel_reviewed_at']) && !empty($runMine['cancel_reason'])) ? (int) $runMine['cancel_reviewed_at'] : 0;
$tourRejectAt = (!empty($tourMine) && isset($tourMine['status']) && $tourMine['status'] === 'active' && !empty($tourMine['cancel_reviewed_at']) && !empty($tourMine['cancel_reason'])) ? (int) $tourMine['cancel_reviewed_at'] : 0;

$now = time();

// Thời hạn đăng ký Fun Run: từ mốc mở sớm nhất tới mốc đóng muộn nhất của các cự ly.
// Khi người dùng đã đăng ký (không còn danh sách cự ly) thì dùng câu mặc định do BTC quy định.
$runEventsAll = isset($runEventsAll) ? $runEventsAll : $runEvents;
$tourSessionsAll = isset($tourSessionsAll) ? $tourSessionsAll : $tourSessions;

$regOpenAts = array();
$regCloseAts = array();
if (!empty($runEventsAll)) {
    foreach ($runEventsAll as $e) {
        $o = !empty($e['open_at']) ? (is_numeric($e['open_at']) ? (int)$e['open_at'] : strtotime($e['open_at'])) : 0;
        $c = !empty($e['close_at']) ? (is_numeric($e['close_at']) ? (int)$e['close_at'] : strtotime($e['close_at'])) : 0;
        if ($o > 0) { $regOpenAts[] = $o; }
        if ($c > 0) { $regCloseAts[] = $c; }
    }
}
if (!empty($regOpenAts) && !empty($regCloseAts)) {
    $registrationWindowText = 'Thời hạn đăng ký: từ ' . date('H\hi', min($regOpenAts)) . ' ngày ' . date('d/m/Y', min($regOpenAts))
        . ' tới ' . date('H\hi', max($regCloseAts)) . ' ngày ' . date('d/m/Y', max($regCloseAts))
        . ' (hoặc có thể kết thúc sớm hơn khi đủ chỉ tiêu).';
} else {
    $registrationWindowText = 'Thời hạn đăng ký: từ 10h00 ngày 10/10/2026 tới 23h59 ngày 13/10/2026 (hoặc có thể kết thúc sớm hơn khi đủ chỉ tiêu).';
}

/**
 * Tính trạng thái khung thời gian đăng ký cho 1 nhóm nội dung (cự ly / đợt tham quan).
 * Trả về: state ('before' | 'open' | 'closed'), target (timestamp đếm ngược), label.
 *  - before: chưa tới giờ mở  -> đếm ngược tới giờ MỞ sớm nhất.
 *  - open:   đang trong hạn    -> đếm ngược tới giờ ĐÓNG gần nhất.
 *  - closed: đã hết hạn tất cả -> không đếm ngược, ẩn form đăng ký.
 */
$computeWindow = function ($rows) use ($now) {
    $futureOpen = array();
    $openClose = array();
    foreach ((array) $rows as $r) {
        $o = !empty($r['open_at']) ? (is_numeric($r['open_at']) ? (int) $r['open_at'] : strtotime($r['open_at'])) : 0;
        $c = !empty($r['close_at']) ? (is_numeric($r['close_at']) ? (int) $r['close_at'] : strtotime($r['close_at'])) : 0;
        if ($o > $now) {
            $futureOpen[] = $o;
        } elseif ($c > $now) {
            $openClose[] = $c;
        } elseif ($c <= 0 && $o > 0 && $o <= $now) {
            // Đã mở, không có mốc đóng -> coi như mở vô thời hạn.
            $openClose[] = PHP_INT_MAX;
        }
    }
    if (!empty($openClose)) {
        return array('state' => 'open', 'target' => min($openClose), 'label' => 'Thời gian đăng ký còn lại');
    }
    if (!empty($futureOpen)) {
        return array('state' => 'before', 'target' => min($futureOpen), 'label' => 'Mở đăng ký sau');
    }
    return array('state' => 'closed', 'target' => 0, 'label' => 'Đã đóng đăng ký');
};

$runWindow = $computeWindow($runEventsAll);
$tourWindow = $computeWindow($tourSessionsAll);

// Thời hạn đăng ký tham quan: từ mốc mở sớm nhất tới mốc đóng muộn nhất của các đợt.
$tourOpenAts = array();
$tourCloseAts = array();
if (!empty($tourSessionsAll)) {
    foreach ($tourSessionsAll as $s) {
        $o = !empty($s['open_at']) ? (is_numeric($s['open_at']) ? (int)$s['open_at'] : strtotime($s['open_at'])) : 0;
        $c = !empty($s['close_at']) ? (is_numeric($s['close_at']) ? (int)$s['close_at'] : strtotime($s['close_at'])) : 0;
        if ($o > 0) { $tourOpenAts[] = $o; }
        if ($c > 0) { $tourCloseAts[] = $c; }
    }
}
if (!empty($tourOpenAts) && !empty($tourCloseAts)) {
    $tourRegistrationWindowText = 'Thời hạn đăng ký: từ ' . date('H\hi', min($tourOpenAts)) . ' ngày ' . date('d/m/Y', min($tourOpenAts))
        . ' tới ' . date('H\hi', max($tourCloseAts)) . ' ngày ' . date('d/m/Y', max($tourCloseAts))
        . ' (hoặc có thể kết thúc sớm hơn khi đủ chỉ tiêu).';
} else {
    $tourRegistrationWindowText = 'Thời hạn đăng ký: từ 10h00 ngày 10/10/2026 tới 23h59 ngày 13/10/2026 (hoặc có thể kết thúc sớm hơn khi đủ chỉ tiêu).';
}
?>

<!-- Header & Thông tin đại biểu -->
<div class="run-portal-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
    <div class="d-flex align-items-center gap-3">
        <div class="user-avatar-circle">
            <i class="bi bi-person-fill"></i>
        </div>
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h4 class="mb-0 fw-bold text-dark">Xin chào, <?php echo CHtml::encode($fullName); ?></h4>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 small">
                    <i class="bi bi-shield-check me-1"></i>Đại biểu VCK
                </span>
            </div>
            <div class="text-muted small mt-1">
                Cổng đăng ký hoạt động tự phục vụ • Vòng Chung Kết Đại hội
            </div>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap justify-content-md-end">
        <!-- Quick status summary badges -->
        <?php if (!empty($runMine)): ?>
            <span class="portal-status-badge bg-primary-subtle text-primary border border-primary-subtle" title="Đã đăng ký chạy">
                <i class="bi bi-check-circle-fill"></i> Fun Run: <?php echo CHtml::encode(!empty($runMine['bib_number']) ? 'BIB ' . $runMine['bib_number'] : 'Đã đăng ký'); ?>
            </span>
        <?php else: ?>
            <span class="portal-status-badge bg-light text-secondary border" title="Chưa đăng ký chạy">
                <i class="bi bi-circle"></i> Fun Run: Chưa chọn
            </span>
        <?php endif; ?>

        <?php if (!empty($tourMine)): ?>
            <span class="portal-status-badge bg-success-subtle text-success border border-success-subtle" title="Đã đăng ký tour">
                <i class="bi bi-check-circle-fill"></i> Tham quan: Đã chọn
            </span>
        <?php else: ?>
            <span class="portal-status-badge bg-light text-secondary border" title="Chưa đăng ký tour">
                <i class="bi bi-circle"></i> Tham quan: Chưa chọn
            </span>
        <?php endif; ?>

        <a href="<?php echo $this->createUrl('/frontend/portal/index'); ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3 ms-md-2" title="Về trang chủ cá nhân">
            <i class="bi bi-grid-fill me-1"></i> Trang chủ
        </a>
        <a href="<?php echo $this->createUrl('/frontend/portal/logout'); ?>" class="btn btn-outline-danger btn-sm rounded-pill px-3" title="Đăng xuất khỏi cổng">
            <i class="bi bi-box-arrow-right me-1"></i> Thoát
        </a>
    </div>
</div>

<!-- Config element cho JS AJAX & Thông báo -->
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
     data-tour-reject-reviewed-at="<?php echo $tourRejectAt; ?>"
     data-save-emergency-url="<?php echo $saveEmergencyUrl; ?>"
     data-save-shirt-size-url="<?php echo $saveShirtSizeUrl; ?>"
     data-has-run="<?php echo !empty($runMine) ? '1' : '0'; ?>"
     data-has-emergency="<?php echo $hasEmergencyInfo ? '1' : '0'; ?>"
     data-needs-shirt-size="<?php echo $needsShirtSize ? '1' : '0'; ?>"></div>

<!-- Hero Banner: Hướng dẫn cơ chế đăng ký (đồng hồ đếm ngược chuyển vào từng card) -->
<div class="run-countdown-banner">
    <div>
        <span class="countdown-live-pill">
            <span class="pulse-circle"></span> Cổng đăng ký hoạt động
        </span>
        <h3 class="fw-bold text-white mb-2">Giữ suất Giải chạy &amp; Tham quan</h3>
        <p class="text-white-50 small mb-3" style="line-height: 1.6;">
            Hệ thống áp dụng cơ chế <strong>FCFS (First-Come, First-Served)</strong> — ưu tiên người đăng ký trước.
            Mỗi đại biểu được chọn <strong>1 cự ly chạy</strong> và <strong>1 đợt tham quan</strong>.
            Mỗi nội dung có <strong>khung thời gian đăng ký riêng</strong> — xem đồng hồ đếm ngược ngay trên từng thẻ bên dưới.
        </p>

        <div class="d-flex flex-wrap gap-2">
            <div class="portal-rule-item">
                <i class="bi bi-lightning-charge-fill text-warning"></i> Giành suất trực tuyến
            </div>
            <div class="portal-rule-item">
                <i class="bi bi-ticket-perforated-fill text-info"></i> Tự động cấp số BIB
            </div>
        </div>
    </div>
</div>

<!-- Khối 2 nội dung chính: Chạy bộ & Đi tham quan -->
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <?php $this->renderPartial('_section_run', array(
            'runMine'   => $runMine,
            'runEvents' => $runEvents,
            'fullName'  => $fullName,
            'registrationWindowText' => $registrationWindowText,
            'window'    => $runWindow,
        )); ?>
    </div>
    <div class="col-lg-6">
        <?php $this->renderPartial('_section_tour', array(
            'tourMine'     => $tourMine,
            'tourSessions' => $tourSessions,
            'fullName'     => $fullName,
            'registrationWindowText' => $tourRegistrationWindowText,
            'window'       => $tourWindow,
        )); ?>
    </div>
</div>

<?php
// Modal xin hủy cho từng nội dung (chỉ render khi có đăng ký còn cho hủy).
if (!empty($runMine) && !empty($runMine['can_request_cancel']) && $runMine['status'] !== 'cancel_requested') {
    $this->renderPartial('_modal_cancel', array(
        'modalId'   => 'modalCancelRun',
        'formId'    => 'form-cancel-run',
        'btnId'     => 'btn-submit-cancel-run',
        'cancelUrl' => $cancelRunUrl,
        'title'     => 'Xin hủy đăng ký cự ly chạy',
    ));
}
if (!empty($tourMine) && !empty($tourMine['can_request_cancel']) && $tourMine['status'] !== 'cancel_requested') {
    $this->renderPartial('_modal_cancel', array(
        'modalId'   => 'modalCancelTour',
        'formId'    => 'form-cancel-tour',
        'btnId'     => 'btn-submit-cancel-tour',
        'cancelUrl' => $cancelTourUrl,
        'title'     => 'Xin hủy đăng ký tham quan',
    ));
}

// Size áo Fun Run (bắt buộc) — chỉ có khi đã có đăng ký chạy đang hiệu lực.
if (!empty($runMine)) {
    $this->renderPartial('_modal_shirt_size', array(
        'runMine'   => $runMine,
        'saveUrl'   => $saveShirtSizeUrl,
        'guideImg'  => $sizeGuideImg,
        'mandatory' => $needsShirtSize,
    ));
}

// Thông tin khẩn cấp (tùy chọn) — chỉ có khi đã có đăng ký chạy đang hiệu lực.
if (!empty($runMine)) {
    $this->renderPartial('_modal_emergency', array(
        'runMine' => $runMine,
        'saveUrl' => $saveEmergencyUrl,
    ));
}

// Popup xác nhận hồ sơ (chỉ có khi chưa xác nhận trong phiên đăng nhập này).
if (!empty($profileModal)) {
    $this->renderPartial('/portal/_modal_profile', $profileModal);
}

// Popup xem lịch trình tham quan chi tiết (file PDF).
$this->renderPartial('_modal_tour_schedule', array(
    'pdfUrl' => Yii::app()->theme->baseUrl . '/assets/docs/lich_trinh_tour.pdf',
));
?>
