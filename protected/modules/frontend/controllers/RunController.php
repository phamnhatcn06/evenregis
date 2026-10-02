<?php

/**
 * Cổng đăng ký bộ môn CHẠY (public). Người tham dự VCK đăng nhập bằng định danh
 * DHMT + số lucky + mã PIN, chọn 1 nội dung còn slot để đăng ký (FCFS). Không có
 * tài khoản SSO — dùng session riêng của cổng (run_attendee_id).
 */
class RunController extends CController
{
    public $layout = '//layouts/frontend';

    private function session()
    {
        return Yii::app()->session;
    }

    private function isLoggedIn()
    {
        return !empty($this->session()['run_attendee_id']);
    }

    /**
     * Đăng nhập nhiều bước:
     *  - Bước 1: nhập định danh -> identify -> hiện form đặt PIN hoặc nhập PIN.
     *  - Bước 2: đặt PIN (chưa có) hoặc đăng nhập (đã có) -> lưu session -> cổng.
     */
    public function actionLogin()
    {
        if ($this->isLoggedIn()) {
            $this->redirect(array('index'));
            return;
        }

        $step = 'identify';
        $identifier = '';
        $fullName = '';

        if (Yii::app()->request->isPostRequest) {
            $formStep = isset($_POST['step']) ? $_POST['step'] : 'identify';
            $identifier = trim(isset($_POST['identifier']) ? $_POST['identifier'] : '');

            if ($formStep === 'identify' || $formStep === 'qr') {
                if ($formStep === 'qr') {
                    $qrValue = trim(isset($_POST['qr_value']) ? $_POST['qr_value'] : '');
                    $res = RunAuth::identifyByQr($qrValue);
                } else {
                    $res = RunAuth::identify($identifier);
                }
                if ($res['success'] && isset($res['data']['data'])) {
                    $data = $res['data']['data'];
                    $fullName = isset($data['full_name']) ? $data['full_name'] : '';
                    // QR trả về định danh (DHMT+lucky) để dùng cho bước nhập PIN
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
                    $res = RunAuth::setPin($identifier, $pin);
                    if ($res['success'] && isset($res['data']['data'])) {
                        $this->loginSession($res['data']['data']);
                        Yii::app()->user->setFlash('success', 'Đặt mã PIN & đăng nhập thành công.');
                        $this->redirect(array('index'));
                        return;
                    }
                    Yii::app()->user->setFlash('error', $res['error'] ?: 'Không đặt được mã PIN.');
                    $step = 'setpin';
                }
            } elseif ($formStep === 'login') {
                $pin = isset($_POST['pin']) ? $_POST['pin'] : '';
                $res = RunAuth::login($identifier, $pin);
                if ($res['success'] && isset($res['data']['data'])) {
                    $this->loginSession($res['data']['data']);
                    Yii::app()->user->setFlash('success', 'Đăng nhập thành công.');
                    $this->redirect(array('index'));
                    return;
                }
                Yii::app()->user->setFlash('error', $res['error'] ?: 'Mã PIN không đúng.');
                $step = 'login';
            }
        }

        $this->render('login', array(
            'step'       => $step,
            'identifier' => $identifier,
            'fullName'   => $fullName,
        ));
    }

    private function loginSession(array $data)
    {
        $s = $this->session();
        $s['run_attendee_id'] = isset($data['attendee_id']) ? $data['attendee_id'] : null;
        $s['run_full_name']   = isset($data['full_name']) ? $data['full_name'] : '';
        $s['run_event_id']    = isset($data['event_id']) ? $data['event_id'] : null;
    }

    public function actionIndex()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect(array('login'));
            return;
        }

        $attendeeId = $this->session()['run_attendee_id'];
        $eventId = $this->session()['run_event_id'];

        // Đã đăng ký rồi -> hiện phiếu + BIB.
        $mine = RunRegistrations::findMine($attendeeId);
        if ($mine) {
            $this->render('result', array(
                'mine'     => $mine,
                'fullName' => $this->session()['run_full_name'],
            ));
            return;
        }

        $events = RunEvents::listOpen($eventId);

        $this->render('index', array(
            'events'   => $events,
            'fullName' => $this->session()['run_full_name'],
        ));
    }

    /** Đăng ký 1 nội dung (AJAX). Trả JSON {success, message, bib}. */
    public function actionRegister()
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

        $attendeeId = $this->session()['run_attendee_id'];
        $runEventId = isset($_POST['run_event_id']) ? (int) $_POST['run_event_id'] : 0;

        $res = RunRegistrations::claimViaApi($runEventId, $attendeeId);

        if ($res['success']) {
            $data = isset($res['data']['data']) ? $res['data']['data'] : array();
            echo CJSON::encode(array(
                'success' => true,
                'message' => isset($res['data']['message']) ? $res['data']['message'] : 'Đăng ký thành công!',
                'bib'     => isset($data['bib_number']) ? $data['bib_number'] : null,
            ));
        } else {
            echo CJSON::encode(array(
                'success' => false,
                'message' => $res['error'] ?: 'Không thể đăng ký. Vui lòng thử lại.',
            ));
        }
        Yii::app()->end();
    }

    public function actionLogout()
    {
        $s = $this->session();
        unset($s['run_attendee_id'], $s['run_full_name'], $s['run_event_id']);
        $this->redirect(array('login'));
    }
}
