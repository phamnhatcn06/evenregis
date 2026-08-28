<?php

/**
 * NewsCategories — Danh mục tin tức.
 *
 * Bảng `news_categories` được quản lý qua External API (không có trong DB local),
 * nên model này là CFormModel giữ attribute + gọi ApiClient theo ApiEndpoints.
 * Thuộc tính bám theo cấu trúc bảng `news_categories` trong docs/mt_registration_portal_struct.sql.
 *
 * Hợp đồng API (xem docs/MTRegistrationPortal API.postman_collection.json — Daihoi Admin):
 *   - GET    /api/admin/news-categories
 *   - POST   /api/admin/news-categories
 *   - GET    /api/admin/news-categories/{id}
 *   - PUT    /api/admin/news-categories/{id}
 *   - DELETE /api/admin/news-categories/{id}
 *   - POST   /api/admin/news-categories/reorder
 */
class NewsCategories extends CFormModel
{
	const IS_ACTIVE = 1;
	const IS_INACTIVE = 0;

	public $id;
	public $event_id;
	public $name;
	public $slug;
	public $icon;
	public $color;
	public $description;
	public $sort_order = 0;
	public $is_active = 1;
	public $created_at;
	public $updated_at;

	public function rules()
	{
		return array(
			array('event_id, name', 'required'),
			array('event_id, sort_order, is_active', 'numerical', 'integerOnly' => true),
			array('name', 'length', 'max' => 100),
			array('slug', 'length', 'max' => 100),
			array('icon', 'length', 'max' => 50),
			array('color', 'length', 'max' => 20),
			array('id, description, created_at, updated_at', 'safe'),
		);
	}

	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'event_id' => 'Sự kiện',
			'name' => 'Tên danh mục',
			'slug' => 'Đường dẫn (slug)',
			'icon' => 'Biểu tượng',
			'color' => 'Màu sắc',
			'description' => 'Mô tả',
			'sort_order' => 'Thứ tự',
			'is_active' => 'Kích hoạt',
			'created_at' => 'Ngày tạo',
			'updated_at' => 'Ngày cập nhật',
		);
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
		if (!empty($result['success']) && isset($result['data'])) {
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
		$data = array_filter($this->getAttributes(), function ($value) {
			return $value !== null && $value !== '';
		});
		return ApiClient::post(ApiEndpoints::NEWS_CATEGORY_STORE, $data);
	}

	public function updateViaApi()
	{
		$data = array_filter($this->getAttributes(), function ($value) {
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
		if ($eventId !== null && $eventId !== '') {
			$params['event_id'] = $eventId;
		}
		$provider = self::getApiDataProvider($params);
		$list = array();
		foreach ($provider->getData() as $item) {
			$id = is_object($item) ? $item->id : $item['id'];
			$name = is_object($item) ? $item->name : $item['name'];
			$list[$id] = $name;
		}
		return $list;
	}
}
