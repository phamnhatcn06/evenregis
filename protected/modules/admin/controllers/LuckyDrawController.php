<?php

/**
 * Quay số may mắn (Lucky Draw).
 *
 * Hai lớp bảo vệ: (1) quyền `luckydraw` từ Portal SSO, (2) mã truy cập (access code)
 * do lucky-show.js hỏi khi mở màn quay.
 *
 * Các action `api*` là lớp ADAPTER: nhận request từ lucky-show.js, gọi API Laravel qua
 * Model LuckyDraw, rồi reshape về dạng {ok,...} mà core JS mong đợi (KHÔNG sửa core JS).
 * Vì JS poll `apiCheckRemote` mỗi 1 giây nên các action api* KHÔNG gọi validateWithPortal
 * (chỉ kiểm tra session) để tránh nện Portal liên tục.
 */
class LuckyDrawController extends AdminController
{
    /** Màn quay số & remote hiển thị toàn màn hình, không layout. */
    public $layout = false;

    public function init()
    {
        // Chỉ kiểm tra session; KHÔNG validate Portal ở đây (xử lý trong beforeAction cho
        // action trang, bỏ qua cho action api để không gọi Portal mỗi giây khi polling).
        Controller::init();

        if (!AuthHandler::isAuthenticated()) {
            if ($this->isApiRoute()) {
                $this->jsonOut(array('ok' => false, 'msg' => 'Chưa đăng nhập'));
            }
            Yii::app()->user->setFlash('error', 'Vui lòng đăng nhập.');
            $this->redirect(array('/site/login'));
            return;
        }

        $this->user = AuthHandler::getUser();
    }

    public function beforeAction($action)
    {
        if (!Controller::beforeAction($action)) {
            return false;
        }

        $actionId = strtolower($action->id);
        $isApi = strpos($actionId, 'api') === 0;

        // Quyền luckydraw theo thao tác
        $operation = $this->luckyOperation($actionId);
        if (!PermissionHelper::can('luckydraw', $operation)) {
            if ($isApi) {
                $this->jsonOut(array('ok' => false, 'msg' => 'Không có quyền quay số'));
            }
            throw new CHttpException(403, 'Bạn không có quyền truy cập Quay số may mắn.');
        }

        // Chỉ validate Portal cho action trang (mỗi lần tải trang), bỏ qua action api polling.
        if (!$isApi && !AuthHandler::validateWithPortal()) {
            Yii::app()->user->setFlash('error', 'Phiên đăng nhập đã hết hạn.');
            $this->redirect(array('/site/login'));
            return false;
        }

        return true;
    }

    /** Map action → thao tác quyền. */
    protected function luckyOperation($actionId)
    {
        if (strpos($actionId, 'delete') !== false || strpos($actionId, 'clear') !== false) {
            return 'delete';
        }
        // Import danh sách trúng giải dùng chung quyền 'update' với cài đặt (admin lucky đã có).
        if (in_array($actionId, array('saveprize', 'savesettings', 'resetwinners', 'import', 'parseexcel', 'saveimport'), true)) {
            return 'update';
        }
        // index/admin/show/remote/downloadtemplate + toàn bộ api* → read
        return 'read';
    }

    protected function isApiRoute()
    {
        $route = strtolower(Yii::app()->getRequest()->getParam('r', ''));
        // Với URL path-format, suy ra từ pathInfo
        $path = strtolower(Yii::app()->getRequest()->getPathInfo());
        return strpos($route, 'luckydraw/api') !== false || strpos($path, 'luckydraw/api') !== false;
    }

    // ===================== TRANG =====================

    /** Quy ước menu admin. */
    public function actionAdmin()
    {
        $this->actionIndex();
    }

    /** Màn hình cài đặt cho admin (giải thưởng + cấu hình). */
    public function actionIndex()
    {
        $this->layout = '//layouts/column2';

        $prizes = LuckyDraw::prizes();
        $cfg = LuckyDraw::settings();

        $this->render('index', array(
            'prizes' => $prizes,
            'settings' => isset($cfg['settings']) ? $cfg['settings'] : array(),
            'types' => isset($cfg['types']) ? $cfg['types'] : array(),
            'eventList' => Events::getListForDropdown(),
            'canUpdate' => PermissionHelper::can('luckydraw', 'update'),
            'canDelete' => PermissionHelper::can('luckydraw', 'delete'),
        ));
    }

    /** Màn quay số toàn màn hình. */
    public function actionShow()
    {
        $this->layout = false;

        $prize = LuckyDraw::currentPrize();
        $winnerList = array();
        if ($prize && isset($prize['id'])) {
            $winnerList = LuckyDraw::confirmedWinners($prize['id']);
        }
        $cfg = LuckyDraw::settings();
        $settings = isset($cfg['settings']) ? $cfg['settings'] : array();

        $this->render('show', array(
            'prize' => $prize,
            'winnerList' => $winnerList,
            'backgroundPath' => isset($settings['background_path']) ? $settings['background_path'] : '',
            'eventTitle' => isset($settings['event_title']) ? $settings['event_title'] : 'Quay số may mắn',
        ));
    }

    /** Bảng điều khiển từ xa (remote) toàn màn hình. */
    public function actionRemote()
    {
        $this->layout = false;
        $this->render('remote', array(
            'prize' => LuckyDraw::currentPrize(),
        ));
    }

    // ===================== SETTINGS (POST) =====================

    public function actionSavePrize()
    {
        $input = $_POST;
        unset($input['YII_CSRF_TOKEN']);
        $result = LuckyDraw::savePrize($input);
        if (!empty($result['success'])) {
            $this->jsonOut(array('success' => true, 'message' => 'Lưu giải thành công', 'data' => LuckyDraw::prizes()));
        }
        $this->jsonOut(array('success' => false, 'message' => LuckyDraw::errorMessage($result, 'Không lưu được giải')));
    }

    public function actionDeletePrize($id)
    {
        $result = LuckyDraw::deletePrize($id);
        if (!empty($result['success'])) {
            $this->jsonOut(array('success' => true, 'message' => 'Đã xoá giải'));
        }
        $this->jsonOut(array('success' => false, 'message' => LuckyDraw::errorMessage($result, 'Không xoá được giải')));
    }

    public function actionSaveSettings()
    {
        $input = $_POST;
        unset($input['YII_CSRF_TOKEN']);
        // exclude_columns gửi dạng JSON string từ form → decode thành mảng
        if (isset($input['exclude_columns']) && is_string($input['exclude_columns'])) {
            $decoded = json_decode($input['exclude_columns'], true);
            $input['exclude_columns'] = is_array($decoded) ? $decoded : array();
        }
        $result = LuckyDraw::saveSettings($input);
        if (!empty($result['success'])) {
            $this->jsonOut(array('success' => true, 'message' => 'Lưu cấu hình thành công'));
        }
        $this->jsonOut(array('success' => false, 'message' => LuckyDraw::errorMessage($result, 'Không lưu được cấu hình')));
    }

    public function actionResetWinners()
    {
        $result = LuckyDraw::resetWinners();
        if (!empty($result['success'])) {
            $this->jsonOut(array('success' => true, 'message' => 'Đã reset danh sách trúng giải'));
        }
        $this->jsonOut(array('success' => false, 'message' => LuckyDraw::errorMessage($result)));
    }

    // ===================== API ADAPTER (cho lucky-show.js) =====================

    public function actionApiPrize()
    {
        $p = LuckyDraw::currentPrize();
        $this->jsonOut(array('ok' => true, 'data' => $p ?: null));
    }

    public function actionApiStatus()
    {
        $s = LuckyDraw::status();
        $remaining = (is_array($s) && isset($s['remaining'])) ? (int) $s['remaining'] : 0;
        $this->jsonOut(array('ok' => true, 'data' => array('remaining' => $remaining)));
    }

    public function actionApiWinners()
    {
        $prizeId = (int) Yii::app()->getRequest()->getParam('prize_id');
        $this->jsonOut(array('ok' => true, 'data' => LuckyDraw::confirmedWinners($prizeId)));
    }

    public function actionApiSpin()
    {
        $result = LuckyDraw::spin();
        if (!empty($result['success'])) {
            $payload = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
            $this->jsonOut(array('ok' => true, 'data' => $payload));
        }
        $this->jsonOut(array('ok' => false, 'error' => LuckyDraw::errorMessage($result, 'Quay thất bại')));
    }

    public function actionApiConfirmWinner()
    {
        $body = $this->jsonBody();
        $id = isset($body['id']) ? $body['id'] : null;
        $prizeId = isset($body['prize_id']) ? (int) $body['prize_id'] : 0;
        LuckyDraw::confirmWinner($id, $prizeId);
        $this->jsonOut(array('ok' => true));
    }

    public function actionApiCancelWinner()
    {
        $body = $this->jsonBody();
        $id = isset($body['id']) ? $body['id'] : null;
        $prizeId = isset($body['prize_id']) ? (int) $body['prize_id'] : 0;
        if ($id === null || $id === 'undefined') {
            $this->jsonOut(array('ok' => false, 'msg' => 'Missing params'));
        }
        $result = LuckyDraw::cancelWinner($id, $prizeId);
        if (!empty($result['success'])) {
            $this->jsonOut(array('ok' => true));
        }
        $this->jsonOut(array('ok' => false, 'error' => LuckyDraw::errorMessage($result)));
    }

    public function actionApiNextPrize()
    {
        $result = LuckyDraw::nextPrize();
        if (!empty($result['success'])) {
            $this->jsonOut(array('ok' => true));
        }
        $this->jsonOut(array('ok' => false, 'msg' => LuckyDraw::errorMessage($result, 'Đã hết toàn bộ giải')));
    }

    public function actionApiCheckAuth()
    {
        $body = $this->jsonBody();
        $code = isset($body['code']) ? $body['code'] : '';
        $result = LuckyDraw::checkAuth($code);
        if (!empty($result['success'])) {
            $this->jsonOut(array('ok' => true));
        }
        $this->jsonOut(array('ok' => false, 'msg' => LuckyDraw::errorMessage($result, 'Mã truy cập không đúng!')));
    }

    public function actionApiCheckRemote()
    {
        $r = LuckyDraw::checkRemote();
        $command = (is_array($r) && isset($r['command'])) ? $r['command'] : null;
        $this->jsonOut(array('ok' => true, 'command' => $command));
    }

    public function actionApiRemoteSpin()
    {
        $result = LuckyDraw::remoteSpin();
        if (!empty($result['success'])) {
            $this->jsonOut(array('ok' => true));
        }
        $this->jsonOut(array('ok' => false, 'msg' => LuckyDraw::errorMessage($result)));
    }

    // ===================== Helpers =====================

    protected function jsonBody()
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : array();
    }

    protected function jsonOut($arr)
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/json; charset=utf-8');
        echo CJSON::encode($arr);
        Yii::app()->end();
    }
}
