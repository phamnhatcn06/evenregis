<?php

class ApprovalController extends AdminController
{
    public function actionIndex()
    {
        $model = new Attendees('search');
        $model->unsetAttributes();

        if (isset($_GET['Attendees'])) {
            $model->setAttributes($_GET['Attendees']);
        }

        $params = array();
        $params['approval_status'] = isset($_GET['approval_status']) ? $_GET['approval_status'] : null;
        $params['property_id'] = isset($_GET['property_id']) ? $_GET['property_id'] : null;
        $params['event_id'] = isset($_GET['event_id']) ? $_GET['event_id'] : null;

        foreach ($params as $key => $value) {
            if ($value === null || $value === '') {
                unset($params[$key]);
            }
        }

        $dataProvider = Attendees::getApiDataProvider($params, 25);

        $events = Events::getApiDataProvider(array('status' => 1), 100)->getData();
        $properties = Properties::getApiDataProvider(array(), 500)->getData();

        $this->render('index', array(
            'model' => $model,
            'dataProvider' => $dataProvider,
            'events' => $events,
            'properties' => $properties,
        ));
    }

    public function actionView($id)
    {
        $model = Attendees::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy người tham dự.');
        }

        if (!empty($model->property_id)) {
            $property = Properties::fetchFromApi($model->property_id);
            $model->property_name = $property ? $property->name : '';
            $model->property_code = $property ? $property->code : '';
        }

        if (!empty($model->role_id)) {
            $model->role_name = Attendees::resolveRoleNames($model->role_id);
        }

        if (!empty($model->staff_id)) {
            $staff = Staffs::fetchFromApi($model->staff_id);
            $model->staff_code = $staff ? $staff->code : '';
        }

        $this->render('view', array(
            'model' => $model,
        ));
    }

    public function actionApprove($id)
    {
        if (!Yii::app()->getRequest()->getIsPostRequest()) {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }

        $model = Attendees::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy người tham dự.');
        }

        $ssoUser = AuthHandler::getUser();
        $model->approved_by = isset($ssoUser['id']) ? $ssoUser['id'] : null;

        $result = $model->approveViaApi();

        if (Yii::app()->getRequest()->getIsAjaxRequest()) {
            header('Content-Type: application/json');
            echo CJSON::encode(array(
                'success' => $result['success'],
                'message' => $result['success'] ? 'Đã phê duyệt thành công.' : 'Không thể phê duyệt.',
            ));
            Yii::app()->end();
        }

        if ($result['success']) {
            Yii::app()->user->setFlash('success', 'Đã phê duyệt người tham dự.');
        } else {
            Yii::app()->user->setFlash('error', 'Không thể phê duyệt.');
        }

        $this->redirect(array('view', 'id' => $id));
    }

    public function actionReject($id)
    {
        if (!Yii::app()->getRequest()->getIsPostRequest()) {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }

        $reason = Yii::app()->getRequest()->getPost('rejection_reason', '');
        if (empty($reason)) {
            if (Yii::app()->getRequest()->getIsAjaxRequest()) {
                header('Content-Type: application/json');
                echo CJSON::encode(array(
                    'success' => false,
                    'message' => 'Vui lòng nhập lý do từ chối.',
                ));
                Yii::app()->end();
            }
            Yii::app()->user->setFlash('error', 'Vui lòng nhập lý do từ chối.');
            $this->redirect(array('view', 'id' => $id));
            return;
        }

        $model = Attendees::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy người tham dự.');
        }

        $ssoUser = AuthHandler::getUser();
        $model->approved_by = isset($ssoUser['id']) ? $ssoUser['id'] : null;

        $result = $model->rejectViaApi($reason);

        if (Yii::app()->getRequest()->getIsAjaxRequest()) {
            header('Content-Type: application/json');
            echo CJSON::encode(array(
                'success' => $result['success'],
                'message' => $result['success'] ? 'Đã từ chối thành công.' : 'Không thể từ chối.',
            ));
            Yii::app()->end();
        }

        if ($result['success']) {
            Yii::app()->user->setFlash('success', 'Đã từ chối người tham dự.');
        } else {
            Yii::app()->user->setFlash('error', 'Không thể từ chối.');
        }

        $this->redirect(array('view', 'id' => $id));
    }

    public function actionBulkApprove()
    {
        if (!Yii::app()->getRequest()->getIsPostRequest()) {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }

        $ids = Yii::app()->getRequest()->getPost('ids', array());
        if (empty($ids)) {
            Yii::app()->user->setFlash('error', 'Vui lòng chọn ít nhất một người.');
            $this->redirect(array('index'));
            return;
        }

        $ssoUser = AuthHandler::getUser();
        $approvedBy = isset($ssoUser['id']) ? $ssoUser['id'] : null;

        $successCount = 0;
        $errorCount = 0;

        foreach ($ids as $id) {
            $model = Attendees::fetchFromApi($id);
            if ($model) {
                $model->approved_by = $approvedBy;
                $result = $model->approveViaApi();
                if ($result['success']) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            } else {
                $errorCount++;
            }
        }

        if ($successCount > 0) {
            Yii::app()->user->setFlash('success', "Đã phê duyệt thành công {$successCount} người.");
        }
        if ($errorCount > 0) {
            Yii::app()->user->setFlash('warning', "Có {$errorCount} người không phê duyệt được.");
        }

        $this->redirect(array('index'));
    }
}
