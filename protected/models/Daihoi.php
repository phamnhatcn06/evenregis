<?php

/**
 * Daihoi - Model tổng hợp dữ liệu cho trang Website công khai của Đại hội.
 *
 * Đây KHÔNG phải model gắn với bảng DB. Model chịu trách nhiệm gọi toàn bộ
 * các endpoint /api/daihoi/* (public) qua ApiClient và trả về mảng dữ liệu
 * đã được chuẩn hoá (luôn "bóc" lớp bao data để controller/view dùng trực tiếp).
 */
class Daihoi
{
    /**
     * Bóc lớp bao của response API.
     * Hỗ trợ cả 2 dạng: {success, data: {...}} và {data: {data: [...]}}.
     *
     * @param array $result Kết quả trả về từ ApiClient
     * @param mixed $default Giá trị mặc định khi lỗi/không có dữ liệu
     * @return mixed
     */
    private static function unwrap($result, $default = array())
    {
        if (!is_array($result) || empty($result['success']) || !isset($result['data'])) {
            return $default;
        }

        $data = $result['data'];

        // {success, data: <payload>}
        if (is_array($data) && array_key_exists('data', $data)) {
            $data = $data['data'];
        }

        return $data === null ? $default : $data;
    }

    /**
     * Thông tin sự kiện Đại hội (tên, slogan, địa điểm, thời gian...).
     * @return array
     */
    public static function getEvent()
    {
        return self::unwrap(ApiClient::get(ApiEndpoints::DAIHOI_EVENT), array());
    }

    /**
     * Mốc đếm ngược khai mạc (thường trả về start_time / target timestamp).
     * @return array
     */
    public static function getCountdown()
    {
        return self::unwrap(ApiClient::get(ApiEndpoints::DAIHOI_COUNTDOWN), array());
    }

    /**
     * Số liệu thống kê nổi bật (số ngày, nội dung, môn thể thao...).
     * @return array
     */
    public static function getStats()
    {
        return self::unwrap(ApiClient::get(ApiEndpoints::DAIHOI_STATS), array());
    }

    /**
     * Danh sách nội dung Đại hội (Pro Skills, Sports, Miss, Gala, Race...).
     * @return array
     */
    public static function getContents()
    {
        return self::unwrap(ApiClient::get(ApiEndpoints::DAIHOI_CONTENTS), array());
    }

    /**
     * Lịch trình tổng quan theo ngày.
     * @return array
     */
    public static function getAgenda()
    {
        return self::unwrap(ApiClient::get(ApiEndpoints::DAIHOI_AGENDA), array());
    }

    /**
     * Các trận đấu đang diễn ra (LIVE).
     * @return array
     */
    public static function getLiveMatches()
    {
        return self::unwrap(ApiClient::get(ApiEndpoints::DAIHOI_MATCHES_LIVE), array());
    }

    /**
     * Các trận đấu vừa kết thúc / sắp diễn ra.
     * @return array
     */
    public static function getRecentMatches()
    {
        return self::unwrap(ApiClient::get(ApiEndpoints::DAIHOI_MATCHES_RECENT), array());
    }

    /**
     * Bảng xếp hạng tạm thời.
     * @param int $limit
     * @return array
     */
    public static function getRankings($limit = 10)
    {
        return self::unwrap(
            ApiClient::get(ApiEndpoints::DAIHOI_RANKINGS, array('limit' => $limit)),
            array()
        );
    }

    /**
     * Danh sách tin tức Đại hội.
     * @param int $limit
     * @return array
     */
    public static function getNews($limit = 10)
    {
        return self::unwrap(
            ApiClient::get(ApiEndpoints::DAIHOI_NEWS, array('limit' => $limit)),
            array()
        );
    }

    /**
     * Chi tiết một bài tin theo slug.
     * @param string $slug
     * @return array
     */
    public static function getNewsDetail($slug)
    {
        $url = ApiEndpoints::url(ApiEndpoints::DAIHOI_NEWS_DETAIL, array('slug' => $slug));
        return self::unwrap(ApiClient::get($url), array());
    }

    /**
     * Danh mục tin tức.
     * @return array
     */
    public static function getNewsCategories()
    {
        return self::unwrap(ApiClient::get(ApiEndpoints::DAIHOI_NEWS_CATEGORIES), array());
    }
}
