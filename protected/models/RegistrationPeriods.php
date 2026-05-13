<?php

Yii::import('application.models._base.BaseRegistrationPeriods');

class RegistrationPeriods extends BaseRegistrationPeriods
{
	public static function model($className=__CLASS__) {
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
			return $model;
		}
		return null;
	}

	public function storeViaApi()
	{
		$data = array_filter($this->attributes, function ($value) {
			return $value !== null && $value !== '';
		});
		return ApiClient::post(ApiEndpoints::REGISTRATION_PERIOD_STORE, $data);
	}

	public function updateViaApi()
	{
		$data = array_filter($this->attributes, function ($value) {
			return $value !== null && $value !== '';
		});
		$url = ApiEndpoints::url(ApiEndpoints::REGISTRATION_PERIOD_UPDATE, array('id' => $this->id));
		return ApiClient::post($url, $data);
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