<?php

Yii::import('application.models._base.BaseRegistrations');

class Registrations extends BaseRegistrations
{
	const STATUS_DRAFT = 0;
	const STATUS_SUBMITTED = 1;
	const STATUS_APPROVED = 2;
	const STATUS_REJECTED = 3;

	// Virtual properties từ API (joined data)
	public $event_name;
	public $property_name;
	public $property_code;
	public $relation_property_name;
	public $period_name;
	public $submitted_by_name;
	public $reviewed_by_name;

	public static function model($className = __CLASS__)
	{
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
			0 => '<span class="badge bg-secondary">Nháp</span>',
			1 => '<span class="badge bg-info">Đã nộp</span>',
			2 => '<span class="badge bg-success">Đã duyệt</span>',
			3 => '<span class="badge bg-danger">Từ chối</span>',
		);
		return isset($labels[$status]) ? $labels[$status] : $status;
	}

	public static function getStatusList()
	{
		return array(
			0 => 'Nháp',
			1 => 'Đã nộp',
			2 => 'Đã duyệt',
			3 => 'Từ chối',
		);
	}
}
