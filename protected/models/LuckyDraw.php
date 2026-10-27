<?php

/**
 * Model Quay số may mắn. Bọc toàn bộ tương tác với API Lucky (Laravel).
 * Controller chỉ gọi các static method ở đây, không gọi ApiClient trực tiếp.
 *
 * API Laravel trả {code,message,data}; các method dưới trả thẳng phần `data`
 * (hoặc null nếu lỗi), kèm method trả nguyên result khi cần đọc message lỗi.
 */
class LuckyDraw
{
    /** Bóc phần payload `data` từ kết quả ApiClient. */
    protected static function payload($result)
    {
        if (!$result || empty($result['success']) || !isset($result['data'])) {
            return null;
        }
        $body = $result['data'];
        return isset($body['data']) ? $body['data'] : $body;
    }

    /** Lấy message lỗi từ kết quả ApiClient. */
    public static function errorMessage($result, $default = 'Có lỗi xảy ra')
    {
        if (isset($result['data']['message'])) {
            return $result['data']['message'];
        }
        if (!empty($result['error'])) {
            return $result['error'];
        }
        return $default;
    }

    // ---------- Prizes ----------

    public static function prizes()
    {
        return self::payload(ApiClient::get(ApiEndpoints::LUCKY_PRIZES)) ?: array();
    }

    public static function savePrize($data)
    {
        return ApiClient::post(ApiEndpoints::LUCKY_PRIZE_SAVE, $data);
    }

    public static function deletePrize($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::LUCKY_PRIZE_DELETE, array('id' => $id));
        return ApiClient::delete($url);
    }

    // ---------- Settings ----------

    /** Trả array ['settings' => [...], 'types' => [...]]. */
    public static function settings()
    {
        return self::payload(ApiClient::get(ApiEndpoints::LUCKY_SETTINGS)) ?: array('settings' => array(), 'types' => array());
    }

    public static function saveSettings($data)
    {
        return ApiClient::post(ApiEndpoints::LUCKY_SETTINGS_SAVE, $data);
    }

    // ---------- Show / spin ----------

    public static function currentPrize()
    {
        return self::payload(ApiClient::get(ApiEndpoints::LUCKY_CURRENT_PRIZE));
    }

    public static function status()
    {
        return self::payload(ApiClient::get(ApiEndpoints::LUCKY_STATUS));
    }

    public static function confirmedWinners($prizeId)
    {
        return self::payload(ApiClient::get(ApiEndpoints::LUCKY_WINNERS, array('prize_id' => $prizeId))) ?: array();
    }

    public static function spin()
    {
        return ApiClient::post(ApiEndpoints::LUCKY_SPIN, array());
    }

    public static function confirmWinner($id, $prizeId)
    {
        return ApiClient::post(ApiEndpoints::LUCKY_CONFIRM, array('id' => $id, 'prize_id' => $prizeId));
    }

    public static function cancelWinner($id, $prizeId)
    {
        return ApiClient::post(ApiEndpoints::LUCKY_CANCEL, array('id' => $id, 'prize_id' => $prizeId));
    }

    public static function nextPrize()
    {
        return ApiClient::post(ApiEndpoints::LUCKY_NEXT, array());
    }

    public static function resetWinners()
    {
        return ApiClient::post(ApiEndpoints::LUCKY_RESET, array());
    }

    // ---------- Auth / remote ----------

    public static function checkAuth($code)
    {
        return ApiClient::post(ApiEndpoints::LUCKY_CHECK_AUTH, array('code' => $code));
    }

    public static function checkRemote()
    {
        return self::payload(ApiClient::get(ApiEndpoints::LUCKY_CHECK_REMOTE));
    }

    public static function remoteSpin()
    {
        return ApiClient::post(ApiEndpoints::LUCKY_REMOTE_SPIN, array());
    }

    // ---------- Người trúng giải nhập thủ công (import Excel) ----------

    /** Danh sách người trúng của 1 sự kiện (cho màn admin). */
    public static function manualWinnersList($eventId)
    {
        return self::payload(ApiClient::get(ApiEndpoints::LUCKY_MANUAL_WINNER_LIST, array('event_id' => $eventId))) ?: array();
    }

    /** Danh sách công bố cho trang public; eventId null = sự kiện mới nhất có người trúng. */
    public static function publicManualWinners($eventId = null)
    {
        $params = $eventId ? array('event_id' => $eventId) : array();
        return self::payload(ApiClient::get(ApiEndpoints::LUCKY_MANUAL_WINNER_PUBLIC, $params)) ?: array('event_id' => 0, 'winners' => array());
    }

    /** Import cộng dồn; trả nguyên result để đọc message/warnings. */
    public static function importManualWinners($eventId, $rows, $createdBy)
    {
        return ApiClient::post(ApiEndpoints::LUCKY_MANUAL_WINNER_IMPORT, array(
            'event_id'   => $eventId,
            'rows'       => $rows,
            'created_by' => $createdBy,
        ));
    }

    public static function deleteManualWinner($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::LUCKY_MANUAL_WINNER_DELETE, array('id' => $id));
        return ApiClient::delete($url);
    }

    public static function clearManualWinners($eventId)
    {
        return ApiClient::post(ApiEndpoints::LUCKY_MANUAL_WINNER_CLEAR, array('event_id' => $eventId));
    }
}
