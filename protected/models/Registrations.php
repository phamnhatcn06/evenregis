<?php

Yii::import('application.models._base.BaseRegistrations');

class Registrations extends BaseRegistrations
{
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	public static function fetchFromApi($id)
	{
		$url = ApiEndpoints::url(ApiEndpoints::REGISTRATION_DETAIL, array('id' => $id));
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
		return ApiClient::post(ApiEndpoints::REGISTRATION_STORE, $data);
	}

	public function updateViaApi()
	{
		$data = array_filter($this->attributes, function ($value) {
			return $value !== null && $value !== '';
		});
		$url = ApiEndpoints::url(ApiEndpoints::REGISTRATION_UPDATE, array('id' => $this->id));
		return ApiClient::post($url, $data);
	}

	public static function deleteViaApi($id)
	{
		$url = ApiEndpoints::url(ApiEndpoints::REGISTRATION_DESTROY, array('id' => $id));
		return ApiClient::delete($url);
	}

	public static function getApiDataProvider($params = array(), $pageSize = 25)
	{
		return new ApiDataProvider(ApiEndpoints::REGISTRATION_LIST, array(
			'modelClass' => 'Registrations',
			'params' => $params,
			'pagination' => array('pageSize' => $pageSize),
		));
	}

	public static function getStatusLabel($status)
	{
		$labels = array(
			'draft' => '<span class="badge bg-secondary">Nháp</span>',
			'submitted' => '<span class="badge bg-info">Đã nộp</span>',
			'approved' => '<span class="badge bg-success">Đã duyệt</span>',
			'rejected' => '<span class="badge bg-danger">Từ chối</span>',
		);
		return isset($labels[$status]) ? $labels[$status] : $status;
	}

	public static function getStatusList()
	{
		return array(
			'draft' => 'Nháp',
			'submitted' => 'Đã nộp',
			'approved' => 'Đã duyệt',
			'rejected' => 'Từ chối',
		);
	}
}