<?php

/**
 * Model ảnh trong album. Lấy qua External API, có phân trang cho lazy-load / infinite scroll.
 */
class GalleryPhotos extends CFormModel
{
    public $id;
    public $album_id;
    public $file_name;
    public $thumb_path;
    public $file_path;
    public $original_path;
    public $width;
    public $height;
    public $claim_count;

    /**
     * Ảnh trong 1 album, phân trang.
     * @return array ['items' => [...], 'page' => int, 'last_page' => int, 'total' => int]
     */
    public static function listByAlbum($albumId, $page = 1, $limit = 24)
    {
        $url = ApiEndpoints::url(ApiEndpoints::GALLERY_ALBUM_PHOTOS, array('id' => $albumId));
        $result = ApiClient::get($url, array('page' => (int) $page, 'limit' => (int) $limit));

        if (!$result['success'] || !isset($result['data']['data'])) {
            return array('items' => array(), 'page' => 1, 'last_page' => 1, 'total' => 0);
        }

        $pagination = isset($result['data']['pagination']) ? $result['data']['pagination'] : array();
        return array(
            'items'     => $result['data']['data'],
            'page'      => isset($pagination['current_page']) ? (int) $pagination['current_page'] : 1,
            'last_page' => isset($pagination['last_page']) ? (int) $pagination['last_page'] : 1,
            'total'     => isset($pagination['total']) ? (int) $pagination['total'] : count($result['data']['data']),
        );
    }
}
