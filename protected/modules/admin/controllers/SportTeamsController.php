<?php

class SportTeamsController extends AdminController
{
    public function actionIndex()
    {
        $this->redirect(array('admin'));
    }

    public function actionView($id)
    {
        $model = $this->loadModelById($id);
        $members = SportTeamMembers::getApiDataProvider(array('sport_team_id' => $id), 100)->getData();

        $this->render('view', array(
            'model' => $model,
            'members' => $members,
        ));
    }

    public function actionCreate()
    {
        $model = new SportTeams;

        if (isset($_POST['SportTeams'])) {
            $model->setAttributes($_POST['SportTeams']);
            if ($model->validate()) {
                $model->status = SportTeams::STATUS_PENDING;
                $result = $model->storeViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Tạo đội thể thao thành công.');
                    $newId = isset($result['data']['id']) ? $result['data']['id'] : null;

                    if (isset($_POST['alliance_org_ids']) && !empty($_POST['alliance_org_ids'])) {
                        $this->createAllianceRequests($model->event_id, $_POST['alliance_org_ids'], $model->property_id);
                    }

                    $this->redirect($newId ? array('view', 'id' => $newId) : array('admin'));
                } else {
                    $errorMsg = $result['error'] ?: 'Không thể tạo đội.';
                    $model->addError('team_name', $errorMsg);
                }
            }
        }

        $events = Events::getActiveList();
        $sports = Sports::getApiDataProvider(array('is_active' => 1), 100)->getData();
        $properties = Properties::getListForDropdown();

        $this->render('create', array(
            'model' => $model,
            'events' => $events,
            'sports' => $sports,
            'properties' => $properties,
        ));
    }

    public function actionUpdate($id)
    {
        $model = $this->loadModelById($id);

        if (isset($_POST['SportTeams'])) {
            $model->setAttributes($_POST['SportTeams']);

            if ($model->validate()) {
                $result = $model->updateViaApi();

                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Cập nhật đội thành công.');
                    $this->redirect(array('view', 'id' => $id));
                } else {
                    $model->addError('team_name', $result['error'] ?: 'Không thể cập nhật.');
                }
            }
        }

        $events = Events::getActiveList();
        $sports = Sports::getApiDataProvider(array('is_active' => 1), 100)->getData();
        $properties = Properties::getListForDropdown();

        $this->render('update', array(
            'model' => $model,
            'events' => $events,
            'sports' => $sports,
            'properties' => $properties,
        ));
    }

    public function actionDelete($id)
    {
        if (Yii::app()->getRequest()->getIsPostRequest()) {
            $result = SportTeams::deleteViaApi($id);

            if ($result['success']) {
                Yii::app()->user->setFlash('success', 'Xóa đội thành công.');
            } else {
                Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể xóa.');
            }

            if (!Yii::app()->getRequest()->getIsAjaxRequest()) {
                $this->redirect(array('admin'));
            }
        } else {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }
    }

    public function actionAdmin()
    {
        $model = new SportTeams('search');
        $model->unsetAttributes();

        $params = array();
        if (isset($_GET['SportTeams'])) {
            $model->setAttributes($_GET['SportTeams']);
            foreach ($_GET['SportTeams'] as $key => $value) {
                if ($value !== null && $value !== '') {
                    $params[$key] = $value;
                }
            }
        }

        $dataProvider = SportTeams::getApiDataProvider($params);
        $events = Events::getActiveList();
        $sports = Sports::getApiDataProvider(array('is_active' => 1), 100)->getData();

        $this->render('admin', array(
            'model' => $model,
            'dataProvider' => $dataProvider,
            'events' => $events,
            'sports' => $sports,
        ));
    }

    public function actionAddMember($teamId)
    {
        $team = $this->loadModelById($teamId);
        $model = new SportTeamMembers;

        if (isset($_POST['SportTeamMembers'])) {
            $model->setAttributes($_POST['SportTeamMembers']);
            $model->sport_team_id = $teamId;

            $attendeeId = $model->attendee_id;
            if (!SportTeamMembers::canRegisterMore($attendeeId)) {
                $model->addError('attendee_id', 'Người này đã đăng ký tối đa ' . SportTeamMembers::MAX_SPORTS_PER_ATTENDEE . ' môn thể thao.');
            } elseif ($model->validate()) {
                $result = $model->storeViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Thêm thành viên thành công.');
                    $this->redirect(array('view', 'id' => $teamId));
                } else {
                    $model->addError('attendee_id', $result['error'] ?: 'Không thể thêm thành viên.');
                }
            }
        }

        $attendees = Attendees::getApiDataProvider(array(
            'property_id' => $team->property_id,
            'approval_status' => Attendees::APPROVAL_APPROVED,
        ), 500)->getData();

        $this->render('add_member', array(
            'model' => $model,
            'team' => $team,
            'attendees' => $attendees,
        ));
    }

    public function actionRemoveMember($id)
    {
        if (Yii::app()->getRequest()->getIsPostRequest()) {
            $member = SportTeamMembers::fetchFromApi($id);
            $teamId = $member ? $member->sport_team_id : null;

            $result = SportTeamMembers::deleteViaApi($id);

            if ($result['success']) {
                Yii::app()->user->setFlash('success', 'Xóa thành viên thành công.');
            } else {
                Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể xóa thành viên.');
            }

            if ($teamId) {
                $this->redirect(array('view', 'id' => $teamId));
            } else {
                $this->redirect(array('admin'));
            }
        } else {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }
    }

    public function actionGetSameRegionalProperties($propertyId)
    {
        $result = ApiClient::get(ApiEndpoints::PROPERTY_LIST, array(
            'same_regional_as' => $propertyId,
            'per_page' => 100,
        ));

        $properties = array();
        if ($result['success'] && isset($result['data']['data'])) {
            foreach ($result['data']['data'] as $prop) {
                if ($prop['id'] != $propertyId) {
                    $properties[] = array(
                        'id' => $prop['id'],
                        'name' => $prop['name'],
                        'code' => $prop['code'],
                    );
                }
            }
        }

        header('Content-Type: application/json');
        echo json_encode(array('success' => true, 'data' => $properties));
        Yii::app()->end();
    }

    protected function createAllianceRequests($eventId, $targetOrgIds, $requesterOrgId)
    {
        $ssoUser = AuthHandler::getUser();
        $requestedBy = isset($ssoUser['id']) ? $ssoUser['id'] : null;

        foreach ($targetOrgIds as $targetOrgId) {
            $request = new AllianceRequests;
            $request->event_id = $eventId;
            $request->requester_org_id = $requesterOrgId;
            $request->target_org_id = $targetOrgId;
            $request->requested_by = $requestedBy;
            $request->storeViaApi();
        }
    }

    protected function loadModelById($id)
    {
        $model = SportTeams::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy đội thể thao.');
        }
        return $model;
    }
}
