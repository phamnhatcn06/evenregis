<?php

class EventsController extends AdminController
{

	public function actions()
	{
		return array(
			'toggle' => array(
				'class' => 'booster.actions.TbToggleAction',
				'modelName' => 'Events',
			)
		);
	}

	public function actionView($id)
	{
		$model = $this->loadModelById($id);
		$eventContents = EventContents::getByEventId($id);
		$allContents = Contents::getApiDataProvider(array(), 100)->getData();
		$eventUnits = EventUnits::getByEventId($id);
		$allProperties = Properties::getApiDataProvider(array(), 100)->getData();

		$this->render('view', array(
			'model' => $model,
			'eventContents' => $eventContents,
			'allContents' => $allContents,
			'eventUnits' => $eventUnits,
			'allProperties' => $allProperties,
		));
	}

	public function actionAddContent($id)
	{
		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			$this->redirect(array('view', 'id' => $id));
			return;
		}

		$contentId = Yii::app()->getRequest()->getPost('content_id');

		if ($contentId) {
			$eventContent = new EventContents;
			$eventContent->event_id = $id;
			$eventContent->content_id = $contentId;
			$eventContent->status = 1;
			$result = $eventContent->storeViaApi();

			if ($result['success']) {
				Yii::app()->user->setFlash('success', 'Thêm nội dung thành công.');
			} else {
				Yii::app()->user->setFlash('error', isset($result['error']) ? $result['error'] : 'Không thể thêm nội dung.');
			}
		}
		$this->redirect(array('view', 'id' => $id));
	}

	public function actionRemoveContent($id, $contentId)
	{
		if (Yii::app()->getRequest()->getIsPostRequest()) {
			$result = EventContents::deleteViaApi($contentId);

			if ($result['success']) {
				Yii::app()->user->setFlash('success', 'Xóa nội dung thành công.');
			} else {
				Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể xóa nội dung.');
			}
			$this->redirect(array('view', 'id' => $id));
		}
	}

	public function actionSyncUnits($id)
	{
		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			$this->redirect(array('view', 'id' => $id));
			return;
		}

		$propertyIds = Yii::app()->getRequest()->getPost('property_ids', array());
		$currentUnits = EventUnits::getByEventId($id);

		$currentPropertyIds = array();
		$unitMap = array();
		foreach ($currentUnits as $eu) {
			$currentPropertyIds[] = $eu['property_id'];
			$unitMap[$eu['property_id']] = $eu['id'];
		}

		$toAdd = array_diff($propertyIds, $currentPropertyIds);
		$toRemove = array_diff($currentPropertyIds, $propertyIds);

		$success = true;
		foreach ($toAdd as $propertyId) {
			$eventUnit = new EventUnits;
			$eventUnit->event_id = $id;
			$eventUnit->property_id = $propertyId;
			$eventUnit->status = 1;
			$result = $eventUnit->storeViaApi();
			if (!$result['success']) {
				$success = false;
			}
		}

		foreach ($toRemove as $propertyId) {
			if (isset($unitMap[$propertyId])) {
				$result = EventUnits::deleteViaApi($unitMap[$propertyId]);
				if (!$result['success']) {
					$success = false;
				}
			}
		}

		if ($success) {
			Yii::app()->user->setFlash('success', 'Cập nhật đơn vị thành công.');
		} else {
			Yii::app()->user->setFlash('error', 'Có lỗi khi cập nhật đơn vị.');
		}

		$this->redirect(array('view', 'id' => $id));
	}

	public function actionCreate()
	{
		$model = new Events;

		if (isset($_POST['Events'])) {
			$model->setAttributes($_POST['Events']);
			if ($model->validate()) {
				$model->status = 1;
				$result = $model->storeViaApi();

				if ($result['success']) {
					Yii::app()->user->setFlash('success', 'Tạo sự kiện thành công.');
					if (Yii::app()->getRequest()->getIsAjaxRequest()) {
						echo CJSON::encode(array('success' => true, 'data' => $result['data']));
						Yii::app()->end();
					}
					$newId = isset($result['data']['id']) ? $result['data']['id'] : null;
					$this->redirect($newId ? array('view', 'id' => $newId) : array('admin'));
				} else {
					$errorMsg = $result['error'] ?: 'Không thể tạo sự kiện.';
					if (isset($result['data']['errors'])) {
						$errorMsg .= ' Chi tiết: ' . json_encode($result['data']['errors']);
					} elseif (isset($result['data']['message'])) {
						$errorMsg .= ' - ' . $result['data']['message'];
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

		if (isset($_POST['Events'])) {
			$model->setAttributes($_POST['Events']);

			if ($model->validate()) {
				$result = $model->updateViaApi();

				if ($result['success']) {
					Yii::app()->user->setFlash('success', 'Cập nhật sự kiện thành công.');
					$this->redirect(array('view', 'id' => $id));
				} else {
					$model->addError('name', $result['error'] ?: 'Không thể cập nhật sự kiện.');
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
			$result = Events::deleteViaApi($id);

			if ($result['success']) {
				Yii::app()->user->setFlash('success', 'Xóa sự kiện thành công.');
			} else {
				Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể xóa sự kiện.');
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
		$model = Events::fetchFromApi($id);
		if ($model === null) {
			throw new CHttpException(404, 'Không tìm thấy sự kiện.');
		}
		return $model;
	}

	public function actionIndex()
	{
		$dataProvider = new CActiveDataProvider('Events');
		$this->render('index', array(
			'dataProvider' => $dataProvider,
		));
	}

	public function actionAdmin()
	{
		$model = new Events('search');
		$model->unsetAttributes();

		if (isset($_GET['Events'])) {
			$model->setAttributes($_GET['Events']);
		}

		$params = array();
		foreach ($model->attributes as $key => $value) {
			if ($value !== null && $value !== '') {
				$params[$key] = $value;
			}
		}

		$dataProvider = Events::getApiDataProvider($params);

		$this->render('admin', array(
			'model' => $model,
			'dataProvider' => $dataProvider,
		));
	}

	public function actionUpdateField($id, $field)
	{
		$r = Yii::app()->getRequest();
		echo $r->getParam('value');
		Events::model()->updateByPk($id, array($field => $r->getParam('value')));
		Yii::app()->end();
	}
}
