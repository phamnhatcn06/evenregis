<?php
/**
 * Lịch trình cá nhân người tham dự - timeline gộp + thẻ "Sự kiện tiếp theo".
 *
 * @var string $fullName
 * @var array  $timeline cấu trúc từ Schedule::getForAttendee (serverTime, nextUp, days, noTime)
 */
$this->pageTitle = 'Lịch trình của tôi';

Yii::app()->clientScript->registerCssFile(
    Yii::app()->theme->baseUrl . '/assets/css/pages/schedule-my.css'
);
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/schedule-my.js',
    CClientScript::POS_END
);

$backUrl = $this->createUrl('/frontend/portal/index');
$serverTime = isset($timeline['serverTime']) ? (int) $timeline['serverTime'] : time();
$nextUp = isset($timeline['nextUp']) ? $timeline['nextUp'] : array();
$days = isset($timeline['days']) ? $timeline['days'] : array();
$noTime = isset($timeline['noTime']) ? $timeline['noTime'] : array();
?>

<div class="container schedule-page">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4 pt-3">
        <div>
            <h2 class="schedule-title">
                <i class="bi bi-calendar-week-fill text-primary"></i>
                Lịch trình của tôi
            </h2>
            <p class="schedule-subtitle mb-0">
                Chào <strong><?php echo CHtml::encode($fullName); ?></strong>, đây là toàn bộ lịch trình liên quan đến anh/chị trong Đại hội.
            </p>
        </div>
        <a href="<?php echo $backUrl; ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Về trang chủ
        </a>
    </div>

    <!-- Card "Sự kiện tiếp theo" -->
    <?php $this->renderPartial('_next_up', array('nextUp' => $nextUp, 'serverTime' => $serverTime)); ?>

    <!-- Timeline + mục chưa có giờ -->
    <?php $this->renderPartial('_timeline', array('days' => $days, 'noTime' => $noTime)); ?>
</div>
