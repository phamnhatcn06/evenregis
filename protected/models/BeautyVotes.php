<?php

/**
 * BeautyVotes - Model cổng bình chọn Miss (public), gọi External API.
 * Việc xác thực người bình chọn (Google Sign-In) do controller đảm nhận; model
 * chỉ chuyển voter_key (Google sub) xuống backend để áp hạn mức.
 */
class BeautyVotes
{
    /**
     * Danh sách thí sinh chung kết kèm số phiếu.
     * @param int $contestId
     * @return array
     */
    public static function getFinalistsForVote($contestId)
    {
        $url = ApiEndpoints::url(ApiEndpoints::BEAUTY_VOTE_FINALISTS, array('id' => $contestId));
        $result = ApiClient::get($url);
        if ($result['success'] && isset($result['data'])) {
            $data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
            return is_array($data) ? $data : array();
        }
        return array();
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
