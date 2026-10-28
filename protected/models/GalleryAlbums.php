<?php

/**
 * Model album ảnh (thư viện đại hội). FE không có DB trực tiếp — mọi dữ liệu lấy qua
 * External API theo chuẩn MVC của dự án. Dùng CFormModel làm carrier.
 */
class GalleryAlbums extends CFormModel
{
    public $id;
    public $event_id;
    public $name;
    public $slug;
    public $photo_count;
    public $cover_path;
    public $sort_order;

    /** Danh sách album công khai (mảng assoc). Lọc theo sự kiện nếu truyền event_id. */
    public static function listPublic($eventId = null)
    {
        $params = array();
        if ($eventId !== null && $eventId !== '') {
            $params['event_id'] = $eventId;
        }
        $result = ApiClient::get(ApiEndpoints::GALLERY_ALBUM_LIST, $params);
        if ($result['success'] && isset($result['data']['data'])) {
            return $result['data']['data'];
        }
        return array();
    }

    /** Tìm 1 album theo slug trong danh sách công khai (API chưa có endpoint theo slug). */
    public static function findBySlug($slug, $eventId = null)
    {
        foreach (self::listPublic($eventId) as $album) {
            if (isset($album['slug']) && $album['slug'] === $slug) {
                return $album;
            }
        }
        return null;
    }

    /**
     * Dựng URL ảnh đầy đủ từ đường dẫn tương đối dưới storage Laravel.
     * Encode từng segment để chịu được tên thư mục tiếng Việt có dấu cách.
     */
    public static function mediaUrl($relPath)
    {
        if (empty($relPath)) {
            return '';
        }
        $segments = array_map('rawurlencode', explode('/', str_replace('\\', '/', $relPath)));
        return rtrim(Yii::app()->params['externalApiUrl'], '/') . '/storage/' . implode('/', $segments);
    }
}
