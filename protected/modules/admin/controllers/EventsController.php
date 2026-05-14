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
		$eventSports = EventSports::getByEventId($id);
		$sportsTreeData = Sports::buildTreeData();
		$eventCompetitions = EventCompetitions::getByEventId($id);
		$allCompetitions = Competitions::getApiDataProvider(array('is_active' => 1), 100)->getData();

		$this->render('view', array(
			'model' => $model,
			'eventContents' => $eventContents,
			'allContents' => $allContents,
			'eventUnits' => $eventUnits,
			'allProperties' => $allProperties,
			'eventSports' => $eventSports,
			'sportsTreeData' => $sportsTreeData,
			'eventCompetitions' => $eventCompetitions,
			'allCompetitions' => $allCompetitions,
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

	public function actionAddSport($id)
	{
		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			$this->redirect(array('view', 'id' => $id));
			return;
		}

		$sportIds = Yii::app()->getRequest()->getPost('sport_ids', array());
		$successCount = 0;
		$errors = array();
		foreach ($sportIds as $sportId) {
			if ($sportId) {
				$result = EventSports::storeViaApi($id, $sportId);
				Yii::log('AddSport result: ' . print_r($result, true), CLogger::LEVEL_INFO, 'api.debug');
				if ($result['success']) {
					$successCount++;
				} else {
					$errorMsg = isset($result['error']) ? $result['error'] : 'Lỗi không xác định';
					$errorMsg .= ' (code: ' . (isset($result['code']) ? $result['code'] : 'N/A') . ')';
					$errors[] = $errorMsg;
				}
			}
		}
		if ($successCount > 0) {
			Yii::app()->user->setFlash('success', "Đã thêm {$successCount} môn thể thao.");
		}
		if (!empty($errors)) {
			Yii::app()->user->setFlash('error', implode('; ', $errors));
		}
		$this->redirect(array('view', 'id' => $id));
	}

	public function actionRemoveSport($id, $sportId)
	{
		if (Yii::app()->getRequest()->getIsPostRequest()) {
			$result = EventSports::deleteViaApi($sportId);
			if ($result['success']) {
				Yii::app()->user->setFlash('success', 'Xóa môn thể thao thành công.');
			} else {
				Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể xóa môn thể thao.');
			}
			$this->redirect(array('view', 'id' => $id));
		}
	}

	public function actionAddCompetition($id)
	{
		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			$this->redirect(array('view', 'id' => $id));
			return;
		}

		$competitionIds = Yii::app()->getRequest()->getPost('competition_ids', array());
		$successCount = 0;
		$errors = array();
		foreach ($competitionIds as $competitionId) {
			if ($competitionId) {
				$result = EventCompetitions::storeViaApi($id, $competitionId);
				if ($result['success']) {
					$successCount++;
				} else {
					$errorMsg = isset($result['error']) ? $result['error'] : 'Lỗi không xác định';
					$errors[] = $errorMsg;
				}
			}
		}
		if ($successCount > 0) {
			Yii::app()->user->setFlash('success', "Đã thêm {$successCount} cuộc thi nghiệp vụ.");
		}
		if (!empty($errors)) {
			Yii::app()->user->setFlash('error', implode('; ', $errors));
		}
		$this->redirect(array('view', 'id' => $id));
	}

	public function actionRemoveCompetition($id, $competitionId)
	{
		if (Yii::app()->getRequest()->getIsPostRequest()) {
			$result = EventCompetitions::deleteViaApi($competitionId);
			if ($result['success']) {
				Yii::app()->user->setFlash('success', 'Xóa cuộc thi nghiệp vụ thành công.');
			} else {
				Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể xóa cuộc thi nghiệp vụ.');
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
