<?php

class BeautyContestsController extends AdminController
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
        $model = new BeautyContests;

        if (isset($_POST['BeautyContests'])) {
            $model->setAttributes($_POST['BeautyContests']);
            if ($model->validate()) {
                $result = $model->storeViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Tạo cuộc thi thành công.');
                    $newId = isset($result['data']['id']) ? $result['data']['id'] : null;
                    $this->redirect($newId ? array('view', 'id' => $newId) : array('admin'));
                } else {
                    $errorMsg = $result['error'] ?: 'Không thể tạo cuộc thi.';
                    $model->addError('name', $errorMsg);
                }
            }
        }

        $events = Events::getListForDropdown();

        $this->render('create', array(
            'model' => $model,
            'events' => $events,
        ));
    }

    public function actionUpdate($id)
    {
        $model = $this->loadModelById($id);

        if (isset($_POST['BeautyContests'])) {
            $model->setAttributes($_POST['BeautyContests']);

            if ($model->validate()) {
                $result = $model->updateViaApi();

                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Cập nhật cuộc thi thành công.');
                    $this->redirect(array('view', 'id' => $id));
                } else {
                    $model->addError('name', $result['error'] ?: 'Không thể cập nhật.');
                }
            }
        }

        $events = Events::getListForDropdown();

        $this->render('update', array(
            'model' => $model,
            'events' => $events,
        ));
    }

    public function actionDelete($id)
    {
        if (Yii::app()->getRequest()->getIsPostRequest()) {
            $result = BeautyContests::deleteViaApi($id);

            if ($result['success']) {
                Yii::app()->user->setFlash('success', 'Xóa cuộc thi thành công.');
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
        $model = new BeautyContests('search');
        $model->unsetAttributes();

        if (isset($_GET['BeautyContests'])) {
            $model->setAttributes($_GET['BeautyContests']);
        }

        $params = array();
        foreach ($model->attributes as $key => $value) {
            if ($value !== null && $value !== '') {
                $params[$key] = $value;
            }
        }

        $dataProvider = BeautyContests::getApiDataProvider($params);
        $events = Events::getListForDropdown();

        $this->render('admin', array(
            'model' => $model,
            'dataProvider' => $dataProvider,
            'events' => $events,
        ));
    }

    protected function loadModelById($id)
    {
        $model = BeautyContests::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy cuộc thi.');
        }
        return $model;
    }
}
