<?php
/**
 * Thẻ "Sự kiện tiếp theo" + đồng hồ đếm ngược (realtime qua schedule-my.js).
 *
 * @var array $nextUp   các item gần nhất ở tương lai (có thể nhiều item cùng mốc)
 * @var int   $serverTime mốc thời gian máy chủ (Unix) để JS đồng bộ đếm ngược
 */
$hasNext = !empty($nextUp);
$targetTime = $hasNext ? (int) $nextUp[0]['startTime'] : 0;

$typeIcons = array(
    'sport' => 'bi-trophy-fill',
    'competition' => 'bi-mortarboard-fill',
    'agenda' => 'bi-flag-fill',
    'meal' => 'bi-cup-hot-fill',
    'banquet' => 'bi-balloon-heart-fill',
);
?>
<div class="next-up-card <?php echo $hasNext ? '' : 'next-up-empty'; ?>"
     data-server-time="<?php echo (int) $serverTime; ?>"
     data-target-time="<?php echo $targetTime; ?>"
     id="nextUpCard">
    <?php if ($hasNext): ?>
        <div class="next-up-head">
            <span class="next-up-eyebrow"><i class="bi bi-hourglass-split"></i> Sự kiện tiếp theo</span>
            <div class="next-up-countdown" id="nextUpCountdown" aria-live="polite">
                <span class="cd-box"><span class="cd-num" data-cd="d">00</span><span class="cd-lbl">ngày</span></span>
                <span class="cd-box"><span class="cd-num" data-cd="h">00</span><span class="cd-lbl">giờ</span></span>
                <span class="cd-box"><span class="cd-num" data-cd="m">00</span><span class="cd-lbl">phút</span></span>
                <span class="cd-box"><span class="cd-num" data-cd="s">00</span><span class="cd-lbl">giây</span></span>
            </div>
        </div>
        <div class="next-up-body">
            <?php foreach ($nextUp as $item): ?>
                <?php $icon = isset($typeIcons[$item['type']]) ? $typeIcons[$item['type']] : 'bi-calendar-event'; ?>
                <div class="next-up-item">
                    <div class="next-up-icon type-<?php echo CHtml::encode($item['type']); ?>">
                        <i class="bi <?php echo $icon; ?>"></i>
                    </div>
                    <div class="next-up-info">
                        <div class="next-up-item-title"><?php echo CHtml::encode($item['title']); ?></div>
                        <?php if (!empty($item['subtitle'])): ?>
                            <div class="next-up-item-sub"><?php echo CHtml::encode($item['subtitle']); ?></div>
                        <?php endif; ?>
                        <div class="next-up-meta">
                            <span><i class="bi bi-clock"></i> <?php echo CHtml::encode($item['startLabel']); ?></span>
                            <?php if (!empty($item['location'])): ?>
                                <span><i class="bi bi-geo-alt"></i> <?php echo CHtml::encode($item['location']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="next-up-done">
            <i class="bi bi-check2-circle"></i>
            <div>
                <div class="next-up-done-title">Đã hoàn thành lịch trình</div>
                <div class="next-up-done-sub">Hiện không còn sự kiện nào sắp diễn ra.</div>
            </div>
        </div>
    <?php endif; ?>
</div>
