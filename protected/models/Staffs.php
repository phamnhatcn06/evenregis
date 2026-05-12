<?php

Yii::import('application.models._base.BaseStaffs');

class Staffs extends BaseStaffs
{
	public $property_name;
	public $division_name;
	public $position_name;

	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	public static function fetchFromApi($id)
	{
		$url = ApiEndpoints::url(ApiEndpoints::STAFF_DETAIL, array('id' => $id));
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
		return ApiClient::post(ApiEndpoints::STAFF_STORE, $data);
	}

	public function updateViaApi()
	{
		$data = array_filter($this->attributes, function ($value) {
			return $value !== null && $value !== '';
		});
		$url = ApiEndpoints::url(ApiEndpoints::STAFF_UPDATE, array('id' => $this->id));
		return ApiClient::post($url, $data);
	}

	public static function deleteViaApi($id)
	{
		$url = ApiEndpoints::url(ApiEndpoints::STAFF_DESTROY, array('id' => $id));
		return ApiClient::delete($url);
	}

	public static function fetchPropertiesForDropdown()
	{
		$result = ApiClient::get(ApiEndpoints::PROPERTY_LIST, array('per_page' => 1000));
		return $result;
	}

	public static function getApiDataProvider($params = array(), $pageSize = 2500)
	{
		return new ApiDataProvider(ApiEndpoints::STAFF_LIST, array(
			'modelClass' => 'Staffs',
			'params' => $params,
			'pagination' => array(
				'pageSize' => $pageSize,
			),
		));
	}
}
