<?php

/**
 * Xác thực cổng chạy (định danh MT + lucky + PIN). Carrier gọi External API.
 * Mỗi hàm trả mảng chuẩn ApiClient (success/code/data/error) để controller xử lý.
 */
class RunAuth extends CFormModel
{
    public static function identify($identifier)
    {
        return ApiClient::post(ApiEndpoints::RUN_AUTH_IDENTIFY, array('identifier' => $identifier));
    }

    public static function identifyByQr($qrValue)
    {
        return ApiClient::post(ApiEndpoints::RUN_AUTH_IDENTIFY_BY_QR, array('qr_value' => $qrValue));
    }

    public static function setPin($identifier, $pin)
    {
        return ApiClient::post(ApiEndpoints::RUN_AUTH_SET_PIN, array('identifier' => $identifier, 'pin' => $pin));
    }

    public static function login($identifier, $pin)
    {
        return ApiClient::post(ApiEndpoints::RUN_AUTH_LOGIN, array('identifier' => $identifier, 'pin' => $pin));
    }

    public static function genLucky($eventId)
    {
        return ApiClient::post(ApiEndpoints::RUN_AUTH_GEN_LUCKY, array('event_id' => $eventId));
    }

    /** Ngày sinh + giới tính (từ final_attendee_rosters) của người đang đăng nhập, hoặc null. */
    public static function getProfile($attendeeId)
    {
        $result = ApiClient::get(ApiEndpoints::RUN_AUTH_PROFILE, array('attendee_id' => $attendeeId));
        if ($result['success'] && isset($result['data']['data'])) {
            return $result['data']['data'];
        }
        return null;
    }

    /** Lưu ngày sinh (Y-m-d) + giới tính (0=Nữ, 1=Nam). Trả mảng chuẩn ApiClient. */
    public static function saveProfile($attendeeId, $birthday, $gender, $authEmail = null)
    {
        return ApiClient::post(ApiEndpoints::RUN_AUTH_SAVE_PROFILE, array(
            'attendee_id' => $attendeeId,
            'birthday'    => $birthday,
            'gender'      => $gender,
            'auth_email'  => $authEmail,
        ));
    }
}
