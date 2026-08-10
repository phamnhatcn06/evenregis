<?php

/**
 * AttendeeReplacements — Lịch sử thay thế / huỷ tư cách người tham dự.
 *
 * Bảng attendee_replacements được ghi qua External API (không phải DB local),
 * nên model này là CFormModel giữ attribute + gọi ApiClient theo ApiEndpoints.
 *
 * Hợp đồng API (xem docs/schema-attendee-replacements.sql):
 *   - POST /api/attendee-replacements/store
 *   - GET  /api/attendee-replacements?registration_id=X
 */
class AttendeeReplacements extends CFormModel
{
    const ACTION_REPLACE = 'replace';
    const ACTION_WITHDRAW = 'withdraw';

    public $registration_id;
    public $event_id;
    public $property_id;
    public $action;

    public $old_attendee_id;
    public $old_attendee_name;
    public $old_staff_code;

    public $new_attendee_id;
    public $new_attendee_name;
    public $new_staff_code;

    /** @var array Snapshot nội dung bị ảnh hưởng: sports / competitions / roles */
    public $affected_contents;
    /** @var array Snapshot các đội bị huỷ */
    public $cancelled_teams;

    public $reason;
    public $performed_by;

    public function rules()
    {
        return array(
            array('registration_id, action', 'required'),
            array('registration_id, event_id, property_id, old_attendee_id, new_attendee_id', 'numerical', 'integerOnly' => true),
            array('action', 'in', 'range' => array(self::ACTION_REPLACE, self::ACTION_WITHDRAW)),
            array('old_attendee_name, old_staff_code, new_attendee_name, new_staff_code', 'length', 'max' => 255),
            array('reason, performed_by, affected_contents, cancelled_teams', 'safe'),
        );
    }

    /**
     * Ghi bản ghi lịch sử qua API.
     * @return array Kết quả ApiClient (success/code/data/error)
     */
    public function storeViaApi()
    {
        $payload = array(
            'registration_id' => $this->registration_id,
            'event_id' => $this->event_id,
            'property_id' => $this->property_id,
            'action' => $this->action,
            'old_attendee_id' => $this->old_attendee_id,
            'old_attendee_name' => $this->old_attendee_name,
            'old_staff_code' => $this->old_staff_code,
            'new_attendee_id' => $this->new_attendee_id,
            'new_attendee_name' => $this->new_attendee_name,
            'new_staff_code' => $this->new_staff_code,
            'affected_contents' => $this->affected_contents !== null ? $this->affected_contents : array(),
            'cancelled_teams' => $this->cancelled_teams !== null ? $this->cancelled_teams : array(),
            'reason' => $this->reason,
            'performed_by' => $this->performed_by,
            'created_at' => time(),
        );

        return ApiClient::post(ApiEndpoints::ATTENDEE_REPLACEMENT_STORE, $payload);
    }

    /**
     * Ghi nhanh 1 bản ghi lịch sử. Không throw — chỉ log nếu lỗi để không chặn
     * luồng thay thế/huỷ chính (đây là audit, không phải bước bắt buộc).
     *
     * @param array $attributes map field => value
     * @return bool true nếu API trả success
     */
    public static function record($attributes)
    {
        $model = new self();
        foreach ($attributes as $key => $value) {
            if (property_exists($model, $key)) {
                $model->$key = $value;
            }
        }

        $result = $model->storeViaApi();
        if (empty($result['success'])) {
            $err = isset($result['error']) ? $result['error'] : 'unknown';
            Yii::log(
                'Ghi attendee_replacements thất bại: ' . $err,
                CLogger::LEVEL_ERROR,
                'application.models.AttendeeReplacements'
            );
            return false;
        }
        return true;
    }

    /**
     * Lấy lịch sử thay đổi theo phiếu đăng ký (loại bản ghi đã soft delete ở backend).
     * @param int $registrationId
     * @return array
     */
    public static function getByRegistrationId($registrationId)
    {
        $result = ApiClient::get(ApiEndpoints::ATTENDEE_REPLACEMENT_LIST, array(
            'registration_id' => $registrationId,
            'per_page' => 1000,
        ));
        if (!empty($result['success'])) {
            if (isset($result['data']['data'])) {
                return $result['data']['data'];
            }
            if (isset($result['data']) && is_array($result['data'])) {
                return $result['data'];
            }
        }
        return array();
    }
}
