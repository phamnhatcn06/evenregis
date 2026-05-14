<?php

class RegistrationsController extends AdminController
{
	public function actionView($id)
	{
		$model = $this->loadModelById($id);
		$registrationDetails = RegistrationDetails::getByRegistrationId($id);

		// Load related names nếu API không trả về
		if (empty($model->event_name) && $model->event_id) {
			$event = Events::fetchFromApi($model->event_id);
			$model->event_name = $event ? $event->name : '';
		}
		if (empty($model->property_name) && $model->property_id) {
			$property = Properties::fetchFromApi($model->property_id);
			$model->property_name = $property ? $property->name : '';
			$model->property_code = $property ? $property->code : '';
		}
		if (empty($model->relation_property_name) && $model->relation_property_id) {
			$relationProperty = Properties::fetchFromApi($model->relation_property_id);
			$model->relation_property_name = $relationProperty ? $relationProperty->name : '';
		}
		if (empty($model->period_name) && $model->period_id) {
			$period = RegistrationPeriods::fetchFromApi($model->period_id);
			$model->period_name = $period ? $period->name : '';
		}

		// Load attendees cho từng registration detail (nghiệp vụ)
		$detailAttendees = array();
		foreach ($registrationDetails as $detail) {
			$detailId = isset($detail['id']) ? $detail['id'] : null;
			$contentCode = isset($detail['content_code']) ? $detail['content_code'] : '';
			if ($detailId && $contentCode === 'competition') {
				$attendees = RegistrationDetailAttendees::getByDetailId($detailId);
				$detailAttendees[$detailId] = $attendees;
			}
		}

		// Load alliance request nếu có liên quân
		$allianceRequest = null;
		if ($model->relation_property_id && $model->event_id && $model->property_id) {
			$allianceRequest = AllianceRequests::findByRegistration(
				$model->event_id,
				$model->property_id,
				$model->relation_property_id
			);
		}

		$this->render('view', array(
			'model' => $model,
			'registrationDetails' => $registrationDetails,
			'detailAttendees' => $detailAttendees,
			'allianceRequest' => $allianceRequest,
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
			$model->status = Registrations::STATUS_DRAFT;	
			$ssoUser = AuthHandler::getUser();
			$model->submitted_by = isset($ssoUser['id']) ? $ssoUser['id'] : null;
			$existingDoc = isset($_POST['Registrations']['document']) ? $_POST['Registrations']['document'] : null;
			$uploadedFiles = $this->handleDocumentUpload($existingDoc);
			if ($uploadedFiles) {
				$model->document = $uploadedFiles;
			}

			if ($model->validate()) {

				$result = $model->storeViaApi();

				if ($result['success']) {
					$newId = isset($result['data']['id']) ? $result['data']['id'] : null;

					if ($model->relation_property_id && $model->event_id && $model->property_id) {
						$this->createAllianceRequest($model->event_id, $model->property_id, $model->relation_property_id);
					}

					Yii::app()->user->setFlash('success', 'Tạo phiếu đăng ký thành công.');
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

		// Lưu lại relation_property_id cũ để so sánh
		$oldRelationPropertyId = $model->relation_property_id;

		$user = AuthHandler::getUser();
		$userPropertyId = isset($user['property_id']) ? $user['property_id'] : null;
		$userPropertyCode = isset($user['property_code']) ? $user['property_code'] : null;
		$userRegionalId = isset($user['regional_id']) ? $user['regional_id'] : null;
		$isAdmin = ($userPropertyCode === '9999');

		$events = Events::getApiDataProvider(array('status' => 1), 100)->getData();

		// Load periods theo event_id hiện có
		$periods = array();
		if ($model->event_id) {
			$periodsData = RegistrationPeriods::getApiDataProvider(array(
				'event_id' => $model->event_id,
				'is_active' => 1,
			), 100)->getData();
			foreach ($periodsData as $p) {
				$pId = isset($p->id) ? $p->id : (isset($p['id']) ? $p['id'] : null);
				$pName = isset($p->name) ? $p->name : (isset($p['name']) ? $p['name'] : '');
				if ($pId) {
					$periods[$pId] = $pName;
				}
			}
		}

		// Load properties và relationProperties
		if ($isAdmin) {
			$properties = Properties::getApiDataProvider(array(), 500)->getData();
			$relationProperties = array();
			if ($model->property_id) {
				$property = Properties::fetchFromApi($model->property_id);
				if ($property && $property->region_id) {
					$relationProperties = Properties::getApiDataProvider(array('region_id' => $property->region_id), 500)->getData();
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
					// Xử lý alliance request khi relation_property_id thay đổi
					$newRelationPropertyId = $model->relation_property_id;
					if ($oldRelationPropertyId != $newRelationPropertyId) {
						// Xóa alliance request cũ nếu có
						if ($oldRelationPropertyId && $model->event_id && $model->property_id) {
							$this->deleteAllianceRequest($model->event_id, $model->property_id, $oldRelationPropertyId);
						}
						// Tạo alliance request mới nếu có chọn đơn vị liên quân mới
						if ($newRelationPropertyId && $model->event_id && $model->property_id) {
							$this->createAllianceRequest($model->event_id, $model->property_id, $newRelationPropertyId);
						}
					}

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
			$model->status = Registrations::STATUS_SUBMITTED;
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
			$model->status = Registrations::STATUS_APPROVED;
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
			$model->status = Registrations::STATUS_REJECTED;
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

	protected function deleteAllianceRequest($eventId, $requesterOrgId, $targetOrgId)
	{
		$existing = AllianceRequests::findByRegistration($eventId, $requesterOrgId, $targetOrgId);
		if ($existing && $existing->id) {
			$result = AllianceRequests::deleteViaApi($existing->id);
			if ($result['success']) {
				Yii::log("Deleted alliance request id={$existing->id} for event=$eventId, requester=$requesterOrgId, target=$targetOrgId", 'info', 'application.alliance');
			} else {
				Yii::log("Failed to delete alliance request: " . json_encode($result), 'error', 'application.alliance');
			}
		}
	}

	protected function createAllianceRequest($eventId, $requesterOrgId, $targetOrgId)
	{
		$existing = AllianceRequests::findByRegistration($eventId, $requesterOrgId, $targetOrgId);
		if ($existing) {
			Yii::log("Alliance request already exists for event=$eventId, requester=$requesterOrgId, target=$targetOrgId", 'info', 'application.alliance');
			return;
		}

		$ssoUser = AuthHandler::getUser();
		$alliance = new AllianceRequests;
		$alliance->event_id = $eventId;
		$alliance->requester_org_id = $requesterOrgId;
		$alliance->target_org_id = $targetOrgId;
		$alliance->requested_by = isset($ssoUser['email']) ? $ssoUser['email'] : null;

		$result = $alliance->storeViaApi();
		if (!$result['success']) {
			Yii::log("Failed to create alliance request: " . json_encode($result), 'error', 'application.alliance');
		} else {
			Yii::log("Created alliance request for event=$eventId, requester=$requesterOrgId, target=$targetOrgId", 'info', 'application.alliance');
		}
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

	public function actionGetEventContents($event_id)
	{
		$contents = EventContents::getByEventId($event_id);
		$result = array();
		foreach ($contents as $item) {
			$result[] = array(
				'id' => isset($item['content_id']) ? $item['content_id'] : $item['id'],
				'name' => isset($item['content_name']) ? $item['content_name'] : (isset($item['name']) ? $item['name'] : ''),
				'code' => isset($item['content_code']) ? $item['content_code'] : (isset($item['code']) ? $item['code'] : ''),
			);
		}

		header('Content-Type: application/json');
		echo CJSON::encode(array('success' => true, 'data' => $result));
		Yii::app()->end();
	}

	public function actionGetContentItems($event_id, $content_type)
	{
		$result = array();

		if ($content_type === 'sports') {
			$sports = EventSports::getByEventId($event_id);
			foreach ($sports as $item) {
				$result[] = array(
					'id' => isset($item['sport_id']) ? $item['sport_id'] : $item['id'],
					'name' => isset($item['sport_name']) ? $item['sport_name'] : (isset($item['name']) ? $item['name'] : ''),
					'parent_id' => isset($item['parent_id']) ? $item['parent_id'] : 0,
					'parent_name' => isset($item['parent_name']) ? $item['parent_name'] : '',
				);
			}
		} elseif ($content_type === 'competition') {
			$competitions = EventCompetitions::getByEventId($event_id);
			foreach ($competitions as $item) {
				$result[] = array(
					'id' => isset($item['competition_id']) ? $item['competition_id'] : $item['id'],
					'name' => isset($item['competition_name']) ? $item['competition_name'] : (isset($item['name']) ? $item['name'] : ''),
				);
			}
		}

		header('Content-Type: application/json');
		echo CJSON::encode(array('success' => true, 'data' => $result));
		Yii::app()->end();
	}

	public function actionAddDetail()
	{
		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
		}

		$registrationId = Yii::app()->getRequest()->getPost('registration_id');
		$contentId = Yii::app()->getRequest()->getPost('content_id');
		$contentType = Yii::app()->getRequest()->getPost('content_type');
		$itemId = Yii::app()->getRequest()->getPost('item_id');
		$quantity = Yii::app()->getRequest()->getPost('quantity', 1);
		$note = Yii::app()->getRequest()->getPost('note', '');

		$data = array(
			'registration_id' => $registrationId,
			'content_id' => $contentId,
			'quantity' => $quantity,
			'note' => $note,
		);

		if ($contentType === 'sports' && $itemId) {
			$data['sport_id'] = $itemId;
		} elseif ($contentType === 'competition' && $itemId) {
			$data['competition_id'] = $itemId;
		}

		$result = RegistrationDetails::storeViaApi($data);

		if ($result['success']) {
			Yii::app()->user->setFlash('success', 'Thêm nội dung đăng ký thành công.');
		} else {
			Yii::app()->user->setFlash('error', isset($result['error']) ? $result['error'] : 'Không thể thêm nội dung.');
		}

		$this->redirect(array('view', 'id' => $registrationId));
	}

	public function actionDeleteDetail($id, $registration_id)
	{
		if (Yii::app()->getRequest()->getIsPostRequest()) {
			$result = RegistrationDetails::deleteViaApi($id);

			if ($result['success']) {
				Yii::app()->user->setFlash('success', 'Xóa nội dung đăng ký thành công.');
			} else {
				Yii::app()->user->setFlash('error', isset($result['error']) ? $result['error'] : 'Không thể xóa.');
			}

			$this->redirect(array('view', 'id' => $registration_id));
		} else {
			throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
		}
	}

	public function actionGetOrganizations()
	{
		$user = AuthHandler::getUser();
		$userPropertyCode = isset($user['property_code']) ? $user['property_code'] : null;
		$userPropertyId = isset($user['property_id']) ? $user['property_id'] : null;
		$isAdmin = ($userPropertyCode === '9999');

		$result = array();

		if ($isAdmin) {
			$properties = Properties::getApiDataProvider(array(), 500)->getData();
			foreach ($properties as $p) {
				$result[] = array(
					'id' => isset($p['id']) ? $p['id'] : (isset($p->id) ? $p->id : null),
					'code' => isset($p['code']) ? $p['code'] : (isset($p->code) ? $p->code : ''),
					'name' => isset($p['name']) ? $p['name'] : (isset($p->name) ? $p->name : ''),
				);
			}
			usort($result, function ($a, $b) {
				return strcmp($a['code'], $b['code']);
			});
		} else {
			if ($userPropertyId) {
				$property = Properties::fetchFromApi($userPropertyId);
				if ($property) {
					$result[] = array(
						'id' => $property->id,
						'code' => $property->code,
						'name' => $property->name,
					);
				}
			}
		}

		header('Content-Type: application/json');
		echo CJSON::encode(array('success' => true, 'data' => $result));
		Yii::app()->end();
	}

	public function actionGetStaffByProperty($property_id)
	{
		$result = array();

		$property = Properties::fetchFromApi($property_id);
		if ($property && $property->code) {
			$staffs = Staffs::getApiDataProvider(array('property_code' => $property->code), 500)->getData();
			foreach ($staffs as $staff) {
				$id = isset($staff['id']) ? $staff['id'] : (isset($staff->id) ? $staff->id : null);
				$fullName = isset($staff['full_name']) ? $staff['full_name'] : (isset($staff->full_name) ? $staff->full_name : '');
				$positionName = isset($staff['position_name']) ? $staff['position_name'] : (isset($staff->position_name) ? $staff->position_name : '');
				$code = isset($staff['code']) ? $staff['code'] : (isset($staff->code) ? $staff->code : '');

				if (!$id) continue;

				$result[] = array(
					'id' => $id,
					'name' => $fullName,
					'position' => $positionName,
					'code' => $code,
					'display' => $code ? ($code . ' - ' . $fullName) : $fullName,
				);
			}
		}

		header('Content-Type: application/json');
		echo CJSON::encode(array('success' => true, 'data' => $result));
		Yii::app()->end();
	}

	public function actionGetCompetitionInfo($competition_id)
	{
		$competition = Competitions::fetchFromApi($competition_id);
		$result = array();

		if ($competition) {
			$result = array(
				'id' => $competition->id,
				'name' => $competition->name,
				'max_per_org' => $competition->max_per_org ? (int)$competition->max_per_org : 0,
			);
		}

		header('Content-Type: application/json');
		echo CJSON::encode(array('success' => true, 'data' => $result));
		Yii::app()->end();
	}

	public function actionAddCompetitionRegistration()
	{
		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
		}

		$registrationId = Yii::app()->getRequest()->getPost('registration_id');
		$contentId = Yii::app()->getRequest()->getPost('content_id');
		$competitionId = Yii::app()->getRequest()->getPost('competition_id');
		$propertyId = Yii::app()->getRequest()->getPost('property_id');
		$staffIds = Yii::app()->getRequest()->getPost('staff_ids', array());
		$note = Yii::app()->getRequest()->getPost('note', '');

		if (empty($staffIds) || !is_array($staffIds)) {
			Yii::app()->user->setFlash('error', 'Vui lòng chọn ít nhất một nhân viên.');
			$this->redirect(array('view', 'id' => $registrationId));
			return;
		}

		$detailData = array(
			'registration_id' => $registrationId,
			'content_id' => $contentId,
			'competition_id' => $competitionId,
			'quantity' => count($staffIds),
			'note' => $note,
		);

		$detailResult = RegistrationDetails::storeViaApi($detailData);

		if (!$detailResult['success']) {
			Yii::app()->user->setFlash('error', isset($detailResult['error']) ? $detailResult['error'] : 'Không thể tạo chi tiết đăng ký.');
			$this->redirect(array('view', 'id' => $registrationId));
			return;
		}

		$detailId = isset($detailResult['data']['id']) ? $detailResult['data']['id'] : null;

		if (!$detailId) {
			Yii::app()->user->setFlash('error', 'Không lấy được ID chi tiết đăng ký.');
			$this->redirect(array('view', 'id' => $registrationId));
			return;
		}

		$successCount = 0;
		$errorCount = 0;

		foreach ($staffIds as $staffId) {
			$attendeeData = array(
				'registration_detail_id' => $detailId,
				'staff_id' => $staffId,
				'status' => RegistrationDetailAttendees::STATUS_PENDING,
			);

			$result = RegistrationDetailAttendees::storeViaApi($attendeeData);

			if ($result['success']) {
				$successCount++;
			} else {
				$errorCount++;
			}
		}

		if ($successCount > 0) {
			Yii::app()->user->setFlash('success', "Đã đăng ký thành công {$successCount} người tham dự thi nghiệp vụ.");
		}
		if ($errorCount > 0) {
			Yii::app()->user->setFlash('warning', "Có {$errorCount} người không đăng ký được.");
		}

		$this->redirect(array('view', 'id' => $registrationId));
	}
}
