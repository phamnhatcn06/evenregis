<?php

/**
 * MediaAlbum — Album ảnh/video trong Thư viện Đại hội.
 *
 * Bảng `media_albums` được quản lý qua External API (không có trong DB local),
 * nên model này là CFormModel giữ attribute + gọi ApiClient theo ApiEndpoints.
 * Cấu trúc bám theo docs/migrations/2026_10_06_slideshows_media.sql.
 *
 * Hợp đồng API:
 *   - GET    /api/admin/media-albums
 *   - POST   /api/admin/media-albums
 *   - GET    /api/admin/media-albums/{id}
 *   - PUT    /api/admin/media-albums/{id}
 *   - DELETE /api/admin/media-albums/{id}
 *   - GET    /api/daihoi/albums               (public - album đang hiển thị)
 *   - GET    /api/daihoi/albums/{id}/items    (public - item trong album)
 */
class MediaAlbum extends CFormModel
{
    const IS_ACTIVE = 1;
    const IS_INACTIVE = 0;

    public $id;
    public $event_id;
    public $title;
    public $slug;
    public $description;
    public $cover_image;
    public $badge;
    public $type = 'photo';
    public $item_count = 0;
    public $sort_order = 0;
    public $is_active = 1;
    public $created_at;
    public $updated_at;
    public $deleted_at;

    public function rules()
    {
        return array(
            array('event_id, title', 'required'),
            array('event_id, item_count, sort_order, is_active', 'numerical', 'integerOnly' => true),
            array('title, slug, cover_image', 'length', 'max' => 500),
            array('badge', 'length', 'max' => 50),
            array('type', 'in', 'range' => array('photo', 'video', 'mixed')),
            array('id, description, created_at, updated_at, deleted_at', 'safe'),
        );
    }

    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'event_id' => 'Sự kiện',
            'title' => 'Tên album',
            'slug' => 'Đường dẫn (slug)',
            'description' => 'Mô tả',
            'cover_image' => 'Ảnh bìa',
            'badge' => 'Nhãn góc',
            'type' => 'Loại',
            'item_count' => 'Số lượng',
            'sort_order' => 'Thứ tự',
            'is_active' => 'Hiển thị',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
        );
    }

    public static function getTypeOptions()
    {
        return array(
            'photo' => 'Ảnh',
            'video' => 'Video',
            'mixed' => 'Ảnh &amp; Video',
        );
    }

    /** Danh sách album active dạng [id => title] cho dropdown. */
    public static function getActiveList($eventId = null)
    {
        $params = array('is_active' => 1);
        if ($eventId !== null && $eventId !== '') {
            $params['event_id'] = $eventId;
        }
        $provider = self::getApiDataProvider($params, 100);
        $list = array();
        foreach ($provider->getData() as $item) {
            $id = is_object($item) ? $item->id : $item['id'];
            $title = is_object($item) ? $item->title : $item['title'];
            $list[$id] = $title;
        }
        return $list;
    }

    public static function fetchFromApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::MEDIA_ALBUM_DETAIL, array('id' => $id));
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
        return ApiClient::post(ApiEndpoints::MEDIA_ALBUM_STORE, $data);
    }

    public function updateViaApi()
    {
        $data = array_filter($this->getAttributes(), function ($value) {
            return $value !== null && $value !== '';
        });
        $url = ApiEndpoints::url(ApiEndpoints::MEDIA_ALBUM_UPDATE, array('id' => $this->id));
        return ApiClient::put($url, $data);
    }

    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::MEDIA_ALBUM_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    public static function getApiDataProvider($params = array(), $pageSize = 25)
    {
        return new ApiDataProvider(ApiEndpoints::MEDIA_ALBUM_LIST, array(
            'modelClass' => 'MediaAlbum',
            'params' => $params,
            'pagination' => array(
                'pageSize' => $pageSize,
            ),
        ));
    }
}
