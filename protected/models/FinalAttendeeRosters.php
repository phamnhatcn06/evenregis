<?php

/**
 * Model màn Tổng hợp danh sách Vòng Chung Kết (VCK) + mã lucky.
 *
 * FE không có DB — toàn bộ việc gọi External API nằm ở đây, controller chỉ gọi method của model.
 */
class FinalAttendeeRosters extends CFormModel
{
    const STATUS_ACTIVE = 1;
    const STATUS_MANUAL = 3;

    /** Giá trị lọc "chưa xác định" cho bộ phận / phòng ban */
    const FILTER_NONE = '__none__';

    /** Chế độ đồng bộ: xem trước (dry-run, không ghi) và ghi thật */
    const SYNC_MODE_PREVIEW = 'preview';
    const SYNC_MODE_APPLY   = 'apply';

    public $id;
    public $event_id;
    public $period_id;
    public $attendee_id;
    public $source_attendee_ids;
    public $registration_id;
    public $dedup_key;
    public $dedup_source;

    public $full_name;
    public $staff_code;
    public $id_card;
    public $birthday;
    public $gender;
    public $phone_number;
    public $email;

    public $property_id;
    public $property_code;
    public $property_name;
    public $unit_label;
    public $division_code;
    public $division_name;
    public $department_code;
    public $department_name;

    public $position;
    public $position_code;
    public $position_name;
    public $position_display;

    public $attendee_type;
    public $shirt_size;
    public $note;
    public $sort_order;

    public $lucky_number;
    public $login_identifier;
    public $lucky_provisioned_at;
    public $pin_is_set;
    public $qr_token;
    public $badge_number;

    public $overridden_fields;
    public $has_override;
    public $source_snapshot;
    public $conflict_flag;

    public $status;
    public $last_synced_at;
    public $created_by;
    public $updated_by;
    public $deleted_by;
    public $created_at;
    public $updated_at;
    public $deleted_at;
    public $is_withdrawn;

    /**
     * Trường HO được sửa thủ công. Phải khớp với EDITABLE_FIELDS ở BE.
     * `lucky_number` cố ý KHÔNG có trong danh sách — mã đã cấp không bao giờ đổi.
     */
    public static function editableFields()
    {
        return array(
            'full_name'       => 'Họ và tên',
            'staff_code'      => 'Mã nhân viên',
            'id_card'         => 'Số CCCD',
            'birthday'        => 'Ngày sinh',
            'gender'          => 'Giới tính',
            'phone_number'    => 'Số điện thoại',
            'email'           => 'Email',
            'property_name'   => 'Đơn vị',
            'unit_label'      => 'Nhãn in thẻ',
            'division_code'   => 'Mã bộ phận',
            'division_name'   => 'Bộ phận',
            'department_code' => 'Mã phòng ban',
            'department_name' => 'Phòng ban',
            'position'        => 'Chức danh',
            'attendee_type'   => 'Loại người tham dự',
            'shirt_size'      => 'Size áo',
            'note'            => 'Ghi chú',
            'sort_order'      => 'Thứ tự',
        );
    }

    /** Trường sửa tay sẽ được ghi ngược sang attendees (hiện trên thẻ in và email) */
    public static function writeBackFields()
    {
        return array('position', 'unit_label', 'shirt_size', 'phone_number', 'note');
    }

    public function rules()
    {
        return array(
            array(
                'id, event_id, period_id, attendee_id, source_attendee_ids, registration_id, dedup_key,
                 dedup_source, full_name, staff_code, id_card, birthday, gender, phone_number, email,
                 property_id, property_code, property_name, unit_label, division_code, division_name,
                 department_code, department_name, position, position_code, position_name, position_display,
                 attendee_type, shirt_size, note, sort_order, lucky_number, login_identifier,
                 lucky_provisioned_at, pin_is_set, qr_token, badge_number, overridden_fields, has_override,
                 source_snapshot, conflict_flag, status, last_synced_at, created_by, updated_by, deleted_by,
                 created_at, updated_at, deleted_at, is_withdrawn',
                'safe'
            ),
        );
    }

    public function attributeLabels()
    {
        return array(
            'full_name'        => 'Họ và tên',
            'staff_code'       => 'Mã nhân viên',
            'id_card'          => 'Số CCCD',
            'property_name'    => 'Đơn vị',
            'division_name'    => 'Bộ phận',
            'department_name'  => 'Phòng ban',
            'position_display' => 'Chức danh',
            'shirt_size'       => 'Size áo',
            'attendee_type'    => 'Loại',
            'lucky_number'     => 'Mã lucky',
            'login_identifier' => 'Định danh đăng nhập',
            'status'           => 'Trạng thái',
        );
    }

    // ------------------------------------------------------------------ Nhãn hiển thị

    public static function getStatusLabel($status, $isWithdrawn = false)
    {
        if ($isWithdrawn) {
            return '<span class="badge bg-danger">Đã huỷ tư cách</span>';
        }

        $labels = array(
            self::STATUS_ACTIVE => '<span class="badge bg-success">Đang tham dự</span>',
            self::STATUS_MANUAL => '<span class="badge bg-info">HO thêm tay</span>',
        );

        return isset($labels[$status]) ? $labels[$status] : '<span class="badge bg-secondary">-</span>';
    }

    public static function getTypeOptions()
    {
        return array(
            'finalist' => 'Vào chung kết',
            'director' => 'Giám đốc',
            'driver'   => 'Lái xe',
            'manual'   => 'HO thêm tay',
        );
    }

    public static function getTypeBadge($type)
    {
        $map = array(
            'finalist' => 'bg-primary',
            'director' => 'bg-warning text-dark',
            'driver'   => 'bg-secondary',
            'manual'   => 'bg-info',
        );
        $options = self::getTypeOptions();
        $label   = isset($options[$type]) ? $options[$type] : ($type ?: '-');
        $class   = isset($map[$type]) ? $map[$type] : 'bg-secondary';

        return '<span class="badge ' . $class . '">' . CHtml::encode($label) . '</span>';
    }

    public static function getConflictLabel($flag)
    {
        $labels = array(
            'duplicate_lucky'      => 'Trùng mã lucky',
            'possible_wrong_merge' => 'Có thể gộp sai người',
            'duplicate_person'     => 'Một người bị tách hai dòng',
        );

        return isset($labels[$flag]) ? $labels[$flag] : $flag;
    }

    public static function getShirtSizeOptions()
    {
        return array('S' => 'S', 'M' => 'M', 'L' => 'L', 'XL' => 'XL', 'XXL' => 'XXL', 'XXXL' => 'XXXL');
    }

    public static function getLuckyFilterOptions()
    {
        return array('1' => 'Đã có mã', '0' => 'Chưa có mã');
    }

    public static function getOverrideFilterOptions()
    {
        return array('1' => 'Đã sửa tay', '0' => 'Chưa sửa tay');
    }

    public static function getConflictFilterOptions()
    {
        return array(
            'duplicate_lucky'      => 'Trùng mã lucky',
            'possible_wrong_merge' => 'Có thể gộp sai người',
            'duplicate_person'     => 'Một người bị tách hai dòng',
        );
    }

    // ------------------------------------------------------------------ Gọi API

    /**
     * DataProvider cho bảng danh sách.
     */
    public static function getApiDataProvider($params = array(), $pageSize = 25)
    {
        return new ApiDataProvider(ApiEndpoints::FINAL_ATTENDEE_ROSTER_LIST, array(
            'modelClass' => 'FinalAttendeeRosters',
            'params'     => $params,
            'pagination' => array('pageSize' => $pageSize),
        ));
    }

    /**
     * Giá trị cho dropdown lọc: đơn vị / bộ phận / phòng ban thực sự có người.
     *
     * Lưu ý: BE gộp các mã cùng tên thành một option (vd mã 650 và 810 cùng tên
     * "An ninh - Kỹ thuật - CNTT") nên `code` có thể là danh sách mã cách nhau dấu phẩy.
     */
    public static function getFilterOptions($eventId, $periodId = null, $propertyId = null, $divisionCode = null)
    {
        $empty = array('properties' => array(), 'divisions' => array(), 'departments' => array());

        if (empty($eventId)) {
            return $empty;
        }

        $result = ApiClient::get(ApiEndpoints::FINAL_ATTENDEE_ROSTER_FILTERS, array(
            'event_id'      => $eventId,
            'period_id'     => $periodId,
            'property_id'   => $propertyId,
            'division_code' => $divisionCode,
        ));

        if ($result['success'] && isset($result['data']['data'])) {
            return array_merge($empty, $result['data']['data']);
        }

        return $empty;
    }

    /**
     * Số liệu thống kê cho dải thẻ trên header.
     */
    public static function getStats($eventId, $periodId = null, $propertyId = null)
    {
        $empty = array(
            'total' => 0, 'with_lucky' => 0, 'without_lucky' => 0, 'pin_set' => 0,
            'with_override' => 0, 'conflicts' => 0, 'manual' => 0, 'withdrawn' => 0,
        );

        if (empty($eventId)) {
            return $empty;
        }

        $result = ApiClient::get(ApiEndpoints::FINAL_ATTENDEE_ROSTER_STATS, array(
            'event_id'    => $eventId,
            'period_id'   => $periodId,
            'property_id' => $propertyId,
        ));

        if ($result['success'] && isset($result['data']['data'])) {
            return array_merge($empty, $result['data']['data']);
        }

        return $empty;
    }

    /**
     * Đồng bộ từ danh sách VCK. $mode = 'preview' (xem trước) hoặc 'apply' (ghi thật).
     */
    public static function syncViaApi($eventId, $periodId, $propertyId = null, $mode = 'preview')
    {
        $ssoUser = AuthHandler::getUser();

        return ApiClient::post(ApiEndpoints::FINAL_ATTENDEE_ROSTER_SYNC, array(
            'event_id'    => $eventId,
            'period_id'   => $periodId,
            'property_id' => $propertyId,
            'mode'        => $mode,
            'run_by'      => isset($ssoUser['email']) ? $ssoUser['email'] : null,
        ));
    }

    /**
     * Sửa thủ công một hoặc nhiều trường. $fields = array('tên_trường' => giá_trị).
     */
    public static function updateFieldsViaApi($id, $fields)
    {
        $ssoUser = AuthHandler::getUser();
        $url     = ApiEndpoints::url(ApiEndpoints::FINAL_ATTENDEE_ROSTER_UPDATE, array('id' => $id));

        return ApiClient::post($url, array(
            'fields'     => $fields,
            'updated_by' => isset($ssoUser['email']) ? $ssoUser['email'] : null,
        ));
    }

    /**
     * Khôi phục các trường về giá trị gốc từ lần đồng bộ cuối.
     */
    public static function resetFieldsViaApi($id, $fields)
    {
        $ssoUser = AuthHandler::getUser();
        $url     = ApiEndpoints::url(ApiEndpoints::FINAL_ATTENDEE_ROSTER_RESET_FIELD, array('id' => $id));

        return ApiClient::post($url, array(
            'fields'     => array_values((array) $fields),
            'updated_by' => isset($ssoUser['email']) ? $ssoUser['email'] : null,
        ));
    }

    /**
     * Cấp mã lucky cho những người chưa có mã. Không bao giờ đổi mã đã cấp.
     */
    public static function provisionLuckyViaApi($eventId, $propertyId = null)
    {
        $ssoUser = AuthHandler::getUser();

        return ApiClient::post(ApiEndpoints::FINAL_ATTENDEE_ROSTER_PROVISION_LUCKY, array(
            'event_id'    => $eventId,
            'property_id' => $propertyId,
            'run_by'      => isset($ssoUser['email']) ? $ssoUser['email'] : null,
        ));
    }

    /**
     * Đối soát dải số thẻ MT và mã lucky. Chỉ đọc.
     */
    public static function getAudit($eventId, $scope = 'all')
    {
        if (empty($eventId)) {
            return null;
        }

        $result = ApiClient::get(ApiEndpoints::FINAL_ATTENDEE_ROSTER_AUDIT, array(
            'event_id' => $eventId,
            'scope'    => $scope,
        ));

        if ($result['success'] && isset($result['data']['data'])) {
            return $result['data']['data'];
        }

        return null;
    }

    /**
     * Lấy đúng một trang dữ liệu, chỉ định trang tường minh.
     *
     * Không dùng ApiDataProvider cho việc này: `CDataProvider::getPagination()` gọi
     * `getTotalItemCount()` nên nạp luôn trang 1 và cache lại, khiến mọi lần `setCurrentPage()`
     * sau đó không có tác dụng — vòng lặp xuất Excel sẽ lấy mãi một trang.
     *
     * @param array $params Tham số lọc
     * @param int   $page    Trang, bắt đầu từ 1
     * @param int   $perPage
     * @return array Danh sách mảng thuộc tính
     */
    public static function fetchPage($params, $page = 1, $perPage = 500)
    {
        $result = ApiClient::get(ApiEndpoints::FINAL_ATTENDEE_ROSTER_LIST, array_merge($params, array(
            'page'     => (int) $page,
            'per_page' => (int) $perPage,
        )));

        if ($result['success'] && isset($result['data']['data']) && is_array($result['data']['data'])) {
            return $result['data']['data'];
        }

        return array();
    }

    /**
     * HO thêm người thủ công. BE tạo kèm bản ghi attendee tối thiểu, cấp luôn mã lucky và số thẻ.
     */
    public static function storeViaApi($data)
    {
        $ssoUser = AuthHandler::getUser();

        return ApiClient::post(ApiEndpoints::FINAL_ATTENDEE_ROSTER_STORE, array_merge($data, array(
            'created_by' => isset($ssoUser['email']) ? $ssoUser['email'] : null,
        )));
    }

    /**
     * Chuyển các dòng dữ liệu API thành model để view dùng.
     */
    public static function createFromApiList($rows)
    {
        $models = array();
        foreach ((array) $rows as $row) {
            $model = new self;
            $model->setAttributes($row, false);
            $models[] = $model;
        }
        return $models;
    }

    /** Trường này đã bị HO sửa tay chưa (để vẽ dấu override trên bảng) */
    public function isOverridden($field)
    {
        return is_array($this->overridden_fields) && in_array($field, $this->overridden_fields, true);
    }

    /** Giá trị gốc trước khi sửa tay, dùng cho tooltip "Gốc: ..." */
    public function originalValue($field)
    {
        if ($this->hasOriginalValue($field)) {
            return $this->source_snapshot[$field];
        }
        return null;
    }

    /**
     * Có giá trị gốc để khôi phục hay không.
     *
     * Dòng HO tự thêm không có nguồn nên không có gì khôi phục — khi đó không hiện nút ↺
     * để HO không bấm vào một thao tác chắc chắn thất bại.
     */
    public function hasOriginalValue($field)
    {
        return is_array($this->source_snapshot) && array_key_exists($field, $this->source_snapshot);
    }
}
