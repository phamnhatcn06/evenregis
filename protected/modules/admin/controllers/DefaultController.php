<?php

class DefaultController extends AdminController
{
    /**
     * AJAX action to get dashboard stats filtered by event and period
     */
    public function actionGetStats()
    {
        if (!Yii::app()->request->isAjaxRequest) {
            throw new CHttpException(400, 'Bad Request');
        }

        $eventId = Yii::app()->request->getParam('event_id');
        $periodId = Yii::app()->request->getParam('period_id');

        $stats = $this->fetchDashboardStats($eventId, $periodId);

        echo CJSON::encode(array(
            'success' => true,
            'data' => $stats,
        ));
        Yii::app()->end();
    }

    /**
     * Clear all cache for current user
     */
    public function actionClearCache()
    {
        if (!Yii::app()->request->isPostRequest || !Yii::app()->request->isAjaxRequest) {
            throw new CHttpException(400, 'Bad Request');
        }

        $userResults = CacheHelper::clearUserCache();
        $staticResults = CacheHelper::clearAllStaticCache();

        echo CJSON::encode(array(
            'success' => true,
            'message' => 'Đã xóa cache thành công',
            'details' => array(
                'user' => $userResults,
                'static' => $staticResults,
            ),
        ));
        Yii::app()->end();
    }

    public function actionIndex()
    {
        $stats = $this->fetchDashboardStats();
        $this->render('index', array('stats' => $stats));
    }

    protected function fetchDashboardStats($eventId = null, $periodId = null)
    {
        // Build filter params
        $filterParams = array('per_page' => 1000);
        if ($eventId) {
            $filterParams['event_id'] = $eventId;
        }
        if ($periodId) {
            $filterParams['period_id'] = $periodId;
        }

        // 1. Fetch all properties
        $propertiesResult = ApiClient::get(ApiEndpoints::PROPERTY_LIST, array('per_page' => 1000));
        $properties = array();
        if ($propertiesResult['success'] && isset($propertiesResult['data']['data'])) {
            $properties = $propertiesResult['data']['data'];
        }

        // 2. Fetch all registrations
        $registrationsResult = ApiClient::get(ApiEndpoints::REGISTRATION_LIST, array('per_page' => 1000));
        $registrations = array();
        if ($registrationsResult['success'] && isset($registrationsResult['data']['data'])) {
            $registrations = $registrationsResult['data']['data'];
        }

        // 3. Count registrations by status and group property IDs by status
        $registrationsByStatus = array(
            'draft' => 0,
            'submitted' => 0,
            'approved' => 0,
            'rejected' => 0,
        );

        $propertyIdsByStatus = array(
            'draft' => array(),
            'submitted' => array(),
            'approved' => array(),
            'rejected' => array(),
        );

        foreach ($registrations as $reg) {
            $status = isset($reg['status']) ? (int)$reg['status'] : 0;
            $propertyId = isset($reg['property_id']) ? $reg['property_id'] : null;

            if ($status === 0) {
                $registrationsByStatus['draft']++;
                if ($propertyId !== null) {
                    $propertyIdsByStatus['draft'][$propertyId] = true;
                }
            } elseif ($status === 1) {
                $registrationsByStatus['submitted']++;
                if ($propertyId !== null) {
                    $propertyIdsByStatus['submitted'][$propertyId] = true;
                }
            } elseif ($status === 2) {
                $registrationsByStatus['approved']++;
                if ($propertyId !== null) {
                    $propertyIdsByStatus['approved'][$propertyId] = true;
                }
            } elseif ($status === 3) {
                $registrationsByStatus['rejected']++;
                if ($propertyId !== null) {
                    $propertyIdsByStatus['rejected'][$propertyId] = true;
                }
            }
        }

        // 4. Group properties by registration status
        $propertiesNotStarted = array();
        $propertiesDraft = array();
        $propertiesSubmitted = array();

        foreach ($properties as $prop) {
            $propertyId = isset($prop['id']) ? $prop['id'] : null;
            $propData = array(
                'code' => isset($prop['code']) ? $prop['code'] : '',
                'name' => isset($prop['name']) ? $prop['name'] : '',
            );

            if ($propertyId === null) {
                $propertiesNotStarted[] = $propData;
            } elseif (isset($propertyIdsByStatus['submitted'][$propertyId]) || isset($propertyIdsByStatus['approved'][$propertyId]) || isset($propertyIdsByStatus['rejected'][$propertyId])) {
                $propertiesSubmitted[] = $propData;
            } elseif (isset($propertyIdsByStatus['draft'][$propertyId])) {
                $propertiesDraft[] = $propData;
            } else {
                $propertiesNotStarted[] = $propData;
            }
        }

        // Sort by name
        $sortByName = function ($a, $b) {
            return strnatcmp($a['name'], $b['name']);
        };
        usort($propertiesNotStarted, $sortByName);
        usort($propertiesDraft, $sortByName);
        usort($propertiesSubmitted, $sortByName);

        // 5. Fetch other statistics dynamically for cards
        $sportTeams = 0;
        $sportRes = ApiClient::get(ApiEndpoints::SPORT_TEAM_LIST, array('per_page' => 1));
        if ($sportRes['success'] && isset($sportRes['data']['pagination']['total'])) {
            $sportTeams = (int)$sportRes['data']['pagination']['total'];
        }

        $beautyContestants = 0;
        $beautyRes = ApiClient::get(ApiEndpoints::BEAUTY_REGISTRATION_LIST, array('per_page' => 1));
        if ($beautyRes['success'] && isset($beautyRes['data']['pagination']['total'])) {
            $beautyContestants = (int)$beautyRes['data']['pagination']['total'];
        }

        $talentEntries = 0;
        $talentRes = ApiClient::get(ApiEndpoints::TALENT_ENTRY_LIST, array('per_page' => 1));
        if ($talentRes['success'] && isset($talentRes['data']['pagination']['total'])) {
            $talentEntries = (int)$talentRes['data']['pagination']['total'];
        }

        $totalAttendees = 0;
        $attendeeRes = ApiClient::get(ApiEndpoints::ATTENDEE_LIST, array('per_page' => 1));
        if ($attendeeRes['success'] && isset($attendeeRes['data']['pagination']['total'])) {
            $totalAttendees = (int)$attendeeRes['data']['pagination']['total'];
        }

        $competitionParticipants = 0;
        $compRes = ApiClient::get(ApiEndpoints::COMPETITION_REGISTRATION_LIST, array('per_page' => 1));
        if ($compRes['success'] && isset($compRes['data']['pagination']['total'])) {
            $competitionParticipants = (int)$compRes['data']['pagination']['total'];
        }

        $result = ApiClient::get(ApiEndpoints::DASHBOARD_STATS);
        $data = array();
        if ($result['success'] && isset($result['data'])) {
            $data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
        }

        return array_merge(array(
            'sport_teams' => $sportTeams,
            'competition_participants' => $competitionParticipants,
            'beauty_contestants' => $beautyContestants,
            'talent_entries' => $talentEntries,
            'total_attendees' => $totalAttendees,
        ), $data, array(
            'total_properties' => count($properties),
            'registered_properties' => count($propertiesSubmitted),
            'registrations_by_status' => $registrationsByStatus,
            'properties_not_started' => $propertiesNotStarted,
            'properties_draft' => $propertiesDraft,
            'properties_submitted' => $propertiesSubmitted,
        ));
    }
}
