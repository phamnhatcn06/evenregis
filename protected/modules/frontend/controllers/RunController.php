<?php

/**
 * Cổng đăng ký bộ môn CHẠY & Tham quan (public). Người tham dự chọn 1 nội dung còn
 * slot để đăng ký (FCFS). Việc đăng nhập (định danh DHMT + mã PIN) dùng chung cổng
 * cá nhân — xem PortalController / AttendeePortalController.
 */
class RunController extends AttendeePortalController
{
    public function actionIndex()
    {
        $this->requireLogin();

        $attendeeId = $this->currentAttendeeId();
        $eventId = $this->currentEventId();

        // Fun Run: phiếu đã đăng ký (nếu có) hoặc danh sách cự ly đang mở.
        $runMine = RunRegistrations::findMine($attendeeId);
        $runEvents = $runMine ? array() : RunEvents::listOpen($eventId);
        $runCancelledNotice = RunRegistrations::findLatestCancelled($attendeeId);

        // Tham quan: độc lập với Fun Run.
        $tourMine = TourRegistrations::findMine($attendeeId);
        $tourSessions = $tourMine ? array() : TourSessions::listOpen($eventId);
        $tourCancelledNotice = TourRegistrations::findLatestCancelled($attendeeId);

        $eventInfo = null;
        if (!empty($eventId)) {
            try {
                $eventInfo = Events::fetchFromApi($eventId);
            } catch (Exception $e) {}
        }
        if (!$eventInfo) {
            try {
                $eventInfo = Daihoi::getEvent();
            } catch (Exception $e) {}
        }

        $this->render('index', array(
            'fullName'            => $this->currentFullName(),
            'runMine'             => $runMine,
            'runEvents'           => $runEvents,
            'runCancelledNotice'  => $runCancelledNotice,
            'tourMine'            => $tourMine,
            'tourSessions'        => $tourSessions,
            'tourCancelledNotice' => $tourCancelledNotice,
            'eventInfo'           => $eventInfo,
            'profileModal'        => $this->profileModalData(),
        ));
    }

    /** Đăng ký 1 cự ly chạy (AJAX). Trả JSON {success, message, bib}. */
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

        $attendeeId = $this->currentAttendeeId();
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

    /** Người dùng xin hủy đăng ký (AJAX). Trả JSON {success, message}. */
    public function actionCancelRequest()
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

        $attendeeId = $this->currentAttendeeId();
        $reason = trim(isset($_POST['reason']) ? $_POST['reason'] : '');
        if ($reason === '') {
            echo CJSON::encode(array('success' => false, 'message' => 'Vui lòng nhập lý do hủy.'));
            Yii::app()->end();
        }

        $res = RunRegistrations::requestCancelViaApi($attendeeId, $reason);

        echo CJSON::encode(array(
            'success' => (bool) $res['success'],
            'message' => $res['success']
                ? (isset($res['data']['message']) ? $res['data']['message'] : 'Đã gửi yêu cầu hủy.')
                : ($res['error'] ?: 'Không gửi được yêu cầu hủy.'),
        ));
        Yii::app()->end();
    }

    /** Đăng ký 1 đợt tham quan (AJAX). Trả JSON {success, message}. */
    public function actionRegisterTour()
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

        $attendeeId = $this->currentAttendeeId();
        $tourSessionId = isset($_POST['tour_session_id']) ? (int) $_POST['tour_session_id'] : 0;

        $res = TourRegistrations::claimViaApi($tourSessionId, $attendeeId);

        if ($res['success']) {
            echo CJSON::encode(array(
                'success' => true,
                'message' => isset($res['data']['message']) ? $res['data']['message'] : 'Đăng ký thành công!',
            ));
        } else {
            echo CJSON::encode(array(
                'success' => false,
                'message' => $res['error'] ?: 'Không thể đăng ký. Vui lòng thử lại.',
            ));
        }
        Yii::app()->end();
    }

    /** Người dùng xin hủy đăng ký tham quan (AJAX). Trả JSON {success, message}. */
    public function actionCancelRequestTour()
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

        $attendeeId = $this->currentAttendeeId();
        $reason = trim(isset($_POST['reason']) ? $_POST['reason'] : '');
        if ($reason === '') {
            echo CJSON::encode(array('success' => false, 'message' => 'Vui lòng nhập lý do hủy.'));
            Yii::app()->end();
        }

        $res = TourRegistrations::requestCancelViaApi($attendeeId, $reason);

        echo CJSON::encode(array(
            'success' => (bool) $res['success'],
            'message' => $res['success']
                ? (isset($res['data']['message']) ? $res['data']['message'] : 'Đã gửi yêu cầu hủy.')
                : ($res['error'] ?: 'Không gửi được yêu cầu hủy.'),
        ));
        Yii::app()->end();
    }
}
