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
        $registrationsRes = Registrations::getApiDataProvider($regParams, 10000)->getData();
        //133 regis => OK
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
                if ($attId) {
                    $attendees[$attId] = $att;
                    $attendeePropertyMap[$attId] = $propId;
                }
            }
        }

        // Gộp các attendee trùng nhau: cùng mã nhân viên hoặc cùng số CCCD/CMND
        // thì tính là 1 người (quy về bản ghi gặp đầu tiên)
        $attendeeAlias = array();
        $staffCodeIndex = array();
        $idCardIndex = array();
        foreach ($attendees as $attId => $att) {
            $staffCode = isset($att->staff_code) ? mb_strtoupper(trim((string)$att->staff_code), 'UTF-8') : '';
            $idCard = isset($att->id_card) ? trim((string)$att->id_card) : '';

            $canonicalId = null;
            if ($staffCode !== '' && isset($staffCodeIndex[$staffCode])) {
                $canonicalId = $staffCodeIndex[$staffCode];
            } elseif ($idCard !== '' && isset($idCardIndex[$idCard])) {
                $canonicalId = $idCardIndex[$idCard];
            }
            if ($canonicalId === null) {
                $canonicalId = $attId;
            }

            $attendeeAlias[$attId] = $canonicalId;
            if ($staffCode !== '') $staffCodeIndex[$staffCode] = $canonicalId;
            if ($idCard !== '') $idCardIndex[$idCard] = $canonicalId;
        }

        // Lấy event_sports để filter chỉ các môn active với event này
        $eventSportsList = EventSports::getByEventId($eventId);
        $activeSportIds = array();
        foreach ($eventSportsList as $es) {
            $sportId = isset($es['sport_id']) ? $es['sport_id'] : null;
            if ($sportId) {
                $activeSportIds[$sportId] = true;
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

        // Nếu có event_sports config, thêm parent sports vào activeSportIds
        if (!empty($activeSportIds)) {
            foreach ($activeSportIds as $spId => $val) {
                $parentId = isset($sportParentMap[$spId]) ? $sportParentMap[$spId] : $spId;
                if ($parentId && $parentId != $spId) {
                    $activeSportIds[$parentId] = true;
                }
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
        $teamsRes = SportTeams::getApiDataProvider(array('event_id' => $eventId), 50000)->getData();
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
        // Chỉ đếm những môn được cấu hình trong event_sports
        foreach ($sportMembers as $sm) {
            $smDeletedAt = isset($sm['deleted_at']) ? $sm['deleted_at'] : null;
            if ($smDeletedAt) continue;

            $attId = isset($sm['attendee_id']) ? $sm['attendee_id'] : null;
            $teamId = isset($sm['sport_team_id']) ? $sm['sport_team_id'] : null;
            if (!$attId || !isset($attendeeStats[$attId])) continue;

            $sportId = isset($teamSportMap[$teamId]) ? $teamSportMap[$teamId] : null;
            if (!$sportId) continue;

            // Filter theo event_sports nếu có cấu hình
            if (!empty($activeSportIds) && !isset($activeSportIds[$sportId])) continue;

            $parentSportId = isset($sportParentMap[$sportId]) ? $sportParentMap[$sportId] : $sportId;
            $attendeeStats[$attId]['parent_sports'][$parentSportId] = true;
        }

        // Đếm competition
        foreach ($competitionRegs as $cr) {
            $crDeletedAt = isset($cr['deleted_at']) ? $cr['deleted_at'] : null;
            if ($crDeletedAt) continue;

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
        // Thống kê số VĐV theo từng môn thể thao (parent sport + children)
        $sportStats = array();
        $childSportsMap = array(); // parent_id => array of children

        // Build map parent -> children và init sportStats
        // Chỉ include những môn được cấu hình trong event_sports
        foreach ($sportsList as $sp) {
            $spId = isset($sp->id) ? $sp->id : null;
            $parentId = isset($sp->parent_id) ? $sp->parent_id : null;
            $spName = isset($sp->name) ? $sp->name : '';

            // Filter theo event_sports nếu có cấu hình
            if (!empty($activeSportIds) && !isset($activeSportIds[$spId])) continue;

            if ($spId && !$parentId) {
                // Parent sport
                $sportStats[$spId] = array(
                    'sport_id' => $spId,
                    'sport_name' => $spName,
                    'sport_code' => isset($sp->code) ? $sp->code : '',
                    'total_athletes' => 0,
                    'children' => array(),
                );
            } else if ($spId && $parentId) {
                // Child sport
                if (!isset($childSportsMap[$parentId])) {
                    $childSportsMap[$parentId] = array();
                }
                $childSportsMap[$parentId][$spId] = array(
                    'sport_id' => $spId,
                    'sport_name' => $spName,
                    'total_athletes' => 0,
                );
            }
        }

        // Attach children to parents
        foreach ($childSportsMap as $parentId => $children) {
            if (isset($sportStats[$parentId])) {
                $sportStats[$parentId]['children'] = $children;
            }
        }

        // Đếm số VĐV cho từng môn con (child sport)
        // Chỉ đếm những môn được cấu hình trong event_sports
        foreach ($sportMembers as $sm) {
            $smDeletedAt = isset($sm['deleted_at']) ? $sm['deleted_at'] : null;
            if ($smDeletedAt) continue;

            $attId = isset($sm['attendee_id']) ? $sm['attendee_id'] : null;
            $teamId = isset($sm['sport_team_id']) ? $sm['sport_team_id'] : null;
            if (!$attId || !isset($attendeeStats[$attId])) continue;

            $sportId = isset($teamSportMap[$teamId]) ? $teamSportMap[$teamId] : null;
            if (!$sportId) continue;

            // Filter theo event_sports nếu có cấu hình
            if (!empty($activeSportIds) && !isset($activeSportIds[$sportId])) continue;

            $parentSportId = isset($sportParentMap[$sportId]) ? $sportParentMap[$sportId] : $sportId;

            // Đếm cho child sport
            if ($sportId != $parentSportId && isset($sportStats[$parentSportId]['children'][$sportId])) {
                $sportStats[$parentSportId]['children'][$sportId]['total_athletes']++;
            }
        }

        // Đếm số VĐV cho parent (unique theo người, đã tính ở attendeeStats)
        foreach ($attendeeStats as $attId => $stats) {
            foreach ($stats['parent_sports'] as $parentSportId => $val) {
                if (isset($sportStats[$parentSportId])) {
                    $sportStats[$parentSportId]['total_athletes']++;
                }
            }
        }

        // Sắp xếp children theo số VĐV giảm dần
        foreach ($sportStats as &$ps) {
            if (!empty($ps['children'])) {
                uasort($ps['children'], function ($a, $b) {
                    return $b['total_athletes'] - $a['total_athletes'];
                });
            }
        }
        unset($ps);

        // Sắp xếp theo số VĐV giảm dần
        usort($sportStats, function ($a, $b) {
            return $b['total_athletes'] - $a['total_athletes'];
        });

        // Tính toán số môn thể thao đăng ký theo đơn vị
        $propertySportCount = array();
        foreach ($propertyStats as $propId => $pStats) {
            $propertySportCount[$propId] = array(
                'property_id' => $propId,
                'property_name' => $pStats['property_name'],
                'property_code' => $pStats['property_code'],
                'sport_count' => 0,
                'sport_names' => array(),
            );
        }

        // Đếm số môn thể thao mà đơn vị có VĐV đăng ký
        // Chỉ đếm những môn được cấu hình trong event_sports
        foreach ($sportMembers as $sm) {
            $smDeletedAt = isset($sm['deleted_at']) ? $sm['deleted_at'] : null;
            if ($smDeletedAt) continue;

            $attId = isset($sm['attendee_id']) ? $sm['attendee_id'] : null;
            $teamId = isset($sm['sport_team_id']) ? $sm['sport_team_id'] : null;
            if (!$attId || !isset($attendeePropertyMap[$attId])) continue;

            $propId = $attendeePropertyMap[$attId];
            if (!isset($propertySportCount[$propId])) continue;

            $sportId = isset($teamSportMap[$teamId]) ? $teamSportMap[$teamId] : null;
            if (!$sportId) continue;

            // Filter theo event_sports nếu có cấu hình
            if (!empty($activeSportIds) && !isset($activeSportIds[$sportId])) continue;

            $parentSportId = isset($sportParentMap[$sportId]) ? $sportParentMap[$sportId] : $sportId;
            $propertySportCount[$propId]['sport_names'][$parentSportId] = true;
        }

        // Đếm số môn và lấy tên
        $sportNameMap = array();
        foreach ($sportsList as $sp) {
            $spId = isset($sp->id) ? $sp->id : null;
            $parentId = isset($sp->parent_id) ? $sp->parent_id : null;
            if ($spId && !$parentId) {
                $sportNameMap[$spId] = isset($sp->name) ? $sp->name : '';
            }
        }

        foreach ($propertySportCount as $propId => &$psc) {
            $psc['sport_count'] = count($psc['sport_names']);
            $names = array();
            foreach (array_keys($psc['sport_names']) as $spId) {
                if (isset($sportNameMap[$spId])) {
                    $names[] = $sportNameMap[$spId];
                }
            }
            $psc['sport_names'] = implode(', ', $names);
        }
        unset($psc);

        // Lọc chỉ đơn vị có đăng ký thể thao
        $propertySportCount = array_filter($propertySportCount, function ($p) {
            return $p['sport_count'] > 0;
        });

        // Top 50 ít nhất
        $top50Least = $propertySportCount;
        usort($top50Least, function ($a, $b) {
            return $a['sport_count'] - $b['sport_count'];
        });
        $top50Least = array_slice($top50Least, 0, 50);

        // Top 50 nhiều nhất
        $top50Most = $propertySportCount;
        usort($top50Most, function ($a, $b) {
            return $b['sport_count'] - $a['sport_count'];
        });
        $top50Most = array_slice($top50Most, 0, 50);

        // Tính toán số nội dung thể thao (child sports) theo đơn vị
        $propertySportContentCount = array();
        foreach ($propertyStats as $propId => $pStats) {
            $propertySportContentCount[$propId] = array(
                'property_id' => $propId,
                'property_name' => $pStats['property_name'],
                'property_code' => $pStats['property_code'],
                'content_count' => 0,
                'content_names' => array(),
            );
        }

        // Map tên sport (cả parent và child)
        $allSportNameMap = array();
        foreach ($sportsList as $sp) {
            $spId = isset($sp->id) ? $sp->id : null;
            if ($spId) {
                $allSportNameMap[$spId] = isset($sp->name) ? $sp->name : '';
            }
        }

        // Đếm số nội dung thể thao con (child sport) mà đơn vị có VĐV đăng ký
        // Chỉ đếm những môn được cấu hình trong event_sports
        foreach ($sportMembers as $sm) {
            $smDeletedAt = isset($sm['deleted_at']) ? $sm['deleted_at'] : null;
            if ($smDeletedAt) continue;

            $attId = isset($sm['attendee_id']) ? $sm['attendee_id'] : null;
            $teamId = isset($sm['sport_team_id']) ? $sm['sport_team_id'] : null;
            if (!$attId || !isset($attendeePropertyMap[$attId])) continue;

            $propId = $attendeePropertyMap[$attId];
            if (!isset($propertySportContentCount[$propId])) continue;

            $sportId = isset($teamSportMap[$teamId]) ? $teamSportMap[$teamId] : null;
            if (!$sportId) continue;

            // Filter theo event_sports nếu có cấu hình
            if (!empty($activeSportIds) && !isset($activeSportIds[$sportId])) continue;

            // Đếm theo sport_id thực tế (child sport)
            $propertySportContentCount[$propId]['content_names'][$sportId] = true;
        }

        // Đếm số nội dung và lấy tên
        foreach ($propertySportContentCount as $propId => &$pcc) {
            $pcc['content_count'] = count($pcc['content_names']);
            $names = array();
            foreach (array_keys($pcc['content_names']) as $spId) {
                if (isset($allSportNameMap[$spId])) {
                    $names[] = $allSportNameMap[$spId];
                }
            }
            $pcc['content_names'] = implode(', ', $names);
        }
        unset($pcc);

        // Lọc chỉ đơn vị có đăng ký thể thao
        $propertySportContentCount = array_filter($propertySportContentCount, function ($p) {
            return $p['content_count'] > 0;
        });

        // Top 50 ít nội dung nhất
        $top50LeastContent = $propertySportContentCount;
        usort($top50LeastContent, function ($a, $b) {
            return $a['content_count'] - $b['content_count'];
        });
        $top50LeastContent = array_slice($top50LeastContent, 0, 50);

        // Top 50 nhiều nội dung nhất
        $top50MostContent = $propertySportContentCount;
        usort($top50MostContent, function ($a, $b) {
            return $b['content_count'] - $a['content_count'];
        });
        $top50MostContent = array_slice($top50MostContent, 0, 50);

        // Sắp xếp đơn vị theo số người đăng ký thể thao (nhiều -> ít)
        $propertiesBySportsAttendees = array();
        foreach ($propertyStats as $propId => $pStats) {
            if ($pStats['sports_attendees'] > 0) {
                $propertiesBySportsAttendees[] = array(
                    'property_id' => $propId,
                    'property_name' => $pStats['property_name'],
                    'property_code' => $pStats['property_code'],
                    'sports_attendees' => $pStats['sports_attendees'],
                    'unique_attendees' => $pStats['unique_attendees'],
                );
            }
        }
        usort($propertiesBySportsAttendees, function ($a, $b) {
            return $b['sports_attendees'] - $a['sports_attendees'];
        });

        return array(
            'regionals' => $regionalData,
            'summary' => $summary,
            'sportStats' => $sportStats,
            'top50LeastSports' => $top50Least,
            'top50MostSports' => $top50Most,
            'top50LeastContent' => $top50LeastContent,
            'top50MostContent' => $top50MostContent,
            'propertiesBySportsAttendees' => $propertiesBySportsAttendees,
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

    /**
     * Xuất Excel báo cáo đơn vị theo số môn thể thao
     */
    public function actionExportSportsByProperty()
    {
        PermissionHelper::requirePermission('reports', 'read');

        $eventId = Yii::app()->request->getParam('event_id');
        $type = Yii::app()->request->getParam('type', 'least'); // least or most

        $user = AuthHandler::getUser();
        $userPropertyCode = isset($user['property_code']) ? $user['property_code'] : '';
        $isHO = ($userPropertyCode === '9999' || $userPropertyCode === 9999);
        $userPropertyId = isset($user['property_id']) ? $user['property_id'] : null;

        $reportData = $this->buildReport($eventId, $isHO, $userPropertyId);

        $category = Yii::app()->request->getParam('category', 'sport'); // sport, content, or attendees

        if ($category === 'attendees') {
            $data = $reportData['propertiesBySportsAttendees'];
            $title = 'Đơn vị theo số người đăng ký thể thao';
            $filename = 'don_vi_theo_so_nguoi_dang_ky_the_thao.xlsx';
        } elseif ($category === 'content') {
            $data = $type === 'most' ? $reportData['top50MostContent'] : $reportData['top50LeastContent'];
            $title = $type === 'most' ? 'Top 50 đơn vị đăng ký nhiều nội dung thể thao nhất' : 'Top 50 đơn vị đăng ký ít nội dung thể thao nhất';
            $filename = $type === 'most' ? 'top50_nhieu_noi_dung_the_thao.xlsx' : 'top50_it_noi_dung_the_thao.xlsx';
        } else {
            $data = $type === 'most' ? $reportData['top50MostSports'] : $reportData['top50LeastSports'];
            $title = $type === 'most' ? 'Top 50 đơn vị đăng ký nhiều môn thể thao nhất' : 'Top 50 đơn vị đăng ký ít môn thể thao nhất';
            $filename = $type === 'most' ? 'top50_nhieu_mon_the_thao.xlsx' : 'top50_it_mon_the_thao.xlsx';
        }

        $excel = $this->createPhpExcel();
        $sheet = $excel->getActiveSheet();
        $sheet->setTitle('Báo cáo');

        // Header
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Column headers và data tùy theo category
        if ($category === 'attendees') {
            $sheet->setCellValue('A3', 'STT');
            $sheet->setCellValue('B3', 'Mã đơn vị');
            $sheet->setCellValue('C3', 'Tên đơn vị');
            $sheet->setCellValue('D3', 'Số người ĐK TT');
            $sheet->setCellValue('E3', 'Tổng người ĐK');

            $headerStyle = array(
                'font' => array('bold' => true),
                'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'E2E8F0')),
                'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),
            );
            $sheet->getStyle('A3:E3')->applyFromArray($headerStyle);

            $row = 4;
            $stt = 1;
            foreach ($data as $item) {
                $sheet->setCellValue('A' . $row, $stt++);
                $sheet->setCellValue('B' . $row, $item['property_code']);
                $sheet->setCellValue('C' . $row, $item['property_name']);
                $sheet->setCellValue('D' . $row, $item['sports_attendees']);
                $sheet->setCellValue('E' . $row, $item['unique_attendees']);
                $row++;
            }

            foreach (range('A', 'E') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        } else {
            $colHeader = $category === 'content' ? 'Số nội dung TT' : 'Số môn TT';
            $countField = $category === 'content' ? 'content_count' : 'sport_count';
            $namesField = $category === 'content' ? 'content_names' : 'sport_names';

            $sheet->setCellValue('A3', 'STT');
            $sheet->setCellValue('B3', 'Mã đơn vị');
            $sheet->setCellValue('C3', 'Tên đơn vị');
            $sheet->setCellValue('D3', $colHeader);
            $sheet->setCellValue('E3', 'Danh sách');

            $headerStyle = array(
                'font' => array('bold' => true),
                'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'E2E8F0')),
                'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),
            );
            $sheet->getStyle('A3:E3')->applyFromArray($headerStyle);

            $row = 4;
            $stt = 1;
            foreach ($data as $item) {
                $sheet->setCellValue('A' . $row, $stt++);
                $sheet->setCellValue('B' . $row, $item['property_code']);
                $sheet->setCellValue('C' . $row, $item['property_name']);
                $sheet->setCellValue('D' . $row, $item[$countField]);
                $sheet->setCellValue('E' . $row, $item[$namesField]);
                $row++;
            }

            foreach (range('A', 'E') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }

        // Output
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $writer->save('php://output');
        Yii::app()->end();
    }

    /**
     * Xuất Excel danh sách VĐV theo cụm
     * Mỗi cụm là 1 sheet, mỗi sheet gồm các nội dung active trong sự kiện,
     * từng nội dung hiển thị đội và danh sách VĐV
     */
    public function actionExportAthletesByRegional()
    {
        PermissionHelper::requirePermission('reports', 'read');

        $eventId = Yii::app()->request->getParam('event_id');
        if (!$eventId) {
            throw new CHttpException(400, 'Thiếu tham số sự kiện.');
        }

        // Cụm
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
        $sortedRegionals = array_values($regionalMap);
        usort($sortedRegionals, function ($a, $b) {
            return strnatcasecmp($a['name'], $b['name']);
        });

        // Đơn vị (map property -> cụm)
        $propertyMap = array();
        $properties = Properties::getApiDataProvider(array('is_active' => 1), 1000)->getData();
        foreach ($properties as $p) {
            $pId = isset($p->id) ? $p->id : null;
            if ($pId) {
                $propertyMap[$pId] = array(
                    'name' => isset($p->name) ? $p->name : '',
                    'code' => isset($p->code) ? $p->code : '',
                    'region_id' => isset($p->region_id) ? $p->region_id : null,
                );
            }
        }

        // Môn thể thao
        $sportsList = Sports::getApiDataProvider(array('is_active' => 1), 500)->getData();
        $sportInfoMap = array();
        foreach ($sportsList as $sp) {
            $spId = isset($sp->id) ? $sp->id : null;
            if ($spId) {
                $sportInfoMap[$spId] = array(
                    'name' => isset($sp->name) ? $sp->name : '',
                    'parent_id' => isset($sp->parent_id) ? $sp->parent_id : null,
                );
            }
        }

        // Nội dung active trong sự kiện (event_sports)
        $activeSportIds = array();
        $eventSportsList = EventSports::getByEventId($eventId);
        foreach ($eventSportsList as $es) {
            $sportId = isset($es['sport_id']) ? $es['sport_id'] : null;
            if ($sportId && isset($sportInfoMap[$sportId])) {
                $activeSportIds[$sportId] = true;
            }
        }

        // Đội thi đấu
        $teamsRes = SportTeams::getApiDataProvider(array('event_id' => $eventId), 50000)->getData();
        $teams = array();
        foreach ($teamsRes as $team) {
            $teamId = isset($team->id) ? $team->id : null;
            $deletedAt = isset($team->deleted_at) ? $team->deleted_at : null;
            $status = isset($team->status) ? (int)$team->status : SportTeams::STATUS_PENDING;
            if (!$teamId || $deletedAt || $status === SportTeams::STATUS_CANCELLED) continue;
            $teams[$teamId] = $team;
        }

        // Nếu sự kiện không cấu hình event_sports thì lấy các môn có đội đăng ký
        if (empty($activeSportIds)) {
            foreach ($teams as $team) {
                $spId = isset($team->sport_id) ? $team->sport_id : null;
                if ($spId && isset($sportInfoMap[$spId])) {
                    $activeSportIds[$spId] = true;
                }
            }
        }

        // Danh sách nội dung, sắp xếp theo môn cha rồi đến tên nội dung
        $contents = array();
        foreach (array_keys($activeSportIds) as $spId) {
            $info = $sportInfoMap[$spId];
            $parentId = $info['parent_id'];
            $parentName = ($parentId && isset($sportInfoMap[$parentId])) ? $sportInfoMap[$parentId]['name'] : $info['name'];
            $label = ($parentId && isset($sportInfoMap[$parentId]) && $sportInfoMap[$parentId]['name'] !== $info['name'])
                ? $sportInfoMap[$parentId]['name'] . ' — ' . $info['name']
                : $info['name'];
            $contents[] = array(
                'sport_id' => $spId,
                'label' => $label,
                'parent_name' => $parentName,
                'name' => $info['name'],
            );
        }
        usort($contents, function ($a, $b) {
            $cmp = strnatcasecmp($a['parent_name'], $b['parent_name']);
            return $cmp !== 0 ? $cmp : strnatcasecmp($a['name'], $b['name']);
        });

        // Người tham dự (map tra cứu tên, chức danh, đơn vị)
        $attendeeMap = array();
        $rawAttendees = Attendees::getApiDataProvider(array('event_id' => $eventId, 'per_page' => 10000), 10000)->getData();
        foreach ($rawAttendees as $att) {
            $attId = isset($att->id) ? $att->id : null;
            if ($attId) {
                $attendeeMap[$attId] = array(
                    'full_name' => isset($att->full_name) ? $att->full_name : '',
                    'position' => isset($att->position) ? $att->position : '',
                    'property_name' => isset($att->property_name) ? $att->property_name : '',
                    'property_id' => isset($att->property_id) ? $att->property_id : null,
                    'gender' => isset($att->gender) ? $att->gender : null,
                );
            }
        }

        // Thành viên đội, group theo đội
        $membersByTeam = array();
        $sportMembers = SportTeamMembers::getRawListByEvent($eventId);
        foreach ($sportMembers as $sm) {
            $smDeletedAt = isset($sm['deleted_at']) ? $sm['deleted_at'] : null;
            if ($smDeletedAt) continue;
            $teamId = isset($sm['sport_team_id']) ? $sm['sport_team_id'] : null;
            if (!$teamId || !isset($teams[$teamId])) continue;
            $membersByTeam[$teamId][] = $sm;
        }

        // Group đội theo cụm và nội dung: regionId => sportId => array of teamId
        $teamsByRegionSport = array();
        foreach ($teams as $teamId => $team) {
            $propId = isset($team->property_id) ? $team->property_id : null;
            $regionId = ($propId && isset($propertyMap[$propId]) && $propertyMap[$propId]['region_id'])
                ? $propertyMap[$propId]['region_id'] : 0;
            // Cụm không có trong danh sách active thì gom vào "Chưa phân cụm"
            if ($regionId && !isset($regionalMap[$regionId])) {
                $regionId = 0;
            }
            $spId = isset($team->sport_id) ? $team->sport_id : null;
            if (!$spId || !isset($activeSportIds[$spId])) continue;
            $teamsByRegionSport[$regionId][$spId][] = $teamId;
        }

        // Map nhãn nội dung theo sport_id (dùng cho sheet tổng hợp)
        $contentLabelMap = array();
        foreach ($contents as $content) {
            $contentLabelMap[$content['sport_id']] = $content['label'];
        }

        // Cuộc thi nghiệp vụ (map id -> tên)
        $competitionNameMap = array();
        $competitionsRes = Competitions::getApiDataProvider(array(), 500)->getData();
        foreach ($competitionsRes as $comp) {
            $compId = isset($comp->id) ? $comp->id : null;
            if ($compId) {
                $competitionNameMap[$compId] = isset($comp->name) ? $comp->name : '';
            }
        }

        // Hàm xác định cụm của một đơn vị
        $resolveRegionId = function ($propId) use ($propertyMap, $regionalMap) {
            $regionId = ($propId && isset($propertyMap[$propId]) && $propertyMap[$propId]['region_id'])
                ? $propertyMap[$propId]['region_id'] : 0;
            return ($regionId && isset($regionalMap[$regionId])) ? $regionId : 0;
        };

        // Đăng ký thi nghiệp vụ: group theo cụm + cuộc thi, và theo người
        $compRegs = CompetitionRegistrations::getRawListByEvent($eventId);
        $contestantsByRegionComp = array(); // regionId => compId => array thí sinh
        $compsByAttendee = array();         // attId => compId => candidate_number
        $eventCompIds = array();            // các cuộc thi có thí sinh trong sự kiện
        foreach ($compRegs as $cr) {
            $crDeletedAt = isset($cr['deleted_at']) ? $cr['deleted_at'] : null;
            if ($crDeletedAt) continue;
            $crStatus = isset($cr['status']) ? (int)$cr['status'] : CompetitionRegistrations::STATUS_PENDING;
            if ($crStatus === CompetitionRegistrations::STATUS_CANCELLED) continue;

            $attId = isset($cr['attendee_id']) ? $cr['attendee_id'] : null;
            $compId = isset($cr['competition_id']) ? $cr['competition_id'] : null;
            if (!$attId || !$compId || !isset($attendeeMap[$attId])) continue;

            $candidateNumber = isset($cr['candidate_number']) ? $cr['candidate_number'] : '';
            $attInfo = $attendeeMap[$attId];
            $regionId = $resolveRegionId($attInfo['property_id']);

            $eventCompIds[$compId] = true;
            $compsByAttendee[$attId][$compId] = $candidateNumber;
            $contestantsByRegionComp[$regionId][$compId][] = array(
                'full_name' => $attInfo['full_name'],
                'property_name' => $attInfo['property_name'],
                'position' => $attInfo['position'],
                'candidate_number' => $candidateNumber,
            );
        }

        // Danh sách cuộc thi hiển thị, sắp theo tên
        $displayCompetitions = array();
        foreach (array_keys($eventCompIds) as $compId) {
            $displayCompetitions[] = array(
                'id' => $compId,
                'name' => isset($competitionNameMap[$compId]) && $competitionNameMap[$compId] !== ''
                    ? $competitionNameMap[$compId] : ('Cuộc thi #' . $compId),
            );
        }
        usort($displayCompetitions, function ($a, $b) {
            return strnatcasecmp($a['name'], $b['name']);
        });

        // Tổng hợp người tham gia (VĐV + thí sinh nghiệp vụ) cho sheet đầu tiên
        $participants = array();
        foreach ($membersByTeam as $tId => $members) {
            $team = $teams[$tId];
            $spId = isset($team->sport_id) ? $team->sport_id : null;
            if (!$spId || !isset($contentLabelMap[$spId])) continue;
            $propId = isset($team->property_id) ? $team->property_id : null;
            $teamPropertyName = !empty($team->property_name)
                ? $team->property_name
                : (($propId && isset($propertyMap[$propId])) ? $propertyMap[$propId]['name'] : '');

            foreach ($members as $m) {
                $attId = isset($m['attendee_id']) ? $m['attendee_id'] : null;
                $key = $attId ? 'a' . $attId : 'm' . (isset($m['id']) ? $m['id'] : uniqid());
                if (!isset($participants[$key])) {
                    $attInfo = ($attId && isset($attendeeMap[$attId])) ? $attendeeMap[$attId] : null;
                    $pPropId = $attInfo && $attInfo['property_id'] ? $attInfo['property_id'] : $propId;
                    $pRegionId = $resolveRegionId($pPropId);
                    $participants[$key] = array(
                        'full_name' => !empty($m['attendee_name']) ? $m['attendee_name']
                            : ($attInfo && !empty($attInfo['full_name']) ? $attInfo['full_name']
                                : (isset($m['name']) ? $m['name'] : '')),
                        'gender' => ($attInfo && $attInfo['gender'] !== null && $attInfo['gender'] !== '')
                            ? $attInfo['gender']
                            : (isset($m['gender']) ? $m['gender'] : null),
                        'property_name' => $attInfo && !empty($attInfo['property_name']) ? $attInfo['property_name'] : $teamPropertyName,
                        'region_name' => isset($regionalMap[$pRegionId]) ? $regionalMap[$pRegionId]['name'] : 'Chưa phân cụm',
                        'position' => !empty($m['attendee_position']) ? $m['attendee_position']
                            : ($attInfo && !empty($attInfo['position']) ? $attInfo['position'] : ''),
                        'contents' => array(),
                    );
                }
                $participants[$key]['contents'][$contentLabelMap[$spId]] = true;
            }
        }
        foreach ($compsByAttendee as $attId => $comps) {
            $key = 'a' . $attId;
            if (!isset($participants[$key])) {
                $attInfo = $attendeeMap[$attId];
                $pRegionId = $resolveRegionId($attInfo['property_id']);
                $participants[$key] = array(
                    'full_name' => $attInfo['full_name'],
                    'gender' => $attInfo['gender'],
                    'property_name' => $attInfo['property_name'],
                    'region_name' => isset($regionalMap[$pRegionId]) ? $regionalMap[$pRegionId]['name'] : 'Chưa phân cụm',
                    'position' => $attInfo['position'],
                    'contents' => array(),
                );
            }
            foreach (array_keys($comps) as $compId) {
                $compName = isset($competitionNameMap[$compId]) && $competitionNameMap[$compId] !== ''
                    ? $competitionNameMap[$compId] : ('Cuộc thi #' . $compId);
                $participants[$key]['contents']['Nghiệp vụ: ' . $compName] = true;
            }
        }
        $participants = array_values($participants);
        usort($participants, function ($a, $b) {
            $cmp = strnatcasecmp($a['region_name'], $b['region_name']);
            if ($cmp !== 0) return $cmp;
            $cmp = strnatcasecmp($a['property_name'], $b['property_name']);
            if ($cmp !== 0) return $cmp;
            return strnatcasecmp($a['full_name'], $b['full_name']);
        });

        $excel = $this->createPhpExcel();
        $excel->removeSheetByIndex(0);

        // Sheet đầu tiên: tổng hợp tất cả VĐV + thí sinh nghiệp vụ
        $usedTitles = array();
        $summarySheet = $excel->createSheet(0);
        $summarySheet->setTitle($this->buildSheetTitle('Tổng hợp', $usedTitles));

        $summarySheet->setCellValue('A1', 'DANH SÁCH VĐV VÀ THÍ SINH THI NGHIỆP VỤ');
        $summarySheet->mergeCells('A1:G1');
        $summarySheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $summarySheet->setCellValue('A3', 'STT');
        $summarySheet->setCellValue('B3', 'Họ và tên');
        $summarySheet->setCellValue('C3', 'Giới tính');
        $summarySheet->setCellValue('D3', 'Đơn vị');
        $summarySheet->setCellValue('E3', 'Cụm');
        $summarySheet->setCellValue('F3', 'Chức danh');
        $summarySheet->setCellValue('G3', 'Nội dung tham gia');
        $summarySheet->getStyle('A3:G3')->applyFromArray(array(
            'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF')),
            'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => '2563EB')),
            'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),
        ));

        $row = 4;
        $stt = 1;
        foreach ($participants as $p) {
            $summarySheet->setCellValue('A' . $row, $stt++);
            $summarySheet->setCellValue('B' . $row, $p['full_name']);
            $summarySheet->setCellValue('C' . $row, $this->formatGender($p['gender']));
            $summarySheet->setCellValue('D' . $row, $p['property_name']);
            $summarySheet->setCellValue('E' . $row, $p['region_name']);
            $summarySheet->setCellValue('F' . $row, $p['position']);
            $summarySheet->setCellValue('G' . $row, implode(', ', array_keys($p['contents'])));
            $summarySheet->getStyle('A' . $row . ':G' . $row)->applyFromArray(array(
                'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),
            ));
            $row++;
        }

        $summarySheet->getColumnDimension('A')->setWidth(6);
        $summarySheet->getColumnDimension('B')->setWidth(30);
        $summarySheet->getColumnDimension('C')->setWidth(10);
        $summarySheet->getColumnDimension('D')->setWidth(30);
        $summarySheet->getColumnDimension('E')->setWidth(18);
        $summarySheet->getColumnDimension('F')->setWidth(22);
        $summarySheet->getColumnDimension('G')->setWidth(60);
        $summarySheet->getStyle('G4:G' . max(4, $row - 1))->getAlignment()->setWrapText(true);

        // Danh sách sheet cụm: các cụm active + "Chưa phân cụm" nếu có đội/thí sinh chưa thuộc cụm nào
        $sheetRegionals = $sortedRegionals;
        if (isset($teamsByRegionSport[0]) || isset($contestantsByRegionComp[0])) {
            $sheetRegionals[] = array('id' => 0, 'name' => 'Chưa phân cụm', 'code' => '');
        }

        $sheetIndex = 1;
        foreach ($sheetRegionals as $regional) {
            $regionId = $regional['id'];
            $sheet = $excel->createSheet($sheetIndex++);
            $sheet->setTitle($this->buildSheetTitle($regional['name'], $usedTitles));

            // Tiêu đề sheet
            $sheet->setCellValue('A1', 'DANH SÁCH VĐV VÀ THÍ SINH — CỤM: ' . mb_strtoupper($regional['name'], 'UTF-8'));
            $sheet->mergeCells('A1:F1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

            $row = 3;

            // Phần I: Thể thao
            $sheet->setCellValue('A' . $row, 'I. THỂ THAO');
            $sheet->mergeCells('A' . $row . ':F' . $row);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
            $row += 2;

            foreach ($contents as $content) {
                $spId = $content['sport_id'];
                $teamIds = isset($teamsByRegionSport[$regionId][$spId]) ? $teamsByRegionSport[$regionId][$spId] : array();

                // Đếm tổng VĐV của nội dung trong cụm
                $athleteCount = 0;
                foreach ($teamIds as $tId) {
                    $athleteCount += isset($membersByTeam[$tId]) ? count($membersByTeam[$tId]) : 0;
                }

                // Header nội dung
                $sheet->setCellValue('A' . $row, $content['label'] . ' (' . count($teamIds) . ' đội, ' . $athleteCount . ' VĐV)');
                $sheet->mergeCells('A' . $row . ':F' . $row);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
                $sheet->getStyle('A' . $row . ':F' . $row)->getFill()
                    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('2563EB');
                $row++;

                if (empty($teamIds)) {
                    $sheet->setCellValue('A' . $row, 'Chưa có đội đăng ký');
                    $sheet->mergeCells('A' . $row . ':F' . $row);
                    $sheet->getStyle('A' . $row)->getFont()->setItalic(true);
                    $row += 2;
                    continue;
                }

                // Sắp xếp đội theo mã đơn vị
                usort($teamIds, function ($a, $b) use ($teams, $propertyMap) {
                    $pa = isset($teams[$a]->property_id, $propertyMap[$teams[$a]->property_id]) ? $propertyMap[$teams[$a]->property_id]['code'] : '';
                    $pb = isset($teams[$b]->property_id, $propertyMap[$teams[$b]->property_id]) ? $propertyMap[$teams[$b]->property_id]['code'] : '';
                    return strnatcasecmp($pa, $pb);
                });

                foreach ($teamIds as $tId) {
                    $team = $teams[$tId];
                    $members = isset($membersByTeam[$tId]) ? $membersByTeam[$tId] : array();

                    $teamName = !empty($team->team_name) ? $team->team_name : (isset($team->name) ? $team->name : '');
                    $propId = isset($team->property_id) ? $team->property_id : null;
                    $teamPropertyName = !empty($team->property_name)
                        ? $team->property_name
                        : (($propId && isset($propertyMap[$propId])) ? $propertyMap[$propId]['name'] : '');

                    $teamLine = 'Đội: ' . $teamName . ' — Đơn vị: ' . $teamPropertyName . ' — Số VĐV: ' . count($members);
                    if (!empty($team->is_alliance) && !empty($team->alliance_org_names)) {
                        $teamLine .= ' — Liên quân: ' . $team->alliance_org_names;
                    }

                    // Header đội
                    $sheet->setCellValue('A' . $row, $teamLine);
                    $sheet->mergeCells('A' . $row . ':F' . $row);
                    $sheet->getStyle('A' . $row)->getFont()->setBold(true);
                    $sheet->getStyle('A' . $row . ':F' . $row)->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('E2E8F0');
                    $row++;

                    // Header cột danh sách VĐV
                    $sheet->setCellValue('A' . $row, 'STT');
                    $sheet->setCellValue('B' . $row, 'Họ và tên');
                    $sheet->setCellValue('C' . $row, 'Đơn vị');
                    $sheet->setCellValue('D' . $row, 'Chức danh');
                    $sheet->setCellValue('E' . $row, 'Số áo');
                    $sheet->setCellValue('F' . $row, 'Đội trưởng');
                    $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray(array(
                        'font' => array('bold' => true),
                        'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'F3F4F6')),
                        'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),
                    ));
                    $row++;

                    $stt = 1;
                    foreach ($members as $m) {
                        $attId = isset($m['attendee_id']) ? $m['attendee_id'] : null;
                        $attInfo = ($attId && isset($attendeeMap[$attId])) ? $attendeeMap[$attId] : null;

                        $memberName = !empty($m['attendee_name']) ? $m['attendee_name']
                            : ($attInfo && !empty($attInfo['full_name']) ? $attInfo['full_name']
                                : (isset($m['name']) ? $m['name'] : ''));
                        $memberProperty = !empty($m['property_name']) ? $m['property_name']
                            : ($attInfo && !empty($attInfo['property_name']) ? $attInfo['property_name'] : $teamPropertyName);
                        $memberPosition = !empty($m['attendee_position']) ? $m['attendee_position']
                            : ($attInfo && !empty($attInfo['position']) ? $attInfo['position'] : '');

                        $sheet->setCellValue('A' . $row, $stt++);
                        $sheet->setCellValue('B' . $row, $memberName);
                        $sheet->setCellValue('C' . $row, $memberProperty);
                        $sheet->setCellValue('D' . $row, $memberPosition);
                        $sheet->setCellValue('E' . $row, isset($m['jersey_number']) ? $m['jersey_number'] : '');
                        $sheet->setCellValue('F' . $row, !empty($m['is_captain']) ? 'X' : '');
                        $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray(array(
                            'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),
                        ));
                        $row++;
                    }

                    $row++; // dòng trống giữa các đội
                }

                $row++; // dòng trống giữa các nội dung
            }

            // Phần II: Thi nghiệp vụ
            if (!empty($displayCompetitions)) {
                $sheet->setCellValue('A' . $row, 'II. THI NGHIỆP VỤ');
                $sheet->mergeCells('A' . $row . ':F' . $row);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
                $row += 2;

                foreach ($displayCompetitions as $competition) {
                    $compId = $competition['id'];
                    $contestants = isset($contestantsByRegionComp[$regionId][$compId])
                        ? $contestantsByRegionComp[$regionId][$compId] : array();

                    // Header cuộc thi
                    $sheet->setCellValue('A' . $row, $competition['name'] . ' (' . count($contestants) . ' thí sinh)');
                    $sheet->mergeCells('A' . $row . ':F' . $row);
                    $sheet->getStyle('A' . $row)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
                    $sheet->getStyle('A' . $row . ':F' . $row)->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('10B981');
                    $row++;

                    if (empty($contestants)) {
                        $sheet->setCellValue('A' . $row, 'Chưa có thí sinh đăng ký');
                        $sheet->mergeCells('A' . $row . ':F' . $row);
                        $sheet->getStyle('A' . $row)->getFont()->setItalic(true);
                        $row += 2;
                        continue;
                    }

                    usort($contestants, function ($a, $b) {
                        $cmp = strnatcasecmp($a['property_name'], $b['property_name']);
                        return $cmp !== 0 ? $cmp : strnatcasecmp($a['full_name'], $b['full_name']);
                    });

                    // Header cột danh sách thí sinh
                    $sheet->setCellValue('A' . $row, 'STT');
                    $sheet->setCellValue('B' . $row, 'Họ và tên');
                    $sheet->setCellValue('C' . $row, 'Đơn vị');
                    $sheet->setCellValue('D' . $row, 'Chức danh');
                    $sheet->setCellValue('E' . $row, 'Số báo danh');
                    $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray(array(
                        'font' => array('bold' => true),
                        'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'F3F4F6')),
                        'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),
                    ));
                    $row++;

                    $stt = 1;
                    foreach ($contestants as $contestant) {
                        $sheet->setCellValue('A' . $row, $stt++);
                        $sheet->setCellValue('B' . $row, $contestant['full_name']);
                        $sheet->setCellValue('C' . $row, $contestant['property_name']);
                        $sheet->setCellValue('D' . $row, $contestant['position']);
                        $sheet->setCellValue('E' . $row, $contestant['candidate_number']);
                        $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray(array(
                            'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),
                        ));
                        $row++;
                    }

                    $row++; // dòng trống giữa các cuộc thi
                }
            }

            // Độ rộng cột
            $sheet->getColumnDimension('A')->setWidth(6);
            $sheet->getColumnDimension('B')->setWidth(30);
            $sheet->getColumnDimension('C')->setWidth(30);
            $sheet->getColumnDimension('D')->setWidth(22);
            $sheet->getColumnDimension('E')->setWidth(8);
            $sheet->getColumnDimension('F')->setWidth(11);
        }

        $excel->setActiveSheetIndex(0);

        // Output
        $filename = 'danh_sach_vdv_thi_sinh_theo_cum.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $writer->save('php://output');
        Yii::app()->end();
    }

    /**
     * Xuất Excel chi tiết người tham dự dạng ma trận:
     * mỗi người 1 dòng, mỗi nội dung thể thao / cuộc thi nghiệp vụ là 1 cột đánh dấu x.
     * Chỉ lấy các nội dung gắn với sự kiện và đang active.
     */
    public function actionExportAttendeeDetail()
    {
        PermissionHelper::requirePermission('reports', 'read');

        $eventId = Yii::app()->request->getParam('event_id');
        if (!$eventId) {
            throw new CHttpException(400, 'Thiếu tham số sự kiện.');
        }

        $event = Events::fetchFromApi($eventId);
        $eventName = ($event && !empty($event->name)) ? $event->name : '';

        // Cụm
        $regionalMap = array();
        $regionals = Regionals::getApiDataProvider(array('is_active' => 1), 100)->getData();
        foreach ($regionals as $r) {
            $rId = isset($r->id) ? $r->id : null;
            if ($rId) {
                $regionalMap[$rId] = array(
                    'name' => isset($r->name) ? $r->name : '',
                    'code' => isset($r->code) ? $r->code : '',
                );
            }
        }

        // Đơn vị
        $propertyMap = array();
        $properties = Properties::getApiDataProvider(array('is_active' => 1), 1000)->getData();
        foreach ($properties as $p) {
            $pId = isset($p->id) ? $p->id : null;
            if ($pId) {
                $propertyMap[$pId] = array(
                    'name' => isset($p->name) ? $p->name : '',
                    'code' => isset($p->code) ? $p->code : '',
                    'region_id' => isset($p->region_id) ? $p->region_id : null,
                );
            }
        }

        // Môn thể thao đang active
        $sportsList = Sports::getApiDataProvider(array('is_active' => 1), 500)->getData();
        $sportInfoMap = array();
        foreach ($sportsList as $sp) {
            $spId = isset($sp->id) ? $sp->id : null;
            if ($spId) {
                $sportInfoMap[$spId] = array(
                    'name' => isset($sp->name) ? $sp->name : '',
                    'parent_id' => isset($sp->parent_id) ? $sp->parent_id : null,
                );
            }
        }

        // Nội dung thể thao gắn với sự kiện và đang active (event_sports)
        $activeSportIds = array();
        $eventSportsList = EventSports::getByEventId($eventId);
        foreach ($eventSportsList as $es) {
            $sportId = isset($es['sport_id']) ? $es['sport_id'] : null;
            if ($sportId && isset($sportInfoMap[$sportId])) {
                $activeSportIds[$sportId] = true;
            }
        }

        // Đội thi đấu (bỏ đã xóa / đã hủy)
        $teams = array();
        $teamsRes = SportTeams::getApiDataProvider(array('event_id' => $eventId), 50000)->getData();
        foreach ($teamsRes as $team) {
            $teamId = isset($team->id) ? $team->id : null;
            $deletedAt = isset($team->deleted_at) ? $team->deleted_at : null;
            $status = isset($team->status) ? (int)$team->status : SportTeams::STATUS_PENDING;
            if (!$teamId || $deletedAt || $status === SportTeams::STATUS_CANCELLED) continue;
            $teams[$teamId] = $team;
        }

        // Nếu sự kiện không cấu hình event_sports thì lấy các môn active có đội đăng ký
        if (empty($activeSportIds)) {
            foreach ($teams as $team) {
                $spId = isset($team->sport_id) ? $team->sport_id : null;
                if ($spId && isset($sportInfoMap[$spId])) {
                    $activeSportIds[$spId] = true;
                }
            }
        }

        // Cột nội dung thể thao, sắp xếp theo môn cha rồi tên nội dung
        $sportColumns = array();
        foreach (array_keys($activeSportIds) as $spId) {
            $info = $sportInfoMap[$spId];
            $parentId = $info['parent_id'];
            $parentName = ($parentId && isset($sportInfoMap[$parentId])) ? $sportInfoMap[$parentId]['name'] : $info['name'];
            $sportColumns[] = array(
                'sport_id' => $spId,
                'name' => $info['name'],
                'parent_name' => $parentName,
            );
        }
        usort($sportColumns, function ($a, $b) {
            $cmp = strnatcasecmp($a['parent_name'], $b['parent_name']);
            return $cmp !== 0 ? $cmp : strnatcasecmp($a['name'], $b['name']);
        });

        // Cuộc thi nghiệp vụ đang active
        $competitionInfoMap = array();
        $competitionsRes = Competitions::getApiDataProvider(array('is_active' => 1), 500)->getData();
        foreach ($competitionsRes as $comp) {
            $compId = isset($comp->id) ? $comp->id : null;
            if ($compId) {
                $competitionInfoMap[$compId] = isset($comp->name) ? $comp->name : '';
            }
        }

        // Cuộc thi gắn với sự kiện và đang active (event_competitions)
        $activeCompIds = array();
        $eventCompetitionsList = EventCompetitions::getByEventId($eventId);
        foreach ($eventCompetitionsList as $ec) {
            $compId = isset($ec['competition_id']) ? $ec['competition_id'] : null;
            if ($compId && isset($competitionInfoMap[$compId])) {
                $activeCompIds[$compId] = true;
            }
        }

        // Đăng ký thi nghiệp vụ (bỏ đã xóa / đã hủy)
        $compRegs = CompetitionRegistrations::getRawListByEvent($eventId);
        $validCompRegs = array();
        foreach ($compRegs as $cr) {
            $crDeletedAt = isset($cr['deleted_at']) ? $cr['deleted_at'] : null;
            if ($crDeletedAt) continue;
            $crStatus = isset($cr['status']) ? (int)$cr['status'] : CompetitionRegistrations::STATUS_PENDING;
            if ($crStatus === CompetitionRegistrations::STATUS_CANCELLED) continue;
            $validCompRegs[] = $cr;
        }

        // Nếu sự kiện không cấu hình event_competitions thì lấy các cuộc thi active có thí sinh
        if (empty($activeCompIds)) {
            foreach ($validCompRegs as $cr) {
                $compId = isset($cr['competition_id']) ? $cr['competition_id'] : null;
                if ($compId && isset($competitionInfoMap[$compId])) {
                    $activeCompIds[$compId] = true;
                }
            }
        }

        // Cột nghiệp vụ, sắp theo tên
        $compColumns = array();
        foreach (array_keys($activeCompIds) as $compId) {
            $compColumns[] = array(
                'competition_id' => $compId,
                'name' => $competitionInfoMap[$compId],
            );
        }
        usort($compColumns, function ($a, $b) {
            return strnatcasecmp($a['name'], $b['name']);
        });

        // Bản đăng ký active (không bị xóa, không phải nháp)
        $activeRegistrationIds = array();
        $registrationsRes = Registrations::getApiDataProvider(array('event_id' => $eventId, 'per_page' => 1000), 10000)->getData();
        foreach ($registrationsRes as $reg) {
            $regDeletedAt = isset($reg->deleted_at) ? $reg->deleted_at : null;
            if ($regDeletedAt) continue;
            $regStatus = isset($reg->status) ? (int)$reg->status : 0;
            if ($regStatus === Registrations::STATUS_DRAFT) continue;
            $regId = isset($reg->id) ? $reg->id : null;
            if ($regId) {
                $activeRegistrationIds[$regId] = true;
            }
        }

        // Người tham dự (map tra cứu): chỉ lấy người đang active,
        // không bị xóa và thuộc một bản đăng ký active
        $attendeeMap = array();
        $rawAttendees = Attendees::getApiDataProvider(array('event_id' => $eventId, 'per_page' => 10000), 10000)->getData();
        foreach ($rawAttendees as $att) {
            $attId = isset($att->id) ? $att->id : null;
            if (!$attId) continue;

            $attDeletedAt = isset($att->deleted_at) ? $att->deleted_at : null;
            if ($attDeletedAt) continue;

            if (isset($att->is_active) && $att->is_active !== null && $att->is_active !== '' && !(int)$att->is_active) continue;

            $regId = isset($att->registration_id) ? $att->registration_id : null;
            if (!$regId || !isset($activeRegistrationIds[$regId])) continue;

            $attendeeMap[$attId] = array(
                'full_name' => isset($att->full_name) ? $att->full_name : '',
                'gender' => isset($att->gender) ? $att->gender : null,
                'staff_code' => isset($att->staff_code) ? $att->staff_code : '',
                'id_card' => isset($att->id_card) ? $att->id_card : '',
                'position' => isset($att->position) ? $att->position : '',
                'department_name' => isset($att->department_name) ? $att->department_name : '',
                'property_id' => isset($att->property_id) ? $att->property_id : null,
                'property_name' => isset($att->property_name) ? $att->property_name : '',
            );
        }

        // Gộp các attendee trùng nhau: cùng mã nhân viên hoặc cùng số CCCD/CMND
        // thì quy về 1 attendee đại diện (bản ghi gặp đầu tiên)
        $attendeeAlias = array();
        $staffCodeIndex = array();
        $idCardIndex = array();
        foreach ($attendeeMap as $attId => $info) {
            $staffCode = mb_strtoupper(trim((string)$info['staff_code']), 'UTF-8');
            $idCard = trim((string)$info['id_card']);

            $canonicalId = null;
            if ($staffCode !== '' && isset($staffCodeIndex[$staffCode])) {
                $canonicalId = $staffCodeIndex[$staffCode];
            } elseif ($idCard !== '' && isset($idCardIndex[$idCard])) {
                $canonicalId = $idCardIndex[$idCard];
            }
            if ($canonicalId === null) {
                $canonicalId = $attId;
            }

            $attendeeAlias[$attId] = $canonicalId;
            if ($staffCode !== '') $staffCodeIndex[$staffCode] = $canonicalId;
            if ($idCard !== '') $idCardIndex[$idCard] = $canonicalId;
        }

        $resolveRegionId = function ($propId) use ($propertyMap, $regionalMap) {
            $regionId = ($propId && isset($propertyMap[$propId]) && $propertyMap[$propId]['region_id'])
                ? $propertyMap[$propId]['region_id'] : 0;
            return ($regionId && isset($regionalMap[$regionId])) ? $regionId : 0;
        };

        // Gom người tham dự + đánh dấu nội dung
        $participants = array();
        $initParticipant = function ($info, $regionId) use ($regionalMap) {
            return array(
                'region_name' => isset($regionalMap[$regionId]) ? $regionalMap[$regionId]['name'] : 'Chưa phân cụm',
                'property_code' => $info['property_code'],
                'property_name' => $info['property_name'],
                'full_name' => $info['full_name'],
                'gender' => $info['gender'],
                'staff_code' => $info['staff_code'],
                'position' => $info['position'],
                'department_name' => $info['department_name'],
                'sports' => array(),
                'competitions' => array(),
            );
        };

        // Thành viên đội thể thao
        $sportMembers = SportTeamMembers::getRawListByEvent($eventId);
        foreach ($sportMembers as $sm) {
            $smDeletedAt = isset($sm['deleted_at']) ? $sm['deleted_at'] : null;
            if ($smDeletedAt) continue;
            $teamId = isset($sm['sport_team_id']) ? $sm['sport_team_id'] : null;
            if (!$teamId || !isset($teams[$teamId])) continue;

            $team = $teams[$teamId];
            $spId = isset($team->sport_id) ? $team->sport_id : null;
            if (!$spId || !isset($activeSportIds[$spId])) continue;

            // Chỉ nhận thành viên gắn với người tham dự đã qua rà soát
            // (đang active, không bị xóa, thuộc bản đăng ký active)
            $attId = isset($sm['attendee_id']) ? $sm['attendee_id'] : null;
            if (!$attId || !isset($attendeeMap[$attId])) continue;

            // Quy về attendee đại diện nếu trùng mã NV / CCCD
            $attId = $attendeeAlias[$attId];

            $key = 'a' . $attId;
            if (!isset($participants[$key])) {
                $attInfo = $attendeeMap[$attId];
                $teamPropId = isset($team->property_id) ? $team->property_id : null;
                $propId = $attInfo['property_id'] ? $attInfo['property_id'] : $teamPropId;
                $propInfo = ($propId && isset($propertyMap[$propId])) ? $propertyMap[$propId] : null;
                $participants[$key] = $initParticipant(array(
                    'full_name' => !empty($attInfo['full_name']) ? $attInfo['full_name']
                        : (isset($sm['attendee_name']) ? $sm['attendee_name'] : ''),
                    'gender' => ($attInfo['gender'] !== null && $attInfo['gender'] !== '')
                        ? $attInfo['gender']
                        : (isset($sm['gender']) ? $sm['gender'] : null),
                    'staff_code' => $attInfo['staff_code'],
                    'position' => !empty($attInfo['position']) ? $attInfo['position']
                        : (isset($sm['attendee_position']) ? $sm['attendee_position'] : ''),
                    'department_name' => $attInfo['department_name'],
                    'property_code' => $propInfo ? $propInfo['code'] : '',
                    'property_name' => !empty($attInfo['property_name']) ? $attInfo['property_name']
                        : ($propInfo ? $propInfo['name'] : (!empty($team->property_name) ? $team->property_name : '')),
                ), $resolveRegionId($propId));
            }
            $participants[$key]['sports'][$spId] = true;
        }

        // Thí sinh thi nghiệp vụ
        foreach ($validCompRegs as $cr) {
            $compId = isset($cr['competition_id']) ? $cr['competition_id'] : null;
            if (!$compId || !isset($activeCompIds[$compId])) continue;

            $attId = isset($cr['attendee_id']) ? $cr['attendee_id'] : null;
            if (!$attId || !isset($attendeeMap[$attId])) continue;

            // Quy về attendee đại diện nếu trùng mã NV / CCCD
            $attId = $attendeeAlias[$attId];

            $key = 'a' . $attId;
            if (!isset($participants[$key])) {
                $attInfo = $attendeeMap[$attId];
                $propId = $attInfo['property_id'];
                $propInfo = ($propId && isset($propertyMap[$propId])) ? $propertyMap[$propId] : null;
                $participants[$key] = $initParticipant(array(
                    'full_name' => $attInfo['full_name'],
                    'gender' => $attInfo['gender'],
                    'staff_code' => $attInfo['staff_code'],
                    'position' => $attInfo['position'],
                    'department_name' => $attInfo['department_name'],
                    'property_code' => $propInfo ? $propInfo['code'] : '',
                    'property_name' => !empty($attInfo['property_name']) ? $attInfo['property_name'] : ($propInfo ? $propInfo['name'] : ''),
                ), $resolveRegionId($propId));
            }
            $participants[$key]['competitions'][$compId] = true;
        }

        $participants = array_values($participants);
        usort($participants, function ($a, $b) {
            $cmp = strnatcasecmp($a['region_name'], $b['region_name']);
            if ($cmp !== 0) return $cmp;
            $cmp = strnatcasecmp($a['property_code'], $b['property_code']);
            if ($cmp !== 0) return $cmp;
            return strnatcasecmp($a['full_name'], $b['full_name']);
        });

        // Build Excel
        $excel = $this->createPhpExcel();
        $sheet = $excel->getActiveSheet();
        $sheet->setTitle('Chi tiết người tham dự');

        $fixedHeaders = array('STT', 'Cụm', 'Mã ĐV', 'Họ và tên', 'Giới tính', 'Mã NV', 'Chức danh', 'Bộ phận');
        $fixedCount = count($fixedHeaders);
        $totalCols = $fixedCount + count($sportColumns) + count($compColumns);
        $lastColLetter = PHPExcel_Cell::stringFromColumnIndex($totalCols - 1);

        // Tiêu đề
        $title = 'DANH SÁCH NGƯỜI THAM DỰ' . ($eventName !== '' ? ' ' . mb_strtoupper($eventName, 'UTF-8') : '');
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:' . $lastColLetter . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // Header cột
        $headerRow = 2;
        $colIndex = 0;
        foreach ($fixedHeaders as $header) {
            $sheet->setCellValueByColumnAndRow($colIndex++, $headerRow, $header);
        }
        foreach ($sportColumns as $sc) {
            $sheet->setCellValueByColumnAndRow($colIndex++, $headerRow, $sc['name']);
        }
        foreach ($compColumns as $cc) {
            $sheet->setCellValueByColumnAndRow($colIndex++, $headerRow, $cc['name']);
        }

        $sheet->getStyle('A' . $headerRow . ':' . $lastColLetter . $headerRow)->applyFromArray(array(
            'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF')),
            'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => '2563EB')),
            'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'wrap' => true,
            ),
        ));
        $sheet->getRowDimension($headerRow)->setRowHeight(45);

        // Dữ liệu
        $row = $headerRow + 1;
        $stt = 1;
        foreach ($participants as $p) {
            $colIndex = 0;
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $stt++);
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $p['region_name']);
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $p['property_code']);
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $p['full_name']);
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $this->formatGender($p['gender']));
            $sheet->setCellValueExplicitByColumnAndRow($colIndex++, $row, $p['staff_code'], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $p['position']);
            $sheet->setCellValueByColumnAndRow($colIndex++, $row, $p['department_name']);
            foreach ($sportColumns as $sc) {
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, isset($p['sports'][$sc['sport_id']]) ? 'x' : '');
            }
            foreach ($compColumns as $cc) {
                $sheet->setCellValueByColumnAndRow($colIndex++, $row, isset($p['competitions'][$cc['competition_id']]) ? 'x' : '');
            }

            $sheet->getStyle('A' . $row . ':' . $lastColLetter . $row)->applyFromArray(array(
                'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),
            ));
            $row++;
        }

        // Căn giữa cột đánh dấu x và STT, giới tính
        $lastDataRow = max($headerRow + 1, $row - 1);
        $firstMarkCol = PHPExcel_Cell::stringFromColumnIndex($fixedCount);
        $sheet->getStyle('A3:A' . $lastDataRow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E3:F' . $lastDataRow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        if ($totalCols > $fixedCount) {
            $sheet->getStyle($firstMarkCol . '3:' . $lastColLetter . $lastDataRow)
                ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }

        // Độ rộng cột
        $fixedWidths = array(6, 12, 10, 28, 9, 12, 32, 26);
        foreach ($fixedWidths as $i => $width) {
            $sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setWidth($width);
        }
        for ($i = $fixedCount; $i < $totalCols; $i++) {
            $sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setWidth(14);
        }

        // Cố định 2 dòng đầu và các cột thông tin khi cuộn
        $sheet->freezePane(PHPExcel_Cell::stringFromColumnIndex($fixedCount) . ($headerRow + 1));

        // Output
        $filename = 'chi_tiet_nguoi_tham_du.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $writer->save('php://output');
        Yii::app()->end();
    }

    /**
     * Hiển thị giới tính: 1 = Nam, 0 = Nữ
     */
    protected function formatGender($gender)
    {
        if ($gender === null || $gender === '') {
            return '';
        }
        return ((int)$gender === 1) ? 'Nam' : 'Nữ';
    }

    /**
     * Khởi tạo PHPExcel (thư viện nằm trong ext.phpexcel.Classes,
     * phải tạm gỡ autoloader của Yii khi load để tránh xung đột)
     */
    protected function createPhpExcel()
    {
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel.Classes');
        spl_autoload_unregister(array('YiiBase', 'autoload'));
        require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        $excel = new PHPExcel();
        spl_autoload_register(array('YiiBase', 'autoload'));
        return $excel;
    }

    /**
     * Tạo tên sheet hợp lệ (bỏ ký tự cấm, tối đa 31 ký tự, không trùng)
     */
    protected function buildSheetTitle($name, &$usedTitles)
    {
        $title = str_replace(array('\\', '/', '?', '*', '[', ']', ':'), '', $name);
        $title = trim($title) !== '' ? trim($title) : 'Cụm';
        $title = mb_substr($title, 0, 31, 'UTF-8');

        $base = $title;
        $suffix = 2;
        while (isset($usedTitles[mb_strtolower($title, 'UTF-8')])) {
            $tail = ' (' . $suffix++ . ')';
            $title = mb_substr($base, 0, 31 - mb_strlen($tail, 'UTF-8'), 'UTF-8') . $tail;
        }
        $usedTitles[mb_strtolower($title, 'UTF-8')] = true;

        return $title;
    }

    /**
     * Xuất Excel số lượng VĐV theo môn thể thao
     */
    public function actionExportSportStats()
    {
        PermissionHelper::requirePermission('reports', 'read');

        $eventId = Yii::app()->request->getParam('event_id');

        $user = AuthHandler::getUser();
        $userPropertyCode = isset($user['property_code']) ? $user['property_code'] : '';
        $isHO = ($userPropertyCode === '9999' || $userPropertyCode === 9999);
        $userPropertyId = isset($user['property_id']) ? $user['property_id'] : null;

        $reportData = $this->buildReport($eventId, $isHO, $userPropertyId);
        $sportStats = isset($reportData['sportStats']) ? $reportData['sportStats'] : array();

        $excel = $this->createPhpExcel();
        $sheet = $excel->getActiveSheet();
        $sheet->setTitle('VĐV theo môn TT');

        // Header
        $sheet->setCellValue('A1', 'Số lượng VĐV theo môn thể thao');
        $sheet->mergeCells('A1:C1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Column headers
        $sheet->setCellValue('A3', 'STT');
        $sheet->setCellValue('B3', 'Môn thể thao');
        $sheet->setCellValue('C3', 'Số lượng VĐV');

        $headerStyle = array(
            'font' => array('bold' => true),
            'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => '10B981')),
            'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),
        );
        $sheet->getStyle('A3:C3')->applyFromArray($headerStyle);

        $row = 4;
        $stt = 1;
        $totalAthletes = 0;

        foreach ($sportStats as $sport) {
            if ($sport['total_athletes'] == 0) continue;

            $children = isset($sport['children']) ? $sport['children'] : array();
            $activeChildren = array_filter($children, function ($c) {
                return $c['total_athletes'] > 0;
            });

            // Parent row
            $sheet->setCellValue('A' . $row, $stt++);
            $sheet->setCellValue('B' . $row, $sport['sport_name']);
            $sheet->setCellValue('C' . $row, $sport['total_athletes']);
            $sheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row . ':C' . $row)->getFill()
                ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                ->getStartColor()->setRGB('F3F4F6');
            $row++;

            // Children rows
            foreach ($activeChildren as $child) {
                $sheet->setCellValue('A' . $row, '');
                $sheet->setCellValue('B' . $row, '    └ ' . $child['sport_name']);
                $sheet->setCellValue('C' . $row, $child['total_athletes']);
                $row++;
            }

            // Tính tổng
            if (!empty($activeChildren)) {
                foreach ($activeChildren as $c) {
                    $totalAthletes += $c['total_athletes'];
                }
            } else {
                $totalAthletes += $sport['total_athletes'];
            }
        }

        // Total row
        $sheet->setCellValue('A' . $row, '');
        $sheet->setCellValue('B' . $row, 'TỔNG CỘNG (lượt đăng ký)');
        $sheet->setCellValue('C' . $row, $totalAthletes);
        $sheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':C' . $row)->getFill()
            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FEF3C7');

        // Auto size columns
        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Output
        $filename = 'so_luong_vdv_theo_mon_the_thao.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $writer->save('php://output');
        Yii::app()->end();
    }
}
