<?php
$this->breadcrumbs = array(
    'Bữa ăn' => $this->createUrl('admin', array('event_id' => $eventId)),
    'Báo cáo chấm ăn',
);
$this->menu = array(
    array(
        'label' => 'Danh sách bữa ăn',
        'url' => $this->createUrl('admin', array('event_id' => $eventId)),
        'color' => 'primary',
        'icon' => 'fa-th',
        'id' => 'btn_manage',
    ),
);
$this->Tabletitle = 'Báo cáo chấm ăn';

$fmtTime = function ($v) {
    if (!$v) return '--';
    $ts = strtotime($v);
    return $ts ? date('d/m/Y H:i', $ts) : $v;
};
$meals = ($summary && isset($summary['meals'])) ? $summary['meals'] : array();
$rows  = ($detail && isset($detail['rows'])) ? $detail['rows'] : array();
?>
<div class="card mb-3">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Sự kiện</label>
                <select name="event_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Chọn sự kiện --</option>
                    <?php foreach ($eventList as $id => $name): ?>
                        <option value="<?php echo $id; ?>" <?php echo ($eventId == $id) ? 'selected' : ''; ?>>
                            <?php echo CHtml::encode($name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Lọc theo bữa (chi tiết)</label>
                <select name="meal_id" class="form-select" onchange="this.form.submit()" <?php echo $eventId ? '' : 'disabled'; ?>>
                    <option value="">-- Tất cả bữa --</option>
                    <?php foreach ($meals as $m): ?>
                        <option value="<?php echo $m['meal_id']; ?>" <?php echo ($mealId == $m['meal_id']) ? 'selected' : ''; ?>>
                            <?php echo CHtml::encode($m['meal_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <?php if ($eventId): ?>
                    <a class="btn btn-success w-100"
                       href="<?php echo $this->createUrl('export', array('event_id' => $eventId, 'meal_id' => $mealId)); ?>">
                        <i class="fa fa-file-excel-o me-1"></i>Xuất Excel
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php if (!$eventId): ?>
    <div class="alert alert-info"><i class="fa fa-info-circle me-1"></i>Chọn sự kiện để xem báo cáo chấm ăn.</div>
<?php else: ?>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Tổng hợp số người ăn theo bữa</h5>
        <span class="badge bg-primary fs-6">Tổng cộng: <?php echo $summary ? (int) $summary['grand_total'] : 0; ?> lượt</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped mb-0">
                <thead>
                    <tr>
                        <th style="width:60px;">STT</th>
                        <th>Tên bữa ăn</th>
                        <th style="width:100px;">Loại</th>
                        <th style="width:110px;">Ngày</th>
                        <th>Địa điểm</th>
                        <th style="width:120px;" class="text-end">Số người ăn</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($meals)): ?>
                        <tr><td colspan="6" class="text-center text-muted">Chưa có bữa ăn.</td></tr>
                    <?php else: $i = 1; foreach ($meals as $m): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo CHtml::encode($m['meal_name']); ?></td>
                            <td><?php echo CHtml::encode(Meals::getTypeLabel($m['meal_type'])); ?></td>
                            <td><?php echo CHtml::encode($m['meal_date']); ?></td>
                            <td><?php echo CHtml::encode($m['location']); ?></td>
                            <td class="text-end"><strong><?php echo (int) $m['total']; ?></strong></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Danh sách người chấm ăn</h5>
        <span class="badge bg-secondary fs-6"><?php echo count($rows); ?> lượt chấm</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped mb-0">
                <thead>
                    <tr>
                        <th style="width:60px;">STT</th>
                        <th>Họ tên</th>
                        <th>Đơn vị</th>
                        <th>Bữa ăn</th>
                        <th>Trạm chấm</th>
                        <th style="width:150px;">Giờ chấm</th>
                        <th style="width:120px;">Nguồn</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="8" class="text-center text-muted">Chưa có lượt chấm ăn nào.</td></tr>
                    <?php else: $i = 1; foreach ($rows as $r): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo CHtml::encode($r['full_name']); ?></td>
                            <td><?php echo CHtml::encode($r['org']); ?></td>
                            <td><?php echo CHtml::encode($r['unit']); ?></td>
                            <td><?php echo CHtml::encode($r['meal_name']); ?></td>
                            <td><?php echo CHtml::encode($r['checked_by']); ?></td>
                            <td><?php echo $fmtTime($r['check_in_time']); ?></td>
                            <td><?php echo CHtml::encode(MealsController::getSourceLabel($r['source'])); ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php endif; ?>
