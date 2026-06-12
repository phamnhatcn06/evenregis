<?php

class ReportAttendeeStatsController extends AdminController
{
    /**
     * Báo cáo thống kê người đăng ký vòng loại theo cụm và đơn vị
     * Phân tích theo NGƯỜI chứ không phải theo MÔN
     */
    public function actionAdmin()
    {
        $user = AuthHandler::getUser();
        if (!$user) {
            throw new CHttpException(403, 'Bạn cần đăng nhập để xem báo cáo.');
        }

        PermissionHelper::requirePermission('reports', 'read');

        $userPropertyCode = isset($user['property_code']) ? $user['property_code'] : '';
        $isHO = ($userPropertyCode === '9999' || $userPropertyCode === 9999);
        $userPropertyId = isset($user['property_id']) ? $user['property_id'] : null;

        // Lấy danh sách events cho dropdown
        $eventsList = array();
        $eventsRes = Events::getApiDataProvider(array(), 100)->getData();
        foreach ($eventsRes as $event) {
            $eId = isset($event->id) ? $event->id : (isset($event['id']) ? $event['id'] : null);
            $eName = isset($event->name) ? $event->name : (isset($event['name']) ? $event['name'] : '');
            if ($eId) {
                $eventsList[$eId] = $event;
            }
        }

        // Xác định event được chọn
        $selectedEventId = Yii::app()->request->getParam('event_id');
        if (empty($selectedEventId) && !empty($eventsList)) {
            foreach ($eventsList as $event) {
                $status = isset($event->status) ? $event->status : (isset($event['status']) ? $event['status'] : 0);
                if ($status == 1) {
                    $selectedEventId = isset($event->id) ? $event->id : $event['id'];
                    break;
                }
            }
            if (empty($selectedEventId)) {
                $firstEvent = reset($eventsList);
                $selectedEventId = isset($firstEvent->id) ? $firstEvent->id : $firstEvent['id'];
            }
        }

        $selectedEventName = '';
        if ($selectedEventId && isset($eventsList[$selectedEventId])) {
            $eventObj = $eventsList[$selectedEventId];
            $selectedEventName = isset($eventObj->name) ? $eventObj->name : (isset($eventObj['name']) ? $eventObj['name'] : '');
        }

        // Build báo cáo
        $reportData = $this->buildReport($selectedEventId, $isHO, $userPropertyId);

        $this->title = 'Báo cáo thống kê người đăng ký vòng loại';
        $this->breadcrumbs = array(
            'Báo cáo' => array('/admin/reports/admin'),
            'Thống kê người đăng ký'
        );

        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->theme->baseUrl . '/assets/js/pages/report-attendee-stats.js',
            CClientScript::POS_END
        );

        $this->render('index', array(
            'isHO' => $isHO,
            'eventsList' => $eventsList,
            'selectedEventId' => $selectedEventId,
            'selectedEventName' => $selectedEventName,
            'reportData' => $reportData,
        ));
    }

    /**
     * Build báo cáo thống kê theo người tham dự
     */
    protected function buildReport($eventId, $isHO, $userPropertyId)
    {
        if (!$eventId) {
            return array(
                'regionals' => array(),
                'summary' => $this->getEmptySummary(),
            );
        }

        // Lấy regionals (cụm)
        $regionals = Regionals::getApiDataProvider(array('is_active' => 1), 100)->getData();
        $regionalMap = array();
        foreach ($regionals as $r) {
            $rId = isset($r->id) ? $r->id : null;
            if ($rId) {
                $regionalMap[$rId] = array(
                    'id' => $rId,
                    'name' => isset($r->name) ? $r->name : '',
                    'code' => isset($r->code) ? $r->code : '',
                );
            }
        }

        // Lấy properties
        $properties = array();
        if ($isHO) {
            $properties = Properties::getApiDataProvider(array('is_active' => 1), 1000)->getData();
        } else if ($userPropertyId) {
            $prop = Properties::fetchFromApi($userPropertyId);
            if ($prop) $properties = array($prop);
        }

        $propertyMap = array();
        foreach ($properties as $p) {
            $pId = isset($p->id) ? $p->id : null;
            if ($pId) {
                $propertyMap[$pId] = array(
                    'id' => $pId,
                    'name' => isset($p->name) ? $p->name : '',
                    'code' => isset($p->code) ? $p->code : '',
                    'region_id' => isset($p->region_id) ? $p->region_id : null,
                );
            }
        }

        // Lấy registrations đã submit (không phải draft)
        $regParams = array('event_id' => $eventId, 'per_page' => 1000);
        if (!$isHO && $userPropertyId) {
            $regParams['property_id'] = $userPropertyId;
        }
        $registrationsRes = Registrations::getApiDataProvider($regParams, 1000)->getData();
        $activeRegistrationIds = array();
        $regPropertyMap = array();
        foreach ($registrationsRes as $reg) {
            $deletedAt = isset($reg->deleted_at) ? $reg->deleted_at : null;
            if ($deletedAt) continue;
            $status = isset($reg->status) ? (int)$reg->status : 0;
            if ($status !== Registrations::STATUS_DRAFT) {
                $regId = isset($reg->id) ? $reg->id : null;
                $propId = isset($reg->property_id) ? $reg->property_id : null;
                if ($regId) {
                    $activeRegistrationIds[$regId] = true;
                    $regPropertyMap[$regId] = $propId;
                }
            }
        }

        // Lấy attendees
        $attParams = array('event_id' => $eventId, 'per_page' => 10000);
        if (!$isHO && $userPropertyId) {
            $attParams['property_id'] = $userPropertyId;
        }
        $rawAttendees = Attendees::getApiDataProvider($attParams, 10000)->getData();
        $attendees = array();
        $attendeePropertyMap = array();
        foreach ($rawAttendees as $att) {
            $attDeletedAt = isset($att->deleted_at) ? $att->deleted_at : null;
            if ($attDeletedAt) continue;
            $regId = isset($att->registration_id) ? $att->registration_id : null;
            if ($regId && isset($activeRegistrationIds[$regId])) {
                $attId = isset($att->id) ? $att->id : null;
                $propId = isset($regPropertyMap[$regId]) ? $regPropertyMap[$regId] : null;
                // Chỉ lấy attendee có role_id chứa "2" (VĐV)
                $roleId = isset($att->role_id) ? $att->role_id : '';
                if ($attId && strpos((string)$roleId, '2') !== false) {
                    $attendees[$attId] = $att;
                    $attendeePropertyMap[$attId] = $propId;
                }
            }
        }

        // Lấy sports để map parent_id
        $sportsList = Sports::getApiDataProvider(array('is_active' => 1), 500)->getData();
        $sportParentMap = array();
        foreach ($sportsList as $sp) {
            $spId = isset($sp->id) ? $sp->id : null;
            $parentId = isset($sp->parent_id) ? $sp->parent_id : null;
            if ($spId) {
                $sportParentMap[$spId] = $parentId ? $parentId : $spId;
            }
        }

        // Lấy sport team members
        $sportMembersRes = ApiClient::get(ApiEndpoints::SPORT_TEAM_MEMBER_LIST, array(
            'event_id' => $eventId,
            'per_page' => 10000,
        ));
        $sportMembers = array();
        if ($sportMembersRes['success']) {
            $data = isset($sportMembersRes['data']['data']) ? $sportMembersRes['data']['data'] : $sportMembersRes['data'];
            if (is_array($data)) {
                $sportMembers = $data;
            }
        }

        // Lấy sport_id từ team
        $teamSportMap = array();
        $teamsRes = SportTeams::getApiDataProvider(array('event_id' => $eventId), 5000)->getData();
        foreach ($teamsRes as $team) {
            $teamId = isset($team->id) ? $team->id : null;
            $spId = isset($team->sport_id) ? $team->sport_id : null;
            if ($teamId && $spId) {
                $teamSportMap[$teamId] = $spId;
            }
        }

        // Lấy competition registrations
        $compRegsRes = ApiClient::get(ApiEndpoints::COMPETITION_REGISTRATION_LIST, array(
            'event_id' => $eventId,
            'per_page' => 10000,
        ));
        $competitionRegs = array();
        if ($compRegsRes['success']) {
            $data = isset($compRegsRes['data']['data']) ? $compRegsRes['data']['data'] : $compRegsRes['data'];
            if (is_array($data)) {
                $competitionRegs = $data;
            }
        }

        // Lấy beauty contestants
        $beautyContestants = array();
        $contests = BeautyContests::getApiDataProvider(array('event_id' => $eventId), 100)->getData();
        foreach ($contests as $contest) {
            $contestId = isset($contest->id) ? $contest->id : null;
            if ($contestId) {
                $contestants = BeautyContestants::getApiDataProvider(array('contest_id' => $contestId), 1000)->getData();
                foreach ($contestants as $c) {
                    $cDeletedAt = isset($c->deleted_at) ? $c->deleted_at : null;
                    if ($cDeletedAt) continue;
                    $attId = isset($c->attendee_id) ? $c->attendee_id : null;
                    if ($attId && isset($attendees[$attId])) {
                        $beautyContestants[] = array('attendee_id' => $attId);
                    }
                }
            }
        }

        // Tính toán cho từng attendee
        $attendeeStats = array();
        foreach ($attendees as $attId => $att) {
            $attendeeStats[$attId] = array(
                'parent_sports' => array(),
                'has_competition' => false,
                'has_miss' => false,
            );
        }

        // Đếm sports (theo parent sport để tính là 1 môn)
        foreach ($sportMembers as $sm) {
            $attId = isset($sm['attendee_id']) ? $sm['attendee_id'] : null;
            $teamId = isset($sm['sport_team_id']) ? $sm['sport_team_id'] : null;
            if (!$attId || !isset($attendeeStats[$attId])) continue;

            $sportId = isset($teamSportMap[$teamId]) ? $teamSportMap[$teamId] : null;
            if (!$sportId) continue;

            $parentSportId = isset($sportParentMap[$sportId]) ? $sportParentMap[$sportId] : $sportId;
            $attendeeStats[$attId]['parent_sports'][$parentSportId] = true;
        }

        // Đếm competition
        foreach ($competitionRegs as $cr) {
            $attId = isset($cr['attendee_id']) ? $cr['attendee_id'] : null;
            if ($attId && isset($attendeeStats[$attId])) {
                $attendeeStats[$attId]['has_competition'] = true;
            }
        }

        // Đếm miss
        foreach ($beautyContestants as $bc) {
            $attId = isset($bc['attendee_id']) ? $bc['attendee_id'] : null;
            if ($attId && isset($attendeeStats[$attId])) {
                $attendeeStats[$attId]['has_miss'] = true;
            }
        }

        // Tổng hợp theo property và regional
        $propertyStats = array();
        $summary = $this->getEmptySummary();

        foreach ($attendeeStats as $attId => $stats) {
            $sportsCount = count($stats['parent_sports']);
            $hasSports = $sportsCount > 0;
            $hasComp = $stats['has_competition'];
            $hasMiss = $stats['has_miss'];

            // Đếm số hạng mục tham gia
            $categoryCount = 0;
            if ($hasSports) $categoryCount++;
            if ($hasComp) $categoryCount++;
            if ($hasMiss) $categoryCount++;

            // Chỉ đếm những người có tham gia ít nhất 1 hạng mục
            if ($categoryCount == 0) continue;

            $propId = isset($attendeePropertyMap[$attId]) ? $attendeePropertyMap[$attId] : null;

            if (!isset($propertyStats[$propId])) {
                $propInfo = isset($propertyMap[$propId]) ? $propertyMap[$propId] : null;
                $propertyStats[$propId] = array(
                    'property_id' => $propId,
                    'property_name' => $propInfo ? $propInfo['name'] : 'Không xác định',
                    'property_code' => $propInfo ? $propInfo['code'] : '',
                    'region_id' => $propInfo ? $propInfo['region_id'] : null,
                    'unique_attendees' => 0,
                    'sports_attendees' => 0,
                    'competition_attendees' => 0,
                    'miss_attendees' => 0,
                    'attendees_3_sports' => 0,
                    'attendees_3_categories' => 0,
                    'attendees_2_categories' => 0,
                );
            }

            $propertyStats[$propId]['unique_attendees']++;
            if ($hasSports) $propertyStats[$propId]['sports_attendees']++;
            if ($hasComp) $propertyStats[$propId]['competition_attendees']++;
            if ($hasMiss) $propertyStats[$propId]['miss_attendees']++;
            if ($sportsCount >= 3) $propertyStats[$propId]['attendees_3_sports']++;
            if ($categoryCount >= 3) $propertyStats[$propId]['attendees_3_categories']++;
            if ($categoryCount == 2) $propertyStats[$propId]['attendees_2_categories']++;

            // Tổng hợp summary
            $summary['total_unique_attendees']++;
            if ($hasSports) $summary['total_sports_attendees']++;
            if ($hasComp) $summary['total_competition_attendees']++;
            if ($hasMiss) $summary['total_miss_attendees']++;
            if ($sportsCount >= 3) $summary['attendees_3_sports']++;
            if ($categoryCount >= 3) $summary['attendees_3_categories']++;
            if ($categoryCount == 2) $summary['attendees_2_categories']++;
        }

        // Group theo regional
        $regionalData = array();
        foreach ($propertyStats as $propId => $pStats) {
            $regionId = $pStats['region_id'];
            if (!$regionId) $regionId = 0;

            if (!isset($regionalData[$regionId])) {
                $regInfo = isset($regionalMap[$regionId]) ? $regionalMap[$regionId] : null;
                $regionalData[$regionId] = array(
                    'regional_id' => $regionId,
                    'regional_name' => $regInfo ? $regInfo['name'] : 'Chưa phân cụm',
                    'regional_code' => $regInfo ? $regInfo['code'] : '',
                    'properties' => array(),
                    'totals' => array(
                        'unique_attendees' => 0,
                        'sports_attendees' => 0,
                        'competition_attendees' => 0,
                        'miss_attendees' => 0,
                        'attendees_3_sports' => 0,
                        'attendees_3_categories' => 0,
                        'attendees_2_categories' => 0,
                    ),
                );
            }

            $regionalData[$regionId]['properties'][] = $pStats;
            $regionalData[$regionId]['totals']['unique_attendees'] += $pStats['unique_attendees'];
            $regionalData[$regionId]['totals']['sports_attendees'] += $pStats['sports_attendees'];
            $regionalData[$regionId]['totals']['competition_attendees'] += $pStats['competition_attendees'];
            $regionalData[$regionId]['totals']['miss_attendees'] += $pStats['miss_attendees'];
            $regionalData[$regionId]['totals']['attendees_3_sports'] += $pStats['attendees_3_sports'];
            $regionalData[$regionId]['totals']['attendees_3_categories'] += $pStats['attendees_3_categories'];
            $regionalData[$regionId]['totals']['attendees_2_categories'] += $pStats['attendees_2_categories'];
        }

        // Sort properties trong mỗi regional theo code
        foreach ($regionalData as &$rd) {
            usort($rd['properties'], function ($a, $b) {
                return strnatcasecmp($a['property_code'], $b['property_code']);
            });
        }
        unset($rd);

        // Sort regionals theo tên
        usort($regionalData, function ($a, $b) {
            if ($a['regional_id'] == 0) return 1;
            if ($b['regional_id'] == 0) return -1;
            return strnatcasecmp($a['regional_name'], $b['regional_name']);
        });

        return array(
            'regionals' => $regionalData,
            'summary' => $summary,
        );
    }

    protected function getEmptySummary()
    {
        return array(
            'total_unique_attendees' => 0,
            'total_sports_attendees' => 0,
            'total_competition_attendees' => 0,
            'total_miss_attendees' => 0,
            'attendees_3_sports' => 0,
            'attendees_3_categories' => 0,
            'attendees_2_categories' => 0,
        );
    }
}
