<?php

/**
 * BeautyVotes - Model cổng bình chọn Miss (public), gọi External API.
 * Việc xác thực người bình chọn (Google Sign-In) do controller đảm nhận; model
 * chỉ chuyển voter_key (Google sub) xuống backend để áp hạn mức.
 */
class BeautyVotes
{
    /**
     * Dữ liệu trang bình chọn: trạng thái cổng + danh sách thí sinh kèm số phiếu.
     * @param int $contestId
     * @return array ['gate' => array, 'finalists' => array]
     */
    public static function getVotePageData($contestId)
    {
        $url = ApiEndpoints::url(ApiEndpoints::BEAUTY_VOTE_FINALISTS, array('id' => $contestId));
        $result = ApiClient::get($url);
        if ($result['success'] && isset($result['data'])) {
            $data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
            if (is_array($data)) {
                return array(
                    'gate' => isset($data['gate']) ? $data['gate'] : array('is_open' => true, 'message' => ''),
                    'finalists' => isset($data['finalists']) ? $data['finalists'] : array(),
                );
            }
        }
        return array('gate' => array('is_open' => true, 'message' => ''), 'finalists' => array());
    }

    /**
     * Bảng xếp hạng số phiếu (trang quản trị).
     * @param int $contestId
     * @return array ['total_votes' => int, 'voter_count' => int, 'ranking' => array]
     */
    public static function getRanking($contestId)
    {
        $url = ApiEndpoints::url(ApiEndpoints::BEAUTY_VOTE_RANKING, array('id' => $contestId));
        $result = ApiClient::get($url);
        if ($result['success'] && isset($result['data'])) {
            $data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
            if (is_array($data)) {
                return array(
                    'total_votes' => isset($data['total_votes']) ? (int) $data['total_votes'] : 0,
                    'voter_count' => isset($data['voter_count']) ? (int) $data['voter_count'] : 0,
                    'ranking' => isset($data['ranking']) ? $data['ranking'] : array(),
                );
            }
        }
        return array('total_votes' => 0, 'voter_count' => 0, 'ranking' => array());
    }

    /**
     * Lấy cấu hình khung giờ bình chọn.
     * @param int $contestId
     * @return array
     */
    public static function getSettings($contestId)
    {
        $url = ApiEndpoints::url(ApiEndpoints::BEAUTY_VOTE_SETTINGS, array('id' => $contestId));
        $result = ApiClient::get($url);
        if ($result['success'] && isset($result['data'])) {
            $data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
            return is_array($data) ? $data : array();
        }
        return array();
    }

    /**
     * Cập nhật khung giờ bình chọn.
     * @param int $contestId
     * @param string|null $startAt
     * @param string|null $endAt
     * @return array raw response
     */
    public static function updateSettings($contestId, $startAt, $endAt)
    {
        return ApiClient::post(ApiEndpoints::BEAUTY_VOTE_SETTINGS_UPDATE, array(
            'contest_id' => $contestId,
            'vote_start_at' => $startAt,
            'vote_end_at' => $endAt,
        ));
    }

    /**
     * Xoá toàn bộ phiếu bình chọn của cuộc thi, trả lại lượt cho mọi người bình chọn.
     * @param int $contestId
     * @return array raw response
     */
    public static function resetVotes($contestId)
    {
        return ApiClient::post(ApiEndpoints::BEAUTY_VOTE_RESET, array(
            'contest_id' => $contestId,
        ));
    }

    /**
     * Ghi nhận một lượt bình chọn.
     * @param array $data contest_id, contestant_id, voter_key, voter_key_type,
     *                    voter_email, voter_name, max_votes
     * @return array raw response từ API (success, message, vote_count, remaining, voted_ids)
     */
    public static function castVote($data)
    {
        $result = ApiClient::post(ApiEndpoints::BEAUTY_VOTE_CAST, $data);
        return isset($result['data']) && is_array($result['data']) ? $result['data'] : array(
            'success' => false,
            'message' => isset($result['error']) ? $result['error'] : 'Lỗi kết nối máy chủ.',
        );
    }

    /**
     * Trạng thái bình chọn của một người (đã chọn ai, còn bao nhiêu lượt).
     * @param int $contestId
     * @param string $voterKey
     * @param int $maxVotes
     * @return array ['voted_ids' => array, 'remaining' => int]
     */
    public static function getVoterStatus($contestId, $voterKey, $maxVotes)
    {
        $result = ApiClient::get(ApiEndpoints::BEAUTY_VOTE_STATUS, array(
            'contest_id' => $contestId,
            'voter_key' => $voterKey,
            'max_votes' => $maxVotes,
        ));
        if ($result['success'] && isset($result['data'])) {
            $data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
            return array(
                'voted_ids' => isset($data['voted_ids']) ? $data['voted_ids'] : array(),
                'remaining' => isset($data['remaining']) ? (int) $data['remaining'] : $maxVotes,
            );
        }
        return array('voted_ids' => array(), 'remaining' => $maxVotes);
    }
}
