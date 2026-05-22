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
		if ($model->property_id) {
			$property = Properties::fetchFromApi($model->property_id);
			if ($property) {
				if (empty($model->property_name)) {
					$model->property_name = $property->name;
				}
				$model->property_code = $property->prefix ? $property->prefix : $property->code;
			}
		}
		if (empty($model->relation_property_name) && $model->relation_property_id) {
			$relationProperty = Properties::fetchFromApi($model->relation_property_id);
			$model->relation_property_name = $relationProperty ? $relationProperty->name : '';
		}
		if (empty($model->period_name) && $model->period_id) {
			$period = RegistrationPeriods::fetchFromApi($model->period_id);
			$model->period_name = $period ? $period->name : '';
		}

		// Load competition registrations từ bảng competition_registrations
		$competitionRegistrations = array();
		$compRegsData = CompetitionRegistrations::getApiDataProvider(array('registration_id' => $id), 200)->getData();

		// Load tất cả attendees của registration để lấy thông tin chi tiết
		$attendeesMap = array();
		$attendeesData = Attendees::getByRegistrationId($id);
		foreach ($attendeesData as $att) {
			$attId = isset($att['id']) ? $att['id'] : null;
			if ($attId) {
				$attendeesMap[$attId] = $att;
			}
		}

		foreach ($compRegsData as $reg) {
			$compId = isset($reg->competition_id) ? $reg->competition_id : (isset($reg['competition_id']) ? $reg['competition_id'] : null);
			if (!$compId) continue;

			if (!isset($competitionRegistrations[$compId])) {
				$competitionRegistrations[$compId] = array(
					'competition_id' => $compId,
					'competition_name' => isset($reg->competition_name) ? $reg->competition_name : (isset($reg['competition_name']) ? $reg['competition_name'] : ''),
					'attendees' => array(),
				);
			}

			// Lấy thông tin attendee từ map
			$attendeeId = isset($reg->attendee_id) ? $reg->attendee_id : (isset($reg['attendee_id']) ? $reg['attendee_id'] : null);
			$attendeeInfo = isset($attendeesMap[$attendeeId]) ? $attendeesMap[$attendeeId] : array();

			$attendeeName = isset($attendeeInfo['full_name']) ? $attendeeInfo['full_name'] : '';
			$positionName = isset($attendeeInfo['position_name']) ? $attendeeInfo['position_name'] : '';
			$divisionName = isset($attendeeInfo['division_name']) ? $attendeeInfo['division_name'] : '';

			$competitionRegistrations[$compId]['attendees'][] = array(
				'id' => isset($reg->id) ? $reg->id : (isset($reg['id']) ? $reg['id'] : null),
				'attendee_id' => $attendeeId,
				'attendee_name' => $attendeeName,
				'position_name' => $positionName,
				'division_name' => $divisionName,
				'status' => isset($reg->status) ? $reg->status : (isset($reg['status']) ? $reg['status'] : 0),
			);
		}

		// Load tên cuộc thi nếu chưa có
		foreach ($competitionRegistrations as $compId => &$compData) {
			if (empty($compData['competition_name'])) {
				$comp = Competitions::fetchFromApi($compId);
				$compData['competition_name'] = $comp ? $comp->name : '';
			}
		}
		unset($compData);

        // Load Sport Teams cho đơn vị
        $sportTeams = array();
        $sportTeamMembers = array();
        if ($model->event_id && $model->property_id) {
            $teamsData = SportTeams::getApiDataProvider(array('event_id' => $model->event_id, 'property_id' => $model->property_id), 100)->getData();
            foreach ($teamsData as $team) {
                $teamId = isset($team->id) ? $team->id : (isset($team['id']) ? $team['id'] : null);
                if ($teamId) {
                    // Fetch sport name if not available
                    if (empty($team->sport_name) && $team->sport_id) {
                        $sport = Sports::fetchFromApi($team->sport_id);
                        $team->sport_name = $sport ? $sport->name : '';
                    }
                    $sportTeams[] = $team;
                    $membersData = SportTeamMembers::getApiDataProvider(array('sport_team_id' => $teamId), 100)->getData();

                    // Enrich member info from attendees map
                    $enrichedMembers = array();
                    foreach ($membersData as $member) {
                        $attId = isset($member->attendee_id) ? $member->attendee_id : (isset($member['attendee_id']) ? $member['attendee_id'] : null);
                        $attInfo = isset($attendeesMap[$attId]) ? $attendeesMap[$attId] : array();

                        $memberArr = is_object($member) ? get_object_vars($member) : $member;
                        if (empty($memberArr['attendee_name']) && !empty($attInfo['full_name'])) {
                            $memberArr['attendee_name'] = $attInfo['full_name'];
                        }
                        if (empty($memberArr['position_name']) && !empty($attInfo['position_name'])) {
                            $memberArr['position_name'] = $attInfo['position_name'];
                        }
                        if (empty($memberArr['division_name']) && !empty($attInfo['division_name'])) {
                            $memberArr['division_name'] = $attInfo['division_name'];
                        }
                        $enrichedMembers[] = $memberArr;
                    }
                    $sportTeamMembers[$teamId] = $enrichedMembers;
                }
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

		// Load Beauty Contestants (Miss) cho registration
		$beautyContestants = array();
		if ($model->event_id) {
			$attendeeIds = array_keys($attendeesMap);
			if (!empty($attendeeIds)) {
				$contests = BeautyContests::getApiDataProvider(array('event_id' => $model->event_id), 100)->getData();
				foreach ($contests as $contest) {
					$contestId = isset($contest->id) ? $contest->id : (isset($contest['id']) ? $contest['id'] : null);
					$contestName = isset($contest->name) ? $contest->name : (isset($contest['name']) ? $contest['name'] : '');
					if (!$contestId) continue;

					$contestants = BeautyContestants::getApiDataProvider(array('contest_id' => $contestId), 500)->getData();
					foreach ($contestants as $c) {
						$attId = isset($c->attendee_id) ? $c->attendee_id : (isset($c['attendee_id']) ? $c['attendee_id'] : null);
						if ($attId && in_array($attId, $attendeeIds)) {
							if (!isset($beautyContestants[$contestId])) {
								$beautyContestants[$contestId] = array(
									'contest_id' => $contestId,
									'contest_name' => $contestName,
									'contestants' => array(),
								);
							}
							$attInfo = isset($attendeesMap[$attId]) ? $attendeesMap[$attId] : array();
							$beautyContestants[$contestId]['contestants'][] = array(
								'id' => isset($c->id) ? $c->id : (isset($c['id']) ? $c['id'] : null),
								'attendee_id' => $attId,
								'attendee_name' => isset($attInfo['full_name']) ? $attInfo['full_name'] : '',
								'candidate_number' => isset($c->candidate_number) ? $c->candidate_number : (isset($c['candidate_number']) ? $c['candidate_number'] : ''),
								'height_cm' => isset($c->height_cm) ? $c->height_cm : (isset($c['height_cm']) ? $c['height_cm'] : null),
								'weight_kg' => isset($c->weight_kg) ? $c->weight_kg : (isset($c['weight_kg']) ? $c['weight_kg'] : null),
								'measurements' => isset($c->measurements) ? $c->measurements : (isset($c['measurements']) ? $c['measurements'] : ''),
								'talent' => isset($c->talent) ? $c->talent : (isset($c['talent']) ? $c['talent'] : ''),
								'bio' => isset($c->bio) ? $c->bio : (isset($c['bio']) ? $c['bio'] : ''),
								'status' => isset($c->status) ? $c->status : (isset($c['status']) ? $c['status'] : 0),
							);
						}
					}
				}
			}
		}

		$this->render('view', array(
			'model' => $model,
			'registrationDetails' => $registrationDetails,
			'competitionRegistrations' => $competitionRegistrations,
			'allianceRequest' => $allianceRequest,
            'sportTeams' => $sportTeams,
            'sportTeamMembers' => $sportTeamMembers,
			'beautyContestants' => $beautyContestants,
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

	protected function createAllianceRequest($eventId, $requesterOrgId, $targetOrgId, $eventContentId = null)
	{
		$ssoUser = AuthHandler::getUser();
		$alliance = new AllianceRequests;
		$alliance->event_id = $eventId;
		$alliance->requester_org_id = $requesterOrgId;
		$alliance->target_org_id = $targetOrgId;
		$alliance->requested_by = isset($ssoUser['email']) ? $ssoUser['email'] : null;
		$alliance->event_content_id = $eventContentId ? $eventContentId : null;

		Yii::log("createAllianceRequest called with eventContentId=" . var_export($eventContentId, true), 'info', 'application.alliance');

		$result = $alliance->storeViaApi();
		Yii::log("storeViaApi result: " . json_encode($result), 'info', 'application.alliance');

		if (!$result['success']) {
			Yii::log("Failed to create alliance request: " . json_encode($result), 'error', 'application.alliance');
		} else {
			Yii::log("Created alliance request for event=$eventId, requester=$requesterOrgId, target=$targetOrgId, event_content_id=$eventContentId", 'info', 'application.alliance');
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
					$prefix = isset($p['prefix']) ? $p['prefix'] : (isset($p->prefix) ? $p->prefix : '');
					$result[] = array(
						'id' => $pId,
						'code' => $prefix ? $prefix : (isset($p['code']) ? $p['code'] : ''),
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

	public function actionGetAllianceProperties($registration_id)
	{
		$eventContentId = isset($_GET['event_content_id']) ? $_GET['event_content_id'] : null;
		$registration = Registrations::fetchFromApi($registration_id);
		$result = array();

		if ($registration && $registration->property_id) {
			$property = Properties::fetchFromApi($registration->property_id);
			if ($property && $property->region_id) {
                // Get existing alliance requests filtered by event_content_id
                $params = array(
                    'event_id' => $registration->event_id,
                    'requester_org_id' => $registration->property_id,
                );
                if ($eventContentId) {
                    $params['event_content_id'] = $eventContentId;
                }
                $existingRequests = AllianceRequests::getApiDataProvider($params, 100)->getData();
                $existingTargetIds = array();
                foreach ($existingRequests as $req) {
                    $targetId = isset($req['target_org_id']) ? $req['target_org_id'] : (isset($req->target_org_id) ? $req->target_org_id : null);
                    if ($targetId) {
                        $existingTargetIds[] = $targetId;
                    }
                }

				$properties = Properties::getApiDataProvider(array('region_id' => $property->region_id), 500)->getData();
				foreach ($properties as $p) {
					$pId = isset($p['id']) ? $p['id'] : (isset($p->id) ? $p->id : null);
					// Loại bỏ đơn vị hiện tại
					if ($pId == $registration->property_id) {
						continue;
					}
					$prefix = isset($p['prefix']) ? $p['prefix'] : (isset($p->prefix) ? $p->prefix : '');
					$result[] = array(
						'id' => $pId,
						'code' => $prefix ? $prefix : (isset($p['code']) ? $p['code'] : ''),
						'name' => isset($p['name']) ? $p['name'] : '',
                        'is_selected' => in_array($pId, $existingTargetIds) ? 1 : 0,
					);
				}
				usort($result, function ($a, $b) {
					return strcmp($a['code'], $b['code']);
				});
			}
		}

		header('Content-Type: application/json');
		echo CJSON::encode(array('success' => true, 'data' => $result));
		Yii::app()->end();
	}

	public function actionSaveAllianceProperties()
	{
		if (!Yii::app()->request->isPostRequest) {
			throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
		}

		$registrationId = Yii::app()->request->getPost('registration_id');
		$targetOrgIds = Yii::app()->request->getPost('target_org_ids', array());
		$eventContentId = Yii::app()->request->getPost('event_content_id');

		$model = Registrations::fetchFromApi($registrationId);
		if (!$model || !$model->event_id || !$model->property_id) {
			header('Content-Type: application/json');
			echo CJSON::encode(array('success' => false, 'error' => 'Phiếu đăng ký không hợp lệ.'));
			Yii::app()->end();
		}

		$eventId = $model->event_id;
		$requesterOrgId = $model->property_id;

		// Get existing alliance requests filtered by event_content_id
		$params = array(
			'event_id' => $eventId,
			'requester_org_id' => $requesterOrgId,
		);
		if ($eventContentId) {
			$params['event_content_id'] = $eventContentId;
		}
		$existingRequests = AllianceRequests::getApiDataProvider($params, 100)->getData();

		$existingTargetIds = array();
		foreach ($existingRequests as $req) {
			$reqId = isset($req['id']) ? $req['id'] : (isset($req->id) ? $req->id : null);
			$targetId = isset($req['target_org_id']) ? $req['target_org_id'] : (isset($req->target_org_id) ? $req->target_org_id : null);

			if ($targetId) {
				$existingTargetIds[] = $targetId;
				// If it's unchecked, we delete the alliance request
				if (!in_array($targetId, $targetOrgIds)) {
					AllianceRequests::deleteViaApi($reqId);
				}
			}
		}

		// Add new ones
		if (!empty($targetOrgIds)) {
			foreach ($targetOrgIds as $targetId) {
				if (!in_array($targetId, $existingTargetIds)) {
					$this->createAllianceRequest($eventId, $requesterOrgId, $targetId, $eventContentId);
				}
			}
		}

		header('Content-Type: application/json');
		echo CJSON::encode(array('success' => true));
		Yii::app()->end();
	}

	public function actionGetSportAttendees($registration_id)
	{
		$result = array();

		// Lấy attendees từ registration hiện tại có role "Thi đấu thể thao"
		$attendees = Attendees::getByRegistrationId($registration_id);
		foreach ($attendees as $att) {
			$roleName = isset($att['role_name']) ? $att['role_name'] : '';
			// Kiểm tra role có chứa "thể thao" hoặc "thi đấu"
			if (stripos($roleName, 'thể thao') !== false || stripos($roleName, 'thi đấu') !== false) {
				$result[] = array(
					'id' => $att['id'],
					'full_name' => isset($att['full_name']) ? $att['full_name'] : '',
					'position' => isset($att['position']) ? $att['position'] : '',
					'department_name' => isset($att['department_name']) ? $att['department_name'] : '',
				);
			}
		}

		header('Content-Type: application/json');
		echo CJSON::encode(array('success' => true, 'data' => $result));
		Yii::app()->end();
	}

	public function actionAddSportRegistration()
	{
		if (!Yii::app()->request->isPostRequest) {
			throw new CHttpException(400, 'Bad Request');
		}
		$isAjax = Yii::app()->request->isAjaxRequest;
		$registrationId = Yii::app()->request->getPost('registration_id');
		$sportId = Yii::app()->request->getPost('sport_id');
		$alliancePropertyIds = Yii::app()->request->getPost('alliance_property_ids', array());
		$teamName = Yii::app()->request->getPost('team_name');
		$note = Yii::app()->request->getPost('note');
		$attendeeIds = Yii::app()->request->getPost('attendee_ids', array());
		$attendeeNames = Yii::app()->request->getPost('attendee_names', array());
		$contentId = Yii::app()->request->getPost('content_id');
	
		if (!$registrationId || !$sportId || empty($attendeeIds)) {
			if ($isAjax) {
				echo CJSON::encode(array('success' => false, 'error' => 'Thiếu thông tin bắt buộc.'));
				Yii::app()->end();
			}
			Yii::app()->user->setFlash('error', 'Thiếu thông tin bắt buộc.');
			$this->redirect(array('view', 'id' => $registrationId));
			return;
		}

		$ssoUser = AuthHandler::getUser();
		$createdBy = isset($ssoUser['id']) ? $ssoUser['id'] : null;

        $registration = Registrations::fetchFromApi($registrationId);
        if (!$registration) {
			if ($isAjax) {
				echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy phiếu đăng ký.'));
				Yii::app()->end();
			}
            Yii::app()->user->setFlash('error', 'Không tìm thấy phiếu đăng ký.');
			$this->redirect(array('admin'));
			return;
        }

        // Tạo SportTeam
        $teamModel = new SportTeams();
        $teamModel->event_id = $registration->event_id;
        $teamModel->sport_id = $sportId;
        $teamModel->property_id = $registration->property_id;
        $teamModel->name = $teamName ? $teamName : 'Team';
        $teamModel->code = $teamName ? $teamName : 'TEAM';
        // Set public custom property if needed
        $teamModel->team_name = $teamName;
        $teamModel->is_alliance = empty($alliancePropertyIds) ? 0 : 1;
        $teamModel->alliance_property_ids = $alliancePropertyIds;
        $teamModel->status = SportTeams::STATUS_CONFIRMED;

        $teamResult = $teamModel->storeViaApi();
        if ($teamResult['success']) {
            $teamId = isset($teamResult['data']['data']['id']) ? $teamResult['data']['data']['id'] : (isset($teamResult['data']['id']) ? $teamResult['data']['id'] : null);
            if ($teamId) {
                // Tạo SportTeamMembers
                foreach ($attendeeIds as $idx => $attId) {
                    $member = new SportTeamMembers();
                    $member->sport_team_id = $teamId;
                    $member->attendee_id = $attId;
                    $member->name = isset($attendeeNames[$idx]) ? $attendeeNames[$idx] : '';
                    $member->storeViaApi();
                }
            }
			if ($isAjax) {
				echo CJSON::encode(array('success' => true, 'message' => 'Đăng ký thể thao thành công.', 'team_id' => $teamId));
				Yii::app()->end();
			}
            Yii::app()->user->setFlash('success', 'Đăng ký thể thao thành công.');
        } else {
			if ($isAjax) {
				echo CJSON::encode(array('success' => false, 'error' => isset($teamResult['error']) ? $teamResult['error'] : 'Không thể tạo đội thi đấu.'));
				Yii::app()->end();
			}
            Yii::app()->user->setFlash('error', isset($teamResult['error']) ? $teamResult['error'] : 'Không thể tạo đội thi đấu.');
            $this->redirect(array('view', 'id' => $registrationId));
            return;
        }

		// Không tạo RegistrationDetails nữa, vì môn thể thao sẽ được quản lý bởi SportTeams
		if (!$isAjax) {
			$this->redirect(array('view', 'id' => $registrationId));
		}
	}

    public function actionDeleteSportTeam($id, $registration_id)
    {
        if (Yii::app()->getRequest()->getIsPostRequest()) {
            $result = SportTeams::deleteViaApi($id);

            if ($result['success']) {
                Yii::app()->user->setFlash('success', 'Xóa đội thể thao thành công.');
            } else {
                Yii::app()->user->setFlash('error', isset($result['error']) ? $result['error'] : 'Không thể xóa đội.');
            }

            $this->redirect(array('view', 'id' => $registration_id));
        } else {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }
    }

    public function actionGetSportTeamDetail($id)
    {
        $team = SportTeams::fetchFromApi($id);
        if (!$team) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy đội.'));
            Yii::app()->end();
        }

        // Fetch sport name if not available
        $sportName = $team->sport_name;
        if (empty($sportName) && $team->sport_id) {
            $sport = Sports::fetchFromApi($team->sport_id);
            $sportName = $sport ? $sport->name : '';
        }

        $members = SportTeamMembers::getApiDataProvider(array('sport_team_id' => $id), 100)->getData();
        $membersArr = array();
        foreach ($members as $m) {
            $membersArr[] = array(
                'id' => $m->id,
                'attendee_id' => $m->attendee_id,
                'name' => $m->name,
                'attendee_name' => $m->attendee_name,
            );
        }

        echo CJSON::encode(array(
            'success' => true,
            'data' => array(
                'team' => array(
                    'id' => $team->id,
                    'sport_id' => $team->sport_id,
                    'sport_name' => $sportName,
                    'team_name' => $team->team_name,
                    'name' => $team->name,
                    'is_alliance' => $team->is_alliance,
                ),
                'members' => $membersArr,
            ),
        ));
        Yii::app()->end();
    }

    public function actionUpdateSportTeam()
    {
        if (!Yii::app()->request->isPostRequest) {
            throw new CHttpException(400, 'Bad Request');
        }

        $teamId = Yii::app()->request->getPost('team_id');
        $teamName = Yii::app()->request->getPost('team_name');
        $attendeeIds = Yii::app()->request->getPost('attendee_ids', array());
        $attendeeNames = Yii::app()->request->getPost('attendee_names', array());

        if (!$teamId) {
            echo CJSON::encode(array('success' => false, 'error' => 'Thiếu team_id.'));
            Yii::app()->end();
        }

        // Update team name
        $team = SportTeams::fetchFromApi($teamId);
        if ($team) {
            $team->team_name = $teamName;
            $team->name = $teamName;
            $team->updateViaApi();
        }

        // Delete old members
        $oldMembers = SportTeamMembers::getApiDataProvider(array('sport_team_id' => $teamId), 100)->getData();
        foreach ($oldMembers as $m) {
            SportTeamMembers::deleteViaApi($m->id);
        }

        // Create new members
        foreach ($attendeeIds as $idx => $attId) {
            $member = new SportTeamMembers();
            $member->sport_team_id = $teamId;
            $member->attendee_id = $attId;
            $member->code = 'T' . $teamId . '-A' . $attId;
            $member->name = isset($attendeeNames[$idx]) ? $attendeeNames[$idx] : '';
            $member->storeViaApi();
        }

        echo CJSON::encode(array('success' => true, 'message' => 'Cập nhật đội thành công.'));
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
		$registrationId = Yii::app()->request->getParam('registration_id');

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

			// Lấy số lượng đã đăng ký cho từng cuộc thi của registration này
			$registeredCounts = array();
			if ($registrationId) {
				$compRegs = CompetitionRegistrations::getByRegistrationId($registrationId);
				foreach ($compRegs as $reg) {
					$compId = isset($reg['competition_id']) ? $reg['competition_id'] : (isset($reg->competition_id) ? $reg->competition_id : null);
					if ($compId) {
						if (!isset($registeredCounts[$compId])) {
							$registeredCounts[$compId] = 0;
						}
						$registeredCounts[$compId]++;
					}
				}
			}

			foreach ($competitions as $item) {
				$compId = isset($item['competition_id']) ? $item['competition_id'] : $item['id'];
				$currentCount = isset($registeredCounts[$compId]) ? $registeredCounts[$compId] : 0;

				// Lấy max_per_org từ Competitions model
				$maxPerOrg = 0;
				if (isset($item['max_per_org'])) {
					$maxPerOrg = (int)$item['max_per_org'];
				} else {
					$competition = Competitions::fetchFromApi($compId);
					if ($competition) {
						$maxPerOrg = $competition->max_per_org ? (int)$competition->max_per_org : 0;
					}
				}

				// Bỏ qua nếu đã đăng ký đủ số lượng (max_per_org > 0 và đã đạt giới hạn)
				if ($maxPerOrg > 0 && $currentCount >= $maxPerOrg) {
					continue;
				}

				$result[] = array(
					'id' => $compId,
					'name' => isset($item['competition_name']) ? $item['competition_name'] : (isset($item['name']) ? $item['name'] : ''),
				);
			}
		} elseif ($content_type === 'miss') {
			$contests = BeautyContests::getApiDataProvider(array('event_id' => $event_id), 100)->getData();
			foreach ($contests as $item) {
				$id = isset($item['id']) ? $item['id'] : (isset($item->id) ? $item->id : null);
				$name = isset($item['name']) ? $item['name'] : (isset($item->name) ? $item->name : '');
				if ($id) {
					$result[] = array('id' => $id, 'name' => $name);
				}
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
		$competitionId = isset($_GET['competition_id']) ? $_GET['competition_id'] : null;

		$allowedDepartments = array();
		if ($competitionId) {
			$competition = Competitions::fetchFromApi($competitionId);
			if ($competition) {
				$allowedDepartments = $competition->getAllowedDepartments();
			}
		}

		$property = Properties::fetchFromApi($property_id);
		if ($property && $property->code) {
			$staffs = Staffs::getListBeforeJune2026($property->code);
			foreach ($staffs as $staff) {
				$id = isset($staff['id']) ? $staff['id'] : (isset($staff->id) ? $staff->id : null);
				$fullName = isset($staff['full_name']) ? $staff['full_name'] : (isset($staff->full_name) ? $staff->full_name : '');
				$positionName = isset($staff['position_name']) ? $staff['position_name'] : (isset($staff->position_name) ? $staff->position_name : '');
				$divisionName = isset($staff['division_name']) ? $staff['division_name'] : (isset($staff->division_name) ? $staff->division_name : '');
				$code = isset($staff['code']) ? $staff['code'] : (isset($staff->code) ? $staff->code : '');
				$startDate = isset($staff['start_date']) ? $staff['start_date'] : (isset($staff->start_date) ? $staff->start_date : '');
				$departmentCode = isset($staff['division_code']) ? $staff['division_code'] : (isset($staff->division_code) ? $staff->division_code : '');

				if (!$id) continue;

				if (!empty($allowedDepartments) && !in_array($departmentCode, $allowedDepartments)) {
					continue;
				}

				$result[] = array(
					'id' => $id,
					'name' => $fullName,
					'position' => $positionName,
					'department_name' => $divisionName,
					'code' => $code,
					'display' => $code ? ($code . ' - ' . $fullName) : $fullName,
					'start_date' => $startDate,
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

	public function actionGetAttendeesForCompetition($registration_id)
	{
		$result = array();
		$competitionId = isset($_GET['competition_id']) ? $_GET['competition_id'] : null;

		$attendees = Attendees::getByRegistrationId($registration_id);
		// Lọc theo phòng ban được phép thi nếu có competition_id
		$allowedDepartments = array();
		if ($competitionId) {
			$competition = Competitions::fetchFromApi($competitionId);
			if ($competition) {
				$allowedDepartments = $competition->getAllowedDepartments();
			}
		}

		foreach ($attendees as $att) {
			$id = isset($att['id']) ? $att['id'] : null;
			$fullName = isset($att['full_name']) ? $att['full_name'] : '';
			$staffCode = isset($att['staff_code']) ? $att['staff_code'] : '';
			$positionName = isset($att['position_name']) ? $att['position_name'] : '';
			$divisionCode = isset($att['division_code']) ? $att['division_code'] : '';

			if (!$id) continue;

			// Lọc theo division_code nằm trong danh sách phòng ban được phép thi
			if (!empty($allowedDepartments) && !in_array($divisionCode, $allowedDepartments)) {
				continue;
			}

			$result[] = array(
				'id' => $id,
				'name' => $fullName,
				'code' => $staffCode,
				'position' => $positionName,
				'division_code' => $divisionCode,
				'display' => $staffCode ? ($staffCode . ' - ' . $fullName) : $fullName,
			);
		}

		header('Content-Type: application/json');
		echo CJSON::encode(array('success' => true, 'data' => $result));
		Yii::app()->end();
	}

	public function actionAddCompetitionRegistration()
	{
		header('Content-Type: application/json');

		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
			Yii::app()->end();
		}

		$registrationId = Yii::app()->getRequest()->getPost('registration_id');
		$competitionId = Yii::app()->getRequest()->getPost('competition_id');
		$propertyId = Yii::app()->getRequest()->getPost('property_id');
		$staffIds = Yii::app()->getRequest()->getPost('staff_ids', array());
		$note = Yii::app()->getRequest()->getPost('note', '');

		if (empty($staffIds) || !is_array($staffIds)) {
			echo CJSON::encode(array('success' => false, 'error' => 'Vui lòng chọn ít nhất một nhân viên.'));
			Yii::app()->end();
		}

		$successCount = 0;
		$errorCount   = 0;
		$debugErrors  = array();
		$createdIds   = array();

		foreach ($staffIds as $staffId) {
			$regData = array(
				'registration_id' => $registrationId,
				'competition_id'  => $competitionId,
				'property_id'     => $propertyId,
				'attendee_id'     => $staffId,
				'status'          => CompetitionRegistrations::STATUS_PENDING,
				'note'            => $note,
			);
			$result = ApiClient::post(ApiEndpoints::COMPETITION_REGISTRATION_STORE, $regData);
			if ($result['success']) {
				$successCount++;
				if (isset($result['data']['data']['id'])) {
					$createdIds[] = $result['data']['data']['id'];
				} elseif (isset($result['data']['id'])) {
					$createdIds[] = $result['data']['id'];
				}
			} else {
				$errorCount++;
				$debugErrors[] = array('attendee_id' => $staffId, 'error' => $result);
			}
		}

		$message = "Đã đăng ký thành công {$successCount} người tham dự thi nghiệp vụ.";
		if ($errorCount > 0) {
			$message .= " Có {$errorCount} người không đăng ký được.";
		}

		// Load thông tin để render
		$competition = Competitions::fetchFromApi($competitionId);
		$competitionName = $competition ? $competition->name : '';

		// Load danh sách vừa đăng ký từ competition_registrations
		$registrations = CompetitionRegistrations::getApiDataProvider(array(
			'registration_id' => $registrationId,
			'competition_id'  => $competitionId,
		), 100)->getData();

		// Load attendees map để lấy thông tin chi tiết
		$attendeesMap = array();
		$attendeesData = Attendees::getByRegistrationId($registrationId);
		foreach ($attendeesData as $att) {
			$attId = isset($att['id']) ? $att['id'] : null;
			if ($attId) {
				$attendeesMap[$attId] = $att;
			}
		}

		$attendeeList = array();
		foreach ($registrations as $reg) {
			$attendeeId = isset($reg->attendee_id) ? $reg->attendee_id : (isset($reg['attendee_id']) ? $reg['attendee_id'] : null);
			$attendeeInfo = isset($attendeesMap[$attendeeId]) ? $attendeesMap[$attendeeId] : array();

			$attendeeList[] = array(
				'id' => isset($reg->id) ? $reg->id : (isset($reg['id']) ? $reg['id'] : null),
				'attendee_id' => $attendeeId,
				'attendee_name' => isset($attendeeInfo['full_name']) ? $attendeeInfo['full_name'] : '',
				'position_name' => isset($attendeeInfo['position_name']) ? $attendeeInfo['position_name'] : '',
				'division_name' => isset($attendeeInfo['division_name']) ? $attendeeInfo['division_name'] : '',
			);
		}

		echo CJSON::encode(array(
			'success'         => true,
			'message'         => $message,
			'successCount'    => $successCount,
			'errorCount'      => $errorCount,
			'competitionId'   => $competitionId,
			'competitionName' => $competitionName,
			'attendees'       => $attendeeList,
		));
		Yii::app()->end();
	}

	public function actionGetCompetitionRegisteredAttendees($registration_id, $competition_id)
	{
		header('Content-Type: application/json');

		$registrations = CompetitionRegistrations::getApiDataProvider(array(
			'registration_id' => $registration_id,
			'competition_id'  => $competition_id,
		), 100)->getData();

		$result = array();
		foreach ($registrations as $reg) {
			$result[] = array(
				'id' => isset($reg->id) ? $reg->id : (isset($reg['id']) ? $reg['id'] : null),
				'attendee_id' => isset($reg->attendee_id) ? $reg->attendee_id : (isset($reg['attendee_id']) ? $reg['attendee_id'] : null),
			);
		}

		echo CJSON::encode(array('success' => true, 'data' => $result));
		Yii::app()->end();
	}

	public function actionUpdateCompetitionRegistration()
	{
		header('Content-Type: application/json');

		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
			Yii::app()->end();
		}

		$registrationId = Yii::app()->getRequest()->getPost('registration_id');
		$competitionId = Yii::app()->getRequest()->getPost('competition_id');
		$propertyId = Yii::app()->getRequest()->getPost('property_id');
		$staffIds = Yii::app()->getRequest()->getPost('staff_ids', array());
		$note = Yii::app()->getRequest()->getPost('note', '');

		// Lấy danh sách đã đăng ký hiện tại
		$existingRegs = CompetitionRegistrations::getApiDataProvider(array(
			'registration_id' => $registrationId,
			'competition_id'  => $competitionId,
		), 100)->getData();

		$existingAttendeeIds = array();
		$existingRegMap = array();
		foreach ($existingRegs as $reg) {
			$attId = isset($reg->attendee_id) ? $reg->attendee_id : (isset($reg['attendee_id']) ? $reg['attendee_id'] : null);
			$regId = isset($reg->id) ? $reg->id : (isset($reg['id']) ? $reg['id'] : null);
			if ($attId && $regId) {
				$existingAttendeeIds[] = $attId;
				$existingRegMap[$attId] = $regId;
			}
		}

		$newStaffIds = is_array($staffIds) ? $staffIds : array();

		// Xóa những người không còn trong danh sách mới
		$toDelete = array_diff($existingAttendeeIds, $newStaffIds);
		foreach ($toDelete as $attId) {
			if (isset($existingRegMap[$attId])) {
				CompetitionRegistrations::deleteViaApi($existingRegMap[$attId]);
			}
		}

		// Thêm những người mới
		$toAdd = array_diff($newStaffIds, $existingAttendeeIds);
		$successCount = 0;
		foreach ($toAdd as $attId) {
			$regData = array(
				'registration_id' => $registrationId,
				'competition_id'  => $competitionId,
				'property_id'     => $propertyId,
				'attendee_id'     => $attId,
				'status'          => CompetitionRegistrations::STATUS_PENDING,
				'note'            => $note,
			);
			$result = ApiClient::post(ApiEndpoints::COMPETITION_REGISTRATION_STORE, $regData);
			if ($result['success']) {
				$successCount++;
			}
		}

		echo CJSON::encode(array(
			'success' => true,
			'message' => 'Đã cập nhật danh sách đăng ký thi nghiệp vụ.',
			'deleted' => count($toDelete),
			'added'   => $successCount,
		));
		Yii::app()->end();
	}

	public function actionDeleteCompetitionRegistration()
	{
		header('Content-Type: application/json');

		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
			Yii::app()->end();
		}

		$registrationId = Yii::app()->getRequest()->getPost('registration_id');
		$competitionId = Yii::app()->getRequest()->getPost('competition_id');

		$registrations = CompetitionRegistrations::getApiDataProvider(array(
			'registration_id' => $registrationId,
			'competition_id'  => $competitionId,
		), 100)->getData();

		$deletedCount = 0;
		foreach ($registrations as $reg) {
			$regId = isset($reg->id) ? $reg->id : (isset($reg['id']) ? $reg['id'] : null);
			if ($regId) {
				$result = CompetitionRegistrations::deleteViaApi($regId);
				if ($result['success']) {
					$deletedCount++;
				}
			}
		}

		echo CJSON::encode(array(
			'success' => true,
			'message' => "Đã xóa {$deletedCount} đăng ký.",
			'deleted' => $deletedCount,
		));
		Yii::app()->end();
	}

	public function actionAddAttendeesFromStaff()
	{
		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
			Yii::app()->end();
		}

		$registrationId = Yii::app()->getRequest()->getPost('registration_id');
		$eventId = Yii::app()->getRequest()->getPost('event_id');
		$propertyId = Yii::app()->getRequest()->getPost('property_id');
		$roleId = Yii::app()->getRequest()->getPost('role_id');
		$staffIds = Yii::app()->getRequest()->getPost('staff_ids', array());
		$checkInDate = Yii::app()->getRequest()->getPost('check_in_date');
		$checkOutDate = Yii::app()->getRequest()->getPost('check_out_date');
		$transportId = Yii::app()->getRequest()->getPost('transport_id');

		if (empty($staffIds) || !is_array($staffIds)) {
			echo CJSON::encode(array('success' => false, 'error' => 'Vui lòng chọn ít nhất một nhân viên.'));
			Yii::app()->end();
		}

		$successCount = 0;
		$errorCount = 0;

		foreach ($staffIds as $staffId) {
			$staff = Staffs::fetchFromApi($staffId);
			if (!$staff) {
				Yii::log("AddAttendeesFromStaff - Staff not found: {$staffId}", 'error', 'application.registration');
				$errorCount++;
				continue;
			}

			Yii::log("AddAttendeesFromStaff - Staff data: " . json_encode($staff->attributes), 'info', 'application.registration');

			$attendee = new Attendees;
			$attendee->event_id = $eventId;
			$attendee->registration_id = $registrationId;
			$attendee->property_id = $propertyId;
			$attendee->staff_id = $staffId;
			$attendee->role_id = $roleId;
			$attendee->full_name = $staff->full_name;
			$attendee->position = isset($staff->position_name) ? $staff->position_name : '';
			$attendee->approval_status = Attendees::APPROVAL_PENDING;
			$attendee->join_hotel_date = isset($staff->join_hotel_date) ? $staff->join_hotel_date : null;
			$attendee->check_in_date = $checkInDate;
			$attendee->check_out_date = $checkOutDate;
			$attendee->transport_id = $transportId;

			$uploadedFiles = $this->handleAttendeeDocumentUpload();
			
			if (isset($uploadedFiles['errors']) && !empty($uploadedFiles['errors'])) {
				Yii::app()->user->setFlash('error', implode("\n", $uploadedFiles['errors']));
				$this->redirect(array('view', 'id' => $registrationId));
			}

			if (isset($uploadedFiles['portrait_path'])) {
				$attendee->portrait_path = $uploadedFiles['portrait_path'];
			}
			if (isset($uploadedFiles['cccd_front_path'])) {
				$attendee->cccd_front_path = $uploadedFiles['cccd_front_path'];
			}
			if (isset($uploadedFiles['cccd_back_path'])) {
				$attendee->cccd_back_path = $uploadedFiles['cccd_back_path'];
			}
			if (isset($uploadedFiles['contract_path'])) {
				$attendee->contract_path = $uploadedFiles['contract_path'];
			}
			$result = $attendee->storeViaApi();
			if ($result['success']) {
				$successCount++;
			} else {
				Yii::log("AddAttendeesFromStaff - Store failed: " . json_encode($result), 'error', 'application.registration');
				$errorCount++;
			}
		}

		$message = '';
		if ($successCount > 0) {
			$message = "Đã thêm thành công {$successCount} người tham dự.";
		}
		if ($errorCount > 0) {
			$message .= ($message ? ' ' : '') . "Có {$errorCount} người không thêm được.";
		}

		echo CJSON::encode(array(
			'success' => $successCount > 0,
			'message' => $message,
			'added' => $successCount,
			'failed' => $errorCount,
		));
		Yii::app()->end();
	}

	public function actionAddAttendeeManual()
	{
		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
		}

		$registrationId = Yii::app()->getRequest()->getPost('registration_id');
		$eventId = Yii::app()->getRequest()->getPost('event_id');
		$propertyId = Yii::app()->getRequest()->getPost('property_id');
		$checkInDate = Yii::app()->getRequest()->getPost('check_in_date');
		$checkOutDate = Yii::app()->getRequest()->getPost('check_out_date');
		$transportId = Yii::app()->getRequest()->getPost('transport_id');
		$join_hotel_date = Yii::app()->getRequest()->getPost('join_hotel_date');
		if ($join_hotel_date === null) {
			$join_hotel_date = Yii::app()->getRequest()->getPost('start_date');
		}

		$attendee = new Attendees;
		$attendee->event_id = $eventId;
		$attendee->registration_id = $registrationId;
		$attendee->property_id = $propertyId;
		$attendee->full_name = Yii::app()->getRequest()->getPost('full_name');
		$attendee->position = Yii::app()->getRequest()->getPost('position');
		$attendee->role_id = Yii::app()->getRequest()->getPost('role_id');
		$attendee->note = Yii::app()->getRequest()->getPost('note');
		$attendee->approval_status = Attendees::APPROVAL_PENDING;
		$attendee->join_hotel_date = $join_hotel_date;
		$attendee->check_in_date = $checkInDate;
		$attendee->check_out_date = $checkOutDate;
		$attendee->transport_id = $transportId;

		$uploadedFiles = $this->handleAttendeeDocumentUpload();
		if (isset($uploadedFiles['portrait_path'])) {
			$attendee->portrait_path = $uploadedFiles['portrait_path'];
		}
		if (isset($uploadedFiles['cccd_front_path'])) {
			$attendee->cccd_front_path = $uploadedFiles['cccd_front_path'];
		}
		if (isset($uploadedFiles['cccd_back_path'])) {
			$attendee->cccd_back_path = $uploadedFiles['cccd_back_path'];
		}
		if (isset($uploadedFiles['contract_path'])) {
			$attendee->contract_path = $uploadedFiles['contract_path'];
		}

		$result = $attendee->storeViaApi();

		if ($result['success']) {
			Yii::app()->user->setFlash('success', 'Đã thêm người tham dự thành công.');
		} else {
			Yii::app()->user->setFlash('error', isset($result['error']) ? $result['error'] : 'Không thể thêm người tham dự.');
		}

		$this->redirect(array('view', 'id' => $registrationId));
	}

	protected function handleAttendeeDocumentUpload()
	{
		$result = array();
		$uploadDir = Yii::getPathOfAlias('webroot') . '/uploads/attendees/';
		if (!is_dir($uploadDir)) {
			mkdir($uploadDir, 0755, true);
		}

		$fileFields = array(
			'portrait_file' => 'portrait_path',
			'cccd_front_file' => 'cccd_front_path',
			'cccd_back_file' => 'cccd_back_path',
			'contract_file' => 'contract_path',
		);

		$allowedTypes = array('jpg', 'jpeg', 'png', 'gif', 'pdf');
		$maxSize = 10 * 1024 * 1024; // Increase to 10MB to support PDF contracts

		foreach ($fileFields as $fieldName => $attrName) {
			if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
				continue;
			}

			if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
				$result['errors'][] = "Lỗi khi tải lên {$fieldName}: Mã lỗi " . $_FILES[$fieldName]['error'];
				continue;
			}

			$ext = strtolower(pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION));
			if (!in_array($ext, $allowedTypes)) {
				continue;
			}

			if ($_FILES[$fieldName]['size'] > $maxSize) {
				continue;
			}

			$filename = date('Ymd_His') . '_' . uniqid() . '.' . $ext;
			$filepath = $uploadDir . $filename;

			if (move_uploaded_file($_FILES[$fieldName]['tmp_name'], $filepath)) {
				$result[$attrName] = Yii::app()->baseUrl . '/uploads/attendees/' . $filename;
			}
		}

		return $result;
	}

	public function actionDeleteAttendee($id, $registration_id)
	{
		if (Yii::app()->getRequest()->getIsPostRequest()) {
			$result = Attendees::deleteViaApi($id);

			if ($result['success']) {
				Yii::app()->user->setFlash('success', 'Xóa người tham dự thành công.');
			} else {
				Yii::app()->user->setFlash('error', isset($result['error']) ? $result['error'] : 'Không thể xóa.');
			}

			$this->redirect(array('view', 'id' => $registration_id));
		} else {
			throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
		}
	}

	public function actionGetAttendeesList($registration_id)
	{
		$attendees = Attendees::getByRegistrationId($registration_id);
		$result = array();

		foreach ($attendees as $att) {
			$attId = isset($att['id']) ? $att['id'] : '';
			$staffId = isset($att['staff_id']) ? $att['staff_id'] : null;
			$positionName = isset($att['position']) ? $att['position'] : '';
			$departmentName = '';

			if ($staffId) {
				$staff = Staffs::fetchFromApi($staffId);
				if ($staff) {
					$positionName = isset($staff->position_name) ? $staff->position_name : $positionName;
					$departmentName = isset($staff->division_name) ? $staff->division_name : '';
				}
			}

			$result[] = array(
				'id' => $attId,
				'full_name' => isset($att['full_name']) ? $att['full_name'] : '',
				'position' => $positionName,
				'department_name' => $departmentName,
				'role_name' => isset($att['role_name']) ? $att['role_name'] : '',
				'portrait_path' => isset($att['portrait_path']) ? $att['portrait_path'] : (isset($att['photo_path']) ? $att['photo_path'] : ''),
				'approval_status' => isset($att['approval_status']) ? (int)$att['approval_status'] : 0,
				'start_date' => isset($att['join_hotel_date']) ? $att['join_hotel_date'] : (isset($att['start_date']) ? $att['start_date'] : ''),
				'check_in_date' => isset($att['check_in_date']) ? $att['check_in_date'] : '',
				'check_out_date' => isset($att['check_out_date']) ? $att['check_out_date'] : '',
				'transport_name' => isset($att['transport_name']) ? $att['transport_name'] : '',
				'contract_path' => isset($att['contract_path']) ? $att['contract_path'] : '',
			);
		}

		echo CJSON::encode(array('success' => true, 'data' => $result));
		Yii::app()->end();
	}

	public function actionGetAttendeeDetail($id)
	{
		$attendee = Attendees::fetchFromApi($id);
		if (!$attendee) {
			echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy người tham dự.'));
			Yii::app()->end();
		}

		$positionName = $attendee->position;
		$departmentName = '';
		if ($attendee->staff_id) {
			$staff = Staffs::fetchFromApi($attendee->staff_id);
			if ($staff) {
				$positionName = isset($staff->position_name) ? $staff->position_name : $attendee->position;
				$departmentName = isset($staff->division_name) ? $staff->division_name : '';
			}
		}

		$data = array(
			'id' => $attendee->id,
			'full_name' => $attendee->full_name,
			'position' => $positionName,
			'department_name' => $departmentName,
			'role_id' => $attendee->role_id,
			'note' => $attendee->note,
			'portrait_path' => $attendee->portrait_path,
			'cccd_front_path' => $attendee->cccd_front_path,
			'cccd_back_path' => $attendee->cccd_back_path,
			'contract_path' => $attendee->contract_path,
			'join_hotel_date' => $attendee->join_hotel_date,
			'start_date' => $attendee->join_hotel_date,
			'check_in_date' => $attendee->check_in_date,
			'check_out_date' => $attendee->check_out_date,
			'transport_id' => $attendee->transport_id,
		);

		echo CJSON::encode(array('success' => true, 'data' => $data));
		Yii::app()->end();
	}

	public function actionUpdateAttendeeAjax()
	{
		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
			Yii::app()->end();
		}

		$id = Yii::app()->getRequest()->getPost('attendee_id');
		$registrationId = Yii::app()->getRequest()->getPost('registration_id');

		$attendee = Attendees::fetchFromApi($id);
		if (!$attendee) {
			echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy người tham dự.'));
			Yii::app()->end();
		}

		$attendee->full_name = Yii::app()->getRequest()->getPost('full_name');
		$attendee->position = Yii::app()->getRequest()->getPost('position');
		$attendee->role_id = Yii::app()->getRequest()->getPost('role_id');
		$attendee->note = Yii::app()->getRequest()->getPost('note');
		
		$joinHotelDate = Yii::app()->getRequest()->getPost('join_hotel_date');
		if ($joinHotelDate === null) {
			$joinHotelDate = Yii::app()->getRequest()->getPost('start_date');
		}
		if ($joinHotelDate !== null) {
			$attendee->join_hotel_date = $joinHotelDate;
		}
		
		$attendee->check_in_date = Yii::app()->getRequest()->getPost('check_in_date');
		$attendee->check_out_date = Yii::app()->getRequest()->getPost('check_out_date');
		$attendee->transport_id = Yii::app()->getRequest()->getPost('transport_id');

		$uploadedFiles = $this->handleAttendeeDocumentUpload();
		
		if (isset($uploadedFiles['errors']) && !empty($uploadedFiles['errors'])) {
			echo CJSON::encode(array('success' => false, 'error' => implode("\n", $uploadedFiles['errors'])));
			Yii::app()->end();
		}

		if (isset($uploadedFiles['portrait_path'])) {
			$attendee->portrait_path = $uploadedFiles['portrait_path'];
		}
		if (isset($uploadedFiles['cccd_front_path'])) {
			$attendee->cccd_front_path = $uploadedFiles['cccd_front_path'];
		}
		if (isset($uploadedFiles['cccd_back_path'])) {
			$attendee->cccd_back_path = $uploadedFiles['cccd_back_path'];
		}
		if (isset($uploadedFiles['contract_path'])) {
			$attendee->contract_path = $uploadedFiles['contract_path'];
		}

		$result = $attendee->updateViaApi();

		if ($result['success']) {
			echo CJSON::encode(array('success' => true, 'message' => 'Cập nhật thành công.'));
		} else {
			echo CJSON::encode(array('success' => false, 'error' => isset($result['error']) ? $result['error'] : 'Không thể cập nhật.'));
		}
		Yii::app()->end();
	}

	// ==================== MISS REGISTRATION ====================

	public function actionGetMissContestInfo($contest_id)
	{
		$contest = BeautyContests::fetchFromApi($contest_id);
		$result = array();

		if ($contest) {
			$result = array(
				'id' => $contest->id,
				'name' => $contest->name,
				'max_per_org' => isset($contest->max_per_org) ? (int)$contest->max_per_org : 0,
				'gender' => isset($contest->gender) ? $contest->gender : null,
			);
		}

		header('Content-Type: application/json');
		echo CJSON::encode(array('success' => true, 'data' => $result));
		Yii::app()->end();
	}

	public function actionGetAttendeesForMiss($registration_id)
	{
		$result = array();
		$registered = array();
		$contestId = isset($_GET['contest_id']) ? $_GET['contest_id'] : null;

		$contest = $contestId ? BeautyContests::fetchFromApi($contestId) : null;
		$contestGenderRaw = $contest && isset($contest->gender) ? $contest->gender : null;
		$contestGender = null;
		if ($contestGenderRaw === 'female') {
			$contestGender = 0;
		} elseif ($contestGenderRaw === 'male') {
			$contestGender = 1;
		}

		$existingContestants = $contestId ? BeautyContestants::getApiDataProvider(array('contest_id' => $contestId), 1000)->getData() : array();
		$registeredAttendeeIds = array();
		foreach ($existingContestants as $c) {
			$attId = isset($c->attendee_id) ? $c->attendee_id : (isset($c['attendee_id']) ? $c['attendee_id'] : null);
			if ($attId) $registeredAttendeeIds[] = $attId;

			$registered[] = array(
				'id' => isset($c->id) ? $c->id : $c['id'],
				'attendee_id' => $attId,
				'name' => isset($c->attendee_name) ? $c->attendee_name : (isset($c['attendee_name']) ? $c['attendee_name'] : ''),
				'candidate_number' => isset($c->candidate_number) ? $c->candidate_number : (isset($c['candidate_number']) ? $c['candidate_number'] : ''),
			);
		}

		$attendees = Attendees::getByRegistrationId($registration_id);

		foreach ($attendees as $att) {
			$id = isset($att['id']) ? $att['id'] : null;
			$fullName = isset($att['full_name']) ? $att['full_name'] : '';
			$positionName = isset($att['position']) ? $att['position'] : '';
			$gender = isset($att['gender']) ? $att['gender'] : null;

			if (!$id) continue;

			if (in_array($id, $registeredAttendeeIds)) continue;

			if ($contestGender !== null && (int)$gender !== $contestGender) continue;

			$result[] = array(
				'id' => $id,
				'name' => $fullName,
				'position' => $positionName,
				'display' => $fullName . ($positionName ? ' (' . $positionName . ')' : ''),
			);
		}

		header('Content-Type: application/json');
		echo CJSON::encode(array('success' => true, 'data' => $result, 'registered' => $registered));
		Yii::app()->end();
	}

	public function actionAddMissRegistration()
	{
		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
			Yii::app()->end();
		}

		$registrationId = Yii::app()->getRequest()->getPost('registration_id');
		$contestId = Yii::app()->getRequest()->getPost('contest_id');
		$attendeeIds = Yii::app()->getRequest()->getPost('attendee_ids', array());
		$note = Yii::app()->getRequest()->getPost('note', '');

		if (empty($attendeeIds)) {
			echo CJSON::encode(array('success' => false, 'error' => 'Vui lòng chọn ít nhất một thí sinh.'));
			Yii::app()->end();
		}

		$contest = BeautyContests::fetchFromApi($contestId);
		if (!$contest) {
			echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy cuộc thi.'));
			Yii::app()->end();
		}

		$prefix = $contest->candidate_prefix ?: 'MS';
		$startNum = $contest->candidate_start ?: 1;

		$existingContestants = BeautyContestants::getApiDataProvider(array('contest_id' => $contestId), 1000)->getData();
		$nextNum = $startNum + count($existingContestants);

		$successCount = 0;
		$errors = array();
		foreach ($attendeeIds as $attendeeId) {
			$candidateNumber = $prefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

			$model = new BeautyContestants;
			$model->contest_id = $contestId;
			$model->attendee_id = $attendeeId;
			$model->candidate_number = $candidateNumber;
			$model->registration_id = $registrationId;
			$model->note = $note;
			$model->status = BeautyContestants::STATUS_REGISTERED;

			$result = $model->storeViaApi();
			if ($result['success']) {
				$successCount++;
				$nextNum++;
			} else {
				$errors[] = isset($result['error']) ? $result['error'] : 'Lỗi không xác định';
			}
		}

		if ($successCount > 0) {
			echo CJSON::encode(array('success' => true, 'message' => "Đăng ký thành công {$successCount} thí sinh."));
		} else {
			$errorMsg = !empty($errors) ? implode('; ', array_unique($errors)) : 'Không thể đăng ký.';
			echo CJSON::encode(array('success' => false, 'error' => $errorMsg));
		}
		Yii::app()->end();
	}

	public function actionGetMissContestant($id)
	{
		$model = BeautyContestants::fetchFromApi($id);
		if (!$model) {
			echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy thí sinh.'));
			Yii::app()->end();
		}

		$attendeeName = $model->attendee_name;
		if (empty($attendeeName) && $model->attendee_id) {
			$attendee = Attendees::fetchFromApi($model->attendee_id);
			if ($attendee) {
				$attendeeName = $attendee->full_name;
			}
		}

		header('Content-Type: application/json');
		echo CJSON::encode(array('success' => true, 'data' => array(
			'id' => $model->id,
			'attendee_name' => $attendeeName,
			'candidate_number' => $model->candidate_number,
			'height_cm' => $model->height_cm,
			'weight_kg' => $model->weight_kg,
			'measurements' => $model->measurements,
			'talent' => $model->talent,
			'bio' => $model->bio,
		)));
		Yii::app()->end();
	}

	public function actionUpdateMissContestant()
	{
		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
			Yii::app()->end();
		}

		$id = Yii::app()->getRequest()->getPost('id');
		$model = BeautyContestants::fetchFromApi($id);
		if (!$model) {
			echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy thí sinh.'));
			Yii::app()->end();
		}

		$model->height_cm = Yii::app()->getRequest()->getPost('height_cm');
		$model->weight_kg = Yii::app()->getRequest()->getPost('weight_kg');
		$model->measurements = Yii::app()->getRequest()->getPost('measurements');
		$model->talent = Yii::app()->getRequest()->getPost('talent');
		$model->bio = Yii::app()->getRequest()->getPost('bio');

		$result = $model->updateViaApi();
		if ($result['success']) {
			echo CJSON::encode(array('success' => true, 'message' => 'Cập nhật thành công.'));
		} else {
			echo CJSON::encode(array('success' => false, 'error' => $result['error'] ?: 'Không thể cập nhật.'));
		}
		Yii::app()->end();
	}

	public function actionDeleteMissContestant()
	{
		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
			Yii::app()->end();
		}

		$id = Yii::app()->getRequest()->getPost('id');
		$result = BeautyContestants::deleteViaApi($id);

		if ($result['success']) {
			echo CJSON::encode(array('success' => true, 'message' => 'Xóa thành công.'));
		} else {
			echo CJSON::encode(array('success' => false, 'error' => $result['error'] ?: 'Không thể xóa.'));
		}
		Yii::app()->end();
	}

	// ==================== TALENT REGISTRATION ====================

	public function actionGetTalentCategories($event_id)
	{
		$result = array();

		$categories = TalentCategories::getApiDataProvider(array(), 100)->getData();
		foreach ($categories as $cat) {
			$id = isset($cat['id']) ? $cat['id'] : (isset($cat->id) ? $cat->id : null);
			$name = isset($cat['name']) ? $cat['name'] : (isset($cat->name) ? $cat->name : '');
			if ($id) {
				$result[] = array('id' => $id, 'name' => $name);
			}
		}

		header('Content-Type: application/json');
		echo CJSON::encode(array('success' => true, 'data' => $result));
		Yii::app()->end();
	}

	public function actionGetAttendeesForTalent($registration_id)
	{
		$result = array();

		$attendees = Attendees::getByRegistrationId($registration_id);

		foreach ($attendees as $att) {
			$id = isset($att['id']) ? $att['id'] : null;
			$fullName = isset($att['full_name']) ? $att['full_name'] : '';
			$positionName = isset($att['position']) ? $att['position'] : '';

			if (!$id) continue;

			$result[] = array(
				'id' => $id,
				'name' => $fullName,
				'position' => $positionName,
				'display' => $fullName . ($positionName ? ' (' . $positionName . ')' : ''),
			);
		}

		header('Content-Type: application/json');
		echo CJSON::encode(array('success' => true, 'data' => $result));
		Yii::app()->end();
	}

	public function actionAddTalentRegistration()
	{
		if (!Yii::app()->getRequest()->getIsPostRequest()) {
			echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
			Yii::app()->end();
		}

		$registrationId = Yii::app()->getRequest()->getPost('registration_id');
		$categoryId = Yii::app()->getRequest()->getPost('category_id');
		$title = Yii::app()->getRequest()->getPost('title');
		$duration = Yii::app()->getRequest()->getPost('duration');
		$attendeeIds = Yii::app()->getRequest()->getPost('attendee_ids', array());
		$alliancePropertyIds = Yii::app()->getRequest()->getPost('alliance_property_ids', array());
		$note = Yii::app()->getRequest()->getPost('note', '');

		if (empty($attendeeIds)) {
			echo CJSON::encode(array('success' => false, 'error' => 'Vui lòng chọn ít nhất một người biểu diễn.'));
			Yii::app()->end();
		}

		// Tạo talent entry
		$entry = new TalentEntries;
		$entry->registration_id = $registrationId;
		$entry->category_id = $categoryId;
		$entry->title = $title;
		$entry->duration = $duration;
		$entry->note = $note;
		$entry->alliance_property_ids = !empty($alliancePropertyIds) ? implode(',', $alliancePropertyIds) : null;

		$result = $entry->storeViaApi();
		if (!$result['success']) {
			echo CJSON::encode(array('success' => false, 'error' => isset($result['error']) ? $result['error'] : 'Không thể tạo tiết mục.'));
			Yii::app()->end();
		}

		$entryId = isset($result['data']['id']) ? $result['data']['id'] : null;

		// Thêm thành viên
		if ($entryId) {
			foreach ($attendeeIds as $attendeeId) {
				$member = new TalentEntryMembers;
				$member->entry_id = $entryId;
				$member->attendee_id = $attendeeId;
				$member->storeViaApi();
			}
		}

		echo CJSON::encode(array('success' => true, 'message' => 'Đăng ký tiết mục thành công.'));
		Yii::app()->end();
	}
}
