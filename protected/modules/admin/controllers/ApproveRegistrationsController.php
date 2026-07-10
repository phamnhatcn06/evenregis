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

        // Đếm số lượng
        $countSubmitted = $dpSubmitted->getTotalItemCount();
        $countRejected = $dpRejected->getTotalItemCount();
        $countApproved = $dpApproved->getTotalItemCount();

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
            'countSubmitted' => $countSubmitted,
            'countRejected' => $countRejected,
            'countApproved' => $countApproved,
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
        if ($model->property_id) {
            $property = Properties::fetchFromApi($model->property_id);
            if ($property) {
                if (empty($model->property_name)) {
                    $model->property_name = $property->name;
                }
                $model->property_code = $property->prefix ? $property->prefix : $property->code;
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

            echo CJSON::encode(array(
                'success' => true,
                'message' => "Đã phê duyệt phiếu đăng ký và {$successCount} người tham dự.",
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
