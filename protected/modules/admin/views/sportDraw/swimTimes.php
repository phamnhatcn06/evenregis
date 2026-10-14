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
        Nhập thời gian dạng <code>ss.cc</code> (vd <code>30.65</code>) hoặc <code>m:ss.cc</code> (vd <code>1:19.03</code>).
        Thứ hạng chung cuộc được tính tự động khi lưu.
    </div>
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
                                        <?php echo isset($lane['rank']) && $lane['rank'] ? '<span class="badge bg-primary">' . (int) $lane['rank'] . '</span>' : '—'; ?>
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
