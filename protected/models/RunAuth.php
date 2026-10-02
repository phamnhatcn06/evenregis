<?php

/**
 * Xác thực cổng chạy (định danh DHMT + lucky + PIN). Carrier gọi External API.
 * Mỗi hàm trả mảng chuẩn ApiClient (success/code/data/error) để controller xử lý.
 */
class RunAuth extends CFormModel
{
    public static function identify($identifier)
    {
        return ApiClient::post(ApiEndpoints::RUN_AUTH_IDENTIFY, array('identifier' => $identifier));
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
}
