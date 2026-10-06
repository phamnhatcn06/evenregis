<?php
/**
 * Hiển thị kết quả bốc thăm: bảng đấu (round-robin) và sơ đồ cây (knockout).
 *
 * @var SportDrawController $this
 * @var SportStages $model
 * @var array|null $preview
 */

$this->breadcrumbs = array(
    'Bốc thăm thi đấu' => $this->createUrl('index'),
    $model->name,
);
$this->Tabletitle = 'Kết quả bốc thăm: ' . $model->name;

$this->menu = array(
    array(
        'label' => 'Quay lại',
        'url' => $this->createUrl('index', array('event_id' => $model->event_id, 'sport_id' => $model->sport_id)),
        'color' => 'secondary',
        'icon' => 'fa-arrow-left',
        'id' => 'btn_back',
    ),
);

// Map team_id -> tên để hiển thị trong trận
$teamMap = array();
$teams = array();
if ($preview && isset($preview['teams'])) {
    $teams = $preview['teams'];
    foreach ($teams as $t) {
        $teamMap[$t['team_id']] = $t['team_name'] !== null ? $t['team_name'] : ('#' . $t['team_id']);
    }
}

$matches = ($preview && isset($preview['matches'])) ? $preview['matches'] : array();

/** Tên đội trong 1 slot của trận */
$slotName = function ($teamId, $source) use ($teamMap) {
    if ($teamId && isset($teamMap[$teamId])) {
        return CHtml::encode($teamMap[$teamId]);
    }
    if ($source) {
        return '<span class="text-muted fst-italic">Chờ: ' . CHtml::encode($source) . '</span>';
    }
    return '<span class="text-muted fst-italic">Chưa xác định</span>';
};
?>

<?php if (!$preview): ?>
    <div class="alert alert-warning">Chưa có dữ liệu bốc thăm cho giai đoạn này.</div>
<?php else: ?>

    <div class="mb-3">
        Thể thức: <strong><?php echo CHtml::encode(SportStages::getFormatLabel($model->format)); ?></strong>
        &nbsp;|&nbsp; <?php echo SportStages::getDrawStatusLabel($preview['draw_status']); ?>
    </div>

    <?php
    // ===== Bảng xếp hạng =====
    $standings = isset($preview['standings']) ? $preview['standings'] : array();
    $standingsByGroup = array();
    foreach ($standings as $s) {
        $standingsByGroup[$s['group_label']][] = $s;
    }
    ksort($standingsByGroup);
    ?>
    <?php if (!empty($standingsByGroup)): ?>
        <h5 class="mb-3">Bảng xếp hạng</h5>
        <div class="row">
            <?php foreach ($standingsByGroup as $label => $rows): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header fw-bold">Bảng <?php echo CHtml::encode($label); ?></div>
                        <table class="table table-sm table-bordered mb-0 text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th><th class="text-start">Đội</th><th>Trận</th>
                                    <th>T</th><th>H</th><th>B</th><th>HS</th><th>Điểm</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($rows as $r): ?>
                                <tr>
                                    <td><?php echo (int) $r['rank']; ?></td>
                                    <td class="text-start"><?php echo CHtml::encode($teamMap[$r['team_id']] ?? ('#' . $r['team_id'])); ?></td>
                                    <td><?php echo (int) $r['played']; ?></td>
                                    <td><?php echo (int) $r['won']; ?></td>
                                    <td><?php echo (int) $r['drawn']; ?></td>
                                    <td><?php echo (int) $r['lost']; ?></td>
                                    <td><?php echo ((int) $r['points_scored']) . ':' . ((int) $r['points_conceded']); ?></td>
                                    <td class="fw-bold"><?php echo (int) $r['points']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php
    // ===== Vòng bảng (round-robin) =====
    $groups = array();
    foreach ($teams as $t) {
        if (!empty($t['group_label'])) {
            $groups[$t['group_label']][] = $t;
        }
    }
    ksort($groups);
    $groupMatches = array();
    foreach ($matches as $m) {
        if (($m['match_type'] ?? '') === 'group' && !empty($m['group_label'])) {
            $groupMatches[$m['group_label']][] = $m;
        }
    }
    ?>

    <?php if (!empty($groups)): ?>
        <h5 class="mb-3">Bảng đấu</h5>
        <div class="row">
            <?php foreach ($groups as $label => $members): ?>
                <?php usort($members, function ($a, $b) { return $a['group_order'] - $b['group_order']; }); ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header fw-bold">Bảng <?php echo CHtml::encode($label); ?></div>
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr><th style="width:40px;">#</th><th>Đội / VĐV</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($members as $i => $t): ?>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td><?php echo CHtml::encode($t['team_name']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php if (!empty($groupMatches[$label])): ?>
                            <div class="card-footer small">
                                <div class="fw-bold mb-1">Lịch đấu</div>
                                <?php foreach ($groupMatches[$label] as $m): ?>
                                    <div>
                                        <?php echo $slotName($m['team_a_id'], $m['team_a_source'] ?? null); ?>
                                        <span class="text-muted"> vs </span>
                                        <?php echo $slotName($m['team_b_id'], $m['team_b_source'] ?? null); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php
    // ===== Sơ đồ loại trực tiếp (knockout) =====
    $rounds = array();
    $thirdPlace = null;
    foreach ($matches as $m) {
        $type = $m['match_type'] ?? '';
        if ($type === 'playoff') {
            $thirdPlace = $m;
            continue;
        }
        if ($type === 'knockout' || $type === 'final') {
            $rounds[$m['round_no']][] = $m;
        }
    }
    ksort($rounds);
    ?>

    <?php if (!empty($rounds)): ?>
        <h5 class="mb-3">Sơ đồ loại trực tiếp</h5>
        <div class="d-flex gap-4 overflow-auto pb-3">
            <?php foreach ($rounds as $roundNo => $roundMatches): ?>
                <?php usort($roundMatches, function ($a, $b) { return $a['bracket_pos'] - $b['bracket_pos']; }); ?>
                <div style="min-width:220px;">
                    <div class="text-center fw-bold mb-2"><?php echo CHtml::encode($roundMatches[0]['round']); ?></div>
                    <?php foreach ($roundMatches as $m): ?>
                        <div class="card mb-3">
                            <div class="card-body p-2 small">
                                <div><?php echo $slotName($m['team_a_id'], $m['team_a_source'] ?? null); ?></div>
                                <hr class="my-1">
                                <div>
                                    <?php if (!empty($m['is_bye'])): ?>
                                        <span class="badge bg-light text-muted">BYE</span>
                                    <?php else: ?>
                                        <?php echo $slotName($m['team_b_id'], $m['team_b_source'] ?? null); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($thirdPlace): ?>
            <div class="card mt-2" style="max-width:260px;">
                <div class="card-header fw-bold py-1 small">Tranh hạng 3</div>
                <div class="card-body p-2 small">
                    <div><?php echo $slotName($thirdPlace['team_a_id'], $thirdPlace['team_a_source'] ?? null); ?></div>
                    <hr class="my-1">
                    <div><?php echo $slotName($thirdPlace['team_b_id'], $thirdPlace['team_b_source'] ?? null); ?></div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

<?php endif; ?>
