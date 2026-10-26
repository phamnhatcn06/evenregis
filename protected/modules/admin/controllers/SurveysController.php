<?php

/**
 * Quản trị Khảo sát ý kiến (link Google Form) của Website công khai Đại hội.
 * Dữ liệu thao tác qua External API theo model Survey.
 */
class SurveysController extends AdminController
{
    public function actionView($id)
    {
        $this->render('view', array('model' => $this->loadModelById($id)));
    }

    public function actionCreate()
    {
        $model = new Survey;
        $model->is_active = Survey::IS_INACTIVE; // Mặc định tắt, admin chủ động bật.
        $model->sort_order = 0;

        if (isset($_POST['Survey'])) {
            $model->setAttributes($_POST['Survey']);
            if ($model->validate()) {
                $result = $model->storeViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Tạo khảo sát thành công.');
                    $newId = isset($result['data']['data']['id']) ? $result['data']['data']['id'] : null;
                    $this->redirect($newId ? array('view', 'id' => $newId) : array('admin'));
                } else {
                    $model->addError('title', $this->buildErrorMessage($result, 'Không thể tạo khảo sát.'));
                }
            }
        }

        $this->render('create', array(
            'model' => $model,
            'eventList' => Events::getActiveList(),
        ));
    }

    public function actionUpdate($id)
    {
        $model = $this->loadModelById($id);

        if (isset($_POST['Survey'])) {
            $model->setAttributes($_POST['Survey']);
            if ($model->validate()) {
                $result = $model->updateViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Cập nhật khảo sát thành công.');
                    $this->redirect(array('view', 'id' => $id));
                } else {
                    $model->addError('title', $this->buildErrorMessage($result, 'Không thể cập nhật khảo sát.'));
                }
            }
        }

        $this->render('update', array(
            'model' => $model,
            'eventList' => Events::getActiveList(),
        ));
    }

    public function actionDelete($id)
    {
        if (Yii::app()->getRequest()->getIsPostRequest()) {
            $result = Survey::deleteViaApi($id);
            if ($result['success']) {
                Yii::app()->user->setFlash('success', 'Xóa khảo sát thành công.');
            } else {
                Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể xóa khảo sát.');
            }
            if (!Yii::app()->getRequest()->getIsAjaxRequest()) {
                $this->redirect(array('admin'));
            }
        } else {
            throw new CHttpException(400, Yii::t('app', 'Your request is invalid.'));
        }
    }

    /**
     * Bật/tắt nhanh trạng thái hiển thị của khảo sát (AJAX).
     */
    public function actionToggle($id)
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!Yii::app()->getRequest()->getIsPostRequest()) {
            echo CJSON::encode(array('success' => false, 'message' => 'Yêu cầu không hợp lệ.'));
            Yii::app()->end();
        }

        $result = Survey::toggleViaApi($id);
        if (!empty($result['success'])) {
            $data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
            $isActive = isset($data['is_active']) ? (int) $data['is_active'] : null;
            echo CJSON::encode(array(
                'success' => true,
                'is_active' => $isActive,
                'message' => isset($result['data']['message']) ? $result['data']['message'] : 'Cập nhật trạng thái thành công.',
            ));
        } else {
            echo CJSON::encode(array(
                'success' => false,
                'message' => $result['error'] ?: 'Không thể cập nhật trạng thái.',
            ));
        }
        Yii::app()->end();
    }

    public function actionIndex()
    {
        $this->redirect(array('admin'));
    }

    public function actionAdmin()
    {
        $model = new Survey;
        $params = array();
        if (isset($_GET['Survey'])) {
            $model->setAttributes($_GET['Survey']);
            foreach ($_GET['Survey'] as $key => $value) {
                if ($value !== null && $value !== '') {
                    $params[$key] = $value;
                }
            }
        }

        $this->render('admin', array(
            'model' => $model,
            'dataProvider' => Survey::getApiDataProvider($params),
        ));
    }

    protected function loadModelById($id)
    {
        $model = Survey::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy khảo sát.');
        }
        return $model;
    }

    protected function buildErrorMessage($result, $default)
    {
        $errorMsg = $result['error'] ?: $default;
        if (isset($result['data']['data']['errors'])) {
            $errorMsg .= ' ' . json_encode($result['data']['data']['errors'], JSON_UNESCAPED_UNICODE);
        }
        return $errorMsg;
    }
}
