<?php

class CompetitionRegistrationsController extends AdminController
{
    public function actionView($id)
    {
        $model = $this->loadModelById($id);
        $this->render('view', array(
            'model' => $model,
        ));
    }

    public function actionCreate()
    {
        $model = new CompetitionRegistrations;

        if (isset($_POST['CompetitionRegistrations'])) {
            $model->setAttributes($_POST['CompetitionRegistrations']);
            if ($model->validate()) {
                $model->status = CompetitionRegistrations::STATUS_PENDING;
                $model->registered_at = time();
                $result = $model->storeViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Đăng ký thi thành công.');
                    $newId = isset($result['data']['id']) ? $result['data']['id'] : null;
                    $this->redirect($newId ? array('view', 'id' => $newId) : array('admin'));
                } else {
                    $errorMsg = $result['error'] ?: 'Không thể đăng ký.';
                    if (isset($result['data']['errors'])) {
                        $errorMsg .= ' Chi tiết: ' . json_encode($result['data']['errors']);
                    }
                    $model->addError('attendee_id', $errorMsg);
                }
            }
        }

        $competitions = Competitions::getActiveList();
        $this->render('create', array(
            'model' => $model,
            'competitions' => $competitions,
        ));
    }

    public function actionUpdate($id)
    {
        $model = $this->loadModelById($id);

        if (isset($_POST['CompetitionRegistrations'])) {
            $model->setAttributes($_POST['CompetitionRegistrations']);

            if ($model->validate()) {
                $result = $model->updateViaApi();

                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Cập nhật đăng ký thành công.');
                    $this->redirect(array('view', 'id' => $id));
                } else {
                    $model->addError('attendee_id', $result['error'] ?: 'Không thể cập nhật.');
                }
            }
        }

        $competitions = Competitions::getActiveList();
        $this->render('update', array(
            'model' => $model,
            'competitions' => $competitions,
        ));
    }

    public function actionDelete($id)
    {
        if (Yii::app()->getRequest()->getIsPostRequest()) {
            $result = CompetitionRegistrations::deleteViaApi($id);

            if ($result['success']) {
                Yii::app()->user->setFlash('success', 'Xóa đăng ký thành công.');
            } else {
                Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể xóa đăng ký.');
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
        $model = new CompetitionRegistrations('search');
        $model->unsetAttributes();

        if (isset($_GET['CompetitionRegistrations'])) {
            $model->setAttributes($_GET['CompetitionRegistrations']);
        }

        $params = array();
        foreach ($model->attributes as $key => $value) {
            if ($value !== null && $value !== '') {
                $params[$key] = $value;
            }
        }

        $dataProvider = CompetitionRegistrations::getApiDataProvider($params);
        $competitions = Competitions::getActiveList();

        $this->render('admin', array(
            'model' => $model,
            'dataProvider' => $dataProvider,
            'competitions' => $competitions,
        ));
    }

    public function actionConfirm($id)
    {
        if (Yii::app()->getRequest()->getIsPostRequest()) {
            $result = CompetitionRegistrations::confirmViaApi($id);

            if ($result['success']) {
                Yii::app()->user->setFlash('success', 'Xác nhận đăng ký thành công.');
            } else {
                Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể xác nhận.');
            }

            $this->redirect(array('view', 'id' => $id));
        } else {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }
    }

    protected function loadModelById($id)
    {
        $model = CompetitionRegistrations::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy đăng ký.');
        }
        return $model;
    }
}
