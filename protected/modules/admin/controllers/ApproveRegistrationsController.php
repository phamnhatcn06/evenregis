<?php

class ApproveRegistrationsController extends AdminController
{
    /**
     * Danh sách đăng ký chờ phê duyệt (status = submitted)
     */
    public function actionAdmin()
    {
        $model = new Registrations('search');
        $model->unsetAttributes();

        if (isset($_GET['Registrations'])) {
            $model->setAttributes($_GET['Registrations']);
        }

        $params = array('status' => Registrations::STATUS_SUBMITTED);
        foreach ($model->attributes as $key => $value) {
            if ($value !== null && $value !== '') {
                $params[$key] = $value;
            }
        }

        $dataProvider = Registrations::getApiDataProvider($params);

        $this->render('admin', array(
            'model' => $model,
            'dataProvider' => $dataProvider,
        ));
    }

    /**
     * Xem chi tiết đăng ký để phê duyệt
     */
    public function actionView($id)
    {
        $model = $this->loadModelById($id);

        // Load related names
        if (empty($model->event_name) && $model->event_id) {
            $event = Events::fetchFromApi($model->event_id);
            $model->event_name = $event ? $event->name : '';
        }
        if ($model->property_id) {
            $property = Properties::fetchFromApi($model->property_id);
            if ($property) {
                if (empty($model->property_name)) {
                    $model->property_name = $property->name;
                }
                $model->property_code = $property->prefix ? $property->prefix : $property->code;
            }
        }
        if (empty($model->period_name) && $model->period_id) {
            $period = RegistrationPeriods::fetchFromApi($model->period_id);
            $model->period_name = $period ? $period->name : '';
        }

        // Load attendees
        $attendees = Attendees::getByRegistrationId($id);

        // Load roles
        $rolesData = Roles::getApiDataProvider(array(), 100)->getData();
        $roles = array();
        foreach ($rolesData as $r) {
            $rId = isset($r['id']) ? $r['id'] : (isset($r->id) ? $r->id : null);
            $rName = isset($r['name']) ? $r['name'] : (isset($r->name) ? $r->name : '');
            if ($rId) $roles[$rId] = $rName;
        }

        // Load transports
        $transportsData = Transports::getApiDataProvider(array(), 100)->getData();
        $transports = array();
        foreach ($transportsData as $t) {
            $tId = isset($t['id']) ? $t['id'] : (isset($t->id) ? $t->id : null);
            $tName = isset($t['name']) ? $t['name'] : (isset($t->name) ? $t->name : '');
            if ($tId) $transports[$tId] = $tName;
        }

        $this->render('view', array(
            'model' => $model,
            'attendees' => $attendees,
            'roles' => $roles,
            'transports' => $transports,
        ));
    }

    /**
     * Phê duyệt một người tham dự
     */
    public function actionApproveAttendee()
    {
        header('Content-Type: application/json');

        if (!Yii::app()->request->isPostRequest) {
            echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
            Yii::app()->end();
        }

        $attendeeId = Yii::app()->request->getPost('attendee_id');
        $attendee = Attendees::fetchFromApi($attendeeId);

        if (!$attendee) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy người tham dự.'));
            Yii::app()->end();
        }

        $attendee->approval_status = Attendees::APPROVAL_APPROVED;
        $attendee->approved_at = time();
        $ssoUser = AuthHandler::getUser();
        $attendee->approved_by = isset($ssoUser['id']) ? $ssoUser['id'] : null;

        $result = $attendee->updateViaApi();

        if ($result['success']) {
            echo CJSON::encode(array('success' => true, 'message' => 'Đã phê duyệt người tham dự.'));
        } else {
            echo CJSON::encode(array('success' => false, 'error' => isset($result['error']) ? $result['error'] : 'Không thể phê duyệt.'));
        }
        Yii::app()->end();
    }

    /**
     * Từ chối một người tham dự
     */
    public function actionRejectAttendee()
    {
        header('Content-Type: application/json');

        if (!Yii::app()->request->isPostRequest) {
            echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
            Yii::app()->end();
        }

        $attendeeId = Yii::app()->request->getPost('attendee_id');
        $reason = Yii::app()->request->getPost('reason', '');
        $attendee = Attendees::fetchFromApi($attendeeId);

        if (!$attendee) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy người tham dự.'));
            Yii::app()->end();
        }

        $attendee->approval_status = Attendees::APPROVAL_REJECTED;
        $attendee->rejection_reason = $reason;
        $attendee->approved_at = time();
        $ssoUser = AuthHandler::getUser();
        $attendee->approved_by = isset($ssoUser['id']) ? $ssoUser['id'] : null;

        $result = $attendee->updateViaApi();

        if ($result['success']) {
            echo CJSON::encode(array('success' => true, 'message' => 'Đã từ chối người tham dự.'));
        } else {
            echo CJSON::encode(array('success' => false, 'error' => isset($result['error']) ? $result['error'] : 'Không thể từ chối.'));
        }
        Yii::app()->end();
    }

    /**
     * Phê duyệt toàn bộ đăng ký
     */
    public function actionApproveAll()
    {
        header('Content-Type: application/json');

        if (!Yii::app()->request->isPostRequest) {
            echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
            Yii::app()->end();
        }

        $registrationId = Yii::app()->request->getPost('registration_id');
        $model = Registrations::fetchFromApi($registrationId);

        if (!$model) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy phiếu đăng ký.'));
            Yii::app()->end();
        }

        $ssoUser = AuthHandler::getUser();
        $approvedBy = isset($ssoUser['id']) ? $ssoUser['id'] : null;

        // Approve all attendees
        $attendees = Attendees::getByRegistrationId($registrationId);
        $successCount = 0;
        foreach ($attendees as $att) {
            $attId = isset($att['id']) ? $att['id'] : null;
            if ($attId) {
                $attendee = Attendees::fetchFromApi($attId);
                if ($attendee && $attendee->approval_status != Attendees::APPROVAL_APPROVED) {
                    $attendee->approval_status = Attendees::APPROVAL_APPROVED;
                    $attendee->approved_at = time();
                    $attendee->approved_by = $approvedBy;
                    $result = $attendee->updateViaApi();
                    if ($result['success']) {
                        $successCount++;
                    }
                }
            }
        }

        // Approve registration
        $model->status = Registrations::STATUS_APPROVED;
        $model->reviewed_at = time();
        $model->reviewed_by = $approvedBy;
        $result = $model->updateViaApi();

        if ($result['success']) {
            echo CJSON::encode(array(
                'success' => true,
                'message' => "Đã phê duyệt phiếu đăng ký và {$successCount} người tham dự.",
            ));
        } else {
            echo CJSON::encode(array('success' => false, 'error' => 'Không thể phê duyệt phiếu đăng ký.'));
        }
        Yii::app()->end();
    }

    /**
     * Từ chối toàn bộ đăng ký
     */
    public function actionRejectAll()
    {
        header('Content-Type: application/json');

        if (!Yii::app()->request->isPostRequest) {
            echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
            Yii::app()->end();
        }

        $registrationId = Yii::app()->request->getPost('registration_id');
        $reason = Yii::app()->request->getPost('reason', '');
        $model = Registrations::fetchFromApi($registrationId);

        if (!$model) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy phiếu đăng ký.'));
            Yii::app()->end();
        }

        $ssoUser = AuthHandler::getUser();
        $approvedBy = isset($ssoUser['id']) ? $ssoUser['id'] : null;

        // Reject all attendees
        $attendees = Attendees::getByRegistrationId($registrationId);
        $rejectCount = 0;
        foreach ($attendees as $att) {
            $attId = isset($att['id']) ? $att['id'] : null;
            if ($attId) {
                $attendee = Attendees::fetchFromApi($attId);
                if ($attendee && $attendee->approval_status != Attendees::APPROVAL_REJECTED) {
                    $attendee->approval_status = Attendees::APPROVAL_REJECTED;
                    $attendee->rejection_reason = $reason;
                    $attendee->approved_at = time();
                    $attendee->approved_by = $approvedBy;
                    $result = $attendee->updateViaApi();
                    if ($result['success']) {
                        $rejectCount++;
                    }
                }
            }
        }

        // Reject registration
        $model->status = Registrations::STATUS_REJECTED;
        $model->reviewed_at = time();
        $model->reviewed_by = $approvedBy;
        $model->rejection_reason = $reason;
        $result = $model->updateViaApi();

        if ($result['success']) {
            echo CJSON::encode(array(
                'success' => true,
                'message' => "Đã từ chối phiếu đăng ký và {$rejectCount} người tham dự.",
            ));
        } else {
            echo CJSON::encode(array('success' => false, 'error' => 'Không thể từ chối phiếu đăng ký.'));
        }
        Yii::app()->end();
    }

    protected function loadModelById($id)
    {
        $model = Registrations::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy phiếu đăng ký.');
        }
        return $model;
    }
}
