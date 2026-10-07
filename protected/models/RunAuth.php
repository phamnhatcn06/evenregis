<?php

/**
 * Xác thực cổng chạy (định danh MT + lucky + PIN). Carrier gọi External API.
 * Mỗi hàm trả mảng chuẩn ApiClient (success/code/data/error) để controller xử lý.
 */
class RunAuth extends CFormModel
{
    /** Danh sách đơn vị VCK cho dropdown đăng nhập (mỗi phần tử: id, code, name). */
    public static function listUnits()
    {
        $result = ApiClient::get(ApiEndpoints::RUN_AUTH_LIST_UNITS);
        if ($result['success'] && isset($result['data']['data']) && is_array($result['data']['data'])) {
            return $result['data']['data'];
        }
        return array();
    }

    public static function identify($identifier, $propertyId = null)
    {
        return ApiClient::post(ApiEndpoints::RUN_AUTH_IDENTIFY, array(
            'identifier'  => $identifier,
            'property_id' => $propertyId,
        ));
    }

    public static function identifyByQr($qrValue)
    {
        return ApiClient::post(ApiEndpoints::RUN_AUTH_IDENTIFY_BY_QR, array('qr_value' => $qrValue));
    }

    public static function setPin($identifier, $pin, $propertyId = null)
    {
        return ApiClient::post(ApiEndpoints::RUN_AUTH_SET_PIN, array(
            'identifier'  => $identifier,
            'pin'         => $pin,
            'property_id' => $propertyId,
        ));
    }

    public static function login($identifier, $pin, $propertyId = null)
    {
        return ApiClient::post(ApiEndpoints::RUN_AUTH_LOGIN, array(
            'identifier'  => $identifier,
            'pin'         => $pin,
            'property_id' => $propertyId,
        ));
    }

    public static function genLucky($eventId)
    {
        return ApiClient::post(ApiEndpoints::RUN_AUTH_GEN_LUCKY, array('event_id' => $eventId));
    }

    /**
     * Hồ sơ người đang đăng nhập (từ final_attendee_rosters), hoặc null:
     * full_name, position, unit_name, birth_year, gender (0=Nữ, 1=Nam), is_complete.
     */
    public static function getProfile($attendeeId)
    {
        $result = ApiClient::get(ApiEndpoints::RUN_AUTH_PROFILE, array('attendee_id' => $attendeeId));
        if ($result['success'] && isset($result['data']['data'])) {
            return $result['data']['data'];
        }
        return null;
    }

    /** Lưu năm sinh + giới tính (0=Nữ, 1=Nam). Trả mảng chuẩn ApiClient. */
    public static function saveProfile($attendeeId, $birthYear, $gender, $authEmail = null)
    {
        return ApiClient::post(ApiEndpoints::RUN_AUTH_SAVE_PROFILE, array(
            'attendee_id' => $attendeeId,
            'birth_year'  => $birthYear,
            'gender'      => $gender,
            'auth_email'  => $authEmail,
        ));
    }
}
