<?php

class TalentShowsController extends AdminController
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
        $model = new TalentShows;

        if (isset($_POST['TalentShows'])) {
            $model->setAttributes($_POST['TalentShows']);
            if ($model->validate()) {
                $result = $model->storeViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Tạo cuộc thi văn nghệ thành công.');
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

        if (isset($_POST['TalentShows'])) {
            $model->setAttributes($_POST['TalentShows']);

            if ($model->validate()) {
                $result = $model->updateViaApi();

                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Cập nhật cuộc thi văn nghệ thành công.');
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
            $result = TalentShows::deleteViaApi($id);

            if ($result['success']) {
                Yii::app()->user->setFlash('success', 'Xóa cuộc thi văn nghệ thành công.');
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
        $model = new TalentShows('search');
        $model->unsetAttributes();

        if (isset($_GET['TalentShows'])) {
            $model->setAttributes($_GET['TalentShows']);
        }

        $params = array();
        foreach ($model->attributes as $key => $value) {
            if ($value !== null && $value !== '') {
                $params[$key] = $value;
            }
        }

        $dataProvider = TalentShows::getApiDataProvider($params);
        $events = Events::getListForDropdown();

        $this->render('admin', array(
            'model' => $model,
            'dataProvider' => $dataProvider,
            'events' => $events,
        ));
    }

    protected function loadModelById($id)
    {
        $model = TalentShows::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy cuộc thi văn nghệ.');
        }
        return $model;
    }
}
