<?php

class AllianceRequestsController extends AdminController
{
    public function actionIndex()
    {
        $this->redirect(array('admin'));
    }

    public function actionView($id)
    {
        $model = $this->loadModelById($id);

        $this->render('view', array(
            'model' => $model,
        ));
    }

    public function actionAdmin()
    {
        $model = new AllianceRequests('search');
        $model->unsetAttributes();

        if (isset($_GET['AllianceRequests'])) {
            $model->setAttributes($_GET['AllianceRequests']);
        }

        $params = array();
        foreach ($model->attributes as $key => $value) {
            if ($value !== null && $value !== '') {
                $params[$key] = $value;
            }
        }

        $dataProvider = AllianceRequests::getApiDataProvider($params);
        $events = Events::getActiveList();

        $this->render('admin', array(
            'model' => $model,
            'dataProvider' => $dataProvider,
            'events' => $events,
        ));
    }

    public function actionPending()
    {
        $ssoUser = AuthHandler::getUser();
        $propertyId = isset($ssoUser['property_id']) ? $ssoUser['property_id'] : null;

        $params = array(
            'target_org_id' => $propertyId,
            'status' => AllianceRequests::STATUS_PENDING,
        );

        $dataProvider = AllianceRequests::getApiDataProvider($params);

        $this->render('pending', array(
            'dataProvider' => $dataProvider,
        ));
    }

    public function actionApprove($id)
    {
        if (Yii::app()->getRequest()->getIsPostRequest()) {
            $model = $this->loadModelById($id);

            $ssoUser = AuthHandler::getUser();
            $model->status = AllianceRequests::STATUS_APPROVED;
            $model->reviewed_by = isset($ssoUser['id']) ? $ssoUser['id'] : null;
            $model->reviewed_at = date('Y-m-d H:i:s');

            $result = $model->updateViaApi();

            if ($result['success']) {
                Yii::app()->user->setFlash('success', 'Đã xác nhận liên quân.');
            } else {
                Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể xác nhận.');
            }

            $this->redirect(array('view', 'id' => $id));
        } else {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }
    }

    public function actionReject($id)
    {
        $model = $this->loadModelById($id);

        if (Yii::app()->getRequest()->getIsPostRequest()) {
            $ssoUser = AuthHandler::getUser();
            $model->status = AllianceRequests::STATUS_REJECTED;
            $model->reviewed_by = isset($ssoUser['id']) ? $ssoUser['id'] : null;
            $model->reviewed_at = date('Y-m-d H:i:s');
            $model->rejection_reason = isset($_POST['rejection_reason']) ? $_POST['rejection_reason'] : '';

            $result = $model->updateViaApi();

            if ($result['success']) {
                Yii::app()->user->setFlash('success', 'Đã từ chối liên quân.');
            } else {
                Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể từ chối.');
            }

            $this->redirect(array('view', 'id' => $id));
        }

        $this->render('reject', array(
            'model' => $model,
        ));
    }

    public function actionDelete($id)
    {
        if (Yii::app()->getRequest()->getIsPostRequest()) {
            $result = AllianceRequests::deleteViaApi($id);

            if ($result['success']) {
                Yii::app()->user->setFlash('success', 'Xóa yêu cầu thành công.');
            } else {
                Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể xóa.');
            }

            if (!Yii::app()->getRequest()->getIsAjaxRequest()) {
                $this->redirect(array('admin'));
            }
        } else {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }
    }

    protected function loadModelById($id)
    {
        $model = AllianceRequests::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy yêu cầu liên quân.');
        }
        return $model;
    }
}
