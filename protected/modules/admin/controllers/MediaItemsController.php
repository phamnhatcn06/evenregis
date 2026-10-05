<?php

/**
 * Quản trị Item (ảnh/video) trong một album Thư viện (media_items).
 * Luôn làm việc trong phạm vi một album: truyền album_id qua GET.
 */
class MediaItemsController extends AdminController
{
    public function actionView($id)
    {
        $this->render('view', array('model' => $this->loadModelById($id)));
    }

    public function actionCreate()
    {
        $albumId = (int) Yii::app()->request->getParam('album_id');
        $album = $this->loadAlbum($albumId);

        $model = new MediaItem;
        $model->album_id = $albumId;
        $model->event_id = $album->event_id;
        $model->is_active = MediaItem::IS_ACTIVE;
        $model->type = 'image';

        if (isset($_POST['MediaItem'])) {
            $model->setAttributes($_POST['MediaItem']);
            $model->album_id = $albumId;
            $model->event_id = $album->event_id;
            if ($model->validate()) {
                $result = $model->storeViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Thêm mục thành công.');
                    $this->redirect(array('admin', 'album_id' => $albumId));
                } else {
                    $model->addError('url', $this->buildErrorMessage($result, 'Không thể thêm mục.'));
                }
            }
        }

        $this->render('create', array('model' => $model, 'album' => $album));
    }

    public function actionUpdate($id)
    {
        $model = $this->loadModelById($id);
        $album = $this->loadAlbum($model->album_id);

        if (isset($_POST['MediaItem'])) {
            $model->setAttributes($_POST['MediaItem']);
            if ($model->validate()) {
                $result = $model->updateViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Cập nhật mục thành công.');
                    $this->redirect(array('admin', 'album_id' => $model->album_id));
                } else {
                    $model->addError('url', $this->buildErrorMessage($result, 'Không thể cập nhật mục.'));
                }
            }
        }

        $this->render('update', array('model' => $model, 'album' => $album));
    }

    public function actionDelete($id)
    {
        if (Yii::app()->getRequest()->getIsPostRequest()) {
            $model = $this->loadModelById($id);
            $albumId = $model->album_id;
            $result = MediaItem::deleteViaApi($id);
            if ($result['success']) {
                Yii::app()->user->setFlash('success', 'Xóa mục thành công.');
            } else {
                Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể xóa mục.');
            }
            if (!Yii::app()->getRequest()->getIsAjaxRequest()) {
                $this->redirect(array('admin', 'album_id' => $albumId));
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
        $albumId = (int) Yii::app()->request->getParam('album_id');
        $album = $this->loadAlbum($albumId);

        $params = array('album_id' => $albumId);
        $model = new MediaItem;
        if (isset($_GET['MediaItem'])) {
            $model->setAttributes($_GET['MediaItem']);
            foreach ($_GET['MediaItem'] as $key => $value) {
                if ($value !== null && $value !== '') {
                    $params[$key] = $value;
                }
            }
        }

        $this->render('admin', array(
            'model' => $model,
            'album' => $album,
            'dataProvider' => MediaItem::getApiDataProvider($params, 50),
        ));
    }

    protected function loadModelById($id)
    {
        $model = MediaItem::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy mục.');
        }
        return $model;
    }

    protected function loadAlbum($albumId)
    {
        $album = MediaAlbum::fetchFromApi($albumId);
        if ($album === null) {
            throw new CHttpException(404, 'Không tìm thấy album.');
        }
        return $album;
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
