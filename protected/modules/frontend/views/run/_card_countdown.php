<?php
/**
 * Đồng hồ đếm ngược nhỏ gọn hiển thị trên header của từng card nội dung.
 * Tham số: $window = array('state' => 'before'|'open'|'closed', 'target' => int, 'label' => string)
 *  - before: đếm ngược tới giờ mở đăng ký.
 *  - open:   đếm ngược tới giờ đóng (thời gian đăng ký còn lại).
 *  - closed: không đếm ngược, chỉ hiển thị nhãn đã đóng.
 */
$state = isset($window['state']) ? $window['state'] : 'open';
$target = isset($window['target']) ? (int) $window['target'] : 0;
$label = isset($window['label']) ? $window['label'] : '';

if ($state === 'closed' || $target <= 0 || $target >= PHP_INT_MAX) {
    if ($state === 'closed') {
        echo '<span class="card-countdown card-countdown-closed"><i class="bi bi-lock-fill me-1"></i>' . CHtml::encode($label) . '</span>';
    }
    return;
}
?>
<div class="card-countdown card-countdown-<?php echo CHtml::encode($state); ?>" data-target="<?php echo $target; ?>" data-type="<?php echo $state === 'before' ? 'open' : 'close'; ?>">
    <span class="cc-label"><i class="bi bi-stopwatch me-1"></i><?php echo CHtml::encode($label); ?></span>
    <span class="cc-clock">
        <span class="cc-unit"><b class="cc-d">00</b><i>ngày</i></span>
        <span class="cc-sep">:</span>
        <span class="cc-unit"><b class="cc-h">00</b><i>giờ</i></span>
        <span class="cc-sep">:</span>
        <span class="cc-unit"><b class="cc-m">00</b><i>phút</i></span>
        <span class="cc-sep">:</span>
        <span class="cc-unit"><b class="cc-s">00</b><i>giây</i></span>
    </span>
</div>
