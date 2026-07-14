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

        $propertiesData = Properties::getApiDataProvider(array('is_active' => 1), 500)->getData();
        $regionals = Regionals::getApiDataProvider(array(), 100)->getData();

        $regionalMap = array();
        foreach ($regionals as $r) {
            $regionalMap[$r->id] = $r->name;
        }

        // Sắp xếp các đơn vị theo cụm trước, sau đó theo bảng chữ cái tên đơn vị
        usort($propertiesData, function ($a, $b) use ($regionalMap) {
            $regIdA = isset($a->region_id) ? $a->region_id : null;
            $regIdB = isset($b->region_id) ? $b->region_id : null;

            $rA = ($regIdA && isset($regionalMap[$regIdA])) ? $regionalMap[$regIdA] : '';
            $rB = ($regIdB && isset($regionalMap[$regIdB])) ? $regionalMap[$regIdB] : '';

            // Đẩy cụm rỗng xuống cuối
            if ($rA === '' && $rB !== '') return 1;
            if ($rB === '' && $rA !== '') return -1;

            $cmp = strnatcasecmp($rA, $rB);
            if ($cmp === 0) {
                return strnatcasecmp($a->name, $b->name);
            }
            return $cmp;
        });

        $organizations = array();
        foreach ($propertiesData as $p) {
            $regId = isset($p->region_id) ? $p->region_id : null;
            $rName = ($regId && isset($regionalMap[$regId])) ? $regionalMap[$regId] : 'Chưa phân cụm';
            $organizations[$p->id] = '[' . $rName . '] ' . $p->name;
        }

        $this->render('overview', array(
            'events' => $events,
            'competitions' => $competitions,
            'organizations' => $organizations,
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
                'registration_stats' => array(
                    'submitted' => 0,
                    'not_submitted' => 0,
                    'approved' => 0,
                    'not_approved' => 0,
                ),
            ));
            Yii::app()->end();
        }

        $registrationsRes = Registrations::getApiDataProvider(array(
            'event_id' => $eventId,
            'period_id' => 2,
            'per_page' => 10000,
        ), 10000)->getData();

        // Thống kê đăng ký theo trạng thái
        $regSubmitted = 0;
        $regNotSubmitted = 0;
        $regApproved = 0;
        $regNotApproved = 0;
        foreach ($registrationsRes as $reg) {
            if (isset($reg->deleted_at) && $reg->deleted_at !== null && $reg->deleted_at !== '') {
                continue;
            }
            $status = isset($reg->status) ? (int)$reg->status : 0;
            if ($status == Registrations::STATUS_SUBMITTED || $status == Registrations::STATUS_APPROVED || $status == Registrations::STATUS_REJECTED || $status == Registrations::STATUS_RETURNED) {
                $regSubmitted++;
            } else {
                $regNotSubmitted++;
            }
            if ($status == Registrations::STATUS_APPROVED) {
                $regApproved++;
            } else {
                $regNotApproved++;
            }
        }

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
            'registration_stats' => array(
                'submitted' => $regSubmitted,
                'not_submitted' => $regNotSubmitted,
                'approved' => $regApproved,
                'not_approved' => $regNotApproved,
            ),
        ));
        Yii::app()->end();
    }

    public function actionGetOrganizationStats()
    {
        $eventId = Yii::app()->request->getQuery('event_id');
        $filterPropertyId = Yii::app()->request->getQuery('organization_id');

        if (empty($eventId)) {
            $activeEvents = Events::getActiveList();
            if (!empty($activeEvents)) {
                $eventId = key($activeEvents);
            }
        }

        if (empty($eventId)) {
            header('Content-Type: application/json');
            echo json_encode(array('success' => true, 'organizations' => array()));
            Yii::app()->end();
        }

        $params = array('event_id' => $eventId);
        if (!empty($filterPropertyId)) {
            $params['property_id'] = $filterPropertyId;
        }

        $apiData = CompetitionRegistrations::getListByProperty($params);

        $properties = Properties::getApiDataProvider(array(), 500)->getData();
        $propertyNameMap = array();
        $propertyRegionMap = array();
        foreach ($properties as $p) {
            $propertyNameMap[$p->id] = $p->name;
            $propertyRegionMap[$p->id] = isset($p->region_id) ? $p->region_id : null;
        }

        $regionals = Regionals::getApiDataProvider(array(), 100)->getData();
        $regionalMap = array();
        foreach ($regionals as $r) {
            $regionalMap[$r->id] = $r->name;
        }

        $rawData = isset($apiData['data']) && is_array($apiData['data']) ? $apiData['data'] : (is_array($apiData) ? $apiData : array());

        $orgStats = array();

        foreach ($rawData as $item) {
            if (isset($item['deleted_at']) && $item['deleted_at'] !== null && $item['deleted_at'] !== '') {
                continue;
            }

            // Extract property details from registration item
            $propId = isset($item['property_id']) ? $item['property_id'] : null;
            $propName = 'Chưa xác định';
            $regionName = '';

            if (isset($item['attendee'])) {
                $att = $item['attendee'];
                if (is_array($att)) {
                    if (!$propId) {
                        $propId = isset($att['property_id']) ? $att['property_id'] : null;
                    }
                    if (isset($att['property']) && is_array($att['property'])) {
                        if (!$propId) {
                            $propId = isset($att['property']['id']) ? $att['property']['id'] : null;
                        }
                        $propName = isset($att['property']['name']) ? $att['property']['name'] : $propName;
                        if (isset($att['property']['region']) && is_array($att['property']['region'])) {
                            $regionName = isset($att['property']['region']['name']) ? $att['property']['region']['name'] : $regionName;
                        }
                    }
                } else {
                    if (!$propId) {
                        $propId = isset($att->property_id) ? $att->property_id : null;
                    }
                    if (isset($att->property)) {
                        $prop = $att->property;
                        if (is_array($prop)) {
                            if (!$propId) {
                                $propId = isset($prop['id']) ? $prop['id'] : null;
                            }
                            $propName = isset($prop['name']) ? $prop['name'] : $propName;
                            if (isset($prop['region']) && is_array($prop['region'])) {
                                $regionName = isset($prop['region']['name']) ? $prop['region']['name'] : $regionName;
                            }
                        } else {
                            if (!$propId) {
                                $propId = isset($prop->id) ? $prop->id : null;
                            }
                            $propName = isset($prop->name) ? $prop->name : $propName;
                            if (isset($prop->region)) {
                                $reg = $prop->region;
                                if (is_array($reg)) {
                                    $regionName = isset($reg['name']) ? $reg['name'] : $regionName;
                                } else {
                                    $regionName = isset($reg->name) ? $reg->name : $regionName;
                                }
                            }
                        }
                    }
                }
            }

            if (empty($propId)) {
                $propId = 0; // Fallback to 0 if not found
            }

            // Fallback for property name and region from local maps if API returned empty/Chưa xác định
            if ($propName === 'Chưa xác định' && $propId && isset($propertyNameMap[$propId])) {
                $propName = $propertyNameMap[$propId];
            }
            if (empty($regionName) && $propId && isset($propertyRegionMap[$propId])) {
                $regId = $propertyRegionMap[$propId];
                if ($regId && isset($regionalMap[$regId])) {
                    $regionName = $regionalMap[$regId];
                }
            }

            // Filter by property if requested
            if (!empty($filterPropertyId) && $propId != $filterPropertyId) {
                continue;
            }

            if (!isset($orgStats[$propId])) {
                $orgStats[$propId] = array(
                    'id' => $propId,
                    'name' => $propName,
                    'region_name' => $regionName,
                    'contestant_count' => 0,
                    'confirmed_count' => 0,
                );
            }

            $orgStats[$propId]['contestant_count']++;

            $status = isset($item['status']) ? (int)$item['status'] : 0;
            if ($status == CompetitionRegistrations::STATUS_CONFIRMED) {
                $orgStats[$propId]['confirmed_count']++;
            }
        }

        $formattedOrgs = array_values($orgStats);
        usort($formattedOrgs, function ($a, $b) {
            $rA = isset($a['region_name']) ? $a['region_name'] : '';
            $rB = isset($b['region_name']) ? $b['region_name'] : '';

            // Sắp xếp cụm rỗng xuống cuối
            if ($rA === '' && $rB !== '') return 1;
            if ($rB === '' && $rA !== '') return -1;

            $cmp = strnatcasecmp($rA, $rB);
            if ($cmp === 0) {
                return strnatcasecmp($a['name'], $b['name']);
            }
            return $cmp;
        });

        header('Content-Type: application/json');
        echo json_encode(array(
            'success' => true,
            'organizations' => $formattedOrgs,
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

        // Lấy tất cả đăng ký của event để biết thí sinh đăng ký những nghiệp vụ nào
        $allCompRegs = CompetitionRegistrations::getApiDataProvider(array(
            'event_id' => $eventId,
            'per_page' => 10000,
        ), 10000)->getData();

        // Map attendee_id -> danh sách competition đã đăng ký
        $attendeeCompetitions = array();
        foreach ($allCompRegs as $reg) {
            if (isset($reg->deleted_at) && $reg->deleted_at !== null && $reg->deleted_at !== '') {
                continue;
            }
            $attId = $reg->attendee_id;
            if (!isset($attendeeCompetitions[$attId])) {
                $attendeeCompetitions[$attId] = array();
            }
            $compName = isset($reg->competition_name) ? $reg->competition_name : '';
            if (!$compName && isset($reg->competition)) {
                $comp = $reg->competition;
                $compName = is_array($comp) ? (isset($comp['name']) ? $comp['name'] : '') : (isset($comp->name) ? $comp->name : '');
            }
            if ($compName && !in_array($compName, $attendeeCompetitions[$attId])) {
                $attendeeCompetitions[$attId][] = $compName;
            }
        }

        $regionals = Regionals::getApiDataProvider(array(), 100)->getData();
        $regionalMap = array();
        $regionalCodeMap = array();
        foreach ($regionals as $r) {
            $regionalMap[$r->id] = $r->name;
            $regionalCodeMap[$r->id] = isset($r->code) ? $r->code : '';
        }

        $properties = Properties::getApiDataProvider(array('is_active' => 1), 500)->getData();
        $propertyRegionMap = array();
        $propertyNameMap = array();
        $propertyPrefixMap = array();
        foreach ($properties as $p) {
            $propertyRegionMap[$p->id] = isset($p->region_id) ? $p->region_id : null;
            $propertyNameMap[$p->id] = $p->name;
            // Ưu tiên prefix, fallback sang code
            $prefix = '';
            if (isset($p->prefix) && $p->prefix) {
                $prefix = $p->prefix;
            } elseif (isset($p->code) && $p->code) {
                $prefix = $p->code;
            }
            $propertyPrefixMap[$p->id] = $prefix;
        }

        // Nhóm thí sinh theo registration_id để tính team_name (cho thi đội)
        $regIdToPropertyIds = array();
        foreach ($compRegs as $compReg) {
            if (isset($compReg->deleted_at) && $compReg->deleted_at !== null && $compReg->deleted_at !== '') {
                continue;
            }
            $regId = isset($compReg->registration_id) ? $compReg->registration_id : null;
            if (!$regId) continue;

            // Lấy property_id từ nhiều nguồn
            $propId = null;

            // 1. Từ attendee.property_id hoặc attendee.property.id
            if (isset($compReg->attendee)) {
                $att = $compReg->attendee;
                if (is_array($att)) {
                    if (isset($att['property_id'])) {
                        $propId = $att['property_id'];
                    } elseif (isset($att['property']['id'])) {
                        $propId = $att['property']['id'];
                    }
                } else {
                    if (isset($att->property_id)) {
                        $propId = $att->property_id;
                    } elseif (isset($att->property->id)) {
                        $propId = $att->property->id;
                    }
                }
            }

            // 2. Từ registration.property_id
            if (!$propId && isset($compReg->registration)) {
                $reg = $compReg->registration;
                if (is_array($reg)) {
                    $propId = isset($reg['property_id']) ? $reg['property_id'] : null;
                } else {
                    $propId = isset($reg->property_id) ? $reg->property_id : null;
                }
            }

            if ($propId) {
                if (!isset($regIdToPropertyIds[$regId])) {
                    $regIdToPropertyIds[$regId] = array();
                }
                if (!in_array($propId, $regIdToPropertyIds[$regId])) {
                    $regIdToPropertyIds[$regId][] = $propId;
                }
            }
        }

        // Tính team_name cho từng registration_id
        $regIdToTeamName = array();
        foreach ($regIdToPropertyIds as $regId => $propIds) {
            $names = array();
            foreach ($propIds as $propId) {
                // Ưu tiên prefix, fallback property name
                if (isset($propertyPrefixMap[$propId]) && $propertyPrefixMap[$propId]) {
                    $names[] = $propertyPrefixMap[$propId];
                } elseif (isset($propertyNameMap[$propId]) && $propertyNameMap[$propId]) {
                    // Rút gọn tên đơn vị: "Mường Thanh Holiday Đà Lạt" → "MT H. Đà Lạt"
                    $fullName = $propertyNameMap[$propId];
                    $shortName = preg_replace('/Mường Thanh\s*/i', 'MT ', $fullName);
                    $shortName = preg_replace('/Holiday\s*/i', 'H. ', $shortName);
                    $shortName = preg_replace('/Grand\s*/i', 'G. ', $shortName);
                    $shortName = preg_replace('/Luxury\s*/i', 'L. ', $shortName);
                    $names[] = trim($shortName);
                }
            }
            sort($names);
            $regIdToTeamName[$regId] = implode(' - ', $names);
        }

        // Đếm số thành viên mỗi đội (theo registration_id)
        $regIdToMemberCount = array();
        foreach ($compRegs as $compReg) {
            if (isset($compReg->deleted_at) && $compReg->deleted_at !== null && $compReg->deleted_at !== '') {
                continue;
            }
            $regId = isset($compReg->registration_id) ? $compReg->registration_id : null;
            if (!$regId) continue;
            if (!isset($regIdToMemberCount[$regId])) {
                $regIdToMemberCount[$regId] = 0;
            }
            $regIdToMemberCount[$regId]++;
        }

        $contestantsByRegion = array();
        foreach ($compRegs as $compReg) {
            if (isset($compReg->deleted_at) && $compReg->deleted_at !== null && $compReg->deleted_at !== '') {
                continue;
            }

            $propId = isset($compReg->property_id) ? $compReg->property_id : null;
            $propNameFromApi = null;
            $regionIdFromApi = null;
            $regionNameFromApi = null;

            if (isset($compReg->attendee)) {
                $att = $compReg->attendee;
                if (is_array($att)) {
                    if (!$propId) {
                        $propId = isset($att['property_id']) ? $att['property_id'] : null;
                    }
                    if (isset($att['property'])) {
                        $prop = $att['property'];
                        if (is_array($prop)) {
                            if (!$propId) {
                                $propId = isset($prop['id']) ? $prop['id'] : null;
                            }
                            $propNameFromApi = isset($prop['name']) ? $prop['name'] : null;
                            if (isset($prop['region'])) {
                                $reg = $prop['region'];
                                if (is_array($reg)) {
                                    $regionIdFromApi = isset($reg['id']) ? $reg['id'] : null;
                                    $regionNameFromApi = isset($reg['name']) ? $reg['name'] : null;
                                } elseif (is_object($reg)) {
                                    $regionIdFromApi = isset($reg->id) ? $reg->id : null;
                                    $regionNameFromApi = isset($reg->name) ? $reg->name : null;
                                }
                            }
                        } elseif (is_object($prop)) {
                            if (!$propId) {
                                $propId = isset($prop->id) ? $prop->id : null;
                            }
                            $propNameFromApi = isset($prop->name) ? $prop->name : null;
                            if (isset($prop->region)) {
                                $reg = $prop->region;
                                if (is_array($reg)) {
                                    $regionIdFromApi = isset($reg['id']) ? $reg['id'] : null;
                                    $regionNameFromApi = isset($reg['name']) ? $reg['name'] : null;
                                } elseif (is_object($reg)) {
                                    $regionIdFromApi = isset($reg->id) ? $reg->id : null;
                                    $regionNameFromApi = isset($reg->name) ? $reg->name : null;
                                }
                            }
                        }
                    }
                } else {
                    if (!$propId) {
                        $propId = isset($att->property_id) ? $att->property_id : null;
                    }
                    if (isset($att->property)) {
                        $prop = $att->property;
                        if (is_array($prop)) {
                            if (!$propId) {
                                $propId = isset($prop['id']) ? $prop['id'] : null;
                            }
                            $propNameFromApi = isset($prop['name']) ? $prop['name'] : null;
                            if (isset($prop['region'])) {
                                $reg = $prop['region'];
                                if (is_array($reg)) {
                                    $regionIdFromApi = isset($reg['id']) ? $reg['id'] : null;
                                    $regionNameFromApi = isset($reg['name']) ? $reg['name'] : null;
                                } elseif (is_object($reg)) {
                                    $regionIdFromApi = isset($reg->id) ? $reg->id : null;
                                    $regionNameFromApi = isset($reg->name) ? $reg->name : null;
                                }
                            }
                        } else {
                            if (!$propId) {
                                $propId = isset($prop->id) ? $prop->id : null;
                            }
                            $propNameFromApi = isset($prop->name) ? $prop->name : null;
                            if (isset($prop->region)) {
                                $reg = $prop->region;
                                if (is_array($reg)) {
                                    $regionIdFromApi = isset($reg['id']) ? $reg['id'] : null;
                                    $regionNameFromApi = isset($reg['name']) ? $reg['name'] : null;
                                } else {
                                    $regionIdFromApi = isset($reg->id) ? $reg->id : null;
                                    $regionNameFromApi = isset($reg->name) ? $reg->name : null;
                                }
                            }
                        }
                    }
                }
            }

            $propName = isset($propertyNameMap[$propId]) ? $propertyNameMap[$propId] : ($propNameFromApi ?: (isset($compReg->property_name) ? $compReg->property_name : 'Chưa xác định'));

            $regionId = isset($propertyRegionMap[$propId]) ? $propertyRegionMap[$propId] : ($regionIdFromApi ?: null);
            $regionName = ($regionId && isset($regionalMap[$regionId])) ? $regionalMap[$regionId] : ($regionNameFromApi ?: 'Chưa phân cụm');
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

            $attendeeName = '-';
            $attendeePosition = '';
            $attendeeDepartment = '';
            $attendeeGender = '';

            // Lấy từ attendee (array) - đây là nguồn chính
            if (isset($compReg->attendee) && is_array($compReg->attendee)) {
                $att = $compReg->attendee;
                $attendeeName = isset($att['full_name']) ? $att['full_name'] : '-';
                $attendeeGender = isset($att['gender']) ? $att['gender'] : '';

                // Position
                if (isset($att['position']) && is_array($att['position'])) {
                    $attendeePosition = isset($att['position']['name']) ? $att['position']['name'] : '';
                }

                // Department
                if (isset($att['department']) && is_array($att['department'])) {
                    $attendeeDepartment = isset($att['department']['name']) ? $att['department']['name'] : '';
                }
            }

            $attId = $compReg->attendee_id;
            $registeredCompetitions = isset($attendeeCompetitions[$attId]) ? $attendeeCompetitions[$attId] : array();

            $regId = isset($compReg->registration_id) ? $compReg->registration_id : null;
            $teamName = ($regId && isset($regIdToTeamName[$regId])) ? $regIdToTeamName[$regId] : '';
            $memberCount = ($regId && isset($regIdToMemberCount[$regId])) ? $regIdToMemberCount[$regId] : 1;

            $contestantsByRegion[$regionId]['properties'][$propId]['contestants'][] = array(
                'id' => $compReg->id,
                'attendee_id' => $compReg->attendee_id,
                'registration_id' => $regId,
                'candidate_number' => $compReg->candidate_number,
                'attendee_name' => $attendeeName,
                'attendee_position' => $attendeePosition,
                'attendee_department' => $attendeeDepartment,
                'attendee_gender' => $attendeeGender,
                'status' => $compReg->status,
                'registered_at' => $compReg->registered_at,
                'note' => $compReg->note,
                'registered_competitions' => $registeredCompetitions,
                'team_name' => $teamName,
                'member_count' => $memberCount,
            );
        }

        foreach ($contestantsByRegion as &$region) {
            // Sắp xếp các đơn vị trong cụm theo bảng chữ cái
            usort($region['properties'], function ($a, $b) {
                return strnatcasecmp($a['property_name'], $b['property_name']);
            });
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
        $departmentList = array();
        $positionList = array();
        foreach ($contestantsByRegion as $regionData) {
            $regionList[$regionData['region_id']] = $regionData['region_name'];
            // Thu thập danh sách phòng ban và chức danh
            foreach ($regionData['properties'] as $propData) {
                foreach ($propData['contestants'] as $c) {
                    if (!empty($c['attendee_department'])) {
                        $departmentList[$c['attendee_department']] = $c['attendee_department'];
                    }
                    if (!empty($c['attendee_position'])) {
                        // Gộp các chức danh giống nhau (khác hoa/thường)
                        $posKey = mb_strtolower($c['attendee_position'], 'UTF-8');
                        if (!isset($positionList[$posKey])) {
                            $positionList[$posKey] = $c['attendee_position'];
                        }
                    }
                }
            }
        }
        ksort($departmentList);
        ksort($positionList);

        $this->render('view_by_competition', array(
            'competitionName' => $competitionName,
            'eventName' => $eventName,
            'eventId' => $eventId,
            'competitionId' => $competitionId,
            'contestantsByRegion' => array_values($contestantsByRegion),
            'regionList' => $regionList,
            'departmentList' => $departmentList,
            'positionList' => $positionList,
            'competitionsList' => $competitionsList,
        ));
    }

    public function actionExportExcel()
    {
        $eventId = Yii::app()->request->getQuery('event_id');
        $competitionId = Yii::app()->request->getQuery('competition_id');
        $filterRegionId = Yii::app()->request->getQuery('region_id');
        $filterProperty = Yii::app()->request->getQuery('property');
        $filterPosition = Yii::app()->request->getQuery('position');

        $compRegs = CompetitionRegistrations::getApiDataProvider(array(
            'event_id' => $eventId,
            'competition_id' => $competitionId,
            'per_page' => 5000,
        ), 5000)->getData();

        $regionals = Regionals::getApiDataProvider(array(), 100)->getData();
        $regionalMap = array();
        foreach ($regionals as $r) {
            $regionalMap[$r->id] = $r->name;
        }

        $properties = Properties::getApiDataProvider(array('is_active' => 1), 500)->getData();
        $propertyRegionMap = array();
        $propertyNameMap = array();
        foreach ($properties as $p) {
            $propertyRegionMap[$p->id] = isset($p->region_id) ? $p->region_id : null;
            $propertyNameMap[$p->id] = $p->name;
        }

        // Lấy tên cuộc thi
        $competitionName = '';
        $competition = Competitions::fetchFromApi($competitionId);
        if ($competition) {
            $competitionName = $competition->name;
        }

        // Chuẩn bị data
        $rows = array();
        foreach ($compRegs as $compReg) {
            if (isset($compReg->deleted_at) && $compReg->deleted_at) continue;

            $propId = null;
            $propName = '';
            $regionId = null;
            $regionName = '';
            $attendeeName = '-';
            $attendeePosition = '';

            if (isset($compReg->attendee) && is_array($compReg->attendee)) {
                $att = $compReg->attendee;
                $attendeeName = isset($att['full_name']) ? $att['full_name'] : '-';
                if (isset($att['position']) && is_array($att['position'])) {
                    $attendeePosition = isset($att['position']['name']) ? $att['position']['name'] : '';
                }
                if (isset($att['property']) && is_array($att['property'])) {
                    $propId = isset($att['property']['id']) ? $att['property']['id'] : null;
                    $propName = isset($att['property']['name']) ? $att['property']['name'] : '';
                    if (isset($att['property']['region']) && is_array($att['property']['region'])) {
                        $regionId = isset($att['property']['region']['id']) ? $att['property']['region']['id'] : null;
                        $regionName = isset($att['property']['region']['name']) ? $att['property']['region']['name'] : '';
                    }
                }
            }

            // Fallback
            if (!$regionName && $propId && isset($propertyRegionMap[$propId])) {
                $regId = $propertyRegionMap[$propId];
                if ($regId && isset($regionalMap[$regId])) {
                    $regionId = $regId;
                    $regionName = $regionalMap[$regId];
                }
            }

            // Apply filters
            if ($filterRegionId && $regionId != $filterRegionId) continue;
            if ($filterProperty && $propName != $filterProperty) continue;
            if ($filterPosition && mb_strtolower($attendeePosition, 'UTF-8') != mb_strtolower($filterPosition, 'UTF-8')) continue;

            $rows[] = array(
                'region_name' => $regionName,
                'property_name' => $propName,
                'attendee_name' => $attendeeName,
                'attendee_position' => $attendeePosition,
            );
        }

        // Sắp xếp thí sinh theo cụm, đơn vị, họ tên (cụm rỗng xuống cuối)
        usort($rows, function ($a, $b) {
            $rA = $a['region_name'];
            $rB = $b['region_name'];

            if ($rA === '' && $rB !== '') return 1;
            if ($rB === '' && $rA !== '') return -1;

            $cmp = strnatcasecmp($rA, $rB);
            if ($cmp === 0) {
                $cmp = strnatcasecmp($a['property_name'], $b['property_name']);
            }
            if ($cmp === 0) {
                $cmp = strnatcasecmp($a['attendee_name'], $b['attendee_name']);
            }
            return $cmp;
        });

        // Export Excel
        $this->initPHPExcel();
        $excel = new PHPExcel();
        $sheet = $excel->getActiveSheet();
        $sheet->setTitle('Danh sách thí sinh');

        // Header
        $sheet->setCellValue('A1', 'STT');
        $sheet->setCellValue('B1', 'Cụm');
        $sheet->setCellValue('C1', 'Đơn vị');
        $sheet->setCellValue('D1', 'Họ tên');
        $sheet->setCellValue('E1', 'Chức danh');

        $headerStyle = array(
            'font' => array('bold' => true),
            'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'CCCCCC')),
            'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),
        );
        $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);

        // Data
        $rowNum = 2;
        $idx = 1;
        foreach ($rows as $row) {
            $sheet->setCellValue('A' . $rowNum, $idx++);
            $sheet->setCellValue('B' . $rowNum, $row['region_name']);
            $sheet->setCellValue('C' . $rowNum, $row['property_name']);
            $sheet->setCellValue('D' . $rowNum, $row['attendee_name']);
            $sheet->setCellValue('E' . $rowNum, $row['attendee_position']);
            $rowNum++;
        }

        // Auto width
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Output
        $filename = 'ThiSinh_' . preg_replace('/[^a-zA-Z0-9]/', '', $competitionName) . '_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $writer->save('php://output');
        Yii::app()->end();
    }

    public function actionExportAllExcel()
    {
        $eventId = Yii::app()->request->getQuery('event_id');

        if (empty($eventId)) {
            $activeEvents = Events::getActiveList();
            if (!empty($activeEvents)) {
                $eventId = key($activeEvents);
            }
        }

        if (empty($eventId)) {
            throw new CHttpException(400, 'Chưa chọn sự kiện.');
        }

        // Lấy tất cả đăng ký thi của event
        $compRegs = CompetitionRegistrations::getApiDataProvider(array(
            'event_id' => $eventId,
            'per_page' => 10000,
        ), 10000)->getData();

        // Lấy danh sách nghiệp vụ
        $competitionsData = Competitions::getApiDataProvider(array('is_active' => 1), 100)->getData();
        $competitionMap = array();
        foreach ($competitionsData as $comp) {
            $competitionMap[$comp->id] = $comp->name;
        }

        // Lấy thông tin properties và regions
        $regionals = Regionals::getApiDataProvider(array(), 100)->getData();
        $regionalMap = array();
        foreach ($regionals as $r) {
            $regionalMap[$r->id] = $r->name;
        }

        $properties = Properties::getApiDataProvider(array('is_active' => 1), 500)->getData();
        $propertyRegionMap = array();
        $propertyNameMap = array();
        foreach ($properties as $p) {
            $propertyRegionMap[$p->id] = isset($p->region_id) ? $p->region_id : null;
            $propertyNameMap[$p->id] = $p->name;
        }

        // Nhóm thí sinh theo nghiệp vụ
        $dataByCompetition = array();
        foreach ($compRegs as $compReg) {
            if (isset($compReg->deleted_at) && $compReg->deleted_at) continue;

            $compId = $compReg->competition_id;
            $compName = isset($competitionMap[$compId]) ? $competitionMap[$compId] : 'Chưa xác định';

            if (!isset($dataByCompetition[$compId])) {
                $dataByCompetition[$compId] = array(
                    'name' => $compName,
                    'contestants' => array(),
                );
            }

            // Lấy thông tin thí sinh
            $propId = null;
            $propName = '';
            $regionName = '';
            $attendeeName = '-';
            $attendeePosition = '';
            $attendeeDepartment = '';
            $attendeeGender = '';
            $attendeePhone = '';
            $attendeeEmail = '';

            if (isset($compReg->attendee) && is_array($compReg->attendee)) {
                $att = $compReg->attendee;
                $attendeeName = isset($att['full_name']) ? $att['full_name'] : '-';
                $attendeeGender = isset($att['gender']) ? $att['gender'] : '';
                $attendeePhone = isset($att['phone']) ? $att['phone'] : '';
                $attendeeEmail = isset($att['email']) ? $att['email'] : '';

                if (isset($att['position']) && is_array($att['position'])) {
                    $attendeePosition = isset($att['position']['name']) ? $att['position']['name'] : '';
                }
                if (isset($att['department']) && is_array($att['department'])) {
                    $attendeeDepartment = isset($att['department']['name']) ? $att['department']['name'] : '';
                }
                if (isset($att['property']) && is_array($att['property'])) {
                    $propId = isset($att['property']['id']) ? $att['property']['id'] : null;
                    $propName = isset($att['property']['name']) ? $att['property']['name'] : '';
                    if (isset($att['property']['region']) && is_array($att['property']['region'])) {
                        $regionName = isset($att['property']['region']['name']) ? $att['property']['region']['name'] : '';
                    }
                }
            }

            // Fallback
            if (!$regionName && $propId && isset($propertyRegionMap[$propId])) {
                $regId = $propertyRegionMap[$propId];
                if ($regId && isset($regionalMap[$regId])) {
                    $regionName = $regionalMap[$regId];
                }
            }
            if (!$propName && $propId && isset($propertyNameMap[$propId])) {
                $propName = $propertyNameMap[$propId];
            }

            $dataByCompetition[$compId]['contestants'][] = array(
                'candidate_number' => $compReg->candidate_number,
                'region_name' => $regionName,
                'property_name' => $propName,
                'attendee_name' => $attendeeName,
                'attendee_gender' => ($attendeeGender == '1' || $attendeeGender === 1 || $attendeeGender == 'male') ? 'Nam' : (($attendeeGender == '0' || $attendeeGender === 0 || $attendeeGender == 'female') ? 'Nữ' : ''),
                'attendee_position' => $attendeePosition,
                'attendee_department' => $attendeeDepartment,
                'attendee_phone' => $attendeePhone,
                'attendee_email' => $attendeeEmail,
                'status' => $compReg->status == CompetitionRegistrations::STATUS_CONFIRMED ? 'Đã xác nhận' : 'Chờ xác nhận',
            );
        }

        // Sắp xếp nghiệp vụ theo tên
        uasort($dataByCompetition, function ($a, $b) {
            return strnatcasecmp($a['name'], $b['name']);
        });

        // Export Excel
        $this->initPHPExcel();
        $excel = new PHPExcel();
        $excel->removeSheetByIndex(0);

        $sheetIndex = 0;
        foreach ($dataByCompetition as $compId => $compData) {
            $sheet = new PHPExcel_Worksheet($excel, $this->sanitizeSheetName($compData['name']));
            $excel->addSheet($sheet, $sheetIndex);

            // Header
            $headers = array('STT', 'SBD', 'Cụm', 'Đơn vị', 'Họ tên', 'Giới tính', 'Chức danh', 'Phòng ban', 'Điện thoại', 'Email', 'Trạng thái');
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $col++;
            }

            $headerStyle = array(
                'font' => array('bold' => true),
                'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'CCCCCC')),
                'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),
                'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
            );
            $sheet->getStyle('A1:K1')->applyFromArray($headerStyle);

            // Sắp xếp thí sinh theo cụm, đơn vị, tên
            usort($compData['contestants'], function ($a, $b) {
                $cmp = strnatcasecmp($a['region_name'], $b['region_name']);
                if ($cmp === 0) {
                    $cmp = strnatcasecmp($a['property_name'], $b['property_name']);
                }
                if ($cmp === 0) {
                    $cmp = strnatcasecmp($a['attendee_name'], $b['attendee_name']);
                }
                return $cmp;
            });

            // Data
            $rowNum = 2;
            $idx = 1;
            foreach ($compData['contestants'] as $row) {
                $sheet->setCellValue('A' . $rowNum, $idx++);
                $sheet->setCellValue('B' . $rowNum, $row['candidate_number']);
                $sheet->setCellValue('C' . $rowNum, $row['region_name']);
                $sheet->setCellValue('D' . $rowNum, $row['property_name']);
                $sheet->setCellValue('E' . $rowNum, $row['attendee_name']);
                $sheet->setCellValue('F' . $rowNum, $row['attendee_gender']);
                $sheet->setCellValue('G' . $rowNum, $row['attendee_position']);
                $sheet->setCellValue('H' . $rowNum, $row['attendee_department']);
                $sheet->setCellValueExplicit('I' . $rowNum, $row['attendee_phone'], PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->setCellValue('J' . $rowNum, $row['attendee_email']);
                $sheet->setCellValue('K' . $rowNum, $row['status']);
                $rowNum++;
            }

            // Auto width
            foreach (range('A', 'K') as $colLetter) {
                $sheet->getColumnDimension($colLetter)->setAutoSize(true);
            }

            $sheetIndex++;
        }

        // Output
        $filename = 'DanhSachThiSinh_TatCaNghiepVu_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $writer->save('php://output');
        Yii::app()->end();
    }

    private function sanitizeSheetName($name)
    {
        // Excel sheet name max 31 chars, không chứa ký tự đặc biệt
        $name = preg_replace('/[\\\\\/\?\*\[\]:\'"]/', '', $name);
        $name = mb_substr($name, 0, 31, 'UTF-8');
        return $name ?: 'Sheet';
    }

    // Debug action - xem structure data từ API
    public function actionDebugData()
    {
        $eventId = Yii::app()->request->getQuery('event_id', 3);
        $competitionId = Yii::app()->request->getQuery('competition_id', 3);

        // Lấy qua ApiDataProvider để xem structure
        $compRegs = CompetitionRegistrations::getApiDataProvider(array(
            'event_id' => $eventId,
            'competition_id' => $competitionId,
            'per_page' => 2,
        ), 2)->getData();

        $debug = array();
        foreach ($compRegs as $reg) {
            $item = array(
                'id' => $reg->id,
                'attendee_id' => $reg->attendee_id,
                'registration_id' => isset($reg->registration_id) ? $reg->registration_id : null,
            );

            // Check attendee
            if (isset($reg->attendee)) {
                $att = $reg->attendee;
                $item['attendee_type'] = gettype($att);
                if (is_array($att)) {
                    $item['attendee_full_name'] = isset($att['full_name']) ? $att['full_name'] : null;
                    $item['attendee_department'] = isset($att['department']) ? $att['department'] : null;
                    $item['attendee_position'] = isset($att['position']) ? $att['position'] : null;
                } else {
                    $item['attendee_full_name'] = isset($att->full_name) ? $att->full_name : null;
                    $item['attendee_department'] = isset($att->department) ? $att->department : null;
                    $item['attendee_position'] = isset($att->position) ? $att->position : null;
                }
            }
            $debug[] = $item;
        }

        header('Content-Type: application/json');
        echo json_encode($debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        Yii::app()->end();
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
                $attendeeName = isset($att['full_name']) ? $att['full_name'] : $attendeeName;
                $attendeeGender = isset($att['gender']) ? $att['gender'] : $attendeeGender;
                if (isset($att['position'])) {
                    $pos = $att['position'];
                    if (is_array($pos) && isset($pos['name'])) {
                        $attendeePosition = $pos['name'];
                    } elseif (is_object($pos) && isset($pos->name)) {
                        $attendeePosition = $pos->name;
                    } elseif (is_string($pos)) {
                        $attendeePosition = $pos;
                    }
                }
                if (isset($att['property'])) {
                    $prop = $att['property'];
                    if (is_array($prop) && isset($prop['name'])) {
                        $propertyName = $prop['name'];
                    } elseif (is_object($prop) && isset($prop->name)) {
                        $propertyName = $prop->name;
                    } elseif (is_string($prop)) {
                        $propertyName = $prop;
                    }
                } else {
                    $propertyName = isset($att['property_name']) ? $att['property_name'] : $propertyName;
                }
            } else {
                $attendeeName = isset($att->full_name) ? $att->full_name : $attendeeName;
                $attendeeGender = isset($att->gender) ? $att->gender : $attendeeGender;
                if (isset($att->position)) {
                    $pos = $att->position;
                    if (is_array($pos) && isset($pos['name'])) {
                        $attendeePosition = $pos['name'];
                    } elseif (is_object($pos) && isset($pos->name)) {
                        $attendeePosition = $pos->name;
                    } elseif (is_string($pos)) {
                        $attendeePosition = $pos;
                    }
                }
                if (isset($att->property)) {
                    $prop = $att->property;
                    if (is_array($prop) && isset($prop['name'])) {
                        $propertyName = $prop['name'];
                    } elseif (is_object($prop) && isset($prop->name)) {
                        $propertyName = $prop->name;
                    } elseif (is_string($prop)) {
                        $propertyName = $prop;
                    }
                } else {
                    $propertyName = isset($att->property_name) ? $att->property_name : $propertyName;
                }
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

    public function actionAjaxGetPropertyContestants()
    {
        $eventId = Yii::app()->request->getQuery('event_id');
        $propertyId = Yii::app()->request->getQuery('property_id');

        header('Content-Type: application/json');

        if (empty($eventId) || empty($propertyId)) {
            echo json_encode(array('success' => false, 'message' => 'Thiếu thông tin sự kiện hoặc đơn vị.'));
            Yii::app()->end();
        }

        // Fetch all registrations for the event by property
        $apiData = CompetitionRegistrations::getListByProperty(array(
            'event_id' => $eventId,
            'property_id' => $propertyId,
        ));

        $rawData = isset($apiData['data']) && is_array($apiData['data']) ? $apiData['data'] : (is_array($apiData) ? $apiData : array());

        // Map all registrations of the event to get the complete registered competitions list for each attendee.
        $allCompRegs = CompetitionRegistrations::getApiDataProvider(array(
            'event_id' => $eventId,
            'per_page' => 10000,
        ), 10000)->getData();

        // Map attendee_id -> array of registered competition names
        $attendeeCompetitions = array();
        foreach ($allCompRegs as $reg) {
            if (isset($reg->deleted_at) && $reg->deleted_at !== null && $reg->deleted_at !== '') {
                continue;
            }
            $attId = $reg->attendee_id;
            if (!isset($attendeeCompetitions[$attId])) {
                $attendeeCompetitions[$attId] = array();
            }
            $compName = isset($reg->competition_name) ? $reg->competition_name : '';
            if (!$compName && isset($reg->competition)) {
                $comp = $reg->competition;
                $compName = is_array($comp) ? (isset($comp['name']) ? $comp['name'] : '') : (isset($comp->name) ? $comp->name : '');
            }
            if ($compName && !in_array($compName, $attendeeCompetitions[$attId])) {
                $attendeeCompetitions[$attId][] = $compName;
            }
        }

        // Map local properties map for fallback name
        $properties = Properties::getApiDataProvider(array(), 500)->getData();
        $propertyNameMap = array();
        foreach ($properties as $p) {
            $propertyNameMap[$p->id] = $p->name;
        }

        $contestants = array();
        foreach ($rawData as $item) {
            if (isset($item['deleted_at']) && $item['deleted_at'] !== null && $item['deleted_at'] !== '') {
                continue;
            }

            // Verify property matches
            $itemPropId = isset($item['property_id']) ? $item['property_id'] : null;
            if (isset($item['attendee']) && is_array($item['attendee']) && !$itemPropId) {
                $itemPropId = isset($item['attendee']['property_id']) ? $item['attendee']['property_id'] : null;
            }
            if ($itemPropId && $itemPropId != $propertyId) {
                continue;
            }

            $attendeeName = isset($item['attendee_name']) ? $item['attendee_name'] : '-';
            $attendeePosition = '';

            if (isset($item['position'])) {
                $pos = $item['position'];
                if (is_array($pos) && isset($pos['name'])) {
                    $attendeePosition = $pos['name'];
                } elseif (is_object($pos) && isset($pos->name)) {
                    $attendeePosition = $pos->name;
                }
            }

            if (isset($item['attendee'])) {
                $att = $item['attendee'];
                if (is_array($att)) {
                    $attendeeName = isset($att['full_name']) ? $att['full_name'] : $attendeeName;
                    if (empty($attendeePosition) && isset($att['position'])) {
                        $pos = $att['position'];
                        $attendeePosition = is_array($pos) ? (isset($pos['name']) ? $pos['name'] : '') : (isset($pos->name) ? $pos->name : '');
                    }
                }
            }

            $status = isset($item['status']) ? (int)$item['status'] : 0;
            $statusLabel = CompetitionRegistrations::getStatusLabel($status);

            $attId = isset($item['attendee_id']) ? $item['attendee_id'] : null;
            $registeredComps = ($attId && isset($attendeeCompetitions[$attId])) ? $attendeeCompetitions[$attId] : array();

            $contestants[] = array(
                'id' => isset($item['id']) ? $item['id'] : null,
                'name' => $attendeeName,
                'position' => $attendeePosition,
                'candidate_number' => isset($item['candidate_number']) ? $item['candidate_number'] : null,
                'status_label' => $statusLabel,
                'status' => $status,
                'registered_competitions' => $registeredComps,
            );
        }

        // Sort contestants alphabetically by name
        usort($contestants, function ($a, $b) {
            return strnatcasecmp($a['name'], $b['name']);
        });

        echo json_encode(array(
            'success' => true,
            'propertyName' => isset($propertyNameMap[$propertyId]) ? $propertyNameMap[$propertyId] : 'Đơn vị',
            'contestants' => $contestants,
        ));
        Yii::app()->end();
    }

    protected function initPHPExcel()
    {
        $phpExcelPath = Yii::getPathOfAlias('application.extensions.phpexcel.Classes');
        if (!defined('PHPEXCEL_ROOT')) {
            define('PHPEXCEL_ROOT', $phpExcelPath . DIRECTORY_SEPARATOR);
        }
        spl_autoload_register(function ($class) {
            if (strpos($class, 'PHPExcel') === 0) {
                $file = PHPEXCEL_ROOT . str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
                if (file_exists($file)) {
                    require_once $file;
                    return true;
                }
            }
            return false;
        }, true, true);
        require_once $phpExcelPath . '/PHPExcel.php';
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
