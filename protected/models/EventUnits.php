<?php

Yii::import('application.models._base.BaseEventUnits');

class EventUnits extends BaseEventUnits
{
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	public static function fetchFromApi($id)
	{
		$url = ApiEndpoints::url(ApiEndpoints::EVENT_UNIT_DETAIL, array('id' => $id));
		$result = ApiClient::get($url);
		if ($result['success'] && isset($result['data'])) {
			$data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
			$model = new self;
			$model->setAttributes($data, false);
			$model->id = $id;
			return $model;
		}
		return null;
	}

	public function storeViaApi()
	{
		$data = array_filter($this->attributes, function ($value) {
			return $value !== null && $value !== '';
		});
		return ApiClient::post(ApiEndpoints::EVENT_UNIT_STORE, $data);
	}

	public static function deleteViaApi($id)
	{
		$url = ApiEndpoints::url(ApiEndpoints::EVENT_UNIT_DESTROY, array('id' => $id));
		return ApiClient::delete($url);
	}

	public static function getApiDataProvider($params = array(), $pageSize = 25)
	{
		return new ApiDataProvider(ApiEndpoints::EVENT_UNIT_LIST, array(
			'modelClass' => 'EventUnits',
			'params' => $params,
			'pagination' => array('pageSize' => $pageSize),
		));
	}

	public static function getByEventId($eventId)
	{
		$result = ApiClient::get(ApiEndpoints::EVENT_UNIT_LIST, array('event_id' => $eventId));
		if ($result['success'] && isset($result['data']['data'])) {
			return $result['data']['data'];
		}
		return array();
	}
}