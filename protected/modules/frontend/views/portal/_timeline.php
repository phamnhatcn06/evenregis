<?php
/**
 * Timeline gộp theo ngày + mục "Chưa có lịch cụ thể".
 *
 * @var array $days   danh sách nhóm ngày: { date, dateLabel, items[] }
 * @var array $noTime item chưa có giờ
 */
$typeIcons = array(
    'sport' => 'bi-trophy-fill',
    'competition' => 'bi-mortarboard-fill',
    'agenda' => 'bi-flag-fill',
    'meal' => 'bi-cup-hot-fill',
    'banquet' => 'bi-balloon-heart-fill',
);
$typeLabels = array(
    'sport' => 'Thể thao',
    'competition' => 'Thi nghiệp vụ',
    'agenda' => 'Nghi thức',
    'meal' => 'Bữa ăn',
    'banquet' => 'Tiệc',
);

$isEmpty = empty($days) && empty($noTime);
?>

<?php
/**
 * Render một dòng timeline theo kiểu agenda: cột giờ | chấm (icon) | thẻ nội dung.
 */
$renderItem = function ($item, $forcedStatus = null) use ($typeIcons, $typeLabels) {
    $icon = isset($typeIcons[$item['type']]) ? $typeIcons[$item['type']] : 'bi-calendar-event';
    $typeLabel = isset($typeLabels[$item['type']]) ? $typeLabels[$item['type']] : $item['type'];
    $status = $forcedStatus !== null ? $forcedStatus : (int) $item['status'];
    $statusLabel = $forcedStatus !== null
        ? Schedule::getStatusLabel(Schedule::STATUS_NO_TIME)
        : Schedule::getStatusLabel($item['status']);
    $time = isset($item['startLabel']) ? trim((string) $item['startLabel']) : '';
    ?>
    <div class="sch-item status-<?php echo $status; ?> type-<?php echo CHtml::encode($item['type']); ?>">
        <div class="sch-time">
            <span class="sch-time-start"><?php echo CHtml::encode($time !== '' ? $time : '—'); ?></span>
        </div>
        <div class="sch-dot">
            <i class="bi <?php echo $icon; ?>"></i>
        </div>
        <div class="sch-card">
            <div class="sch-card-top">
                <strong class="sch-card-title"><?php echo CHtml::encode($item['title']); ?></strong>
                <span class="sch-tag type-<?php echo CHtml::encode($item['type']); ?>"><?php echo CHtml::encode($typeLabel); ?></span>
            </div>
            <div class="sch-card-status"><?php echo $statusLabel; ?></div>
            <?php if (!empty($item['subtitle'])): ?>
                <div class="sch-card-sub"><?php echo CHtml::encode($item['subtitle']); ?></div>
            <?php endif; ?>
            <?php if (!empty($item['location'])): ?>
                <div class="sch-place"><i class="bi bi-geo-alt"></i> <?php echo CHtml::encode($item['location']); ?></div>
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
                <div class="sch-day-badge"><i class="bi bi-calendar3"></i></div>
                <div>
                    <h2 class="fw-bold mb-0"><?php echo CHtml::encode($day['dateLabel']); ?></h2>
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
                <div class="sch-day-badge"><i class="bi bi-question-circle"></i></div>
                <div>
                    <h2 class="fw-bold mb-0">Chưa có lịch cụ thể</h2>
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
