<?php

/**
 * Cổng cá nhân người tham dự (public, không SSO). Đăng nhập một lần bằng định danh
 * DHMT + số lucky + mã PIN, sau đó vào trang chủ cá nhân (dashboard) để truy cập
 * các tính năng: đăng ký hoạt động, lịch thi đấu, lịch thi nghiệp vụ, hình ảnh...
 */
class PortalController extends AttendeePortalController
{
    /**
     * Đăng nhập nhiều bước:
     *  - Bước 1: nhập định danh (hoặc quét QR) -> hiện form đặt PIN hoặc nhập PIN.
     *  - Bước 2: đặt PIN (chưa có) hoặc đăng nhập (đã có) -> lưu session -> dashboard.
     */
    public function actionLogin()
    {
        $this->layout = '//layouts/portal_login';
        $returnUrl = $this->resolveReturnUrl();

        if ($this->isLoggedIn()) {
            $this->redirect($returnUrl);
            return;
        }

        $step = 'identify';
        $identifier = '';
        $fullName = '';
        $propertyId = '';

        if (Yii::app()->request->isPostRequest) {
            $formStep = isset($_POST['step']) ? $_POST['step'] : 'identify';
            $identifier = trim(isset($_POST['identifier']) ? $_POST['identifier'] : '');
            $propertyId = trim(isset($_POST['property_id']) ? $_POST['property_id'] : '');

            if ($formStep === 'identify' || $formStep === 'qr') {
                if ($formStep === 'qr') {
                    // Quét QR thẻ vật lý đã chứng minh danh tính nên không cần chọn đơn vị.
                    $qrValue = trim(isset($_POST['qr_value']) ? $_POST['qr_value'] : '');
                    $propertyId = '';
                    $res = RunAuth::identifyByQr($qrValue);
                } else {
                    $res = RunAuth::identify($identifier, $propertyId);
                }
                if ($res['success'] && isset($res['data']['data'])) {
                    $data = $res['data']['data'];
                    $fullName = isset($data['full_name']) ? $data['full_name'] : '';
                    if (!empty($data['identifier'])) {
                        $identifier = $data['identifier'];
                    }
                    $step = !empty($data['pin_is_set']) ? 'login' : 'setpin';
                } else {
                    Yii::app()->user->setFlash('error', $res['error'] ?: 'Không nhận diện được. Vui lòng thử lại.');
                }
            } elseif ($formStep === 'setpin') {
                $pin = isset($_POST['pin']) ? $_POST['pin'] : '';
                $pin2 = isset($_POST['pin_confirm']) ? $_POST['pin_confirm'] : '';
                if ($pin !== $pin2) {
                    Yii::app()->user->setFlash('error', 'Xác nhận mã PIN không khớp.');
                    $step = 'setpin';
                } else {
                    $res = RunAuth::setPin($identifier, $pin, $propertyId !== '' ? $propertyId : null);
                    if ($res['success'] && isset($res['data']['data'])) {
                        $this->loginSession($res['data']['data']);
                        Yii::app()->user->setFlash('success', 'Đặt mã PIN & đăng nhập thành công.');
                        $this->redirect($returnUrl);
                        return;
                    }
                    Yii::app()->user->setFlash('error', $res['error'] ?: 'Không đặt được mã PIN.');
                    $step = 'setpin';
                }
            } elseif ($formStep === 'login') {
                $pin = isset($_POST['pin']) ? $_POST['pin'] : '';
                $res = RunAuth::login($identifier, $pin, $propertyId !== '' ? $propertyId : null);
                if ($res['success'] && isset($res['data']['data'])) {
                    $this->loginSession($res['data']['data']);
                    Yii::app()->user->setFlash('success', 'Đăng nhập thành công.');
                    $this->redirect($returnUrl);
                    return;
                }
                Yii::app()->user->setFlash('error', $res['error'] ?: 'Mã PIN không đúng.');
                $step = 'login';
            }
        }

        // Dropdown đơn vị chỉ cần ở bước nhập định danh tay (không cần cho bước PIN/QR).
        $units = ($step === 'identify') ? RunAuth::listUnits() : array();

        $this->render('login', array(
            'step'       => $step,
            'identifier' => $identifier,
            'fullName'   => $fullName,
            'propertyId' => $propertyId,
            'units'      => $units,
            'returnUrl'  => $returnUrl,
        ));
    }

    /** Trang chủ cá nhân: danh sách tính năng người tham dự có thể truy cập. */
    public function actionIndex()
    {
        $this->requireLogin();
        $this->layout = '//layouts/portal';

        $this->render('index', array(
            'fullName'     => $this->currentFullName(),
            'profileModal' => $this->profileModalData(),
        ));
    }

    /**
     * Lưu năm sinh + giới tính từ popup hồ sơ (AJAX). Lưu thành công mới đánh dấu đã xác
     * nhận trong phiên, mở khóa các chức năng đăng ký. Trả JSON {success, message}.
     */
    public function actionSaveProfile()
    {
        header('Content-Type: application/json');

        if (!$this->isLoggedIn()) {
            echo CJSON::encode(array('success' => false, 'message' => 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.'));
            Yii::app()->end();
        }
        if (!Yii::app()->request->isPostRequest) {
            echo CJSON::encode(array('success' => false, 'message' => 'Yêu cầu không hợp lệ.'));
            Yii::app()->end();
        }

        $birthYear = isset($_POST['birth_year']) && $_POST['birth_year'] !== '' ? (int) $_POST['birth_year'] : null;
        $gender = isset($_POST['gender']) && $_POST['gender'] !== '' ? (int) $_POST['gender'] : null;
        if ($birthYear === null || $gender === null) {
            echo CJSON::encode(array('success' => false, 'message' => 'Vui lòng chọn đầy đủ năm sinh và giới tính.'));
            Yii::app()->end();
        }

        $res = RunAuth::saveProfile($this->currentAttendeeId(), $birthYear, $gender, $this->currentFullName());
        if ($res['success']) {
            $this->markProfileConfirmed();
        }

        echo CJSON::encode(array(
            'success' => (bool) $res['success'],
            'message' => $res['success']
                ? (isset($res['data']['message']) ? $res['data']['message'] : 'Đã lưu thông tin.')
                : ($res['error'] ?: 'Không lưu được thông tin.'),
        ));
        Yii::app()->end();
    }

    public function actionLogout()
    {
        $this->clearSession();
        Yii::app()->user->setFlash('success', 'Bạn đã đăng xuất.');
        $this->redirect(array('/frontend/portal/login'));
    }

    /**
     * Chỉ chấp nhận URL quay lại nội bộ (bắt đầu bằng '/') để tránh open-redirect.
     * Mặc định về trang chủ cá nhân.
     */
    private function resolveReturnUrl()
    {
        $default = array('/frontend/portal/index');
        $return = isset($_GET['return']) ? trim($_GET['return']) : '';
        if ($return === '' && isset($_POST['return'])) {
            $return = trim($_POST['return']);
        }
        if ($return !== '' && $return[0] === '/' && strpos($return, '//') !== 0) {
            return $return;
        }
        return $default;
    }
}
