<?php

Yii::import('application.models._base.BaseStaffs');

class Staffs extends BaseStaffs
{
	public $property_name;
	public $division_name;
	public $position_name;
	public $department_code;

	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	public function rules()
	{
		$rules = parent::rules();
		$rules[] = array('department_code', 'length', 'max' => 50);
		$rules[] = array('department_code', 'safe');
		return $rules;
	}

	public function attributeLabels()
	{
		$labels = parent::attributeLabels();
		$labels['department_code'] = Yii::t('app', 'Mã phòng ban');
		return $labels;
	}

	public static function fetchFromApi($id)
	{
		$url = ApiEndpoints::url(ApiEndpoints::STAFF_DETAIL, array('id' => $id));
		$result = ApiClient::get($url);
		if ($result['success'] && isset($result['data'])) {
			$data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
			$model = new self;
			$model->setAttributes($data, false);
			$model->position_name = isset($data['position_name']) ? $data['position_name'] : '';
			$model->division_name = isset($data['division_name']) ? $data['division_name'] : '';
			$model->property_name = isset($data['property_name']) ? $data['property_name'] : '';
			$model->department_code = isset($data['department_code']) ? $data['department_code'] : '';
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
