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
    const ACTION_CANCEL_CONTENT = 'cancel_content';

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
            array('action', 'in', 'range' => array(self::ACTION_REPLACE, self::ACTION_WITHDRAW, self::ACTION_CANCEL_CONTENT)),
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

    /**
     * Lấy giá trị từ bản ghi (mảng hoặc object) theo tên field.
     */
    private static function pick($record, $key, $default = null)
    {
        if (is_array($record)) {
            return isset($record[$key]) ? $record[$key] : $default;
        }
        if (is_object($record)) {
            return isset($record->$key) ? $record->$key : $default;
        }
        return $default;
    }

    /**
     * Chuẩn hoá field JSON (affected_contents / cancelled_teams) về mảng.
     * Backend có thể trả về chuỗi JSON hoặc mảng đã decode.
     */
    private static function toArray($value)
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_object($value)) {
            return json_decode(json_encode($value), true);
        }
        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : array();
        }
        return array();
    }

    /**
     * Lịch sử thay đổi nhân sự của 1 phiếu đăng ký, đã format sẵn để hiển thị
     * trong email/PDF xác nhận. Mỗi phần tử là 1 dòng thay đổi gồm chuỗi mô tả
     * người cũ/người thay + danh sách nội dung ảnh hưởng đã diễn giải.
     *
     * @param int $registrationId
     * @return array Danh sách các thay đổi (mảng rỗng nếu không có)
     */
    public static function getFormattedByRegistrationId($registrationId)
    {
        $records = self::getByRegistrationId($registrationId);
        $rows = array();

        foreach ($records as $rec) {
            // Bỏ qua bản ghi đã soft delete ở backend
            if (self::pick($rec, 'deleted_at')) {
                continue;
            }

            $action = self::pick($rec, 'action');
            $affected = self::toArray(self::pick($rec, 'affected_contents'));
            $cancelledTeams = self::toArray(self::pick($rec, 'cancelled_teams'));

            // Diễn giải nội dung ảnh hưởng thành các cụm ngắn gọn
            $contentLines = array();

            $sports = isset($affected['sports']) && is_array($affected['sports']) ? $affected['sports'] : array();
            foreach ($sports as $s) {
                $label = self::pick($s, 'sport_name');
                if (!$label) { $label = self::pick($s, 'team_name', 'Đội thể thao'); }
                $extra = ($action === self::ACTION_REPLACE) ? ' (người thay kế thừa)' : '';
                $contentLines[] = 'Thể thao: ' . $label . $extra;
            }
            foreach ($cancelledTeams as $s) {
                $label = self::pick($s, 'sport_name');
                if (!$label) { $label = self::pick($s, 'team_name', 'Đội thể thao'); }
                $contentLines[] = 'Thể thao: ' . $label . ' (huỷ cả đội)';
            }

            $competitions = isset($affected['competitions']) && is_array($affected['competitions']) ? $affected['competitions'] : array();
            foreach ($competitions as $c) {
                $label = self::pick($c, 'competition_name', 'Thi nghiệp vụ');
                $extra = ($action === self::ACTION_REPLACE) ? ' (cấp số báo danh mới)' : '';
                $contentLines[] = 'Thi nghiệp vụ: ' . $label . $extra;
            }

            // Thi Miss: cả thay thế lẫn huỷ tư cách đều chỉ huỷ đăng ký (không kế thừa)
            $beautyContests = isset($affected['beauty_contests']) && is_array($affected['beauty_contests']) ? $affected['beauty_contests'] : array();
            foreach ($beautyContests as $bc) {
                $label = self::pick($bc, 'contest_name', 'Thi Miss');
                $contentLines[] = 'Thi Miss: ' . $label . ' (huỷ)';
            }

            $roles = isset($affected['roles']) && is_array($affected['roles']) ? $affected['roles'] : array();
            $roleNames = array();
            foreach ($roles as $r) {
                $rn = self::pick($r, 'role_name');
                if ($rn) { $roleNames[] = $rn; }
            }
            if (!empty($roleNames)) {
                $contentLines[] = 'Vai trò: ' . implode(', ', $roleNames);
            }

            $createdAt = self::pick($rec, 'created_at');
            $performedAt = '';
            if ($createdAt) {
                $ts = is_numeric($createdAt) ? (int)$createdAt : strtotime($createdAt);
                if ($ts) { $performedAt = date('d/m/Y H:i', $ts); }
            }

            $rows[] = array(
                'action' => $action,
                'action_label' => ($action === self::ACTION_REPLACE) ? 'Thay thế' : 'Huỷ tư cách',
                'old_name' => self::pick($rec, 'old_attendee_name', ''),
                'old_staff_code' => self::pick($rec, 'old_staff_code', ''),
                'new_name' => self::pick($rec, 'new_attendee_name', ''),
                'new_staff_code' => self::pick($rec, 'new_staff_code', ''),
                'content_lines' => $contentLines,
                'reason' => self::pick($rec, 'reason', ''),
                'performed_by' => self::pick($rec, 'performed_by', ''),
                'performed_at' => $performedAt,
            );
        }

        return $rows;
    }
}
