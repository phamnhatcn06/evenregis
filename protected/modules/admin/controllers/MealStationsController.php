<?php

/**
 * Quản trị Trạm chấm ăn (mỗi cửa = 1 trạm, có mã truy cập riêng).
 * Dữ liệu qua External API (model MealStations). Permission key: "meal".
 */
class MealStationsController extends AdminController
{
    public function actionAdmin()
    {
        $params = array();
        if (isset($_GET['event_id']) && $_GET['event_id'] !== '') {
            $params['event_id'] = (int) $_GET['event_id'];
        }

        $dataProvider = MealStations::getApiDataProvider($params);

        $this->render('admin', array(
            'dataProvider' => $dataProvider,
            'eventList'    => $this->getEventList(),
            'eventId'      => isset($params['event_id']) ? $params['event_id'] : '',
        ));
    }

    public function actionCreate()
    {
        $model = new MealStations;
        $model->setScenario('insert');

        if (isset($_POST['MealStations'])) {
            $model->setAttributes($_POST['MealStations']);
            if ($model->validate()) {
                $result = $model->storeViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Tạo trạm chấm ăn thành công.');
                    $this->redirect(array('admin', 'event_id' => $model->event_id));
                } else {
                    $model->addError('name', $result['error'] ?: 'Không thể tạo trạm.');
                }
            }
        }

        $this->render('create', array(
            'model'     => $model,
            'eventList' => $this->getEventList(),
        ));
    }

    public function actionUpdate($id)
    {
        $model = $this->loadModelById($id);

        if (isset($_POST['MealStations'])) {
            $model->setAttributes($_POST['MealStations']);
            if ($model->validate()) {
                $result = $model->updateViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Cập nhật trạm thành công.');
                    $this->redirect(array('admin', 'event_id' => $model->event_id));
                } else {
                    $model->addError('name', $result['error'] ?: 'Không thể cập nhật trạm.');
                }
            }
        }

        $this->render('update', array(
            'model'     => $model,
            'eventList' => $this->getEventList(),
        ));
    }

    public function actionDelete($id)
    {
        if (!Yii::app()->getRequest()->getIsPostRequest()) {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }
        $result = MealStations::deleteViaApi($id);
        if ($result['success']) {
            Yii::app()->user->setFlash('success', 'Xóa trạm thành công.');
        } else {
            Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể xóa trạm.');
        }
        if (!Yii::app()->getRequest()->getIsAjaxRequest()) {
            $this->redirect(array('admin'));
        }
    }

    protected function loadModelById($id)
    {
        $model = MealStations::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy trạm chấm ăn.');
        }
        return $model;
    }

    protected function getEventList()
    {
        $list = array();
        try {
            $events = Events::getApiDataProvider(array(), 200)->getData();
            foreach ($events as $e) {
                $list[$e->id] = $e->name;
            }
        } catch (Exception $e) {
            // bỏ qua
        }
        return $list;
    }
}
