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
     * Sinh sơ đồ loại trực tiếp từ kết quả vòng bảng (AJAX).
     */
    public function actionGenerateKnockout($id)
    {
        $this->requirePost();
        $result = SportStages::generateKnockout($id);
        $this->handleDrawResult($result);
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
