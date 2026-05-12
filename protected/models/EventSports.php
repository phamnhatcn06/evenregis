<?php

Yii::import('application.models._base.BaseEventSports');

class EventSports extends BaseEventSports
{
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	public static function getByEventId($eventId)
	{
		$result = ApiClient::get(ApiEndpoints::EVENT_SPORT_LIST, array('event_id' => $eventId));
		if ($result['success'] && isset($result['data']['data'])) {
			return $result['data']['data'];
		}
		return array();
	}

	public static function storeViaApi($eventId, $sportId)
	{
		return ApiClient::post(ApiEndpoints::EVENT_SPORT_STORE, array(
			'event_id' => $eventId,
			'sport_id' => $sportId,
		));
	}

	public static function deleteViaApi($id)
	{
		$url = ApiEndpoints::url(ApiEndpoints::EVENT_SPORT_DESTROY, array('id' => $id));
		return ApiClient::delete($url);
	}
}