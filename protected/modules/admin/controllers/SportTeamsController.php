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

        // 1. Fetch active sports
        $sportsRes = Sports::getApiDataProvider(array('is_active' => 1), 500)->getData();
        $sportStats = array();
        foreach ($sportsRes as $sport) {
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

    protected function loadModelById($id)
    {
        $model = SportTeams::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy đội thể thao.');
        }
        return $model;
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
