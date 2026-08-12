<?php

class ApproveRegistrationsController extends AdminController
{
    /**
     * Danh sách đăng ký chờ phê duyệt (status = submitted)
     */
    public function actionAdmin()
    {
        // Filter params từ GET
        $filterEventId = Yii::app()->request->getQuery('event_id', '');
        $filterPropertyId = Yii::app()->request->getQuery('property_id', '');
        $filterPeriodId = Yii::app()->request->getQuery('period_id', '');

        // Base params cho tất cả tabs
        $baseParams = array();
        if ($filterEventId) $baseParams['event_id'] = $filterEventId;
        if ($filterPropertyId) $baseParams['property_id'] = $filterPropertyId;
        if ($filterPeriodId) $baseParams['period_id'] = $filterPeriodId;

        // DataProvider cho từng tab
        $dpSubmitted = Registrations::getApiDataProvider(array_merge($baseParams, array('status' => Registrations::STATUS_SUBMITTED)));
        $dpRejected = Registrations::getApiDataProvider(array_merge($baseParams, array('status' => Registrations::STATUS_REJECTED)));
        $dpApproved = Registrations::getApiDataProvider(array_merge($baseParams, array('status' => Registrations::STATUS_APPROVED)));
        $dpAll = Registrations::getApiDataProvider($baseParams);

        // Đếm số lượng
        $countSubmitted = $dpSubmitted->getTotalItemCount();
        $countRejected = $dpRejected->getTotalItemCount();
        $countApproved = $dpApproved->getTotalItemCount();
        $countAll = $dpAll->getTotalItemCount();

        // Load dropdown data
        $eventsData = Events::getApiDataProvider(array('is_active' => 1), 100)->getData();
        $eventList = array('' => '-- Tất cả --');
        foreach ($eventsData as $e) {
            $id = isset($e->id) ? $e->id : (isset($e['id']) ? $e['id'] : null);
            $name = isset($e->name) ? $e->name : (isset($e['name']) ? $e['name'] : '');
            if ($id) $eventList[$id] = $name;
        }

        $propertiesData = Properties::getApiDataProvider(array(), 500)->getData();
        $propertyList = array('' => '-- Tất cả --');
        foreach ($propertiesData as $p) {
            $id = isset($p->id) ? $p->id : (isset($p['id']) ? $p['id'] : null);
            $name = isset($p->name) ? $p->name : (isset($p['name']) ? $p['name'] : '');
            if ($id) $propertyList[$id] = $name;
        }

        $periodList = array('' => '-- Tất cả --');
        if ($filterEventId) {
            $periodsData = RegistrationPeriods::getApiDataProvider(array('event_id' => $filterEventId), 100)->getData();
            foreach ($periodsData as $p) {
                $id = isset($p->id) ? $p->id : (isset($p['id']) ? $p['id'] : null);
                $name = isset($p->name) ? $p->name : (isset($p['name']) ? $p['name'] : '');
                if ($id) $periodList[$id] = $name;
            }
        }

        $this->render('admin', array(
            'dpSubmitted' => $dpSubmitted,
            'dpRejected' => $dpRejected,
            'dpApproved' => $dpApproved,
            'dpAll' => $dpAll,
            'countSubmitted' => $countSubmitted,
            'countRejected' => $countRejected,
            'countApproved' => $countApproved,
            'countAll' => $countAll,
            'eventList' => $eventList,
            'propertyList' => $propertyList,
            'periodList' => $periodList,
            'filterEventId' => $filterEventId,
            'filterPropertyId' => $filterPropertyId,
            'filterPeriodId' => $filterPeriodId,
        ));
    }

    /**
     * Xem chi tiết đăng ký để phê duyệt
     */
    public function actionView($id)
    {
        $model = $this->loadModelById($id);

        // Load related names
        if (empty($model->event_name) && $model->event_id) {
            $event = Events::fetchFromApi($model->event_id);
            $model->event_name = $event ? $event->name : '';
        }
        $propertyCode = null;
        if ($model->property_id) {
            $property = Properties::fetchFromApi($model->property_id);
            if ($property) {
                if (empty($model->property_name)) {
                    $model->property_name = $property->name;
                }
                $model->property_code = $property->prefix ? $property->prefix : $property->code;
                $propertyCode = $property->code;
            } else {
                $localProp = Properties::model()->findByPk($model->property_id);
                if ($localProp) {
                    $propertyCode = $localProp->code;
                }
            }
        }
        if (empty($model->period_name) && $model->period_id) {
            $period = RegistrationPeriods::fetchFromApi($model->period_id);
            $model->period_name = $period ? $period->name : '';
        }

        // Load period contents - danh sách nội dung được phép đăng ký trong đợt này
        $periodContentCodes = array();
        if ($model->period_id) {
            $periodContents = RegistrationPeriodContents::getContentsByPeriod($model->period_id);
            foreach ($periodContents as $pc) {
                $code = '';
                if (is_array($pc)) {
                    $code = isset($pc['content_code']) ? $pc['content_code'] : (isset($pc['content']['code']) ? $pc['content']['code'] : '');
                } else {
                    $code = isset($pc->content_code) ? $pc->content_code : (isset($pc->content) && isset($pc->content->code) ? $pc->content->code : '');
                }
                if ($code) {
                    // Normalize code names
                    if ($code === 'sport') $code = 'sports';
                    if ($code === 'competitions') $code = 'competition';
                    if ($code === 'talents') $code = 'talent';
                    if ($code === 'beauty_contests') $code = 'miss';
                    $periodContentCodes[] = $code;
                }
            }
        }

        // Load attendees - chỉ lấy của registration này
        $attendees = Attendees::getByRegistrationId($id);
        $attendeesMap = array();
        foreach ($attendees as $att) {
            $attId = isset($att['id']) ? $att['id'] : null;
            if ($attId) {
                $attendeesMap[$attId] = $att;
            }
        }

        // Load roles
        $rolesData = Roles::getApiDataProvider(array(), 100)->getData();
        $roles = array();
        foreach ($rolesData as $r) {
            $rId = isset($r['id']) ? $r['id'] : (isset($r->id) ? $r->id : null);
            $rName = isset($r['name']) ? $r['name'] : (isset($r->name) ? $r->name : '');
            if ($rId) $roles[$rId] = $rName;
        }

        // Load danh sách nhân sự SMILE của đơn vị (cho form thay thế)
        $staffList = array();
        if ($propertyCode) {
            $staffsData = Staffs::getApiDataProvider(array('property_code' => $propertyCode, 'is_active' => 1), 10000)->getData();
            foreach ($staffsData as $st) {
                $stId = isset($st->id) ? $st->id : (isset($st['id']) ? $st['id'] : null);
                $stName = isset($st->full_name) ? $st->full_name : (isset($st['full_name']) ? $st['full_name'] : '');
                $stCode = isset($st->staff_code) ? $st->staff_code : (isset($st['staff_code']) ? $st['staff_code'] : '');
                $stPos = isset($st->position_name) ? $st->position_name : (isset($st['position_name']) ? $st['position_name'] : '');
                if ($stId) {
                    $staffList[$stId] = array(
                        'id' => $stId,
                        'name' => $stName,
                        'code' => $stCode,
                        'position' => $stPos,
                    );
                }
            }
        }

        // Load transports
        $transportsData = Transports::getApiDataProvider(array(), 100)->getData();
        $transports = array();
        foreach ($transportsData as $t) {
            $tId = isset($t['id']) ? $t['id'] : (isset($t->id) ? $t->id : null);
            $tName = isset($t['name']) ? $t['name'] : (isset($t->name) ? $t->name : '');
            if ($tId) $transports[$tId] = $tName;
        }

        // Load competition registrations - chỉ nếu period có content 'competition'
        $competitionRegistrations = array();
        if (empty($periodContentCodes) || in_array('competition', $periodContentCodes)) {
            $compRegsData = CompetitionRegistrations::getApiDataProvider(array('registration_id' => $id), 200)->getData();
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

                $attendeeId = isset($reg->attendee_id) ? $reg->attendee_id : (isset($reg['attendee_id']) ? $reg['attendee_id'] : null);
                $attendeeInfo = isset($attendeesMap[$attendeeId]) ? $attendeesMap[$attendeeId] : array();

                $competitionRegistrations[$compId]['attendees'][] = array(
                    'id' => isset($reg->id) ? $reg->id : (isset($reg['id']) ? $reg['id'] : null),
                    'attendee_id' => $attendeeId,
                    'attendee_name' => isset($attendeeInfo['full_name']) ? $attendeeInfo['full_name'] : '',
                    'position_name' => isset($attendeeInfo['position_name']) ? $attendeeInfo['position_name'] : '',
                    'division_name' => isset($attendeeInfo['division_name']) ? $attendeeInfo['division_name'] : '',
                );
            }

            // Load competition names if missing
            foreach ($competitionRegistrations as $compId => &$compData) {
                if (empty($compData['competition_name'])) {
                    $comp = Competitions::fetchFromApi($compId);
                    $compData['competition_name'] = $comp ? $comp->name : '';
                }
            }
            unset($compData);
        }

        // Load Sport Teams - bao gồm cả đội liên quân
        $sportTeams = array();
        $sportTeamMembers = array();
        if ($model->event_id && $model->property_id && (empty($periodContentCodes) || in_array('sports', $periodContentCodes))) {
            $apiResult = ApiClient::get(ApiEndpoints::SPORT_TEAM_LIST_BY_PROPERTY, array(
                'property_id' => $model->property_id,
                'event_id' => $model->event_id,
            ));
            $teamsData = array();
            if ($apiResult['success'] && isset($apiResult['data']['data'])) {
                $teamsData = $apiResult['data']['data'];
            } elseif ($apiResult['success'] && isset($apiResult['data']) && is_array($apiResult['data'])) {
                $teamsData = $apiResult['data'];
            }

            foreach ($teamsData as $team) {
                $isObject = is_object($team);
                $teamId = $isObject ? (isset($team->id) ? $team->id : null) : (isset($team['id']) ? $team['id'] : null);
                if ($teamId) {
                    $sportName = $isObject ? (isset($team->sport_name) ? $team->sport_name : '') : (isset($team['sport_name']) ? $team['sport_name'] : '');
                    $sportId = $isObject ? (isset($team->sport_id) ? $team->sport_id : null) : (isset($team['sport_id']) ? $team['sport_id'] : null);

                    if (empty($sportName) && $sportId) {
                        $sport = Sports::fetchFromApi($sportId);
                        $sportName = $sport ? $sport->name : '';
                        if ($isObject) {
                            $team->sport_name = $sportName;
                        } else {
                            $team['sport_name'] = $sportName;
                        }
                    }

                    $teamPropertyId = $isObject ? (isset($team->property_id) ? $team->property_id : null) : (isset($team['property_id']) ? $team['property_id'] : null);

                    if (!$isObject) {
                        $teamObj = new stdClass();
                        foreach ($team as $key => $value) {
                            $teamObj->$key = $value;
                        }
                        $team = $teamObj;
                    }
                    $sportTeams[] = $team;

                    $membersData = array();
                    if (isset($team->members) && is_array($team->members)) {
                        $membersData = $team->members;
                    } else {
                        $membersData = SportTeamMembers::getApiDataProvider(array('sport_team_id' => $teamId), 100)->getData();
                    }

                    $teamPropertyName = isset($team->property_name) ? $team->property_name : '';
                    if (empty($teamPropertyName) && $teamPropertyId == $model->property_id) {
                        $teamPropertyName = $model->property_name;
                    }

                    $enrichedMembers = array();
                    foreach ($membersData as $member) {
                        $memberIsObj = is_object($member);
                        $attId = $memberIsObj ? (isset($member->attendee_id) ? $member->attendee_id : null) : (isset($member['attendee_id']) ? $member['attendee_id'] : null);
                        $attInfo = isset($attendeesMap[$attId]) ? $attendeesMap[$attId] : array();

                        $memberArr = $memberIsObj ? (method_exists($member, 'getAttributes') ? array_merge($member->getAttributes(), get_object_vars($member)) : get_object_vars($member)) : $member;
                        if (empty($memberArr['attendee_name']) && !empty($attInfo['full_name'])) {
                            $memberArr['attendee_name'] = $attInfo['full_name'];
                        }
                        if (empty($memberArr['position_name']) && !empty($attInfo['position_name'])) {
                            $memberArr['position_name'] = $attInfo['position_name'];
                        }
                        if (empty($memberArr['division_name']) && !empty($attInfo['division_name'])) {
                            $memberArr['division_name'] = $attInfo['division_name'];
                        }
                        if (empty($memberArr['gender']) && isset($attInfo['gender'])) {
                            $memberArr['gender'] = $attInfo['gender'];
                        }
                        if (empty($memberArr['property_name']) && !empty($attInfo['property_name'])) {
                            $memberArr['property_name'] = $attInfo['property_name'];
                        }
                        if (empty($memberArr['property_name']) && !empty($teamPropertyName)) {
                            $memberArr['property_name'] = $teamPropertyName;
                        }
                        $enrichedMembers[] = $memberArr;
                    }
                    $sportTeamMembers[$teamId] = $enrichedMembers;
                }
            }
        }

        // Load Beauty Contestants - chỉ nếu period có content 'miss'
        $beautyContestants = array();
        if ($model->event_id && (empty($periodContentCodes) || in_array('miss', $periodContentCodes))) {
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
                                'position_name' => isset($attInfo['position_name']) ? $attInfo['position_name'] : '',
                                'division_name' => isset($attInfo['division_name']) ? $attInfo['division_name'] : '',
                                'candidate_number' => isset($c->candidate_number) ? $c->candidate_number : (isset($c['candidate_number']) ? $c['candidate_number'] : ''),
                                'height_cm' => isset($c->height_cm) ? $c->height_cm : (isset($c['height_cm']) ? $c['height_cm'] : null),
                                'weight_kg' => isset($c->weight_kg) ? $c->weight_kg : (isset($c['weight_kg']) ? $c['weight_kg'] : null),
                                'measurements' => isset($c->measurements) ? $c->measurements : (isset($c['measurements']) ? $c['measurements'] : ''),
                            );
                        }
                    }
                }
            }
        }

        // Load Talent Entries - chỉ nếu period có content 'talent'
        $talentEntries = array();
        $talentEntryMembers = array();
        $allianceTalentEntries = array(); // Tiết mục liên quân mà đơn vị được mời tham gia
        if ($model->property_id && (empty($periodContentCodes) || in_array('talent', $periodContentCodes))) {
            // Lấy talent shows của event
            $showIds = array();
            if ($model->event_id) {
                $showsData = TalentShows::getApiDataProvider(array('event_id' => $model->event_id), 100)->getData();
                foreach ($showsData as $show) {
                    $showId = isset($show->id) ? $show->id : (isset($show['id']) ? $show['id'] : null);
                    if ($showId) $showIds[] = $showId;
                }
            }

            // Lấy tất cả talent entries của event, sau đó filter theo property_id bằng PHP
            // (giống RegistrationsController vì API filter property_id không tin cậy)
            $allEntriesData = array();
            if ($model->event_id) {
                $allEntriesData = TalentEntries::getApiDataProvider(array('event_id' => $model->event_id), 500)->getData();
            }

            $currentPropertyId = (string)$model->property_id;
            $processedEntryIds = array();

            foreach ($allEntriesData as $entry) {
                $entryId = isset($entry->id) ? $entry->id : (isset($entry['id']) ? $entry['id'] : null);
                $entryShowId = isset($entry->show_id) ? $entry->show_id : (isset($entry['show_id']) ? $entry['show_id'] : null);
                $entryPropertyId = isset($entry->property_id) ? (string)$entry->property_id : (isset($entry['property_id']) ? (string)$entry['property_id'] : '');

                // Chỉ lấy entries của đơn vị hiện tại (owner)
                if ($entryId && $entryPropertyId === $currentPropertyId && (empty($showIds) || in_array($entryShowId, $showIds))) {
                    $processedEntryIds[] = $entryId;
                    // Fetch category name if not available
                    if (empty($entry->category_name) && (isset($entry->category_id) || isset($entry['category_id']))) {
                        $catId = isset($entry->category_id) ? $entry->category_id : $entry['category_id'];
                        $cat = TalentCategories::fetchFromApi($catId);
                        if ($cat) {
                            if (is_object($entry)) {
                                $entry->category_name = $cat->name;
                            } else {
                                $entry['category_name'] = $cat->name;
                            }
                        }
                    }

                    if (is_object($entry)) {
                        $entry->video_path = $this->cleanStorageUrl($entry->video_path);
                        $entry->music_path = $this->cleanStorageUrl($entry->music_path);
                    } else {
                        $entry['video_path'] = $this->cleanStorageUrl($entry['video_path']);
                        $entry['music_path'] = $this->cleanStorageUrl($entry['music_path']);
                    }

                    $talentEntries[] = $entry;
                    $membersResult = ApiClient::get(ApiEndpoints::TALENT_ENTRY_MEMBER_LIST, array('entry_id' => $entryId));
                    $membersData = array();
                    if ($membersResult['success'] && isset($membersResult['data'])) {
                        $membersData = isset($membersResult['data']['data']) ? $membersResult['data']['data'] : $membersResult['data'];
                    }
                    $enrichedMembers = array();
                    foreach ($membersData as $member) {
                        $attId = isset($member['attendee_id']) ? $member['attendee_id'] : null;
                        $attInfo = isset($attendeesMap[$attId]) ? $attendeesMap[$attId] : array();
                        $memberArr = is_array($member) ? $member : array_merge($member->attributes, get_object_vars($member));
                        if (empty($memberArr['attendee_name']) && !empty($attInfo['full_name'])) {
                            $memberArr['attendee_name'] = $attInfo['full_name'];
                        }
                        if (empty($memberArr['position_name']) && !empty($attInfo['position_name'])) {
                            $memberArr['position_name'] = $attInfo['position_name'];
                        }
                        if (empty($memberArr['division_name']) && !empty($attInfo['division_name'])) {
                            $memberArr['division_name'] = $attInfo['division_name'];
                        }
                        if (empty($memberArr['property_name']) && !empty($attInfo['property_name'])) {
                            $memberArr['property_name'] = $attInfo['property_name'];
                        }
                        $enrichedMembers[] = $memberArr;
                    }
                    $talentEntryMembers[$entryId] = $enrichedMembers;
                }
            }

            // Tìm các tiết mục liên quân mà đơn vị này được mời tham gia (alliance_org_ids chứa property_id)
            foreach ($allEntriesData as $entry) {
                $entryId = isset($entry->id) ? $entry->id : (isset($entry['id']) ? $entry['id'] : null);
                if (!$entryId || in_array($entryId, $processedEntryIds)) {
                    continue;
                }

                $allianceIds = isset($entry->alliance_org_ids) ? $entry->alliance_org_ids : (isset($entry['alliance_org_ids']) ? $entry['alliance_org_ids'] : '');
                if (empty($allianceIds)) {
                    $allianceIds = isset($entry->alliance_property_ids) ? $entry->alliance_property_ids : (isset($entry['alliance_property_ids']) ? $entry['alliance_property_ids'] : '');
                }

                $idArray = array();
                if (is_array($allianceIds)) {
                    $idArray = $allianceIds;
                } elseif (is_string($allianceIds) && !empty($allianceIds)) {
                    $decoded = json_decode($allianceIds, true);
                    if (is_array($decoded)) {
                        $idArray = $decoded;
                    } else {
                        $idArray = array_filter(array_map('trim', explode(',', $allianceIds)));
                    }
                }

                // Kiểm tra xem property_id hiện tại có trong danh sách alliance không
                if (in_array($model->property_id, $idArray)) {
                    // Fetch category name
                    if (empty($entry->category_name) && (isset($entry->category_id) || isset($entry['category_id']))) {
                        $catId = isset($entry->category_id) ? $entry->category_id : $entry['category_id'];
                        $cat = TalentCategories::fetchFromApi($catId);
                        if ($cat) {
                            if (is_object($entry)) {
                                $entry->category_name = $cat->name;
                            } else {
                                $entry['category_name'] = $cat->name;
                            }
                        }
                    }

                    // Fetch owner property name
                    $ownerPropertyId = isset($entry->property_id) ? $entry->property_id : (isset($entry['property_id']) ? $entry['property_id'] : null);
                    if ($ownerPropertyId && empty($entry->property_name)) {
                        $ownerProp = Properties::fetchFromApi($ownerPropertyId);
                        if ($ownerProp) {
                            if (is_object($entry)) {
                                $entry->property_name = $ownerProp->name;
                            } else {
                                $entry['property_name'] = $ownerProp->name;
                            }
                        }
                    }

                    if (is_object($entry)) {
                        $entry->video_path = $this->cleanStorageUrl($entry->video_path);
                        $entry->music_path = $this->cleanStorageUrl($entry->music_path);
                    } else {
                        $entry['video_path'] = $this->cleanStorageUrl($entry['video_path']);
                        $entry['music_path'] = $this->cleanStorageUrl($entry['music_path']);
                    }

                    $allianceTalentEntries[] = $entry;

                    // Load members cho entry liên quân
                    $membersResult = ApiClient::get(ApiEndpoints::TALENT_ENTRY_MEMBER_LIST, array('entry_id' => $entryId));
                    $membersData = array();
                    if ($membersResult['success'] && isset($membersResult['data'])) {
                        $membersData = isset($membersResult['data']['data']) ? $membersResult['data']['data'] : $membersResult['data'];
                    }
                    $enrichedMembers = array();
                    foreach ($membersData as $member) {
                        $attId = isset($member['attendee_id']) ? $member['attendee_id'] : null;
                        $memberArr = is_array($member) ? $member : get_object_vars($member);
                        // Fetch attendee info from API if not in local map
                        if (empty($memberArr['attendee_name']) && $attId) {
                            $attData = Attendees::fetchFromApi($attId);
                            if ($attData) {
                                $memberArr['attendee_name'] = $attData->full_name;
                                $memberArr['position_name'] = $attData->position_name;
                                $memberArr['division_name'] = $attData->division_name;
                                $memberArr['property_name'] = $attData->property_name;
                            }
                        }
                        $enrichedMembers[] = $memberArr;
                    }
                    $talentEntryMembers[$entryId] = $enrichedMembers;
                }
            }
        }

        // Load alliance property names for all talent entries (cả owned và alliance)
        $talentAllianceProperties = array();
        $allTalentEntries = array_merge($talentEntries, $allianceTalentEntries);
        foreach ($allTalentEntries as $entry) {
            $entryId = isset($entry->id) ? $entry->id : (isset($entry['id']) ? $entry['id'] : null);
            $allianceIds = isset($entry->alliance_org_ids) ? $entry->alliance_org_ids : (isset($entry['alliance_org_ids']) ? $entry['alliance_org_ids'] : '');
            if (empty($allianceIds)) {
                $allianceIds = isset($entry->alliance_property_ids) ? $entry->alliance_property_ids : (isset($entry['alliance_property_ids']) ? $entry['alliance_property_ids'] : '');
            }
            $talentAllianceProperties[$entryId] = array();
            if (!empty($allianceIds)) {
                $idArray = array();
                if (is_array($allianceIds)) {
                    $idArray = $allianceIds;
                } elseif (is_string($allianceIds)) {
                    $decoded = json_decode($allianceIds, true);
                    if (is_array($decoded)) {
                        $idArray = $decoded;
                    } else {
                        $idArray = array_filter(array_map('trim', explode(',', $allianceIds)));
                    }
                }
                foreach ($idArray as $propId) {
                    if ($propId) {
                        $prop = Properties::fetchFromApi($propId);
                        if ($prop) {
                            $talentAllianceProperties[$entryId][] = array(
                                'id' => $propId,
                                'name' => $prop->name,
                            );
                        }
                    }
                }
            }
        }

        $this->render('view', array(
            'model' => $model,
            'attendees' => $attendees,
            'roles' => $roles,
            'transports' => $transports,
            'staffList' => $staffList,
            'competitionRegistrations' => $competitionRegistrations,
            'sportTeams' => $sportTeams,
            'sportTeamMembers' => $sportTeamMembers,
            'beautyContestants' => $beautyContestants,
            'talentEntries' => $talentEntries,
            'allianceTalentEntries' => $allianceTalentEntries,
            'talentEntryMembers' => $talentEntryMembers,
            'talentAllianceProperties' => $talentAllianceProperties,
            'periodContentCodes' => $periodContentCodes,
        ));
    }

    /**
     * Phê duyệt một hoặc tất cả người tham dự
     */
    public function actionApproveAttendee()
    {
        header('Content-Type: application/json');

        if (!Yii::app()->request->isPostRequest) {
            echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
            Yii::app()->end();
        }
        if (!PermissionHelper::can('approveregistrations', 'update')) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không có quyền thực hiện.'));
            Yii::app()->end();
        }

        $ssoUser = AuthHandler::getUser();
        $approvedBy = isset($ssoUser['email']) ? $ssoUser['email'] : null;
        $all = Yii::app()->request->getPost('all');
        $registrationId = Yii::app()->request->getPost('registration_id');

        // Bulk approve all pending attendees
        if ($all && $registrationId) {
            $attendees = Attendees::getByRegistrationId($registrationId);
            $count = 0;
            foreach ($attendees as $att) {
                $status = isset($att['approval_status']) ? (int)$att['approval_status'] : Attendees::APPROVAL_PENDING;
                if ($status == Attendees::APPROVAL_PENDING) {
                    $attendee = new Attendees();
                    $attendee->id = $att['id'];
                    $attendee->approval_status = Attendees::APPROVAL_APPROVED;
                    $attendee->approved_at = date('Y-m-d H:i:s');
                    $attendee->approved_by = $approvedBy;
                    $attendee->updateViaApi();
                    $count++;
                }
            }
            echo CJSON::encode(array('success' => true, 'message' => "Đã duyệt {$count} người tham dự."));
            Yii::app()->end();
        }

        // Single approve
        $attendeeId = Yii::app()->request->getPost('attendee_id');
        $attendee = Attendees::fetchFromApi($attendeeId);

        if (!$attendee) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy người tham dự.'));
            Yii::app()->end();
        }

        $attendee->approval_status = Attendees::APPROVAL_APPROVED;
        $attendee->approved_at = date('Y-m-d H:i:s');
        $attendee->approved_by = $approvedBy;

        $result = $attendee->updateViaApi();

        if ($result['success']) {
            echo CJSON::encode(array('success' => true, 'message' => 'Đã phê duyệt người tham dự.'));
        } else {
            echo CJSON::encode(array('success' => false, 'error' => isset($result['error']) ? $result['error'] : 'Không thể phê duyệt.'));
        }
        Yii::app()->end();
    }

    /**
     * Từ chối một hoặc tất cả người tham dự
     */
    public function actionRejectAttendee()
    {
        header('Content-Type: application/json');

        if (!Yii::app()->request->isPostRequest) {
            echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
            Yii::app()->end();
        }

        if (!PermissionHelper::can('approveregistrations', 'update')) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không có quyền thực hiện.'));
            Yii::app()->end();
        }

        $ssoUser = AuthHandler::getUser();
        $approvedBy = isset($ssoUser['email']) ? $ssoUser['email'] : null;
        $reason = Yii::app()->request->getPost('reason', '');
        $all = Yii::app()->request->getPost('all');
        $registrationId = Yii::app()->request->getPost('registration_id');

        // Bulk reject all pending attendees
        if ($all && $registrationId) {
            $attendees = Attendees::getByRegistrationId($registrationId);
            $count = 0;
            foreach ($attendees as $att) {
                $status = isset($att['approval_status']) ? (int)$att['approval_status'] : Attendees::APPROVAL_PENDING;
                if ($status == Attendees::APPROVAL_PENDING) {
                    $attendee = new Attendees();
                    $attendee->id = $att['id'];
                    $attendee->approval_status = Attendees::APPROVAL_REJECTED;
                    $attendee->note = $reason;
                    $attendee->approved_at = date('Y-m-d H:i:s');
                    $attendee->approved_by = $approvedBy;
                    $attendee->updateViaApi();
                    $count++;
                }
            }
            echo CJSON::encode(array('success' => true, 'message' => "Đã từ chối {$count} người tham dự."));
            Yii::app()->end();
        }

        // Single reject
        $attendeeId = Yii::app()->request->getPost('attendee_id');
        $attendee = Attendees::fetchFromApi($attendeeId);

        if (!$attendee) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy người tham dự.'));
            Yii::app()->end();
        }

        $attendee->approval_status = Attendees::APPROVAL_REJECTED;
        $attendee->note = $reason;
        $attendee->approved_at = date('Y-m-d H:i:s');
        $attendee->approved_by = $approvedBy;

        $result = $attendee->updateViaApi();

        if ($result['success']) {
            echo CJSON::encode(array('success' => true, 'message' => 'Đã từ chối người tham dự.'));
        } else {
            echo CJSON::encode(array('success' => false, 'error' => isset($result['error']) ? $result['error'] : 'Không thể từ chối.'));
        }
        Yii::app()->end();
    }

    /**
     * Phê duyệt toàn bộ đăng ký
     */
    public function actionApproveAll()
    {
        header('Content-Type: application/json');

        if (!Yii::app()->request->isPostRequest) {
            echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
            Yii::app()->end();
        }

        if (!PermissionHelper::can('approveregistrations', 'update')) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không có quyền thực hiện.'));
            Yii::app()->end();
        }

        $registrationId = Yii::app()->request->getPost('registration_id');
        $model = Registrations::fetchFromApi($registrationId);

        if (!$model) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy phiếu đăng ký.'));
            Yii::app()->end();
        }

        $ssoUser = AuthHandler::getUser();
        $approvedBy = isset($ssoUser['email']) ? $ssoUser['email'] : null;

        // Approve all attendees
        $attendees = Attendees::getByRegistrationId($registrationId);
        $successCount = 0;
        foreach ($attendees as $att) {
            $attId = isset($att['id']) ? $att['id'] : null;
            if ($attId) {
                $attendee = Attendees::fetchFromApi($attId);
                if ($attendee && $attendee->approval_status != Attendees::APPROVAL_APPROVED) {
                    $attendee->approval_status = Attendees::APPROVAL_APPROVED;
                    $attendee->approved_at = date('Y-m-d H:i:s');
                    $attendee->approved_by = $approvedBy;
                    $result = $attendee->updateViaApi();
                    if ($result['success']) {
                        $successCount++;
                    }
                }
            }
        }

        // Approve registration
        $model->status = Registrations::STATUS_APPROVED;
        $model->reviewed_at = date('Y-m-d H:i:s');
        $model->reviewed_by = $approvedBy;
        $result = $model->updateViaApi();

        if ($result['success']) {
            // Ghi vào registration_approvals
            $approval = RegistrationApprovals::getActiveByRegistrationId($registrationId);
            $ssoId = isset($ssoUser['id']) ? $ssoUser['id'] : null;
            $fullName = isset($ssoUser['full_name']) ? $ssoUser['full_name'] : $approvedBy;
            $stepIndex = $approval ? $approval->current_index : 1;

            if ($approval) {
                RegistrationApprovals::approveViaApi($approval->id, $ssoId, $fullName);
            }

            // Ghi log duyệt
            RegistrationApprovalLogs::createLog(
                $registrationId,
                RegistrationApprovalLogs::ACTION_APPROVED,
                $stepIndex,
                'Phê duyệt',
                $ssoId,
                $fullName
            );

            // Tự động gửi email xác nhận cho đơn vị sau khi phê duyệt (người nhận lấy từ cột mail_confirm của property; submitted_by đang tạm tắt)
            $emailNote = '';
            try {
                $emailResult = EmailHelper::sendRegistrationConfirmation($registrationId);
                if (empty($emailResult['success'])) {
                    $emailNote = ' (Lưu ý: chưa gửi được email xác nhận)';
                }
            } catch (Exception $e) {
                Yii::log('Auto send approval confirmation email failed: ' . $e->getMessage(), 'warning', 'application.controllers.ApproveRegistrationsController');
                $emailNote = ' (Lưu ý: chưa gửi được email xác nhận)';
            }

            echo CJSON::encode(array(
                'success' => true,
                'message' => "Đã phê duyệt phiếu đăng ký và {$successCount} người tham dự." . $emailNote,
            ));
        } else {
            echo CJSON::encode(array('success' => false, 'error' => 'Không thể phê duyệt phiếu đăng ký.'));
        }
        Yii::app()->end();
    }

    /**
     * Từ chối toàn bộ đăng ký
     */
    public function actionRejectAll()
    {
        header('Content-Type: application/json');

        if (!Yii::app()->request->isPostRequest) {
            echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
            Yii::app()->end();
        }

        if (!PermissionHelper::can('approveregistrations', 'update')) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không có quyền thực hiện.'));
            Yii::app()->end();
        }

        $registrationId = Yii::app()->request->getPost('registration_id');
        $reason = Yii::app()->request->getPost('reason', '');
        $model = Registrations::fetchFromApi($registrationId);

        if (!$model) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy phiếu đăng ký.'));
            Yii::app()->end();
        }

        $ssoUser = AuthHandler::getUser();
        $approvedBy = isset($ssoUser['email']) ? $ssoUser['email'] : null;

        // Reject all attendees
        $attendees = Attendees::getByRegistrationId($registrationId);
        $rejectCount = 0;
        foreach ($attendees as $att) {
            $attId = isset($att['id']) ? $att['id'] : null;
            if ($attId) {
                $attendee = Attendees::fetchFromApi($attId);
                if ($attendee && $attendee->approval_status != Attendees::APPROVAL_REJECTED) {
                    $attendee->approval_status = Attendees::APPROVAL_REJECTED;
                    $attendee->note = $reason;
                    $attendee->approved_at = date('Y-m-d H:i:s');
                    $attendee->approved_by = $approvedBy;
                    $result = $attendee->updateViaApi();
                    if ($result['success']) {
                        $rejectCount++;
                    }
                }
            }
        }

        // Reject registration
        $model->status = Registrations::STATUS_REJECTED;
        $model->reviewed_at = date('Y-m-d H:i:s');
        $model->reviewed_by = $approvedBy;
        $model->rejection_reason = $reason;
        $result = $model->updateViaApi();

        if ($result['success']) {
            // Ghi vào registration_approvals
            $approval = RegistrationApprovals::getActiveByRegistrationId($registrationId);
            $ssoId = isset($ssoUser['id']) ? $ssoUser['id'] : null;
            $fullName = isset($ssoUser['full_name']) ? $ssoUser['full_name'] : $approvedBy;
            $stepIndex = $approval ? $approval->current_index : 1;

            if ($approval) {
                RegistrationApprovals::rejectViaApi($approval->id, $ssoId, $fullName, $reason);
            }

            // Ghi log từ chối
            RegistrationApprovalLogs::createLog(
                $registrationId,
                RegistrationApprovalLogs::ACTION_REJECTED,
                $stepIndex,
                'Từ chối',
                $ssoId,
                $fullName,
                $reason
            );

            echo CJSON::encode(array(
                'success' => true,
                'message' => "Đã từ chối phiếu đăng ký và {$rejectCount} người tham dự.",
            ));
        } else {
            echo CJSON::encode(array('success' => false, 'error' => 'Không thể từ chối phiếu đăng ký.'));
        }
        Yii::app()->end();
    }

    public function actionReturn()
    {
        header('Content-Type: application/json');

        if (!Yii::app()->request->isPostRequest) {
            echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
            Yii::app()->end();
        }

        if (!PermissionHelper::can('approveregistrations', 'update')) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không có quyền thực hiện.'));
            Yii::app()->end();
        }

        $registrationId = Yii::app()->request->getPost('registration_id');
        $reason = Yii::app()->request->getPost('reason', '');
        $model = Registrations::fetchFromApi($registrationId);

        if (!$model) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy phiếu đăng ký.'));
            Yii::app()->end();
        }

        $ssoUser = AuthHandler::getUser();
        $reviewedBy = isset($ssoUser['email']) ? $ssoUser['email'] : null;

        $model->status = Registrations::STATUS_REJECTED; // Trả về = chuyển về draft
        $model->reviewed_at = date('Y-m-d H:i:s');
        $model->reviewed_by = $reviewedBy;
        $model->rejection_reason = $reason;
        $result = $model->updateViaApi();

        if ($result['success']) {
            // Ghi vào registration_approvals
            $approval = RegistrationApprovals::getActiveByRegistrationId($registrationId);
            $ssoId = isset($ssoUser['id']) ? $ssoUser['id'] : null;
            $fullName = isset($ssoUser['full_name']) ? $ssoUser['full_name'] : $reviewedBy;
            $stepIndex = $approval ? $approval->current_index : 1;

            if ($approval) {
                RegistrationApprovals::revisionViaApi($approval->id, $ssoId, $fullName, 0, $reason);
            }

            // Ghi log yêu cầu chỉnh sửa
            RegistrationApprovalLogs::createLog(
                $registrationId,
                RegistrationApprovalLogs::ACTION_REVISION,
                $stepIndex,
                'Yêu cầu chỉnh sửa',
                $ssoId,
                $fullName,
                $reason,
                0
            );

            echo CJSON::encode(array(
                'success' => true,
                'message' => 'Đã trả phiếu đăng ký về đơn vị để chỉnh sửa.',
            ));
        } else {
            $errorMsg = isset($result['message']) ? $result['message'] : (isset($result['error']) ? $result['error'] : 'Không thể trả phiếu đăng ký về.');
            Yii::log('Return registration failed: ' . print_r($result, true), 'error', 'application.controllers.ApproveRegistrationsController');
            echo CJSON::encode(array('success' => false, 'error' => $errorMsg, 'debug' => $result));
        }
        Yii::app()->end();
    }

    /**
     * Gửi email xác nhận thông tin đăng ký cho đơn vị
     */
    public function actionSendEmail()
    {
        header('Content-Type: application/json');

        // Tắt CWebLogRoute để tránh chèn HTML log vào chuỗi JSON
        foreach (Yii::app()->log->routes as $route) {
            if ($route instanceof CWebLogRoute) {
                $route->enabled = false;
            }
        }

        if (!Yii::app()->request->isPostRequest) {
            echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
            Yii::app()->end();
        }

        if (!PermissionHelper::can('approveregistrations', 'update')) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không có quyền thực hiện.'));
            Yii::app()->end();
        }

        $registrationId = Yii::app()->request->getPost('registration_id');
        $recipientEmail = trim(Yii::app()->request->getPost('recipient_email', ''));

        if (!$registrationId) {
            echo CJSON::encode(array('success' => false, 'error' => 'Thiếu ID phiếu đăng ký.'));
            Yii::app()->end();
        }

        $result = EmailHelper::sendRegistrationConfirmation($registrationId, $recipientEmail);
        echo CJSON::encode($result);
        Yii::app()->end();
    }

    /**
     * Lấy email người nhận mặc định cho phiếu đăng ký (cột mail_confirm của đơn vị/property).
     * Dùng để đổ sẵn vào ô email khi mở modal gửi mail.
     */
    public function actionGetMailRecipient($registration_id)
    {
        header('Content-Type: application/json');

        $model = Registrations::fetchFromApi($registration_id);
        if ($model === null) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy phiếu đăng ký.'));
            Yii::app()->end();
        }

        $email = '';
        if (!empty($model->property_id)) {
            $property = Properties::fetchFromApi($model->property_id);
            if ($property !== null && !empty($property->mail_confirm)) {
                $email = $property->mail_confirm;
            }
        }

        echo CJSON::encode(array('success' => true, 'email' => $email));
        Yii::app()->end();
    }

    /**
     * Trả về bản kê nội dung (đội thể thao, cuộc thi, vai trò) của 1 người tham dự.
     * Dùng để dựng modal Thay thế / Huỷ tư cách.
     */
    public function actionParticipationSummary($attendee_id)
    {
        header('Content-Type: application/json');

        if (!PermissionHelper::can('approveregistrations', 'update')) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không có quyền thực hiện.'));
            Yii::app()->end();
        }

        $attendee = Attendees::fetchFromApi($attendee_id);
        if (!$attendee) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy người tham dự.'));
            Yii::app()->end();
        }

        $summary = Attendees::getParticipationSummary($attendee_id);

        // Trạng thái đã in thẻ: ưu tiên cờ từ API, nếu thiếu thì suy ra từ badge
        // (có badge đang hiệu lực với print_count > 0 = đã in).
        $badgePrinted = isset($attendee->badge_printed) ? (int)$attendee->badge_printed : 0;
        if (!$badgePrinted) {
            foreach (Badges::getByAttendeeId($attendee_id) as $badge) {
                $printCount = is_array($badge)
                    ? (isset($badge['print_count']) ? (int)$badge['print_count'] : 0)
                    : (isset($badge->print_count) ? (int)$badge->print_count : 0);
                if ($printCount > 0) {
                    $badgePrinted = 1;
                    break;
                }
            }
        }

        echo CJSON::encode(array(
            'success' => true,
            'attendee' => array(
                'id' => $attendee->id,
                'full_name' => $attendee->full_name,
                'position_name' => $attendee->position_name,
                'division_name' => $attendee->division_name,
                'badge_printed' => $badgePrinted,
            ),
            'summary' => $summary,
        ));
        Yii::app()->end();
    }

    /**
     * Huỷ tư cách 1 người tham dự: gỡ đăng ký thi nghiệp vụ + vai trò, đánh dấu huỷ.
     * Xử lý đội thể thao (huỷ đội / captain) sẽ bổ sung ở Slice 3.
     */
    public function actionWithdrawAttendee()
    {
        header('Content-Type: application/json');

        if (!Yii::app()->request->isPostRequest) {
            echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
            Yii::app()->end();
        }
        if (!PermissionHelper::can('approveregistrations', 'update')) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không có quyền thực hiện.'));
            Yii::app()->end();
        }

        $attendeeId = Yii::app()->request->getPost('attendee_id');
        $reason = trim(Yii::app()->request->getPost('reason', ''));

        if (!$attendeeId) {
            echo CJSON::encode(array('success' => false, 'error' => 'Thiếu thông tin người tham dự.'));
            Yii::app()->end();
        }
        if ($reason === '') {
            echo CJSON::encode(array('success' => false, 'error' => 'Vui lòng nhập lý do huỷ tư cách.'));
            Yii::app()->end();
        }

        $attendee = Attendees::fetchFromApi($attendeeId);
        if (!$attendee) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy người tham dự.'));
            Yii::app()->end();
        }

        $ssoUser = AuthHandler::getUser();
        $email = isset($ssoUser['email']) ? $ssoUser['email'] : null;

        // Chụp snapshot nội dung trước khi gỡ (dùng ghi lịch sử sau khi huỷ)
        $summary = Attendees::getParticipationSummary($attendeeId);

        // 1. Gỡ đăng ký thi nghiệp vụ
        $compRegs = CompetitionRegistrations::getApiDataProvider(array('attendee_id' => $attendeeId), 500)->getData();
        foreach ($compRegs as $reg) {
            $regId = isset($reg->id) ? $reg->id : (isset($reg['id']) ? $reg['id'] : null);
            $regAttId = isset($reg->attendee_id) ? $reg->attendee_id : (isset($reg['attendee_id']) ? $reg['attendee_id'] : null);
            if ($regId && $regAttId == $attendeeId) {
                CompetitionRegistrations::deleteViaApi($regId);
            }
        }

        // 1b. Gỡ đăng ký thi Miss (thí sinh sắc đẹp)
        foreach ($summary['beauty_contests'] as $bc) {
            if (!empty($bc['contestant_id'])) {
                BeautyContestants::deleteViaApi($bc['contestant_id']);
            }
        }

        // 2. Gỡ vai trò
        foreach (AttendeeRoles::getByAttendeeId($attendeeId) as $role) {
            if (isset($role['id'])) {
                AttendeeRoles::deleteViaApi($role['id']);
            }
        }

        // 3. Xử lý đội thể thao
        $cancelTeamIds = Yii::app()->request->getPost('cancel_team_ids', array());
        if (!is_array($cancelTeamIds)) {
            $cancelTeamIds = array();
        }
        $newCaptains = Yii::app()->request->getPost('new_captain', array());
        if (!is_array($newCaptains)) {
            $newCaptains = array();
        }

        $memberships = SportTeamMembers::getMembershipsByAttendee($attendeeId);
        $cancelledTeams = array();
        foreach ($memberships as $m) {
            $teamId = $m['sport_team_id'];
            $memberId = $m['member_id'];
            if (in_array($teamId, $cancelTeamIds) || in_array((string)$teamId, $cancelTeamIds)) {
                // Huỷ cả đội (một lần)
                if (!isset($cancelledTeams[$teamId])) {
                    foreach (SportTeamMembers::getTeamMemberBriefs($teamId) as $tm) {
                        if (!empty($tm['member_id'])) {
                            SportTeamMembers::deleteViaApi($tm['member_id']);
                        }
                    }
                    SportTeams::deleteViaApi($teamId);
                    $cancelledTeams[$teamId] = true;
                }
            } else {
                // Chỉ gỡ người khỏi đội
                if ($memberId) {
                    SportTeamMembers::deleteViaApi($memberId);
                }
                // Nếu người bị gỡ là đội trưởng và có chỉ định captain mới
                if (!empty($m['is_captain']) && !empty($newCaptains[$teamId])) {
                    SportTeamMembers::assignCaptain($newCaptains[$teamId]);
                }
            }
        }

        // 4. Đánh dấu huỷ tư cách
        $result = Attendees::withdrawViaApi($attendeeId, $reason, $email);

        if (isset($result['success']) && $result['success']) {
            // 4b. Vô hiệu thẻ/QR đã cấp cho người này (nếu có) — thẻ không còn giá trị
            Badges::revokeByAttendee($attendeeId);

            // 5. Ghi lịch sử huỷ tư cách (audit + email xác nhận đơn vị)
            $affectedSports = array();
            $withdrawCancelledTeams = array();
            foreach ($summary['sport_teams'] as $t) {
                $entry = array(
                    'team_id' => $t['sport_team_id'],
                    'sport_name' => $t['sport_name'],
                    'team_name' => $t['team_name'],
                    'jersey_number' => $t['jersey_number'],
                    'is_captain' => $t['is_captain'],
                );
                if (in_array($t['sport_team_id'], $cancelTeamIds) || in_array((string)$t['sport_team_id'], $cancelTeamIds)) {
                    $withdrawCancelledTeams[] = $entry;
                } else {
                    $affectedSports[] = $entry;
                }
            }
            $affectedCompetitions = array();
            foreach ($summary['competitions'] as $c) {
                $affectedCompetitions[] = array(
                    'competition_id' => $c['competition_id'],
                    'competition_name' => $c['competition_name'],
                    'candidate_number' => $c['candidate_number'],
                );
            }
            $affectedBeautyContests = array();
            foreach ($summary['beauty_contests'] as $bc) {
                $affectedBeautyContests[] = array(
                    'contest_id' => $bc['contest_id'],
                    'contest_name' => $bc['contest_name'],
                    'candidate_number' => $bc['candidate_number'],
                );
            }
            $affectedRoles = array();
            foreach ($summary['roles'] as $r) {
                $affectedRoles[] = array(
                    'role_id' => $r['role_id'],
                    'role_name' => $r['role_name'],
                );
            }

            AttendeeReplacements::record(array(
                'registration_id' => $attendee->registration_id,
                'event_id' => $attendee->event_id,
                'property_id' => $attendee->property_id,
                'action' => AttendeeReplacements::ACTION_WITHDRAW,
                'old_attendee_id' => $attendeeId,
                'old_attendee_name' => $attendee->full_name,
                'old_staff_code' => isset($attendee->staff_code) ? $attendee->staff_code : null,
                'affected_contents' => array(
                    'sports' => $affectedSports,
                    'competitions' => $affectedCompetitions,
                    'beauty_contests' => $affectedBeautyContests,
                    'roles' => $affectedRoles,
                ),
                'cancelled_teams' => $withdrawCancelledTeams,
                'reason' => $reason,
                'performed_by' => $email,
            ));

            Yii::log("Huỷ tư cách attendee #{$attendeeId} bởi {$email}. Lý do: {$reason}", 'info', 'application.controllers.ApproveRegistrationsController');
            echo CJSON::encode(array('success' => true, 'message' => 'Đã huỷ tư cách người tham dự.'));
        } else {
            $err = isset($result['error']) ? $result['error'] : (isset($result['message']) ? $result['message'] : 'Không thể huỷ tư cách.');
            echo CJSON::encode(array('success' => false, 'error' => $err));
        }
        Yii::app()->end();
    }

    /**
     * Kiểm tra nhân sự chọn thay thế đã từng đăng ký attendee trước đây chưa.
     * Trả về thông tin attendee cũ + các đường dẫn file ảnh/hồ sơ đã có để tái sử dụng.
     */
    public function actionCheckStaffAttendee()
    {
        header('Content-Type: application/json');

        $staffId = Yii::app()->request->getParam('staff_id');
        $idCard = trim(Yii::app()->request->getParam('id_card', ''));
        $staffCode = trim(Yii::app()->request->getParam('staff_code', ''));
        $registrationId = Yii::app()->request->getParam('registration_id');

        if (!$staffId && $idCard === '' && $staffCode === '') {
            echo CJSON::encode(array('success' => true, 'has_attendee' => false));
            Yii::app()->end();
        }

        // Dùng chung logic chọn hồ sơ CÓ ẢNH đầy đủ nhất với luồng thay thế
        // để modal hiển thị preview đúng với những gì sẽ thực sự được lưu.
        // Loại các bản ghi thuộc chính đăng ký đang thao tác (chỉ lấy từ đăng ký khác).
        $detail = $this->resolveExistingProfile(null, $staffId, $staffCode, $idCard, null, $registrationId);

        if ($detail) {
            $portrait = (!empty($detail->portrait_path)) ? $detail->portrait_path : (!empty($detail->photo_path) ? $detail->photo_path : '');

            echo CJSON::encode(array(
                'success' => true,
                'has_attendee' => true,
                'attendee' => array(
                    'id' => $detail->id,
                    'full_name' => isset($detail->full_name) ? $detail->full_name : '',
                    'position' => !empty($detail->position_name) ? $detail->position_name : (isset($detail->position) ? $detail->position : ''),
                    'id_card' => isset($detail->id_card) ? $detail->id_card : '',
                    'portrait_path' => $portrait,
                    'cccd_front_path' => isset($detail->cccd_front_path) ? $detail->cccd_front_path : '',
                    'cccd_back_path' => isset($detail->cccd_back_path) ? $detail->cccd_back_path : '',
                    'contract_path' => isset($detail->contract_path) ? $detail->contract_path : '',
                    'role_id' => isset($detail->role_id) ? $detail->role_id : '',
                ),
            ));
        } else {
            echo CJSON::encode(array('success' => true, 'has_attendee' => false));
        }
        Yii::app()->end();
    }

    /**
     * Thay thế 1 người tham dự bằng người khác (SMILE hoặc thủ công).
     * Người thay kế thừa approved + đội được tích + toàn bộ cuộc thi (số báo danh mới do backend cấp) + vai trò.
     * Đội không tích sẽ bị huỷ. Số báo danh cũ không kế thừa.
     */
    public function actionReplaceAttendee()
    {
        header('Content-Type: application/json');

        if (!Yii::app()->request->isPostRequest) {
            echo CJSON::encode(array('success' => false, 'error' => 'Yêu cầu không hợp lệ.'));
            Yii::app()->end();
        }
        if (!PermissionHelper::can('approveregistrations', 'update')) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không có quyền thực hiện.'));
            Yii::app()->end();
        }

        $req = Yii::app()->request;
        $oldId = $req->getPost('attendee_id');
        $reason = trim($req->getPost('reason', ''));
        $staffId = $req->getPost('staff_id');
        $fullName = trim($req->getPost('full_name', ''));

        if (!$oldId || $reason === '' || (!$staffId && $fullName === '')) {
            echo CJSON::encode(array('success' => false, 'error' => 'Thiếu thông tin bắt buộc (người bị thay, lý do, người thay).'));
            Yii::app()->end();
        }

        $oldAttendee = Attendees::fetchFromApi($oldId);
        if (!$oldAttendee) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy người bị thay.'));
            Yii::app()->end();
        }

        $ssoUser = AuthHandler::getUser();
        $email = isset($ssoUser['email']) ? $ssoUser['email'] : null;

        // Thông tin người thay: ưu tiên SMILE
        $position = trim($req->getPost('position', ''));
        $idCard = trim($req->getPost('id_card', ''));
        $staffCode = null;
        if ($staffId) {
            $staff = Staffs::fetchFromApi($staffId);
            if ($staff) {
                if ($fullName === '') { $fullName = $staff->full_name; }
                if ($position === '') { $position = isset($staff->position_name) ? $staff->position_name : ''; }
                $staffCode = isset($staff->staff_code) ? $staff->staff_code : null;
            }
        }

        // 1. Tạo người thay (kế thừa approved)
        $new = new Attendees();
        $new->event_id = $req->getPost('event_id');
        $new->registration_id = $req->getPost('registration_id');
        $new->property_id = $req->getPost('property_id');
        $new->full_name = $fullName;
        $new->position = $position;
        $new->position_name = $position;
        $new->id_card = $idCard;
        if ($staffId) { $new->staff_id = $staffId; }
        if ($staffCode) { $new->staff_code = $staffCode; }

        $postedRoles = $req->getPost('role_id', array());
        if (is_array($postedRoles)) { $postedRoles = implode(', ', $postedRoles); }
        $new->role_id = $postedRoles !== '' ? $postedRoles : $oldAttendee->role_id;
        $new->transport_id = $oldAttendee->transport_id;
        $new->approval_status = Attendees::APPROVAL_APPROVED;
        $new->approved_by = $email;

        // Tìm hồ sơ attendee cũ của nhân sự này (nếu có) để tái sử dụng ảnh/hồ sơ.
        // LƯU Ý: một nhân sự có thể có NHIỀU bản ghi attendee (bản nháp, bản thay thế cũ...),
        // trong đó chỉ một số bản có ảnh/hồ sơ. Phải chọn bản CÓ ẢNH nhiều nhất,
        // KHÔNG lấy bừa bản ghi đầu tiên (dễ trúng bản rỗng → attendee mới bị thiếu ảnh).
        $existingAttendeeId = $req->getPost('existing_attendee_id');
        $existingAttendee = $this->resolveExistingProfile($existingAttendeeId, $staffId, $staffCode, $idCard, $oldId, $req->getPost('registration_id'));

        $uploads = $this->handleReplaceUpload();

        // URL file từ hồ sơ cũ được frontend gửi trực tiếp qua hidden inputs
        $postedFileUrls = array(
            'portrait_path'   => trim($req->getPost('existing_portrait_url', '')),
            'cccd_front_path' => trim($req->getPost('existing_cccd_front_url', '')),
            'cccd_back_path'  => trim($req->getPost('existing_cccd_back_url', '')),
            'contract_path'   => trim($req->getPost('existing_contract_url', '')),
        );

        $fileMap = array(
            'portrait_path' => array('portrait_path', 'photo_path'),
            'cccd_front_path' => array('cccd_front_path'),
            'cccd_back_path' => array('cccd_back_path'),
            'contract_path' => array('contract_path'),
        );
        foreach ($fileMap as $targetAttr => $sourceAttrs) {
            if (isset($uploads[$targetAttr]) && !empty($uploads[$targetAttr])) {
                // Ưu tiên 1: file upload mới
                $new->$targetAttr = $uploads[$targetAttr];
            } elseif (!empty($postedFileUrls[$targetAttr])) {
                // Ưu tiên 2: URL từ hồ sơ cũ (gửi qua hidden input)
                $new->$targetAttr = $postedFileUrls[$targetAttr];
            } elseif ($existingAttendee) {
                // Ưu tiên 3: copy từ bản ghi attendee cũ qua API
                foreach ($sourceAttrs as $sAttr) {
                    if (isset($existingAttendee->$sAttr) && !empty($existingAttendee->$sAttr)) {
                        $new->$targetAttr = $existingAttendee->$sAttr;
                        break;
                    }
                }
            }
        }

        // DEBUG: ghi lại quá trình resolve ảnh/hồ sơ để chẩn đoán vì sao attendee mới thiếu ảnh.
        Yii::log(sprintf(
            "[REPLACE] existing_attendee_id=%s | existingAttendee=%s | uploads=[%s] | postedUrls=[%s] | resolved: portrait=%s cccd_front=%s cccd_back=%s contract=%s",
            $existingAttendeeId ?: '(none)',
            $existingAttendee ? ('#' . $existingAttendee->id) : 'NULL',
            implode(',', array_keys($uploads)),
            implode(',', array_keys(array_filter($postedFileUrls, function ($v) { return $v !== ''; }))),
            $new->portrait_path ?: '-',
            $new->cccd_front_path ?: '-',
            $new->cccd_back_path ?: '-',
            $new->contract_path ?: '-'
        ), 'info', 'application.controllers.ApproveRegistrationsController');

        $storeResult = $new->storeViaApi();
        Yii::log('[REPLACE] storeResult=' . CJSON::encode($storeResult), 'info', 'application.controllers.ApproveRegistrationsController');
        $newId = $this->extractNewId($storeResult);
        if (!$newId) {
            $err = isset($storeResult['error']) ? $storeResult['error'] : 'Không thể tạo người thay.';
            echo CJSON::encode(array('success' => false, 'error' => $err));
            Yii::app()->end();
        }

        // Kể từ đây người thay đã tồn tại → kế thừa nội dung của người bị thay
        $summary = Attendees::getParticipationSummary($oldId);
        $inheritTeamIds = $req->getPost('inherit_team_ids', array());
        if (!is_array($inheritTeamIds)) { $inheritTeamIds = array(); }
        $inheritTeamIds = array_map('strval', $inheritTeamIds);

        // 2. Đội thể thao
        foreach ($summary['sport_teams'] as $t) {
            $tid = $t['sport_team_id'];
            if (in_array((string)$tid, $inheritTeamIds)) {
                // Kế thừa: thêm người thay vào đội, gỡ người cũ
                $m = new SportTeamMembers();
                $m->sport_team_id = $tid;
                $m->attendee_id = $newId;
                $m->name = $fullName;
                $m->jersey_number = $t['jersey_number'];
                $m->position = $t['position'];
                $m->is_captain = $t['is_captain'];
                $m->storeViaApi();
                if (!empty($t['member_id'])) {
                    SportTeamMembers::deleteViaApi($t['member_id']);
                }
            } else {
                // Không tích: huỷ cả đội
                foreach (SportTeamMembers::getTeamMemberBriefs($tid) as $tm) {
                    if (!empty($tm['member_id'])) {
                        SportTeamMembers::deleteViaApi($tm['member_id']);
                    }
                }
                SportTeams::deleteViaApi($tid);
            }
        }

        // 3. Thi nghiệp vụ (kế thừa hết, cấp số báo danh mới → để trống candidate_number)
        foreach ($summary['competitions'] as $c) {
            $cr = new CompetitionRegistrations();
            $cr->competition_id = $c['competition_id'];
            $cr->registration_id = $req->getPost('registration_id');
            $cr->attendee_id = $newId;
            $cr->status = CompetitionRegistrations::STATUS_PENDING;
            $cr->storeViaApi();
            if (!empty($c['registration_id'])) {
                CompetitionRegistrations::deleteViaApi($c['registration_id']);
            }
        }

        // 3b. Thi Miss: chỉ huỷ đăng ký của người cũ, KHÔNG kế thừa cho người thay
        // (hồ sơ thí sinh gồm nhân trắc/ảnh mang tính cá nhân, không chuyển được).
        foreach ($summary['beauty_contests'] as $bc) {
            if (!empty($bc['contestant_id'])) {
                BeautyContestants::deleteViaApi($bc['contestant_id']);
            }
        }

        // 4. Vai trò: nếu admin không chọn thì kế thừa của người cũ
        if ($postedRoles === '') {
            foreach ($summary['roles'] as $r) {
                $ar = new AttendeeRoles();
                $ar->attendee_id = $newId;
                $ar->role_id = $r['role_id'];
                $ar->storeViaApi();
            }
        }
        foreach ($summary['roles'] as $r) {
            if (!empty($r['attendee_role_id'])) {
                AttendeeRoles::deleteViaApi($r['attendee_role_id']);
            }
        }

        // 5. Đánh dấu người bị thay là huỷ tư cách (đã thay thế)
        Attendees::withdrawViaApi($oldId, 'Đã được thay thế. ' . $reason, $email);

        // 5b. Vô hiệu thẻ/QR của người bị thay (nếu đã in). Người thay (B) là bản ghi
        // mới chưa có badge nên sẽ tự nằm trong danh sách "cần in".
        Badges::revokeByAttendee($oldId);

        // 6. Ghi lịch sử thay thế (audit + email xác nhận đơn vị)
        $affectedSports = array();
        $cancelledTeams = array();
        foreach ($summary['sport_teams'] as $t) {
            $entry = array(
                'team_id' => $t['sport_team_id'],
                'sport_name' => $t['sport_name'],
                'team_name' => $t['team_name'],
                'jersey_number' => $t['jersey_number'],
                'is_captain' => $t['is_captain'],
            );
            if (in_array((string)$t['sport_team_id'], $inheritTeamIds)) {
                $affectedSports[] = $entry;
            } else {
                $cancelledTeams[] = $entry;
            }
        }
        $affectedCompetitions = array();
        foreach ($summary['competitions'] as $c) {
            $affectedCompetitions[] = array(
                'competition_id' => $c['competition_id'],
                'competition_name' => $c['competition_name'],
                'candidate_number' => $c['candidate_number'],
            );
        }
        $affectedBeautyContests = array();
        foreach ($summary['beauty_contests'] as $bc) {
            $affectedBeautyContests[] = array(
                'contest_id' => $bc['contest_id'],
                'contest_name' => $bc['contest_name'],
                'candidate_number' => $bc['candidate_number'],
            );
        }
        $affectedRoles = array();
        foreach ($summary['roles'] as $r) {
            $affectedRoles[] = array(
                'role_id' => $r['role_id'],
                'role_name' => $r['role_name'],
            );
        }

        AttendeeReplacements::record(array(
            'registration_id' => $req->getPost('registration_id'),
            'event_id' => $req->getPost('event_id'),
            'property_id' => $req->getPost('property_id'),
            'action' => AttendeeReplacements::ACTION_REPLACE,
            'old_attendee_id' => $oldId,
            'old_attendee_name' => $oldAttendee->full_name,
            'old_staff_code' => isset($oldAttendee->staff_code) ? $oldAttendee->staff_code : null,
            'new_attendee_id' => $newId,
            'new_attendee_name' => $fullName,
            'new_staff_code' => $staffCode,
            'affected_contents' => array(
                'sports' => $affectedSports,
                'competitions' => $affectedCompetitions,
                'roles' => $affectedRoles,
            ),
            'cancelled_teams' => $cancelledTeams,
            'reason' => $reason,
            'performed_by' => $email,
        ));

        Yii::log("Thay thế attendee #{$oldId} bằng #{$newId} bởi {$email}. Lý do: {$reason}", 'info', 'application.controllers.ApproveRegistrationsController');
        echo CJSON::encode(array('success' => true, 'message' => 'Đã thay thế người tham dự thành công.'));
        Yii::app()->end();
    }

    /**
     * Trích id bản ghi attendee mới từ kết quả ApiClient.
     */
    private function extractNewId($result)
    {
        if (!isset($result['success']) || !$result['success'] || !isset($result['data'])) {
            return null;
        }
        $data = $result['data'];
        if (isset($data['data']['id'])) { return $data['data']['id']; }
        if (isset($data['id'])) { return $data['id']; }
        return null;
    }

    /**
     * Chọn hồ sơ attendee cũ CỦA ĐÚNG NHÂN SỰ, có ẢNH/HỒ SƠ đầy đủ nhất để tái sử dụng khi thay thế.
     *
     * Một nhân sự có thể có nhiều bản ghi attendee (nháp, bản thay thế trước...),
     * chỉ một số bản có ảnh. Hàm chọn bản CÓ NHIỀU ẢNH nhất — nhưng CHỈ trong số các
     * bản ghi ĐÚNG danh tính (staff_code / staff_id / id_card). Bộ lọc staff_id của API
     * không đáng tin, nên phải tự xác thực từng ứng viên để tránh lấy nhầm người khác
     * (người tình cờ có nhiều ảnh hơn).
     *
     * @param mixed  $preferredId  id gợi ý từ frontend (existing_attendee_id)
     * @param mixed  $staffId
     * @param string $staffCode
     * @param string $idCard
     * @param mixed  $excludeId    id người đang bị thay (không tự chọn lại)
     * @return Attendees|null       bản ghi chi tiết tốt nhất, hoặc null
     */
    private function resolveExistingProfile($preferredId, $staffId, $staffCode, $idCard, $excludeId, $excludeRegistrationId = null)
    {
        // Không có tiêu chí danh tính nào → không thể xác thực an toàn.
        if (!$staffId && $staffCode === '' && $idCard === '') {
            return null;
        }

        // LƯU Ý QUAN TRỌNG: API danh sách attendee KHÔNG lọc theo staff_id/staff_code/id_card
        // (mọi tham số lọc bị bỏ qua, luôn trả về toàn bộ). Vì vậy phải tải TOÀN BỘ danh sách
        // rồi tự lọc theo danh tính ở phía PHP. May mắn là danh sách có sẵn staff_id/staff_code/
        // id_card/photo_path cho từng bản ghi nên lọc được ngay, chỉ cần nạp chi tiết cho vài
        // ứng viên triển vọng (để lấy thêm cccd/contract).
        $all = $this->fetchAllAttendeesRaw();
        if (empty($all)) {
            return null;
        }

        // 1. Lọc ứng viên ĐÚNG danh tính. Loại người đang bị thay, và loại các bản ghi thuộc
        //    CHÍNH đăng ký đang thao tác — chức năng là tái sử dụng hồ sơ từ ĐĂNG KÝ KHÁC,
        //    đồng thời tránh vòng lặp lấy nhầm chính bản ghi vừa tạo trong đăng ký này.
        $candidates = array(); // id => hasPhoto(bool)
        foreach ($all as $a) {
            $aArr = is_array($a) ? $a : (array)$a;
            $aid = isset($aArr['id']) ? (string)$aArr['id'] : '';
            if ($aid === '' || $aid === (string)$excludeId) { continue; }
            if ($excludeRegistrationId !== null && isset($aArr['registration_id'])
                && (string)$aArr['registration_id'] === (string)$excludeRegistrationId) {
                continue;
            }
            if (!$this->attendeeIdentityMatches($aArr, $staffId, $staffCode, $idCard)) { continue; }
            $candidates[$aid] = !empty($aArr['photo_path']) || !empty($aArr['portrait_path']);
        }
        if (empty($candidates)) {
            return null;
        }

        // 2. Ưu tiên bản CÓ ẢNH, sau đó tới bản mới hơn (id lớn hơn).
        uksort($candidates, function ($x, $y) use ($candidates) {
            if ($candidates[$x] !== $candidates[$y]) { return $candidates[$x] ? -1 : 1; }
            return ((int)$y) - ((int)$x);
        });

        // 3. Nạp chi tiết vài ứng viên hàng đầu (để có cccd/contract), chọn bản nhiều hồ sơ nhất.
        $best = null;
        $bestScore = -1;
        $fetched = 0;
        $maxFetch = 6;
        foreach (array_keys($candidates) as $cid) {
            if ($fetched >= $maxFetch) { break; }
            $cand = Attendees::fetchFromApi($cid);
            $fetched++;
            if (!$cand) { continue; }
            if (!$this->attendeeIdentityMatches($cand, $staffId, $staffCode, $idCard)) { continue; }

            $score = 0;
            foreach (array('portrait_path', 'photo_path', 'cccd_front_path', 'cccd_back_path', 'contract_path') as $pf) {
                if (!empty($cand->$pf)) { $score++; }
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $cand;
            }
            if ($bestScore >= 4) { break; } // đủ ảnh chân dung + 2 CCCD + hợp đồng
        }

        return $best;
    }

    /**
     * Tải TOÀN BỘ danh sách attendee (raw array). Dùng khi cần tự lọc phía PHP vì API
     * không hỗ trợ lọc. Lấy per_page theo tổng số bản ghi (meta.total) trong 1 lần gọi.
     *
     * @return array
     */
    private function fetchAllAttendeesRaw()
    {
        $res = ApiClient::get(ApiEndpoints::ATTENDEE_LIST, array('per_page' => 5000));
        if (!$res['success'] || !isset($res['data'])) {
            return array();
        }
        $list = isset($res['data']['data']) ? $res['data']['data'] : $res['data'];
        return is_array($list) ? $list : array();
    }

    /**
     * Kiểm tra một bản ghi attendee (mảng từ danh sách HOẶC model chi tiết) có ĐÚNG danh tính
     * mục tiêu không. Khớp theo bất kỳ tiêu chí nào có: staff_code / staff_id / id_card.
     *
     * @param array|Attendees $att
     * @return bool
     */
    private function attendeeIdentityMatches($att, $staffId, $staffCode, $idCard)
    {
        $get = function ($key) use ($att) {
            if (is_array($att)) {
                return isset($att[$key]) ? $att[$key] : null;
            }
            return isset($att->$key) ? $att->$key : null;
        };

        if ($staffCode !== '') {
            $c = $get('staff_code');
            if ($c !== null && $c !== '' && strtolower(trim((string)$c)) === strtolower(trim((string)$staffCode))) {
                return true;
            }
        }
        if ($staffId) {
            $s = $get('staff_id');
            if ($s !== null && $s !== '' && (string)$s === (string)$staffId) {
                return true;
            }
        }
        if ($idCard !== '') {
            $ic = $get('id_card');
            if ($ic !== null && $ic !== '' && trim((string)$ic) === trim((string)$idCard)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Upload ảnh/hồ sơ cho người thay. Trả về map path.
     */
    private function handleReplaceUpload()
    {
        $result = array();
        $uploadDir = Yii::getPathOfAlias('webroot') . '/uploads/attendees/';
        if (!is_dir($uploadDir)) {
            if (!@mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
                Yii::log("Không thể tạo thư mục upload: {$uploadDir}", 'error', 'application.controllers.ApproveRegistrationsController');
                echo CJSON::encode(array('success' => false, 'error' => 'Không thể tạo thư mục lưu ảnh trên máy chủ. Vui lòng liên hệ quản trị viên.'));
                Yii::app()->end();
            }
        }
        $fileFields = array(
            'portrait_file' => 'portrait_path',
            'cccd_front_file' => 'cccd_front_path',
            'cccd_back_file' => 'cccd_back_path',
            'contract_file' => 'contract_path',
        );
        $allowedTypes = array('jpg', 'jpeg', 'png', 'gif', 'pdf');
        $maxSize = 50 * 1024 * 1024;

        foreach ($fileFields as $fieldName => $attrName) {
            if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
                continue;
            }
            $ext = strtolower(pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedTypes) || $_FILES[$fieldName]['size'] > $maxSize) {
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

    protected function loadModelById($id)
    {
        $model = Registrations::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy phiếu đăng ký.');
        }
        return $model;
    }

    private function cleanStorageUrl($url)
    {
        if (empty($url)) {
            return $url;
        }
        $prefix = 'https://portal-registration.muongthanh.vn/storage/';
        if (strpos($url, $prefix) === 0) {
            $remaining = substr($url, strlen($prefix));
            if (preg_match('/^https?:\/\//i', $remaining)) {
                return $remaining;
            }
        }
        $prefixHttp = 'http://portal-registration.muongthanh.vn/storage/';
        if (strpos($url, $prefixHttp) === 0) {
            $remaining = substr($url, strlen($prefixHttp));
            if (preg_match('/^https?:\/\//i', $remaining)) {
                return $remaining;
            }
        }
        return $url;
    }
}
