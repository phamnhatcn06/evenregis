<?php
/**
 * Màn hình bốc thăm chia bảng / chia cặp đấu theo nội dung thể thao.
 *
 * @var SportDrawController $this
 * @var int|null $eventId
 * @var int|null $sportId
 * @var array $eventList
 * @var array $sportList
 * @var ApiDataProvider|null $dataProvider
 */

$this->breadcrumbs = array('Bốc thăm thi đấu');
$this->Tabletitle  = 'Bốc thăm chia bảng / chia cặp đấu';

Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/plugins/toast.js',
    CClientScript::POS_END
);
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/sportdraw-index.js?v=1.1',
    CClientScript::POS_END
);

$stages = ($dataProvider !== null) ? $dataProvider->getData() : array();

// Map sport_id => stage vòng bảng đích (để chuyển người thắng play-off sang).
// Ưu tiên giai đoạn chia bảng (round_robin_knockout / round_robin) cùng môn.
$targetStageBySport = array();
// Map sport_id => stage chung kết bơi (heat_lane, stage_type=final) để chuyển Top N.
$swimFinalBySport = array();
$groupFinalBySport = array();
// Map sport_id => danh sách giai đoạn play-off (để chọn làm nguồn đặt chỗ trước cho vòng bảng).
$playoffStagesBySport = array();
foreach ($stages as $s) {
    if ($s->format === SportStages::FORMAT_PLAYOFF) {
        $playoffStagesBySport[$s->sport_id][] = array('id' => (int) $s->id, 'name' => $s->name);
    }
    if (SportStages::isGroupFormat($s->format) && !isset($targetStageBySport[$s->sport_id])) {
        $targetStageBySport[$s->sport_id] = array('id' => $s->id, 'name' => $s->name);
    }
    if (SportStages::isSwimFormat($s->format) && $s->stage_type === 'final' && !isset($swimFinalBySport[$s->sport_id])) {
        $swimFinalBySport[$s->sport_id] = array('id' => $s->id, 'name' => $s->name);
    }
    // Vòng chung kết chia bảng (vd vòng tròn tranh 1-2-3) nhận đội đi tiếp từ vòng loại
    $isGroupFinal = SportStages::isGroupFormat($s->format) && $s->stage_type === 'final';
    if ($isGroupFinal && (!isset($groupFinalBySport[$s->sport_id]) || $s->id < $groupFinalBySport[$s->sport_id]['id'])) {
        $groupFinalBySport[$s->sport_id] = array('id' => $s->id, 'name' => $s->name);
    }
}
?>

<div id="sport-draw-config"
     data-config-url="<?php echo $this->createUrl('config', array('id' => '__ID__')); ?>"
     data-draw-groups-url="<?php echo $this->createUrl('drawGroups', array('id' => '__ID__')); ?>"
     data-draw-bracket-url="<?php echo $this->createUrl('drawBracket', array('id' => '__ID__')); ?>"
     data-standings-url="<?php echo $this->createUrl('standings', array('id' => '__ID__')); ?>"
     data-generate-knockout-url="<?php echo $this->createUrl('generateKnockout', array('id' => '__ID__')); ?>"
     data-draw-playoff-url="<?php echo $this->createUrl('drawPlayoff', array('id' => '__ID__')); ?>"
     data-playoff-entrants-list-url="<?php echo $this->createUrl('playoffEntrants', array('id' => '__ID__')); ?>"
     data-playoff-entrants-move-url="<?php echo $this->createUrl('movePlayoffEntrants', array('id' => '__ID__')); ?>"
     data-promote-playoff-url="<?php echo $this->createUrl('promotePlayoff', array('id' => '__ID__')); ?>"
     data-draw-swim-url="<?php echo $this->createUrl('drawSwimHeats', array('id' => '__ID__')); ?>"
     data-promote-swim-url="<?php echo $this->createUrl('promoteSwim', array('id' => '__ID__')); ?>"
     data-promote-groups-url="<?php echo $this->createUrl('promoteGroups', array('id' => '__ID__')); ?>"
     data-lock-url="<?php echo $this->createUrl('lock', array('id' => '__ID__')); ?>">
</div>

<!-- Bộ lọc -->
<div class="card mb-3">
    <div class="card-body">
        <form method="get" action="<?php echo $this->createUrl('index'); ?>" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Sự kiện</label>
                <?php echo CHtml::dropDownList('event_id', $eventId, $eventList, array(
                    'class' => 'form-select',
                    'prompt' => '-- Chọn sự kiện --',
                )); ?>
            </div>
            <div class="col-md-5">
                <label class="form-label">Nội dung thi đấu</label>
                <?php echo CHtml::dropDownList('sport_id', $sportId, $sportList, array(
                    'class' => 'form-select',
                    'prompt' => '-- Tất cả nội dung --',
                )); ?>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fa fa-search me-1"></i>Xem
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($dataProvider === null): ?>
    <div class="alert alert-info">Vui lòng chọn sự kiện để xem danh sách giai đoạn và tiến hành bốc thăm.</div>
<?php elseif (empty($stages)): ?>
    <div class="alert alert-warning">Không có giai đoạn nào cho lựa chọn này.</div>
<?php else: ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nội dung thi đấu</th>
                        <th>Giai đoạn</th>
                        <th>Thể thức</th>
                        <th class="text-center">Cấu hình</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-center" style="width:260px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($stages as $stage): ?>
                    <?php
                    $isGroup = SportStages::isGroupFormat($stage->format);
                    $isBracket = SportStages::isBracketFormat($stage->format);
                    $isLocked = ($stage->draw_status === SportStages::DRAW_LOCKED);
                    $configJson = CJSON::encode(array(
                        'id' => $stage->id,
                        'name' => $stage->name,
                        'format' => $stage->format,
                        'num_groups' => $stage->num_groups,
                        'teams_per_group' => $stage->teams_per_group,
                        'advance_per_group' => $stage->advance_per_group,
                        'bracket_size' => $stage->bracket_size,
                        'win_points' => $stage->win_points,
                        'draw_points' => $stage->draw_points,
                        'loss_points' => $stage->loss_points,
                        'tiebreak' => $stage->tiebreak,
                        'ko_pairing' => $stage->ko_pairing,
                        'playoff_source_stage_id' => $stage->playoff_source_stage_id,
                    ));
                    $playoffSources = isset($playoffStagesBySport[$stage->sport_id]) ? $playoffStagesBySport[$stage->sport_id] : array();
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo CHtml::encode($stage->sport_name ? $stage->sport_name : ('#' . $stage->sport_id)); ?></strong>
                            <?php if ($stage->sport_code): ?>
                                <br><small class="text-muted"><?php echo CHtml::encode($stage->sport_code); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo CHtml::encode($stage->name); ?>
                        </td>
                        <td><?php echo CHtml::encode(SportStages::getFormatLabel($stage->format)); ?></td>
                        <td class="text-center">
                            <?php if ($isGroup): ?>
                                <span class="badge bg-light text-dark"><?php echo (int) $stage->num_groups; ?> bảng</span>
                            <?php endif; ?>
                            <?php if ($isBracket && $stage->bracket_size): ?>
                                <span class="badge bg-light text-dark">Nhánh <?php echo (int) $stage->bracket_size; ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?php echo SportStages::getDrawStatusLabel($stage->draw_status); ?></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-config"
                                    data-config='<?php echo $configJson; ?>' <?php echo $isLocked ? 'disabled' : ''; ?>>
                                <i class="fa fa-cog"></i> Cấu hình
                            </button>
                            <?php if ($isGroup && !$isLocked): ?>
                                <button type="button" class="btn btn-sm btn-primary btn-draw"
                                        data-id="<?php echo $stage->id; ?>" data-type="groups">
                                    <i class="fa fa-random"></i> Bốc bảng
                                </button>
                            <?php endif; ?>
                            <?php if ($stage->format === SportStages::FORMAT_SINGLE_ELIMINATION && !$isLocked): ?>
                                <button type="button" class="btn btn-sm btn-primary btn-draw"
                                        data-id="<?php echo $stage->id; ?>" data-type="bracket">
                                    <i class="fa fa-sitemap"></i> Bốc nhánh
                                </button>
                            <?php endif; ?>
                            <?php if ($stage->format === SportStages::FORMAT_PLAYOFF && !$isLocked): ?>
                                <button type="button" class="btn btn-sm btn-outline-primary btn-playoff-entrants"
                                        data-id="<?php echo $stage->id; ?>"
                                        data-name="<?php echo CHtml::encode($stage->name); ?>">
                                    <i class="fa fa-user-plus"></i> Chọn VĐV
                                </button>
                                <button type="button" class="btn btn-sm btn-primary btn-draw"
                                        data-id="<?php echo $stage->id; ?>" data-type="playoff">
                                    <i class="fa fa-random"></i> Bốc play-off
                                </button>
                                <?php if (isset($targetStageBySport[$stage->sport_id])): ?>
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-promote-playoff"
                                            data-id="<?php echo $stage->id; ?>"
                                            data-target-id="<?php echo $targetStageBySport[$stage->sport_id]['id']; ?>"
                                            data-target-name="<?php echo CHtml::encode($targetStageBySport[$stage->sport_id]['name']); ?>">
                                        <i class="fa fa-arrow-right"></i> Chuyển người thắng → vòng bảng
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if (SportStages::isSwimFormat($stage->format)): ?>
                                <?php if (!$isLocked): ?>
                                    <button type="button" class="btn btn-sm btn-primary btn-swim-draw"
                                            data-id="<?php echo $stage->id; ?>">
                                        <i class="fa fa-random"></i> Bốc lượt/làn
                                    </button>
                                <?php endif; ?>
                                <a href="<?php echo $this->createUrl('swimTimes', array('id' => $stage->id)); ?>"
                                   class="btn btn-sm btn-outline-success">
                                    <i class="fa fa-stopwatch"></i> Nhập thành tích
                                </a>
                                <?php if ($stage->stage_type !== 'final' && isset($swimFinalBySport[$stage->sport_id])): ?>
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-promote-swim"
                                            data-id="<?php echo $stage->id; ?>"
                                            data-final-id="<?php echo $swimFinalBySport[$stage->sport_id]['id']; ?>"
                                            data-final-name="<?php echo CHtml::encode($swimFinalBySport[$stage->sport_id]['name']); ?>">
                                        <i class="fa fa-arrow-right"></i> Chuyển Top 6 → CK
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if ($isGroup && !$isLocked): ?>
                                <button type="button" class="btn btn-sm btn-outline-success btn-standings"
                                        data-id="<?php echo $stage->id; ?>">
                                    <i class="fa fa-list-ol"></i> Tính BXH
                                </button>
                            <?php endif; ?>
                            <?php if ($isGroup && $stage->stage_type === 'qualification' && isset($groupFinalBySport[$stage->sport_id])): ?>
                                <button type="button" class="btn btn-sm btn-outline-primary btn-promote-groups"
                                        data-id="<?php echo $stage->id; ?>"
                                        data-target-id="<?php echo $groupFinalBySport[$stage->sport_id]['id']; ?>"
                                        data-target-name="<?php echo CHtml::encode($groupFinalBySport[$stage->sport_id]['name']); ?>">
                                    <i class="fa fa-arrow-right"></i> Chuyển đội đi tiếp → CK
                                </button>
                            <?php endif; ?>
                            <?php if ($stage->format === SportStages::FORMAT_ROUND_ROBIN_KNOCKOUT && !$isLocked): ?>
                                <button type="button" class="btn btn-sm btn-primary btn-genko"
                                        data-id="<?php echo $stage->id; ?>">
                                    <i class="fa fa-sitemap"></i> Sinh nhánh từ bảng
                                </button>
                            <?php endif; ?>
                            <?php if (($isGroup || $isBracket || $stage->format === SportStages::FORMAT_PLAYOFF) && !$isLocked): ?>
                                <a href="<?php echo $this->createUrl('assign', array('id' => $stage->id)); ?>"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fa fa-hand-rock-o"></i> Gán thủ công
                                </a>
                            <?php endif; ?>
                            <a href="<?php echo $this->createUrl('results', array('id' => $stage->id)); ?>"
                               class="btn btn-sm btn-outline-success">
                                <i class="fa fa-pencil-square-o"></i> Ghi kết quả
                            </a>
                            <a href="<?php echo $this->createUrl('view', array('id' => $stage->id)); ?>"
                               class="btn btn-sm btn-outline-info">
                                <i class="fa fa-eye"></i> Xem
                            </a>
                            <?php if ($stage->draw_status === SportStages::DRAW_DRAWN): ?>
                                <button type="button" class="btn btn-sm btn-outline-dark btn-lock" data-id="<?php echo $stage->id; ?>">
                                    <i class="fa fa-lock"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php $this->renderPartial('_modal_config'); ?>
<?php $this->renderPartial('_modal_playoff_entrants'); ?>
