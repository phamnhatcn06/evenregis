<?php

class RegistrationPeriodsController extends AdminController
{
	public function actionView($id)
	{
		$model = $this->loadModelById($id);
		$this->render('view', array('model' => $model));
	}

	public function actionCreate()
	{
		$model = new RegistrationPeriods;
		$events = Events::getApiDataProvider(array('status' => 1), 100)->getData();

		if (isset($_POST['RegistrationPeriods'])) {
			$model->setAttributes($_POST['RegistrationPeriods']);
			$model->is_active = true;
			if ($model->validate()) {
				$result = $model->storeViaApi();

				if ($result['success']) {
					Yii::app()->user->setFlash('success', 'Tạo đợt đăng ký thành công.');
					$newId = isset($result['data']['id']) ? $result['data']['id'] : null;
					$this->redirect($newId ? array('view', 'id' => $newId) : array('admin'));
				} else {
					$errorMsg = isset($result['error']) ? $result['error'] : 'Không thể tạo đợt đăng ký.';
					$model->addError('name', $errorMsg);
				}
			}
		}

		$this->render('create', array(
			'model' => $model,
			'events' => $events,
		));
	}

	public function actionUpdate($id)
	{
		$model = $this->loadModelById($id);
		$events = Events::getApiDataProvider(array('status' => 1), 100)->getData();

		if (isset($_POST['RegistrationPeriods'])) {
			$model->setAttributes($_POST['RegistrationPeriods']);
			$model->is_active = isset($_POST['RegistrationPeriods']['is_active']) ? 1 : 0;

			if ($model->validate()) {
				$result = $model->updateViaApi();

				if ($result['success']) {
					Yii::app()->user->setFlash('success', 'Cập nhật đợt đăng ký thành công.');
					$this->redirect(array('view', 'id' => $id));
				} else {
					$model->addError('name', isset($result['error']) ? $result['error'] : 'Không thể cập nhật.');
				}
			}
		}

		$this->render('update', array(
			'model' => $model,
			'events' => $events,
		));
	}

	public function actionDelete($id)
	{
		if (Yii::app()->getRequest()->getIsPostRequest()) {
			$result = RegistrationPeriods::deleteViaApi($id);

			if ($result['success']) {
				Yii::app()->user->setFlash('success', 'Xóa đợt đăng ký thành công.');
			} else {
				Yii::app()->user->setFlash('error', isset($result['error']) ? $result['error'] : 'Không thể xóa.');
			}

			if (!Yii::app()->getRequest()->getIsAjaxRequest()) {
				$this->redirect(array('admin'));
			}
		} else {
			throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
		}
	}

	protected function loadModelById($id)
	{
		$model = RegistrationPeriods::fetchFromApi($id);
		if ($model === null) {
			throw new CHttpException(404, 'Không tìm thấy đợt đăng ký.');
		}
		return $model;
	}

	public function actionAdmin()
	{
		$model = new RegistrationPeriods('search');
		$model->unsetAttributes();

		if (isset($_GET['RegistrationPeriods'])) {
			$model->setAttributes($_GET['RegistrationPeriods']);
		}

		$params = array();
		foreach ($model->attributes as $key => $value) {
			if ($value !== null && $value !== '') {
				$params[$key] = $value;
			}
		}

		$dataProvider = RegistrationPeriods::getApiDataProvider($params);

		$this->render('admin', array(
			'model' => $model,
			'dataProvider' => $dataProvider,
		));
	}
}
