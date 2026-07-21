<?php

class TalentRoundsController extends AdminController
{
    public function actionIndex()
    {
        $this->redirect(array('admin'));
    }

    public function actionAdmin()
    {
        $model = new TalentRounds('search');
        $model->unsetAttributes();

        if (isset($_GET['TalentRounds'])) {
            $model->setAttributes($_GET['TalentRounds']);
        }

        $params = array();
        foreach ($model->attributes as $key => $value) {
            if ($value !== null && $value !== '') {
                $params[$key] = $value;
            }
        }

        $dataProvider = TalentRounds::getApiDataProvider($params);
        $talentShows = TalentShows::getListForDropdown();

        $this->render('admin', array(
            'model' => $model,
            'dataProvider' => $dataProvider,
            'talentShows' => $talentShows,
        ));
    }

    public function actionView($id)
    {
        $model = $this->loadModelById($id);
        $this->render('view', array('model' => $model));
    }

    public function actionCreate()
    {
        $model = new TalentRounds;

        if (isset($_POST['TalentRounds'])) {
            $model->setAttributes($_POST['TalentRounds']);
            if ($model->validate()) {
                $result = $model->storeViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Tạo vòng thi thành công.');
                    $newId = isset($result['data']['id']) ? $result['data']['id'] : null;
                    $this->redirect($newId ? array('view', 'id' => $newId) : array('admin'));
                } else {
                    $model->addError('name', $result['error'] ?: 'Không thể tạo vòng thi.');
                }
            }
        }

        $talentShows = TalentShows::getListForDropdown();

        $this->render('create', array(
            'model' => $model,
            'talentShows' => $talentShows,
        ));
    }

    public function actionUpdate($id)
    {
        $model = $this->loadModelById($id);

        if (isset($_POST['TalentRounds'])) {
            $model->setAttributes($_POST['TalentRounds']);
            if ($model->validate()) {
                $result = $model->updateViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Cập nhật vòng thi thành công.');
                    $this->redirect(array('view', 'id' => $id));
                } else {
                    $model->addError('name', $result['error'] ?: 'Không thể cập nhật.');
                }
            }
        }

        $talentShows = TalentShows::getListForDropdown();

        $this->render('update', array(
            'model' => $model,
            'talentShows' => $talentShows,
        ));
    }

    public function actionDelete($id)
    {
        if (Yii::app()->getRequest()->getIsPostRequest()) {
            $result = TalentRounds::deleteViaApi($id);

            if ($result['success']) {
                Yii::app()->user->setFlash('success', 'Xóa vòng thi thành công.');
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
        $model = TalentRounds::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy vòng thi.');
        }
        return $model;
    }
}
