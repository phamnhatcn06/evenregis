<?php

class RegistrationsController extends AdminController
{
	public function actionView($id)
	{
		$model = $this->loadModelById($id);
		$registrationDetails = RegistrationDetails::getByRegistrationId($id);

		$this->render('view', array(
			'model' => $model,
			'registrationDetails' => $registrationDetails,
		));
	}

	public function actionCreate()
	{
		$model = new Registrations;

		$user = AuthHandler::getUser();
		$userPropertyId = isset($user['property_id']) ? $user['property_id'] : null;
		$userPropertyCode = isset($user['property_code']) ? $user['property_code'] : null;
		$userRegionalId = isset($user['regional_id']) ? $user['regional_id'] : null;
		$isAdmin = ($userPropertyCode === '9999');

		$events = Events::getApiDataProvider(array('status' => 1), 100)->getData();
		$periods = RegistrationPeriods::getActiveList();

		if ($isAdmin) {
			$properties = Properties::getApiDataProvider(array(), 100)->getData();
			$relationProperties = $properties;
		} else {
			$properties = $userPropertyId ? Properties::getApiDataProvider(array('id' => $userPropertyId), 100)->getData() : array();
			$relationProperties = $userRegionalId ? Properties::getApiDataProvider(array('region_id' => $userRegionalId), 100)->getData() : array();
		}

		if ($userPropertyId && !$model->property_id) {
			$model->property_id = $userPropertyId;
		}

		if (isset($_POST['Registrations'])) {
			$model->setAttributes($_POST['Registrations']);
			$model->status = 'draft';
			$model->submitted_by = Yii::app()->user->id ?: 1;

			$existingDoc = isset($_POST['Registrations']['document']) ? $_POST['Registrations']['document'] : null;
			$uploadedFiles = $this->handleDocumentUpload($existingDoc);
			if ($uploadedFiles) {
				$model->document = $uploadedFiles;
			}

			if ($model->validate()) {
				$result = $model->storeViaApi();

				if ($result['success']) {
					Yii::app()->user->setFlash('success', 'Tạo phiếu đăng ký thành công.');
					$newId = isset($result['data']['id']) ? $result['data']['id'] : null;
					$this->redirect($newId ? array('view', 'id' => $newId) : array('admin'));
				} else {
					$errorMsg = isset($result['error']) ? $result['error'] : 'Không thể tạo phiếu đăng ký.';
					$model->addError('property_id', $errorMsg);
				}
			}
		}

		$this->render('create', array(
			'model' => $model,
			'events' => $events,
			'periods' => $periods,
			'properties' => $properties,
			'relationProperties' => $relationProperties,
			'isAdmin' => $isAdmin,
		));
	}

	public function actionUpdate($id)
	{
		$model = $this->loadModelById($id);

		$user = AuthHandler::getUser();
		$userPropertyId = isset($user['property_id']) ? $user['property_id'] : null;
		$userPropertyCode = isset($user['property_code']) ? $user['property_code'] : null;
		$userRegionalId = isset($user['regional_id']) ? $user['regional_id'] : null;
		$isAdmin = ($userPropertyCode === '9999');

		$events = Events::getApiDataProvider(array('status' => 1), 100)->getData();
		$periods = RegistrationPeriods::getActiveList();

		if ($isAdmin) {
			$properties = Properties::getApiDataProvider(array(), 500)->getData();
			$relationProperties = array();
			if ($model->property_id) {
				$property = Properties::fetchFromApi($model->property_id);
				if ($property && $property->regional_id) {
					$relationProperties = Properties::getApiDataProvider(array('regional_id' => $property->regional_id), 500)->getData();
				}
			}
		} else {
			$properties = $userPropertyId ? Properties::getApiDataProvider(array('id' => $userPropertyId), 100)->getData() : array();
			$relationProperties = $userRegionalId ? Properties::getApiDataProvider(array('region_id' => $userRegionalId), 500)->getData() : array();
		}

		if (isset($_POST['Registrations'])) {
			$model->setAttributes($_POST['Registrations']);

			$existingDoc = isset($_POST['Registrations']['document']) ? $_POST['Registrations']['document'] : null;
			$uploadedFiles = $this->handleDocumentUpload($existingDoc);
			if ($uploadedFiles) {
				$model->document = $uploadedFiles;
			}

			if ($model->validate()) {
				$result = $model->updateViaApi();

				if ($result['success']) {
					Yii::app()->user->setFlash('success', 'Cập nhật phiếu đăng ký thành công.');
					$this->redirect(array('view', 'id' => $id));
				} else {
					$model->addError('property_id', isset($result['error']) ? $result['error'] : 'Không thể cập nhật.');
				}
			}
		}

		$this->render('update', array(
			'model' => $model,
			'events' => $events,
			'periods' => $periods,
			'properties' => $properties,
			'relationProperties' => $relationProperties,
			'isAdmin' => $isAdmin,
		));
	}

	public function actionDelete($id)
	{
		if (Yii::app()->getRequest()->getIsPostRequest()) {
			$result = Registrations::deleteViaApi($id);

			if ($result['success']) {
				Yii::app()->user->setFlash('success', 'Xóa phiếu đăng ký thành công.');
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

	public function actionSubmit($id)
	{
		if (Yii::app()->getRequest()->getIsPostRequest()) {
			$model = $this->loadModelById($id);
			$model->status = 'submitted';
			$model->submitted_at = time();
			$result = $model->updateViaApi();

			if ($result['success']) {
				Yii::app()->user->setFlash('success', 'Đã nộp phiếu đăng ký.');
			} else {
				Yii::app()->user->setFlash('error', 'Không thể nộp phiếu đăng ký.');
			}
			$this->redirect(array('view', 'id' => $id));
		}
	}

	public function actionApprove($id)
	{
		if (Yii::app()->getRequest()->getIsPostRequest()) {
			$model = $this->loadModelById($id);
			$model->status = 'approved';
			$model->reviewed_at = time();
			$result = $model->updateViaApi();

			if ($result['success']) {
				Yii::app()->user->setFlash('success', 'Đã phê duyệt phiếu đăng ký.');
			} else {
				Yii::app()->user->setFlash('error', 'Không thể phê duyệt.');
			}
			$this->redirect(array('view', 'id' => $id));
		}
	}

	public function actionReject($id)
	{
		if (Yii::app()->getRequest()->getIsPostRequest()) {
			$model = $this->loadModelById($id);
			$model->status = 'rejected';
			$model->reviewed_at = time();
			$model->rejection_reason = Yii::app()->getRequest()->getPost('rejection_reason', '');
			$result = $model->updateViaApi();

			if ($result['success']) {
				Yii::app()->user->setFlash('success', 'Đã từ chối phiếu đăng ký.');
			} else {
				Yii::app()->user->setFlash('error', 'Không thể từ chối.');
			}
			$this->redirect(array('view', 'id' => $id));
		}
	}

	protected function loadModelById($id)
	{
		$model = Registrations::fetchFromApi($id);
		if ($model === null) {
			throw new CHttpException(404, 'Không tìm thấy phiếu đăng ký.');
		}
		return $model;
	}

	protected function handleDocumentUpload($existingDocument = null)
	{
		$uploadedFiles = array();

		if ($existingDocument) {
			$existing = json_decode($existingDocument, true);
			if (is_array($existing)) {
				$uploadedFiles = $existing;
			} elseif ($existingDocument) {
				$uploadedFiles[] = $existingDocument;
			}
		}

		if (!isset($_FILES['document_files']) || !is_array($_FILES['document_files']['name'])) {
			return $uploadedFiles ? json_encode($uploadedFiles) : null;
		}

		$allowedTypes = array('pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png');
		$maxSize = 5 * 1024 * 1024;

		$uploadDir = Yii::getPathOfAlias('webroot') . '/uploads/registrations/';
		if (!is_dir($uploadDir)) {
			mkdir($uploadDir, 0755, true);
		}

		$fileCount = count($_FILES['document_files']['name']);
		for ($i = 0; $i < $fileCount; $i++) {
			if ($_FILES['document_files']['error'][$i] !== UPLOAD_ERR_OK) {
				continue;
			}

			$ext = strtolower(pathinfo($_FILES['document_files']['name'][$i], PATHINFO_EXTENSION));
			if (!in_array($ext, $allowedTypes)) {
				continue;
			}

			if ($_FILES['document_files']['size'][$i] > $maxSize) {
				continue;
			}

			$filename = date('Ymd_His') . '_' . uniqid() . '.' . $ext;
			$filepath = $uploadDir . $filename;

			if (move_uploaded_file($_FILES['document_files']['tmp_name'][$i], $filepath)) {
				$uploadedFiles[] = Yii::app()->baseUrl . '/uploads/registrations/' . $filename;
			}
		}

		return $uploadedFiles ? json_encode($uploadedFiles) : null;
	}

	public function actionGetRelationProperties($property_id)
	{
		$property = Properties::fetchFromApi($property_id);
		$result = array();

		if ($property && $property->region_id) {
			$properties = Properties::getApiDataProvider(array('region_id' => $property->region_id), 500)->getData();
			foreach ($properties as $p) {
				$pId = isset($p['id']) ? $p['id'] : (isset($p->id) ? $p->id : null);
				if ($pId && $pId != $property_id) {
					$result[] = array(
						'id' => $pId,
						'code' => isset($p['code']) ? $p['code'] : '',
						'name' => isset($p['name']) ? $p['name'] : '',
					);
				}
			}
			usort($result, function ($a, $b) {
				return strcmp($a['code'], $b['code']);
			});
		}

		header('Content-Type: application/json');
		echo CJSON::encode(array('success' => true, 'data' => $result));
		Yii::app()->end();
	}

	public function actionAdmin()
	{
		$model = new Registrations('search');
		$model->unsetAttributes();

		if (isset($_GET['Registrations'])) {
			$model->setAttributes($_GET['Registrations']);
		}

		$params = array();
		foreach ($model->attributes as $key => $value) {
			if ($value !== null && $value !== '') {
				$params[$key] = $value;
			}
		}

		$dataProvider = Registrations::getApiDataProvider($params);

		$this->render('admin', array(
			'model' => $model,
			'dataProvider' => $dataProvider,
		));
	}
}
