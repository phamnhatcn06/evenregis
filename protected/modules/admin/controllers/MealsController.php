<?php

/**
 * Quản trị Bữa ăn (cấu hình chấm ăn). Bữa ăn sinh tự động từ agenda
 * (sáng/trưa/tối); BTC chỉ cấu hình khung giờ chấm, địa điểm, bật/tắt.
 * Dữ liệu qua External API (model Meals). Permission key: "meal".
 */
class MealsController extends AdminController
{
    public function actionAdmin()
    {
        $params = array();
        if (isset($_GET['event_id']) && $_GET['event_id'] !== '') {
            $params['event_id'] = (int) $_GET['event_id'];
        }

        $dataProvider = Meals::getApiDataProvider($params);

        $this->render('admin', array(
            'dataProvider' => $dataProvider,
            'eventList'    => $this->getEventList(),
            'eventId'      => isset($params['event_id']) ? $params['event_id'] : '',
        ));
    }

    public function actionCreate()
    {
        $model = new Meals;
        $model->event_id = isset($_GET['event_id']) ? (int) $_GET['event_id'] : null;
        $model->is_active = 1;

        if (isset($_POST['Meals'])) {
            $this->bindModel($model, $_POST['Meals']);
            if ($model->validate()) {
                $result = $model->storeViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Thêm bữa ăn thành công.');
                    $this->redirect(array('admin', 'event_id' => $model->event_id));
                } else {
                    $model->addError('name', $result['error'] ?: 'Không thể thêm bữa ăn.');
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

        if (isset($_POST['Meals'])) {
            $this->bindModel($model, $_POST['Meals']);
            if ($model->validate()) {
                $result = $model->updateViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Cập nhật bữa ăn thành công.');
                    $this->redirect(array('admin', 'event_id' => $model->event_id));
                } else {
                    $model->addError('name', $result['error'] ?: 'Không thể cập nhật bữa ăn.');
                }
            }
        }

        $this->render('update', array(
            'model'     => $model,
            'eventList' => $this->getEventList(),
        ));
    }

    /** Đồng bộ danh sách bữa ăn từ agenda của sự kiện. */
    public function actionSyncFromAgenda()
    {
        if (!Yii::app()->getRequest()->getIsPostRequest()) {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }
        $eventId = isset($_POST['event_id']) ? (int) $_POST['event_id'] : 0;
        if (!$eventId) {
            Yii::app()->user->setFlash('error', 'Vui lòng chọn sự kiện trước khi đồng bộ.');
            $this->redirect(array('admin'));
        }

        $result = Meals::syncFromAgenda($eventId);
        if ($result['success']) {
            $msg = isset($result['data']['message']) ? $result['data']['message'] : 'Đồng bộ bữa ăn thành công.';
            Yii::app()->user->setFlash('success', $msg);
        } else {
            Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể đồng bộ bữa ăn.');
        }
        $this->redirect(array('admin', 'event_id' => $eventId));
    }

    /** Bật/tắt nhanh một bữa ăn (is_active). */
    public function actionToggle($id)
    {
        if (!Yii::app()->getRequest()->getIsPostRequest()) {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }
        $model = $this->loadModelById($id);
        $model->is_active = $model->is_active ? 0 : 1;
        $result = $model->updateViaApi();
        if ($result['success']) {
            Yii::app()->user->setFlash('success', 'Đã cập nhật trạng thái bữa ăn.');
        } else {
            Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể cập nhật trạng thái.');
        }
        $this->redirect(array('admin', 'event_id' => $model->event_id));
    }

    public function actionDelete($id)
    {
        if (!Yii::app()->getRequest()->getIsPostRequest()) {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }
        $result = Meals::deleteViaApi($id);
        if ($result['success']) {
            Yii::app()->user->setFlash('success', 'Xóa bữa ăn thành công.');
        } else {
            Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể xóa bữa ăn.');
        }
        if (!Yii::app()->getRequest()->getIsAjaxRequest()) {
            $this->redirect(array('admin'));
        }
    }

    protected function loadModelById($id)
    {
        $model = Meals::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy bữa ăn.');
        }
        return $model;
    }

    /** Gán form vào model; chuyển datetime-local -> 'Y-m-d H:i:s'. */
    protected function bindModel(Meals $model, array $post)
    {
        $model->setAttributes($post);
        $model->checkin_start = $this->toDateTime(isset($post['checkin_start']) ? $post['checkin_start'] : null);
        $model->checkin_end   = $this->toDateTime(isset($post['checkin_end']) ? $post['checkin_end'] : null);
    }

    protected function toDateTime($value)
    {
        if (empty($value)) {
            return null;
        }
        $ts = strtotime($value);
        return $ts ? date('Y-m-d H:i:s', $ts) : null;
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
