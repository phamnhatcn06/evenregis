<?php

Yii::import('application.models._base.BaseEventCompetitions');

class EventCompetitions extends BaseEventCompetitions
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public static function getByEventId($eventId)
    {
        $result = ApiClient::get(ApiEndpoints::EVENT_COMPETITION_LIST, array('event_id' => $eventId));
        if ($result['success'] && isset($result['data']['data'])) {
            return $result['data']['data'];
        }
        return array();
    }

    public static function storeViaApi($eventId, $competitionId)
    {
        return ApiClient::post(ApiEndpoints::EVENT_COMPETITION_STORE, array(
            'event_id' => $eventId,
            'competition_id' => $competitionId,
        ));
    }

    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::EVENT_COMPETITION_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }
}
