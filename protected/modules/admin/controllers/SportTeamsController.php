<?php

class SportTeamsController extends AdminController
{
    public function actionIndex()
    {
        $this->redirect(array('admin'));
    }

    public function actionAdmin()
    {
        $events = Events::getActiveList();
        $sports = Sports::getApiDataProvider(array('is_active' => 1), 100)->getData();
        $properties = Properties::getListForDropdown();

        $this->render('overview', array(
            'events' => $events,
            'sports' => $sports,
            'properties' => $properties,
        ));
    }

    public function actionViewByProperty()
    {
        $eventId = Yii::app()->request->getQuery('event_id');
        $propertyId = Yii::app()->request->getQuery('property_id');

        $teams = SportTeams::getApiDataProvider(array(
            'event_id' => $eventId,
            'property_id' => $propertyId,
        ), 500)->getData();

        $teamsBySport = array();
        foreach ($teams as $team) {
            $sportName = $team->sport_name ?: 'Chưa xác định';
            if (!isset($teamsBySport[$sportName])) {
                $teamsBySport[$sportName] = array(
                    'sport_name' => $sportName,
                    'teams' => array(),
                );
            }

            $members = SportTeamMembers::getApiDataProvider(array('sport_team_id' => $team->id), 100)->getData();
            $memberList = array();
            foreach ($members as $m) {
                $memberList[] = array(
                    'name' => $m->attendee_name,
                    'department' => isset($m->department_name) ? $m->department_name : '',
                );
            }

            $teamsBySport[$sportName]['teams'][] = array(
                'id' => $team->id,
                'team_name' => $team->team_name,
                'name' => isset($team->name) ? $team->name : '',
                'is_alliance' => $team->is_alliance,
                'status' => $team->status,
                'member_count' => count($members),
                'members' => $memberList,
            );
        }

        $eventName = '';
        $propertyName = '';
        $eventList = Events::getActiveList();
        if (isset($eventList[$eventId])) {
            $eventName = $eventList[$eventId];
        }
        $propList = Properties::getListForDropdown();
        if (isset($propList[$propertyId])) {
            $propertyName = $propList[$propertyId];
        }

        $this->render('view_by_property', array(
            'propertyName' => $propertyName,
            'eventName' => $eventName,
            'eventId' => $eventId,
            'propertyId' => $propertyId,
            'teamsBySport' => array_values($teamsBySport),
        ));
    }

    public function actionViewBySport()
    {
        $eventId = Yii::app()->request->getQuery('event_id');
        $sportId = Yii::app()->request->getQuery('sport_id');

        $teams = SportTeams::getApiDataProvider(array(
            'event_id' => $eventId,
            'sport_id' => $sportId,
        ), 500)->getData();

        // Lấy danh sách khu vực và property để map
        $regionals = Regionals::getApiDataProvider(array(), 100)->getData();
        $regionalMap = array();
        $regionalCodeMap = array();
        foreach ($regionals as $r) {
            $regionalMap[$r->id] = $r->name;
            $regionalCodeMap[$r->id] = isset($r->code) ? $r->code : '';
        }

        $properties = Properties::getApiDataProvider(array(), 500)->getData();
        $propertyRegionMap = array();
        foreach ($properties as $p) {
            $propertyRegionMap[$p->id] = isset($p->region_id) ? $p->region_id : null;
        }

        $teamsByRegion = array();
        foreach ($teams as $team) {
            $propName = $team->property_name ?: 'Chưa xác định';
            $propId = $team->property_id;
            $regionId = isset($propertyRegionMap[$propId]) ? $propertyRegionMap[$propId] : null;
            $regionName = ($regionId && isset($regionalMap[$regionId])) ? $regionalMap[$regionId] : 'Chưa phân cụm';

            $regionCode = ($regionId && isset($regionalCodeMap[$regionId])) ? $regionalCodeMap[$regionId] : 'ZZZ';

            if (!isset($teamsByRegion[$regionId])) {
                $teamsByRegion[$regionId] = array(
                    'region_id' => $regionId,
                    'region_name' => $regionName,
                    'region_code' => $regionCode,
                    'properties' => array(),
                );
            }

            if (!isset($teamsByRegion[$regionId]['properties'][$propId])) {
                $teamsByRegion[$regionId]['properties'][$propId] = array(
                    'property_name' => $propName,
                    'teams' => array(),
                );
            }

            $members = SportTeamMembers::getApiDataProvider(array('sport_team_id' => $team->id), 100)->getData();

            $teamsByRegion[$regionId]['properties'][$propId]['teams'][] = array(
                'id' => $team->id,
                'team_name' => $team->team_name,
                'name' => isset($team->name) ? $team->name : '',
                'is_alliance' => $team->is_alliance,
                'status' => $team->status,
                'member_count' => count($members),
            );
        }

        // Convert properties từ associative array sang indexed array
        foreach ($teamsByRegion as &$region) {
            $region['properties'] = array_values($region['properties']);
        }
        unset($region);

        // Sắp xếp theo mã cụm (region_code)
        usort($teamsByRegion, function($a, $b) {
            return strcmp($a['region_code'], $b['region_code']);
        });

        $eventName = '';
        $sportName = '';
        $eventList = Events::getActiveList();
        if (isset($eventList[$eventId])) {
            $eventName = $eventList[$eventId];
        }
        $sport = Sports::fetchFromApi($sportId);
        if ($sport) {
            $sportName = $sport->name;
        }

        // Lấy danh sách khu vực có đội để hiển thị filter
        $regionList = array();
        foreach ($teamsByRegion as $regionId => $regionData) {
            $regionList[$regionId] = $regionData['region_name'];
        }

        $this->render('view_by_sport', array(
            'sportName' => $sportName,
            'eventName' => $eventName,
            'eventId' => $eventId,
            'sportId' => $sportId,
            'teamsByRegion' => array_values($teamsByRegion),
            'regionList' => $regionList,
        ));
    }

    public function actionAjaxViewByProperty()
    {
        $eventId = Yii::app()->request->getQuery('event_id');
        $propertyId = Yii::app()->request->getQuery('property_id');

        $teams = SportTeams::getApiDataProvider(array(
            'event_id' => $eventId,
            'property_id' => $propertyId,
        ), 500)->getData();

        $teamsBySport = array();
        foreach ($teams as $team) {
            $sportName = $team->sport_name ?: 'Chưa xác định';
            if (!isset($teamsBySport[$sportName])) {
                $teamsBySport[$sportName] = array(
                    'sport_name' => $sportName,
                    'teams' => array(),
                );
            }

            $members = SportTeamMembers::getApiDataProvider(array('sport_team_id' => $team->id), 100)->getData();
            $memberList = array();
            foreach ($members as $m) {
                $memberList[] = array(
                    'name' => $m->attendee_name,
                    'department' => isset($m->department_name) ? $m->department_name : '',
                );
            }

            $teamsBySport[$sportName]['teams'][] = array(
                'id' => $team->id,
                'team_name' => $team->team_name,
                'name' => isset($team->name) ? $team->name : '',
                'is_alliance' => $team->is_alliance,
                'status' => $team->status,
                'member_count' => count($members),
                'members' => $memberList,
            );
        }

        $eventName = '';
        $propertyName = '';
        $eventList = Events::getActiveList();
        if (isset($eventList[$eventId])) {
            $eventName = $eventList[$eventId];
        }
        $propList = Properties::getListForDropdown();
        if (isset($propList[$propertyId])) {
            $propertyName = $propList[$propertyId];
        }

        $this->renderPartial('_view_by_property', array(
            'propertyName' => $propertyName,
            'eventName' => $eventName,
            'teamsBySport' => array_values($teamsBySport),
        ));
    }

    public function actionAjaxViewBySport()
    {
        $eventId = Yii::app()->request->getQuery('event_id');
        $sportId = Yii::app()->request->getQuery('sport_id');

        $teams = SportTeams::getApiDataProvider(array(
            'event_id' => $eventId,
            'sport_id' => $sportId,
        ), 500)->getData();

        // Lấy danh sách khu vực và property để map
        $regionals = Regionals::getApiDataProvider(array(), 100)->getData();
        $regionalMap = array();
        $regionalCodeMap = array();
        foreach ($regionals as $r) {
            $regionalMap[$r->id] = $r->name;
            $regionalCodeMap[$r->id] = isset($r->code) ? $r->code : '';
        }

        $properties = Properties::getApiDataProvider(array(), 500)->getData();
        $propertyRegionMap = array();
        foreach ($properties as $p) {
            $propertyRegionMap[$p->id] = isset($p->region_id) ? $p->region_id : null;
        }

        $teamsByRegion = array();
        foreach ($teams as $team) {
            $propName = $team->property_name ?: 'Chưa xác định';
            $propId = $team->property_id;
            $regionId = isset($propertyRegionMap[$propId]) ? $propertyRegionMap[$propId] : null;
            $regionName = ($regionId && isset($regionalMap[$regionId])) ? $regionalMap[$regionId] : 'Chưa phân cụm';

            if (!isset($teamsByRegion[$regionId])) {
                $teamsByRegion[$regionId] = array(
                    'region_id' => $regionId,
                    'region_name' => $regionName,
                    'properties' => array(),
                );
            }

            if (!isset($teamsByRegion[$regionId]['properties'][$propId])) {
                $teamsByRegion[$regionId]['properties'][$propId] = array(
                    'property_name' => $propName,
                    'teams' => array(),
                );
            }

            $teamsByRegion[$regionId]['properties'][$propId]['teams'][] = array(
                'id' => $team->id,
                'team_name' => $team->team_name,
                'name' => isset($team->name) ? $team->name : '',
                'is_alliance' => $team->is_alliance,
                'status' => $team->status,
                'member_count' => isset($team->member_count) ? $team->member_count : 0,
            );
        }

        // Convert properties từ associative array sang indexed array
        foreach ($teamsByRegion as &$region) {
            $region['properties'] = array_values($region['properties']);
        }
        unset($region);

        $eventName = '';
        $sportName = '';
        $eventList = Events::getActiveList();
        if (isset($eventList[$eventId])) {
            $eventName = $eventList[$eventId];
        }
        $sports = Sports::getApiDataProvider(array('is_active' => 1), 100)->getData();
        foreach ($sports as $sport) {
            if ($sport->id == $sportId) {
                $sportName = $sport->name;
                break;
            }
        }

        // Lấy danh sách khu vực có đội để hiển thị filter
        $regionList = array();
        foreach ($teamsByRegion as $regionId => $regionData) {
            $regionList[$regionId] = $regionData['region_name'];
        }

        $this->renderPartial('_view_by_sport', array(
            'sportName' => $sportName,
            'eventName' => $eventName,
            'teamsByRegion' => array_values($teamsByRegion),
            'regionList' => $regionList,
        ));
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

    // public function actionAdmin()
    // {
    //     $model = new SportTeams('search');
    //     $model->unsetAttributes();

    //     $params = array();
    //     if (isset($_GET['SportTeams'])) {
    //         $model->setAttributes($_GET['SportTeams']);
    //         foreach ($_GET['SportTeams'] as $key => $value) {
    //             if ($value !== null && $value !== '') {
    //                 $params[$key] = $value;
    //             }
    //         }
    //     }

    //     $dataProvider = SportTeams::getApiDataProvider($params);
    //     $events = Events::getActiveList();
    //     $sports = Sports::getApiDataProvider(array('is_active' => 1), 100)->getData();
    //     $properties = Properties::getListForDropdown();

    //     $this->render('admin', array(
    //         'model' => $model,
    //         'dataProvider' => $dataProvider,
    //         'events' => $events,
    //         'sports' => $sports,
    //         'properties' => $properties,
    //     ));
    // }

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

    public function actionGetPropertiesByEvent()
    {
        $eventId = Yii::app()->request->getQuery('event_id');
        $result = array();

        if ($eventId) {
            $eventUnits = EventUnits::getByEventId($eventId);
            $propertyIds = array();
            foreach ($eventUnits as $eu) {
                if (isset($eu['property_id'])) {
                    $propertyIds[] = $eu['property_id'];
                }
            }

            $allProperties = Properties::getListForDropdown();
            foreach ($allProperties as $id => $name) {
                if (in_array($id, $propertyIds)) {
                    $result[] = array(
                        'id' => $id,
                        'name' => $name,
                    );
                }
            }
        }

        header('Content-Type: application/json');
        echo json_encode(array('success' => true, 'data' => $result));
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
