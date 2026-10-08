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

// =========================================================================
// Tính toán mốc đếm ngược (Countdown) thông minh
// =========================================================================
$now = time();
$countdownTarget = 0;
$countdownType = 'close';
$countdownBadgeText = 'Đang mở đăng ký';
$countdownTitle = 'Thời gian đăng ký còn lại';

// Mốc mở (chưa tới giờ mở) và mốc đóng của các nội dung ĐANG mở.
// Quy ước: chưa mở -> đếm ngược tới giờ MỞ; đã mở -> đếm ngược tới giờ ĐÓNG.
$futureOpenAts = array();   // open_at còn ở tương lai (chưa mở)
$openCloseAts = array();    // close_at của nội dung đang mở (đã qua open_at, chưa tới close_at)

$collectWindow = function ($row) use ($now, &$futureOpenAts, &$openCloseAts) {
    $oAt = !empty($row['open_at']) ? (is_numeric($row['open_at']) ? (int)$row['open_at'] : strtotime($row['open_at'])) : 0;
    $cAt = !empty($row['close_at']) ? (is_numeric($row['close_at']) ? (int)$row['close_at'] : strtotime($row['close_at'])) : 0;

    if ($oAt > $now) {
        // Chưa đến giờ mở.
        $futureOpenAts[] = $oAt;
    } elseif ($cAt > $now) {
        // Đã mở và còn trong hạn.
        $openCloseAts[] = $cAt;
    }
};

if (!empty($runEvents)) {
    foreach ($runEvents as $e) { $collectWindow($e); }
}
if (!empty($tourSessions)) {
    foreach ($tourSessions as $s) { $collectWindow($s); }
}

if (!empty($openCloseAts)) {
    // Có nội dung đang mở -> đếm ngược tới thời điểm đóng gần nhất.
    $countdownTarget = min($openCloseAts);
    $countdownType = 'close';
    $countdownBadgeText = 'Đang mở đăng ký';
    $countdownTitle = 'Thời gian đăng ký còn lại';
} elseif (!empty($futureOpenAts)) {
    // Chưa mở -> đếm ngược tới thời điểm mở gần nhất.
    $countdownTarget = min($futureOpenAts);
    $countdownType = 'open';
    $countdownBadgeText = 'Sắp mở đăng ký';
    $countdownTitle = 'Cổng đăng ký sẽ mở sau';
} elseif (!empty($runMine) && !empty($runMine['cancel_until']) && (int)$runMine['cancel_until'] > $now) {
    $countdownTarget = (int)$runMine['cancel_until'];
    $countdownType = 'cancel';
    $countdownBadgeText = 'Thời hạn hủy';
    $countdownTitle = 'Hạn chót xin hủy đăng ký';
} elseif (!empty($tourMine) && !empty($tourMine['cancel_until']) && (int)$tourMine['cancel_until'] > $now) {
    $countdownTarget = (int)$tourMine['cancel_until'];
    $countdownType = 'cancel';
    $countdownBadgeText = 'Thời hạn hủy';
    $countdownTitle = 'Hạn chót xin hủy đăng ký';
}

// Nếu chưa có target từ danh mục, kiểm tra eventInfo
if ($countdownTarget <= 0 && !empty($eventInfo)) {
    if (is_array($eventInfo)) {
        if (!empty($eventInfo['countdown_seconds']) && (int)$eventInfo['countdown_seconds'] > 0) {
            $countdownTarget = $now + (int)$eventInfo['countdown_seconds'];
            $countdownType = 'event';
            $countdownBadgeText = 'Đếm ngược Đại hội';
            $countdownTitle = 'Thời gian đến ngày khai mạc';
        } elseif (!empty($eventInfo['from_date']) && strtotime($eventInfo['from_date']) > $now) {
            $countdownTarget = strtotime($eventInfo['from_date']);
            $countdownType = 'event';
            $countdownBadgeText = 'Đếm ngược Đại hội';
            $countdownTitle = 'Thời gian đến ngày diễn ra';
        }
    } elseif (is_object($eventInfo)) {
        if (!empty($eventInfo->from_date) && strtotime($eventInfo->from_date) > $now) {
            $countdownTarget = strtotime($eventInfo->from_date);
            $countdownType = 'event';
            $countdownBadgeText = 'Đếm ngược Đại hội';
            $countdownTitle = 'Thời gian đến ngày diễn ra';
        }
    }
}

// Fallback an toàn: nếu chưa có mốc cụ thể trong DB, đặt mốc kết thúc sau 3 ngày
// để đồng hồ đếm ngược luôn có số hiển thị thực tế và chạy realtime
if ($countdownTarget <= 0) {
    $countdownTarget = strtotime('+3 days 23:59:59');
    $countdownType = 'close';
    $countdownBadgeText = 'Cổng đang mở';
    $countdownTitle = 'Thời gian đăng ký còn lại';
}

$countdownDeadlineDate = date('H:i d/m/Y', $countdownTarget);

// Thời hạn đăng ký Fun Run: từ mốc mở sớm nhất tới mốc đóng muộn nhất của các cự ly.
// Khi người dùng đã đăng ký (không còn danh sách cự ly) thì dùng câu mặc định do BTC quy định.
$regOpenAts = array();
$regCloseAts = array();
if (!empty($runEvents)) {
    foreach ($runEvents as $e) {
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
     data-has-run="<?php echo !empty($runMine) ? '1' : '0'; ?>"
     data-has-emergency="<?php echo $hasEmergencyInfo ? '1' : '0'; ?>"></div>

<!-- Hero Banner: Countdown thời gian đăng ký & Hướng dẫn -->
<div class="run-countdown-banner">
    <div class="row align-items-center g-4">
        <div class="col-lg-7">
            <div>
                <span class="countdown-live-pill">
                    <span class="pulse-circle"></span> <?php echo $countdownBadgeText; ?>
                </span>
                <h3 class="fw-bold text-white mb-2"><?php echo $countdownTitle; ?></h3>
                <p class="text-white-50 small mb-3" style="line-height: 1.6;">
                    Hệ thống áp dụng cơ chế <strong>FCFS (First-Come, First-Served)</strong> — ưu tiên người đăng ký trước.
                    Mỗi đại biểu được chọn <strong>1 cự ly chạy</strong> và <strong>1 đợt tham quan</strong>.
                </p>

                <div class="d-flex flex-wrap gap-2">
                    <div class="portal-rule-item">
                        <i class="bi bi-lightning-charge-fill text-warning"></i> Giành suất trực tuyến
                    </div>
                    <div class="portal-rule-item">
                        <i class="bi bi-ticket-perforated-fill text-info"></i> Tự động cấp số BIB
                    </div>
                    <div class="portal-rule-item">
                        <i class="bi bi-calendar-event text-success"></i> <?php echo CHtml::encode($registrationWindowText); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5 text-center text-lg-end">
            <!-- Khung đồng hồ đếm ngược -->
            <div class="countdown-clock-container" id="run-portal-countdown" data-target="<?php echo $countdownTarget; ?>" data-type="<?php echo $countdownType; ?>">
                <div class="countdown-unit-box">
                    <div class="countdown-digit" id="cd-days">00</div>
                    <div class="countdown-unit-label">Ngày</div>
                </div>
                <div class="countdown-colon">:</div>
                <div class="countdown-unit-box">
                    <div class="countdown-digit" id="cd-hours">00</div>
                    <div class="countdown-unit-label">Giờ</div>
                </div>
                <div class="countdown-colon">:</div>
                <div class="countdown-unit-box">
                    <div class="countdown-digit" id="cd-mins">00</div>
                    <div class="countdown-unit-label">Phút</div>
                </div>
                <div class="countdown-colon">:</div>
                <div class="countdown-unit-box">
                    <div class="countdown-digit text-warning" id="cd-secs">00</div>
                    <div class="countdown-unit-label">Giây</div>
                </div>
            </div>

            <div class="text-white-50 small mt-2">
                <i class="bi bi-clock-history me-1 text-warning"></i> Cổng tự động khóa khi hết thời gian
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
        )); ?>
    </div>
    <div class="col-lg-6">
        <?php $this->renderPartial('_section_tour', array(
            'tourMine'     => $tourMine,
            'tourSessions' => $tourSessions,
            'fullName'     => $fullName,
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

// Popup xác nhận hồ sơ (chỉ có khi chưa xác nhận trong phiên đăng nhập này).
if (!empty($profileModal)) {
    $this->renderPartial('/portal/_modal_profile', $profileModal);
}
?>
