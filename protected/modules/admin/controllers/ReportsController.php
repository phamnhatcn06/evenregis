<?php

class ReportsController extends AdminController
{
    public function actionIndex()
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
            $status = isset($reg->status) ? (int)$reg->status : 0;
            if ($status !== Registrations::STATUS_DRAFT) {
                $submittedRegistrations[] = $reg;
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
        $attendeesRes = Attendees::getApiDataProvider($attParams, 5000)->getData();

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

        // Sort attendee stats by property_name naturally
        usort($attendeeStats, function ($a, $b) {
            return strnatcasecmp($a['property_name'], $b['property_name']);
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
        $totalTalentParticipantsCount = 0;
        foreach ($talentEntries as $entry) {
            $totalTalentParticipantsCount += isset($entry->participant_count) ? (int)$entry->participant_count : 0;
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
            'kpis' => array(
                'registered_units' => count($uniqueRegisteredOrgs),
                'total_units' => count($properties),
                'total_attendees' => $totalAttendeesCount,
                'total_talent_entries' => $totalTalentEntriesCount,
                'total_talent_participants' => $totalTalentParticipantsCount,
            )
        ));
    }
}
