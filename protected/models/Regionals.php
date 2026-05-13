<?php

Yii::import('application.models._base.BaseRegionals');

class Regionals extends BaseRegionals
{
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	public static function label($n = 1) {
		return Yii::t('app', 'Khu vực|Khu vực', $n);
	}

	public static function fetchFromApi($id)
	{
		$url = ApiEndpoints::url(ApiEndpoints::REGIONAL_DETAIL, array('id' => $id));
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
		return ApiClient::post(ApiEndpoints::REGIONAL_STORE, $data);
	}

	public function updateViaApi()
	{
		$data = array_filter($this->attributes, function ($value) {
			return $value !== null && $value !== '';
		});
		$url = ApiEndpoints::url(ApiEndpoints::REGIONAL_UPDATE, array('id' => $this->id));
		return ApiClient::post($url, $data);
	}

	public static function deleteViaApi($id)
	{
		$url = ApiEndpoints::url(ApiEndpoints::REGIONAL_DESTROY, array('id' => $id));
		return ApiClient::delete($url);
	}

	public static function getApiDataProvider($params = array(), $pageSize = 25)
	{
		return new ApiDataProvider(ApiEndpoints::REGIONAL_LIST, array(
			'modelClass' => 'Regionals',
			'params' => $params,
			'pagination' => array(
				'pageSize' => $pageSize,
			),
		));
	}

	public static function getOrganizations($regionalId)
	{
		$url = ApiEndpoints::url(ApiEndpoints::REGIONAL_ORGANIZATIONS, array('id' => $regionalId));
		$result = ApiClient::get($url);
		if ($result['success'] && isset($result['data'])) {
			return isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
		}
		return array();
	}

	public static function assignOrganizations($regionalId, $organizationIds)
	{
		$url = ApiEndpoints::url(ApiEndpoints::REGIONAL_ASSIGN_ORGANIZATIONS, array('id' => $regionalId));
		return ApiClient::post($url, array('organization_ids' => $organizationIds));
	}
}
