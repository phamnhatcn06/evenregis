<?php

/**
 * Quản trị Album Thư viện (media_albums).
 * Dữ liệu thao tác qua External API theo model MediaAlbum.
 */
class MediaAlbumsController extends AdminController
{
    public function actionView($id)
    {
        $model = $this->loadModelById($id);
        $this->render('view', array(
            'model' => $model,
            'items' => Daihoi::getAlbumItems($id),
        ));
    }

    public function actionCreate()
    {
        $model = new MediaAlbum;
        $model->is_active = MediaAlbum::IS_ACTIVE;
        $model->type = 'photo';

        if (isset($_POST['MediaAlbum'])) {
            $model->setAttributes($_POST['MediaAlbum']);
            if ($model->validate()) {
                $result = $model->storeViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Tạo album thành công.');
                    $newId = isset($result['data']['data']['id']) ? $result['data']['data']['id'] : null;
                    $this->redirect($newId ? array('view', 'id' => $newId) : array('admin'));
                } else {
                    $model->addError('title', $this->buildErrorMessage($result, 'Không thể tạo album.'));
                }
            }
        }

        $this->render('create', array(
            'model' => $model,
            'eventList' => Events::getActiveList(),
            'typeOptions' => MediaAlbum::getTypeOptions(),
        ));
    }

    public function actionUpdate($id)
    {
        $model = $this->loadModelById($id);

        if (isset($_POST['MediaAlbum'])) {
            $model->setAttributes($_POST['MediaAlbum']);
            if ($model->validate()) {
                $result = $model->updateViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Cập nhật album thành công.');
                    $this->redirect(array('view', 'id' => $id));
                } else {
                    $model->addError('title', $this->buildErrorMessage($result, 'Không thể cập nhật album.'));
                }
            }
        }

        $this->render('update', array(
            'model' => $model,
            'eventList' => Events::getActiveList(),
            'typeOptions' => MediaAlbum::getTypeOptions(),
        ));
    }

    public function actionDelete($id)
    {
        if (Yii::app()->getRequest()->getIsPostRequest()) {
            $result = MediaAlbum::deleteViaApi($id);
            if ($result['success']) {
                Yii::app()->user->setFlash('success', 'Xóa album thành công.');
            } else {
                Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể xóa album.');
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
        $model = new MediaAlbum;
        $params = array();
        if (isset($_GET['MediaAlbum'])) {
            $model->setAttributes($_GET['MediaAlbum']);
            foreach ($_GET['MediaAlbum'] as $key => $value) {
                if ($value !== null && $value !== '') {
                    $params[$key] = $value;
                }
            }
        }

        $this->render('admin', array(
            'model' => $model,
            'dataProvider' => MediaAlbum::getApiDataProvider($params),
            'typeOptions' => MediaAlbum::getTypeOptions(),
        ));
    }

    protected function loadModelById($id)
    {
        $model = MediaAlbum::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy album.');
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
