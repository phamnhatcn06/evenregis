<?php

/**
 * Slideshow — Slide hero của trang chủ Website Đại hội.
 *
 * Bảng `slideshows` được quản lý qua External API (không có trong DB local),
 * nên model này là CFormModel giữ attribute + gọi ApiClient theo ApiEndpoints.
 * Cấu trúc bám theo docs/migrations/2026_10_06_slideshows_media.sql.
 *
 * Hợp đồng API:
 *   - GET    /api/admin/slideshows
 *   - POST   /api/admin/slideshows
 *   - GET    /api/admin/slideshows/{id}
 *   - PUT    /api/admin/slideshows/{id}
 *   - DELETE /api/admin/slideshows/{id}
 *   - POST   /api/admin/slideshows/reorder
 *   - GET    /api/daihoi/slides           (public - slide đang hiển thị)
 */
class Slideshow extends CFormModel
{
    const IS_ACTIVE = 1;
    const IS_INACTIVE = 0;

    public $id;
    public $event_id;
    public $title;
    public $subtitle;
    public $description;
    public $image;
    public $mobile_image;
    public $button_text;
    public $button_url;
    public $theme = 'blue';
    public $sort_order = 0;
    public $is_active = 1;
    public $start_at;
    public $end_at;
    public $created_at;
    public $updated_at;
    public $deleted_at;

    public function rules()
    {
        return array(
            array('event_id', 'required'),
            array('event_id, sort_order, is_active', 'numerical', 'integerOnly' => true),
            array('title, image, mobile_image, button_url', 'length', 'max' => 500),
            array('subtitle', 'length', 'max' => 500),
            array('button_text', 'length', 'max' => 100),
            array('theme', 'length', 'max' => 30),
            array('id, description, start_at, end_at, created_at, updated_at, deleted_at', 'safe'),
        );
    }

    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'event_id' => 'Sự kiện',
            'title' => 'Tiêu đề',
            'subtitle' => 'Dòng nhấn',
            'description' => 'Mô tả',
            'image' => 'Ảnh nền',
            'mobile_image' => 'Ảnh nền (mobile)',
            'button_text' => 'Nhãn nút',
            'button_url' => 'Link nút',
            'theme' => 'Màu nhấn',
            'sort_order' => 'Thứ tự',
            'is_active' => 'Hiển thị',
            'start_at' => 'Bắt đầu hiển thị',
            'end_at' => 'Kết thúc hiển thị',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
        );
    }

    /** Các màu nhấn gradient được hỗ trợ (map sang class Tailwind ở view). */
    public static function getThemeOptions()
    {
        return array(
            'blue' => 'Xanh dương',
            'amber' => 'Vàng cam',
            'purple' => 'Tím',
            'teal' => 'Xanh ngọc',
        );
    }

    public static function fetchFromApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::SLIDESHOW_DETAIL, array('id' => $id));
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
        return ApiClient::post(ApiEndpoints::SLIDESHOW_STORE, $data);
    }

    public function updateViaApi()
    {
        $data = array_filter($this->getAttributes(), function ($value) {
            return $value !== null && $value !== '';
        });
        $url = ApiEndpoints::url(ApiEndpoints::SLIDESHOW_UPDATE, array('id' => $this->id));
        return ApiClient::put($url, $data);
    }

    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::SLIDESHOW_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    public static function getApiDataProvider($params = array(), $pageSize = 25)
    {
        return new ApiDataProvider(ApiEndpoints::SLIDESHOW_LIST, array(
            'modelClass' => 'Slideshow',
            'params' => $params,
            'pagination' => array(
                'pageSize' => $pageSize,
            ),
        ));
    }
}
