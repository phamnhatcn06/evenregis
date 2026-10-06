<?php

/**
 * Dashboard mức lấp đầy suất Fun Run + Đi tham quan theo sự kiện.
 * Chỉ đọc (actionIndex là public action). Dữ liệu qua Model (RunEvents / TourSessions).
 */
class ActivityDashboardController extends AdminController
{
    public function actionIndex()
    {
        $eventId = isset($_GET['event_id']) && $_GET['event_id'] !== '' ? (int) $_GET['event_id'] : null;

        $runEvents = array();
        $tourSessions = array();
        if ($eventId) {
            $runEvents = RunEvents::getApiDataProvider(array('event_id' => $eventId), 1000)->getData();
            $tourSessions = TourSessions::getApiDataProvider(array('event_id' => $eventId), 1000)->getData();
        }

        $this->render('index', array(
            'eventId'      => $eventId,
            'eventList'    => $this->getEventList(),
            'runEvents'    => $runEvents,
            'tourSessions' => $tourSessions,
        ));
    }

    protected function getEventList()
    {
        $list = array();
        try {
            $events = Events::getApiDataProvider(array(), 200)->getData();
            foreach ($events as $e) {
                $list[$e->id] = $e->name;
            }
        } catch (Exception $e) {
            // bỏ qua
        }
        return $list;
    }
}
