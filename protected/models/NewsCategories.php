<?php

Yii::import('application.models._base.BaseNewsCategories');

class NewsCategories extends BaseNewsCategories
{
	const IS_ACTIVE = 1;
	const IS_INACTIVE = 0;

	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	public static function getActiveLabel($isActive)
	{
		return $isActive
			? '<span class="badge bg-success">Kích hoạt</span>'
			: '<span class="badge bg-secondary">Tạm ẩn</span>';
	}

	public static function fetchFromApi($id)
	{
		$url = ApiEndpoints::url(ApiEndpoints::NEWS_CATEGORY_DETAIL, array('id' => $id));
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
		return ApiClient::post(ApiEndpoints::NEWS_CATEGORY_STORE, $data);
	}

	public function updateViaApi()
	{
		$data = array_filter($this->attributes, function ($value) {
			return $value !== null && $value !== '';
		});
		$url = ApiEndpoints::url(ApiEndpoints::NEWS_CATEGORY_UPDATE, array('id' => $this->id));
		return ApiClient::put($url, $data);
	}

	public static function deleteViaApi($id)
	{
		$url = ApiEndpoints::url(ApiEndpoints::NEWS_CATEGORY_DESTROY, array('id' => $id));
		return ApiClient::delete($url);
	}

	public static function reorderViaApi($categories)
	{
		return ApiClient::post(ApiEndpoints::NEWS_CATEGORY_REORDER, array('categories' => $categories));
	}

	public static function getApiDataProvider($params = array(), $pageSize = 100)
	{
		return new ApiDataProvider(ApiEndpoints::NEWS_CATEGORY_LIST, array(
			'modelClass' => 'NewsCategories',
			'params' => $params,
			'pagination' => array(
				'pageSize' => $pageSize,
			),
		));
	}

	/**
	 * Danh sách danh mục active dạng key=>value cho dropdown
	 */
	public static function getActiveList($eventId = null)
	{
		$params = array('is_active' => 1);
		if ($eventId !== null) {
			$params['event_id'] = $eventId;
		}
		$provider = self::getApiDataProvider($params);
		$list = array();
		foreach ($provider->getData() as $item) {
			$list[$item->id] = $item->name;
		}
		return $list;
	}
}
