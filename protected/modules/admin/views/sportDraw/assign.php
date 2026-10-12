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
$isBracket = SportStages::isBracketFormat($model->format);
$mode = $isGroup ? 'groups' : ($isBracket ? 'bracket' : '');

// Danh sách đội của giai đoạn + phân bổ hiện tại (nếu đã bốc)
$teams = ($preview && isset($preview['teams'])) ? $preview['teams'] : array();

// Nhãn bảng A, B, C...
$numGroups = (int) $model->num_groups;
$groupLabels = array();
for ($i = 0; $i < $numGroups; $i++) {
    $groupLabels[] = chr(65 + $i); // A, B, C...
}
$bracketSize = (int) $model->bracket_size;
// Fallback: nếu chưa cấu hình kích thước nhánh, tự tính theo số đội (lũy thừa 2 gần nhất)
if ($isBracket && $bracketSize < 2) {
    $teamCount = count($teams);
    $bracketSize = 2;
    while ($bracketSize < $teamCount) {
        $bracketSize *= 2;
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
            <?php echo $isGroup ? 'bảng đấu tương ứng' : 'vị trí hạt giống (seed)'; ?>.
            Có thể kéo đổi lại giữa các ô. Nhấn <strong>Lưu phân bổ</strong> khi hoàn tất.
            Sau khi ghi kết quả từng trận, đội thắng sẽ tự được điền vào vòng sau theo quy tắc.
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
                                    <?php echo CHtml::encode($t['team_name'] !== null ? $t['team_name'] : ('#' . $t['team_id'])); ?>
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
                                                    <?php echo CHtml::encode($t['team_name'] !== null ? $t['team_name'] : ('#' . $t['team_id'])); ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-header fw-bold">Vị trí hạt giống (nhánh <?php echo $bracketSize; ?>)</div>
                        <div class="card-body">
                            <div class="row">
                                <?php for ($seed = 1; $seed <= $bracketSize; $seed++): ?>
                                    <?php
                                    $seedTeam = null;
                                    foreach ($teams as $t) {
                                        if ((int) ($t['seed'] ?? 0) === $seed) { $seedTeam = $t; break; }
                                    }
                                    ?>
                                    <div class="col-md-6 mb-2 d-flex align-items-center">
                                        <span class="badge bg-primary me-2" style="width:34px;">#<?php echo $seed; ?></span>
                                        <div class="team-zone flex-grow-1 border rounded p-1 seed-slot"
                                             data-zone="seed" data-seed="<?php echo $seed; ?>"
                                             style="min-height:46px;">
                                            <?php if ($seedTeam): ?>
                                                <div class="team-chip card shadow-sm mb-0" draggable="true"
                                                     data-team-id="<?php echo CHtml::encode($seedTeam['team_id']); ?>">
                                                    <div class="card-body p-2 small d-flex align-items-center">
                                                        <i class="fa fa-arrows me-2 text-muted"></i>
                                                        <?php echo CHtml::encode($seedTeam['team_name'] !== null ? $seedTeam['team_name'] : ('#' . $seedTeam['team_id'])); ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
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
