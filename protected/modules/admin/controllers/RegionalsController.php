<?php

class RegionalsController extends AdminController
{
	public function actionView($id)
	{
		$model = $this->loadModelById($id);
		$organizations = Regionals::getOrganizations($id);

		$this->render('view', array(
			'model' => $model,
			'organizations' => $organizations,
		));
	}

	public function actionCreate()
	{
		$model = new Regionals;

		if (isset($_POST['Regionals'])) {
			$model->setAttributes($_POST['Regionals']);

			if ($model->validate()) {
				$result = $model->storeViaApi();

				if ($result['success']) {
					Yii::app()->user->setFlash('success', 'Tạo khu vực thành công.');
					$newId = isset($result['data']['data']['id']) ? $result['data']['data']['id'] : null;
					$this->redirect($newId ? array('view', 'id' => $newId) : array('admin'));
				} else {
					$errorMsg = $result['error'] ?: 'Không thể tạo.';
					if (isset($result['data']['data']['errors'])) {
						$errorMsg .= ' ' . json_encode($result['data']['data']['errors']);
					}
					$model->addError('name', $errorMsg);
				}
			}
		}

		$this->render('create', array('model' => $model));
	}

	public function actionUpdate($id)
	{
		$model = $this->loadModelById($id);

		if (isset($_POST['Regionals'])) {
			$model->setAttributes($_POST['Regionals']);

			if ($model->validate()) {
				$result = $model->updateViaApi();

				if ($result['success']) {
					Yii::app()->user->setFlash('success', 'Cập nhật thành công.');
					$this->redirect(array('view', 'id' => $id));
				} else {
					$model->addError('name', $result['error'] ?: 'Không thể cập nhật.');
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
			$result = Regionals::deleteViaApi($id);

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
		$model = new Regionals('search');
		$model->unsetAttributes();

		if (isset($_GET['Regionals'])) {
			$model->setAttributes($_GET['Regionals']);
		}

		$params = array();
		foreach ($model->attributes as $key => $value) {
			if ($value !== null && $value !== '') {
				$params[$key] = $value;
			}
		}

		$dataProvider = Regionals::getApiDataProvider($params);

		$this->render('admin', array(
			'model' => $model,
			'dataProvider' => $dataProvider,
		));
	}

	public function actionAssignOrganizations($id)
	{
		if (Yii::app()->getRequest()->getIsPostRequest()) {
			$organizationIds = isset($_POST['organization_ids']) ? $_POST['organization_ids'] : array();
			$result = Regionals::assignOrganizations($id, $organizationIds);

			if ($result['success']) {
				Yii::app()->user->setFlash('success', 'Cập nhật danh sách đơn vị thành công.');
			} else {
				Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể cập nhật.');
			}

			$this->redirect(array('view', 'id' => $id));
		} else {
			throw new CHttpException(400, Yii::t('app', 'Your request is invalid.'));
		}
	}

	protected function loadModelById($id)
	{
		$model = Regionals::fetchFromApi($id);
		if ($model === null) {
			throw new CHttpException(404, 'Không tìm thấy khu vực.');
		}
		return $model;
	}
}
