<?php

class NewsController extends AdminController
{
	public function actionView($id)
	{
		$model = $this->loadModelById($id);
		$this->render('view', array(
			'model' => $model,
		));
	}

	public function actionCreate()
	{
		$model = new News;
		$model->is_published = News::IS_DRAFT;
		$model->is_featured = News::NOT_FEATURED;
		$model->category = 'general';

		if (isset($_POST['News'])) {
			$model->setAttributes($_POST['News']);
			$this->applyAuthor($model, true);

			if ($model->validate()) {
				$result = $model->storeViaApi();

				if ($result['success']) {
					Yii::app()->user->setFlash('success', 'Tạo tin tức thành công.');
					$newId = isset($result['data']['data']['id']) ? $result['data']['data']['id'] : null;
					$this->redirect($newId ? array('view', 'id' => $newId) : array('admin'));
				} else {
					$model->addError('title', $this->buildErrorMessage($result, 'Không thể tạo tin tức.'));
				}
			}
		}

		$this->render('create', array(
			'model' => $model,
			'eventList' => Events::getActiveList(),
			'categoryOptions' => News::getCategoryOptions(),
			'newsCategories' => NewsCategories::getActiveList($model->event_id),
		));
	}

	public function actionUpdate($id)
	{
		$model = $this->loadModelById($id);

		if (isset($_POST['News'])) {
			$model->setAttributes($_POST['News']);
			$this->applyAuthor($model, false);

			if ($model->validate()) {
				$result = $model->updateViaApi();

				if ($result['success']) {
					Yii::app()->user->setFlash('success', 'Cập nhật tin tức thành công.');
					$this->redirect(array('view', 'id' => $id));
				} else {
					$model->addError('title', $this->buildErrorMessage($result, 'Không thể cập nhật tin tức.'));
				}
			}
		}

		$this->render('update', array(
			'model' => $model,
			'eventList' => Events::getActiveList(),
			'categoryOptions' => News::getCategoryOptions(),
			'newsCategories' => NewsCategories::getActiveList($model->event_id),
		));
	}

	public function actionDelete($id)
	{
		if (Yii::app()->getRequest()->getIsPostRequest()) {
			$result = News::deleteViaApi($id);

			if ($result['success']) {
				Yii::app()->user->setFlash('success', 'Xóa tin tức thành công.');
			} else {
				Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể xóa tin tức.');
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

	/**
	 * Nhận file ảnh (AJAX) từ TinyMCE hoặc ô chọn ảnh đại diện,
	 * lưu vào webroot/uploads/news/Y/m và trả JSON { success, url, location }.
	 * Trường `location` dùng cho TinyMCE images_upload_handler.
	 */
	public function actionUploadImage()
	{
		header('Content-Type: application/json');

		$fieldKey = isset($_FILES['file']) ? 'file' : 'image';
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

		$relativeDir = '/uploads/news/' . date('Y/m');
		$uploadDir = Yii::getPathOfAlias('webroot') . $relativeDir;
		if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
			echo CJSON::encode(array('success' => false, 'message' => 'Không thể tạo thư mục lưu ảnh.'));
			Yii::app()->end();
		}

		$filename = 'news_' . uniqid() . '.' . $ext;
		$targetPath = $uploadDir . '/' . $filename;

		if (move_uploaded_file($file['tmp_name'], $targetPath)) {
			$url = $relativeDir . '/' . $filename;
			echo CJSON::encode(array('success' => true, 'url' => $url, 'location' => $url));
		} else {
			echo CJSON::encode(array('success' => false, 'message' => 'Không thể lưu ảnh lên máy chủ.'));
		}
		Yii::app()->end();
	}

	public function actionAdmin()
	{
		$model = new News('search');

		if (isset($_GET['News'])) {
			$model->setAttributes($_GET['News']);
		}

		$params = array();
		foreach ($model->attributes as $key => $value) {
			if ($value !== null && $value !== '') {
				$params[$key] = $value;
			}
		}

		$dataProvider = News::getApiDataProvider($params);

		$this->render('admin', array(
			'model' => $model,
			'dataProvider' => $dataProvider,
			'categoryOptions' => News::getCategoryOptions(),
		));
	}

	protected function loadModelById($id)
	{
		$model = News::fetchFromApi($id);
		if ($model === null) {
			throw new CHttpException(404, 'Không tìm thấy tin tức.');
		}
		return $model;
	}

	/**
	 * Gán người tạo/cập nhật từ SSO token
	 */
	protected function applyAuthor($model, $isCreate)
	{
		$ssoUser = AuthHandler::getUser();
		$email = isset($ssoUser['email']) ? $ssoUser['email'] : null;
		if ($isCreate && $email) {
			$model->created_by = $email;
		}
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
