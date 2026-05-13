<?php

Yii::import('application.models._base.BaseRegistrationPeriods');

class RegistrationPeriods extends BaseRegistrationPeriods
{
	public $event_name;

	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	public static function fetchFromApi($id)
	{
		$url = ApiEndpoints::url(ApiEndpoints::REGISTRATION_PERIOD_DETAIL, array('id' => $id));
		$result = ApiClient::get($url);
		if ($result['success'] && isset($result['data'])) {
			$data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
			$model = new self;
			$model->setAttributes($data, false);
			$model->id = $id;
			$model->mapRelations($data);
			return $model;
		}
		return null;
	}

	public function setAttributes($values, $safeOnly = true)
	{
		parent::setAttributes($values, $safeOnly);
		if (is_array($values)) {
			$this->mapRelations($values);
		}
	}

	protected function mapRelations($data)
	{
		if (isset($data['event']['name'])) {
			$this->event_name = $data['event']['name'];
		} elseif (isset($data['event_name'])) {
			$this->event_name = $data['event_name'];
		}
	}

	public function storeViaApi()
	{
		$data = $this->prepareApiData();
		return ApiClient::post(ApiEndpoints::REGISTRATION_PERIOD_STORE, $data);
	}

	public function updateViaApi()
	{
		$data = $this->prepareApiData();
		$url = ApiEndpoints::url(ApiEndpoints::REGISTRATION_PERIOD_UPDATE, array('id' => $this->id));
		return ApiClient::post($url, $data);
	}

	protected function prepareApiData()
	{
		$data = array();
		$data['name'] = $this->name;
		$data['event_id'] = (int) $this->event_id;
		$data['is_active'] = (bool) $this->is_active;
		$data['max_per_org'] = $this->max_per_org ? (int) $this->max_per_org : null;
		$data['note'] = $this->note ?: null;

		if ($this->start_time) {
			$data['start_time'] = date('Y-m-d H:i:s', $this->start_time);
		}
		if ($this->end_time) {
			$data['end_time'] = date('Y-m-d H:i:s', $this->end_time);
		}

		return $data;
	}

	public static function deleteViaApi($id)
	{
		$url = ApiEndpoints::url(ApiEndpoints::REGISTRATION_PERIOD_DESTROY, array('id' => $id));
		return ApiClient::delete($url);
	}

	public static function getApiDataProvider($params = array(), $pageSize = 25)
	{
		return new ApiDataProvider(ApiEndpoints::REGISTRATION_PERIOD_LIST, array(
			'modelClass' => 'RegistrationPeriods',
			'params' => $params,
			'pagination' => array('pageSize' => $pageSize),
		));
	}

	public static function getActiveList()
	{
		$list = array();
		$items = self::getApiDataProvider(array('is_active' => 1), 100)->getData();
		foreach ($items as $item) {
			$list[$item->id] = $item->name;
		}
		return $list;
	}
}
