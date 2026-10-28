<?php
/**
 * Màn nhập thành tích bơi theo làn.
 *
 * @var SportDrawController $this
 * @var SportStages $model
 * @var array $heats Mảng lượt: mỗi phần tử có heat_no, heat_type, lanes[]
 */

$this->breadcrumbs = array(
    'Bốc thăm thi đấu' => $this->createUrl('index'),
    'Nhập thành tích bơi',
);
$this->Tabletitle = 'Nhập thành tích bơi — ' . $model->name;

Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/plugins/toast.js',
    CClientScript::POS_END
);
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/sportdraw-swimtimes.js',
    CClientScript::POS_END
);

/** Đổi mili-giây sang chuỗi hiển thị mm:ss.cc hoặc ss.cc */
function swimMsToText($ms)
{
    if ($ms === null || $ms === '') {
        return '';
    }
    $ms = (int) $ms;
    $totalSec = intval($ms / 1000);
    $cs = intval(round(($ms % 1000) / 10)); // phần trăm giây
    $min = intval($totalSec / 60);
    $sec = $totalSec % 60;
    if ($min > 0) {
        return sprintf('%d:%02d.%02d', $min, $sec, $cs);
    }
    return sprintf('%d.%02d', $sec, $cs);
}

/** Huy chương cho hạng 1-3, số thứ tự cho hạng còn lại */
function swimRankBadge($rank)
{
    $medals = array(1 => array('Nhất', 'bg-warning text-dark'), 2 => array('Nhì', 'bg-secondary'), 3 => array('Ba', 'bg-danger'));
    if (!$rank) {
        return '—';
    }
    if (isset($medals[$rank])) {
        return '<span class="badge ' . $medals[$rank][1] . '">' . $medals[$rank][0] . '</span>';
    }
    return '<span class="badge bg-light text-dark">' . (int) $rank . '</span>';
}

/** Hiển thị tên VĐV (nếu có) kèm đơn vị/đội phía dưới */
function swimLaneName($lane)
{
    $athlete = isset($lane['athlete_name']) ? trim($lane['athlete_name']) : '';
    $team = isset($lane['team_name']) ? $lane['team_name'] : '';
    if ($athlete !== '') {
        $html = '<div class="fw-semibold">' . CHtml::encode($athlete) . '</div>';
        if ($team !== '' && $team !== $athlete) {
            $html .= '<div class="text-muted small">' . CHtml::encode($team) . '</div>';
        }
        return $html;
    }
    return CHtml::encode($team);
}

// Bảng xếp hạng chung cuộc: gộp mọi lượt, sắp theo hạng
$ranking = array();
foreach ($heats as $heat) {
    foreach ($heat['lanes'] as $lane) {
        if (!empty($lane['rank'])) {
            $ranking[] = array(
                'rank' => (int) $lane['rank'],
                'team_name' => $lane['team_name'],
                'athlete_name' => isset($lane['athlete_name']) ? $lane['athlete_name'] : '',
                'heat_no' => $heat['heat_no'],
                'lane_no' => $lane['lane_no'],
                'result_ms' => $lane['result_ms'],
            );
        }
    }
}
usort($ranking, function ($a, $b) {
    return $a['rank'] - $b['rank'];
});
?>

<div id="swim-times-root"
     data-save-url="<?php echo $this->createUrl('saveSwimTimes', array('id' => $model->id)); ?>"
     data-back-url="<?php echo $this->createUrl('index'); ?>">
</div>

<div class="card mb-3">
    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-1"><?php echo CHtml::encode($model->sport_name ? $model->sport_name : ('Môn #' . $model->sport_id)); ?></h5>
            <span class="text-muted"><?php echo CHtml::encode($model->name); ?></span>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo $this->createUrl('index'); ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i>Quay lại
            </a>
            <button type="button" class="btn btn-success btn-sm" id="btn-save-swim-times">
                <i class="fa fa-save me-1"></i>Lưu thành tích
            </button>
        </div>
    </div>
</div>

<?php if (empty($heats)): ?>
    <div class="alert alert-warning">
        Chưa có lượt/làn nào. Hãy bấm <strong>"Bốc lượt/làn"</strong> ở màn bốc thăm trước.
    </div>
<?php else: ?>
    <div class="alert alert-info py-2 small">
        Có thể sửa số làn trực tiếp. Nhập thời gian dạng <code>ss.cc</code> (vd <code>30.65</code>) hoặc
        <code>m:ss.cc</code> (vd <code>1:19.03</code>). Thứ hạng chung cuộc (gộp các lượt) được tính tự động khi lưu.
    </div>

    <?php if (!empty($ranking)): ?>
        <div class="card mb-3">
            <div class="card-header bg-light fw-bold">Bảng xếp hạng chung cuộc</div>
            <div class="card-body p-0">
                <table class="table table-sm table-bordered mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:80px;" class="text-center">Hạng</th>
                            <th>VĐV</th>
                            <th style="width:110px;" class="text-center">Lượt / Làn</th>
                            <th style="width:120px;">Thành tích</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($ranking as $row): ?>
                        <tr>
                            <td class="text-center"><?php echo swimRankBadge($row['rank']); ?></td>
                            <td><?php echo swimLaneName($row); ?></td>
                            <td class="text-center"><?php echo (int) $row['heat_no'] . ' / ' . (int) $row['lane_no']; ?></td>
                            <td class="font-monospace"><?php echo swimMsToText($row['result_ms']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
    <div class="row g-3">
        <?php foreach ($heats as $heat): ?>
            <div class="col-12 col-lg-6">
                <div class="card h-100">
                    <div class="card-header bg-light fw-bold">
                        Lượt <?php echo (int) $heat['heat_no']; ?>
                        <span class="badge bg-secondary ms-1"><?php echo CHtml::encode($heat['heat_type']); ?></span>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-bordered mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:60px;" class="text-center">Làn</th>
                                    <th>VĐV</th>
                                    <th style="width:130px;">Thành tích</th>
                                    <th style="width:60px;" class="text-center">Hạng</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($heat['lanes'] as $lane): ?>
                                <tr>
                                    <td>
                                        <input type="number" min="1" max="10"
                                               class="form-control form-control-sm text-center swim-lane-input"
                                               data-lane-id="<?php echo (int) $lane['lane_id']; ?>"
                                               value="<?php echo (int) $lane['lane_no']; ?>">
                                    </td>
                                    <td><?php echo CHtml::encode($lane['team_name']); ?></td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm swim-time-input"
                                               data-lane-id="<?php echo (int) $lane['lane_id']; ?>"
                                               value="<?php echo swimMsToText(isset($lane['result_ms']) ? $lane['result_ms'] : null); ?>"
                                               placeholder="ss.cc">
                                    </td>
                                    <td class="text-center">
                                        <?php echo swimRankBadge(isset($lane['rank']) ? $lane['rank'] : null); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
