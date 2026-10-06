<?php

/**
 * Quản trị nội dung chạy (Run event): cấu hình cự ly/nhóm tuổi, quota, khung thời gian.
 * Dữ liệu qua External API (model RunEvents). Permission key: "runevents".
 */
class RunEventsController extends AdminController
{
    public function actionAdmin()
    {
        $params = array();
        if (isset($_GET['event_id']) && $_GET['event_id'] !== '') {
            $params['event_id'] = (int) $_GET['event_id'];
        }
        if (isset($_GET['status']) && $_GET['status'] !== '') {
            $params['status'] = $_GET['status'];
        }

        $dataProvider = RunEvents::getApiDataProvider($params);

        $this->render('admin', array(
            'dataProvider' => $dataProvider,
            'eventList'    => $this->getEventList(),
        ));
    }

    public function actionView($id)
    {
        $model = $this->loadModelById($id);
        $this->render('view', array('model' => $model));
    }

    public function actionCreate()
    {
        $model = new RunEvents;

        if (isset($_POST['RunEvents'])) {
            $this->bindModel($model, $_POST['RunEvents']);
            if ($model->validate()) {
                $result = $model->storeViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Tạo nội dung chạy thành công.');
                    $newId = isset($result['data']['data']['id']) ? $result['data']['data']['id'] : null;
                    $this->redirect($newId ? array('view', 'id' => $newId) : array('admin'));
                } else {
                    $model->addError('name', $result['error'] ?: 'Không thể tạo nội dung chạy.');
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

        if (isset($_POST['RunEvents'])) {
            $this->bindModel($model, $_POST['RunEvents']);
            if ($model->validate()) {
                $result = $model->updateViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Cập nhật nội dung chạy thành công.');
                    $this->redirect(array('view', 'id' => $id));
                } else {
                    $model->addError('name', $result['error'] ?: 'Không thể cập nhật nội dung chạy.');
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
        $result = RunEvents::deleteViaApi($id);
        if ($result['success']) {
            Yii::app()->user->setFlash('success', 'Xóa nội dung chạy thành công.');
        } else {
            Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể xóa nội dung chạy.');
        }
        if (!Yii::app()->getRequest()->getIsAjaxRequest()) {
            $this->redirect(array('admin'));
        }
    }

    protected function loadModelById($id)
    {
        $model = RunEvents::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy nội dung chạy.');
        }
        return $model;
    }

    /** Gán dữ liệu form vào model, chuyển open_at/close_at (datetime-local) -> unix. */
    protected function bindModel(RunEvents $model, array $post)
    {
        $model->setAttributes($post);
        $model->open_at      = $this->toTimestamp(isset($post['open_at']) ? $post['open_at'] : null);
        $model->close_at     = $this->toTimestamp(isset($post['close_at']) ? $post['close_at'] : null);
        $model->cancel_until = $this->toTimestamp(isset($post['cancel_until']) ? $post['cancel_until'] : null);
    }

    protected function toTimestamp($value)
    {
        if (empty($value)) {
            return null;
        }
        if (is_numeric($value)) {
            return (int) $value;
        }
        $ts = strtotime($value);
        return $ts ?: null;
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
            // bỏ qua nếu không lấy được danh sách sự kiện
        }
        return $list;
    }
}
