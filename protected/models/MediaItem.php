<?php

/**
 * MediaItem — Một ảnh/video thuộc một album trong Thư viện Đại hội.
 *
 * Bảng `media_items` được quản lý qua External API (không có trong DB local),
 * nên model này là CFormModel giữ attribute + gọi ApiClient theo ApiEndpoints.
 * Cấu trúc bám theo docs/migrations/2026_10_06_slideshows_media.sql.
 *
 * Hợp đồng API:
 *   - GET    /api/admin/media-items?album_id={id}
 *   - POST   /api/admin/media-items
 *   - GET    /api/admin/media-items/{id}
 *   - PUT    /api/admin/media-items/{id}
 *   - DELETE /api/admin/media-items/{id}
 */
class MediaItem extends CFormModel
{
    const IS_ACTIVE = 1;
    const IS_INACTIVE = 0;

    public $id;
    public $album_id;
    public $event_id;
    public $type = 'image';
    public $url;
    public $thumbnail;
    public $title;
    public $caption;
    public $sort_order = 0;
    public $is_active = 1;
    public $created_at;
    public $updated_at;
    public $deleted_at;

    public function rules()
    {
        return array(
            array('album_id, event_id, url', 'required'),
            array('album_id, event_id, sort_order, is_active', 'numerical', 'integerOnly' => true),
            array('url, thumbnail, title', 'length', 'max' => 500),
            array('type', 'in', 'range' => array('image', 'video')),
            array('id, caption, created_at, updated_at, deleted_at', 'safe'),
        );
    }

    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'album_id' => 'Album',
            'event_id' => 'Sự kiện',
            'type' => 'Loại',
            'url' => 'Đường dẫn',
            'thumbnail' => 'Ảnh thu nhỏ',
            'title' => 'Tiêu đề',
            'caption' => 'Chú thích',
            'sort_order' => 'Thứ tự',
            'is_active' => 'Hiển thị',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
        );
    }

    public static function fetchFromApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::MEDIA_ITEM_DETAIL, array('id' => $id));
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
        return ApiClient::post(ApiEndpoints::MEDIA_ITEM_STORE, $data);
    }

    public function updateViaApi()
    {
        $data = array_filter($this->getAttributes(), function ($value) {
            return $value !== null && $value !== '';
        });
        $url = ApiEndpoints::url(ApiEndpoints::MEDIA_ITEM_UPDATE, array('id' => $this->id));
        return ApiClient::put($url, $data);
    }

    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::MEDIA_ITEM_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    public static function getApiDataProvider($params = array(), $pageSize = 50)
    {
        return new ApiDataProvider(ApiEndpoints::MEDIA_ITEM_LIST, array(
            'modelClass' => 'MediaItem',
            'params' => $params,
            'pagination' => array(
                'pageSize' => $pageSize,
            ),
        ));
    }
}
