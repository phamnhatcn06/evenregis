<?php
/**
 * Timeline gộp theo ngày + mục "Chưa có lịch cụ thể".
 * Thiết kế giao diện hiện đại đồng bộ với Agenda và hiển thị icon danh mục màu sắc trực quan.
 *
 * @var array $days   danh sách nhóm ngày: { date, dateLabel, items[] }
 * @var array $noTime item chưa có giờ
 */
$isEmpty = empty($days) && empty($noTime);
$tz = new DateTimeZone(Schedule::TZ);
?>

<?php
/**
 * Render một dòng timeline theo kiểu agenda: cột giờ | chấm (icon tròn) | thẻ nội dung.
 */
$renderItem = function ($item, $forcedStatus = null) use ($tz) {
    $meta = Schedule::getItemMeta($item);
    $status = $forcedStatus !== null ? $forcedStatus : (int) $item['status'];

    $startTimeStr = '';
    $endTimeStr = '';

    if (!empty($item['startTime']) && is_numeric($item['startTime']) && (int) $item['startTime'] > 86400) {
        $dt = new DateTime('@' . (int) $item['startTime']);
        $dt->setTimezone($tz);
        $startTimeStr = $dt->format('H:i');
    } elseif (!empty($item['startLabel'])) {
        if (preg_match('/(\d{1,2}:\d{2})/', (string) $item['startLabel'], $m)) {
            $startTimeStr = $m[1];
        } else {
            $startTimeStr = (string) $item['startLabel'];
        }
    }

    if (!empty($item['endTime']) && is_numeric($item['endTime']) && (int) $item['endTime'] > 86400) {
        $dtEnd = new DateTime('@' . (int) $item['endTime']);
        $dtEnd->setTimezone($tz);
        $endTimeStr = $dtEnd->format('H:i');
    } elseif (!empty($item['endLabel'])) {
        if (preg_match('/(\d{1,2}:\d{2})/', (string) $item['endLabel'], $m)) {
            $endTimeStr = $m[1];
        }
    }

    $desc = !empty($item['subtitle']) ? $item['subtitle'] : (!empty($item['description']) ? $item['description'] : '');
    ?>
    <div class="sch-item status-<?php echo $status; ?> type-<?php echo CHtml::encode($item['type']); ?> cat-<?php echo CHtml::encode($meta['key']); ?>">
        <div class="sch-time">
            <span class="sch-time-start"><?php echo CHtml::encode($startTimeStr !== '' ? $startTimeStr : '—'); ?></span>
            <?php if ($endTimeStr !== ''): ?>
                <span class="sch-time-end">— <?php echo CHtml::encode($endTimeStr); ?></span>
            <?php endif; ?>
        </div>
        <div class="sch-dot">
            <div class="sch-dot-circle" style="background-color: <?php echo $meta['color']; ?>;">
                <?php echo Schedule::renderIcon($meta['icon_type']); ?>
            </div>
        </div>
        <div class="sch-card" style="border-left-color: <?php echo $meta['color']; ?>;">
            <div class="sch-card-top">
                <h3 class="sch-card-title"><?php echo CHtml::encode($item['title']); ?></h3>
                <span class="sch-pill" style="background-color: <?php echo $meta['bg_light']; ?>; color: <?php echo $meta['text_color']; ?>;">
                    <?php echo CHtml::encode($meta['label']); ?>
                </span>
            </div>
            <?php if (!empty($item['location'])): ?>
                <div class="sch-card-loc">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    <span><?php echo CHtml::encode($item['location']); ?></span>
                </div>
            <?php endif; ?>
            <?php if (!empty($desc)): ?>
                <div class="sch-card-desc"><?php echo CHtml::encode($desc); ?></div>
            <?php endif; ?>
            <?php if ($status === Schedule::STATUS_ONGOING): ?>
                <div class="sch-card-live">
                    <span class="sch-live-dot"></span> Đang diễn ra
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php };
?>

<?php if ($isEmpty): ?>
    <div class="schedule-empty">
        <i class="bi bi-calendar-x"></i>
        <div class="schedule-empty-title">Chưa có lịch trình</div>
        <div class="schedule-empty-sub">Lịch trình của anh/chị sẽ hiển thị tại đây khi Ban Tổ chức cập nhật.</div>
    </div>
<?php else: ?>

    <?php foreach ($days as $i => $day): ?>
        <div class="sch-day">
            <div class="sch-day-head">
                <div class="sch-day-badge">
                    <span class="sch-day-badge-lbl">NGÀY</span>
                    <span class="sch-day-badge-num"><?php echo ($i + 1); ?></span>
                </div>
                <div class="sch-day-info">
                    <h2 class="sch-day-title"><?php echo CHtml::encode($day['dateLabel']); ?></h2>
                    <span class="sch-day-count"><?php echo count($day['items']); ?> hoạt động</span>
                </div>
            </div>
            <div class="sch-timeline">
                <?php foreach ($day['items'] as $item) {
                    $renderItem($item);
                } ?>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (!empty($noTime)): ?>
        <div class="sch-day sch-day-notime">
            <div class="sch-day-head">
                <div class="sch-day-badge">
                    <span class="sch-day-badge-lbl">LỊCH</span>
                    <span class="sch-day-badge-num"><i class="bi bi-question-lg"></i></span>
                </div>
                <div class="sch-day-info">
                    <h2 class="sch-day-title">Chưa có lịch cụ thể</h2>
                    <span class="sch-day-count"><?php echo count($noTime); ?> hoạt động</span>
                </div>
            </div>
            <div class="sch-timeline">
                <?php foreach ($noTime as $item) {
                    $renderItem($item, Schedule::STATUS_NO_TIME);
                } ?>
            </div>
        </div>
    <?php endif; ?>

<?php endif; ?>
