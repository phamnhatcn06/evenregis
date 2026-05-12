<?php

class SportsController extends AdminController
{

	public function actions()
	{
		return array(
			'toggle' => array(
				'class' => 'booster.actions.TbToggleAction',
				'modelName' => 'Sports',
			)
		);
	}

	public function actionView($id)
	{
		$model = $this->loadModelById($id);
		$this->render('view', array(
			'model' => $model,
		));
	}

	public function actionCreate()
	{
		$model = new Sports;

		if (isset($_POST['Sports'])) {
			$model->setAttributes($_POST['Sports']);
			if ($model->validate()) {
				$model->is_active = 1;
				$result = $model->storeViaApi();
				if ($result['success']) {
					Yii::app()->user->setFlash('success', 'Tạo môn thể thao thành công.');
					if (Yii::app()->getRequest()->getIsAjaxRequest()) {
						echo CJSON::encode(array('success' => true, 'data' => $result['data']));
						Yii::app()->end();
					}
					$newId = isset($result['data']['id']) ? $result['data']['id'] : null;
					$this->redirect($newId ? array('view', 'id' => $newId) : array('admin'));
				} else {
					$errorMsg = $result['error'] ?: 'Không thể tạo môn thể thao.';
					if (isset($result['data']['errors'])) {
						$errorMsg .= ' Chi tiết: ' . json_encode($result['data']['errors']);
					} elseif (isset($result['data']['message'])) {
						$errorMsg .= ' - ' . $result['data']['message'];
					}
					$model->addError('name', $errorMsg);
				}
			}
		}

		$parentSports = Sports::getParentList();
		$this->render('create', array(
			'model' => $model,
			'parentSports' => $parentSports,
		));
	}

	public function actionUpdate($id)
	{
		$model = $this->loadModelById($id);

		if (isset($_POST['Sports'])) {
			$model->setAttributes($_POST['Sports']);

			if ($model->validate()) {
				$result = $model->updateViaApi();

				if ($result['success']) {
					Yii::app()->user->setFlash('success', 'Cập nhật môn thể thao thành công.');
					$this->redirect(array('view', 'id' => $id));
				} else {
					$model->addError('name', $result['error'] ?: 'Không thể cập nhật môn thể thao.');
				}
			}
		}

		$parentSports = Sports::getParentList($id);
		$this->render('update', array(
			'model' => $model,
			'parentSports' => $parentSports,
		));
	}

	public function actionDelete($id)
	{
		if (Yii::app()->getRequest()->getIsPostRequest()) {
			$result = Sports::deleteViaApi($id);

			if ($result['success']) {
				Yii::app()->user->setFlash('success', 'Xóa môn thể thao thành công.');
			} else {
				Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể xóa môn thể thao.');
			}

			if (!Yii::app()->getRequest()->getIsAjaxRequest()) {
				$this->redirect(array('admin'));
			}
		} else {
			throw new CHttpException(400, Yii::t('app', 'Your request is invalid.'));
		}
	}

	protected function loadModelById($id)
	{
		$model = Sports::fetchFromApi($id);
		if ($model === null) {
			throw new CHttpException(404, 'Không tìm thấy môn thể thao.');
		}
		return $model;
	}

	public function actionIndex()
	{
		$dataProvider = Sports::getApiDataProvider();
		$this->render('index', array(
			'dataProvider' => $dataProvider,
		));
	}

	public function actionAdmin()
	{
		$model = new Sports('search');
		$model->unsetAttributes();

		if (isset($_GET['Sports'])) {
			$model->setAttributes($_GET['Sports']);
		}

		$params = array();
		foreach ($model->attributes as $key => $value) {
			if ($value !== null && $value !== '') {
				$params[$key] = $value;
			}
		}

		$treeData = Sports::buildTreeData();
		$dataProvider = new CArrayDataProvider($treeData['items'], array(
			'keyField' => 'id',
			'pagination' => array(
				'pageSize' => 50,
			),
		));

		$this->render('admin', array(
			'model' => $model,
			'dataProvider' => $dataProvider,
			'levelMap' => $treeData['levelMap'],
		));
	}

	public function actionUpdateField($id, $field)
	{
		$r = Yii::app()->getRequest();
		echo $r->getParam('value');
		Sports::model()->updateByPk($id, array($field => $r->getParam('value')));
		Yii::app()->end();
	}
}
