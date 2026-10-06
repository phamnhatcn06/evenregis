<?php

class SportDrawController extends AdminController
{
    /**
     * Bốc thăm là một phần của quản lý thể thao → dùng chung quyền 'sport'
     * thay vì yêu cầu một permission 'sportdraw' riêng từ Portal.
     */
    public function beforeAction($action)
    {
        if (!Controller::beforeAction($action)) {
            return false;
        }

        $actionId = strtolower($action->id);
        $operation = $this->mapActionToOperation($actionId);

        if ($operation && !PermissionHelper::can('sport', $operation)) {
            throw new CHttpException(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        return true;
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
