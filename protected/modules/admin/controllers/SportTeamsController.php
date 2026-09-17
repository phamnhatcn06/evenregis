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

        $this->render('admin', array(
            'events' => $events,
            'sports' => $sports,
            'properties' => $properties,
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
                'total_teams' => 0,
                'total_athletes' => 0,
                'sports' => array(),
            ));
            Yii::app()->end();
        }

        // 1. Fetch active sports - filter theo event_sports của event
        $eventSportsList = EventSports::getByEventId($eventId);
        $activeSportIds = array();
        foreach ($eventSportsList as $es) {
            $sportId = isset($es['sport_id']) ? $es['sport_id'] : null;
            if ($sportId) {
                $activeSportIds[$sportId] = true;
            }
        }

        $sportsRes = Sports::getApiDataProvider(array('is_active' => 1), 500)->getData();
        $sportStats = array();
        foreach ($sportsRes as $sport) {
            // Chỉ include môn được cấu hình trong event_sports (nếu có cấu hình)
            if (!empty($activeSportIds) && !isset($activeSportIds[$sport->id])) {
                continue;
            }
            $sportStats[$sport->id] = array(
                'id' => $sport->id,
                'name' => $sport->name,
                'team_count' => 0,
                'attendee_ids' => array(),
            );
        }

        // 2. Fetch registrations for checking deleted status and not draft
        $registrationsRes = Registrations::getApiDataProvider(array(
            'event_id' => $eventId,
            'per_page' => 1000,
        ), 1000)->getData();

        $activeRegsMap = array();
        foreach ($registrationsRes as $reg) {
            if (isset($reg->deleted_at) && $reg->deleted_at !== null && $reg->deleted_at !== '') {
                continue;
            }
            $status = isset($reg->status) ? (int)$reg->status : 0;
            if ($status === Registrations::STATUS_DRAFT) {
                continue;
            }
            $activeRegsMap[$reg->id] = true;
        }

        // 3. Fetch sport teams
        $teamsRes = SportTeams::getApiDataProvider(array(
            'event_id' => $eventId,
            'per_page' => 1000,
        ), 1000)->getData();

        $activeTeamsMap = array();
        $singleTeamCount = 0;
        $allianceTeamCount = 0;
        foreach ($teamsRes as $team) {
            if (isset($team->deleted_at) && $team->deleted_at !== null && $team->deleted_at !== '') {
                continue;
            }
            if ($team->status == SportTeams::STATUS_CANCELLED) {
                continue;
            }
            if (!isset($activeRegsMap[$team->registration_id])) {
                continue;
            }
            // Filter theo event_sports nếu có cấu hình
            if (!empty($activeSportIds) && !isset($activeSportIds[$team->sport_id])) {
                continue;
            }
            $activeTeamsMap[$team->id] = $team;

            if (isset($sportStats[$team->sport_id])) {
                $sportStats[$team->sport_id]['team_count']++;
            }

            if (!empty($team->is_alliance) && $team->is_alliance == 1) {
                $allianceTeamCount++;
            } else {
                $singleTeamCount++;
            }
        }

        // 4. Fetch valid attendees (not deleted, belongs to active registration)
        $validAttendeeIds = array();
        $attParams = array('event_id' => $eventId, 'per_page' => 5000);
        $rawAttendees = Attendees::getApiDataProvider($attParams, 5000)->getData();
        foreach ($rawAttendees as $att) {
            $attDeletedAt = isset($att->deleted_at) ? $att->deleted_at : null;
            if ($attDeletedAt) continue;
            $regId = isset($att->registration_id) ? $att->registration_id : null;
            if ($regId && isset($activeRegsMap[$regId])) {
                $attId = isset($att->id) ? $att->id : null;
                if ($attId) {
                    $validAttendeeIds[$attId] = true;
                }
            }
        }

        // 5. Fetch sport team members
        $membersRes = ApiClient::get(ApiEndpoints::SPORT_TEAM_MEMBER_LIST, array(
            'event_id' => $eventId,
            'per_page' => 5000,
        ));

        $uniqueAttendeeIds = array();
        if ($membersRes['success']) {
            $membersData = isset($membersRes['data']['data']) ? $membersRes['data']['data'] : $membersRes['data'];
            if (is_array($membersData)) {
                foreach ($membersData as $member) {
                    $teamId = isset($member['sport_team_id']) ? $member['sport_team_id'] : null;
                    $attendeeId = isset($member['attendee_id']) ? $member['attendee_id'] : null;
                    if (!$teamId || !$attendeeId || !isset($activeTeamsMap[$teamId])) {
                        continue;
                    }
                    // Chỉ đếm attendee hợp lệ (không bị xóa, thuộc registration active)
                    if (!isset($validAttendeeIds[$attendeeId])) {
                        continue;
                    }

                    $uniqueAttendeeIds[$attendeeId] = true;
                    $team = $activeTeamsMap[$teamId];
                    if (isset($sportStats[$team->sport_id])) {
                        $sportStats[$team->sport_id]['attendee_ids'][$attendeeId] = true;
                    }
                }
            }
        }

        // Format stats per sport
        $formattedSports = array();
        foreach ($sportStats as $sportId => $stats) {
            if ($stats['team_count'] > 0 || count($stats['attendee_ids']) > 0) {
                $formattedSports[] = array(
                    'id' => $stats['id'],
                    'name' => $stats['name'],
                    'team_count' => $stats['team_count'],
                    'athlete_count' => count($stats['attendee_ids']),
                );
            }
        }

        // Sort by sport name naturally
        usort($formattedSports, function ($a, $b) {
            return strnatcasecmp($a['name'], $b['name']);
        });

        // Fetch regionals
        $regionals = Regionals::getApiDataProvider(array('is_active' => 1), 100)->getData();
        $regionalMap = array();
        foreach ($regionals as $reg) {
            $regId = isset($reg->id) ? $reg->id : (isset($reg['id']) ? $reg['id'] : null);
            $regName = isset($reg->name) ? $reg->name : (isset($reg['name']) ? $reg['name'] : '');
            if ($regId) {
                $regionalMap[$regId] = $regName;
            }
        }

        // Fetch properties
        $user = AuthHandler::getUser();
        $userPropertyCode = isset($user['property_code']) ? $user['property_code'] : '';
        $isHO = ($userPropertyCode === '9999' || $userPropertyCode === 9999);
        $userPropertyId = isset($user['property_id']) ? $user['property_id'] : null;

        $properties = array();
        if ($isHO) {
            $properties = Properties::getApiDataProvider(array('is_active' => 1), 1000)->getData();
        } else if ($userPropertyId) {
            $prop = Properties::fetchFromApi($userPropertyId);
            if ($prop) {
                $properties = array($prop);
            }
        }

        $propertyRegionalMap = array();
        foreach ($properties as $prop) {
            $propId = isset($prop->id) ? $prop->id : (isset($prop['id']) ? $prop['id'] : null);
            $propRegionId = isset($prop->region_id) ? $prop->region_id : null;
            if ($propId) {
                $propertyRegionalMap[$propId] = array(
                    'region_id' => $propRegionId,
                    'region_name' => isset($regionalMap[$propRegionId]) ? $regionalMap[$propRegionId] : 'Chưa phân cụm',
                    'code' => isset($prop->code) ? $prop->code : '',
                    'name' => isset($prop->name) ? $prop->name : '',
                );
            }
        }

        // Fetch all attendees for mapping attendee_id => property_id
        $attParams = array('event_id' => $eventId, 'per_page' => 5000);
        if (!$isHO && $userPropertyId) {
            $attParams['property_id'] = $userPropertyId;
        }
        $rawAttendees = Attendees::getApiDataProvider($attParams, 5000)->getData();
        $attendeePropertyMap = array();
        foreach ($rawAttendees as $att) {
            $attId = isset($att->id) ? $att->id : (isset($att['id']) ? $att['id'] : null);
            $attPropId = isset($att->property_id) ? $att->property_id : (isset($att['property_id']) ? $att['property_id'] : null);
            if ($attId && $attPropId) {
                $attendeePropertyMap[$attId] = $attPropId;
            }
        }

        // Build sports report data: sportsReportData[region_id][property_id][sport_id] = {team_count, member_count}
        $sportsReportData = array();

        // Fetch all active properties for name mapping (useful for the alliance notes)
        $allPropertiesForMap = Properties::getApiDataProvider(array('is_active' => 1), 1000)->getData();
        $propertyNamesMap = array();
        foreach ($allPropertiesForMap as $prop) {
            $pId = isset($prop->id) ? $prop->id : (isset($prop['id']) ? $prop['id'] : null);
            if ($pId) {
                $propertyNamesMap[$pId] = isset($prop->name) ? $prop->name : '';
            }
        }

        // Initialize team counts for host properties
        foreach ($activeTeamsMap as $team) {
            $teamId = isset($team->id) ? $team->id : null;
            $propId = isset($team->property_id) ? $team->property_id : null;
            $spId = isset($team->sport_id) ? $team->sport_id : null;
            if (!$propId || !$spId || !$teamId) continue;

            $regionId = isset($propertyRegionalMap[$propId]) ? $propertyRegionalMap[$propId]['region_id'] : 0;
            if (!$regionId) $regionId = 0;

            if (!isset($sportsReportData[$regionId])) $sportsReportData[$regionId] = array();
            if (!isset($sportsReportData[$regionId][$propId])) $sportsReportData[$regionId][$propId] = array();
            if (!isset($sportsReportData[$regionId][$propId][$spId])) {
                $sportsReportData[$regionId][$propId][$spId] = array('team_count' => 0, 'member_count' => 0, 'note' => '', 'notes' => array());
            }

            $sportsReportData[$regionId][$propId][$spId]['team_count']++;
        }

        // Distribute member counts based on athlete properties and credit alliance partners
        if ($membersRes['success'] && is_array($membersData)) {
            foreach ($activeTeamsMap as $team) {
                $teamId = isset($team->id) ? $team->id : null;
                $hostPropId = isset($team->property_id) ? $team->property_id : null;
                $spId = isset($team->sport_id) ? $team->sport_id : null;
                if (!$hostPropId || !$spId || !$teamId) continue;

                $isAlliance = isset($team->is_alliance) && $team->is_alliance == 1;

                $participatingProperties = array();
                $participatingProperties[$hostPropId] = true;

                foreach ($membersData as $member) {
                    $mTeamId = isset($member['sport_team_id']) ? $member['sport_team_id'] : null;
                    if ($mTeamId != $teamId) continue;

                    $attendeeId = isset($member['attendee_id']) ? $member['attendee_id'] : null;
                    if (!$attendeeId) continue;

                    $memberPropId = isset($attendeePropertyMap[$attendeeId]) ? $attendeePropertyMap[$attendeeId] : null;
                    if (!$memberPropId) continue;

                    $participatingProperties[$memberPropId] = true;

                    // For alliance teams, show count at the registering unit (host)
                    $targetPropId = $isAlliance ? $hostPropId : $memberPropId;

                    $regionId = isset($propertyRegionalMap[$targetPropId]) ? $propertyRegionalMap[$targetPropId]['region_id'] : 0;
                    if (!$regionId) $regionId = 0;

                    if (!isset($sportsReportData[$regionId])) $sportsReportData[$regionId] = array();
                    if (!isset($sportsReportData[$regionId][$targetPropId])) $sportsReportData[$regionId][$targetPropId] = array();
                    if (!isset($sportsReportData[$regionId][$targetPropId][$spId])) {
                        $sportsReportData[$regionId][$targetPropId][$spId] = array('team_count' => 0, 'member_count' => 0, 'note' => '', 'notes' => array());
                    }

                    $sportsReportData[$regionId][$targetPropId][$spId]['member_count']++;
                }

                if ($isAlliance) {
                    // Build alliance note at registering unit
                    $allianceNames = array();
                    if (isset($propertyNamesMap[$hostPropId])) {
                        $allianceNames[] = $propertyNamesMap[$hostPropId];
                    }
                    $allianceOrgIds = isset($team->alliance_org_ids) ? $team->alliance_org_ids : '';
                    if (!empty($allianceOrgIds)) {
                        $partnerIds = array_filter(array_map('trim', explode(',', $allianceOrgIds)));
                        foreach ($partnerIds as $pId) {
                            if (isset($propertyNamesMap[$pId])) {
                                $allianceNames[] = $propertyNamesMap[$pId];
                            }
                        }
                    }
                    if (count($allianceNames) > 1) {
                        $allianceNote = 'Liên quân: ' . implode(' - ', $allianceNames);
                        $hostRegionId = isset($propertyRegionalMap[$hostPropId]) ? $propertyRegionalMap[$hostPropId]['region_id'] : 0;
                        if (!$hostRegionId) $hostRegionId = 0;

                        if (isset($sportsReportData[$hostRegionId][$hostPropId][$spId])) {
                            $sportsReportData[$hostRegionId][$hostPropId][$spId]['notes'][] = $allianceNote;
                        }
                    }
                } else {
                    // For single teams, increment team_count for participating properties that are not the host (alliance partners)
                    foreach ($participatingProperties as $pId => $dummy) {
                        if ($pId == $hostPropId) {
                            continue;
                        }

                        $regionId = isset($propertyRegionalMap[$pId]) ? $propertyRegionalMap[$pId]['region_id'] : 0;
                        if (!$regionId) $regionId = 0;

                        if (!isset($sportsReportData[$regionId])) $sportsReportData[$regionId] = array();
                        if (!isset($sportsReportData[$regionId][$pId])) $sportsReportData[$regionId][$pId] = array();
                        if (!isset($sportsReportData[$regionId][$pId][$spId])) {
                            $sportsReportData[$regionId][$pId][$spId] = array('team_count' => 0, 'member_count' => 0, 'note' => '', 'notes' => array());
                        }

                        $sportsReportData[$regionId][$pId][$spId]['team_count']++;
                    }
                }
            }
        }

        // Finalize note strings
        foreach ($sportsReportData as $regionId => $propData) {
            foreach ($propData as $propId => $sportsData) {
                foreach ($sportsData as $spId => $spData) {
                    if (!empty($spData['notes'])) {
                        $sportsReportData[$regionId][$propId][$spId]['note'] = implode('; ', array_unique($spData['notes']));
                    }
                }
            }
        }

        header('Content-Type: application/json');
        echo json_encode(array(
            'success' => true,
            'total_teams' => count($activeTeamsMap),
            'total_athletes' => count($uniqueAttendeeIds),
            'single_team_count' => $singleTeamCount,
            'alliance_team_count' => $allianceTeamCount,
            'sports' => $formattedSports,
            'regional_map' => $regionalMap,
            'property_regional_map' => $propertyRegionalMap,
            'sports_report_data' => $sportsReportData,
        ));
        Yii::app()->end();
    }


    public function actionViewByProperty()
    {
        $eventId = Yii::app()->request->getQuery('event_id');
        $propertyId = Yii::app()->request->getQuery('property_id');

        $teams = $this->getTeamsForProperty($eventId, $propertyId);

        // Fetch and build attendee map for fast lookup of position and department
        $attendeeMap = array();
        $attRes = ApiClient::get(ApiEndpoints::ATTENDEE_LIST, array(
            'event_id' => $eventId,
            'per_page' => 5000,
        ));
        if ($attRes['success']) {
            $attData = isset($attRes['data']['data']) ? $attRes['data']['data'] : $attRes['data'];
            if (is_array($attData)) {
                foreach ($attData as $att) {
                    if (isset($att['id'])) {
                        $attendeeMap[$att['id']] = $att;
                    }
                }
            }
        }

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
                $pos = '';
                $dept = '';
                if (!empty($m->attendee_id) && isset($attendeeMap[$m->attendee_id])) {
                    $att = $attendeeMap[$m->attendee_id];
                    $pos = !empty($att['position_name']) ? $att['position_name'] : (!empty($att['position']) ? $att['position'] : '');
                    $dept = !empty($att['division_name']) ? $att['division_name'] : (!empty($att['unit_label']) ? $att['unit_label'] : '');
                } else {
                    $pos = $m->attendee_position;
                    if (empty($pos) && $m->attendee) {
                        $pos = $m->attendee->position;
                    }
                    $dept = isset($m->department_name) ? $m->department_name : '';
                }
                $memberList[] = array(
                    'name' => $m->attendee_name ?: $m->name,
                    'department' => $dept,
                    'attendee_position' => $pos,
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
                'property_name' => isset($team->property_name) ? $team->property_name : '',
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
        ), 5000)->getData();

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

        // Fetch and group event sports for navigation filter
        $eventSports = EventSports::getByEventId($eventId);
        $sportsList = array();
        foreach ($eventSports as $item) {
            $spId = isset($item['sport_id']) ? $item['sport_id'] : (isset($item['id']) ? $item['id'] : null);
            $spName = isset($item['sport_name']) ? $item['sport_name'] : (isset($item['name']) ? $item['name'] : '');
            if ($spId) {
                $sportsList[] = array(
                    'id' => $spId,
                    'name' => $spName,
                );
            }
        }

        $groupedSports = array();
        $prefixes = array('Bóng bàn', 'Bóng đá', 'Cầu lông', 'Pickerball', 'Bơi ếch', 'Bơi tự do', 'Kéo co', 'Tennis', 'Cờ vua', 'Cờ tướng');
        foreach ($sportsList as $item) {
            $groupName = 'Khác';
            foreach ($prefixes as $prefix) {
                if (mb_strpos($item['name'], $prefix) === 0) {
                    $groupName = $prefix;
                    break;
                }
            }
            if (!isset($groupedSports[$groupName])) {
                $groupedSports[$groupName] = array();
            }
            $groupedSports[$groupName][] = $item;
        }
        uksort($groupedSports, 'strnatcasecmp');
        foreach ($groupedSports as $groupName => &$items) {
            usort($items, function ($a, $b) {
                return strnatcasecmp($a['name'], $b['name']);
            });
        }
        unset($items);

        // Sắp xếp theo mã cụm (region_code)
        uasort($teamsByRegion, function ($a, $b) {
            return strcmp($a['region_code'], $b['region_code']);
        });

        // Lấy danh sách khu vực có đội để hiển thị filter (sau khi sort)
        $regionList = array();
        foreach ($teamsByRegion as $regionData) {
            $regionList[$regionData['region_id']] = $regionData['region_name'];
        }

        $this->render('view_by_sport', array(
            'sportName' => $sportName,
            'eventName' => $eventName,
            'eventId' => $eventId,
            'sportId' => $sportId,
            'teamsByRegion' => array_values($teamsByRegion),
            'regionList' => $regionList,
            'groupedSports' => $groupedSports,
        ));
    }

    /**
     * Xuất Excel danh sách VĐV chi tiết theo bộ môn.
     * - Nội dung đơn (đội 1 người): mỗi đội hiển thị 1 dòng với tên VĐV.
     * - Nội dung đôi/đồng đội: liệt kê chi tiết từng VĐV trong đội (mỗi VĐV 1 dòng).
     */
    public function actionExportBySport()
    {
        $eventId = Yii::app()->request->getQuery('event_id');
        $sportId = Yii::app()->request->getQuery('sport_id');

        $teams = SportTeams::getApiDataProvider(array(
            'event_id' => $eventId,
            'sport_id' => $sportId,
        ), 5000)->getData();

        // Map cụm (khu vực) và property
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

        // Map attendee để lấy chức danh, phòng ban
        $attendeeMap = array();
        $attRes = ApiClient::get(ApiEndpoints::ATTENDEE_LIST, array(
            'event_id' => $eventId,
            'per_page' => 5000,
        ));
        if ($attRes['success']) {
            $attData = isset($attRes['data']['data']) ? $attRes['data']['data'] : $attRes['data'];
            if (is_array($attData)) {
                foreach ($attData as $att) {
                    if (isset($att['id'])) {
                        $attendeeMap[$att['id']] = $att;
                    }
                }
            }
        }

        // Lấy tất cả thành viên của sự kiện, gom nhóm theo đội
        $membersByTeam = array();
        $membersRes = ApiClient::get(ApiEndpoints::SPORT_TEAM_MEMBER_LIST, array(
            'event_id' => $eventId,
            'per_page' => 5000,
        ));
        if ($membersRes['success']) {
            $membersData = isset($membersRes['data']['data']) ? $membersRes['data']['data'] : $membersRes['data'];
            if (is_array($membersData)) {
                foreach ($membersData as $m) {
                    $teamId = isset($m['sport_team_id']) ? $m['sport_team_id'] : null;
                    if ($teamId) {
                        $membersByTeam[$teamId][] = $m;
                    }
                }
            }
        }

        // Gom đội theo cụm > đơn vị
        $teamsByRegion = array();
        foreach ($teams as $team) {
            if (isset($team->deleted_at) && $team->deleted_at !== null && $team->deleted_at !== '') {
                continue;
            }
            $propName = $team->property_name ?: 'Chưa xác định';
            $propId = $team->property_id;
            $regionId = isset($propertyRegionMap[$propId]) ? $propertyRegionMap[$propId] : null;
            $regionName = ($regionId && isset($regionalMap[$regionId])) ? $regionalMap[$regionId] : 'Chưa phân cụm';
            $regionCode = ($regionId && isset($regionalCodeMap[$regionId])) ? $regionalCodeMap[$regionId] : 'ZZZ';

            if (!isset($teamsByRegion[$regionId])) {
                $teamsByRegion[$regionId] = array(
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
            $teamsByRegion[$regionId]['properties'][$propId]['teams'][] = $team;
        }

        // Sắp xếp theo mã cụm
        uasort($teamsByRegion, function ($a, $b) {
            return strcmp($a['region_code'], $b['region_code']);
        });

        $sportName = '';
        $sport = Sports::fetchFromApi($sportId);
        if ($sport) {
            $sportName = $sport->name;
        }
        $eventName = '';
        $eventList = Events::getActiveList();
        if (isset($eventList[$eventId])) {
            $eventName = $eventList[$eventId];
        }

        // Khởi tạo PHPExcel
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel.Classes');
        spl_autoload_unregister(array('YiiBase', 'autoload'));
        require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        $objPHPExcel = new PHPExcel();
        spl_autoload_register(array('YiiBase', 'autoload'));

        $objPHPExcel->getProperties()->setCreator('System')
            ->setTitle('Danh sach VDV theo bo mon');

        $headerStyle = array(
            'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF')),
            'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => '3A57E8')),
            'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER),
            'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),
        );
        $borderStyle = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => 'E9ECEF'))));

        $sheet = $objPHPExcel->setActiveSheetIndex(0);
        $sheet->setTitle('Danh sach VDV');

        // Tiêu đề
        $sheet->setCellValue('A1', 'DANH SÁCH VĐV BỘ MÔN: ' . mb_strtoupper($sportName, 'UTF-8'));
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->applyFromArray(array(
            'font' => array('bold' => true, 'size' => 14),
            'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
        ));
        $sheet->setCellValue('A2', 'Sự kiện: ' . $eventName);
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2')->applyFromArray(array('font' => array('italic' => true)));

        $headers = array('STT', 'Cụm', 'Đơn vị đăng ký', 'Tên đội', 'Liên quân', 'Họ tên VĐV', 'Giới tính', 'Chức danh - Phòng ban');
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '4', $h);
            $sheet->getStyle($col . '4')->applyFromArray($headerStyle);
            $col++;
        }

        $row = 5;
        $stt = 1;
        foreach ($teamsByRegion as $regionData) {
            foreach ($regionData['properties'] as $propData) {
                foreach ($propData['teams'] as $team) {
                    $teamId = isset($team->id) ? $team->id : null;
                    $teamName = $team->team_name ?: (isset($team->name) ? $team->name : '');
                    $isAlliance = !empty($team->is_alliance) ? 'Có' : 'Không';
                    $members = isset($membersByTeam[$teamId]) ? $membersByTeam[$teamId] : array();

                    if (empty($members)) {
                        // Đội chưa có VĐV - vẫn xuất 1 dòng
                        $this->writeSportExportRow($sheet, $borderStyle, $row, $stt++, $regionData['region_name'], $propData['property_name'], $teamName, $isAlliance, '', '', '');
                        $row++;
                        continue;
                    }

                    foreach ($members as $m) {
                        $attendeeId = isset($m['attendee_id']) ? $m['attendee_id'] : null;
                        $att = ($attendeeId && isset($attendeeMap[$attendeeId])) ? $attendeeMap[$attendeeId] : null;

                        $name = isset($m['attendee_name']) ? $m['attendee_name'] : (isset($m['name']) ? $m['name'] : '');
                        if (!$name && $att) {
                            $name = isset($att['full_name']) ? $att['full_name'] : '';
                        }

                        $genderRaw = isset($m['gender']) ? $m['gender'] : null;
                        $gender = ($genderRaw === 1 || $genderRaw === '1') ? 'Nam' : (($genderRaw === 0 || $genderRaw === '0') ? 'Nữ' : '');

                        $position = '';
                        $dept = '';
                        if ($att) {
                            $position = !empty($att['position_name']) ? $att['position_name'] : (!empty($att['position']) ? $att['position'] : '');
                            $dept = !empty($att['division_name']) ? $att['division_name'] : (!empty($att['unit_label']) ? $att['unit_label'] : '');
                        }
                        if (!$position && isset($m['attendee_position'])) {
                            $position = $m['attendee_position'];
                        }
                        $posDept = trim($position . ($dept ? ' - ' . $dept : ''));

                        $this->writeSportExportRow($sheet, $borderStyle, $row, $stt++, $regionData['region_name'], $propData['property_name'], $teamName, $isAlliance, $name, $gender, $posDept);
                        $row++;
                    }
                }
            }
        }

        foreach (range('A', 'H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $safeName = preg_replace('/[^A-Za-z0-9]+/', '_', UrlTransliterate::cleanString($sportName, '_'));
        $filename = 'Danh_sach_VDV_' . trim($safeName, '_') . '_' . date('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        Yii::app()->end();
    }

    private function writeSportExportRow($sheet, $borderStyle, $row, $stt, $regionName, $propName, $teamName, $isAlliance, $name, $gender, $posDept)
    {
        $sheet->setCellValue('A' . $row, $stt);
        $sheet->setCellValue('B' . $row, $regionName);
        $sheet->setCellValue('C' . $row, $propName);
        $sheet->setCellValue('D' . $row, $teamName);
        $sheet->setCellValue('E' . $row, $isAlliance);
        $sheet->setCellValue('F' . $row, $name);
        $sheet->setCellValue('G' . $row, $gender);
        $sheet->setCellValue('H' . $row, $posDept);
        $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray($borderStyle);
    }

    public function actionAjaxViewByProperty()
    {
        $eventId = Yii::app()->request->getQuery('event_id');
        $propertyId = Yii::app()->request->getQuery('property_id');

        $teams = $this->getTeamsForProperty($eventId, $propertyId);

        // Fetch and build attendee map for fast lookup of position and department
        $attendeeMap = array();
        $attRes = ApiClient::get(ApiEndpoints::ATTENDEE_LIST, array(
            'event_id' => $eventId,
            'per_page' => 5000,
        ));
        if ($attRes['success']) {
            $attData = isset($attRes['data']['data']) ? $attRes['data']['data'] : $attRes['data'];
            if (is_array($attData)) {
                foreach ($attData as $att) {
                    if (isset($att['id'])) {
                        $attendeeMap[$att['id']] = $att;
                    }
                }
            }
        }

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
                $pos = '';
                $dept = '';
                if (!empty($m->attendee_id) && isset($attendeeMap[$m->attendee_id])) {
                    $att = $attendeeMap[$m->attendee_id];
                    $pos = !empty($att['position_name']) ? $att['position_name'] : (!empty($att['position']) ? $att['position'] : '');
                    $dept = !empty($att['division_name']) ? $att['division_name'] : (!empty($att['unit_label']) ? $att['unit_label'] : '');
                } else {
                    $pos = $m->attendee_position;
                    if (empty($pos) && $m->attendee) {
                        $pos = $m->attendee->position;
                    }
                    $dept = isset($m->department_name) ? $m->department_name : '';
                }
                $memberList[] = array(
                    'name' => $m->attendee_name ?: $m->name,
                    'department' => $dept,
                    'attendee_position' => $pos,
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
                'property_name' => isset($team->property_name) ? $team->property_name : '',
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
        ), 5000)->getData();

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

        // Fetch and group event sports for navigation filter
        $eventSports = EventSports::getByEventId($eventId);
        $sportsList = array();
        foreach ($eventSports as $item) {
            $spId = isset($item['sport_id']) ? $item['sport_id'] : (isset($item['id']) ? $item['id'] : null);
            $spName = isset($item['sport_name']) ? $item['sport_name'] : (isset($item['name']) ? $item['name'] : '');
            if ($spId) {
                $sportsList[] = array(
                    'id' => $spId,
                    'name' => $spName,
                );
            }
        }

        $groupedSports = array();
        $prefixes = array('Bóng bàn', 'Bóng đá', 'Cầu lông', 'Pickerball', 'Bơi ếch', 'Bơi tự do', 'Kéo co', 'Tennis', 'Cờ vua', 'Cờ tướng');
        foreach ($sportsList as $item) {
            $groupName = 'Khác';
            foreach ($prefixes as $prefix) {
                if (mb_strpos($item['name'], $prefix) === 0) {
                    $groupName = $prefix;
                    break;
                }
            }
            if (!isset($groupedSports[$groupName])) {
                $groupedSports[$groupName] = array();
            }
            $groupedSports[$groupName][] = $item;
        }
        uksort($groupedSports, 'strnatcasecmp');
        foreach ($groupedSports as $groupName => &$items) {
            usort($items, function ($a, $b) {
                return strnatcasecmp($a['name'], $b['name']);
            });
        }
        unset($items);

        // Sắp xếp theo mã cụm (region_code)
        uasort($teamsByRegion, function ($a, $b) {
            return strcmp($a['region_code'], $b['region_code']);
        });

        // Lấy danh sách khu vực có đội để hiển thị filter (sau khi sort)
        $regionList = array();
        foreach ($teamsByRegion as $regionData) {
            $regionList[$regionData['region_id']] = $regionData['region_name'];
        }

        $this->renderPartial('_view_by_sport', array(
            'sportName' => $sportName,
            'eventName' => $eventName,
            'eventId' => $eventId,
            'sportId' => $sportId,
            'teamsByRegion' => array_values($teamsByRegion),
            'regionList' => $regionList,
            'groupedSports' => $groupedSports,
        ));
    }

    public function actionView($id)
    {
        $model = $this->loadModelById($id);
        $members = SportTeamMembers::getApiDataProvider(array('sport_team_id' => $id), 10000)->getData();

        $this->render('view', array(
            'model' => $model,
            'members' => $members,
        ));
    }

    public function actionAjaxView($id)
    {
        $model = SportTeams::fetchFromApi($id);
        if ($model === null) {
            echo CJSON::encode(array('success' => false, 'message' => 'Không tìm thấy đội'));
            Yii::app()->end();
        }

        $memberList = array();
        foreach ($model->members as $m) {
            $genderRaw = isset($m['gender']) ? $m['gender'] : null;
            $memberList[] = array(
                'name' => isset($m['attendee_name']) ? $m['attendee_name'] : (isset($m['name']) ? $m['name'] : ''),
                'gender' => $genderRaw,
                'photo_path' => isset($m['photo_path']) ? $m['photo_path'] : '',
                'position' => isset($m['attendee_position']) ? $m['attendee_position'] : '',
                'property_name' => isset($m['property_name']) ? $m['property_name'] : '',
            );
        }

        echo CJSON::encode(array(
            'success' => true,
            'data' => array(
                'id' => $model->id,
                'name' => $model->name,
                'team_name' => $model->team_name,
                'sport_name' => $model->sport_name,
                'property_name' => $model->property_name,
                'is_alliance' => $model->is_alliance,
                'status' => $model->status,
                'status_label' => SportTeams::getStatusLabel($model->status),
                'members' => $memberList,
            ),
        ));
        Yii::app()->end();
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

    // ==================== VÒNG CHUNG KẾT ====================

    /**
     * Màn hình chọn đội vào vòng chung kết (theo sự kiện + môn thi).
     */
    public function actionFinal()
    {
        $events = Events::getActiveList();
        $sports = Sports::getApiDataProvider(array('is_active' => 1), 100)->getData();

        $this->render('final', array(
            'events' => $events,
            'sports' => $sports,
        ));
    }

    /**
     * AJAX: danh sách đội đủ điều kiện (đã xác nhận, chưa vào chung kết).
     */
    public function actionFinalCandidates()
    {
        $eventId = Yii::app()->request->getQuery('event_id');
        $sportId = Yii::app()->request->getQuery('sport_id');
        if (empty($eventId) || empty($sportId)) {
            $this->renderJson(array('success' => true, 'data' => array()));
        }

        $excluded = array();
        foreach (SportTeams::getFinalists($eventId, $sportId) as $f) {
            $ref = isset($f['team_id']) ? $f['team_id'] : (isset($f['id']) ? $f['id'] : null);
            if ($ref !== null) {
                $excluded[$ref] = true;
            }
        }

        $teams = SportTeams::getApiDataProvider(array(
            'event_id' => $eventId,
            'sport_id' => $sportId,
        ), 10000)->getData();

        $membersByTeam = $this->getMemberNamesByTeam($eventId);

        $data = array();
        foreach ($teams as $team) {
            if ((string)$team->status === (string)SportTeams::STATUS_CANCELLED) {
                continue;
            }
            if (isset($excluded[$team->id])) {
                continue;
            }
            $members = isset($membersByTeam[$team->id]) ? $membersByTeam[$team->id] : array();
            $count = count($members);

            $name = $this->buildTeamDisplayName($team->name, $team->team_name, $team->id, ($team->is_alliance || $team->is_alliance_team));
            // Nội dung đơn (đội chỉ 1 VĐV): hiển thị luôn tên VĐV kèm tên đội.
            if ($count === 1) {
                $name .= ' — ' . $members[0];
            }

            $sub = trim($team->property_name);
            if ($count > 0) {
                $sub .= ($sub ? ' · ' : '') . $count . ' VĐV';
            }
            $data[] = array(
                'id' => $team->id,
                'code' => '',
                'name' => $name,
                'sub' => $sub,
            );
        }

        $this->renderJson(array('success' => true, 'data' => $data));
    }

    /**
     * AJAX: danh sách đội đã vào chung kết.
     */
    public function actionFinalList()
    {
        $eventId = Yii::app()->request->getQuery('event_id');
        $sportId = Yii::app()->request->getQuery('sport_id');
        if (empty($eventId) || empty($sportId)) {
            $this->renderJson(array('success' => true, 'data' => array()));
        }

        $data = array();
        foreach (SportTeams::getFinalists($eventId, $sportId) as $f) {
            $ref = isset($f['team_id']) ? $f['team_id'] : null;
            $team = isset($f['team']) && is_array($f['team']) ? $f['team'] : array();
            $isAlliance = !empty($team['is_alliance']) || !empty($team['is_alliance_team']);
            $teamNameRaw = isset($team['name']) ? $team['name'] : null;
            $name = $this->buildTeamDisplayName($teamNameRaw, isset($team['team_name']) ? $team['team_name'] : null, $ref, $isAlliance);
            $rank = isset($f['final_rank']) ? $f['final_rank'] : null;

            $members = (isset($team['members']) && is_array($team['members'])) ? $team['members'] : array();
            $count = count($members);
            // Nội dung đơn: hiển thị tên VĐV kèm tên đội.
            if ($count === 1) {
                $memberName = isset($members[0]['name']) ? $members[0]['name']
                    : (isset($members[0]['attendee_name']) ? $members[0]['attendee_name'] : '');
                if ($memberName !== '') {
                    $name .= ' — ' . $memberName;
                }
            }
            $sub = $count > 0 ? ($count . ' VĐV') : '';
            $data[] = array(
                'id' => isset($f['id']) ? $f['id'] : $ref,
                'ref' => $ref,
                'code' => $rank ? ('Hạng ' . $rank) : '',
                'name' => $name,
                'sub' => $sub,
            );
        }

        $this->renderJson(array('success' => true, 'data' => $data));
    }

    /**
     * AJAX: thêm các đội đã chọn vào chung kết.
     */
    public function actionFinalAdd()
    {
        $eventId = Yii::app()->request->getPost('event_id');
        $sportId = Yii::app()->request->getPost('sport_id');
        $ids = Yii::app()->request->getPost('ids', array());

        if (empty($eventId) || empty($sportId) || empty($ids)) {
            $this->renderJson(array('success' => false, 'message' => 'Thiếu dữ liệu bắt buộc.'));
        }

        $result = SportTeams::addToFinal($eventId, $sportId, $ids);
        $this->renderFinalResult($result, 'Đã thêm ' . count($ids) . ' đội vào chung kết.');
    }

    /**
     * AJAX: gỡ một đội khỏi chung kết.
     */
    public function actionFinalRemove()
    {
        $id = Yii::app()->request->getPost('id');
        if (empty($id)) {
            $this->renderJson(array('success' => false, 'message' => 'Thiếu ID.'));
        }
        $result = SportTeams::removeFromFinal($id);
        $this->renderFinalResult($result, 'Đã gỡ đội khỏi chung kết.');
    }

    protected function loadModelById($id)
    {
        $model = SportTeams::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy đội thể thao.');
        }
        return $model;
    }

    /**
     * Dựng tên hiển thị của đội: ưu tiên name, rồi team_name, cuối cùng "Đội #id".
     * Kèm nhãn "(Liên quân)" cho đội liên quân để phân biệt.
     */
    protected function buildTeamDisplayName($name, $teamName, $teamId, $isAlliance = false)
    {
        $label = !empty($name) ? $name : (!empty($teamName) ? $teamName : ('Đội #' . $teamId));
        if ($isAlliance) {
            $label .= ' (Liên quân)';
        }
        return $label;
    }

    /**
     * Lấy danh sách tên VĐV theo từng đội của sự kiện.
     * @param int $eventId
     * @return array map sport_team_id => [tên VĐV, ...]
     */
    protected function getMemberNamesByTeam($eventId)
    {
        $map = array();
        $res = ApiClient::get(ApiEndpoints::SPORT_TEAM_MEMBER_LIST, array(
            'event_id' => $eventId,
            'per_page' => 5000,
        ));
        if (!empty($res['success'])) {
            $rows = isset($res['data']['data']) ? $res['data']['data'] : $res['data'];
            if (is_array($rows)) {
                foreach ($rows as $m) {
                    $tid = isset($m['sport_team_id']) ? $m['sport_team_id'] : null;
                    if (!$tid) {
                        continue;
                    }
                    $memberName = isset($m['name']) ? $m['name']
                        : (isset($m['attendee_name']) ? $m['attendee_name'] : '');
                    if ($memberName !== '') {
                        $map[$tid][] = $memberName;
                    }
                }
            }
        }
        return $map;
    }

    /**
     * Trả JSON và kết thúc request.
     */
    protected function renderJson($payload)
    {
        header('Content-Type: application/json');
        echo CJSON::encode($payload);
        Yii::app()->end();
    }

    /**
     * Chuẩn hoá kết quả gọi Finals API thành JSON cho client.
     */
    protected function renderFinalResult($result, $successMessage)
    {
        if (isset($result['success']) && $result['success']) {
            $this->renderJson(array('success' => true, 'message' => $successMessage));
        }
        $message = isset($result['error']) && $result['error'] ? $result['error'] : 'Có lỗi xảy ra.';
        $this->renderJson(array('success' => false, 'message' => $message));
    }

    /**
     * Lấy danh sách đội thể thao của đơn vị dựa trên 2 điều kiện:
     * 1. registration_id mà đơn vị đăng ký
     * 2. các team bóng đá, kéo co mà đơn vị liên quân dựa theo cột alliance_org_ids
     */
    private function getTeamsForProperty($eventId, $propertyId)
    {
        // 1. Lấy danh sách registration_id của đơn vị này trong sự kiện
        $registrations = Registrations::getApiDataProvider(array(
            'event_id' => $eventId,
            'property_id' => $propertyId,
        ), 100)->getData();

        $regIds = array();
        foreach ($registrations as $reg) {
            if (isset($reg->deleted_at) && $reg->deleted_at !== null && $reg->deleted_at !== '') {
                continue;
            }
            $regIds[] = $reg->id;
        }

        // 2. Lấy tất cả đội của sự kiện
        $allTeams = SportTeams::getApiDataProvider(array(
            'event_id' => $eventId,
        ), 5000)->getData();

        // 3. Lọc theo 2 điều kiện
        $filteredTeams = array();
        foreach ($allTeams as $team) {
            if (isset($team->deleted_at) && $team->deleted_at !== null && $team->deleted_at !== '') {
                continue;
            }

            // ĐK 1: registration_id thuộc danh sách registration của đơn vị
            $cond1 = (!empty($team->registration_id) && in_array($team->registration_id, $regIds));

            // ĐK 2: môn bóng đá hoặc kéo co và đơn vị nằm trong alliance_org_ids
            $cond2 = false;
            $sportName = $team->sport_name ?: '';
            $isBongDaOrKeoCo = false;
            if ($sportName) {
                $sportNameLower = mb_strtolower($sportName, 'UTF-8');
                if (mb_strpos($sportNameLower, 'bóng đá') !== false || mb_strpos($sportNameLower, 'kéo co') !== false) {
                    $isBongDaOrKeoCo = true;
                }
            }

            if ($isBongDaOrKeoCo && !empty($team->alliance_org_ids)) {
                $allianceIds = array_filter(array_map('trim', explode(',', $team->alliance_org_ids)));
                if (in_array($propertyId, $allianceIds)) {
                    $cond2 = true;
                }
            }

            if ($cond1 || $cond2) {
                $filteredTeams[] = $team;
            }
        }

        return $filteredTeams;
    }
}
