<?php
/**
 * Hiển thị kết quả bốc thăm: bảng đấu (round-robin) và sơ đồ cây loại trực tiếp (knockout).
 * Giao diện hiện đại tích hợp:
 * 1. Sơ đồ loại trực tiếp với đường nối SVG động, hiệu ứng rà chuột, bục cúp vô địch và trận tranh hạng 3.
 * 2. Bảng xếp hạng chi tiết cho từng bảng (luôn tự động tính toán từ các trận đấu).
 * 3. Chức năng ghi tỉ số / kết quả trực tiếp trên từng trận (modal popup AJAX).
 * 4. Kéo thả sắp xếp thứ tự các trận đấu và lưu lại backend.
 * 5. Ghi thời gian bắt đầu và địa điểm sân thi đấu cho từng trận.
 * 6. Sắp xếp các trận đấu tự động theo thứ tự thời gian tăng dần.
 *
 * @var SportDrawController $this
 * @var SportStages $model
 * @var array|null $preview
 * @var int|null $playoffSource ID giai đoạn play-off nguồn (nếu có) để đặt chỗ trước
 */
$playoffSource = isset($playoffSource) ? $playoffSource : null;
$qualSource = isset($qualSource) ? $qualSource : null;

$this->breadcrumbs = array(
    'Bốc thăm thi đấu' => $this->createUrl('index'),
    $model->name,
);
$this->Tabletitle = 'Chi tiết thi đấu: ' . $model->name;

$this->menu = array(
    array(
        'label' => 'Quay lại',
        'url' => $this->createUrl('index', array('event_id' => $model->event_id, 'sport_id' => $model->sport_id)),
        'color' => 'secondary',
        'icon' => 'fa-arrow-left',
        'id' => 'btn_back',
    ),
    array(
        'label' => 'Ghi kết quả',
        'url' => $this->createUrl('results', array('id' => $model->id)),
        'color' => 'success',
        'icon' => 'fa-pencil-square-o',
        'id' => 'btn_results',
    ),
    array(
        'label' => 'Gán thủ công',
        'url' => $this->createUrl('assign', array('id' => $model->id)),
        'color' => 'outline-primary',
        'icon' => 'fa-hand-rock-o',
        'id' => 'btn_assign',
    ),
);

// Register CSS & JS
Yii::app()->clientScript->registerCssFile(
    Yii::app()->theme->baseUrl . '/assets/css/pages/sportdraw-bracket.css?v=1.3'
);
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/plugins/toast.js',
    CClientScript::POS_END
);
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/sportdraw-view.js?v=1.5',
    CClientScript::POS_END
);

// Map team_id -> tên để hiển thị
$teamMap = array();
$teams = array();
// Nội dung đơn/đôi (cá nhân): hiển thị tên VĐV thay cho tên đội.
$sportNameLower = mb_strtolower((string) $model->sport_name, 'UTF-8');
$isIndividualSport = (strpos($sportNameLower, 'đơn') !== false || strpos($sportNameLower, 'đôi') !== false
    || (isset($model->stage_type) && $model->stage_type === 'individual'));
if ($preview && isset($preview['teams'])) {
    $teams = $preview['teams'];
    foreach ($teams as $t) {
        // Bỏ qua slot giữ chỗ (team_id null): nếu đưa vào map với khoá rỗng sẽ làm
        // mọi trận có đội chưa xác định hiển thị sai cùng một tên. Để các trận này
        // tự rơi về nhãn nguồn (team_a_source/team_b_source) = "Chờ Thắng Play-off X".
        if ($t['team_id'] === null || $t['team_id'] === '') {
            continue;
        }
        // Nội dung đơn (1 VĐV): hiển thị "Tên VĐV (Mã đơn vị)" thay cho tên đội.
        $players = isset($t['player_names']) && is_array($t['player_names']) ? $t['player_names'] : array();
        $unitCode = isset($t['unit_code']) && $t['unit_code'] !== null && $t['unit_code'] !== '' ? $t['unit_code'] : null;
        $names = array_values(array_filter($players, function ($p) {
            return $p !== null && $p !== '';
        }));
        // Nội dung đơn: 1 VĐV. Nội dung đôi: ghép tên 2 VĐV (" / "). Môn đồng đội: dùng tên đội.
        if (!empty($names) && ($isIndividualSport || count($names) === 1)) {
            $label = implode(' / ', $names) . ($unitCode ? ' (' . $unitCode . ')' : '');
        } else {
            $label = $t['team_name'] !== null ? $t['team_name'] : ('#' . $t['team_id']);
        }
        $teamMap[$t['team_id']] = $label;
    }
}

$matches = ($preview && isset($preview['matches'])) ? $preview['matches'] : array();

// =========================================================================
// PHÂN LOẠI TRẬN ĐẤU: VÒNG BẢNG VÀ VÒNG LOẠI TRỰC TIẾP
// =========================================================================
$groupMatches = array();
$koMatches = array();
$rounds = array();
$thirdPlace = null;
$finalMatch = null;
$championTeamId = null;
$championTeamName = null;

foreach ($matches as $m) {
    $type = $m['match_type'] ?? '';
    if ($type === 'group') {
        $groupMatches[$m['group_label'] ?? '?'][] = $m;
    } elseif ($type === 'playoff') {
        $thirdPlace = $m;
    } else {
        if ($type === 'final') {
            $finalMatch = $m;
        }
        $rounds[$m['round_no']][] = $m;
        $koMatches[] = $m;
    }
}
ksort($groupMatches);
ksort($rounds);

// Đếm số trận vòng bảng đã có kết quả
$totalGroupMatches = 0;
$completedGroupMatches = 0;
foreach ($groupMatches as $gLabel => $gList) {
    foreach ($gList as $gm) {
        $totalGroupMatches++;
        $gres = $gm['result'] ?? null;
        if ($gres && $gres['score_a'] !== null && $gres['score_b'] !== null && $gres['score_a'] !== '' && $gres['score_b'] !== '') {
            $completedGroupMatches++;
        }
    }
}
$hasGroupResults = ($completedGroupMatches > 0);

// Nếu thể thức là Vòng bảng + Knockout (round_robin_knockout) mà chưa có trận knockout nào trong dữ liệu:
// Tự động dựng khung cấu trúc sơ đồ nhánh (placeholder) để hiển thị nhánh thi đấu
if ($model->format === SportStages::FORMAT_ROUND_ROBIN_KNOCKOUT && empty($rounds)) {
    $mBK1 = array(
        'id' => 'ko_bk1', 'code' => 'BK1', 'round' => 'Bán kết', 'match_type' => 'knockout',
        'round_no' => 1, 'bracket_pos' => 1, 'team_a_id' => null, 'team_b_id' => null,
        'team_a_source' => 'Nhất Bảng A', 'team_b_source' => 'Nhì Bảng B',
        'winner_to_match_id' => 'ko_final', 'loser_to_match_id' => 'ko_third',
        'result' => null, 'match_time' => null, 'location' => null, 'match_order' => 101, 'is_bye' => 0,
    );
    $mBK2 = array(
        'id' => 'ko_bk2', 'code' => 'BK2', 'round' => 'Bán kết', 'match_type' => 'knockout',
        'round_no' => 1, 'bracket_pos' => 2, 'team_a_id' => null, 'team_b_id' => null,
        'team_a_source' => 'Nhất Bảng B', 'team_b_source' => 'Nhì Bảng A',
        'winner_to_match_id' => 'ko_final', 'loser_to_match_id' => 'ko_third',
        'result' => null, 'match_time' => null, 'location' => null, 'match_order' => 102, 'is_bye' => 0,
    );
    $mFinal = array(
        'id' => 'ko_final', 'code' => 'CK', 'round' => 'Chung kết', 'match_type' => 'final',
        'round_no' => 2, 'bracket_pos' => 1, 'team_a_id' => null, 'team_b_id' => null,
        'team_a_source' => 'Thắng Bán kết 1', 'team_b_source' => 'Thắng Bán kết 2',
        'winner_to_match_id' => null, 'loser_to_match_id' => null,
        'result' => null, 'match_time' => null, 'location' => null, 'match_order' => 103, 'is_bye' => 0,
    );
    $mThird = array(
        'id' => 'ko_third', 'code' => 'TRANH-HANG-3', 'round' => 'Tranh hạng 3', 'match_type' => 'playoff',
        'round_no' => 2, 'bracket_pos' => 2, 'team_a_id' => null, 'team_b_id' => null,
        'team_a_source' => 'Thua Bán kết 1', 'team_b_source' => 'Thua Bán kết 2',
        'winner_to_match_id' => null, 'loser_to_match_id' => null,
        'result' => null, 'match_time' => null, 'location' => null, 'match_order' => 104, 'is_bye' => 0,
    );

    if ($model->ko_pairing === SportStages::PAIRING_PLACEMENT) {
        // Xếp hạng theo thứ hạng bảng: không có bán kết
        $mFinal = array_merge($mFinal, array(
            'round' => 'Chung kết (Tranh 1-2)', 'round_no' => 1,
            'team_a_source' => 'Nhất Bảng A', 'team_b_source' => 'Nhất Bảng B',
        ));
        $mThird = array_merge($mThird, array(
            'round' => 'Tranh hạng 3-4', 'round_no' => 1,
            'team_a_source' => 'Nhì Bảng A', 'team_b_source' => 'Nhì Bảng B',
        ));
        $rounds[1] = array($mFinal);
        $koMatches = array($mFinal);
    } else {
        $rounds[1] = array($mBK1, $mBK2);
        $rounds[2] = array($mFinal);
        $koMatches = array($mBK1, $mBK2, $mFinal);
    }
    $finalMatch = $mFinal;
    $thirdPlace = $mThird;
}

if ($finalMatch) {
    $fRes = $finalMatch['result'] ?? null;
    if ($fRes && !empty($fRes['winner_team_id'])) {
        $championTeamId = $fRes['winner_team_id'];
        $championTeamName = $teamMap[$championTeamId] ?? ('#' . $championTeamId);
    }
}

// Match lookup & Feeder hierarchy mapping
$matchMap = array();
$feedersOf = array();
$loserFeedersOf = array();

$allActiveMatches = array_merge($matches, $koMatches);
if ($thirdPlace && !isset($matchMap[$thirdPlace['id']])) {
    $allActiveMatches[] = $thirdPlace;
}

foreach ($allActiveMatches as $m) {
    $matchMap[$m['id']] = $m;
    if (!empty($m['winner_to_match_id'])) {
        $feedersOf[$m['winner_to_match_id']][] = $m;
    }
    if (!empty($m['loser_to_match_id'])) {
        $loserFeedersOf[$m['loser_to_match_id']][] = $m;
    }
}

/**
 * Tên đội hoặc nguồn chờ thông minh
 * Khi chưa có kết quả vòng bảng (completedGroupMatches == 0) ở thể thức round_robin_knockout,
 * các slot knockout KHÔNG điền đội thật mà hiển thị nguồn chờ: Nhất Bảng A, Nhì Bảng B...
 */
$getSlotLabel = function ($teamId, $explicitSource, $match, $slotIndex) use ($teamMap, $feedersOf, $loserFeedersOf, $completedGroupMatches, $model) {
    $matchType = $match['match_type'] ?? '';
    $roundNo = (int)($match['round_no'] ?? 1);
    $isKnockoutRound1 = ($matchType === 'knockout' && $roundNo === 1);
    $isRoundRobinKnockout = ($model->format === SportStages::FORMAT_ROUND_ROBIN_KNOCKOUT);

    // Xác định nguồn mặc định theo nhánh chéo
    $defaultSource = '';
    if ($isKnockoutRound1 && $isRoundRobinKnockout) {
        $pos = (int)($match['bracket_pos'] ?? 1);
        if ($pos === 1) {
            $defaultSource = ($slotIndex === 0) ? 'Nhất Bảng A' : 'Nhì Bảng B';
        } elseif ($pos === 2) {
            $defaultSource = ($slotIndex === 0) ? 'Nhất Bảng B' : 'Nhì Bảng A';
        } else {
            $gLabel = chr(ord('A') + ($pos - 1));
            $defaultSource = ($slotIndex === 0) ? "Nhất Bảng $gLabel" : "Nhì Bảng $gLabel";
        }
    }

    $sourceLabel = !empty($explicitSource) ? $explicitSource : $defaultSource;

    // Chưa có dữ liệu gì ở vòng bảng thì toàn bộ Sơ đồ loại trực tiếp chưa điền đội!
    if ($isRoundRobinKnockout && $completedGroupMatches === 0) {
        if ($isKnockoutRound1) {
            return array('name' => !empty($sourceLabel) ? $sourceLabel : 'Chờ xác định', 'isTeam' => false, 'source' => $sourceLabel);
        }
        if ($matchType === 'playoff') {
            $pSource = ($slotIndex === 0) ? 'Thua Bán kết 1' : 'Thua Bán kết 2';
            return array('name' => !empty($explicitSource) ? $explicitSource : $pSource, 'isTeam' => false, 'source' => $pSource);
        }
        if ($matchType === 'final' || $roundNo > 1) {
            $fSource = ($slotIndex === 0) ? 'Thắng Bán kết 1' : 'Thắng Bán kết 2';
            return array('name' => !empty($explicitSource) ? $explicitSource : $fSource, 'isTeam' => false, 'source' => $fSource);
        }
    }

    // Đã có đội hợp lệ
    if ($teamId && isset($teamMap[$teamId])) {
        return array('name' => $teamMap[$teamId], 'isTeam' => true, 'source' => $sourceLabel);
    }

    // Nguồn chờ từ source cấu hình
    if (!empty($sourceLabel)) {
        return array('name' => $sourceLabel, 'isTeam' => false, 'source' => $sourceLabel);
    }

    // Dò tìm từ nhánh vòng trước
    if ($matchType === 'playoff' && !empty($loserFeedersOf[$match['id']][$slotIndex])) {
        $f = $loserFeedersOf[$match['id']][$slotIndex];
        return array('name' => 'Thua ' . ($f['round'] ?? 'Trận') . ' ' . ($f['bracket_pos'] ?? ''), 'isTeam' => false);
    }
    if (!empty($feedersOf[$match['id']][$slotIndex])) {
        $f = $feedersOf[$match['id']][$slotIndex];
        return array('name' => 'Thắng ' . ($f['round'] ?? 'Trận') . ' ' . ($f['bracket_pos'] ?? ''), 'isTeam' => false);
    }
    return array('name' => 'Chờ xác định', 'isTeam' => false);
};
?>

<?php if (!$preview): ?>
    <div class="alert alert-warning d-flex align-items-center">
        <i class="fa fa-exclamation-triangle fs-4 me-3"></i>
        <div>Chưa có dữ liệu bốc thăm cho giai đoạn này. Vui lòng tiến hành bốc thăm hoặc gán đội trước.</div>
    </div>
<?php else: ?>

    <div id="sport-draw-view-root"
         data-stage-id="<?php echo CHtml::encode($model->id); ?>"
         data-event-id="<?php echo CHtml::encode($model->event_id); ?>"
         data-sport-id="<?php echo CHtml::encode($model->sport_id); ?>"
         data-save-result-url="<?php echo $this->createUrl('saveResult'); ?>"
         data-update-match-url="<?php echo $this->createUrl('updateMatch'); ?>"
         data-delete-match-url="<?php echo $this->createUrl('deleteMatch'); ?>"
         data-reorder-matches-url="<?php echo $this->createUrl('reorderMatches'); ?>"
         data-standings-url="<?php echo $this->createUrl('standings', array('id' => $model->id)); ?>"
         data-generate-knockout-url="<?php echo $this->createUrl('generateKnockout', array('id' => $model->id)); ?>"
         data-reserve-playoff-url="<?php echo $this->createUrl('reservePlayoff', array('id' => $model->id)); ?>"
         data-reserve-group-winners-url="<?php echo $this->createUrl('reserveGroupWinners', array('id' => $model->id)); ?>"
         data-total-group-matches="<?php echo (int) $totalGroupMatches; ?>"
         data-completed-group-matches="<?php echo (int) $completedGroupMatches; ?>">

        <!-- 1. Header Banner Giai đoạn -->
        <div class="sportdraw-header-card">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary px-2 py-1"><i class="fa fa-trophy me-1"></i><?php echo CHtml::encode($model->name); ?></span>
                        <span class="text-muted small">Giai đoạn ID: #<?php echo CHtml::encode($model->id); ?></span>
                    </div>
                    <h4 class="mb-0 fw-bold text-dark"><?php echo CHtml::encode(SportStages::getFormatLabel($model->format)); ?></h4>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="sportdraw-stat-pill">
                        <i class="fa fa-info-circle"></i>
                        <span>Trạng thái: <strong><?php echo SportStages::getDrawStatusLabel($preview['draw_status']); ?></strong></span>
                    </div>
                    <div class="sportdraw-stat-pill">
                        <i class="fa fa-users"></i>
                        <span>Đội tham gia: <strong><?php echo count($teams); ?></strong></span>
                    </div>
                    <div class="sportdraw-stat-pill">
                        <i class="fa fa-calendar-check-o"></i>
                        <span>Trận đấu: <strong><?php echo count($matches); ?></strong></span>
                    </div>
                    <?php if ($model->format === SportStages::FORMAT_ROUND_ROBIN_KNOCKOUT): ?>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-genko-top"
                                onclick="if(confirm('Sinh sơ đồ loại trực tiếp từ bảng xếp hạng hiện tại?')){ fetch('<?php echo $this->createUrl('generateKnockout', array('id' => $model->id)); ?>', {method:'POST'}).then(r=>r.json()).then(res=>{ if(res.success){ alert('Đã sinh sơ đồ nhánh!'); location.reload(); }else{ alert(res.message||'Lỗi sinh nhánh'); } }); }">
                            <i class="fa fa-sitemap me-1"></i>Sinh nhánh từ BXH
                        </button>
                    <?php endif; ?>
                    <?php if ($playoffSource): ?>
                        <button type="button" class="btn btn-sm btn-outline-warning" id="btn-reserve-playoff"
                                data-source-stage-id="<?php echo (int) $playoffSource; ?>"
                                title="Thêm slot giữ chỗ đội thắng play-off vào các bảng (không xáo lại đội đã bốc)">
                            <i class="fa fa-user-plus me-1"></i>Đặt chỗ Play-off
                        </button>
                    <?php endif; ?>
                    <?php if ($qualSource): ?>
                        <button type="button" class="btn btn-sm btn-outline-warning" id="btn-reserve-group-winners"
                                data-qual-source-stage-id="<?php echo (int) $qualSource; ?>"
                                title="Tạo slot giữ chỗ Nhất bảng vòng loại; đội thật tự điền khi bảng đó đá xong">
                            <i class="fa fa-user-plus me-1"></i>Lấy Nhất bảng vòng loại
                        </button>
                    <?php endif; ?>
                    <a href="<?php echo $this->createUrl('results', array('id' => $model->id)); ?>" class="btn btn-sm btn-success">
                        <i class="fa fa-pencil-square-o me-1"></i>Ghi kết quả (Màn riêng)
                    </a>
                </div>
            </div>
        </div>

        <?php
        // Nhóm đội theo bảng
        $groups = array();
        foreach ($teams as $t) {
            if (!empty($t['group_label'])) {
                $groups[$t['group_label']][] = $t;
            }
        }
        ksort($groups);

        // =========================================================================
        // BẢNG XẾP HẠNG CỦA TỪNG BẢNG (TỰ ĐỘNG TÍNH TOÁN NẾU API CHƯA CÓ)
        // =========================================================================
        $standingsByGroup = array();
        $rawStandings = isset($preview['standings']) ? $preview['standings'] : array();
        foreach ($rawStandings as $s) {
            if (!empty($s['group_label'])) {
                $standingsByGroup[$s['group_label']][] = $s;
            }
        }

        // Điểm thắng/hoà/thua theo cấu hình giai đoạn (mặc định 3-1-0)
        $winPts = ($model->win_points !== null && $model->win_points !== '') ? (int) $model->win_points : 3;
        $drawPts = ($model->draw_points !== null && $model->draw_points !== '') ? (int) $model->draw_points : 1;
        $lossPts = ($model->loss_points !== null && $model->loss_points !== '') ? (int) $model->loss_points : 0;

        // Luôn đảm bảo tất cả các bảng đều có Bảng Xếp Hạng
        foreach ($groups as $label => $members) {
            if (empty($standingsByGroup[$label])) {
                $computed = array();
                foreach ($members as $mem) {
                    $tid = $mem['team_id'];
                    // Slot giữ chỗ play-off (team_id null) được bổ sung riêng ở bước sau
                    if ($tid === null || $tid === '') {
                        continue;
                    }
                    $computed[$tid] = array(
                        'group_label' => $label,
                        'team_id' => $tid,
                        'team_name' => $teamMap[$tid] ?? ('#' . $tid),
                        'rank' => 1,
                        'played' => 0,
                        'won' => 0,
                        'drawn' => 0,
                        'lost' => 0,
                        'points_scored' => 0,
                        'points_conceded' => 0,
                        'goal_diff' => 0,
                        'points' => 0,
                    );
                }

                if (!empty($groupMatches[$label])) {
                    foreach ($groupMatches[$label] as $gm) {
                        $res = $gm['result'] ?? null;
                        if ($res && $res['score_a'] !== null && $res['score_b'] !== null && $res['score_a'] !== '' && $res['score_b'] !== '') {
                            $sa = (int) $res['score_a'];
                            $sb = (int) $res['score_b'];
                            $ta = $gm['team_a_id'];
                            $tb = $gm['team_b_id'];
                            if (isset($computed[$ta]) && isset($computed[$tb])) {
                                $computed[$ta]['played']++;
                                $computed[$tb]['played']++;
                                $computed[$ta]['points_scored'] += $sa;
                                $computed[$ta]['points_conceded'] += $sb;
                                $computed[$tb]['points_scored'] += $sb;
                                $computed[$tb]['points_conceded'] += $sa;

                                if ($sa > $sb) {
                                    $computed[$ta]['won']++;
                                    $computed[$ta]['points'] += $winPts;
                                    $computed[$tb]['lost']++;
                                    $computed[$tb]['points'] += $lossPts;
                                } elseif ($sb > $sa) {
                                    $computed[$tb]['won']++;
                                    $computed[$tb]['points'] += $winPts;
                                    $computed[$ta]['lost']++;
                                    $computed[$ta]['points'] += $lossPts;
                                } else {
                                    $computed[$ta]['drawn']++;
                                    $computed[$ta]['points'] += $drawPts;
                                    $computed[$tb]['drawn']++;
                                    $computed[$tb]['points'] += $drawPts;
                                }
                            }
                        }
                    }
                }

                foreach ($computed as $tid => $row) {
                    $computed[$tid]['goal_diff'] = $row['points_scored'] - $row['points_conceded'];
                }

                usort($computed, function ($a, $b) {
                    if ($b['points'] !== $a['points']) return $b['points'] - $a['points'];
                    if ($b['goal_diff'] !== $a['goal_diff']) return $b['goal_diff'] - $a['goal_diff'];
                    if ($b['points_scored'] !== $a['points_scored']) return $b['points_scored'] - $a['points_scored'];
                    return 0;
                });

                $rNum = 1;
                foreach ($computed as $idx => $row) {
                    $computed[$idx]['rank'] = $rNum++;
                }
                $standingsByGroup[$label] = $computed;
            }
        }

        // Bổ sung các suất giữ chỗ Play-off vào cuối BXH mỗi bảng, ghi rõ "Đội thắng Play-off X".
        // Nguồn tin cậy là các trận vòng bảng: vế có team_id trống nhưng có nhãn nguồn
        // (team_a_source / team_b_source) kiểu "Đội thắng Play-off 1" chính là suất chờ của bảng đó.
        $groupLabelsAll = array_unique(array_merge(array_keys($standingsByGroup), array_keys($groupMatches)));
        foreach ($groupLabelsAll as $label) {
            if (empty($groupMatches[$label])) {
                continue;
            }
            // Thu thập các nguồn chờ (distinct) trong bảng
            $slotSources = array();
            foreach ($groupMatches[$label] as $gm) {
                $sides = array(
                    array($gm['team_a_id'] ?? null, $gm['team_a_source'] ?? null),
                    array($gm['team_b_id'] ?? null, $gm['team_b_source'] ?? null),
                );
                foreach ($sides as $side) {
                    list($tid, $src) = $side;
                    if (($tid === null || $tid === '') && !empty($src)) {
                        $slotSources[$src] = true;
                    }
                }
            }
            if (empty($slotSources)) {
                continue;
            }
            if (!isset($standingsByGroup[$label])) {
                $standingsByGroup[$label] = array();
            }
            foreach (array_keys($slotSources) as $src) {
                // Tránh thêm trùng nếu đã có dòng giữ chỗ cùng nhãn
                $already = false;
                foreach ($standingsByGroup[$label] as $row) {
                    if (!empty($row['is_playoff_slot']) && isset($row['team_name']) && $row['team_name'] === $src) {
                        $already = true;
                        break;
                    }
                }
                if ($already) {
                    continue;
                }
                $standingsByGroup[$label][] = array(
                    'group_label' => $label,
                    'team_id' => null,
                    'team_name' => $src,
                    'is_playoff_slot' => true,
                    'rank' => count($standingsByGroup[$label]) + 1,
                    'played' => 0,
                    'won' => 0,
                    'drawn' => 0,
                    'lost' => 0,
                    'points_scored' => 0,
                    'points_conceded' => 0,
                    'goal_diff' => 0,
                    'points' => 0,
                );
            }
        }
        ksort($standingsByGroup);
        $advancePerGroup = (int) ($model->advance_per_group ?: 2);
        ?>

        <!-- 2. BẢNG XẾP HẠNG TỪNG BẢNG (GROUP STANDINGS) -->
        <?php if (!empty($standingsByGroup)): ?>
            <?php
            $isFinalStage = (isset($model->stage_type) && $model->stage_type === 'final');
            $groupCount = count($standingsByGroup);
            $groupColClass = ($groupCount === 1) ? 'col-lg-8 mx-auto mb-3' : 'col-lg-6 mb-3';
            $isPureRoundRobinFinal = ($isFinalStage && $groupCount === 1 && empty($rounds) && $model->format === SportStages::FORMAT_ROUND_ROBIN);
            ?>
            <div class="card mb-4 border-0 shadow-sm <?php echo $isPureRoundRobinFinal ? 'border-top border-4 border-warning' : ''; ?>">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div>
                            <h5 class="mb-0 fw-bold text-dark">
                                <?php if ($isPureRoundRobinFinal): ?>
                                    <i class="fa fa-trophy text-warning me-2"></i>Bảng xếp hạng Vòng chung kết (Tranh Huy chương Vàng • Bạc • Đồng)
                                <?php else: ?>
                                    <i class="fa fa-list-ol text-primary me-2"></i>Bảng xếp hạng từng bảng
                                <?php endif; ?>
                            </h5>
                            <small class="text-muted">
                                <?php if ($isPureRoundRobinFinal): ?>
                                    <?php echo $hasGroupResults ? 'Kết quả thi đấu vòng tròn xếp hạng: 🥇 Nhất (HCV) • 🥈 Nhì (HCB) • 🥉 Ba (HCĐ)' : 'Chưa có kết quả thi đấu vòng chung kết'; ?>
                                <?php else: ?>
                                    Cập nhật điểm số, hiệu số và thứ hạng của các đội sau mỗi trận đấu vòng bảng
                                <?php endif; ?>
                            </small>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <?php if ($isPureRoundRobinFinal): ?>
                                <span class="badge bg-warning text-dark border">
                                    <i class="fa fa-trophy me-1"></i>Tranh Huy chương 1 - 2 - 3
                                </span>
                            <?php else: ?>
                                <span class="badge bg-light text-muted border">
                                    Top <?php echo $advancePerGroup; ?> đội mỗi bảng vào vòng trong
                                </span>
                            <?php endif; ?>
                            <?php if ($model->format === SportStages::FORMAT_ROUND_ROBIN_KNOCKOUT): ?>
                                <button type="button" class="btn btn-sm btn-primary shadow-sm" id="btn-push-to-bracket" title="Cập nhật các đội xếp đầu bảng vào Sơ đồ loại trực tiếp">
                                    <i class="fa fa-refresh me-1"></i>Cập nhật vào Sơ đồ nhánh
                                </button>
                            <?php endif; ?>
                            <button type="button" class="btn btn-sm btn-outline-success" id="btn-recompute-standings" title="Tính lại BXH qua API">
                                <i class="fa fa-refresh me-1"></i>Tính lại BXH
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body p-3">
                    <div class="row">
                        <?php foreach ($standingsByGroup as $label => $rows): ?>
                            <div class="<?php echo $groupColClass; ?>">
                                <div class="sportdraw-table-card h-100 <?php echo $isPureRoundRobinFinal ? 'shadow-sm' : ''; ?>">
                                    <div class="sportdraw-table-header <?php echo $isPureRoundRobinFinal ? 'bg-light' : ''; ?>">
                                        <span class="fw-bold fs-6 text-dark">
                                            <?php if ($isPureRoundRobinFinal): ?>
                                                <i class="fa fa-trophy text-warning me-2"></i>Bảng Chung kết
                                            <?php else: ?>
                                                <i class="fa fa-flag text-primary me-2"></i>Bảng <?php echo CHtml::encode($label); ?>
                                            <?php endif; ?>
                                        </span>
                                        <span class="badge bg-light text-muted border"><?php echo count($rows); ?> đội</span>
                                    </div>
                                    <div class="table-responsive mb-0">
                                        <table class="table table-hover align-middle mb-0 text-center group-standings-table"
                                               data-group-label="<?php echo CHtml::encode($label); ?>"
                                               data-is-medal-table="<?php echo $isPureRoundRobinFinal ? '1' : '0'; ?>"
                                               data-advance-count="<?php echo (int) $advancePerGroup; ?>">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 44px;">#</th>
                                                    <th class="text-start">Đội / VĐV</th>
                                                    <th title="Trận đã đấu">Trận</th>
                                                    <th title="Thắng" class="text-success">T</th>
                                                    <th title="Hòa">H</th>
                                                    <th title="Bại" class="text-danger">B</th>
                                                    <th title="Bàn thắng : Bàn thua">Hiệu số</th>
                                                    <th title="Điểm" class="text-primary">Điểm</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($rows as $r): ?>
                                                <?php
                                                $rank = (int)$r['rank'];
                                                $played = (int)$r['played'];
                                                $hasPlayed = ($played > 0);
                                                $rankClass = ($hasPlayed && $isPureRoundRobinFinal)
                                                    ? (($rank === 1) ? 'rank-gold' : (($rank === 2) ? 'rank-silver' : (($rank === 3) ? 'rank-bronze' : 'bg-light text-muted')))
                                                    : 'bg-light text-muted';
                                                $isPlayoffSlot = !empty($r['is_playoff_slot']);
                                                $tName = $teamMap[$r['team_id']] ?? ($r['team_name'] ?? ('#' . $r['team_id']));
                                                $isQualified = (!$isPlayoffSlot && $hasPlayed && $rank <= $advancePerGroup);
                                                ?>
                                                <tr data-team-id="<?php echo CHtml::encode($r['team_id']); ?>"
                                                    class="<?php echo (!$isPureRoundRobinFinal && $isQualified) ? 'standings-qualify-row' : ($isPlayoffSlot ? 'table-warning' : ''); ?>">
                                                    <td>
                                                        <span class="rank-badge <?php echo $rankClass; ?>">
                                                            <?php echo $rank; ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-start fw-semibold <?php echo $isPlayoffSlot ? 'text-warning-emphasis fst-italic' : 'text-dark'; ?>">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span><?php echo CHtml::encode($tName); ?></span>
                                                            <?php if ($isPlayoffSlot): ?>
                                                                <span class="badge bg-warning text-dark" title="Suất giữ chỗ cho đội thắng play-off"><i class="fa fa-hourglass-half me-1"></i>Chờ Play-off</span>
                                                            <?php elseif ($isPureRoundRobinFinal && $hasPlayed): ?>
                                                                <?php if ($rank === 1): ?>
                                                                    <span class="badge bg-warning text-dark fw-bold"><i class="fa fa-trophy me-1"></i>HCV (Nhất)</span>
                                                                <?php elseif ($rank === 2): ?>
                                                                    <span class="badge bg-secondary text-white fw-bold"><i class="fa fa-star me-1"></i>HCB (Nhì)</span>
                                                                <?php elseif ($rank === 3): ?>
                                                                    <span class="badge text-white fw-bold" style="background:#cd7f32;"><i class="fa fa-star-half-o me-1"></i>HCĐ (Ba)</span>
                                                                <?php endif; ?>
                                                            <?php elseif (!$isPureRoundRobinFinal && $isQualified): ?>
                                                                <span class="standings-qualify-badge" title="Đủ điều kiện vào vòng loại trực tiếp">Vào vòng trong</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td class="col-played"><?php echo $played; ?></td>
                                                    <td class="col-won text-success fw-bold"><?php echo (int) $r['won']; ?></td>
                                                    <td class="col-drawn"><?php echo (int) $r['drawn']; ?></td>
                                                    <td class="col-lost text-danger"><?php echo (int) $r['lost']; ?></td>
                                                    <td class="col-diff">
                                                        <span class="badge bg-light text-dark border">
                                                            <?php echo ((int)$r['points_scored']) . ':' . ((int)$r['points_conceded']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="col-pts fw-bold fs-6 text-primary"><?php echo (int) $r['points']; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- 3. DANH SÁCH & LỊCH THI ĐẤU (CÓ THỂ KÉO THẢ, GHI KẾT QUẢ, ĐẶT GIỜ, SẮP XẾP THỜI GIAN) -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fa fa-calendar text-primary me-2"></i>Lịch thi đấu & Kéo sắp xếp trận đấu
                        </h5>
                        <small class="text-muted">Kéo thả biểu tượng <i class="fa fa-ellipsis-v text-muted"></i> để sắp xếp thứ tự thi đấu, hoặc sắp xếp tự động theo thời gian bắt đầu</small>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-sort-by-time" title="Tự động sắp xếp các trận đấu theo thời gian bắt đầu tăng dần">
                            <i class="fa fa-sort-amount-asc me-1"></i>Sắp xếp theo thứ tự thời gian
                        </button>
                        <button type="button" class="btn btn-sm btn-success shadow-sm" id="btn-save-match-order" style="display: none;" title="Lưu lại thứ tự thi đấu vừa sắp xếp">
                            <i class="fa fa-floppy-o me-1"></i>Lưu thứ tự thi đấu
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body p-3">
                <?php
                // Sắp xếp các trận: ưu tiên match_order nếu có
                $allDisplayMatches = $matches;
                usort($allDisplayMatches, function ($a, $b) {
                    $ordA = $a['match_order'] !== null ? (int)$a['match_order'] : 9999;
                    $ordB = $b['match_order'] !== null ? (int)$b['match_order'] : 9999;
                    if ($ordA !== $ordB) return $ordA - $ordB;
                    return ($a['id'] ?? 0) - ($b['id'] ?? 0);
                });
                ?>

                <div class="sortable-matches-container">
                    <?php foreach ($allDisplayMatches as $idx => $m): ?>
                        <?php
                        $mRes = $m['result'] ?? null;
                        $hasScore = ($mRes !== null && $mRes['score_a'] !== null && $mRes['score_b'] !== null && $mRes['score_a'] !== '' && $mRes['score_b'] !== '');
                        $sA = $hasScore ? $mRes['score_a'] : '';
                        $sB = $hasScore ? $mRes['score_b'] : '';
                        $wId = $mRes['winner_team_id'] ?? null;
                        $teamAName = $teamMap[$m['team_a_id']] ?? ($m['team_a_source'] ? ('Chờ ' . $m['team_a_source']) : 'Chờ xác định');
                        $teamBName = $teamMap[$m['team_b_id']] ?? ($m['team_b_source'] ? ('Chờ ' . $m['team_b_source']) : 'Chờ xác định');
                        $orderVal = $m['match_order'] ?: ($idx + 1);
                        ?>

                        <div class="sortable-match-item"
                             draggable="true"
                             data-match-id="<?php echo CHtml::encode($m['id']); ?>"
                             data-match-code="<?php echo CHtml::encode($m['code'] ?: ('M' . $m['id'])); ?>"
                             data-round-name="<?php echo CHtml::encode($m['round']); ?>"
                             data-match-type="<?php echo CHtml::encode($m['match_type']); ?>"
                             data-group-label="<?php echo CHtml::encode($m['group_label'] ?: ''); ?>"
                             data-team-a-id="<?php echo CHtml::encode($m['team_a_id'] ?: ''); ?>"
                             data-team-b-id="<?php echo CHtml::encode($m['team_b_id'] ?: ''); ?>"
                             data-team-a-name="<?php echo CHtml::encode($teamAName); ?>"
                             data-team-b-name="<?php echo CHtml::encode($teamBName); ?>"
                             data-score-a="<?php echo CHtml::encode($sA); ?>"
                             data-score-b="<?php echo CHtml::encode($sB); ?>"
                             data-winner-id="<?php echo CHtml::encode($wId ?: ''); ?>"
                             data-is-draw="<?php echo (!empty($mRes['is_draw'])) ? '1' : '0'; ?>"
                             data-match-time="<?php echo CHtml::encode($m['match_time'] ?: ''); ?>"
                             data-location="<?php echo CHtml::encode($m['location'] ?: ''); ?>"
                             data-match-order="<?php echo CHtml::encode($orderVal); ?>">

                            <!-- Khối 1: Kéo thả & Thứ tự & Vòng đấu -->
                            <div class="d-flex align-items-center gap-2">
                                <div class="drag-handle" title="Kéo để đổi thứ tự thi đấu">
                                    <i class="fa fa-ellipsis-v"></i><i class="fa fa-ellipsis-v"></i>
                                </div>
                                <span class="match-order-pill" title="Thứ tự trận">
                                    #<span class="order-num"><?php echo $orderVal; ?></span>
                                </span>
                                <span class="badge bg-light text-dark border">
                                    <?php echo CHtml::encode($m['round']); ?>
                                </span>
                                <span class="text-muted small fw-semibold">
                                    #<?php echo CHtml::encode($m['code'] ?: ('M' . $m['id'])); ?>
                                </span>
                            </div>

                            <!-- Khối 2: Cặp đấu & Tỉ số -->
                            <div class="d-flex align-items-center justify-content-center gap-3 flex-grow-1 px-3">
                                <div class="text-end fw-semibold text-truncate" style="max-width: 220px;" title="<?php echo CHtml::encode($teamAName); ?>">
                                    <?php echo CHtml::encode($teamAName); ?>
                                    <?php if ($wId && $wId == $m['team_a_id']): ?>
                                        <i class="fa fa-trophy text-warning ms-1" title="Thắng"></i>
                                    <?php endif; ?>
                                </div>

                                <div class="badge <?php echo $hasScore ? 'bg-primary text-white' : 'bg-light text-muted border'; ?> px-3 py-2 fs-6 shadow-sm match-score-pill">
                                    <?php if ($hasScore): ?>
                                        <strong><?php echo $sA; ?> - <?php echo $sB; ?></strong>
                                    <?php else: ?>
                                        <span>vs</span>
                                    <?php endif; ?>
                                </div>

                                <div class="text-start fw-semibold text-truncate" style="max-width: 220px;" title="<?php echo CHtml::encode($teamBName); ?>">
                                    <?php if ($wId && $wId == $m['team_b_id']): ?>
                                        <i class="fa fa-trophy text-warning me-1" title="Thắng"></i>
                                    <?php endif; ?>
                                    <?php echo CHtml::encode($teamBName); ?>
                                </div>
                            </div>

                            <!-- Khối 3: Thời gian, Địa điểm sân & Thao tác ghi kết quả -->
                            <div class="d-flex align-items-center gap-2">
                                <!-- Nút đặt/sửa giờ bắt đầu -->
                                <button type="button"
                                        class="match-time-btn <?php echo !empty($m['match_time']) ? 'has-time' : ''; ?>"
                                        title="Nhấn để thiết lập thời gian bắt đầu và địa điểm">
                                    <?php if (!empty($m['match_time'])): ?>
                                        <i class="fa fa-clock-o me-1"></i><?php echo date('H:i d/m/Y', strtotime($m['match_time'])); ?>
                                    <?php else: ?>
                                        <i class="fa fa-calendar-plus-o me-1"></i>Đặt giờ & sân
                                    <?php endif; ?>
                                </button>

                                <!-- Huy hiệu địa điểm nếu có -->
                                <span class="badge bg-light text-muted border location-badge"
                                      style="<?php echo empty($m['location']) ? 'display:none;' : ''; ?>">
                                    <i class="fa fa-map-marker text-danger me-1"></i><?php echo CHtml::encode($m['location'] ?: ''); ?>
                                </span>

                                <!-- Nút Ghi tỉ số -->
                                <button type="button" class="btn btn-sm btn-primary btn-quick-result" title="Ghi tỉ số và kết quả">
                                    <i class="fa fa-pencil me-1"></i>Ghi kết quả
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- 4. SƠ ĐỒ LOẠI TRỰC TIẾP (KNOCKOUT BRACKET TREE WITH CONNECTORS) - NẾU CÓ CÁC VÒNG KNOCKOUT -->
        <?php if (!empty($rounds)): ?>
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div>
                            <h5 class="mb-0 fw-bold text-dark">
                                <i class="fa fa-sitemap text-primary me-2"></i>Sơ đồ thi đấu loại trực tiếp (Nhánh thi đấu có đường nối)
                            </h5>
                            <small class="text-muted">Nhấn vào từng trận trên sơ đồ để ghi kết quả hoặc đặt giờ thi đấu</small>
                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <?php if ($model->format === SportStages::FORMAT_ROUND_ROBIN_KNOCKOUT): ?>
                                <button type="button" class="btn btn-sm btn-primary shadow-sm" id="btn-sync-bracket-teams"
                                        title="Cập nhật danh sách đội từ BXH các bảng vào sơ đồ loại trực tiếp">
                                    <i class="fa fa-refresh me-1"></i>Cập nhật danh sách đội từ BXH
                                </button>
                            <?php endif; ?>
                            <span class="badge bg-light text-muted border">Kéo rê để di chuyển vùng nhìn</span>
                        </div>
                    </div>
                </div>

                <?php if ($model->format === SportStages::FORMAT_ROUND_ROBIN_KNOCKOUT && $completedGroupMatches === 0): ?>
                    <div class="alert alert-info py-2 px-3 mb-0 border-0 rounded-0 d-flex align-items-center justify-content-between gap-2" style="background: #eff6ff; font-size: 0.825rem;">
                        <div>
                            <i class="fa fa-info-circle text-primary me-1"></i>
                            <strong>Chế độ xem trước nhánh thi đấu:</strong> Các nhánh Bán kết đang hiển thị nguồn chờ (<em>Nhất Bảng A vs Nhì Bảng B</em>, <em>Nhất Bảng B vs Nhì Bảng A</em>). Sau khi có kết quả thi đấu vòng bảng, nhấn nút <strong>"Cập nhật danh sách đội từ BXH"</strong> để tự động điền các đội vào sơ đồ.
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Toolbar điều khiển sơ đồ -->
                <div class="bracket-toolbar">
                    <div class="bracket-controls-group">
                        <button type="button" class="bracket-btn-tool" id="btn-bracket-zoomin" title="Thu phóng to (Zoom In)">
                            <i class="fa fa-search-plus"></i>
                        </button>
                        <button type="button" class="bracket-btn-tool" id="btn-bracket-zoomreset" title="Tỷ lệ 100%">
                            <span id="bracket-zoom-level">100%</span>
                        </button>
                        <button type="button" class="bracket-btn-tool" id="btn-bracket-zoomout" title="Thu nhỏ (Zoom Out)">
                            <i class="fa fa-search-minus"></i>
                        </button>
                        <button type="button" class="bracket-btn-tool" id="btn-bracket-toggle-lines" title="Đổi kiểu đường nối">
                            <i class="fa fa-code-fork"></i>
                            <span class="mode-label">Góc vuông</span>
                        </button>
                    </div>

                    <div class="bracket-controls-group">
                        <div class="input-group input-group-sm" style="width: 230px;">
                            <span class="input-group-text bg-white border-end-0"><i class="fa fa-filter text-muted"></i></span>
                            <select class="form-select form-select-sm border-start-0" id="bracket-team-filter">
                                <option value="">-- Theo dõi đường đi của đội --</option>
                                <?php foreach ($teams as $t): ?>
                                    <?php if ($t['team_id'] === null || $t['team_id'] === '') continue; ?>
                                    <option value="<?php echo CHtml::encode($t['team_id']); ?>">
                                        <?php echo CHtml::encode($teamMap[$t['team_id']] ?? ($t['team_name'] ?: ('#' . $t['team_id']))); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="bracket-controls-group">
                        <button type="button" class="bracket-btn-tool" id="btn-bracket-fullscreen" title="Xem toàn màn hình">
                            <i class="fa fa-arrows-alt me-1"></i>Toàn màn hình
                        </button>
                        <button type="button" class="bracket-btn-tool" id="btn-bracket-print" title="In sơ đồ thi đấu">
                            <i class="fa fa-print me-1"></i>In
                        </button>
                    </div>

                    <div class="d-none d-lg-flex align-items-center gap-3">
                        <div class="bracket-legend-item">
                            <span class="bracket-legend-dot" style="background: #10b981;"></span>
                            <span>Đã có tỉ số</span>
                        </div>
                        <div class="bracket-legend-item">
                            <span class="bracket-legend-dot" style="background: #3b82f6;"></span>
                            <span>Sẵn sàng đấu</span>
                        </div>
                        <div class="bracket-legend-item">
                            <span class="bracket-legend-dot" style="background: #f59e0b;"></span>
                            <span>Chung kết / Cup</span>
                        </div>
                    </div>
                </div>

                <!-- Khung vẽ Sơ đồ (Viewport) -->
                <div class="bracket-viewport" id="bracket-viewport">
                    <button type="button" class="btn btn-dark btn-sm bracket-fullscreen-close shadow" id="btn-bracket-fullscreen-close">
                        <i class="fa fa-times me-1"></i>Thoát toàn màn hình (Esc)
                    </button>

                    <div class="bracket-tree-wrapper" id="bracket-tree-wrapper">
                        <svg class="bracket-svg-canvas" id="bracket-svg-canvas"></svg>

                        <?php
                        $roundCount = count($rounds);
                        $currentRoundIndex = 0;
                        ?>

                        <?php foreach ($rounds as $roundNo => $roundMatches): ?>
                            <?php
                            $currentRoundIndex++;
                            usort($roundMatches, function ($a, $b) {
                                return ($a['bracket_pos'] ?? 0) - ($b['bracket_pos'] ?? 0);
                            });
                            $firstMatch = $roundMatches[0];
                            $isLastRound = ($currentRoundIndex === $roundCount);
                            $isFinalRound = ($firstMatch['match_type'] ?? '') === 'final' || $isLastRound;
                            ?>

                            <div class="bracket-round-column" data-round="<?php echo $roundNo; ?>">
                                <div class="bracket-round-header">
                                    <span class="bracket-round-pill <?php echo $isFinalRound ? 'pill-final' : ''; ?>">
                                        Vòng <?php echo $roundNo; ?>
                                    </span>
                                    <h6 class="bracket-round-title">
                                        <?php echo CHtml::encode($firstMatch['round']); ?>
                                    </h6>
                                    <small class="text-muted"><?php echo count($roundMatches); ?> trận</small>
                                </div>

                                <div class="bracket-round-matches">
                                    <?php foreach ($roundMatches as $m): ?>
                                        <?php
                                        $res = $m['result'] ?? null;
                                        $hasResult = ($res !== null && $res['score_a'] !== null && $res['score_b'] !== null && $res['score_a'] !== '' && $res['score_b'] !== '');
                                        $scoreA = $hasResult ? $res['score_a'] : '';
                                        $scoreB = $hasResult ? $res['score_b'] : '';
                                        $winnerId = $res['winner_team_id'] ?? null;

                                        if ($hasResult && !$winnerId) {
                                            if ((int)$scoreA > (int)$scoreB) $winnerId = $m['team_a_id'];
                                            elseif ((int)$scoreB > (int)$scoreA) $winnerId = $m['team_b_id'];
                                        }

                                        $isWinnerA = ($winnerId && $m['team_a_id'] && $winnerId == $m['team_a_id']);
                                        $isWinnerB = ($winnerId && $m['team_b_id'] && $winnerId == $m['team_b_id']);
                                        $isMatchFinal = ($m['match_type'] ?? '') === 'final' || $isLastRound;

                                        $targetMatchId = $m['winner_to_match_id'] ?? null;
                                        // Fallback thông minh: nếu targetMatchId rỗng, tính theo ceil(bracket_pos / 2) ở vòng sau
                                        if (!$targetMatchId && isset($rounds[$roundNo + 1])) {
                                            $nextPos = (int) ceil(($m['bracket_pos'] ?? 1) / 2);
                                            foreach ($rounds[$roundNo + 1] as $nm) {
                                                if (($nm['bracket_pos'] ?? 0) === $nextPos) {
                                                    $targetMatchId = $nm['id'];
                                                    break;
                                                }
                                            }
                                        }

                                        $slotA = $getSlotLabel($m['team_a_id'], $m['team_a_source'] ?? null, $m, 0);
                                        $slotB = $getSlotLabel($m['team_b_id'], $m['team_b_source'] ?? null, $m, 1);

                                        $cardClass = 'bracket-match-card';
                                        if ($hasResult) $cardClass .= ' status-completed';
                                        elseif (!empty($m['team_a_id']) && !empty($m['team_b_id'])) $cardClass .= ' status-scheduled';
                                        if ($isMatchFinal) $cardClass .= ' status-final';
                                        ?>

                                        <div class="<?php echo $cardClass; ?>"
                                             data-match-id="<?php echo CHtml::encode($m['id']); ?>"
                                             data-match-code="<?php echo CHtml::encode($m['code'] ?: ('M' . $m['id'])); ?>"
                                             data-round-name="<?php echo CHtml::encode($m['round']); ?>"
                                             data-match-type="<?php echo CHtml::encode($m['match_type']); ?>"
                                             data-target-id="<?php echo CHtml::encode($targetMatchId ?: ''); ?>"
                                             data-has-winner="<?php echo $winnerId ? '1' : '0'; ?>"
                                             data-winner-id="<?php echo CHtml::encode($winnerId ?: ''); ?>"
                                             data-team-a-id="<?php echo CHtml::encode($m['team_a_id'] ?: ''); ?>"
                                             data-team-b-id="<?php echo CHtml::encode($m['team_b_id'] ?: ''); ?>"
                                             data-team-a-name="<?php echo CHtml::encode($slotA['name']); ?>"
                                             data-team-b-name="<?php echo CHtml::encode($slotB['name']); ?>"
                                             data-score-a="<?php echo CHtml::encode($scoreA); ?>"
                                             data-score-b="<?php echo CHtml::encode($scoreB); ?>"
                                             data-match-time="<?php echo CHtml::encode($m['match_time'] ?: ''); ?>"
                                             data-location="<?php echo CHtml::encode($m['location'] ?: ''); ?>"
                                             data-match-order="<?php echo CHtml::encode($m['match_order'] ?: ''); ?>"
                                             data-is-final="<?php echo $isMatchFinal ? '1' : '0'; ?>">

                                            <span class="bracket-anchor-in"></span>
                                            <span class="bracket-anchor-out"></span>

                                            <!-- Header trận đấu trên nhánh -->
                                            <div class="match-card-header">
                                                <span class="match-code-badge">
                                                    <i class="fa fa-hashtag"></i><?php echo CHtml::encode($m['code'] ?: ('M' . $m['id'])); ?>
                                                </span>
                                                <div class="d-flex align-items-center gap-1">
                                                    <?php if (!empty($m['match_time'])): ?>
                                                        <span class="badge bg-light text-primary border" title="<?php echo CHtml::encode($m['match_time']); ?>">
                                                            <i class="fa fa-clock-o"></i> <?php echo date('H:i', strtotime($m['match_time'])); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-xs btn-outline-primary btn-quick-result py-0 px-1" title="Ghi tỉ số / Đặt giờ">
                                                        <i class="fa fa-pencil"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Đội A -->
                                            <div class="match-team-row <?php echo $isWinnerA ? 'is-winner' : ($isWinnerB ? 'is-loser' : ''); ?>"
                                                 data-team-id="<?php echo CHtml::encode($m['team_a_id'] ?: ''); ?>">
                                                <div class="team-info-col">
                                                    <span class="team-seed-chip">A</span>
                                                    <?php if ($slotA['isTeam']): ?>
                                                        <span class="team-name-text" title="<?php echo CHtml::encode($slotA['name']); ?>">
                                                            <?php echo CHtml::encode($slotA['name']); ?>
                                                        </span>
                                                        <?php if ($isWinnerA): ?>
                                                            <i class="fa fa-trophy winner-crown-icon" title="Thắng"></i>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="team-source-hint text-primary fw-semibold" title="<?php echo CHtml::encode($slotA['name']); ?>">
                                                            <i class="fa fa-clock-o text-muted me-1"></i><?php echo CHtml::encode($slotA['name']); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <span class="team-score-box">
                                                    <?php echo ($scoreA !== '') ? CHtml::encode($scoreA) : '-'; ?>
                                                </span>
                                            </div>

                                            <!-- Đội B -->
                                            <div class="match-team-row <?php echo $isWinnerB ? 'is-winner' : ($isWinnerA ? 'is-loser' : ''); ?>"
                                                 data-team-id="<?php echo CHtml::encode($m['team_b_id'] ?: ''); ?>">
                                                <div class="team-info-col">
                                                    <span class="team-seed-chip">B</span>
                                                    <?php if (!empty($m['is_bye'])): ?>
                                                        <span class="badge bg-light text-muted">BYE</span>
                                                    <?php elseif ($slotB['isTeam']): ?>
                                                        <span class="team-name-text" title="<?php echo CHtml::encode($slotB['name']); ?>">
                                                            <?php echo CHtml::encode($slotB['name']); ?>
                                                        </span>
                                                        <?php if ($isWinnerB): ?>
                                                            <i class="fa fa-trophy winner-crown-icon" title="Thắng"></i>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="team-source-hint text-primary fw-semibold" title="<?php echo CHtml::encode($slotB['name']); ?>">
                                                            <i class="fa fa-clock-o text-muted me-1"></i><?php echo CHtml::encode($slotB['name']); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <span class="team-score-box">
                                                    <?php if (!empty($m['is_bye'])): ?>
                                                        -
                                                    <?php else: ?>
                                                        <?php echo ($scoreB !== '') ? CHtml::encode($scoreB) : '-'; ?>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- CỘT CÚP VÔ ĐỊCH (CHAMPION SHOWCASE) -->
                        <div class="bracket-champion-column">
                            <div class="bracket-champion-card" id="bracket-champion-card">
                                <span class="bracket-anchor-in"></span>
                                <div class="champion-trophy-halo">
                                    <i class="fa fa-trophy"></i>
                                </div>
                                <div class="champion-tag">Nhà Vô Địch Đại Hội</div>
                                <?php if ($championTeamName): ?>
                                    <div class="champion-team-name text-success">
                                        <?php echo CHtml::encode($championTeamName); ?>
                                    </div>
                                    <span class="badge bg-warning text-dark px-3 py-1 fw-bold">
                                        <i class="fa fa-star me-1"></i>QUÁN QUÂN
                                    </span>
                                <?php else: ?>
                                    <div class="champion-placeholder">
                                        Đang chờ kết quả trận Chung kết...
                                    </div>
                                    <span class="badge bg-light text-muted border mt-2">
                                        Cúp Vô Địch
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- TRẬN TRANH HẠNG 3 (BRONZE MEDAL MATCH) -->
            <?php if ($thirdPlace): ?>
                <?php
                $tpRes = $thirdPlace['result'] ?? null;
                $tpDone = ($tpRes !== null && $tpRes['score_a'] !== null && $tpRes['score_b'] !== null);
                $tpScoreA = $tpDone ? $tpRes['score_a'] : '';
                $tpScoreB = $tpDone ? $tpRes['score_b'] : '';
                $tpWinner = $tpRes['winner_team_id'] ?? null;
                if ($tpDone && !$tpWinner) {
                    if ((int)$tpScoreA > (int)$tpScoreB) $tpWinner = $thirdPlace['team_a_id'];
                    elseif ((int)$tpScoreB > (int)$tpScoreA) $tpWinner = $thirdPlace['team_b_id'];
                }
                $tpSlotA = $getSlotLabel($thirdPlace['team_a_id'], $thirdPlace['team_a_source'] ?? null, $thirdPlace, 0);
                $tpSlotB = $getSlotLabel($thirdPlace['team_b_id'], $thirdPlace['team_b_source'] ?? null, $thirdPlace, 1);
                ?>
                <div class="bracket-playoff-section mb-4">
                    <div class="playoff-header">
                        <span class="playoff-medal-icon">
                            <i class="fa fa-shield"></i>
                        </span>
                        <div>
                            <h6 class="mb-0 fw-bold text-dark">Trận Tranh Hạng 3 (Huy chương Đồng)</h6>
                            <small class="text-muted">Dành cho hai đội thua ở vòng Bán kết</small>
                        </div>
                    </div>

                    <div class="bracket-match-card card-playoff" style="max-width: 340px;"
                         data-match-id="<?php echo CHtml::encode($thirdPlace['id']); ?>"
                         data-match-code="<?php echo CHtml::encode($thirdPlace['code'] ?: 'TRANH-HANG-3'); ?>"
                         data-round-name="<?php echo CHtml::encode($thirdPlace['round']); ?>"
                         data-match-type="playoff"
                         data-team-a-id="<?php echo CHtml::encode($thirdPlace['team_a_id'] ?: ''); ?>"
                         data-team-b-id="<?php echo CHtml::encode($thirdPlace['team_b_id'] ?: ''); ?>"
                         data-team-a-name="<?php echo CHtml::encode($tpSlotA['name']); ?>"
                         data-team-b-name="<?php echo CHtml::encode($tpSlotB['name']); ?>"
                         data-score-a="<?php echo CHtml::encode($tpScoreA); ?>"
                         data-score-b="<?php echo CHtml::encode($tpScoreB); ?>"
                         data-winner-id="<?php echo CHtml::encode($tpWinner ?: ''); ?>"
                         data-match-time="<?php echo CHtml::encode($thirdPlace['match_time'] ?: ''); ?>"
                         data-location="<?php echo CHtml::encode($thirdPlace['location'] ?: ''); ?>"
                         data-match-order="<?php echo CHtml::encode($thirdPlace['match_order'] ?: ''); ?>">

                        <div class="match-card-header">
                            <span class="match-code-badge">
                                <i class="fa fa-hashtag"></i><?php echo CHtml::encode($thirdPlace['code'] ?: 'TRANH-HANG-3'); ?>
                            </span>
                            <div class="d-flex align-items-center gap-1">
                                <?php if ($tpDone): ?>
                                    <span class="match-status-badge bg-success text-white">
                                        <i class="fa fa-check me-1"></i>Đã xong
                                    </span>
                                <?php else: ?>
                                    <span class="match-status-badge bg-warning text-dark">Sắp diễn ra</span>
                                <?php endif; ?>
                                <button type="button" class="btn btn-xs btn-outline-primary btn-quick-result py-0 px-1" title="Ghi tỉ số / Đặt giờ">
                                    <i class="fa fa-pencil"></i>
                                </button>
                            </div>
                        </div>

                        <div class="match-team-row <?php echo ($tpWinner && $thirdPlace['team_a_id'] == $tpWinner) ? 'is-winner' : ''; ?>">
                            <div class="team-info-col">
                                <span class="team-seed-chip">1</span>
                                <?php if ($tpSlotA['isTeam']): ?>
                                    <span class="team-name-text"><?php echo CHtml::encode($tpSlotA['name']); ?></span>
                                    <?php if ($tpWinner && $thirdPlace['team_a_id'] == $tpWinner): ?>
                                        <span class="badge bg-warning text-dark ms-1">Huy chương Đồng</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="team-source-hint text-primary fw-semibold" title="<?php echo CHtml::encode($tpSlotA['name']); ?>">
                                        <i class="fa fa-clock-o text-muted me-1"></i><?php echo CHtml::encode($tpSlotA['name']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <span class="team-score-box">
                                <?php echo ($tpScoreA !== '') ? CHtml::encode($tpScoreA) : '-'; ?>
                            </span>
                        </div>

                        <div class="match-team-row <?php echo ($tpWinner && $thirdPlace['team_b_id'] == $tpWinner) ? 'is-winner' : ''; ?>">
                            <div class="team-info-col">
                                <span class="team-seed-chip">2</span>
                                <?php if ($tpSlotB['isTeam']): ?>
                                    <span class="team-name-text"><?php echo CHtml::encode($tpSlotB['name']); ?></span>
                                    <?php if ($tpWinner && $thirdPlace['team_b_id'] == $tpWinner): ?>
                                        <span class="badge bg-warning text-dark ms-1">Huy chương Đồng</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="team-source-hint text-primary fw-semibold" title="<?php echo CHtml::encode($tpSlotB['name']); ?>">
                                        <i class="fa fa-clock-o text-muted me-1"></i><?php echo CHtml::encode($tpSlotB['name']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <span class="team-score-box">
                                <?php echo ($tpScoreB !== '') ? CHtml::encode($tpScoreB) : '-'; ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </div><!-- /#sport-draw-view-root -->

    <!-- =========================================================================
         5. MODAL POPUP GHI KẾT QUẢ VÀ ĐẶT LỊCH THI ĐẤU (QUICK MATCH EDITOR)
         ========================================================================= -->
    <div class="modal fade" id="modal-match-editor" tabindex="-1" aria-labelledby="modalMatchEditorLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header bg-light border-bottom py-3">
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="modal-match-title">
                            <i class="fa fa-pencil-square text-primary me-2"></i>Cập nhật Trận đấu
                        </h5>
                        <small class="text-muted">Nhập tỉ số thi đấu và thông tin ngày giờ, sân đấu</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="form-modal-match-editor">
                    <input type="hidden" id="modal-field-match-id" name="match_id" value="">

                    <div class="modal-body p-4">
                        <!-- PHẦN 1: TỈ SỐ TRẬN ĐẤU -->
                        <h6 class="fw-bold text-dark mb-3"><i class="fa fa-futbol-o text-success me-2"></i>Kết quả trận đấu</h6>
                        <div class="modal-match-editor-scores">
                            <!-- Đội A -->
                            <div class="text-center flex-grow-1" style="max-width: 140px;">
                                <label class="form-label fw-bold small text-truncate d-block mb-1" id="modal-team-a-name">Đội A</label>
                                <input type="number" class="form-control modal-score-input mx-auto"
                                       id="modal-field-score-a" min="0" placeholder="0">
                            </div>

                            <span class="fs-4 fw-bold text-muted">-</span>

                            <!-- Đội B -->
                            <div class="text-center flex-grow-1" style="max-width: 140px;">
                                <label class="form-label fw-bold small text-truncate d-block mb-1" id="modal-team-b-name">Đội B</label>
                                <input type="number" class="form-control modal-score-input mx-auto"
                                       id="modal-field-score-b" min="0" placeholder="0">
                            </div>
                        </div>

                        <!-- Chọn đội thắng khi trận Knockout hòa điểm -->
                        <div class="mb-3" id="modal-winner-section">
                            <label class="form-label small fw-bold text-muted">Đội thắng (Loại trực tiếp / Pen):</label>
                            <select class="form-select form-select-sm" id="modal-field-winner">
                                <option value="">-- Tự động theo tỉ số --</option>
                            </select>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="modal-field-is-draw" value="1">
                            <label class="form-check-label small" for="modal-field-is-draw">
                                Xác nhận kết quả hòa (vòng bảng)
                            </label>
                        </div>

                        <hr class="my-3">

                        <!-- PHẦN 2: THỜI GIAN & ĐỊA ĐIỂM SÂN THI ĐẤU -->
                        <h6 class="fw-bold text-dark mb-3"><i class="fa fa-clock-o text-primary me-2"></i>Thời gian & Địa điểm thi đấu</h6>
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label class="form-label small fw-semibold">Thời gian bắt đầu:</label>
                                <input type="datetime-local" class="form-control form-control-sm" id="modal-field-match-time">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-semibold">Thứ tự thi đấu:</label>
                                <input type="number" class="form-control form-control-sm" id="modal-field-match-order" min="1" placeholder="1, 2, 3...">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Sân đấu / Địa điểm:</label>
                                <input type="text" class="form-control form-control-sm" id="modal-field-location" placeholder="Ví dụ: Sân bóng số 1, Sân A, Nhà thi đấu...">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-sm btn-primary px-3" id="btn-modal-save-all">
                            <i class="fa fa-save me-1"></i>Lưu thông tin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php endif; ?>
