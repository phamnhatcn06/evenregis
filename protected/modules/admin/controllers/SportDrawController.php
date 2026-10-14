<?php

class SportDrawController extends AdminController
{
    /**
     * Bốc thăm là một phần của quản lý thể thao. Quyền được cấp từ Portal theo
     * tên controller thật ('sports', 'sportteams', 'sportdraw'), KHÔNG phải 'sport'.
     * Chấp nhận nếu user có quyền ở bất kỳ controller thể thao nào tương ứng.
     */
    public function beforeAction($action)
    {
        if (!Controller::beforeAction($action)) {
            return false;
        }

        $actionId = strtolower($action->id);
        $operation = $this->mapActionToOperation($actionId);

        if ($operation) {
            $allowed = PermissionHelper::can('sports', $operation)
                || PermissionHelper::can('sportdraw', $operation)
                || PermissionHelper::can('sportteams', $operation)
                || PermissionHelper::can('sport', $operation);
            if (!$allowed) {
                throw new CHttpException(403, 'Bạn không có quyền thực hiện thao tác này.');
            }
        }

        return true;
    }

    /**
     * Alias cho action 'admin' (quy ước menu admin) → dùng chung màn hình chính.
     */
    public function actionAdmin()
    {
        $this->actionIndex();
    }

    /**
     * Màn hình chính: chọn sự kiện + nội dung → danh sách giai đoạn + bốc thăm.
     */
    public function actionIndex()
    {
        $eventId = Yii::app()->getRequest()->getParam('event_id');
        $sportId = Yii::app()->getRequest()->getParam('sport_id');

        $dataProvider = null;
        if ($eventId) {
            $params = array('event_id' => $eventId);
            if ($sportId) {
                $params['sport_id'] = $sportId;
            }
            $dataProvider = SportStages::getApiDataProvider($params);
        }

        $this->render('index', array(
            'eventId' => $eventId,
            'sportId' => $sportId,
            'eventList' => Events::getListForDropdown(),
            'sportList' => $this->getSportDropdown(),
            'dataProvider' => $dataProvider,
        ));
    }

    /**
     * Xem kết quả bốc thăm (bảng / sơ đồ cây).
     */
    public function actionView($id)
    {
        $model = SportStages::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy giai đoạn.');
        }

        $preview = SportStages::fetchDrawPreview($id);

        $this->render('view', array(
            'model' => $model,
            'preview' => $preview,
        ));
    }

    /**
     * Màn hình gán thủ công (kéo-thả) đội vào bảng / vị trí seed nhánh.
     */
    public function actionAssign($id)
    {
        $model = SportStages::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy giai đoạn.');
        }

        $preview = SportStages::fetchDrawPreview($id);

        $this->render('assign', array(
            'model' => $model,
            'preview' => $preview,
        ));
    }

    /**
     * Màn hình ghi kết quả từng trận của giai đoạn.
     */
    public function actionResults($id)
    {
        $model = SportStages::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy giai đoạn.');
        }

        $preview = SportStages::fetchDrawPreview($id);

        $this->render('results', array(
            'model' => $model,
            'preview' => $preview,
        ));
    }

    /**
     * Ghi kết quả 1 trận (AJAX). Backend tự đẩy đội thắng vòng kế.
     */
    public function actionSaveResult()
    {
        $this->requirePost();

        $req = Yii::app()->getRequest();
        $matchId = $req->getPost('match_id');
        $scoreA = $req->getPost('score_a');
        $scoreB = $req->getPost('score_b');

        if (!$matchId || $scoreA === null || $scoreA === '' || $scoreB === null || $scoreB === '') {
            $this->jsonResponse(false, 'Vui lòng nhập đủ tỉ số hai đội.');
        }

        $result = SportMatchResults::recordViaApi(array(
            'match_id' => $matchId,
            'score_a' => (int) $scoreA,
            'score_b' => (int) $scoreB,
            'winner_team_id' => $req->getPost('winner_team_id') ?: null,
            'is_draw' => $req->getPost('is_draw') ? 1 : 0,
        ));

        if (!empty($result['success'])) {
            $this->jsonResponse(true, 'Đã ghi kết quả trận đấu.', isset($result['data']) ? $result['data'] : null);
        }
        $this->jsonResponse(false, $this->apiError($result, 'Không thể ghi kết quả.'));
    }

    /**
     * Cập nhật thông tin trận đấu (thời gian bắt đầu, địa điểm, thứ tự) (AJAX).
     */
    public function actionUpdateMatch()
    {
        $this->requirePost();

        $req = Yii::app()->getRequest();
        $matchId = $req->getPost('match_id');
        $eventId = (int) $req->getPost('event_id');
        $sportId = (int) $req->getPost('sport_id');
        $matchTime = $req->getPost('match_time');
        $location = $req->getPost('location');
        $matchOrder = $req->getPost('match_order');

        if (!$matchId) {
            $this->jsonResponse(false, 'Thiếu ID trận đấu.');
        }

        $payload = array(
            'event_id' => $eventId,
            'sport_id' => $sportId,
        );
        if ($matchTime !== null) {
            $payload['match_time'] = !empty($matchTime) ? date('Y-m-d H:i:s', strtotime($matchTime)) : null;
        }
        if ($location !== null) {
            $payload['location'] = trim($location);
        }
        if ($matchOrder !== null && $matchOrder !== '') {
            $payload['match_order'] = (int) $matchOrder;
        }

        $url = ApiEndpoints::url(ApiEndpoints::SPORT_MATCH_UPDATE, array('id' => $matchId));
        $result = ApiClient::post($url, $payload);

        if (!empty($result['success'])) {
            $this->jsonResponse(true, 'Đã cập nhật thông tin trận đấu.', isset($result['data']) ? $result['data'] : null);
        }
        $this->jsonResponse(false, $this->apiError($result, 'Không thể cập nhật thông tin trận đấu.'));
    }

    /**
     * Lưu thứ tự sắp xếp lại các trận đấu (AJAX).
     */
    public function actionReorderMatches()
    {
        $this->requirePost();

        $req = Yii::app()->getRequest();
        $eventId = (int) $req->getPost('event_id');
        $sportId = (int) $req->getPost('sport_id');
        $rawOrders = $req->getPost('orders');
        $orders = is_string($rawOrders) ? CJSON::decode($rawOrders) : $rawOrders;

        if (!is_array($orders) || empty($orders)) {
            $this->jsonResponse(false, 'Dữ liệu thứ tự không hợp lệ.');
        }

        $hasError = false;
        foreach ($orders as $item) {
            $mId = isset($item['match_id']) ? $item['match_id'] : null;
            $order = isset($item['match_order']) ? (int) $item['match_order'] : null;
            if (!$mId || $order === null) continue;

            $payload = array(
                'event_id' => $eventId,
                'sport_id' => $sportId,
                'match_order' => $order,
            );
            $url = ApiEndpoints::url(ApiEndpoints::SPORT_MATCH_UPDATE, array('id' => $mId));
            $res = ApiClient::post($url, $payload);
            if (empty($res['success'])) {
                $hasError = true;
            }
        }

        if (!$hasError) {
            $this->jsonResponse(true, 'Đã lưu thứ tự các trận đấu.');
        }
        $this->jsonResponse(false, 'Có một số trận chưa được lưu thứ tự thành công.');
    }

    /**
     * Lưu kết quả gán thủ công (AJAX, nhận JSON assignments).
     */
    public function actionSaveAssignment($id)
    {
        $this->requirePost();

        $raw = Yii::app()->getRequest()->getPost('assignments');
        $assignments = is_string($raw) ? CJSON::decode($raw) : $raw;
        if (!is_array($assignments) || empty($assignments)) {
            $this->jsonResponse(false, 'Chưa có dữ liệu phân bổ đội.');
        }

        $result = SportStages::saveManualDraw($id, $assignments);
        if (!empty($result['success'])) {
            $this->jsonResponse(true, 'Đã lưu phân bổ đội.', isset($result['data']) ? $result['data'] : null);
        }
        $this->jsonResponse(false, $this->apiError($result, 'Không thể lưu phân bổ đội.'));
    }

    /**
     * Lưu cấu hình thể thức/bốc thăm cho giai đoạn (AJAX).
     */
    public function actionConfig($id)
    {
        $this->requirePost();
        $model = SportStages::fetchFromApi($id);
        if ($model === null) {
            $this->jsonResponse(false, 'Không tìm thấy giai đoạn.');
        }

        $input = Yii::app()->getRequest()->getPost('SportStages', array());
        $model->setAttributes($input, false);

        $result = $model->updateConfigViaApi();
        if ($result['success']) {
            $this->jsonResponse(true, 'Đã lưu cấu hình thể thức.');
        }
        $this->jsonResponse(false, $this->apiError($result, 'Không thể lưu cấu hình.'));
    }

    /**
     * Bốc thăm chia bảng (AJAX).
     */
    public function actionDrawGroups($id)
    {
        $this->requirePost();
        $result = SportStages::drawGroups($id);
        $this->handleDrawResult($result);
    }

    /**
     * Bốc thăm sơ đồ loại trực tiếp (AJAX).
     */
    public function actionDrawBracket($id)
    {
        $this->requirePost();
        $result = SportStages::drawBracket($id);
        $this->handleDrawResult($result);
    }

    /**
     * Tính bảng xếp hạng vòng tròn (AJAX).
     */
    public function actionStandings($id)
    {
        $this->requirePost();
        $result = SportStages::computeStandings($id);
        if ($result['success']) {
            $this->jsonResponse(true, 'Đã tính bảng xếp hạng.', isset($result['data']) ? $result['data'] : null);
        }
        $this->jsonResponse(false, $this->apiError($result, 'Không thể tính bảng xếp hạng.'));
    }

    /**
     * Sinh / Cập nhật danh sách đội vào sơ đồ loại trực tiếp từ kết quả vòng bảng (AJAX).
     */
    public function actionGenerateKnockout($id)
    {
        $this->requirePost();
        $stageId = (int) $id;
        $force = (int) Yii::app()->getRequest()->getPost('force', 0);

        // 1. Kiểm tra số trận vòng bảng đã có kết quả
        $db = Yii::app()->db;
        $completedGroupCount = (int) $db->createCommand("
            SELECT COUNT(*) 
            FROM sport_matches m 
            JOIN sport_match_results r ON m.id = r.match_id 
            WHERE m.stage_id = :sid AND m.match_type = 'group' 
              AND m.deleted_at IS NULL 
              AND r.score_a IS NOT NULL AND r.score_b IS NOT NULL
        ")->queryScalar(array(':sid' => $stageId));

        $totalGroupCount = (int) $db->createCommand("
            SELECT COUNT(*) 
            FROM sport_matches m 
            WHERE m.stage_id = :sid AND m.match_type = 'group' AND m.deleted_at IS NULL
        ")->queryScalar(array(':sid' => $stageId));

        if ($totalGroupCount > 0 && $completedGroupCount === 0 && !$force) {
            $this->jsonResponse(false, 'Vòng bảng chưa có trận đấu nào có kết quả (0/' . $totalGroupCount . ' trận). Vui lòng ghi kết quả các trận vòng bảng trước khi cập nhật danh sách đội vào sơ đồ loại trực tiếp.');
        }

        // Tự động tính lại BXH trước
        try {
            SportStages::computeStandings($stageId);
        } catch (Exception $e) {}

        // Thử gọi qua API backend
        $apiSuccess = false;
        $result = null;
        try {
            $result = SportStages::generateKnockout($stageId);
            if (!empty($result['success'])) {
                $apiSuccess = true;
            }
        } catch (Exception $e) {}

        if ($apiSuccess) {
            $this->jsonResponse(true, 'Đã cập nhật danh sách đội từ Bảng xếp hạng vào sơ đồ nhánh!', isset($result['data']) ? $result['data'] : null);
        }

        // Fallback: Đồng bộ trực tiếp qua cơ sở dữ liệu nếu API chưa bật hoặc gặp lỗi
        $fallback = $this->syncKnockoutFromDb($stageId);
        if ($fallback['success']) {
            $this->jsonResponse(true, $fallback['message'], $fallback['data'] ?? null);
        }

        $this->jsonResponse(false, isset($result) ? $this->apiError($result, $fallback['message']) : $fallback['message']);
    }

    /**
     * Đồng bộ danh sách đội nhất/nhì bảng vào các nhánh Bán kết trực tiếp qua DB.
     */
    protected function syncKnockoutFromDb($stageId)
    {
        try {
            $db = Yii::app()->db;
            $stage = $db->createCommand("SELECT * FROM sport_stages WHERE id = :id AND deleted_at IS NULL")
                ->queryRow(true, array(':id' => $stageId));
            if (!$stage) {
                return array('success' => false, 'message' => 'Không tìm thấy giai đoạn.');
            }

            $advance = (int) ($stage['advance_per_group'] ?: 2);

            // 1. Lấy danh sách đội theo bảng
            $teams = $db->createCommand("
                SELECT st.team_id, st.group_label, st.group_order, t.name as team_name 
                FROM sport_stage_teams st 
                LEFT JOIN sport_teams t ON st.team_id = t.id 
                WHERE st.stage_id = :sid AND st.group_label IS NOT NULL AND st.deleted_at IS NULL 
                ORDER BY st.group_label, st.group_order
            ")->queryAll(true, array(':sid' => $stageId));

            if (empty($teams)) {
                return array('success' => false, 'message' => 'Chưa có phân bổ đội vào bảng.');
            }

            // 2. Lấy trận vòng bảng và kết quả
            $matches = $db->createCommand("
                SELECT m.*, r.score_a, r.score_b, r.winner_team_id, r.is_draw 
                FROM sport_matches m 
                LEFT JOIN sport_match_results r ON m.id = r.match_id 
                WHERE m.stage_id = :sid AND m.match_type = 'group' AND m.deleted_at IS NULL
            ")->queryAll(true, array(':sid' => $stageId));

            // Khởi tạo thống kê
            $byGroup = array();
            $stats = array();
            foreach ($teams as $t) {
                $tid = (int) $t['team_id'];
                $g = $t['group_label'];
                $byGroup[$g][] = $tid;
                $stats[$tid] = array(
                    'team_id' => $tid,
                    'group_label' => $g,
                    'played' => 0, 'won' => 0, 'drawn' => 0, 'lost' => 0,
                    'points_scored' => 0, 'points_conceded' => 0, 'points' => 0,
                );
            }

            foreach ($matches as $m) {
                if ($m['score_a'] !== null && $m['score_b'] !== null && $m['score_a'] !== '' && $m['score_b'] !== '') {
                    $a = (int) $m['team_a_id'];
                    $b = (int) $m['team_b_id'];
                    $sa = (int) $m['score_a'];
                    $sb = (int) $m['score_b'];
                    if (isset($stats[$a]) && isset($stats[$b])) {
                        $stats[$a]['played']++;
                        $stats[$b]['played']++;
                        $stats[$a]['points_scored'] += $sa;
                        $stats[$a]['points_conceded'] += $sb;
                        $stats[$b]['points_scored'] += $sb;
                        $stats[$b]['points_conceded'] += $sa;
                        if ($sa > $sb) {
                            $stats[$a]['won']++; $stats[$a]['points'] += 3;
                            $stats[$b]['lost']++;
                        } elseif ($sb > $sa) {
                            $stats[$b]['won']++; $stats[$b]['points'] += 3;
                            $stats[$a]['lost']++;
                        } else {
                            $stats[$a]['drawn']++; $stats[$a]['points'] += 1;
                            $stats[$b]['drawn']++; $stats[$b]['points'] += 1;
                        }
                    }
                }
            }

            // Xếp hạng từng bảng
            $qualifiers = array();
            ksort($byGroup);
            foreach ($byGroup as $gLabel => $tids) {
                usort($tids, function ($x, $y) use ($stats) {
                    $diffX = $stats[$x]['points_scored'] - $stats[$x]['points_conceded'];
                    $diffY = $stats[$y]['points_scored'] - $stats[$y]['points_conceded'];
                    if ($stats[$y]['points'] !== $stats[$x]['points']) return $stats[$y]['points'] - $stats[$x]['points'];
                    if ($diffY !== $diffX) return $diffY - $diffX;
                    return $stats[$y]['points_scored'] - $stats[$x]['points_scored'];
                });

                $rank = 1;
                foreach ($tids as $tid) {
                    $qualifiers[$gLabel][$rank] = $tid;
                    // Cập nhật lại sport_standings nếu có bảng này
                    try {
                        $db->createCommand("
                            INSERT INTO sport_standings 
                                (event_id, sport_id, stage_id, team_id, group_label, played, won, drawn, lost, points_scored, points_conceded, points, `rank`, created_at, updated_at)
                            VALUES 
                                (:eid, :spid, :sid, :tid, :g, :p, :w, :d, :l, :ps, :pc, :pts, :rk, NOW(), NOW())
                            ON DUPLICATE KEY UPDATE 
                                played = VALUES(played), won = VALUES(won), drawn = VALUES(drawn), lost = VALUES(lost),
                                points_scored = VALUES(points_scored), points_conceded = VALUES(points_conceded),
                                points = VALUES(points), `rank` = VALUES(`rank`), updated_at = NOW()
                        ")->execute(array(
                            ':eid' => $stage['event_id'],
                            ':spid' => $stage['sport_id'],
                            ':sid' => $stageId,
                            ':tid' => $tid,
                            ':g' => $gLabel,
                            ':p' => $stats[$tid]['played'],
                            ':w' => $stats[$tid]['won'],
                            ':d' => $stats[$tid]['drawn'],
                            ':l' => $stats[$tid]['lost'],
                            ':ps' => $stats[$tid]['points_scored'],
                            ':pc' => $stats[$tid]['points_conceded'],
                            ':pts' => $stats[$tid]['points'],
                            ':rk' => $rank,
                        ));
                    } catch (Exception $ex) {}
                    $rank++;
                }
            }

            // Cross-pairing chuẩn:
            // Trận 1 (Bán kết 1): Nhất A vs Nhì B
            // Trận 2 (Bán kết 2): Nhất B vs Nhì A
            $team1A = $qualifiers['A'][1] ?? null;
            $team2B = $qualifiers['B'][2] ?? null;
            $team1B = $qualifiers['B'][1] ?? null;
            $team2A = $qualifiers['A'][2] ?? null;

            // Kiểm tra xem trận knockout đã tồn tại chưa
            $koMatches = $db->createCommand("
                SELECT id, round_no, bracket_pos, match_type 
                FROM sport_matches 
                WHERE stage_id = :sid AND match_type IN ('knockout', 'final', 'playoff') AND deleted_at IS NULL 
                ORDER BY round_no, bracket_pos
            ")->queryAll(true, array(':sid' => $stageId));

            if (!empty($koMatches)) {
                // Đã có trận knockout -> cập nhật lại team_a_id và team_b_id cho Bán kết
                foreach ($koMatches as $km) {
                    $rNo = (int) $km['round_no'];
                    $pos = (int) $km['bracket_pos'];
                    if ($rNo === 1 && $pos === 1) {
                        $db->createCommand("
                            UPDATE sport_matches 
                            SET team_a_id = :ta, team_b_id = :tb, 
                                team_a_source = 'Nhất Bảng A', team_b_source = 'Nhì Bảng B', 
                                updated_at = NOW() 
                            WHERE id = :mid
                        ")->execute(array(
                            ':ta' => $team1A,
                            ':tb' => $team2B,
                            ':mid' => $km['id'],
                        ));
                    } elseif ($rNo === 1 && $pos === 2) {
                        $db->createCommand("
                            UPDATE sport_matches 
                            SET team_a_id = :ta, team_b_id = :tb, 
                                team_a_source = 'Nhất Bảng B', team_b_source = 'Nhì Bảng A', 
                                updated_at = NOW() 
                            WHERE id = :mid
                        ")->execute(array(
                            ':ta' => $team1B,
                            ':tb' => $team2A,
                            ':mid' => $km['id'],
                        ));
                    }
                }
            } else {
                // Chưa có trận knockout trong DB -> tạo mới 2 trận Bán kết, 1 trận Chung kết, 1 trận Tranh hạng 3
                $now = date('Y-m-d H:i:s');
                $maxId = (int) $db->createCommand("SELECT COALESCE(MAX(id), 0) FROM sport_matches")->queryScalar();
                
                // Bán kết 1
                $codeBK1 = 'SPO' . str_pad($maxId + 1, 6, '0', STR_PAD_LEFT);
                $db->createCommand("
                    INSERT INTO sport_matches 
                        (event_id, sport_id, stage_id, code, round, match_type, round_no, bracket_pos, team_a_id, team_b_id, team_a_source, team_b_source, status, created_at, updated_at) 
                    VALUES 
                        (:eid, :spid, :sid, :code, 'Bán kết', 'knockout', 1, 1, :ta, :tb, 'Nhất Bảng A', 'Nhì Bảng B', 'scheduled', :now, :now)
                ")->execute(array(
                    ':eid' => $stage['event_id'], ':spid' => $stage['sport_id'], ':sid' => $stageId,
                    ':code' => $codeBK1, ':ta' => $team1A, ':tb' => $team2B, ':now' => $now,
                ));
                $idBK1 = (int) $db->getLastInsertID();

                // Bán kết 2
                $codeBK2 = 'SPO' . str_pad($maxId + 2, 6, '0', STR_PAD_LEFT);
                $db->createCommand("
                    INSERT INTO sport_matches 
                        (event_id, sport_id, stage_id, code, round, match_type, round_no, bracket_pos, team_a_id, team_b_id, team_a_source, team_b_source, status, created_at, updated_at) 
                    VALUES 
                        (:eid, :spid, :sid, :code, 'Bán kết', 'knockout', 1, 2, :ta, :tb, 'Nhất Bảng B', 'Nhì Bảng A', 'scheduled', :now, :now)
                ")->execute(array(
                    ':eid' => $stage['event_id'], ':spid' => $stage['sport_id'], ':sid' => $stageId,
                    ':code' => $codeBK2, ':ta' => $team1B, ':tb' => $team2A, ':now' => $now,
                ));
                $idBK2 = (int) $db->getLastInsertID();

                // Chung kết
                $codeFinal = 'SPO' . str_pad($maxId + 3, 6, '0', STR_PAD_LEFT);
                $db->createCommand("
                    INSERT INTO sport_matches 
                        (event_id, sport_id, stage_id, code, round, match_type, round_no, bracket_pos, team_a_source, team_b_source, status, created_at, updated_at) 
                    VALUES 
                        (:eid, :spid, :sid, :code, 'Chung kết', 'final', 2, 1, 'Thắng Bán kết 1', 'Thắng Bán kết 2', 'scheduled', :now, :now)
                ")->execute(array(
                    ':eid' => $stage['event_id'], ':spid' => $stage['sport_id'], ':sid' => $stageId,
                    ':code' => $codeFinal, ':now' => $now,
                ));
                $idFinal = (int) $db->getLastInsertID();

                // Tranh hạng 3
                $codeThird = 'SPO' . str_pad($maxId + 4, 6, '0', STR_PAD_LEFT);
                $db->createCommand("
                    INSERT INTO sport_matches 
                        (event_id, sport_id, stage_id, code, round, match_type, round_no, bracket_pos, team_a_source, team_b_source, status, created_at, updated_at) 
                    VALUES 
                        (:eid, :spid, :sid, :code, 'Tranh hạng 3', 'playoff', 2, 2, 'Thua Bán kết 1', 'Thua Bán kết 2', 'scheduled', :now, :now)
                ")->execute(array(
                    ':eid' => $stage['event_id'], ':spid' => $stage['sport_id'], ':sid' => $stageId,
                    ':code' => $codeThird, ':now' => $now,
                ));
                $idThird = (int) $db->getLastInsertID();

                // Liên kết winner_to và loser_to
                $db->createCommand("UPDATE sport_matches SET winner_to_match_id = :fid, loser_to_match_id = :tid WHERE id IN (:bk1, :bk2)")
                    ->execute(array(':fid' => $idFinal, ':tid' => $idThird, ':bk1' => $idBK1, ':bk2' => $idBK2));
            }

            return array(
                'success' => true,
                'message' => 'Đã cập nhật danh sách đội từ Bảng xếp hạng vào sơ đồ nhánh!',
                'data' => array('stage_id' => $stageId),
            );
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Lỗi đồng bộ sơ đồ nhánh: ' . $e->getMessage());
        }
    }

    /**
     * Khoá kết quả bốc thăm (AJAX).
     */
    public function actionLock($id)
    {
        $this->requirePost();
        $result = SportStages::lockDraw($id);
        if ($result['success']) {
            $this->jsonResponse(true, 'Đã khoá kết quả bốc thăm.');
        }
        $this->jsonResponse(false, $this->apiError($result, 'Không thể khoá kết quả.'));
    }

    /* ===== Helpers ===== */

    protected function handleDrawResult($result)
    {
        if ($result['success']) {
            $this->jsonResponse(true, 'Bốc thăm thành công.', isset($result['data']) ? $result['data'] : null);
        }
        $this->jsonResponse(false, $this->apiError($result, 'Bốc thăm thất bại.'));
    }

    /**
     * Lấy thông điệp lỗi từ phản hồi API.
     */
    protected function apiError($result, $default)
    {
        if (!empty($result['error'])) {
            return $result['error'];
        }
        if (isset($result['data']['message'])) {
            return $result['data']['message'];
        }
        return $default;
    }

    protected function requirePost()
    {
        if (!Yii::app()->getRequest()->getIsPostRequest()) {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }
    }

    protected function jsonResponse($success, $message, $data = null)
    {
        echo CJSON::encode(array('success' => $success, 'message' => $message, 'data' => $data));
        Yii::app()->end();
    }

    /**
     * Dropdown nội dung thi đấu (môn), con thụt lề theo cha.
     */
    protected function getSportDropdown()
    {
        $tree = Sports::buildTreeData();
        $list = array();
        foreach ($tree['items'] as $sport) {
            $level = isset($tree['levelMap'][$sport->id]) ? $tree['levelMap'][$sport->id] : 0;
            $prefix = str_repeat('— ', $level);
            $list[$sport->id] = $prefix . $sport->name;
        }
        return $list;
    }
}
