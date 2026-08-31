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
     * Thông tin sự kiện Đại hội - query thẳng từ /api/events, lấy sự kiện mới nhất.
     * @return array
     */
    public static function getEvent()
    {
        $list = self::unwrap(
            ApiClient::get(ApiEndpoints::EVENT_LIST, array('per_page' => 1, 'page' => 1)),
            array()
        );
        // Danh sách phân trang -> lấy phần tử đầu
        if (isset($list[0]) && is_array($list[0])) {
            return $list[0];
        }
        return is_array($list) ? $list : array();
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
     * Danh sách tin tức - query thẳng từ /api/admin/news (list news).
     * @param int $limit
     * @return array
     */
    public static function getNews($limit = 10)
    {
        return self::unwrap(
            ApiClient::get(ApiEndpoints::NEWS_LIST, array('per_page' => $limit, 'page' => 1)),
            array()
        );
    }

    /**
     * Chi tiết một bài tin theo id.
     * @param int $id
     * @return array
     */
    public static function getNewsDetail($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::NEWS_DETAIL, array('id' => $id));
        return self::unwrap(ApiClient::get($url), array());
    }
}
