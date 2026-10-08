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

    /**
     * Nhận file ảnh từ dropzone (AJAX), lưu vào webroot/uploads/slideshows/Y/m
     * và trả JSON { success, url } để JS gán vào hidden field image/mobile_image.
     */
    public function actionUploadImage()
    {
        header('Content-Type: application/json');

        $fieldKey = 'file';
        if (!isset($_FILES[$fieldKey]) || $_FILES[$fieldKey]['error'] !== UPLOAD_ERR_OK) {
            echo CJSON::encode(array('success' => false, 'message' => 'Không nhận được tệp tải lên.'));
            Yii::app()->end();
        }

        $file = $_FILES[$fieldKey];
        $allowedExt = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt)) {
            echo CJSON::encode(array('success' => false, 'message' => 'Định dạng ảnh không hợp lệ (chỉ JPG, PNG, GIF, WEBP).'));
            Yii::app()->end();
        }

        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            echo CJSON::encode(array('success' => false, 'message' => 'Ảnh vượt quá dung lượng cho phép (5MB).'));
            Yii::app()->end();
        }

        $relativeDir = '/uploads/slideshows/' . date('Y/m');
        $uploadDir = Yii::getPathOfAlias('webroot') . $relativeDir;
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
            echo CJSON::encode(array('success' => false, 'message' => 'Không thể tạo thư mục lưu ảnh.'));
            Yii::app()->end();
        }

        $filename = 'slide_' . uniqid() . '.' . $ext;
        $targetPath = $uploadDir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            echo CJSON::encode(array('success' => true, 'url' => $relativeDir . '/' . $filename));
        } else {
            echo CJSON::encode(array('success' => false, 'message' => 'Không thể lưu ảnh lên máy chủ.'));
        }
        Yii::app()->end();
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
