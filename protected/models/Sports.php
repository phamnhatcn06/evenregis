<?php

Yii::import('application.models._base.BaseSports');

class Sports extends BaseSports
{
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	public static function fetchFromApi($id)
	{
		$url = ApiEndpoints::url(ApiEndpoints::SPORT_DETAIL, array('id' => $id));
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
		return ApiClient::post(ApiEndpoints::SPORT_STORE, $data);
	}

	public function updateViaApi()
	{
		$url = ApiEndpoints::url(ApiEndpoints::SPORT_UPDATE, array('id' => $this->id));
		return ApiClient::post($url, $this->attributes);
	}

	public static function deleteViaApi($id)
	{
		$url = ApiEndpoints::url(ApiEndpoints::SPORT_DESTROY, array('id' => $id));
		return ApiClient::delete($url);
	}

	public static function getApiDataProvider($params = array(), $pageSize = 25)
	{
		return new ApiDataProvider(ApiEndpoints::SPORT_LIST, array(
			'modelClass' => 'Sports',
			'params' => $params,
			'pagination' => array(
				'pageSize' => $pageSize,
			),
		));
	}

	public static function getParentList($excludeId = null)
	{
		$list = array('0' => '-- Root (Gốc) --');
		$sports = self::getApiDataProvider(array('is_active' => 1), 100)->getData();
		foreach ($sports as $sport) {
			if ($excludeId === null || $sport->id != $excludeId) {
				$list[$sport->id] = $sport->name;
			}
		}
		return $list;
	}

	public static function buildTreeData()
	{
		$sports = self::getApiDataProvider(array(), 200)->getData();

		$items = array();
		$children = array();

		foreach ($sports as $sport) {
			$items[$sport->id] = $sport;
			$parentId = $sport->parent_id ? $sport->parent_id : 0;
			if (!isset($children[$parentId])) {
				$children[$parentId] = array();
			}
			$children[$parentId][] = $sport->id;
		}

		$result = array();
		$levelMap = array();

		self::buildTreeRecursive($items, $children, 0, 0, $result, $levelMap);

		return array(
			'items' => $result,
			'levelMap' => $levelMap,
		);
	}

	private static function buildTreeRecursive($items, $children, $parentId, $level, &$result, &$levelMap)
	{
		if (!isset($children[$parentId])) {
			return;
		}

		foreach ($children[$parentId] as $id) {
			if (isset($items[$id])) {
				$result[] = $items[$id];
				$levelMap[$id] = $level;
				self::buildTreeRecursive($items, $children, $id, $level + 1, $result, $levelMap);
			}
		}
	}
}
