<?php

Yii::import('application.models._base.BaseProperties');

class Properties extends BaseProperties
{
	public $regional_id;

	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	public static function fetchFromApi($id)
	{
		$url = ApiEndpoints::url(ApiEndpoints::PROPERTY_DETAIL, array('id' => $id));
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
		return ApiClient::post(ApiEndpoints::PROPERTY_STORE, $data);
	}

	public function updateViaApi()
	{
		$data = array_filter($this->attributes, function ($value) {
			return $value !== null && $value !== '';
		});
		$url = ApiEndpoints::url(ApiEndpoints::PROPERTY_UPDATE, array('id' => $this->id));
		return ApiClient::post($url, $data);
	}

	public static function deleteViaApi($id)
	{
		$url = ApiEndpoints::url(ApiEndpoints::PROPERTY_DESTROY, array('id' => $id));
		return ApiClient::delete($url);
	}

	public static function getApiDataProvider($params = array(), $pageSize = 100)
	{
		return new ApiDataProvider(ApiEndpoints::PROPERTY_LIST, array(
			'modelClass' => 'Properties',
			'params' => $params,
			'pagination' => array(
				'pageSize' => $pageSize,
			),
		));
	}
}
