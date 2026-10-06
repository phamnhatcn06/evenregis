<?php
$this->breadcrumbs = array('Dashboard đăng ký hoạt động');
$this->Tabletitle = 'Dashboard mức lấp đầy — Fun Run & Tham quan';

/**
 * Vẽ 1 thẻ thống kê cho 1 nội dung con (quota / đã đăng ký / còn lại) kèm thanh tiến độ.
 */
function renderFillBar($name, $extra, $registered, $quota)
{
    $registered = (int) $registered;
    $quota = (int) $quota;
    $remaining = max(0, $quota - $registered);
    $pct = $quota > 0 ? min(100, round($registered / $quota * 100)) : 0;
    $barCls = $pct >= 100 ? 'bg-danger' : ($pct >= 80 ? 'bg-warning' : 'bg-success');
    ?>
    <div class="mb-3">
        <div class="d-flex justify-content-between">
            <span class="fw-semibold"><?php echo CHtml::encode($name); ?>
                <?php if ($extra !== ''): ?><small class="text-muted">(<?php echo CHtml::encode($extra); ?>)</small><?php endif; ?>
            </span>
            <span class="text-muted small"><?php echo $registered; ?>/<?php echo $quota; ?> — còn <?php echo $remaining; ?></span>
        </div>
        <div class="progress" style="height:20px;">
            <div class="progress-bar <?php echo $barCls; ?>" role="progressbar"
                 style="width:<?php echo $pct; ?>%;"><?php echo $pct; ?>%</div>
        </div>
    </div>
    <?php
}

// Tổng hợp nhanh.
$sumReg = 0;
$sumQuota = 0;
foreach ($runEvents as $e) { $sumReg += (int) $e->registered_count; $sumQuota += (int) $e->quota; }
foreach ($tourSessions as $s) { $sumReg += (int) $s->registered_count; $sumQuota += (int) $s->quota; }
?>
<div class="card mb-3">
    <div class="card-body">
        <form method="get" action="<?php echo $this->createUrl('index'); ?>" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Sự kiện</label>
                <?php echo CHtml::dropDownList('event_id', $eventId, $eventList, array(
                    'class' => 'form-select',
                    'empty' => '-- Chọn sự kiện --',
                    'onchange' => 'this.form.submit()',
                )); ?>
            </div>
            <?php if ($eventId): ?>
            <div class="col-md-7 text-end">
                <span class="badge bg-primary fs-6">Tổng đã đăng ký: <?php echo $sumReg; ?>/<?php echo $sumQuota; ?></span>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php if (!$eventId): ?>
    <div class="alert alert-info">Vui lòng chọn sự kiện để xem mức lấp đầy.</div>
<?php else: ?>
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-primary text-white"><i class="fa fa-running me-1"></i> Chạy bộ (Fun Run)</div>
            <div class="card-body">
                <?php if (empty($runEvents)): ?>
                    <div class="alert alert-warning mb-0">Chưa có cự ly nào.</div>
                <?php else: foreach ($runEvents as $e): ?>
                    <?php renderFillBar($e->name, $e->code, $e->registered_count, $e->quota); ?>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-success text-white"><i class="fa fa-bus me-1"></i> Đi tham quan</div>
            <div class="card-body">
                <?php if (empty($tourSessions)): ?>
                    <div class="alert alert-warning mb-0">Chưa có đợt nào.</div>
                <?php else: foreach ($tourSessions as $s): ?>
                    <?php renderFillBar($s->name, $s->start_time, $s->registered_count, $s->quota); ?>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
