<?php

/**
 * Model đăng ký chạy + xác thực cổng chạy. FE không có DB — carrier gọi External API.
 */
class RunRegistrations extends CFormModel
{
    public $id;
    public $run_event_id;
    public $run_event_name;
    public $attendee_id;
    public $bib_number;
    public $age_group_label;
    public $registered_at;
    public $full_name;
    public $unit_label;
    public $phone_number;
    public $lucky_number;
    public $emergency_contact_name;
    public $emergency_contact_phone;
    public $medical_conditions;
    public $medications;

    public function rules()
    {
        return array(
            array('id, run_event_id, run_event_name, attendee_id, bib_number, age_group_label, registered_at, full_name, unit_label, phone_number, lucky_number, emergency_contact_name, emergency_contact_phone, medical_conditions, medications', 'safe'),
        );
    }

    /**
     * Chiếm slot. Trả về mảng ApiClient chuẩn (success/code/data/error).
     * code=200 thành công; 409 hết chỗ/đã đăng ký; 422 đóng cổng/ngoài giờ.
     */
    public static function claimViaApi($runEventId, $attendeeId, $ageGroupLabel = null)
    {
        return ApiClient::post(ApiEndpoints::RUN_REGISTRATION_CLAIM, array(
            'run_event_id'    => $runEventId,
            'attendee_id'     => $attendeeId,
            'age_group_label' => $ageGroupLabel,
        ));
    }

    /** Phiếu đăng ký của 1 người (hoặc null). */
    public static function findMine($attendeeId)
    {
        $result = ApiClient::get(ApiEndpoints::RUN_REGISTRATION_MINE, array('attendee_id' => $attendeeId));
        if ($result['success'] && isset($result['data']['data']) && !empty($result['data']['data'])) {
            return $result['data']['data'];
        }
        return null;
    }

    /** Bản ghi hủy đã được duyệt gần nhất của 1 người (để báo trên cổng) hoặc null. */
    public static function findLatestCancelled($attendeeId)
    {
        $result = ApiClient::get(ApiEndpoints::RUN_REGISTRATION_MINE, array(
            'attendee_id'      => $attendeeId,
            'latest_cancelled' => 1,
        ));
        if ($result['success'] && isset($result['data']['data']) && !empty($result['data']['data'])) {
            return $result['data']['data'];
        }
        return null;
    }

    /** Người dùng xin hủy đăng ký (kèm lý do). Trả mảng ApiClient chuẩn. */
    public static function requestCancelViaApi($attendeeId, $reason)
    {
        return ApiClient::post(ApiEndpoints::RUN_REGISTRATION_CANCEL_REQUEST, array(
            'attendee_id' => $attendeeId,
            'reason'      => $reason,
        ));
    }

    /** Danh sách đăng ký theo sự kiện (admin). Trả mảng assoc. */
    public static function listByEvent($eventId)
    {
        $result = ApiClient::get(ApiEndpoints::RUN_REGISTRATION_LIST, array('event_id' => $eventId));
        if ($result['success'] && isset($result['data']['data'])) {
            return $result['data']['data'];
        }
        return array();
    }

    /** Danh sách yêu cầu hủy chờ duyệt theo sự kiện (admin). Trả mảng assoc. */
    public static function listCancelRequests($eventId)
    {
        $result = ApiClient::get(ApiEndpoints::RUN_REGISTRATION_CANCEL_REQUESTS, array('event_id' => $eventId));
        if ($result['success'] && isset($result['data']['data'])) {
            return $result['data']['data'];
        }
        return array();
    }

    /** Admin duyệt hủy một đăng ký. Trả mảng ApiClient chuẩn. */
    public static function approveCancelViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::RUN_REGISTRATION_CANCEL_APPROVE, array('id' => $id));
        return ApiClient::post($url, array());
    }

    /** Admin từ chối hủy một đăng ký. Trả mảng ApiClient chuẩn. */
    public static function rejectCancelViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::RUN_REGISTRATION_CANCEL_REJECT, array('id' => $id));
        return ApiClient::post($url, array());
    }
}
