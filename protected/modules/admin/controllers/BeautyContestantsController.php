<?php

class BeautyContestantsController extends AdminController
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

    public function actionCreate()
    {
        $model = new BeautyContestants;

        if (isset($_POST['BeautyContestants'])) {
            $model->setAttributes($_POST['BeautyContestants']);
            if ($model->validate()) {
                $model->status = BeautyContestants::STATUS_REGISTERED;
                $result = $model->storeViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Đăng ký thí sinh thành công.');
                    $newId = isset($result['data']['id']) ? $result['data']['id'] : null;
                    $this->redirect($newId ? array('view', 'id' => $newId) : array('admin'));
                } else {
                    $errorMsg = $result['error'] ?: 'Không thể đăng ký.';
                    $model->addError('attendee_id', $errorMsg);
                }
            }
        }

        $contests = $this->getActiveContests();
        $properties = Properties::getListForDropdown();

        $this->render('create', array(
            'model' => $model,
            'contests' => $contests,
            'properties' => $properties,
        ));
    }

    public function actionUpdate($id)
    {
        $model = $this->loadModelById($id);

        if (isset($_POST['BeautyContestants'])) {
            $model->setAttributes($_POST['BeautyContestants']);

            if ($model->validate()) {
                $result = $model->updateViaApi();

                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Cập nhật thí sinh thành công.');
                    $this->redirect(array('view', 'id' => $id));
                } else {
                    $model->addError('attendee_id', $result['error'] ?: 'Không thể cập nhật.');
                }
            }
        }

        $contests = $this->getActiveContests();

        $this->render('update', array(
            'model' => $model,
            'contests' => $contests,
        ));
    }

    public function actionDelete($id)
    {
        if (Yii::app()->getRequest()->getIsPostRequest()) {
            $result = BeautyContestants::deleteViaApi($id);

            if ($result['success']) {
                Yii::app()->user->setFlash('success', 'Xóa thí sinh thành công.');
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

    public function actionAdmin()
    {
        $model = new BeautyContestants('search');
        $model->unsetAttributes();

        if (isset($_GET['BeautyContestants'])) {
            $model->setAttributes($_GET['BeautyContestants']);
        }

        $params = array();
        foreach ($model->attributes as $key => $value) {
            if ($value !== null && $value !== '') {
                $params[$key] = $value;
            }
        }

        $dataProvider = BeautyContestants::getApiDataProvider($params);
        $contests = $this->getActiveContests();

        $this->render('admin', array(
            'model' => $model,
            'dataProvider' => $dataProvider,
            'contests' => $contests,
        ));
    }

    public function actionGetFemaleAttendees($propertyId)
    {
        $attendees = Attendees::getApiDataProvider(array(
            'property_id' => $propertyId,
            'approval_status' => Attendees::APPROVAL_APPROVED,
            'gender' => 'female',
        ), 500)->getData();

        $result = array();
        foreach ($attendees as $att) {
            $result[] = array(
                'id' => $att->id,
                'name' => $att->full_name,
                'staff_code' => isset($att->staff_code) ? $att->staff_code : '',
            );
        }

        header('Content-Type: application/json');
        echo json_encode(array('success' => true, 'data' => $result));
        Yii::app()->end();
    }

    protected function getActiveContests()
    {
        $result = ApiClient::get(ApiEndpoints::BEAUTY_CONTEST_LIST, array(
            'is_active' => 1,
            'per_page' => 100,
        ));

        $list = array();
        if ($result['success'] && isset($result['data']['data'])) {
            foreach ($result['data']['data'] as $item) {
                $list[$item['id']] = $item['name'];
            }
        }
        return $list;
    }

    protected function loadModelById($id)
    {
        $model = BeautyContestants::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy thí sinh.');
        }
        return $model;
    }
}
