<?php

class CompetitionRegistrationsController extends AdminController
{
    public function actionView($id)
    {
        $model = $this->loadModelById($id);
        $this->render('view', array(
            'model' => $model,
        ));
    }

    public function actionCreate()
    {
        $model = new CompetitionRegistrations;

        if (isset($_POST['CompetitionRegistrations'])) {
            $model->setAttributes($_POST['CompetitionRegistrations']);
            if ($model->validate()) {
                $model->status = CompetitionRegistrations::STATUS_PENDING;
                $model->registered_at = time();
                $result = $model->storeViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Đăng ký thi thành công.');
                    $newId = isset($result['data']['id']) ? $result['data']['id'] : null;
                    $this->redirect($newId ? array('view', 'id' => $newId) : array('admin'));
                } else {
                    $errorMsg = $result['error'] ?: 'Không thể đăng ký.';
                    if (isset($result['data']['errors'])) {
                        $errorMsg .= ' Chi tiết: ' . json_encode($result['data']['errors']);
                    }
                    $model->addError('attendee_id', $errorMsg);
                }
            }
        }

        $competitions = Competitions::getActiveList();
        $this->render('create', array(
            'model' => $model,
            'competitions' => $competitions,
        ));
    }

    public function actionUpdate($id)
    {
        $model = $this->loadModelById($id);

        if (isset($_POST['CompetitionRegistrations'])) {
            $model->setAttributes($_POST['CompetitionRegistrations']);

            if ($model->validate()) {
                $result = $model->updateViaApi();

                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Cập nhật đăng ký thành công.');
                    $this->redirect(array('view', 'id' => $id));
                } else {
                    $model->addError('attendee_id', $result['error'] ?: 'Không thể cập nhật.');
                }
            }
        }

        $competitions = Competitions::getActiveList();
        $this->render('update', array(
            'model' => $model,
            'competitions' => $competitions,
        ));
    }

    public function actionDelete($id)
    {
        if (Yii::app()->getRequest()->getIsPostRequest()) {
            $result = CompetitionRegistrations::deleteViaApi($id);

            if ($result['success']) {
                Yii::app()->user->setFlash('success', 'Xóa đăng ký thành công.');
            } else {
                Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể xóa đăng ký.');
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
    //     $model = new CompetitionRegistrations('search');
    //     $model->unsetAttributes();

    //     if (isset($_GET['CompetitionRegistrations'])) {
    //         $model->setAttributes($_GET['CompetitionRegistrations']);
    //     }

    //     $params = array();
    //     foreach ($model->attributes as $key => $value) {
    //         if ($value !== null && $value !== '') {
    //             $params[$key] = $value;
    //         }
    //     }

    //     $dataProvider = CompetitionRegistrations::getApiDataProvider($params);
    //     $competitions = Competitions::getActiveList();

    //     $this->render('admin', array(
    //         'model' => $model,
    //         'dataProvider' => $dataProvider,
    //         'competitions' => $competitions,
    //     ));
    // }

    public function actionConfirm($id)
    {
        if (Yii::app()->getRequest()->getIsPostRequest()) {
            $result = CompetitionRegistrations::confirmViaApi($id);

            if ($result['success']) {
                Yii::app()->user->setFlash('success', 'Xác nhận đăng ký thành công.');
            } else {
                Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể xác nhận.');
            }

            $this->redirect(array('view', 'id' => $id));
        } else {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }
    }

    public function actionAdmin()
    {
        $events = Events::getActiveList();
        $competitions = Competitions::getActiveList();

        $this->render('overview', array(
            'events' => $events,
            'competitions' => $competitions,
        ));
    }

    public function actionGetOverviewStats()
    {
        $eventId = Yii::app()->request->getQuery('event_id');
        if (empty($eventId)) {
            $activeEvents = Events::getActiveList();
            if (!empty($activeEvents)) {
                $eventId = key($activeEvents);
            }
        }

        if (empty($eventId)) {
            header('Content-Type: application/json');
            echo json_encode(array(
                'success' => true,
                'total_contestants' => 0,
                'competitions' => array(),
            ));
            Yii::app()->end();
        }

        $registrationsRes = Registrations::getApiDataProvider(array(
            'event_id' => $eventId,
            'per_page' => 1000,
        ), 1000)->getData();

        $activeRegsMap = array();
        foreach ($registrationsRes as $reg) {
            if (isset($reg->deleted_at) && $reg->deleted_at !== null && $reg->deleted_at !== '') {
                continue;
            }
            $activeRegsMap[$reg->id] = true;
        }

        $compRegsRes = CompetitionRegistrations::getApiDataProvider(array(
            'event_id' => $eventId,
            'per_page' => 5000,
        ), 5000)->getData();

        $competitionStats = array();
        $totalContestants = 0;
        $confirmedCount = 0;
        $pendingCount = 0;

        foreach ($compRegsRes as $compReg) {
            if (isset($compReg->deleted_at) && $compReg->deleted_at !== null && $compReg->deleted_at !== '') {
                continue;
            }

            $compId = $compReg->competition_id;
            $compName = 'Chưa xác định';
            if (isset($compReg->competition_name)) {
                $compName = $compReg->competition_name;
            } elseif (isset($compReg->competition)) {
                if (is_array($compReg->competition)) {
                    $compName = $compReg->competition['name'];
                } else {
                    $compName = $compReg->competition->name;
                }
            }

            if (!isset($competitionStats[$compId])) {
                $competitionStats[$compId] = array(
                    'id' => $compId,
                    'name' => $compName,
                    'contestant_count' => 0,
                    'confirmed_count' => 0,
                    'pending_count' => 0,
                );
            }

            $competitionStats[$compId]['contestant_count']++;
            $totalContestants++;

            if ($compReg->status == CompetitionRegistrations::STATUS_CONFIRMED) {
                $competitionStats[$compId]['confirmed_count']++;
                $confirmedCount++;
            } else if ($compReg->status == CompetitionRegistrations::STATUS_PENDING) {
                $competitionStats[$compId]['pending_count']++;
                $pendingCount++;
            }
        }

        $formattedCompetitions = array_values($competitionStats);
        usort($formattedCompetitions, function ($a, $b) {
            return strnatcasecmp($a['name'], $b['name']);
        });

        header('Content-Type: application/json');
        echo json_encode(array(
            'success' => true,
            'total_contestants' => $totalContestants,
            'confirmed_count' => $confirmedCount,
            'pending_count' => $pendingCount,
            'competitions' => $formattedCompetitions,
        ));
        Yii::app()->end();
    }

    public function actionViewByCompetition()
    {
        $eventId = Yii::app()->request->getQuery('event_id');
        $competitionId = Yii::app()->request->getQuery('competition_id');

        $compRegs = CompetitionRegistrations::getApiDataProvider(array(
            'event_id' => $eventId,
            'competition_id' => $competitionId,
            'per_page' => 5000,
        ), 5000)->getData();

        $regionals = Regionals::getApiDataProvider(array(), 100)->getData();
        $regionalMap = array();
        $regionalCodeMap = array();
        foreach ($regionals as $r) {
            $regionalMap[$r->id] = $r->name;
            $regionalCodeMap[$r->id] = isset($r->code) ? $r->code : '';
        }

        $properties = Properties::getApiDataProvider(array(), 500)->getData();
        $propertyRegionMap = array();
        $propertyNameMap = array();
        foreach ($properties as $p) {
            $propertyRegionMap[$p->id] = isset($p->region_id) ? $p->region_id : null;
            $propertyNameMap[$p->id] = $p->name;
        }

        $contestantsByRegion = array();
        foreach ($compRegs as $compReg) {
            if (isset($compReg->deleted_at) && $compReg->deleted_at !== null && $compReg->deleted_at !== '') {
                continue;
            }

            $propId = isset($compReg->property_id) ? $compReg->property_id : null;
            if (!$propId && isset($compReg->attendee)) {
                $att = $compReg->attendee;
                $propId = is_array($att) ? (isset($att['property_id']) ? $att['property_id'] : null) : (isset($att->property_id) ? $att->property_id : null);
            }
            $propName = isset($propertyNameMap[$propId]) ? $propertyNameMap[$propId] : (isset($compReg->property_name) ? $compReg->property_name : 'Chưa xác định');

            $regionId = isset($propertyRegionMap[$propId]) ? $propertyRegionMap[$propId] : null;
            $regionName = ($regionId && isset($regionalMap[$regionId])) ? $regionalMap[$regionId] : 'Chưa phân cụm';
            $regionCode = ($regionId && isset($regionalCodeMap[$regionId])) ? $regionalCodeMap[$regionId] : 'ZZZ';

            if (!isset($contestantsByRegion[$regionId])) {
                $contestantsByRegion[$regionId] = array(
                    'region_id' => $regionId,
                    'region_name' => $regionName,
                    'region_code' => $regionCode,
                    'properties' => array(),
                );
            }

            if (!isset($contestantsByRegion[$regionId]['properties'][$propId])) {
                $contestantsByRegion[$regionId]['properties'][$propId] = array(
                    'property_name' => $propName,
                    'contestants' => array(),
                );
            }

            $attendeeName = isset($compReg->attendee_name) ? $compReg->attendee_name : '-';
            $attendeePosition = isset($compReg->attendee_position) ? $compReg->attendee_position : '';
            $attendeeGender = isset($compReg->attendee_gender) ? $compReg->attendee_gender : '';
            if (isset($compReg->attendee)) {
                $att = $compReg->attendee;
                if (is_array($att)) {
                    $attendeeName = isset($att['full_name']) ? $att['full_name'] : $attendeeName;
                    $attendeePosition = isset($att['position']) ? $att['position'] : $attendeePosition;
                    $attendeeGender = isset($att['gender']) ? $att['gender'] : $attendeeGender;
                } else {
                    $attendeeName = isset($att->full_name) ? $att->full_name : $attendeeName;
                    $attendeePosition = isset($att->position) ? $att->position : $attendeePosition;
                    $attendeeGender = isset($att->gender) ? $att->gender : $attendeeGender;
                }
            }

            $contestantsByRegion[$regionId]['properties'][$propId]['contestants'][] = array(
                'id' => $compReg->id,
                'attendee_id' => $compReg->attendee_id,
                'candidate_number' => $compReg->candidate_number,
                'attendee_name' => $attendeeName,
                'attendee_position' => $attendeePosition,
                'attendee_gender' => $attendeeGender,
                'status' => $compReg->status,
                'registered_at' => $compReg->registered_at,
                'note' => $compReg->note,
            );
        }

        foreach ($contestantsByRegion as &$region) {
            $region['properties'] = array_values($region['properties']);
        }
        unset($region);

        $eventName = '';
        $competitionName = '';
        $eventList = Events::getActiveList();
        if (isset($eventList[$eventId])) {
            $eventName = $eventList[$eventId];
        }
        $competition = Competitions::fetchFromApi($competitionId);
        if ($competition) {
            $competitionName = $competition->name;
        }

        $competitionsList = array();
        $activeComps = Competitions::getApiDataProvider(array('is_active' => 1), 100)->getData();
        foreach ($activeComps as $comp) {
            $competitionsList[] = array(
                'id' => $comp->id,
                'name' => $comp->name,
            );
        }
        usort($competitionsList, function ($a, $b) {
            return strnatcasecmp($a['name'], $b['name']);
        });

        uasort($contestantsByRegion, function ($a, $b) {
            return strcmp($a['region_code'], $b['region_code']);
        });

        $regionList = array();
        foreach ($contestantsByRegion as $regionData) {
            $regionList[$regionData['region_id']] = $regionData['region_name'];
        }

        $this->render('view_by_competition', array(
            'competitionName' => $competitionName,
            'eventName' => $eventName,
            'eventId' => $eventId,
            'competitionId' => $competitionId,
            'contestantsByRegion' => array_values($contestantsByRegion),
            'regionList' => $regionList,
            'competitionsList' => $competitionsList,
        ));
    }

    public function actionAjaxView()
    {
        $id = Yii::app()->request->getQuery('id');
        header('Content-Type: application/json');

        $model = CompetitionRegistrations::fetchFromApi($id);
        if (!$model) {
            echo json_encode(array('success' => false, 'message' => 'Không tìm thấy'));
            Yii::app()->end();
        }

        $attendeeName = $model->attendee_name ?? '-';
        $attendeePosition = $model->attendee_position ?? '';
        $attendeeGender = $model->attendee_gender ?? '';
        $propertyName = $model->property_name ?? '';
        $competitionName = $model->competition_name ?? '';

        if (isset($model->attendee)) {
            $att = $model->attendee;
            if (is_array($att)) {
                $attendeeName = $att['full_name'] ?? $attendeeName;
                $attendeePosition = $att['position'] ?? $attendeePosition;
                $attendeeGender = $att['gender'] ?? $attendeeGender;
                $propertyName = $att['property_name'] ?? $propertyName;
            } else {
                $attendeeName = $att->full_name ?? $attendeeName;
                $attendeePosition = $att->position ?? $attendeePosition;
                $attendeeGender = $att->gender ?? $attendeeGender;
                $propertyName = $att->property_name ?? $propertyName;
            }
        }
        if (isset($model->competition)) {
            $comp = $model->competition;
            $competitionName = is_array($comp) ? ($comp['name'] ?? $competitionName) : ($comp->name ?? $competitionName);
        }

        echo json_encode(array(
            'success' => true,
            'data' => array(
                'id' => $model->id,
                'candidate_number' => $model->candidate_number,
                'attendee_name' => $attendeeName,
                'attendee_position' => $attendeePosition,
                'attendee_gender' => $attendeeGender,
                'property_name' => $propertyName,
                'competition_name' => $competitionName,
                'status' => $model->status,
                'status_label' => CompetitionRegistrations::getStatusLabel($model->status),
                'registered_at' => MyHelper::formatDateTime($model->registered_at),
                'note' => $model->note,
            ),
        ));
        Yii::app()->end();
    }

    protected function loadModelById($id)
    {
        $model = CompetitionRegistrations::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy đăng ký.');
        }
        return $model;
    }
}
