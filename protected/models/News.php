<?php

/**
 * News — Tin tức đại hội.
 *
 * Bảng `news` được quản lý qua External API (không có trong DB local),
 * nên model này là CFormModel giữ attribute + gọi ApiClient theo ApiEndpoints.
 * Thuộc tính bám theo cấu trúc bảng `news` trong docs/mt_registration_portal_struct.sql.
 *
 * Hợp đồng API (xem docs/MTRegistrationPortal API.postman_collection.json — Daihoi Admin):
 *   - GET    /api/admin/news
 *   - POST   /api/admin/news
 *   - GET    /api/admin/news/{id}
 *   - PUT    /api/admin/news/{id}
 *   - DELETE /api/admin/news/{id}
 */
class News extends CFormModel
{
	const IS_PUBLISHED = 1;
	const IS_DRAFT = 0;

	const IS_FEATURED = 1;
	const NOT_FEATURED = 0;

	public $id;
	public $event_id;
	public $title;
	public $slug;
	public $excerpt;
	public $content;
	public $thumbnail;
	public $category_id;
	public $category = 'general';
	public $is_featured = 0;
	public $is_published = 0;
	public $published_at;
	public $view_count;
	public $created_by;
	public $created_at;
	public $updated_at;
	public $deleted_at;

	public function rules()
	{
		return array(
			array('event_id, title', 'required'),
			array('event_id, category_id, is_featured, is_published, view_count', 'numerical', 'integerOnly' => true),
			array('title, slug, thumbnail', 'length', 'max' => 500),
			array('category', 'length', 'max' => 50),
			array('created_by', 'length', 'max' => 100),
			array('id, excerpt, content, published_at, created_at, updated_at, deleted_at', 'safe'),
		);
	}

	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'event_id' => 'Sự kiện',
			'title' => 'Tiêu đề',
			'slug' => 'Đường dẫn (slug)',
			'excerpt' => 'Tóm tắt',
			'content' => 'Nội dung',
			'thumbnail' => 'Ảnh đại diện',
			'category_id' => 'Danh mục',
			'category' => 'Loại tin',
			'is_featured' => 'Tin nổi bật',
			'is_published' => 'Xuất bản',
			'published_at' => 'Ngày xuất bản',
			'view_count' => 'Lượt xem',
			'created_by' => 'Người tạo',
			'created_at' => 'Ngày tạo',
			'updated_at' => 'Ngày cập nhật',
			'deleted_at' => 'Ngày xóa',
		);
	}

	/**
	 * Danh mục loại tin (enum trong cột news.category)
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
		return ApiClient::post(ApiEndpoints::NEWS_STORE, $data);
	}

	public function updateViaApi()
	{
		$data = array_filter($this->getAttributes(), function ($value) {
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
