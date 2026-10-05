<?php

/**
 * Quản trị Slideshow (hero slider trang chủ Đại hội).
 * Dữ liệu thao tác qua External API theo model Slideshow.
 */
class SlideshowController extends AdminController
{
    public function actionView($id)
    {
        $this->render('view', array('model' => $this->loadModelById($id)));
    }

    public function actionCreate()
    {
        $model = new Slideshow;
        $model->is_active = Slideshow::IS_ACTIVE;
        $model->theme = 'blue';

        if (isset($_POST['Slideshow'])) {
            $model->setAttributes($_POST['Slideshow']);
            if ($model->validate()) {
                $result = $model->storeViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Tạo slide thành công.');
                    $newId = isset($result['data']['data']['id']) ? $result['data']['data']['id'] : null;
                    $this->redirect($newId ? array('view', 'id' => $newId) : array('admin'));
                } else {
                    $model->addError('title', $this->buildErrorMessage($result, 'Không thể tạo slide.'));
                }
            }
        }

        $this->render('create', array(
            'model' => $model,
            'eventList' => Events::getActiveList(),
            'themeOptions' => Slideshow::getThemeOptions(),
        ));
    }

    public function actionUpdate($id)
    {
        $model = $this->loadModelById($id);

        if (isset($_POST['Slideshow'])) {
            $model->setAttributes($_POST['Slideshow']);
            if ($model->validate()) {
                $result = $model->updateViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Cập nhật slide thành công.');
                    $this->redirect(array('view', 'id' => $id));
                } else {
                    $model->addError('title', $this->buildErrorMessage($result, 'Không thể cập nhật slide.'));
                }
            }
        }

        $this->render('update', array(
            'model' => $model,
            'eventList' => Events::getActiveList(),
            'themeOptions' => Slideshow::getThemeOptions(),
        ));
    }

    public function actionDelete($id)
    {
        if (Yii::app()->getRequest()->getIsPostRequest()) {
            $result = Slideshow::deleteViaApi($id);
            if ($result['success']) {
                Yii::app()->user->setFlash('success', 'Xóa slide thành công.');
            } else {
                Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể xóa slide.');
            }
            if (!Yii::app()->getRequest()->getIsAjaxRequest()) {
                $this->redirect(array('admin'));
            }
        } else {
            throw new CHttpException(400, Yii::t('app', 'Your request is invalid.'));
        }
    }

    public function actionIndex()
    {
        $this->redirect(array('admin'));
    }

    public function actionAdmin()
    {
        $model = new Slideshow;
        $params = array();
        if (isset($_GET['Slideshow'])) {
            $model->setAttributes($_GET['Slideshow']);
            foreach ($_GET['Slideshow'] as $key => $value) {
                if ($value !== null && $value !== '') {
                    $params[$key] = $value;
                }
            }
        }

        $this->render('admin', array(
            'model' => $model,
            'dataProvider' => Slideshow::getApiDataProvider($params),
            'themeOptions' => Slideshow::getThemeOptions(),
        ));
    }

    protected function loadModelById($id)
    {
        $model = Slideshow::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy slide.');
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
