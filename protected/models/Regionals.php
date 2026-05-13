<?php

Yii::import('application.models._base.BaseRegionals');

class Regionals extends BaseRegionals
{
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	public static function label($n = 1)
	{
		return Yii::t('app', 'Khu vực|Khu vực', $n);
	}

	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'code' => 'Mã khu vực',
			'name' => 'Tên khu vực',
			'description' => 'Mô tả',
			'status' => 'Trạng thái',
			'created_at' => 'Ngày tạo',
			'updated_at' => 'Ngày cập nhật',
			'deleted_at' => 'Ngay xoa',
		);
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
		$dataProvider = Properties::getApiDataProvider(array('region_id' => $regionalId), 500);
		return $dataProvider->getData();
	}

	public static function assignOrganizations($regionalId, $newOrgIds)
	{

		$currentOrgs = self::getOrganizations($regionalId);
		$currentOrgIds = array_column($currentOrgs, 'id');
		$errors = array();
		// Remove regional_id from properties no longer assigned
		$toRemove = array_diff($currentOrgIds, $newOrgIds);
		foreach ($toRemove as $propId) {
			$url = ApiEndpoints::url(ApiEndpoints::PROPERTY_UPDATE, array('id' => $propId));
			$result = ApiClient::post($url, array('region_id' => null));
			if (!$result['success']) {
				$errors[] = "Failed to remove property $propId";
			}
		}
		// Add regional_id to newly assigned properties
		$toAdd = array_diff($newOrgIds, $currentOrgIds);
		foreach ($toAdd as $propId) {
			$url = ApiEndpoints::url(ApiEndpoints::PROPERTY_UPDATE, array('id' => $propId));
			$result = ApiClient::post($url, array('region_id' => $regionalId));
			if (!$result['success']) {
				$errors[] = "Failed to assign property $propId";
			}
		}

		return array(
			'success' => empty($errors),
			'error' => implode(', ', $errors),
		);
	}
}
