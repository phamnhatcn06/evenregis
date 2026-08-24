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

        // Load danh sách người tham dự của cùng đơn vị (cho form thay thế) — bao gồm cả người
        // ở CHÍNH đăng ký này và ở các đăng ký KHÁC. Cho phép chọn nhanh một attendee đã có để
        // tái sử dụng hồ sơ (ảnh, CCCD, HĐLĐ). Nếu người được chọn đã ở đăng ký hiện tại thì
        // backend sẽ dùng lại bản ghi đó (chỉ gán thêm nội dung), không tạo attendee mới.
        $otherAttendees = array();
        if ($model->property_id) {
            $seenIdentity = array();
            foreach ($this->fetchAllAttendeesRaw() as $a) {
                $aArr = is_array($a) ? $a : (array)$a;
                $aid = isset($aArr['id']) ? (string)$aArr['id'] : '';
                if ($aid === '') {
                    continue;
                }
                // Chỉ lấy người của đúng đơn vị này và còn hoạt động.
                $aPropId = isset($aArr['property_id']) ? (string)$aArr['property_id'] : '';
                if ($aPropId !== (string)$model->property_id) {
                    continue;
                }
                $isActive = isset($aArr['is_active']) ? (int)$aArr['is_active'] : 1;
                if ($isActive === 0) {
                    continue;
                }
                $inCurrentReg = isset($aArr['registration_id']) && (string)$aArr['registration_id'] === (string)$id;
                $aName = isset($aArr['full_name']) ? $aArr['full_name'] : '';
                $aPos = isset($aArr['position_name']) && $aArr['position_name'] !== ''
                    ? $aArr['position_name']
                    : (isset($aArr['position']) ? $aArr['position'] : '');
                $aIdCard = isset($aArr['id_card']) ? $aArr['id_card'] : '';
                // Khử trùng lặp cùng một người xuất hiện ở nhiều đăng ký. Ưu tiên giữ bản thuộc
                // đăng ký hiện tại (để chọn sẽ dùng lại thay vì tạo mới).
                $identityKey = strtolower(trim($aName)) . '|' . trim((string)$aIdCard);
                if (isset($seenIdentity[$identityKey])) {
                    if ($inCurrentReg && isset($otherAttendees[$seenIdentity[$identityKey]])) {
                        $otherAttendees[$seenIdentity[$identityKey]]['id'] = $aid;
                        $otherAttendees[$seenIdentity[$identityKey]]['in_current'] = 1;
                    }
                    continue;
                }
                $seenIdentity[$identityKey] = count($otherAttendees);
                $otherAttendees[] = array(
                    'id' => $aid,
                    'name' => $aName,
                    'position' => $aPos,
                    'id_card' => $aIdCard,
                    'in_current' => $inCurrentReg ? 1 : 0,
                );
            }
            $otherAttendees = array_values($otherAttendees);
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
            'otherAttendees' => $otherAttendees,
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

        // 2b. Gỡ khỏi các tiết mục văn nghệ (chỉ gỡ người này, giữ tiết mục cho các thành viên còn lại)
        foreach ($summary['talent_entries'] as $te) {
            if (!empty($te['member_id'])) {
                TalentEntryMembers::deleteViaApi($te['member_id']);
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
            $affectedTalents = array();
            foreach ($summary['talent_entries'] as $te) {
                $affectedTalents[] = array(
                    'entry_id' => $te['entry_id'],
                    'entry_title' => $te['entry_title'],
                    'category_name' => $te['category_name'],
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
                    'talents' => $affectedTalents,
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
     * Huỷ 1 hoặc nhiều nội dung THỂ THAO của người tham dự do VĐV đổi ý (không huỷ cả tư cách).
     * VĐV vẫn giữ các nội dung còn lại. Mặc định chỉ gỡ VĐV khỏi đội; tích "Huỷ cả đội"
     * mới xoá toàn bộ đội. Nếu sau khi huỷ VĐV không còn bất kỳ nội dung nào (thể thao,
     * thi NV, Miss, vai trò) → tự động chuyển thành huỷ tư cách (gỡ thẻ/QR).
     * Bản ghi huỷ được ghi audit và xuất hiện trong email xác nhận.
     *
     * Payload:
     *   attendee_id
     *   team_ids[]                    các sport_team_id cần huỷ (phải là đội của VĐV này)
     *   cancel_whole_team[team_id]=1  huỷ cả đội thay vì chỉ gỡ VĐV
     *   new_captain[team_id]=member_id đội trưởng mới nếu VĐV bị gỡ là đội trưởng
     *   reason                        lý do (bắt buộc)
     */
    public function actionCancelContent()
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
        $teamIds = Yii::app()->request->getPost('team_ids', array());
        if (!is_array($teamIds)) {
            $teamIds = array();
        }
        $wholeTeamFlags = Yii::app()->request->getPost('cancel_whole_team', array());
        if (!is_array($wholeTeamFlags)) {
            $wholeTeamFlags = array();
        }
        $newCaptains = Yii::app()->request->getPost('new_captain', array());
        if (!is_array($newCaptains)) {
            $newCaptains = array();
        }

        if (!$attendeeId) {
            echo CJSON::encode(array('success' => false, 'error' => 'Thiếu thông tin người tham dự.'));
            Yii::app()->end();
        }
        if (empty($teamIds)) {
            echo CJSON::encode(array('success' => false, 'error' => 'Vui lòng chọn ít nhất một đội cần huỷ.'));
            Yii::app()->end();
        }
        if ($reason === '') {
            echo CJSON::encode(array('success' => false, 'error' => 'Vui lòng nhập lý do huỷ nội dung.'));
            Yii::app()->end();
        }

        $attendee = Attendees::fetchFromApi($attendeeId);
        if (!$attendee) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy người tham dự.'));
            Yii::app()->end();
        }

        $ssoUser = AuthHandler::getUser();
        $email = isset($ssoUser['email']) ? $ssoUser['email'] : null;

        // Snapshot đội của VĐV trước khi gỡ (để đối chiếu + ghi audit)
        $summary = Attendees::getParticipationSummary($attendeeId);
        $teamsById = array();
        foreach ($summary['sport_teams'] as $t) {
            $teamsById[(string)$t['sport_team_id']] = $t;
        }

        $cancelledEntries = array();
        foreach ($teamIds as $tid) {
            $tidKey = (string)$tid;
            // Chỉ xử lý đội mà VĐV đang là thành viên (tránh gỡ nhầm đội không liên quan)
            if (!isset($teamsById[$tidKey])) {
                continue;
            }
            $t = $teamsById[$tidKey];
            $isWhole = !empty($wholeTeamFlags[$tid]) || (isset($wholeTeamFlags[$tidKey]) && $wholeTeamFlags[$tidKey]);

            if ($isWhole) {
                // Huỷ cả đội: gỡ toàn bộ thành viên rồi xoá đội
                foreach (SportTeamMembers::getTeamMemberBriefs($tid) as $tm) {
                    if (!empty($tm['member_id'])) {
                        SportTeamMembers::deleteViaApi($tm['member_id']);
                    }
                }
                SportTeams::deleteViaApi($tid);
            } else {
                // Chỉ gỡ VĐV khỏi đội
                if (!empty($t['member_id'])) {
                    SportTeamMembers::deleteViaApi($t['member_id']);
                }
                // Nếu VĐV bị gỡ là đội trưởng và có chỉ định đội trưởng mới
                if (!empty($t['is_captain']) && !empty($newCaptains[$tid])) {
                    SportTeamMembers::assignCaptain($newCaptains[$tid]);
                }
            }

            $cancelledEntries[] = array(
                'team_id' => $t['sport_team_id'],
                'sport_name' => $t['sport_name'],
                'team_name' => $t['team_name'],
                'jersey_number' => $t['jersey_number'],
                'is_captain' => $t['is_captain'],
                'whole_team' => $isWhole ? 1 : 0,
            );
        }

        if (empty($cancelledEntries)) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không có đội hợp lệ để huỷ.'));
            Yii::app()->end();
        }

        // Sau khi gỡ: nếu VĐV không còn nội dung nào → tự động huỷ tư cách
        $remaining = Attendees::getParticipationSummary($attendeeId);
        $hasRemaining = !empty($remaining['sport_teams']) || !empty($remaining['competitions'])
            || !empty($remaining['beauty_contests']) || !empty($remaining['talent_entries'])
            || !empty($remaining['roles']);

        $autoWithdrawn = false;
        if (!$hasRemaining) {
            $wres = Attendees::withdrawViaApi($attendeeId, $reason, $email);
            if (!empty($wres['success'])) {
                Badges::revokeByAttendee($attendeeId);
                $autoWithdrawn = true;
            }
        }

        // External API chỉ chấp nhận action = replace|withdraw (VARCHAR enum, không đổi được).
        // Với huỷ nội dung lẻ (không auto huỷ tư cách) → gửi action='withdraw' cho backend,
        // nhúng loại thật vào affected_contents._kind (giống pattern _batch_id đã dùng) để
        // khôi phục nhãn "Huỷ nội dung" khi hiển thị/gửi email.
        $affectedContents = array(
            'sports' => array(),
            'competitions' => array(),
            'beauty_contests' => array(),
            'roles' => array(),
        );
        if (!$autoWithdrawn) {
            $affectedContents['_kind'] = AttendeeReplacements::ACTION_CANCEL_CONTENT;
        }

        AttendeeReplacements::record(array(
            'registration_id' => $attendee->registration_id,
            'event_id' => $attendee->event_id,
            'property_id' => $attendee->property_id,
            'action' => AttendeeReplacements::ACTION_WITHDRAW,
            'old_attendee_id' => $attendeeId,
            'old_attendee_name' => $attendee->full_name,
            'old_staff_code' => isset($attendee->staff_code) ? $attendee->staff_code : null,
            'affected_contents' => $affectedContents,
            'cancelled_teams' => $cancelledEntries,
            'reason' => $reason,
            'performed_by' => $email,
        ));

        Yii::log(
            "Huỷ nội dung thể thao của attendee #{$attendeeId} bởi {$email}. Lý do: {$reason}"
                . ($autoWithdrawn ? ' (tự động huỷ tư cách do hết nội dung)' : ''),
            'info',
            'application.controllers.ApproveRegistrationsController'
        );

        $msg = $autoWithdrawn
            ? 'Đã huỷ nội dung. Người này không còn nội dung nào nên đã tự động huỷ tư cách.'
            : 'Đã huỷ nội dung đã chọn.';
        echo CJSON::encode(array('success' => true, 'message' => $msg, 'auto_withdrawn' => $autoWithdrawn));
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

        // 1. Ưu tiên: người đã là attendee ĐANG hoạt động trong CHÍNH đăng ký này → sẽ được dùng lại.
        $inRegistration = false;
        $detail = null;
        $inReg = $this->findActiveAttendeeInRegistration($registrationId, $staffId, $staffCode, $idCard, null);
        if ($inReg && isset($inReg['id'])) {
            $detail = Attendees::fetchFromApi($inReg['id']);
            if ($detail) { $inRegistration = true; }
        }
        // 2. Nếu chưa có trong đăng ký này → tìm hồ sơ ở đăng ký KHÁC để tái sử dụng ảnh.
        if (!$detail) {
            $detail = $this->resolveExistingProfile(null, $staffId, $staffCode, $idCard, null, $registrationId);
        }

        if ($detail) {
            $portrait = (!empty($detail->portrait_path)) ? $detail->portrait_path : (!empty($detail->photo_path) ? $detail->photo_path : '');

            echo CJSON::encode(array(
                'success' => true,
                'has_attendee' => true,
                'in_registration' => $inRegistration,
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
     * Lấy hồ sơ chi tiết của một attendee theo id (ảnh chân dung, CCCD, HĐLĐ, vai trò)
     * để dùng lại khi chọn "người thay từ đăng ký khác" trong form thay thế.
     */
    public function actionAttendeeProfile()
    {
        header('Content-Type: application/json');

        $attendeeId = Yii::app()->request->getParam('attendee_id');
        if (!$attendeeId) {
            echo CJSON::encode(array('success' => false, 'error' => 'Thiếu mã người tham dự.'));
            Yii::app()->end();
        }

        $detail = Attendees::fetchFromApi($attendeeId);
        if (!$detail) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy người tham dự.'));
            Yii::app()->end();
        }

        $portrait = (!empty($detail->portrait_path)) ? $detail->portrait_path : (!empty($detail->photo_path) ? $detail->photo_path : '');
        echo CJSON::encode(array(
            'success' => true,
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
        Yii::app()->end();
    }

    /**
     * Thay thế người tham dự (đa nội dung).
     * Cho phép nhiều người thay trong 1 thao tác; mỗi nội dung (đội thể thao / cuộc thi)
     * được gán cho một người thay cụ thể hoặc đánh dấu huỷ. Thi Miss luôn huỷ.
     * Vai trò nhập riêng cho từng người thay. Huỷ đội mặc định chỉ gỡ người bị thay;
     * tích "Huỷ cả đội" mới xoá toàn đội.
     *
     * Payload:
     *   sub[j][staff_id|full_name|position|id_card|role_id|existing_attendee_id|existing_*_url]
     *   sub_{field}_file_{j}                    ảnh/hồ sơ từng người thay
     *   assign_team[team_id] = s{j} | cancel    (+ cancel_whole_team[team_id]=1)
     *   assign_comp[comp_id] = s{j} | cancel
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
        $registrationId = $req->getPost('registration_id');
        $eventId = $req->getPost('event_id');
        $propertyId = $req->getPost('property_id');

        $subs = $req->getPost('sub', array());
        $assignTeam = $req->getPost('assign_team', array());
        $assignComp = $req->getPost('assign_comp', array());
        $assignTalent = $req->getPost('assign_talent', array());
        $cancelWholeTeam = $req->getPost('cancel_whole_team', array());
        if (!is_array($subs)) { $subs = array(); }
        if (!is_array($assignTeam)) { $assignTeam = array(); }
        if (!is_array($assignComp)) { $assignComp = array(); }
        if (!is_array($assignTalent)) { $assignTalent = array(); }
        if (!is_array($cancelWholeTeam)) { $cancelWholeTeam = array(); }

        if (!$oldId || $reason === '') {
            echo CJSON::encode(array('success' => false, 'error' => 'Thiếu thông tin bắt buộc (người bị thay, lý do).'));
            Yii::app()->end();
        }

        $oldAttendee = Attendees::fetchFromApi($oldId);
        if (!$oldAttendee) {
            echo CJSON::encode(array('success' => false, 'error' => 'Không tìm thấy người bị thay.'));
            Yii::app()->end();
        }

        $ssoUser = AuthHandler::getUser();
        $email = isset($ssoUser['email']) ? $ssoUser['email'] : null;

        $summary = Attendees::getParticipationSummary($oldId);

        // Xác định các người thay được gán ít nhất 1 nội dung (chỉ tạo những người này).
        $referenced = array();
        foreach (array_merge(array_values($assignTeam), array_values($assignComp), array_values($assignTalent)) as $v) {
            if (preg_match('/^s(\d+)$/', (string)$v, $mm)) { $referenced[$mm[1]] = true; }
        }
        if (empty($referenced)) {
            echo CJSON::encode(array('success' => false, 'error' => 'Chưa gán nội dung nào cho người thay.'));
            Yii::app()->end();
        }

        // 0. Kiểm tra quy định: mỗi VĐV không được tham gia quá 3 môn thể thao.
        // Tính theo môn (sport_id) sau thao tác = môn đã có sẵn (nếu dùng lại người có sẵn) + môn được gán.
        // Chạy TRƯỚC khi tạo/sửa để không áp dụng dở dang.
        $maxSports = 3;

        // Chuẩn hoá về MÔN gốc: các nội dung con (vd: Cầu lông đôi nữ, Cầu lông
        // đôi nam-nữ) đều thuộc cùng một môn (Cầu lông) nên phải quy về sport gốc
        // trước khi đếm, tránh đếm nội dung con thành nhiều môn.
        $rootSportCache = array();
        $resolveRootSport = function ($sportId) use (&$rootSportCache) {
            if (empty($sportId)) {
                return null;
            }
            if (isset($rootSportCache[$sportId])) {
                return $rootSportCache[$sportId];
            }
            $cur = $sportId;
            $rootId = $sportId;
            $seen = array();
            while ($cur && !isset($seen[$cur])) {
                $seen[$cur] = true;
                $sp = Sports::fetchFromApi($cur);
                if ($sp && !empty($sp->parent_id)) {
                    $rootId = $sp->parent_id;
                    $cur = $sp->parent_id;
                } else {
                    $rootId = $cur;
                    break;
                }
            }
            $rootSportCache[$sportId] = $rootId;
            return $rootId;
        };

        $teamSportKey = array(); // team_id => sport_key (theo môn gốc)
        foreach ($summary['sport_teams'] as $t) {
            $tk = (string)$t['sport_team_id'];
            $rootId = !empty($t['sport_id']) ? $resolveRootSport($t['sport_id']) : null;
            $teamSportKey[$tk] = $rootId ? ('s' . $rootId) : ('t' . $tk);
        }
        $assignedSports = array(); // j => set(sport_key) được gán
        foreach ($assignTeam as $tKey => $vv) {
            if (preg_match('/^s(\d+)$/', (string)$vv, $am) && isset($teamSportKey[(string)$tKey])) {
                $aj = $am[1];
                if (!isset($assignedSports[$aj])) { $assignedSports[$aj] = array(); }
                $assignedSports[$aj][$teamSportKey[(string)$tKey]] = true;
            }
        }
        foreach (array_keys($referenced) as $vj) {
            $vs = isset($subs[$vj]) && is_array($subs[$vj]) ? $subs[$vj] : array();
            $vStaffId = isset($vs['staff_id']) ? trim($vs['staff_id']) : '';
            $vIdCard = isset($vs['id_card']) ? trim($vs['id_card']) : '';
            $vName = isset($vs['full_name']) ? trim($vs['full_name']) : '';
            $vStaffCode = '';
            if ($vStaffId) {
                $vStaff = Staffs::fetchFromApi($vStaffId);
                if ($vStaff) {
                    if ($vName === '') { $vName = $vStaff->full_name; }
                    $vStaffCode = isset($vStaff->staff_code) ? $vStaff->staff_code : '';
                }
            }
            $sportKeys = isset($assignedSports[$vj]) ? $assignedSports[$vj] : array();
            $vExisting = $this->findActiveAttendeeInRegistration($registrationId, $vStaffId, $vStaffCode, $vIdCard, $oldId);
            if ($vExisting) {
                if ($vName === '' && !empty($vExisting['full_name'])) { $vName = $vExisting['full_name']; }
                $vExSum = Attendees::getParticipationSummary($vExisting['id']);
                foreach ($vExSum['sport_teams'] as $et) {
                    $etk = (string)$et['sport_team_id'];
                    $etRoot = !empty($et['sport_id']) ? $resolveRootSport($et['sport_id']) : null;
                    $sportKeys[$etRoot ? ('s' . $etRoot) : ('t' . $etk)] = true;
                }
            }
            if (count($sportKeys) > $maxSports) {
                $who = $vName !== '' ? ('"' . $vName . '"') : ('người thay #' . ((int)$vj + 1));
                echo CJSON::encode(array('success' => false, 'error' => 'Người thay ' . $who . ' sẽ tham gia ' . count($sportKeys) . ' môn thể thao, vượt quá quy định tối đa ' . $maxSports . ' môn.'));
                Yii::app()->end();
            }
        }

        // 1. Tạo từng người thay được tham chiếu.
        $subMap = array();        // j => newAttendeeId
        $subInfo = array();       // j => array(name, staff_code)
        $subReuseSkip = array();  // j => array('teams'=>set, 'comps'=>set) — nội dung người thay ĐÃ có sẵn
        foreach (array_keys($referenced) as $j) {
            $s = isset($subs[$j]) && is_array($subs[$j]) ? $subs[$j] : array();
            $staffId = isset($s['staff_id']) ? trim($s['staff_id']) : '';
            $fullName = isset($s['full_name']) ? trim($s['full_name']) : '';
            $position = isset($s['position']) ? trim($s['position']) : '';
            $idCard = isset($s['id_card']) ? trim($s['id_card']) : '';
            $staffCode = null;
            if ($staffId) {
                $staff = Staffs::fetchFromApi($staffId);
                if ($staff) {
                    if ($fullName === '') { $fullName = $staff->full_name; }
                    if ($position === '') { $position = isset($staff->position_name) ? $staff->position_name : ''; }
                    $staffCode = isset($staff->staff_code) ? $staff->staff_code : null;
                }
            }
            if (!$staffId && $fullName === '') {
                echo CJSON::encode(array('success' => false, 'error' => 'Một người thay được gán nội dung nhưng chưa có thông tin (SMILE hoặc họ tên).'));
                Yii::app()->end();
            }

            // Nếu người thay đã là attendee ĐANG hoạt động trong chính đăng ký này,
            // dùng lại bản ghi đó (chỉ gán thêm nội dung) thay vì tạo bản ghi trùng người.
            // Ưu tiên existing_attendee_id do frontend gửi (khi chọn trực tiếp từ danh sách),
            // sau đó mới khớp theo danh tính (staff/CCCD).
            $existingInReg = null;
            $postedExistingId = isset($s['existing_attendee_id']) ? trim($s['existing_attendee_id']) : '';
            if ($postedExistingId !== '' && (string)$postedExistingId !== (string)$oldId) {
                $cand = Attendees::fetchFromApi($postedExistingId);
                if ($cand
                    && (string)$cand->registration_id === (string)$registrationId
                    && (int)(isset($cand->is_active) ? $cand->is_active : 1) !== 0) {
                    $existingInReg = array(
                        'id' => $cand->id,
                        'full_name' => isset($cand->full_name) ? $cand->full_name : '',
                        'staff_code' => isset($cand->staff_code) ? $cand->staff_code : null,
                    );
                }
            }
            if (!$existingInReg) {
                $existingInReg = $this->findActiveAttendeeInRegistration($registrationId, $staffId, $staffCode, $idCard, $oldId);
            }
            if ($existingInReg) {
                $subMap[$j] = $existingInReg['id'];
                $subInfo[$j] = array(
                    'name' => !empty($existingInReg['full_name']) ? $existingInReg['full_name'] : $fullName,
                    'staff_code' => !empty($existingInReg['staff_code']) ? $existingInReg['staff_code'] : $staffCode,
                );
                // Ghi lại nội dung người này ĐÃ có để không thêm trùng khi gán.
                $reuseSum = Attendees::getParticipationSummary($existingInReg['id']);
                $teamSet = array();
                foreach ($reuseSum['sport_teams'] as $rt) { $teamSet[(string)$rt['sport_team_id']] = true; }
                $compSet = array();
                foreach ($reuseSum['competitions'] as $rc) { $compSet[(string)$rc['competition_id']] = true; }
                $subReuseSkip[$j] = array('teams' => $teamSet, 'comps' => $compSet);
                continue;
            }

            $new = new Attendees();
            $new->event_id = $eventId;
            $new->registration_id = $registrationId;
            $new->property_id = $propertyId;
            $new->full_name = $fullName;
            $new->position = $position;
            $new->position_name = $position;
            $new->id_card = $idCard;
            if ($staffId) { $new->staff_id = $staffId; }
            if ($staffCode) { $new->staff_code = $staffCode; }
            $roleStr = isset($s['role_id']) ? trim($s['role_id']) : '';
            $new->role_id = $roleStr;
            $new->approval_status = Attendees::APPROVAL_APPROVED;
            $new->approved_by = $email;

            // Ảnh/hồ sơ: file upload mới → URL hồ sơ cũ (frontend gửi) → copy từ bản ghi cũ.
            $existingAttendeeId = isset($s['existing_attendee_id']) ? $s['existing_attendee_id'] : null;
            $existingAttendee = $this->resolveExistingProfile($existingAttendeeId, $staffId, $staffCode, $idCard, $oldId, $registrationId);
            $uploads = $this->handleReplaceUpload($j);
            $postedFileUrls = array(
                'portrait_path'   => isset($s['existing_portrait_url']) ? trim($s['existing_portrait_url']) : '',
                'cccd_front_path' => isset($s['existing_cccd_front_url']) ? trim($s['existing_cccd_front_url']) : '',
                'cccd_back_path'  => isset($s['existing_cccd_back_url']) ? trim($s['existing_cccd_back_url']) : '',
                'contract_path'   => isset($s['existing_contract_url']) ? trim($s['existing_contract_url']) : '',
            );
            $fileMap = array(
                'portrait_path' => array('portrait_path', 'photo_path'),
                'cccd_front_path' => array('cccd_front_path'),
                'cccd_back_path' => array('cccd_back_path'),
                'contract_path' => array('contract_path'),
            );
            foreach ($fileMap as $targetAttr => $sourceAttrs) {
                if (isset($uploads[$targetAttr]) && !empty($uploads[$targetAttr])) {
                    $new->$targetAttr = $uploads[$targetAttr];
                } elseif (!empty($postedFileUrls[$targetAttr])) {
                    $new->$targetAttr = $postedFileUrls[$targetAttr];
                } elseif ($existingAttendee) {
                    foreach ($sourceAttrs as $sAttr) {
                        if (isset($existingAttendee->$sAttr) && !empty($existingAttendee->$sAttr)) {
                            $new->$targetAttr = $existingAttendee->$sAttr;
                            break;
                        }
                    }
                }
            }

            $storeResult = $new->storeViaApi();
            $newId = $this->extractNewId($storeResult);
            if (!$newId) {
                $err = isset($storeResult['error']) ? $storeResult['error'] : 'Không thể tạo người thay.';
                echo CJSON::encode(array('success' => false, 'error' => $err));
                Yii::app()->end();
            }
            $subMap[$j] = $newId;
            $subInfo[$j] = array('name' => $fullName, 'staff_code' => $staffCode);
        }

        // Chuẩn bị audit theo từng người thay.
        $auditPerSub = array();
        foreach (array_keys($subMap) as $j) {
            $auditPerSub[$j] = array('sports' => array(), 'competitions' => array());
        }

        // 2. Đội thể thao: gán → thêm người thay + gỡ người cũ; huỷ → gỡ người cũ (hoặc xoá cả đội).
        $cancelledTeams = array();
        foreach ($summary['sport_teams'] as $t) {
            $tid = (string)$t['sport_team_id'];
            $v = isset($assignTeam[$tid]) ? $assignTeam[$tid] : 'cancel';
            if (preg_match('/^s(\d+)$/', (string)$v, $mm) && isset($subMap[$mm[1]])) {
                $j = $mm[1];
                // Người thay tái sử dụng đã có sẵn trong đội này → không thêm trùng, chỉ gỡ người cũ.
                $alreadyMember = isset($subReuseSkip[$j]['teams'][$tid]);
                if (!$alreadyMember) {
                    $mem = new SportTeamMembers();
                    $mem->sport_team_id = $t['sport_team_id'];
                    $mem->attendee_id = $subMap[$j];
                    $mem->name = $subInfo[$j]['name'];
                    $mem->jersey_number = $t['jersey_number'];
                    $mem->position = $t['position'];
                    $mem->is_captain = $t['is_captain'];
                    $mem->storeViaApi();
                }
                if (!empty($t['member_id'])) {
                    SportTeamMembers::deleteViaApi($t['member_id']);
                }
                $auditPerSub[$j]['sports'][] = array(
                    'team_id' => $t['sport_team_id'],
                    'sport_name' => $t['sport_name'],
                    'team_name' => $t['team_name'],
                    'jersey_number' => $t['jersey_number'],
                    'is_captain' => $t['is_captain'],
                );
            } else {
                // Huỷ: mặc định chỉ gỡ người bị thay; tích "Huỷ cả đội" mới xoá toàn đội.
                $whole = !empty($cancelWholeTeam[$tid]);
                if ($whole) {
                    foreach (SportTeamMembers::getTeamMemberBriefs($t['sport_team_id']) as $tm) {
                        if (!empty($tm['member_id'])) {
                            SportTeamMembers::deleteViaApi($tm['member_id']);
                        }
                    }
                    SportTeams::deleteViaApi($t['sport_team_id']);
                } elseif (!empty($t['member_id'])) {
                    SportTeamMembers::deleteViaApi($t['member_id']);
                }
                $cancelledTeams[] = array(
                    'team_id' => $t['sport_team_id'],
                    'sport_name' => $t['sport_name'],
                    'team_name' => $t['team_name'],
                    'jersey_number' => $t['jersey_number'],
                    'is_captain' => $t['is_captain'],
                    'whole' => $whole ? 1 : 0,
                );
            }
        }

        // 3. Thi nghiệp vụ: gán theo từng cuộc thi (số báo danh mới do backend cấp).
        $cancelledComps = array();
        foreach ($summary['competitions'] as $c) {
            $cid = (string)$c['competition_id'];
            $v = isset($assignComp[$cid]) ? $assignComp[$cid] : 'cancel';
            if (preg_match('/^s(\d+)$/', (string)$v, $mm) && isset($subMap[$mm[1]])) {
                $j = $mm[1];
                // Người thay tái sử dụng đã đăng ký cuộc thi này → không thêm trùng, chỉ gỡ người cũ.
                if (!isset($subReuseSkip[$j]['comps'][$cid])) {
                    $cr = new CompetitionRegistrations();
                    $cr->competition_id = $c['competition_id'];
                    $cr->registration_id = $registrationId;
                    $cr->attendee_id = $subMap[$j];
                    $cr->status = CompetitionRegistrations::STATUS_PENDING;
                    $cr->storeViaApi();
                }
                if (!empty($c['registration_id'])) {
                    CompetitionRegistrations::deleteViaApi($c['registration_id']);
                }
                $auditPerSub[$j]['competitions'][] = array(
                    'competition_id' => $c['competition_id'],
                    'competition_name' => $c['competition_name'],
                    'candidate_number' => $c['candidate_number'],
                );
            } else {
                if (!empty($c['registration_id'])) {
                    CompetitionRegistrations::deleteViaApi($c['registration_id']);
                }
                $cancelledComps[] = array(
                    'competition_id' => $c['competition_id'],
                    'competition_name' => $c['competition_name'],
                    'candidate_number' => $c['candidate_number'],
                );
            }
        }

        // 4. Thi Miss: luôn huỷ đăng ký của người bị thay (không kế thừa).
        $cancelledBeauty = array();
        foreach ($summary['beauty_contests'] as $bc) {
            if (!empty($bc['contestant_id'])) {
                BeautyContestants::deleteViaApi($bc['contestant_id']);
            }
            $cancelledBeauty[] = array(
                'contest_id' => $bc['contest_id'],
                'contest_name' => $bc['contest_name'],
                'candidate_number' => $bc['candidate_number'],
            );
        }

        // 4b. Văn nghệ: gỡ người bị thay khỏi các tiết mục (giữ tiết mục cho thành viên còn lại).
        $cancelledTalents = array();
        foreach ($summary['talent_entries'] as $te) {
            if (!empty($te['member_id'])) {
                TalentEntryMembers::deleteViaApi($te['member_id']);
            }
            $cancelledTalents[] = array(
                'entry_id' => $te['entry_id'],
                'entry_title' => $te['entry_title'],
                'category_name' => $te['category_name'],
            );
        }

        // 5. Gỡ toàn bộ vai trò của người bị thay (mỗi người thay đã có vai trò riêng).
        foreach ($summary['roles'] as $r) {
            if (!empty($r['attendee_role_id'])) {
                AttendeeRoles::deleteViaApi($r['attendee_role_id']);
            }
        }

        // 6. Huỷ tư cách người bị thay + thu hồi thẻ/QR.
        Attendees::withdrawViaApi($oldId, 'Đã được thay thế. ' . $reason, $email);
        Badges::revokeByAttendee($oldId);

        // 7. Ghi lịch sử: mỗi người thay 1 bản ghi (cùng batch); phần huỷ ghi 1 bản ghi riêng.
        $batchId = 'RPL' . time() . substr(uniqid(), -5);
        foreach ($subMap as $j => $newId) {
            AttendeeReplacements::record(array(
                'registration_id' => $registrationId,
                'event_id' => $eventId,
                'property_id' => $propertyId,
                'action' => AttendeeReplacements::ACTION_REPLACE,
                'old_attendee_id' => $oldId,
                'old_attendee_name' => $oldAttendee->full_name,
                'old_staff_code' => isset($oldAttendee->staff_code) ? $oldAttendee->staff_code : null,
                'new_attendee_id' => $newId,
                'new_attendee_name' => $subInfo[$j]['name'],
                'new_staff_code' => $subInfo[$j]['staff_code'],
                'affected_contents' => array(
                    'sports' => $auditPerSub[$j]['sports'],
                    'competitions' => $auditPerSub[$j]['competitions'],
                    'roles' => array(),
                    '_batch_id' => $batchId,
                ),
                'cancelled_teams' => array(),
                'reason' => $reason,
                'performed_by' => $email,
            ));
        }
        if (!empty($cancelledTeams) || !empty($cancelledComps) || !empty($cancelledBeauty) || !empty($cancelledTalents)) {
            AttendeeReplacements::record(array(
                'registration_id' => $registrationId,
                'event_id' => $eventId,
                'property_id' => $propertyId,
                'action' => AttendeeReplacements::ACTION_WITHDRAW,
                'old_attendee_id' => $oldId,
                'old_attendee_name' => $oldAttendee->full_name,
                'old_staff_code' => isset($oldAttendee->staff_code) ? $oldAttendee->staff_code : null,
                'new_attendee_id' => null,
                'new_attendee_name' => null,
                'new_staff_code' => null,
                'affected_contents' => array(
                    'competitions' => $cancelledComps,
                    'beauty_contests' => $cancelledBeauty,
                    'talents' => $cancelledTalents,
                    '_batch_id' => $batchId,
                ),
                'cancelled_teams' => $cancelledTeams,
                'reason' => $reason,
                'performed_by' => $email,
            ));
        }

        Yii::log("Thay thế đa nội dung attendee #{$oldId} → [" . implode(',', array_values($subMap)) . "] bởi {$email}. Batch {$batchId}. Lý do: {$reason}", 'info', 'application.controllers.ApproveRegistrationsController');
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
     * Tìm attendee ĐANG hoạt động trong 1 đăng ký, khớp danh tính (staff_code / staff_id / id_card),
     * loại người đang bị thay. Dùng để tái sử dụng bản ghi có sẵn thay vì tạo trùng người thay.
     *
     * @param mixed  $registrationId
     * @param mixed  $staffId
     * @param string $staffCode
     * @param string $idCard
     * @param mixed  $excludeId  id người đang bị thay (không tự chọn lại)
     * @return array|null bản ghi attendee (mảng thô) hoặc null
     */
    private function findActiveAttendeeInRegistration($registrationId, $staffId, $staffCode, $idCard, $excludeId)
    {
        if (!$staffId && (string)$staffCode === '' && (string)$idCard === '') {
            return null;
        }
        foreach (Attendees::getByRegistrationId($registrationId) as $att) {
            $aArr = is_array($att) ? $att : (array)$att;
            $aid = isset($aArr['id']) ? (string)$aArr['id'] : '';
            if ($aid === '' || $aid === (string)$excludeId) { continue; }
            if ($this->attendeeIdentityMatches($aArr, $staffId, $staffCode, $idCard)) {
                return $aArr;
            }
        }
        return null;
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
    private function handleReplaceUpload($index = 0)
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
            'sub_portrait_file_' . $index => 'portrait_path',
            'sub_cccd_front_file_' . $index => 'cccd_front_path',
            'sub_cccd_back_file_' . $index => 'cccd_back_path',
            'sub_contract_file_' . $index => 'contract_path',
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
