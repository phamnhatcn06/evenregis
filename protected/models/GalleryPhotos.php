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
    public static function listByAlbum($albumId, $page = 1, $limit = 24, $attendeeId = null)
    {
        $url = ApiEndpoints::url(ApiEndpoints::GALLERY_ALBUM_PHOTOS, array('id' => $albumId));
        $params = array('page' => (int) $page, 'limit' => (int) $limit);
        if ($attendeeId) {
            $params['attendee_id'] = (int) $attendeeId;
        }
        $result = ApiClient::get($url, $params);

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

    /**
     * Nhận diện người tham dự từ qr_token (cho luồng "đánh dấu ảnh của tôi").
     * @return array|null ['attendee_id', 'full_name', 'event_id'] hoặc null nếu không hợp lệ.
     */
    public static function resolveAttendee($qrToken)
    {
        $result = ApiClient::post(ApiEndpoints::GALLERY_RESOLVE, array('qr_token' => $qrToken));
        if ($result['success'] && isset($result['data']['data']['attendee_id'])) {
            return $result['data']['data'];
        }
        return null;
    }

    /** Đánh dấu / bỏ đánh dấu ảnh của người tham dự. Trả về mảng ảnh đã cập nhật hoặc null. */
    public static function toggleClaim($photoId, $attendeeId, $claimerName, $claim = true)
    {
        $endpoint = $claim ? ApiEndpoints::GALLERY_CLAIM : ApiEndpoints::GALLERY_UNCLAIM;
        $url = ApiEndpoints::url($endpoint, array('id' => $photoId));
        $data = array('attendee_id' => $attendeeId);
        if ($claim) {
            $data['claimer_name'] = $claimerName;
        }
        $result = ApiClient::post($url, $data);
        if ($result['success'] && isset($result['data']['data'])) {
            return $result['data']['data'];
        }
        return null;
    }
}
