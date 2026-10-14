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

<?php if ($isEmpty): ?>
    <div class="schedule-empty">
        <i class="bi bi-calendar-x"></i>
        <div class="schedule-empty-title">Chưa có lịch trình</div>
        <div class="schedule-empty-sub">Lịch trình của anh/chị sẽ hiển thị tại đây khi Ban Tổ chức cập nhật.</div>
    </div>
<?php else: ?>

    <?php foreach ($days as $day): ?>
        <div class="timeline-day">
            <div class="timeline-day-header">
                <i class="bi bi-calendar3"></i>
                <?php echo CHtml::encode($day['dateLabel']); ?>
            </div>
            <div class="timeline-items">
                <?php foreach ($day['items'] as $item): ?>
                    <?php
                    $icon = isset($typeIcons[$item['type']]) ? $typeIcons[$item['type']] : 'bi-calendar-event';
                    $typeLabel = isset($typeLabels[$item['type']]) ? $typeLabels[$item['type']] : $item['type'];
                    ?>
                    <div class="timeline-item status-<?php echo (int) $item['status']; ?>">
                        <div class="timeline-item-icon type-<?php echo CHtml::encode($item['type']); ?>">
                            <i class="bi <?php echo $icon; ?>"></i>
                        </div>
                        <div class="timeline-item-content">
                            <div class="timeline-item-top">
                                <span class="timeline-type-badge type-<?php echo CHtml::encode($item['type']); ?>"><?php echo CHtml::encode($typeLabel); ?></span>
                                <?php echo Schedule::getStatusLabel($item['status']); ?>
                            </div>
                            <div class="timeline-item-title"><?php echo CHtml::encode($item['title']); ?></div>
                            <?php if (!empty($item['subtitle'])): ?>
                                <div class="timeline-item-sub"><?php echo CHtml::encode($item['subtitle']); ?></div>
                            <?php endif; ?>
                            <div class="timeline-item-meta">
                                <span><i class="bi bi-clock"></i> <?php echo CHtml::encode($item['startLabel']); ?></span>
                                <?php if (!empty($item['location'])): ?>
                                    <span><i class="bi bi-geo-alt"></i> <?php echo CHtml::encode($item['location']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (!empty($noTime)): ?>
        <div class="timeline-day timeline-notime">
            <div class="timeline-day-header">
                <i class="bi bi-question-circle"></i>
                Chưa có lịch cụ thể
            </div>
            <div class="timeline-items">
                <?php foreach ($noTime as $item): ?>
                    <?php
                    $icon = isset($typeIcons[$item['type']]) ? $typeIcons[$item['type']] : 'bi-calendar-event';
                    $typeLabel = isset($typeLabels[$item['type']]) ? $typeLabels[$item['type']] : $item['type'];
                    ?>
                    <div class="timeline-item status-3">
                        <div class="timeline-item-icon type-<?php echo CHtml::encode($item['type']); ?>">
                            <i class="bi <?php echo $icon; ?>"></i>
                        </div>
                        <div class="timeline-item-content">
                            <div class="timeline-item-top">
                                <span class="timeline-type-badge type-<?php echo CHtml::encode($item['type']); ?>"><?php echo CHtml::encode($typeLabel); ?></span>
                                <?php echo Schedule::getStatusLabel(Schedule::STATUS_NO_TIME); ?>
                            </div>
                            <div class="timeline-item-title"><?php echo CHtml::encode($item['title']); ?></div>
                            <?php if (!empty($item['subtitle'])): ?>
                                <div class="timeline-item-sub"><?php echo CHtml::encode($item['subtitle']); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($item['location'])): ?>
                                <div class="timeline-item-meta"><span><i class="bi bi-geo-alt"></i> <?php echo CHtml::encode($item['location']); ?></span></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

<?php endif; ?>
