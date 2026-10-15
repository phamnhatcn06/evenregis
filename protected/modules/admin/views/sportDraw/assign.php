<?php
/**
 * Màn hình gán thủ công (kéo-thả) đội vào bảng đấu hoặc vị trí seed nhánh.
 *
 * @var SportDrawController $this
 * @var SportStages $model
 * @var array|null $preview
 */

$this->breadcrumbs = array(
    'Bốc thăm thi đấu' => $this->createUrl('index'),
    $model->name => $this->createUrl('view', array('id' => $model->id)),
    'Gán thủ công',
);
$this->Tabletitle = 'Gán thủ công: ' . $model->name;

$this->menu = array(
    array(
        'label' => 'Quay lại',
        'url' => $this->createUrl('index', array('event_id' => $model->event_id, 'sport_id' => $model->sport_id)),
        'color' => 'secondary',
        'icon' => 'fa-arrow-left',
        'id' => 'btn_back',
    ),
    array(
        'label' => 'Xem sơ đồ cây',
        'url' => $this->createUrl('view', array('id' => $model->id)),
        'color' => 'info',
        'icon' => 'fa-sitemap',
        'id' => 'btn_view_bracket',
    ),
);

Yii::app()->clientScript->registerCssFile(
    Yii::app()->theme->baseUrl . '/assets/css/pages/sportdraw-bracket.css?v=1.2'
);

Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/plugins/toast.js',
    CClientScript::POS_END
);
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/sportdraw-assign.js?v=1.0',
    CClientScript::POS_END
);

$isGroup = SportStages::isGroupFormat($model->format);
$isPlayoff = ($model->format === SportStages::FORMAT_PLAYOFF);
// Play-off dùng chung bố cục ghép cặp theo slot như nhánh loại trực tiếp.
$isBracket = SportStages::isBracketFormat($model->format) || $isPlayoff;
$mode = $isGroup ? 'groups' : ($isBracket ? 'bracket' : '');

// Danh sách đội của giai đoạn + phân bổ hiện tại (nếu đã bốc)
$teams = ($preview && isset($preview['teams'])) ? $preview['teams'] : array();

// Nhãn hiển thị: ưu tiên tên VĐV (display_name), fallback mã đội.
$teamLabel = function ($t) {
    if (!empty($t['display_name'])) {
        return $t['display_name'];
    }
    return (isset($t['team_name']) && $t['team_name'] !== null) ? $t['team_name'] : ('#' . $t['team_id']);
};

// Nhãn bảng A, B, C...
$numGroups = (int) $model->num_groups;
$groupLabels = array();
for ($i = 0; $i < $numGroups; $i++) {
    $groupLabels[] = chr(65 + $i); // A, B, C...
}
$bracketSize = (int) $model->bracket_size;
// Đảm bảo nhánh đủ chứa số đội (lũy thừa 2 gần nhất ≥ số đội); dùng cả khi chưa cấu hình.
if ($isBracket) {
    $teamCount = count($teams);
    $needed = 2;
    while ($needed < $teamCount) {
        $needed *= 2;
    }
    if ($bracketSize < $needed) {
        $bracketSize = $needed;
    }
}
?>

<?php if (!$mode): ?>
    <div class="alert alert-warning">
        Thể thức <strong><?php echo CHtml::encode(SportStages::getFormatLabel($model->format)); ?></strong>
        không áp dụng gán theo bảng/nhánh. Chỉ các thể thức vòng bảng hoặc loại trực tiếp mới dùng màn hình này.
    </div>
<?php elseif (empty($teams)): ?>
    <div class="alert alert-info">Giai đoạn này chưa có đội/VĐV tham gia.</div>
<?php else: ?>

    <div id="assign-board"
         data-save-url="<?php echo $this->createUrl('saveAssignment', array('id' => $model->id)); ?>"
         data-mode="<?php echo $mode; ?>">

        <div class="alert alert-light border small">
            <i class="fa fa-info-circle me-1"></i>
            Kéo các đội từ cột <strong>Chưa phân bổ</strong> thả vào
            <?php echo $isGroup ? 'bảng đấu tương ứng' : ($isPlayoff ? 'các ô cặp đấu play-off' : 'vị trí hạt giống (seed)'); ?>.
            Có thể kéo đổi lại giữa các ô. Nhấn <strong>Lưu phân bổ</strong> khi hoàn tất.
            <?php if ($isPlayoff): ?>
                Mỗi cặp 2 ô là <strong>một trận play-off độc lập</strong>; người thắng sẽ được chuyển sang vòng chung kết.
            <?php else: ?>
                Sau khi ghi kết quả từng trận, đội thắng sẽ tự được điền vào vòng sau theo quy tắc.
            <?php endif; ?>
        </div>

        <div class="row">
            <!-- Cột đội chưa phân bổ -->
            <div class="col-md-3 mb-3">
                <div class="card h-100">
                    <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                        <span>Chưa phân bổ</span>
                        <span class="badge bg-secondary" id="pool-count">0</span>
                    </div>
                    <div class="card-body team-zone p-2" id="team-pool" data-zone="pool" style="min-height:120px;">
                        <?php foreach ($teams as $t): ?>
                            <?php
                            $assigned = $isGroup ? !empty($t['group_label']) : !empty($t['seed']);
                            if ($assigned) {
                                continue;
                            }
                            ?>
                            <div class="team-chip card mb-2 shadow-sm" draggable="true"
                                 data-team-id="<?php echo CHtml::encode($t['team_id']); ?>">
                                <div class="card-body p-2 small d-flex align-items-center">
                                    <i class="fa fa-arrows me-2 text-muted"></i>
                                    <?php echo CHtml::encode($teamLabel($t)); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Cột bảng / nhánh -->
            <div class="col-md-9 mb-3">
                <?php if ($isGroup): ?>
                    <div class="row">
                        <?php foreach ($groupLabels as $label): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-header fw-bold">Bảng <?php echo $label; ?></div>
                                    <div class="card-body team-zone p-2" data-zone="group" data-group="<?php echo $label; ?>"
                                         style="min-height:120px;">
                                        <?php foreach ($teams as $t): ?>
                                            <?php if (($t['group_label'] ?? '') !== $label) continue; ?>
                                            <div class="team-chip card mb-2 shadow-sm" draggable="true"
                                                 data-team-id="<?php echo CHtml::encode($t['team_id']); ?>">
                                                <div class="card-body p-2 small d-flex align-items-center">
                                                    <i class="fa fa-arrows me-2 text-muted"></i>
                                                    <?php echo CHtml::encode($teamLabel($t)); ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <?php
                    // Render từng ô slot: tìm đội theo bracket_slot
                    $slotChip = function ($pos) use ($teams) {
                        $team = null;
                        foreach ($teams as $t) {
                            if ((int) ($t['bracket_slot'] ?? 0) === $pos) { $team = $t; break; }
                        }
                        ?>
                        <div class="team-zone flex-grow-1 border rounded p-1 seed-slot"
                             data-zone="seed" data-seed="<?php echo $pos; ?>" style="min-height:46px;">
                            <?php if ($team): ?>
                                <div class="team-chip card shadow-sm mb-0" draggable="true"
                                     data-team-id="<?php echo CHtml::encode($team['team_id']); ?>">
                                    <div class="card-body p-2 small d-flex align-items-center">
                                        <i class="fa fa-arrows me-2 text-muted"></i>
                                        <?php echo CHtml::encode($teamLabel($team)); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php
                    };
                    $numMatches = (int) ($bracketSize / 2);
                    ?>
                    <div class="card">
                        <div class="card-header fw-bold">
                            <?php if ($isPlayoff): ?>
                                Ghép cặp play-off (tối đa <?php echo $numMatches; ?> trận)
                            <?php else: ?>
                                Cặp đấu vòng 1 (nhánh <?php echo $bracketSize; ?> — <?php echo $numMatches; ?> trận)
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php for ($m = 1; $m <= $numMatches; $m++): ?>
                                    <div class="col-lg-6 mb-3">
                                        <div class="border rounded p-2 bg-white">
                                            <div class="small fw-bold text-muted mb-1">Trận <?php echo $m; ?></div>
                                            <div class="d-flex align-items-center gap-2">
                                                <?php $slotChip(2 * $m - 1); ?>
                                                <span class="fw-bold text-muted">VS</span>
                                                <?php $slotChip(2 * $m); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="text-end">
            <a href="<?php echo $this->createUrl('view', array('id' => $model->id)); ?>" class="btn btn-outline-secondary">
                <i class="fa fa-eye me-1"></i>Xem sơ đồ
            </a>
            <button type="button" class="btn btn-primary" id="btn-save-assignment">
                <i class="fa fa-save me-1"></i>Lưu phân bổ
            </button>
        </div>
    </div>

<?php endif; ?>
