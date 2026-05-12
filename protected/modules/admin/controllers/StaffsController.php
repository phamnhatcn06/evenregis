<?php

class StaffsController extends AdminController
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
		$model = new Staffs;

		if (isset($_POST['Staffs'])) {
			$model->setAttributes($_POST['Staffs']);

			if ($model->validate()) {
				$result = $model->storeViaApi();

				if ($result['success']) {
					Yii::app()->user->setFlash('success', 'Tạo thành công.');
					$newId = isset($result['data']['data']['id']) ? $result['data']['data']['id'] : null;
					$this->redirect($newId ? array('view', 'id' => $newId) : array('admin'));
				} else {
					$errorMsg = $result['error'] ?: 'Không thể tạo.';
					if (isset($result['data']['data']['errors'])) {
						$errorMsg .= ' ' . json_encode($result['data']['data']['errors']);
					}
					$model->addError('full_name', $errorMsg);
				}
			}
		}

		$this->render('create', array('model' => $model));
	}

	public function actionUpdate($id)
	{
		$model = $this->loadModelById($id);

		if (isset($_POST['Staffs'])) {
			$model->setAttributes($_POST['Staffs']);

			if ($model->validate()) {
				$result = $model->updateViaApi();

				if ($result['success']) {
					Yii::app()->user->setFlash('success', 'Cập nhật thành công.');
					$this->redirect(array('view', 'id' => $id));
				} else {
					$model->addError('full_name', $result['error'] ?: 'Không thể cập nhật.');
				}
			}
		}

		$this->render('update', array(
			'model' => $model,
		));
	}

	public function actionDelete($id)
	{
		if (Yii::app()->getRequest()->getIsPostRequest()) {
			$result = Staffs::deleteViaApi($id);

			if ($result['success']) {
				Yii::app()->user->setFlash('success', 'Xóa thành công.');
			} else {
				Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể xóa.');
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
		$model = new Staffs('search');
		$model->unsetAttributes();

		if (isset($_GET['Staffs'])) {
			$model->setAttributes($_GET['Staffs']);
		}

		$propertiesResult = Staffs::fetchPropertiesForDropdown();
		$properties = array();
		if ($propertiesResult['success'] && isset($propertiesResult['data']['data'])) {
			foreach ($propertiesResult['data']['data'] as $prop) {
				$properties[$prop['code']] = $prop['name'];
			}
		}

		$params = array();
		$selectedProperty = isset($_GET['property_code']) ? $_GET['property_code'] : '';
		if ($selectedProperty) {
			$params['property_code'] = $selectedProperty;
		}

		$dataProvider = Staffs::getApiDataProvider($params);

		$this->render('admin', array(
			'model' => $model,
			'dataProvider' => $dataProvider,
			'properties' => $properties,
			'selectedProperty' => $selectedProperty,
		));
	}

	protected function loadModelById($id)
	{
		$model = Staffs::fetchFromApi($id);
		if ($model === null) {
			throw new CHttpException(404, 'Không tìm thấy dữ liệu.');
		}
		return $model;
	}
}
