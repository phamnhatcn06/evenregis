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

<div class="schedule-page">
    <!-- Hero -->
    <section class="sch-hero">
        <div class="container position-relative">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                <div>
                    <span class="badge rounded-pill sch-hero-badge mb-3">
                        <i class="bi bi-calendar-week"></i> Lịch trình cá nhân
                    </span>
                    <h1 class="fw-bold text-white mb-2">Lịch trình của tôi</h1>
                    <p class="text-white-50 mb-0" style="max-width:640px;">
                        Chào <strong class="text-white"><?php echo CHtml::encode($fullName); ?></strong>, đây là toàn bộ
                        lịch trình liên quan đến anh/chị trong Đại hội — thể thao, nghiệp vụ, bữa ăn và tiệc.
                    </p>
                </div>
                <a href="<?php echo $backUrl; ?>" class="btn sch-hero-back">
                    <i class="bi bi-arrow-left me-1"></i> Về trang chủ
                </a>
            </div>
        </div>
    </section>

    <section class="container sch-body">
        <!-- Card "Sự kiện tiếp theo" -->
        <?php $this->renderPartial('_next_up', array('nextUp' => $nextUp, 'serverTime' => $serverTime)); ?>

        <!-- Timeline + mục chưa có giờ -->
        <?php $this->renderPartial('_timeline', array('days' => $days, 'noTime' => $noTime)); ?>
    </section>
</div>
