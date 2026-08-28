<?php

class NewsCategoriesController extends AdminController
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
		$model = new NewsCategories;
		$model->is_active = NewsCategories::IS_ACTIVE;
		$model->sort_order = 0;

		if (isset($_POST['NewsCategories'])) {
			$model->setAttributes($_POST['NewsCategories']);

			if ($model->validate()) {
				$result = $model->storeViaApi();

				if ($result['success']) {
					Yii::app()->user->setFlash('success', 'Tạo danh mục thành công.');
					$newId = isset($result['data']['data']['id']) ? $result['data']['data']['id'] : null;
					$this->redirect($newId ? array('view', 'id' => $newId) : array('admin'));
				} else {
					$model->addError('name', $this->buildErrorMessage($result, 'Không thể tạo danh mục.'));
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

		if (isset($_POST['NewsCategories'])) {
			$model->setAttributes($_POST['NewsCategories']);

			if ($model->validate()) {
				$result = $model->updateViaApi();

				if ($result['success']) {
					Yii::app()->user->setFlash('success', 'Cập nhật danh mục thành công.');
					$this->redirect(array('view', 'id' => $id));
				} else {
					$model->addError('name', $this->buildErrorMessage($result, 'Không thể cập nhật danh mục.'));
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
			$result = NewsCategories::deleteViaApi($id);

			if ($result['success']) {
				Yii::app()->user->setFlash('success', 'Xóa danh mục thành công.');
			} else {
				Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể xóa danh mục.');
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
		$model = new NewsCategories('search');
		$model->unsetAttributes();

		if (isset($_GET['NewsCategories'])) {
			$model->setAttributes($_GET['NewsCategories']);
		}

		$params = array();
		foreach ($model->attributes as $key => $value) {
			if ($value !== null && $value !== '') {
				$params[$key] = $value;
			}
		}

		$dataProvider = NewsCategories::getApiDataProvider($params);

		$this->render('admin', array(
			'model' => $model,
			'dataProvider' => $dataProvider,
		));
	}

	protected function loadModelById($id)
	{
		$model = NewsCategories::fetchFromApi($id);
		if ($model === null) {
			throw new CHttpException(404, 'Không tìm thấy danh mục.');
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
