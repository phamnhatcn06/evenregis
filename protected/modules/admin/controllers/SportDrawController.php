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
            'sportList' => $this->getSportDropdown($eventId),
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

        // Nguồn play-off để đặt chỗ trước vào vòng bảng (đã cấu hình, hoặc tự dò
        // giai đoạn play-off duy nhất cùng nội dung). Chỉ áp dụng cho thể thức bảng.
        $playoffSource = null;
        if (SportStages::isGroupFormat($model->format)) {
            if ($model->playoff_source_stage_id) {
                $playoffSource = (int) $model->playoff_source_stage_id;
            } else {
                $siblings = SportStages::getApiDataProvider(array(
                    'event_id' => $model->event_id,
                    'sport_id' => $model->sport_id,
                ), 200)->getData();
                $playoffStages = array();
                foreach ($siblings as $s) {
                    if ($s->format === SportStages::FORMAT_PLAYOFF) {
                        $playoffStages[] = (int) $s->id;
                    }
                }
                if (count($playoffStages) === 1) {
                    $playoffSource = $playoffStages[0];
                }
            }
        }

        // Nguồn vòng loại để lấy đội nhất bảng vào vòng chung kết (đã cấu hình,
        // hoặc tự dò giai đoạn vòng loại duy nhất cùng nội dung). Chỉ áp dụng cho
        // giai đoạn chung kết thể thức vòng tròn.
        $qualSource = null;
        if ($model->stage_type === 'final' && SportStages::isGroupFormat($model->format)) {
            if ($model->qual_source_stage_id) {
                $qualSource = (int) $model->qual_source_stage_id;
            } else {
                $siblings = SportStages::getApiDataProvider(array(
                    'event_id' => $model->event_id,
                    'sport_id' => $model->sport_id,
                ), 200)->getData();
                $qualStages = array();
                foreach ($siblings as $s) {
                    if ($s->id != $model->id && $s->stage_type === 'qualification') {
                        $qualStages[] = (int) $s->id;
                    }
                }
                if (count($qualStages) === 1) {
                    $qualSource = $qualStages[0];
                }
            }
        }

        $this->render('view', array(
            'model' => $model,
            'preview' => $preview,
            'playoffSource' => $playoffSource,
            'qualSource' => $qualSource,
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

        // Sau mỗi lần lưu, JS gọi lại action này qua AJAX để refresh riêng phần
        // bảng (đội thắng đã được điền vào vòng sau) thay vì tải lại cả trang.
        if (Yii::app()->getRequest()->getIsAjaxRequest()) {
            $this->renderPartial('_board', array(
                'model' => $model,
                'preview' => $preview,
            ));
            return;
        }

        $this->render('results', array(
            'model' => $model,
            'preview' => $preview,
        ));
    }

    /**
     * Xuất biên bản trận đấu (.docx) cho một giai đoạn.
     *
     * Dùng CHÍNH file Word mẫu của người dùng (docs/bienban/Bien_ban_bong_ban_template.docx)
     * làm khuôn — giữ nguyên 100% định dạng. Mỗi trận đấu thành 1 trang A4 gồm
     * hai bản biên bản giống nhau (bản lưu + bản cắt rời); hệ thống chỉ điền
     * Mã trận / tên đơn vị / cầu thủ và tích các ô Đơn-Đôi, Nam-Nữ, vòng đấu.
     */
    public function actionExportMinutes($id)
    {
        $data = $this->buildMinutesData((int) $id);
        if ($data === null) {
            throw new CHttpException(404, 'Không tìm thấy giai đoạn.');
        }
        $this->streamMinutesDocx($data);
    }

    /**
     * Dựng file .docx từ template Word và tải về.
     *
     * @param array $data Kết quả buildMinutesData()
     */
    protected function streamMinutesDocx($data)
    {
        $template = dirname(Yii::app()->basePath) . '/docs/bienban/Bien_ban_bong_ban_template.docx';
        if (!is_file($template)) {
            throw new CHttpException(500, 'Không tìm thấy file Word mẫu biên bản.');
        }

        // Đọc document.xml từ template.
        $zip = new ZipArchive();
        if ($zip->open($template) !== true) {
            throw new CHttpException(500, 'Không mở được file Word mẫu.');
        }
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        if ($xml === false) {
            throw new CHttpException(500, 'File Word mẫu không hợp lệ.');
        }

        // Tách: phần mở <w:body ...> | khối một trang (lặp lại mỗi trận) | <w:sectPr>...
        $bodyTagEnd = strpos($xml, '>', strpos($xml, '<w:body')) + 1;
        $sectStart = strrpos($xml, '<w:sectPr');
        $pre = substr($xml, 0, $bodyTagEnd);
        $pageUnit = substr($xml, $bodyTagEnd, $sectStart - $bodyTagEnd);
        $tail = substr($xml, $sectStart);

        $pageBreak = '<w:p><w:r><w:br w:type="page"/></w:r></w:p>';

        // Mỗi tờ A4 chứa 2 biên bản của 2 trận khác nhau (copy trên = suffix 1,
        // copy dưới = suffix 2). Ghép lần lượt 2 trận một tờ.
        $pages = array();
        $matches = array_values($data['matches']);
        $total = count($matches);
        for ($i = 0; $i < $total; $i += 2) {
            $m1 = $matches[$i];
            $m2 = isset($matches[$i + 1]) ? $matches[$i + 1] : null;
            $tokens = $this->minutesTokens($m1, $data, '1') + $this->minutesTokens($m2, $data, '2');
            $pages[] = strtr($pageUnit, $tokens);
        }
        if (empty($pages)) {
            // Không có trận đủ hai đội -> xuất 1 tờ trống theo mẫu.
            $tokens = $this->minutesTokens(null, $data, '1') + $this->minutesTokens(null, $data, '2');
            $pages[] = strtr($pageUnit, $tokens);
        }

        $newXml = $pre . implode($pageBreak, $pages) . $tail;

        // Ghi ra file tạm rồi tải về (ZipArchive cần thao tác trên file).
        $tmp = Yii::app()->runtimePath . '/bienban_' . getmypid() . '_' . time() . '.docx';
        if (!@copy($template, $tmp)) {
            throw new CHttpException(500, 'Không tạo được file tạm.');
        }
        $out = new ZipArchive();
        if ($out->open($tmp) !== true) {
            @unlink($tmp);
            throw new CHttpException(500, 'Không ghi được file biên bản.');
        }
        $out->addFromString('word/document.xml', $newXml);
        $out->close();

        $slug = $this->asciiSlug($data['sportName'] . '-' . $data['stage']['name']);
        $filename = 'Bien-ban-' . ($slug !== '' ? $slug : 'tran-dau') . '.docx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tmp));
        header('Cache-Control: no-cache, must-revalidate');
        readfile($tmp);
        @unlink($tmp);
        Yii::app()->end();
    }

    /**
     * Bảng thay thế token -> giá trị thật cho một trận (hoặc trang trống nếu null).
     *
     * @param array|null $m   Trận (kèm team_a/team_b) hoặc null.
     * @param array $data      Kết quả buildMinutesData (isDoubles/isFemale).
     * @param string $suf      Hậu tố copy trên tờ ('1' hoặc '2').
     * @return array map token => giá trị đã escape XML.
     */
    protected function minutesTokens($m, $data, $suf)
    {
        $box = function ($on) {
            return $on ? '☒' : '□';
        };
        $x = function ($s) {
            return htmlspecialchars((string) $s, ENT_QUOTES | ENT_XML1, 'UTF-8');
        };

        $org = function ($team) {
            if (!$team) return '';
            if (!empty($team['property_name'])) return $team['property_name'];
            return isset($team['name']) ? $team['name'] : '';
        };
        $players = function ($team) {
            if (!$team || empty($team['players'])) return '';
            return implode(' - ', $team['players']);
        };
        $phones = function ($team) {
            if (!$team || empty($team['phones'])) return '';
            $list = array();
            foreach ($team['phones'] as $p) {
                $p = trim((string) $p);
                if ($p !== '') {
                    $list[] = $p;
                }
            }
            return implode(' - ', $list);
        };

        $rKey = $m ? $this->minutesRoundKey($m) : '';

        return array(
            '@@MA' . $suf . '@@'  => $m && isset($m['code']) ? $x($m['code']) : '',
            '@@DVA' . $suf . '@@' => $m ? $x($org($m['team_a'])) : '',
            '@@DVB' . $suf . '@@' => $m ? $x($org($m['team_b'])) : '',
            '@@CTA' . $suf . '@@' => $m ? $x($players($m['team_a'])) : '',
            '@@CTB' . $suf . '@@' => $m ? $x($players($m['team_b'])) : '',
            '@@DTA' . $suf . '@@' => $m ? $x($phones($m['team_a'])) : '',
            '@@DTB' . $suf . '@@' => $m ? $x($phones($m['team_b'])) : '',
            '@@BDON' . $suf . '@@' => $box($m && empty($data['isDoubles'])),
            '@@BDOI' . $suf . '@@' => $box($m && !empty($data['isDoubles'])),
            '@@BNAM' . $suf . '@@' => $box($m && empty($data['isFemale'])),
            '@@BNU' . $suf . '@@'  => $box($m && !empty($data['isFemale'])),
            '@@BQL' . $suf . '@@'  => $box($rKey === 'qualify'),
            '@@B16' . $suf . '@@'  => $box($rKey === 'r16'),
            '@@BTK' . $suf . '@@'  => $box($rKey === 'quarter'),
            '@@BBK' . $suf . '@@'  => $box($rKey === 'semi'),
            '@@BBT' . $suf . '@@'  => $box($rKey === 'third'),
            '@@BCK' . $suf . '@@'  => $box($rKey === 'final'),
        );
    }

    /**
     * Xác định vòng đấu để tích ô: qualify | r16 | quarter | semi | third | final.
     */
    protected function minutesRoundKey($m)
    {
        $type = isset($m['match_type']) ? $m['match_type'] : '';
        if ($type === 'group' || $type === 'playoff') {
            return 'qualify';
        }
        $round = isset($m['round']) ? mb_strtolower($m['round'], 'UTF-8') : '';
        if (strpos($round, 'chung kết') !== false) return 'final';
        if (strpos($round, 'hạng 3') !== false || strpos($round, 'ba-tư') !== false || strpos($round, 'tranh hạng') !== false) return 'third';
        if (strpos($round, 'bán kết') !== false) return 'semi';
        if (strpos($round, 'tứ kết') !== false) return 'quarter';
        if (strpos($round, '1/16') !== false) return 'r16';
        return 'qualify';
    }

    /**
     * Tạo slug ASCII từ chuỗi tiếng Việt cho tên file tải về.
     */
    protected function asciiSlug($str)
    {
        $str = (string) $str;
        $map = array(
            'à','á','ạ','ả','ã','â','ầ','ấ','ậ','ẩ','ẫ','ă','ằ','ắ','ặ','ẳ','ẵ',
            'è','é','ẹ','ẻ','ẽ','ê','ề','ế','ệ','ể','ễ',
            'ì','í','ị','ỉ','ĩ','ò','ó','ọ','ỏ','õ','ô','ồ','ố','ộ','ổ','ỗ','ơ','ờ','ớ','ợ','ở','ỡ',
            'ù','ú','ụ','ủ','ũ','ư','ừ','ứ','ự','ử','ữ','ỳ','ý','ỵ','ỷ','ỹ','đ',
        );
        $rep = array(
            'a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a',
            'e','e','e','e','e','e','e','e','e','e','e',
            'i','i','i','i','i','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o',
            'u','u','u','u','u','u','u','u','u','u','u','y','y','y','y','y','d',
        );
        $lower = function_exists('mb_strtolower') ? mb_strtolower($str, 'UTF-8') : strtolower($str);
        $lower = str_replace($map, $rep, $lower);
        $lower = preg_replace('/[^a-z0-9]+/', '-', $lower);
        return trim($lower, '-');
    }

    /**
     * Gom dữ liệu các trận đấu + thông tin đội/VĐV cho trang biên bản.
     *
     * Đọc qua API backend (giống các action khác của màn bốc thăm) để chạy đúng
     * trên cả prod (Yii đọc `yii_event`, dữ liệu thể thao nằm ở DB Laravel) lẫn
     * local — KHÔNG truy vấn trực tiếp `Yii::app()->db`.
     *
     * @return array|null null nếu không tìm thấy giai đoạn.
     */
    protected function buildMinutesData($stageId)
    {
        $stage = SportStages::fetchFromApi($stageId);
        if ($stage === null) {
            return null;
        }

        $preview = SportStages::fetchDrawPreview($stageId);
        $teams = $this->buildTeamInfoMap($preview);

        // Chỉ lấy các trận đã xác định đủ hai đội (bỏ BYE và trận còn chờ).
        $matches = array();
        $rawMatches = ($preview && isset($preview['matches'])) ? $preview['matches'] : array();
        foreach ($rawMatches as $m) {
            $aId = isset($m['team_a_id']) ? (int) $m['team_a_id'] : 0;
            $bId = isset($m['team_b_id']) ? (int) $m['team_b_id'] : 0;
            if ($aId <= 0 || $bId <= 0) {
                continue;
            }
            $m['team_a'] = isset($teams[$aId]) ? $teams[$aId] : null;
            $m['team_b'] = isset($teams[$bId]) ? $teams[$bId] : null;
            $matches[] = $m;
        }

        $sportName = $stage->sport_name ? $stage->sport_name : '';
        $nameLower = function_exists('mb_strtolower') ? mb_strtolower($sportName, 'UTF-8') : strtolower($sportName);
        $isDoubles = (strpos($nameLower, 'đôi') !== false);
        $isFemale = (strpos($nameLower, 'nữ') !== false);

        return array(
            'stage' => array('name' => $stage->name),
            'sportName' => $sportName,
            'eventName' => '',
            'matches' => $matches,
            'isDoubles' => $isDoubles,
            'isFemale' => $isFemale,
        );
    }

    /**
     * Dựng map team_id => thông tin hiển thị (tên đơn vị, cầu thủ, đội trưởng)
     * từ mảng `teams` của bản xem trước bốc thăm (fetchDrawPreview).
     *
     * @param array|null $preview
     * @return array map team_id => thông tin
     */
    protected function buildTeamInfoMap($preview)
    {
        $result = array();
        $teams = ($preview && isset($preview['teams'])) ? $preview['teams'] : array();
        foreach ($teams as $t) {
            $tid = isset($t['team_id']) ? (int) $t['team_id'] : 0;
            if ($tid <= 0) {
                continue;
            }
            $players = (isset($t['player_names']) && is_array($t['player_names'])) ? $t['player_names'] : array();
            $phones = (isset($t['player_phones']) && is_array($t['player_phones'])) ? $t['player_phones'] : array();
            $result[$tid] = array(
                'team_id' => $tid,
                'name' => isset($t['team_name']) ? $t['team_name'] : null,
                'property_name' => isset($t['property_name']) ? $t['property_name'] : null,
                'players' => $players,
                'phones' => $phones,
                'captain' => isset($t['captain_name']) ? $t['captain_name'] : null,
                // Trưởng đoàn chưa có trong dữ liệu bốc thăm -> để trống cho điền tay.
                'team_lead' => null,
            );
        }
        return $result;
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
     * Xoá một trận đấu (AJAX). Dùng khi lỡ tạo nhầm trận play-off / knockout.
     */
    public function actionDeleteMatch()
    {
        $this->requirePost();

        $matchId = Yii::app()->getRequest()->getPost('match_id');
        if (!$matchId) {
            $this->jsonResponse(false, 'Thiếu ID trận đấu.');
        }

        // Trận "ảo" (xem trước khung nhánh) chưa được lưu xuống DB, không cần xoá.
        if (strpos((string) $matchId, 'ko_') === 0) {
            $this->jsonResponse(false, 'Đây là trận xem trước, chưa được tạo nên không cần xoá.');
        }

        $url = ApiEndpoints::url(ApiEndpoints::SPORT_MATCH_DESTROY, array('id' => $matchId));
        $result = ApiClient::delete($url);

        if (!empty($result['success'])) {
            $this->jsonResponse(true, 'Đã xoá trận đấu.');
        }
        $this->jsonResponse(false, $this->apiError($result, 'Không thể xoá trận đấu.'));
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

        // Lưu ý: KHÔNG chặn khi vòng bảng chưa có kết quả. Backend sẽ dựng khung sơ đồ
        // nhánh với các trận "Chờ xác định" (chỉ điền đội khi toàn bộ vòng bảng đá xong),
        // nên việc bấm nút lúc này chỉ tạo/đặt lại khung chờ — đúng mong muốn nghiệp vụ.

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

        // Backend đã xử lý nhưng từ chối vì lý do nghiệp vụ (vd chưa có BXH, sai số bảng):
        // báo đúng lỗi, KHÔNG fallback (fallback chỉ biết ghép chéo 3-1-0, sai với thể thức xếp hạng)
        $httpCode = isset($result['code']) ? (int) $result['code'] : 0;
        if ($httpCode >= 200 && $httpCode < 500) {
            $this->jsonResponse(false, $this->apiError($result, 'Không thể sinh sơ đồ nhánh.'));
        }

        // Fallback: Đồng bộ trực tiếp qua cơ sở dữ liệu nếu API không kết nối được / lỗi máy chủ
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

            // Vòng bảng chỉ coi là kết thúc khi mọi trận đều đã có kết quả.
            // Khi chưa kết thúc, các trận Bán kết phải để "Chờ xác định" (team_id = null).
            $groupComplete = !empty($matches);
            foreach ($matches as $m) {
                if ($m['score_a'] === null || $m['score_b'] === null || $m['score_a'] === '' || $m['score_b'] === '') {
                    $groupComplete = false;
                    break;
                }
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
            if ($groupComplete) {
                $team1A = $qualifiers['A'][1] ?? null;
                $team2B = $qualifiers['B'][2] ?? null;
                $team1B = $qualifiers['B'][1] ?? null;
                $team2A = $qualifiers['A'][2] ?? null;
            } else {
                // Vòng bảng chưa kết thúc -> chưa xác định đội vào Bán kết
                $team1A = $team2B = $team1B = $team2A = null;
            }

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
                'message' => $groupComplete
                    ? 'Đã cập nhật danh sách đội từ Bảng xếp hạng vào sơ đồ nhánh!'
                    : 'Đã tạo sơ đồ nhánh. Vòng bảng chưa kết thúc nên các trận Bán kết đang chờ xác định đội.',
                'data' => array('stage_id' => $stageId, 'group_complete' => $groupComplete),
            );
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Lỗi đồng bộ sơ đồ nhánh: ' . $e->getMessage());
        }
    }

    /**
     * Bốc thăm play-off (AJAX) — các trận độc lập, người thắng chuyển sang vòng bảng.
     */
    public function actionDrawPlayoff($id)
    {
        $this->requirePost();
        $result = SportStages::drawPlayoff($id);
        $this->handleDrawResult($result);
    }

    /**
     * Đặt chỗ trước đội thắng play-off vào vòng bảng đã bốc (AJAX).
     * Không xáo lại đội hiện có; chỉ thêm slot giữ chỗ + các trận vòng tròn liên quan.
     */
    public function actionReservePlayoff($id)
    {
        $this->requirePost();
        $sourceStageId = Yii::app()->getRequest()->getPost('source_stage_id');
        $result = SportStages::reservePlayoffSlots($id, $sourceStageId ? (int) $sourceStageId : null);
        if (!empty($result['success'])) {
            $this->jsonResponse(true, 'Đã đặt chỗ play-off vào vòng bảng.', isset($result['data']) ? $result['data'] : null);
        }
        $this->jsonResponse(false, $this->apiError($result, 'Không thể đặt chỗ play-off.'));
    }

    /**
     * Tạo slot lấy đội nhất bảng vòng loại vào vòng chung kết (AJAX).
     * Thêm các slot giữ chỗ "Nhất bảng A/B/C" + sinh lịch vòng tròn; đội thật tự
     * điền khi bảng vòng loại tương ứng đá xong.
     */
    public function actionReserveGroupWinners($id)
    {
        $this->requirePost();
        $qualStageId = Yii::app()->getRequest()->getPost('qual_source_stage_id');
        $result = SportStages::reserveGroupWinnerSlots($id, $qualStageId ? (int) $qualStageId : null);
        if (!empty($result['success'])) {
            $this->jsonResponse(true, 'Đã tạo slot lấy đội nhất bảng vào vòng chung kết.', isset($result['data']) ? $result['data'] : null);
        }
        $this->jsonResponse(false, $this->apiError($result, 'Không thể tạo slot vòng chung kết.'));
    }

    /**
     * Danh sách VĐV/đội ở vòng chung kết của nội dung (để chọn đưa vào play-off) (AJAX).
     */
    public function actionPlayoffEntrants($id)
    {
        $stage = SportStages::fetchFromApi($id);
        if ($stage === null) {
            $this->jsonResponse(false, 'Không tìm thấy giai đoạn.');
        }

        $data = array();
        foreach (SportTeams::getFinalists($stage->event_id, $stage->sport_id) as $f) {
            $teamId = isset($f['team_id']) ? $f['team_id'] : null;
            if ($teamId === null) {
                continue;
            }
            $team = isset($f['team']) && is_array($f['team']) ? $f['team'] : array();
            $name = isset($team['name']) && $team['name'] !== null ? $team['name'] : ('#' . $teamId);

            $members = (isset($team['members']) && is_array($team['members'])) ? $team['members'] : array();
            if (count($members) >= 1 && count($members) <= 2) {
                $names = array();
                foreach ($members as $mm) {
                    $nm = isset($mm['name']) ? $mm['name'] : (isset($mm['attendee_name']) ? $mm['attendee_name'] : '');
                    if ($nm !== '') {
                        $names[] = $nm;
                    }
                }
                if (!empty($names)) {
                    $name .= ' — ' . implode(' + ', $names);
                }
            }

            $data[] = array(
                'team_id' => $teamId,
                'name' => $name,
            );
        }

        $this->jsonResponse(true, '', $data);
    }

    /**
     * Chuyển các đội/VĐV đã chọn từ vòng chung kết sang giai đoạn play-off (AJAX).
     */
    public function actionMovePlayoffEntrants($id)
    {
        $this->requirePost();
        $raw = Yii::app()->getRequest()->getPost('team_ids', array());
        $teamIds = is_array($raw) ? $raw : array_filter(explode(',', (string) $raw));
        $teamIds = array_values(array_filter(array_map('intval', $teamIds)));
        if (empty($teamIds)) {
            $this->jsonResponse(false, 'Vui lòng chọn ít nhất một đội/VĐV.');
        }

        $result = SportStages::movePlayoffEntrants($id, $teamIds);
        if (!empty($result['success'])) {
            $moved = isset($result['data']['moved']) ? count($result['data']['moved']) : 0;
            $this->jsonResponse(true, 'Đã chuyển ' . $moved . ' VĐV vào play-off.', isset($result['data']) ? $result['data'] : null);
        }
        $this->jsonResponse(false, $this->apiError($result, 'Không thể chuyển VĐV vào play-off.'));
    }

    /**
     * Chuyển người/đội thắng play-off vào giai đoạn vòng bảng đích (AJAX).
     */
    public function actionPromotePlayoff($id)
    {
        $this->requirePost();
        $targetStageId = (int) Yii::app()->getRequest()->getPost('target_stage_id', 0);
        if ($targetStageId < 1) {
            $this->jsonResponse(false, 'Thiếu giai đoạn vòng bảng đích.');
        }
        $result = SportStages::promotePlayoffWinners($id, $targetStageId);
        if ($result['success']) {
            $data = isset($result['data']) ? $result['data'] : null;
            $this->jsonResponse(true, 'Đã chuyển người thắng play-off vào vòng bảng.', $data);
        }
        $this->jsonResponse(false, $this->apiError($result, 'Không thể chuyển người thắng.'));
    }

    /**
     * Chuyển đội đi tiếp của vòng loại chia bảng sang vòng chung kết (AJAX).
     */
    public function actionPromoteGroups($id)
    {
        $this->requirePost();
        $targetStageId = (int) Yii::app()->getRequest()->getPost('target_stage_id', 0);
        if ($targetStageId < 1) {
            $this->jsonResponse(false, 'Thiếu giai đoạn chung kết đích.');
        }
        $result = SportStages::promoteGroupWinners($id, $targetStageId);
        if ($result['success']) {
            $this->jsonResponse(true, 'Đã chuyển đội đi tiếp vào vòng chung kết.', isset($result['data']) ? $result['data'] : null);
        }
        $this->jsonResponse(false, $this->apiError($result, 'Không thể chuyển đội đi tiếp.'));
    }

    /**
     * Bơi: bốc thăm phân lượt/làn (AJAX).
     */
    public function actionDrawSwimHeats($id)
    {
        $this->requirePost();
        $laneCount = (int) Yii::app()->getRequest()->getPost('lane_count', 6);
        $result = SportStages::drawSwimHeats($id, $laneCount > 0 ? $laneCount : 6);
        $this->handleDrawResult($result);
    }

    /**
     * Bơi: chuyển Top N vòng loại vào chung kết (AJAX).
     */
    public function actionPromoteSwim($id)
    {
        $this->requirePost();
        $finalStageId = (int) Yii::app()->getRequest()->getPost('final_stage_id', 0);
        $topN = (int) Yii::app()->getRequest()->getPost('top_n', 6);
        if ($finalStageId < 1) {
            $this->jsonResponse(false, 'Thiếu giai đoạn chung kết đích.');
        }
        $result = SportStages::promoteSwimTop($id, $finalStageId, $topN > 0 ? $topN : 6);
        if ($result['success']) {
            $this->jsonResponse(true, 'Đã chuyển Top vào chung kết.', isset($result['data']) ? $result['data'] : null);
        }
        $this->jsonResponse(false, $this->apiError($result, 'Không thể chuyển Top.'));
    }

    /**
     * Bơi: màn nhập thành tích theo làn.
     */
    public function actionSwimTimes($id)
    {
        $model = SportStages::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy giai đoạn.');
        }
        $preview = SportStages::fetchDrawPreview($id);
        $heats = ($preview && isset($preview['swim_heats'])) ? $preview['swim_heats'] : array();
        $this->render('swimTimes', array(
            'model' => $model,
            'heats' => $heats,
        ));
    }

    /**
     * Bơi: lưu thành tích (AJAX). POST times[lane_id] = "mm:ss.xx" đã đổi sang ms ở JS.
     */
    public function actionSaveSwimTimes($id)
    {
        $this->requirePost();
        $times = Yii::app()->getRequest()->getPost('times', array());
        $lanes = Yii::app()->getRequest()->getPost('lanes', array());
        $result = SportStages::recordSwimTimes(
            $id,
            is_array($times) ? $times : array(),
            is_array($lanes) ? $lanes : array()
        );
        if ($result['success']) {
            $this->jsonResponse(true, 'Đã lưu thành tích.', isset($result['data']) ? $result['data'] : null);
        }
        $this->jsonResponse(false, $this->apiError($result, 'Không thể lưu thành tích.'));
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
    /**
     * Danh sách nội dung thi đấu cho dropdown.
     *
     * Khi đã chọn sự kiện, chỉ lấy những nội dung được cấu hình (active) cho sự
     * kiện đó qua event_sports. Giữ lại các nội dung cha của nội dung active để
     * cây phân cấp (thụt lề) vẫn đúng.
     */
    protected function getSportDropdown($eventId = null)
    {
        $tree = Sports::buildTreeData();

        $allowedIds = null;
        if ($eventId) {
            $activeSportIds = array();
            foreach (EventSports::getByEventId($eventId) as $es) {
                $sportId = isset($es['sport_id']) ? $es['sport_id'] : null;
                if ($sportId) {
                    $activeSportIds[$sportId] = true;
                }
            }

            // Build map id => parent_id để truy ngược tổ tiên.
            $parentOf = array();
            foreach ($tree['items'] as $sport) {
                $parentOf[$sport->id] = $sport->parent_id ? $sport->parent_id : 0;
            }

            $allowedIds = array();
            foreach ($activeSportIds as $id => $_) {
                $cursor = $id;
                while ($cursor && !isset($allowedIds[$cursor])) {
                    $allowedIds[$cursor] = true;
                    $cursor = isset($parentOf[$cursor]) ? $parentOf[$cursor] : 0;
                }
            }
        }

        $list = array();
        foreach ($tree['items'] as $sport) {
            if ($allowedIds !== null && !isset($allowedIds[$sport->id])) {
                continue;
            }
            $level = isset($tree['levelMap'][$sport->id]) ? $tree['levelMap'][$sport->id] : 0;
            $prefix = str_repeat('— ', $level);
            $list[$sport->id] = $prefix . $sport->name;
        }
        return $list;
    }
}
