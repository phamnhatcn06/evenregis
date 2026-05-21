<?php

class DefaultController extends AdminController
{
    public function actionIndex()
    {
        $stats = $this->fetchDashboardStats();
        $this->render('index', array('stats' => $stats));
    }

    protected function fetchDashboardStats()
    {
        $result = ApiClient::get(ApiEndpoints::DASHBOARD_STATS);

        if ($result['success'] && isset($result['data'])) {
            $data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
            return $data;
        }

        return array(
            'total_properties' => 0,
            'registered_properties' => 0,
            'unregistered_properties' => array(),
            'registrations_by_status' => array(
                'draft' => 0,
                'submitted' => 0,
                'approved' => 0,
                'rejected' => 0,
            ),
            'sport_teams' => 0,
            'competition_participants' => 0,
            'beauty_contestants' => 0,
            'total_attendees' => 0,
        );
    }
}
