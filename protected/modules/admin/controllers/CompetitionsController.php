<?php

class CompetitionsController extends AdminController
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
        $model = new Competitions;

        if (isset($_POST['Competitions'])) {
            $model->setAttributes($_POST['Competitions']);
            if ($model->validate()) {
                $model->is_active = 1;
                $ssoUser = AuthHandler::getUser();
                $model->created_by = isset($ssoUser['id']) ? $ssoUser['id'] : null;
                $result = $model->storeViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Tạo cuộc thi thành công.');
                    $newId = isset($result['data']['id']) ? $result['data']['id'] : null;
                    $this->redirect($newId ? array('view', 'id' => $newId) : array('admin'));
                } else {
                    $errorMsg = $result['error'] ?: 'Không thể tạo cuộc thi.';
                    if (isset($result['data']['errors'])) {
                        $errorMsg .= ' Chi tiết: ' . json_encode($result['data']['errors']);
                    }
                    $model->addError('name', $errorMsg);
                }
            }
        }

        $this->render('create', array(
            'model' => $model,
        ));
    }

    public function actionUpdate($id)
    {
        $model = $this->loadModelById($id);

        if (isset($_POST['Competitions'])) {
            $model->setAttributes($_POST['Competitions']);

            if ($model->validate()) {
                $result = $model->updateViaApi();

                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Cập nhật cuộc thi thành công.');
                    $this->redirect(array('view', 'id' => $id));
                } else {
                    $model->addError('name', $result['error'] ?: 'Không thể cập nhật cuộc thi.');
                }
            }
        }

        $this->render('update', array(
            'model' => $model,
        ));
    }

    public function actionDelete($id)
    {
        if (Yii::app()->getRequest()->getIsPostRequest()) {
            $result = Competitions::deleteViaApi($id);

            if ($result['success']) {
                Yii::app()->user->setFlash('success', 'Xóa cuộc thi thành công.');
            } else {
                Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể xóa cuộc thi.');
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
        $model = new Competitions('search');
        $model->unsetAttributes();

        if (isset($_GET['Competitions'])) {
            $model->setAttributes($_GET['Competitions']);
        }

        $params = array();
        foreach ($model->attributes as $key => $value) {
            if ($value !== null && $value !== '') {
                $params[$key] = $value;
            }
        }

        $dataProvider = Competitions::getApiDataProvider($params);

        $this->render('admin', array(
            'model' => $model,
            'dataProvider' => $dataProvider,
        ));
    }

    public function actionAssignNumbers($id)
    {
        if (Yii::app()->getRequest()->getIsPostRequest()) {
            $result = Competitions::assignCandidateNumbers($id);

            if ($result['success']) {
                Yii::app()->user->setFlash('success', 'Cấp số báo danh thành công.');
            } else {
                Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể cấp số báo danh.');
            }

            $this->redirect(array('view', 'id' => $id));
        } else {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }
    }

    protected function loadModelById($id)
    {
        $model = Competitions::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy cuộc thi.');
        }
        return $model;
    }
}
