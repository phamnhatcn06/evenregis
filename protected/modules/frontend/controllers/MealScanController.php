<?php

/**
 * MÀN QUÉT CHẤM ĂN (station) — PUBLIC, không SSO.
 *
 * Mỗi cửa nhà hàng mở màn này trên 1 máy có đầu đọc QR. Đăng nhập bằng
 * trạm + mã truy cập (passcode) do BTC cấu hình. Sau khi đăng nhập, station_id
 * được giữ trong SESSION phía server — trình duyệt không bao giờ thấy API key
 * và không thể giả mạo station_id (mọi lệnh quét lấy station_id từ session).
 *
 * Các action JSON (activeMeals/doScan/sync/stats) là proxy mỏng xuống External
 * API, giữ API key ở phía server.
 */
class MealScanController extends CController
{
    const SESSION_KEY = 'meal_station';
    const COOKIE_KEY = 'meal_station';
    const COOKIE_TTL = 31536000; // 1 năm — trạm không bao giờ tự hết hạn

    public function filters()
    {
        return array();
    }

    /** Trang đăng nhập trạm (chọn trạm + nhập mã truy cập). */
    public function actionIndex()
    {
        $this->layout = '//layouts/portal_login';

        if ($this->currentStation()) {
            $this->redirect(array('scan'));
            return;
        }

        $stations = $this->fetchStations();

        $this->render('login', array('stations' => $stations));
    }

    /** Xử lý đăng nhập trạm. */
    public function actionLogin()
    {
        if (!Yii::app()->request->isPostRequest) {
            $this->redirect(array('index'));
            return;
        }

        $stationId = (int) Yii::app()->request->getPost('station_id');
        $passcode = (string) Yii::app()->request->getPost('passcode');

        $result = ApiClient::post(ApiEndpoints::MEAL_CHECKIN_LOGIN, array(
            'station_id' => $stationId,
            'passcode'   => $passcode,
        ));

        $station = $this->extractData($result, 'station');
        if ($result['success'] && $station && !empty($station['id'])) {
            $data = array(
                'id'       => $station['id'],
                'name'     => $station['name'],
                'event_id' => $station['event_id'],
                'location' => isset($station['location']) ? $station['location'] : null,
            );
            Yii::app()->session[self::SESSION_KEY] = $data;
            $this->setStationCookie($data);
            $this->redirect(array('scan'));
            return;
        }

        $msg = $this->extractMessage($result, 'Tên trạm hoặc mã truy cập không đúng.');
        Yii::app()->user->setFlash('error', $msg);
        $this->redirect(array('index'));
    }

    /** Màn quét fullscreen. */
    public function actionScan()
    {
        $station = $this->currentStation();
        if (!$station) {
            $this->redirect(array('index'));
            return;
        }

        $this->layout = '//layouts/meal_station';
        $this->render('scan', array('station' => $station));
    }

    /** JSON: bữa ăn trong ngày + bữa đang mở. */
    public function actionActiveMeals()
    {
        $station = $this->requireStationJson();

        $result = ApiClient::get(ApiEndpoints::MEAL_CHECKIN_ACTIVE_MEALS, array(
            'event_id' => $station['event_id'],
        ));

        $this->renderJson($this->passthrough($result));
    }

    /** JSON: quét 1 thẻ QR. */
    public function actionDoScan()
    {
        $station = $this->requireStationJson();

        $payload = array(
            'qr_value'   => (string) Yii::app()->request->getPost('qr_value'),
            'station_id' => $station['id'],
        );
        $mealId = Yii::app()->request->getPost('meal_id');
        if ($mealId !== null && $mealId !== '') {
            $payload['meal_id'] = (int) $mealId;
        }
        $scannedAt = Yii::app()->request->getPost('scanned_at');
        if ($scannedAt !== null && $scannedAt !== '') {
            $payload['scanned_at'] = (int) $scannedAt;
        }

        $result = ApiClient::post(ApiEndpoints::MEAL_CHECKIN_SCAN, $payload);
        $this->renderJson($this->passthrough($result));
    }

    /** JSON: đồng bộ lô bản ghi offline. */
    public function actionSync()
    {
        $station = $this->requireStationJson();

        $itemsRaw = Yii::app()->request->getPost('items');
        $items = is_string($itemsRaw) ? json_decode($itemsRaw, true) : $itemsRaw;
        if (!is_array($items)) {
            $items = array();
        }

        $result = ApiClient::post(ApiEndpoints::MEAL_CHECKIN_SYNC, array(
            'station_id' => $station['id'],
            'items'      => $items,
        ));
        $this->renderJson($this->passthrough($result));
    }

    /** JSON: thống kê số lượng đã chấm của 1 bữa. */
    public function actionStats()
    {
        $this->requireStationJson();
        $mealId = (int) Yii::app()->request->getParam('meal_id');

        $result = ApiClient::get(ApiEndpoints::MEAL_CHECKIN_STATS, array('meal_id' => $mealId));
        $this->renderJson($this->passthrough($result));
    }

    public function actionLogout()
    {
        unset(Yii::app()->session[self::SESSION_KEY]);
        $this->redirect(array('index'));
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    protected function currentStation()
    {
        $s = Yii::app()->session[self::SESSION_KEY];
        return (is_array($s) && !empty($s['id'])) ? $s : null;
    }

    /** Bắt buộc có phiên trạm cho các action JSON. */
    protected function requireStationJson()
    {
        $station = $this->currentStation();
        if (!$station) {
            $this->renderJson(array(
                'success' => false,
                'code'    => 401,
                'message' => 'Phiên trạm đã hết hạn. Vui lòng đăng nhập lại.',
                'data'    => null,
            ));
        }
        return $station;
    }

    /** Lấy danh sách trạm đang hoạt động cho dropdown đăng nhập. */
    protected function fetchStations()
    {
        $result = ApiClient::get(ApiEndpoints::MEAL_CHECKIN_STATIONS);
        $data = $this->extractData($result, 'stations');
        return is_array($data) ? $data : array();
    }

    /** Chuẩn hoá body API thành mảng JSON trả cho client (giữ nguyên status nghiệp vụ). */
    protected function passthrough($result)
    {
        if (isset($result['data']) && is_array($result['data'])) {
            // data có thể là {success,code,message,data} đầy đủ hoặc chỉ phần data.
            if (array_key_exists('success', $result['data']) || array_key_exists('code', $result['data'])) {
                return $result['data'];
            }
            return array(
                'success' => (bool) $result['success'],
                'code'    => $result['success'] ? 200 : 400,
                'message' => $this->extractMessage($result, ''),
                'data'    => $result['data'],
            );
        }
        return array(
            'success' => false,
            'code'    => 500,
            'message' => $this->extractMessage($result, 'Lỗi kết nối máy chủ.'),
            'data'    => null,
        );
    }

    /** Lấy 1 nhánh dữ liệu từ kết quả ApiClient (bao 1-2 lớp 'data'). */
    protected function extractData($result, $key)
    {
        if (!is_array($result) || empty($result['success']) || !isset($result['data'])) {
            return null;
        }
        $body = $result['data'];
        $inner = isset($body['data']) && is_array($body['data']) ? $body['data'] : $body;
        if (is_array($inner) && array_key_exists($key, $inner)) {
            return $inner[$key];
        }
        return null;
    }

    protected function extractMessage($result, $default)
    {
        if (isset($result['data']['message'])) {
            return $result['data']['message'];
        }
        if (isset($result['error']) && $result['error']) {
            return $result['error'];
        }
        return $default;
    }

    protected function renderJson($data)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo CJSON::encode($data);
        Yii::app()->end();
    }
}
