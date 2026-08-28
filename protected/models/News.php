<?php

Yii::import('application.models._base.BaseNews');

class News extends BaseNews
{
	const IS_PUBLISHED = 1;
	const IS_DRAFT = 0;

	const IS_FEATURED = 1;
	const NOT_FEATURED = 0;

	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * Danh mục loại tin (enum trong bảng news.category)
	 */
	public static function getCategoryOptions()
	{
		return array(
			'general' => 'Tổng hợp',
			'announcement' => 'Thông báo',
			'sports' => 'Thể thao',
			'competition' => 'Thi nghiệp vụ',
			'beauty' => 'Sắc đẹp',
			'talent' => 'Văn nghệ',
		);
	}

	public function getCategoryLabel()
	{
		$options = self::getCategoryOptions();
		return isset($options[$this->category]) ? $options[$this->category] : $this->category;
	}

	public static function getPublishedLabel($isPublished)
	{
		return $isPublished
			? '<span class="badge bg-success">Đã xuất bản</span>'
			: '<span class="badge bg-secondary">Bản nháp</span>';
	}

	public static function getFeaturedLabel($isFeatured)
	{
		return $isFeatured
			? '<span class="badge bg-warning text-dark">Nổi bật</span>'
			: '<span class="badge bg-light text-dark">Thường</span>';
	}

	public static function fetchFromApi($id)
	{
		$url = ApiEndpoints::url(ApiEndpoints::NEWS_DETAIL, array('id' => $id));
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
		return ApiClient::post(ApiEndpoints::NEWS_STORE, $data);
	}

	public function updateViaApi()
	{
		$data = array_filter($this->attributes, function ($value) {
			return $value !== null && $value !== '';
		});
		$url = ApiEndpoints::url(ApiEndpoints::NEWS_UPDATE, array('id' => $this->id));
		return ApiClient::put($url, $data);
	}

	public static function deleteViaApi($id)
	{
		$url = ApiEndpoints::url(ApiEndpoints::NEWS_DESTROY, array('id' => $id));
		return ApiClient::delete($url);
	}

	public static function getApiDataProvider($params = array(), $pageSize = 25)
	{
		return new ApiDataProvider(ApiEndpoints::NEWS_LIST, array(
			'modelClass' => 'News',
			'params' => $params,
			'pagination' => array(
				'pageSize' => $pageSize,
			),
		));
	}
}
