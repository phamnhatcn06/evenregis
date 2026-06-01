<?php

class ReportsController extends AdminController
{
    public function actionAdmin()
    {
        $user = AuthHandler::getUser();
        if (!$user) {
            throw new CHttpException(403, 'Bạn cần đăng nhập để xem báo cáo.');
        }

        $userPropertyCode = isset($user['property_code']) ? $user['property_code'] : '';
        $isHO = ($userPropertyCode === '9999' || $userPropertyCode === 9999);
        $userPropertyId = isset($user['property_id']) ? $user['property_id'] : null;

        // 1. Fetch all events for dropdown filter
        $eventsList = array();
        $eventsRes = Events::getApiDataProvider(array(), 100)->getData();
        foreach ($eventsRes as $event) {
            $eId = isset($event->id) ? $event->id : (isset($event['id']) ? $event['id'] : null);
            $eName = isset($event->name) ? $event->name : (isset($event['name']) ? $event['name'] : '');
            if ($eId) {
                $eventsList[$eId] = $event;
            }
        }

        // Determine selected event
        $selectedEventId = Yii::app()->request->getParam('event_id');
        if (empty($selectedEventId) && !empty($eventsList)) {
            // Find active event first
            foreach ($eventsList as $event) {
                $status = isset($event->status) ? $event->status : (isset($event['status']) ? $event['status'] : 0);
                if ($status == 1) {
                    $selectedEventId = isset($event->id) ? $event->id : $event['id'];
                    break;
                }
            }
            // Fallback to first event
            if (empty($selectedEventId)) {
                $firstEvent = reset($eventsList);
                $selectedEventId = isset($firstEvent->id) ? $firstEvent->id : $firstEvent['id'];
            }
        }

        // Get selected event name
        $selectedEventName = '';
        if ($selectedEventId && isset($eventsList[$selectedEventId])) {
            $eventObj = $eventsList[$selectedEventId];
            $selectedEventName = isset($eventObj->name) ? $eventObj->name : (isset($eventObj['name']) ? $eventObj['name'] : '');
        }

        // 2. Fetch properties/units based on permission
        $properties = array();
        if ($isHO) {
            $properties = Properties::getApiDataProvider(array('is_active' => 1), 1000)->getData();
        } else if ($userPropertyId) {
            $prop = Properties::fetchFromApi($userPropertyId);
            if ($prop) {
                $properties = array($prop);
            }
        }

        // 3. Fetch registrations for Report 1: Danh sách các đơn vị đã gửi đăng ký
        $regParams = array(
            'event_id' => $selectedEventId,
            'per_page' => 1000,
        );
        if (!$isHO && $userPropertyId) {
            $regParams['property_id'] = $userPropertyId;
        }
        $registrationsRes = Registrations::getApiDataProvider($regParams, 1000)->getData();

        $submittedRegistrations = array();
        foreach ($registrationsRes as $reg) {
            $deletedAt = isset($reg->deleted_at) ? $reg->deleted_at : (isset($reg['deleted_at']) ? $reg['deleted_at'] : null);
            if ($deletedAt !== null && $deletedAt !== '') {
                continue;
            }
            $status = isset($reg->status) ? (int)$reg->status : 0;
            if ($status !== Registrations::STATUS_DRAFT) {
                $submittedRegistrations[] = $reg;
            }
        }

        // Sort submitted registrations by property_code naturally
        usort($submittedRegistrations, function ($a, $b) {
            $codeA = isset($a->property_code) ? $a->property_code : (isset($a['property_code']) ? $a['property_code'] : '');
            $codeB = isset($b->property_code) ? $b->property_code : (isset($b['property_code']) ? $b['property_code'] : '');
            return strnatcasecmp($codeA, $codeB);
        });

        // Map of submitted and active registration IDs
        $activeRegistrationIds = array();
        foreach ($submittedRegistrations as $reg) {
            $regId = isset($reg->id) ? $reg->id : (isset($reg['id']) ? $reg['id'] : null);
            if ($regId) {
                $activeRegistrationIds[$regId] = true;
            }
        }

        // 4. Fetch attendees for Report 2: Báo cáo số người tham dự theo đơn vị
        $attParams = array(
            'event_id' => $selectedEventId,
            'per_page' => 5000,
        );
        if (!$isHO && $userPropertyId) {
            $attParams['property_id'] = $userPropertyId;
        }
        $rawAttendees = Attendees::getApiDataProvider($attParams, 5000)->getData();
        $attendeesRes = array();
        foreach ($rawAttendees as $att) {
            $attDeletedAt = isset($att->deleted_at) ? $att->deleted_at : (isset($att['deleted_at']) ? $att['deleted_at'] : null);
            if ($attDeletedAt !== null && $attDeletedAt !== '') {
                continue;
            }
            $regId = isset($att->registration_id) ? $att->registration_id : (isset($att['registration_id']) ? $att['registration_id'] : null);
            if ($regId && isset($activeRegistrationIds[$regId])) {
                $attendeesRes[] = $att;
            }
        }

        // Compute attendee statistics grouped by property
        $attendeeStats = array();
        foreach ($properties as $prop) {
            $propId = isset($prop->id) ? $prop->id : (isset($prop['id']) ? $prop['id'] : null);
            if (!$propId) continue;

            $attendeeStats[$propId] = array(
                'property_code' => isset($prop->code) ? $prop->code : '',
                'property_name' => isset($prop->name) ? $prop->name : '',
                'total' => 0,
                'approved' => 0,
                'pending' => 0,
                'rejected' => 0,
                'roles' => array(),
            );
        }

        foreach ($attendeesRes as $att) {
            $propId = isset($att->property_id) ? $att->property_id : (isset($att['property_id']) ? $att['property_id'] : null);
            if (!$propId) continue;

            if (!isset($attendeeStats[$propId])) {
                if ($isHO) {
                    $attendeeStats[$propId] = array(
                        'property_code' => isset($att->property_code) ? $att->property_code : '',
                        'property_name' => isset($att->property_name) ? $att->property_name : (isset($att['property_name']) ? $att['property_name'] : 'Không xác định'),
                        'total' => 0,
                        'approved' => 0,
                        'pending' => 0,
                        'rejected' => 0,
                        'roles' => array(),
                    );
                } else {
                    continue;
                }
            }

            $attendeeStats[$propId]['total']++;
            $status = isset($att->approval_status) ? (int)$att->approval_status : 0;
            if ($status === Attendees::APPROVAL_APPROVED) {
                $attendeeStats[$propId]['approved']++;
            } elseif ($status === Attendees::APPROVAL_REJECTED) {
                $attendeeStats[$propId]['rejected']++;
            } else {
                $attendeeStats[$propId]['pending']++;
            }

            // Roles breakdown
            $roleName = isset($att->role_name) ? $att->role_name : '';
            if (empty($roleName) && !empty($att->role_id)) {
                $roleName = Attendees::resolveRoleNames($att->role_id);
            }
            if (empty($roleName)) {
                $roleName = 'Khác';
            }
            if (!isset($attendeeStats[$propId]['roles'][$roleName])) {
                $attendeeStats[$propId]['roles'][$roleName] = 0;
            }
            $attendeeStats[$propId]['roles'][$roleName]++;
        }

        // Sort attendee stats by property_code naturally
        usort($attendeeStats, function ($a, $b) {
            return strnatcasecmp($a['property_code'], $b['property_code']);
        });

        // 5. Fetch talent shows and entries for Report 3 & 4
        $shows = TalentShows::getApiDataProvider(array('event_id' => $selectedEventId), 100)->getData();
        $showIds = array();
        $showNames = array();
        foreach ($shows as $show) {
            $sId = isset($show->id) ? $show->id : (isset($show['id']) ? $show['id'] : null);
            $sName = isset($show->name) ? $show->name : (isset($show['name']) ? $show['name'] : '');
            if ($sId) {
                $showIds[] = $sId;
                $showNames[$sId] = $sName;
            }
        }

        $talentEntries = array();
        if (!empty($showIds)) {
            $entryParams = array(
                'event_id' => $selectedEventId,
                'per_page' => 1000,
            );
            if (!$isHO && $userPropertyId) {
                $entryParams['property_id'] = $userPropertyId;
            }
            $allEntries = TalentEntries::getApiDataProvider($entryParams, 1000)->getData();
            foreach ($allEntries as $entry) {
                $entryDeletedAt = isset($entry->deleted_at) ? $entry->deleted_at : (isset($entry['deleted_at']) ? $entry['deleted_at'] : null);
                if ($entryDeletedAt !== null && $entryDeletedAt !== '') {
                    continue;
                }
                $regId = isset($entry->registration_id) ? $entry->registration_id : (isset($entry['registration_id']) ? $entry['registration_id'] : null);
                if (!$regId || !isset($activeRegistrationIds[$regId])) {
                    continue;
                }

                $eShowId = isset($entry->show_id) ? $entry->show_id : (isset($entry['show_id']) ? $entry['show_id'] : null);
                if ($eShowId && in_array($eShowId, $showIds)) {
                    if (empty($entry->show_name) && isset($showNames[$eShowId])) {
                        $entry->show_name = $showNames[$eShowId];
                    }
                    $talentEntries[] = $entry;
                }
            }
        }

        // Compute Talent summary aggregates (Report 4)
        $categoriesList = TalentCategories::getApiDataProvider(array(), 200)->getData();
        $categoryNames = array();
        foreach ($categoriesList as $cat) {
            $cId = isset($cat->id) ? $cat->id : (isset($cat['id']) ? $cat['id'] : null);
            $cName = isset($cat->name) ? $cat->name : (isset($cat['name']) ? $cat['name'] : '');
            if ($cId) {
                $categoryNames[$cId] = $cName;
            }
        }

        $statsByShow = array();
        foreach ($shows as $show) {
            $sId = isset($show->id) ? $show->id : (isset($show['id']) ? $show['id'] : null);
            if (!$sId) continue;
            $statsByShow[$sId] = array(
                'name' => isset($show->name) ? $show->name : $show['name'],
                'count' => 0,
                'participants' => 0,
            );
        }

        $statsByCategory = array();
        $statsByProperty = array();

        foreach ($talentEntries as $entry) {
            $eShowId = isset($entry->show_id) ? $entry->show_id : (isset($entry['show_id']) ? $entry['show_id'] : null);
            $eCatId = isset($entry->category_id) ? $entry->category_id : (isset($entry['category_id']) ? $entry['category_id'] : null);
            $ePropId = isset($entry->property_id) ? $entry->property_id : (isset($entry['property_id']) ? $entry['property_id'] : null);
            $ePropName = isset($entry->property_name) ? $entry->property_name : (isset($entry['property_name']) ? $entry['property_name'] : 'Không xác định');
            $pCount = isset($entry->participant_count) ? (int)$entry->participant_count : 0;

            if ($eShowId && isset($statsByShow[$eShowId])) {
                $statsByShow[$eShowId]['count']++;
                $statsByShow[$eShowId]['participants'] += $pCount;
            }

            if ($eCatId) {
                if (!isset($statsByCategory[$eCatId])) {
                    $statsByCategory[$eCatId] = array(
                        'name' => isset($categoryNames[$eCatId]) ? $categoryNames[$eCatId] : (isset($entry->category_name) ? $entry->category_name : 'Khác'),
                        'count' => 0,
                        'participants' => 0,
                    );
                }
                $statsByCategory[$eCatId]['count']++;
                $statsByCategory[$eCatId]['participants'] += $pCount;
            }

            if ($ePropId) {
                if (!isset($statsByProperty[$ePropId])) {
                    $statsByProperty[$ePropId] = array(
                        'property_id' => $ePropId,
                        'name' => $ePropName,
                        'count' => 0,
                        'participants' => 0,
                    );
                }
                $statsByProperty[$ePropId]['count']++;
                $statsByProperty[$ePropId]['participants'] += $pCount;
            }
        }

        // Sort statsByProperty by unit name naturally
        usort($statsByProperty, function ($a, $b) {
            return strnatcasecmp($a['name'], $b['name']);
        });

        // Build map of property_id to registration
        $propertyRegistrationMap = array();
        foreach ($registrationsRes as $reg) {
            $deletedAt = isset($reg->deleted_at) ? $reg->deleted_at : (isset($reg['deleted_at']) ? $reg['deleted_at'] : null);
            if ($deletedAt !== null && $deletedAt !== '') {
                continue;
            }
            $status = isset($reg->status) ? (int)$reg->status : 0;
            if ($status === Registrations::STATUS_DRAFT) {
                continue;
            }
            $regPropId = isset($reg->property_id) ? $reg->property_id : (isset($reg['property_id']) ? $reg['property_id'] : null);
            $regId = isset($reg->id) ? $reg->id : (isset($reg['id']) ? $reg['id'] : null);
            if ($regPropId && $regId) {
                $propertyRegistrationMap[$regPropId] = $reg;
            }
        }

        // 6. Compute KPI Statistics
        $uniqueRegisteredOrgs = array();
        foreach ($submittedRegistrations as $reg) {
            $regPropId = isset($reg->property_id) ? $reg->property_id : (isset($reg['property_id']) ? $reg['property_id'] : null);
            if ($regPropId) {
                $uniqueRegisteredOrgs[$regPropId] = true;
            }
        }

        $totalAttendeesCount = count($attendeesRes);
        $totalTalentEntriesCount = count($talentEntries);

        // Fetch beauty contestants count and detailed list for selected event
        $totalBeautyContestantsCount = 0;
        $beautyContestantsList = array();
        if ($selectedEventId) {
            $allowedAttendeeIds = array();
            $attendeeMap = array();
            foreach ($attendeesRes as $att) {
                $attId = isset($att->id) ? $att->id : (isset($att['id']) ? $att['id'] : null);
                if ($attId) {
                    $allowedAttendeeIds[$attId] = true;
                    $attendeeMap[$attId] = $att;
                }
            }

            $uniqueBeautyContestants = array();
            $contests = BeautyContests::getApiDataProvider(array('event_id' => $selectedEventId), 100)->getData();
            foreach ($contests as $contest) {
                $contestId = isset($contest->id) ? $contest->id : (isset($contest['id']) ? $contest['id'] : null);
                $contestName = isset($contest->name) ? $contest->name : (isset($contest['name']) ? $contest['name'] : '');
                if ($contestId) {
                    $contestantParams = array('contest_id' => $contestId, 'per_page' => 1000);
                    $contestants = BeautyContestants::getApiDataProvider($contestantParams, 1000)->getData();
                    foreach ($contestants as $c) {
                        $cDeletedAt = isset($c->deleted_at) ? $c->deleted_at : (isset($c['deleted_at']) ? $c['deleted_at'] : null);
                        if ($cDeletedAt !== null && $cDeletedAt !== '') {
                            continue;
                        }
                        $cAttendeeId = isset($c->attendee_id) ? $c->attendee_id : (isset($c['attendee_id']) ? $c['attendee_id'] : null);
                        if ($cAttendeeId) {
                            if (!isset($allowedAttendeeIds[$cAttendeeId])) {
                                continue;
                            }
                            $uniqueBeautyContestants[$cAttendeeId] = true;

                            // Hydrate contestant with attendee and property details
                            if (isset($attendeeMap[$cAttendeeId])) {
                                $attObj = $attendeeMap[$cAttendeeId];
                                $pName = isset($attObj->property_name) ? $attObj->property_name : (isset($attObj['property_name']) ? $attObj['property_name'] : '');
                                $pCode = isset($attObj->property_code) ? $attObj->property_code : (isset($attObj['property_code']) ? $attObj['property_code'] : '');
                                $attFullName = isset($attObj->full_name) ? $attObj->full_name : (isset($attObj['full_name']) ? $attObj['full_name'] : '');
                                $attPortrait = isset($attObj->portrait_path) ? $attObj->portrait_path : (isset($attObj['photo_path']) ? $attObj['photo_path'] : (isset($attObj['portrait_path']) ? $attObj['portrait_path'] : ''));

                                if (is_object($c)) {
                                    $c->contest_name = $contestName;
                                    $c->attendee_name = $attFullName;
                                    $c->property_name = $pName;
                                    $c->property_code = $pCode;
                                    $c->photo_portrait = $attPortrait;
                                } else {
                                    $c['contest_name'] = $contestName;
                                    $c['attendee_name'] = $attFullName;
                                    $c['property_name'] = $pName;
                                    $c['property_code'] = $pCode;
                                    $c['photo_portrait'] = $attPortrait;
                                }
                                $beautyContestantsList[] = $c;
                            }
                        }
                    }
                }
            }
            $totalBeautyContestantsCount = count($uniqueBeautyContestants);

            // Sort beauty contestants list by property_code naturally
            usort($beautyContestantsList, function ($a, $b) {
                $codeA = is_object($a) ? (isset($a->property_code) ? $a->property_code : '') : (isset($a['property_code']) ? $a['property_code'] : '');
                $codeB = is_object($b) ? (isset($b->property_code) ? $b->property_code : '') : (isset($b['property_code']) ? $b['property_code'] : '');
                return strnatcasecmp($codeA, $codeB);
            });
        }

        // Fetch all sports to pre-cache sport names and initialize stats
        $sportsList = Sports::getApiDataProvider(array('is_active' => 1), 500)->getData();
        $sportNameMap = array();
        $statsBySport = array();
        foreach ($sportsList as $sp) {
            $spId = isset($sp->id) ? $sp->id : (isset($sp['id']) ? $sp['id'] : null);
            $spName = isset($sp->name) ? $sp->name : (isset($sp['name']) ? $sp['name'] : '');
            if ($spId) {
                $sportNameMap[$spId] = $spName;
                $statsBySport[$spId] = array(
                    'name' => $spName,
                    'team_count' => 0,
                );
            }
        }

        // Build property name and code map for pre-caching
        $propertyNameMap = array();
        $propertyCodeMap = array();
        foreach ($properties as $prop) {
            $propId = isset($prop->id) ? $prop->id : (isset($prop['id']) ? $prop['id'] : null);
            if ($propId) {
                $propertyNameMap[$propId] = isset($prop->name) ? $prop->name : '';
                $propertyCodeMap[$propId] = isset($prop->code) ? $prop->code : '';
            }
        }

        // Fetch Sport Teams
        $sportTeams = array();
        if ($selectedEventId) {
            $teamParams = array(
                'event_id' => $selectedEventId,
                'per_page' => 1000,
            );
            if (!$isHO && $userPropertyId) {
                $teamParams['property_id'] = $userPropertyId;
            }
            $rawTeams = SportTeams::getApiDataProvider($teamParams, 1000)->getData();
            foreach ($rawTeams as $team) {
                $teamDeletedAt = isset($team->deleted_at) ? $team->deleted_at : (isset($team['deleted_at']) ? $team['deleted_at'] : null);
                if ($teamDeletedAt !== null && $teamDeletedAt !== '') {
                    continue;
                }
                $regId = isset($team->registration_id) ? $team->registration_id : (isset($team['registration_id']) ? $team['registration_id'] : null);
                if (!$regId || !isset($activeRegistrationIds[$regId])) {
                    continue;
                }

                // Hydrate sport and property names/codes from pre-cached maps
                $spId = isset($team->sport_id) ? $team->sport_id : (isset($team['sport_id']) ? $team['sport_id'] : null);
                if ($spId && isset($sportNameMap[$spId])) {
                    if (is_object($team)) {
                        $team->sport_name = $sportNameMap[$spId];
                    } else {
                        $team['sport_name'] = $sportNameMap[$spId];
                    }
                    if (isset($statsBySport[$spId])) {
                        $statsBySport[$spId]['team_count']++;
                    }
                }

                $propId = isset($team->property_id) ? $team->property_id : (isset($team['property_id']) ? $team['property_id'] : null);
                if ($propId) {
                    if (is_object($team)) {
                        $team->property_name = isset($propertyNameMap[$propId]) ? $propertyNameMap[$propId] : '';
                        $team->property_code = isset($propertyCodeMap[$propId]) ? $propertyCodeMap[$propId] : '';
                    } else {
                        $team['property_name'] = isset($propertyNameMap[$propId]) ? $propertyNameMap[$propId] : '';
                        $team['property_code'] = isset($propertyCodeMap[$propId]) ? $propertyCodeMap[$propId] : '';
                    }
                }

                $sportTeams[] = $team;
            }

            // Filter out sports that have 0 registered teams for a cleaner report
            $activeStatsBySport = array();
            foreach ($statsBySport as $spId => $spStat) {
                if ($spStat['team_count'] > 0) {
                    $activeStatsBySport[$spId] = $spStat;
                }
            }
            $statsBySport = $activeStatsBySport;

            usort($statsBySport, function ($a, $b) {
                return strnatcasecmp($a['name'], $b['name']);
            });

            // Sort sportTeams list by sport_name, then property_code naturally
            usort($sportTeams, function ($a, $b) {
                $sportA = is_object($a) ? (isset($a->sport_name) ? $a->sport_name : '') : (isset($a['sport_name']) ? $a['sport_name'] : '');
                $sportB = is_object($b) ? (isset($b->sport_name) ? $b->sport_name : '') : (isset($b['sport_name']) ? $b['sport_name'] : '');
                $cmp = strnatcasecmp($sportA, $sportB);
                if ($cmp !== 0) return $cmp;

                $codeA = is_object($a) ? (isset($a->property_code) ? $a->property_code : '') : (isset($a['property_code']) ? $a['property_code'] : '');
                $codeB = is_object($b) ? (isset($b->property_code) ? $b->property_code : '') : (isset($b['property_code']) ? $b['property_code'] : '');
                return strnatcasecmp($codeA, $codeB);
            });
        }

        $this->title = 'Báo cáo tổng hợp Sự kiện';
        $this->breadcrumbs = array(
            'Báo cáo' => array('index'),
            'Tổng hợp'
        );

        $this->render('index', array(
            'isHO' => $isHO,
            'user' => $user,
            'eventsList' => $eventsList,
            'selectedEventId' => $selectedEventId,
            'selectedEventName' => $selectedEventName,
            'submittedRegistrations' => $submittedRegistrations,
            'attendeeStats' => $attendeeStats,
            'talentEntries' => $talentEntries,
            'statsByShow' => $statsByShow,
            'statsByCategory' => $statsByCategory,
            'statsByProperty' => $statsByProperty,
            'propertyRegistrationMap' => $propertyRegistrationMap,
            'beautyContestantsList' => $beautyContestantsList,
            'sportTeams' => $sportTeams,
            'statsBySport' => $statsBySport,
            'kpis' => array(
                'registered_units' => count($uniqueRegisteredOrgs),
                'total_units' => count($properties),
                'total_attendees' => $totalAttendeesCount,
                'total_talent_entries' => $totalTalentEntriesCount,
                'total_beauty_contestants' => $totalBeautyContestantsCount,
            )
        ));
    }

    public function actionExportUnit($id)
    {
        $user = AuthHandler::getUser();
        if (!$user) {
            throw new CHttpException(403, 'Bạn cần đăng nhập để xuất báo cáo.');
        }

        $model = Registrations::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy phiếu đăng ký.');
        }

        // Fetch property details
        if ($model->property_id) {
            $property = Properties::fetchFromApi($model->property_id);
            if ($property) {
                if (empty($model->property_name)) {
                    $model->property_name = $property->name;
                }
                $model->property_code = $property->prefix ? $property->prefix : $property->code;
            }
        }
        if (empty($model->event_name) && $model->event_id) {
            $event = Events::fetchFromApi($model->event_id);
            $model->event_name = $event ? $event->name : '';
        }
        if (empty($model->period_name) && $model->period_id) {
            $period = RegistrationPeriods::fetchFromApi($model->period_id);
            $model->period_name = $period ? $period->name : '';
        }

        // 1. Fetch Attendees
        $attendees = Attendees::getByRegistrationId($id);

        // 2. Fetch Sport Teams & Members
        $sportTeams = SportTeams::getApiDataProvider(array('registration_id' => $id), 100)->getData();
        $sportTeamMembers = array();
        foreach ($sportTeams as $team) {
            $teamId = isset($team->id) ? $team->id : (isset($team['id']) ? $team['id'] : null);
            if ($teamId) {
                if (empty($team->sport_name) && $team->sport_id) {
                    $sport = Sports::fetchFromApi($team->sport_id);
                    $team->sport_name = $sport ? $sport->name : '';
                }
                $membersData = SportTeamMembers::getApiDataProvider(array('sport_team_id' => $teamId), 100)->getData();
                $sportTeamMembers[$teamId] = $membersData;
            }
        }

        // 3. Fetch Competitions
        $competitionRegistrations = CompetitionRegistrations::getApiDataProvider(array('registration_id' => $id), 500)->getData();

        // 4. Fetch Beauty Contestants
        $beautyContestants = array();
        if ($model->event_id) {
            $attendeeIds = array();
            foreach ($attendees as $att) {
                if (isset($att['id'])) $attendeeIds[] = $att['id'];
            }
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
                            // Find attendee name
                            $attName = '';
                            foreach ($attendees as $attItem) {
                                if (isset($attItem['id']) && $attItem['id'] == $attId) {
                                    $attName = isset($attItem['full_name']) ? $attItem['full_name'] : '';
                                    break;
                                }
                            }
                            $c->contest_name = $contestName;
                            $c->attendee_name = $attName;
                            $beautyContestants[] = $c;
                        }
                    }
                }
            }
        }

        // 5. Fetch Talent Entries
        $talentEntries = array();
        $showsData = TalentShows::getApiDataProvider(array('event_id' => $model->event_id), 100)->getData();
        $showIds = array();
        $showNames = array();
        foreach ($showsData as $show) {
            $showId = isset($show->id) ? $show->id : (isset($show['id']) ? $show['id'] : null);
            if ($showId) {
                $showIds[] = $showId;
                $showNames[$showId] = $show->name;
            }
        }

        if (!empty($showIds)) {
            $entriesData = TalentEntries::getApiDataProvider(array(
                'property_id' => $model->property_id,
                'registration_id' => $id,
                'event_id' => $model->event_id,
            ), 100)->getData();

            foreach ($entriesData as $entry) {
                $entryShowId = isset($entry->show_id) ? $entry->show_id : (isset($entry['show_id']) ? $entry['show_id'] : null);
                if (in_array($entryShowId, $showIds)) {
                    if (empty($entry->show_name) && isset($showNames[$entryShowId])) {
                        $entry->show_name = $showNames[$entryShowId];
                    }
                    if (empty($entry->category_name) && (isset($entry->category_id) || isset($entry['category_id']))) {
                        $catId = isset($entry->category_id) ? $entry->category_id : $entry['category_id'];
                        $cat = TalentCategories::fetchFromApi($catId);
                        if ($cat) $entry->category_name = $cat->name;
                    }
                    $talentEntries[] = $entry;
                }
            }
        }

        // Initialize PHPExcel
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel.Classes');
        spl_autoload_unregister(array('YiiBase', 'autoload'));
        require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        $objPHPExcel = new PHPExcel();
        spl_autoload_register(array('YiiBase', 'autoload'));

        $objPHPExcel->getProperties()->setCreator("System")
            ->setLastModifiedBy("System")
            ->setTitle("Bao cao chi tiet don vi")
            ->setSubject("Bao cao chi tiet don vi");

        // Styling helpers
        $headerStyle = array(
            'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF'), 'size' => 11),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => '3A57E8')
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => 'CCCCCC')
                )
            )
        );

        $borderStyle = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => 'E9ECEF')
                )
            )
        );

        // SHEET 0: THÔNG TIN CHUNG
        $sheet = $objPHPExcel->setActiveSheetIndex(0);
        $sheet->setTitle('Thong_tin_chung');
        
        $sheet->setCellValue('A1', 'THÔNG TIN CHUNG PHIẾU ĐĂNG KÝ');
        $sheet->mergeCells('A1:B1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new PHPExcel_Style_Color('3A57E8'));
        
        $statusList = Registrations::getStatusList();
        $statusText = isset($statusList[$model->status]) ? $statusList[$model->status] : $model->status;

        $generalInfo = array(
            array('Sự kiện:', $model->event_name),
            array('Đơn vị đăng ký:', $model->property_name),
            array('Mã đơn vị:', $model->property_code),
            array('Đợt đăng ký:', $model->period_name),
            array('Trạng thái:', $statusText),
            array('Ngày gửi:', $model->submitted_at ? date('d/m/Y H:i', strtotime($model->submitted_at)) : '-'),
            array('Người gửi:', $model->submitted_by),
            array('Ngày duyệt:', $model->reviewed_at ? date('d/m/Y H:i', strtotime($model->reviewed_at)) : '-'),
            array('Lý do từ chối:', $model->rejection_reason ?: '-'),
            array('Ghi chú:', $model->note ?: '-'),
        );

        $row = 3;
        foreach ($generalInfo as $info) {
            $sheet->setCellValue('A' . $row, $info[0]);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $sheet->setCellValue('B' . $row, $info[1]);
            $row++;
        }
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);

        // SHEET 1: NGƯỜI THAM DỰ
        $sheet = $objPHPExcel->createSheet(1);
        $sheet->setTitle('Nguoi_tham_du');
        $headers = array('STT', 'Họ và tên', 'Phòng ban', 'Chức danh', 'Vai trò', 'Ngày vào làm', 'Ngày đến', 'Ngày đi', 'Phương tiện', 'Trạng thái');
        
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->applyFromArray($headerStyle);
            $col++;
        }

        $row = 2;
        if (empty($attendees)) {
            $sheet->setCellValue('A2', 'Không có người tham dự.');
            $sheet->mergeCells('A2:J2');
            $sheet->getStyle('A2')->getFont()->setItalic(true);
        } else {
            $stt = 1;
            foreach ($attendees as $att) {
                $roleName = Attendees::resolveRoleNames(isset($att['role_id']) ? $att['role_id'] : '');
                $startDate = isset($att['join_hotel_date']) ? $att['join_hotel_date'] : (isset($att['start_date']) ? $att['start_date'] : '');
                $checkInDate = isset($att['check_in_date']) ? $att['check_in_date'] : '';
                $checkOutDate = isset($att['check_out_date']) ? $att['check_out_date'] : '';
                $transportName = isset($att['transport_name']) ? $att['transport_name'] : '';
                $appStatus = isset($att['approval_status']) ? (int)$att['approval_status'] : 0;
                
                $statusValText = 'Chờ duyệt';
                if ($appStatus === Attendees::APPROVAL_APPROVED) $statusValText = 'Đã duyệt';
                elseif ($appStatus === Attendees::APPROVAL_REJECTED) $statusValText = 'Từ chối';

                $sheet->setCellValue('A' . $row, $stt++);
                $sheet->setCellValue('B' . $row, isset($att['full_name']) ? $att['full_name'] : '');
                $sheet->setCellValue('C' . $row, isset($att['position']) ? explode(' - ', $att['position'])[0] : '');
                $sheet->setCellValue('D' . $row, isset($att['position']) && strpos($att['position'], ' - ') !== false ? explode(' - ', $att['position'])[1] : '');
                $sheet->setCellValue('E' . $row, $roleName);
                $sheet->setCellValue('F' . $row, $startDate ? date('d/m/Y', strtotime($startDate)) : '-');
                $sheet->setCellValue('G' . $row, $checkInDate ? date('d/m/Y', strtotime($checkInDate)) : '-');
                $sheet->setCellValue('H' . $row, $checkOutDate ? date('d/m/Y', strtotime($checkOutDate)) : '-');
                $sheet->setCellValue('I' . $row, $transportName);
                $sheet->setCellValue('J' . $row, $statusValText);
                
                $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray($borderStyle);
                $row++;
            }
        }
        foreach (range('A', 'J') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // SHEET 2: THỂ THAO
        $sheet = $objPHPExcel->createSheet(2);
        $sheet->setTitle('The_thao');
        $headers = array('STT', 'Bộ môn thi đấu', 'Tên đội', 'Họ tên vận động viên', 'Phòng ban - Chức danh', 'Đơn vị thành viên');
        
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->applyFromArray($headerStyle);
            $col++;
        }

        $row = 2;
        if (empty($sportTeams)) {
            $sheet->setCellValue('A2', 'Chưa có đăng ký môn thể thao nào.');
            $sheet->mergeCells('A2:F2');
            $sheet->getStyle('A2')->getFont()->setItalic(true);
        } else {
            $stt = 1;
            foreach ($sportTeams as $team) {
                $teamId = isset($team->id) ? $team->id : (isset($team['id']) ? $team['id'] : null);
                $sportName = isset($team->sport_name) ? $team->sport_name : '';
                $teamName = isset($team->team_name) ? $team->team_name : (isset($team->name) ? $team->name : (isset($team['name']) ? $team['name'] : ''));
                $members = ($teamId && isset($sportTeamMembers[$teamId])) ? $sportTeamMembers[$teamId] : array();
                
                if (empty($members)) {
                    $sheet->setCellValue('A' . $row, $stt++);
                    $sheet->setCellValue('B' . $row, $sportName);
                    $sheet->setCellValue('C' . $row, $teamName);
                    $sheet->setCellValue('D' . $row, '(Chưa chọn VĐV)');
                    $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray($borderStyle);
                    $row++;
                } else {
                    foreach ($members as $member) {
                        $memberName = isset($member['attendee_name']) ? $member['attendee_name'] : (isset($member['name']) ? $member['name'] : '');
                        $memberPosition = isset($member['position_name']) ? $member['position_name'] : '';
                        $memberDivision = isset($member['division_name']) ? $member['division_name'] : '';
                        $memberProperty = isset($member['property_name']) ? $member['property_name'] : '';
                        
                        $sheet->setCellValue('A' . $row, $stt++);
                        $sheet->setCellValue('B' . $row, $sportName);
                        $sheet->setCellValue('C' . $row, $teamName);
                        $sheet->setCellValue('D' . $row, $memberName);
                        $sheet->setCellValue('E' . $row, $memberPosition . ($memberDivision ? ' (BP: ' . $memberDivision . ')' : ''));
                        $sheet->setCellValue('F' . $row, $memberProperty);
                        
                        $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray($borderStyle);
                        $row++;
                    }
                }
            }
        }
        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // SHEET 3: NGHIỆP VỤ
        $sheet = $objPHPExcel->createSheet(3);
        $sheet->setTitle('Nghiep_vu');
        $headers = array('STT', 'Tên cuộc thi', 'Họ tên thí sinh', 'Phòng ban', 'Chức danh');
        
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->applyFromArray($headerStyle);
            $col++;
        }

        $row = 2;
        if (empty($competitionRegistrations)) {
            $sheet->setCellValue('A2', 'Chưa có đăng ký cuộc thi nghiệp vụ nào.');
            $sheet->mergeCells('A2:E2');
            $sheet->getStyle('A2')->getFont()->setItalic(true);
        } else {
            $stt = 1;
            foreach ($competitionRegistrations as $regItem) {
                $compName = isset($regItem->competition_name) ? $regItem->competition_name : (isset($regItem['competition_name']) ? $regItem['competition_name'] : '');
                $attName = isset($regItem->attendee_name) ? $regItem->attendee_name : (isset($regItem['attendee_name']) ? $regItem['attendee_name'] : '');
                $position = isset($regItem->position_name) ? $regItem->position_name : (isset($regItem['position_name']) ? $regItem['position_name'] : '');
                $division = isset($regItem->division_name) ? $regItem->division_name : (isset($regItem['division_name']) ? $regItem['division_name'] : '');

                $sheet->setCellValue('A' . $row, $stt++);
                $sheet->setCellValue('B' . $row, $compName);
                $sheet->setCellValue('C' . $row, $attName);
                $sheet->setCellValue('D' . $row, $division);
                $sheet->setCellValue('E' . $row, $position);
                
                $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray($borderStyle);
                $row++;
            }
        }
        foreach (range('A', 'E') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // SHEET 4: THI SẮC ĐẸP (MISS)
        $sheet = $objPHPExcel->createSheet(4);
        $sheet->setTitle('Thi_Miss');
        $headers = array('STT', 'Cuộc thi', 'Họ tên thí sinh', 'Số báo danh', 'Chiều cao (cm)', 'Cân nặng (kg)', 'Số đo', 'Năng khiếu', 'Tiểu sử');
        
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->applyFromArray($headerStyle);
            $col++;
        }

        $row = 2;
        if (empty($beautyContestants)) {
            $sheet->setCellValue('A2', 'Chưa có đăng ký thí sinh thi Miss sắc đẹp.');
            $sheet->mergeCells('A2:I2');
            $sheet->getStyle('A2')->getFont()->setItalic(true);
        } else {
            $stt = 1;
            foreach ($beautyContestants as $c) {
                $sheet->setCellValue('A' . $row, $stt++);
                $sheet->setCellValue('B' . $row, isset($c->contest_name) ? $c->contest_name : '');
                $sheet->setCellValue('C' . $row, isset($c->attendee_name) ? $c->attendee_name : '');
                $sheet->setCellValue('D' . $row, isset($c->candidate_number) ? $c->candidate_number : '');
                $sheet->setCellValue('E' . $row, isset($c->height_cm) ? $c->height_cm : '');
                $sheet->setCellValue('F' . $row, isset($c->weight_kg) ? $c->weight_kg : '');
                $sheet->setCellValue('G' . $row, isset($c->measurements) ? $c->measurements : '');
                $sheet->setCellValue('H' . $row, isset($c->talent) ? $c->talent : '');
                $sheet->setCellValue('I' . $row, isset($c->bio) ? $c->bio : '');
                
                $sheet->getStyle('A' . $row . ':I' . $row)->applyFromArray($borderStyle);
                $row++;
            }
        }
        foreach (range('A', 'I') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // SHEET 5: THI VĂN NGHỆ
        $sheet = $objPHPExcel->createSheet(5);
        $sheet->setTitle('Thi_Van_nghe');
        $headers = array('STT', 'Tên tiết mục', 'Thể loại', 'Hội diễn', 'Số diễn viên', 'Đạo diễn / Biên đạo', 'SĐT đạo diễn', 'Trạng thái');
        
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->applyFromArray($headerStyle);
            $col++;
        }

        $row = 2;
        if (empty($talentEntries)) {
            $sheet->setCellValue('A2', 'Chưa có đăng ký tiết mục văn nghệ nào.');
            $sheet->mergeCells('A2:H2');
            $sheet->getStyle('A2')->getFont()->setItalic(true);
        } else {
            $stt = 1;
            foreach ($talentEntries as $entry) {
                $eTitle = isset($entry->title) ? $entry->title : (isset($entry['title']) ? $entry['title'] : '');
                $eCatName = isset($entry->category_name) ? $entry->category_name : '';
                $eShowName = isset($entry->show_name) ? $entry->show_name : '';
                $ePCount = isset($entry->participant_count) ? (int)$entry->participant_count : 0;
                $eDir = isset($entry->director) ? $entry->director : '';
                $eDirPhone = isset($entry->director_phone) ? $entry->director_phone : '';
                $entryStatus = isset($entry->status) ? (int)$entry->status : 0;

                $statusTextText = 'Nháp';
                if ($entryStatus === TalentEntries::STATUS_SUBMITTED) $statusTextText = 'Đã nộp';
                elseif ($entryStatus === TalentEntries::STATUS_APPROVED) $statusTextText = 'Đã duyệt';
                elseif ($entryStatus === TalentEntries::STATUS_REJECTED) $statusTextText = 'Từ chối';
                elseif ($entryStatus === TalentEntries::STATUS_PENDING) $statusTextText = 'Chờ xử lý';

                $sheet->setCellValue('A' . $row, $stt++);
                $sheet->setCellValue('B' . $row, $eTitle);
                $sheet->setCellValue('C' . $row, $eCatName);
                $sheet->setCellValue('D' . $row, $eShowName);
                $sheet->setCellValue('E' . $row, $ePCount);
                $sheet->setCellValue('F' . $row, $eDir);
                $sheet->setCellValue('G' . $row, $eDirPhone);
                $sheet->setCellValue('H' . $row, $statusTextText);
                
                $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray($borderStyle);
                $row++;
            }
        }
        foreach (range('A', 'H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Set back active sheet to the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        // Send Excel file header
        $propName = !empty($model->property_name) ? $model->property_name : 'Don_vi';
        $safeName = preg_replace('/[^A-Za-z0-9]/', '_', UrlTransliterate::cleanString($propName, '_'));
        $filename = "Bao_cao_chi_tiet_" . $safeName . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        Yii::app()->end();
    }
}
