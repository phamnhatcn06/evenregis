<?php
/**
 * Ruột bảng ghi kết quả (render được độc lập để refresh qua AJAX sau mỗi lần lưu,
 * không phải tải lại cả trang).
 *
 * @var SportDrawController $this
 * @var SportStages $model
 * @var array|null $preview
 */

// Nội dung đơn/đôi (cá nhân): hiển thị tên VĐV thay cho tên đội.
$sportNameLower = mb_strtolower((string) $model->sport_name, 'UTF-8');
$isIndividualSport = (strpos($sportNameLower, 'đơn') !== false || strpos($sportNameLower, 'đôi') !== false
    || (isset($model->stage_type) && $model->stage_type === 'individual'));

$teamMap = array();
if ($preview && isset($preview['teams'])) {
    foreach ($preview['teams'] as $t) {
        $players = isset($t['player_names']) && is_array($t['player_names']) ? $t['player_names'] : array();
        $unitCode = isset($t['unit_code']) && $t['unit_code'] !== null && $t['unit_code'] !== '' ? $t['unit_code'] : null;
        $names = array_values(array_filter($players, function ($p) {
            return $p !== null && $p !== '';
        }));
        // Nội dung đơn: 1 VĐV. Nội dung đôi: ghép tên 2 VĐV (" / "). Môn đồng đội: dùng tên đội.
        if (!empty($names) && ($isIndividualSport || count($names) === 1)) {
            $teamMap[$t['team_id']] = implode(' / ', $names) . ($unitCode ? ' (' . $unitCode . ')' : '');
        } else {
            $teamMap[$t['team_id']] = $t['team_name'] !== null ? $t['team_name'] : ('#' . $t['team_id']);
        }
    }
}
$matches = ($preview && isset($preview['matches'])) ? $preview['matches'] : array();

$slotName = function ($teamId, $source) use ($teamMap) {
    if ($teamId && isset($teamMap[$teamId])) {
        return CHtml::encode($teamMap[$teamId]);
    }
    if ($source) {
        return '<span class="text-muted fst-italic">Chờ: ' . CHtml::encode($source) . '</span>';
    }
    return '<span class="text-muted fst-italic">Chưa xác định</span>';
};

/** Render 1 dòng trận với ô nhập tỉ số */
$renderMatch = function ($m) use ($slotName, $teamMap) {
    $hasBoth = !empty($m['team_a_id']) && !empty($m['team_b_id']);
    $res = isset($m['result']) ? $m['result'] : null;
    $scoreA = $res ? $res['score_a'] : '';
    $scoreB = $res ? $res['score_b'] : '';
    $done = $res !== null && $res['score_a'] !== null && $res['score_b'] !== null;
    // Trận loại trực tiếp/chung kết/tranh hạng không được hòa -> cần chọn đội thắng khi hòa điểm
    $isKnockout = in_array(($m['match_type'] ?? ''), array('knockout', 'final', 'playoff'), true);
    $winnerId = $res && isset($res['winner_team_id']) ? $res['winner_team_id'] : null;
    ?>
    <div class="card mb-2 match-row"
         data-match-id="<?php echo CHtml::encode($m['id']); ?>"
         data-knockout="<?php echo $isKnockout ? '1' : '0'; ?>">
        <div class="card-body p-2">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge bg-light text-dark"><?php echo CHtml::encode($m['round']); ?></span>
                <div class="flex-grow-1 text-end" style="min-width:140px;">
                    <?php echo $slotName($m['team_a_id'], isset($m['team_a_source']) ? $m['team_a_source'] : null); ?>
                </div>
                <?php if (!empty($m['is_bye'])): ?>
                    <span class="badge bg-secondary">BYE</span>
                <?php elseif ($hasBoth): ?>
                    <input type="number" class="form-control form-control-sm text-center score-a"
                           style="width:60px;" value="<?php echo CHtml::encode($scoreA); ?>" min="0">
                    <span class="fw-bold">-</span>
                    <input type="number" class="form-control form-control-sm text-center score-b"
                           style="width:60px;" value="<?php echo CHtml::encode($scoreB); ?>" min="0">
                <?php else: ?>
                    <span class="text-muted">vs</span>
                <?php endif; ?>
                <div class="flex-grow-1" style="min-width:140px;">
                    <?php echo $slotName($m['team_b_id'], isset($m['team_b_source']) ? $m['team_b_source'] : null); ?>
                </div>
                <?php if ($hasBoth && empty($m['is_bye']) && $isKnockout): ?>
                    <select class="form-select form-select-sm winner-select" style="width:auto;"
                            title="Đội thắng (bắt buộc khi hòa điểm)">
                        <option value="">Thắng: tự động</option>
                        <option value="<?php echo CHtml::encode($m['team_a_id']); ?>"
                            <?php echo ($winnerId == $m['team_a_id']) ? 'selected' : ''; ?>>
                            <?php echo CHtml::encode(isset($teamMap[$m['team_a_id']]) ? $teamMap[$m['team_a_id']] : ('#' . $m['team_a_id'])); ?>
                        </option>
                        <option value="<?php echo CHtml::encode($m['team_b_id']); ?>"
                            <?php echo ($winnerId == $m['team_b_id']) ? 'selected' : ''; ?>>
                            <?php echo CHtml::encode(isset($teamMap[$m['team_b_id']]) ? $teamMap[$m['team_b_id']] : ('#' . $m['team_b_id'])); ?>
                        </option>
                    </select>
                <?php endif; ?>
                <?php if ($hasBoth && empty($m['is_bye'])): ?>
                    <button type="button" class="btn btn-sm btn-primary btn-save-result">
                        <i class="fa fa-save"></i>
                    </button>
                    <span class="badge bg-success result-badge <?php echo $done ? '' : 'd-none'; ?>">
                        <i class="fa fa-check"></i>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
};
?>

<?php if (!$preview || empty($matches)): ?>
    <div class="alert alert-warning">Chưa có trận đấu nào. Hãy bốc thăm/gán đội và sinh lịch trước.</div>
<?php else: ?>

    <div class="alert alert-light border small d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <i class="fa fa-info-circle me-1"></i>
            Nhập tỉ số rồi nhấn <i class="fa fa-save"></i>. Với sơ đồ loại trực tiếp, đội thắng sẽ
            tự được điền vào trận vòng sau. Với vòng bảng, sau khi nhập đủ hãy
            <strong>Tính BXH</strong> rồi <strong>Sinh nhánh từ bảng</strong> ở màn danh sách.
        </div>
        <a href="<?php echo $this->createUrl('view', array('id' => $model->id)); ?>" class="btn btn-sm btn-outline-info text-nowrap">
            <i class="fa fa-sitemap me-1"></i>Xem sơ đồ cây (có đường nối)
        </a>
    </div>

    <?php
    // Nhóm trận: vòng bảng theo group_label, còn lại theo round_no
    $groupMatches = array();
    $koMatches = array();
    foreach ($matches as $m) {
        if (($m['match_type'] ?? '') === 'group') {
            $groupMatches[$m['group_label'] ?? '?'][] = $m;
        } else {
            $koMatches[] = $m;
        }
    }
    ksort($groupMatches);
    ?>

    <?php if (!empty($groupMatches)): ?>
        <h5 class="mb-2">Vòng bảng</h5>
        <div class="row">
            <?php foreach ($groupMatches as $label => $list): ?>
                <div class="col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header fw-bold py-2">Bảng <?php echo CHtml::encode($label); ?></div>
                        <div class="card-body p-2">
                            <?php foreach ($list as $m) { $renderMatch($m); } ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($koMatches)): ?>
        <h5 class="mb-2 mt-2">Vòng loại trực tiếp</h5>
        <?php foreach ($koMatches as $m) { $renderMatch($m); } ?>
    <?php endif; ?>

<?php endif; ?>
