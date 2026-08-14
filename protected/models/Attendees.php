<?php

Yii::import('application.models._base.BaseAttendees');

class Attendees extends BaseAttendees
{
    const APPROVAL_PENDING = 0;
    const APPROVAL_APPROVED = 1;
    const APPROVAL_REJECTED = 2;

    public $cccd_front_path;
    public $cccd_back_path;
    public $portrait_path;
    public $contract_path;
    public $approval_status;
    public $rejection_reason;
    public $property_name;
    public $property_code;
    public $role_name;
    public $staff_code;
    public $position_code;
    public $position_name;
    public $department_code;
    public $department_name;
    public $division_code;
    public $division_name;
    public $division;
    public $end_starting_date;
    public $join_hotel_date;
    public $check_in_date;
    public $check_out_date;
    public $transport_id;
    public $transport_name;
    public $gender;
    public $id_card;
    public $birthday;
    public $phone_number;
    public $end_date;
    public $current_approval_index;
    public $next_approval_index;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = array('cccd_front_path, cccd_back_path, portrait_path, contract_path', 'length', 'max' => 500);
        $rules[] = array('cccd_front_path, cccd_back_path, portrait_path, contract_path, approval_status, rejection_reason, personal_email', 'safe');
        $rules[] = array('approval_status, transport_id', 'numerical', 'integerOnly' => true);
        $rules[] = array('join_hotel_date, check_in_date, check_out_date, transport_id, transport_name, gender, id_card, birthday, phone_number', 'safe');
        $rules[] = array('phone_number', 'length', 'max' => 50);
        $rules[] = array('position_code, position_name, department_code, department_name, division_code, division_name, division', 'length', 'max' => 255);
        $rules[] = array('position_code, position_name, department_code, department_name, division_code, division_name, division, end_starting_date', 'safe');
        $rules[] = array('id_card', 'length', 'max' => 20);
        return $rules;
    }

    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        $labels['cccd_front_path'] = Yii::t('app', 'Ảnh CCCD mặt trước');
        $labels['cccd_back_path'] = Yii::t('app', 'Ảnh CCCD mặt sau');
        $labels['portrait_path'] = Yii::t('app', 'Ảnh chân dung');
        $labels['contract_path'] = Yii::t('app', 'Hợp đồng lao động');
        $labels['approval_status'] = Yii::t('app', 'Trạng thái duyệt');
        $labels['rejection_reason'] = Yii::t('app', 'Lý do từ chối');
        $labels['join_hotel_date'] = Yii::t('app', 'Ngày vào khách sạn');
        $labels['check_in_date'] = Yii::t('app', 'Ngày check-in');
        $labels['check_out_date'] = Yii::t('app', 'Ngày check-out');
        $labels['transport_id'] = Yii::t('app', 'Phương tiện');
        $labels['transport_name'] = Yii::t('app', 'Tên phương tiện');
        $labels['gender'] = Yii::t('app', 'Giới tính');
        $labels['id_card'] = Yii::t('app', 'Số CCCD/CMND');
        $labels['phone_number'] = Yii::t('app', 'Số điện thoại');
        $labels['position_code'] = Yii::t('app', 'Mã chức danh');
        $labels['position_name'] = Yii::t('app', 'Tên chức danh');
        $labels['department_code'] = Yii::t('app', 'Mã phòng ban');
        $labels['department_name'] = Yii::t('app', 'Tên phòng ban');
        $labels['division_code'] = Yii::t('app', 'Mã bộ phận');
        $labels['division_name'] = Yii::t('app', 'Tên bộ phận');
        $labels['division'] = Yii::t('app', 'Bộ phận');
        $labels['end_starting_date'] = Yii::t('app', 'Ngày kết thúc thử việc');
        return $labels;
    }

    public static function getApprovalStatusLabel($status)
    {
        $labels = array(
            self::APPROVAL_PENDING => '<span class="badge bg-warning text-dark">Chờ duyệt</span>',
            self::APPROVAL_APPROVED => '<span class="badge bg-success">Đã duyệt</span>',
            self::APPROVAL_REJECTED => '<span class="badge bg-danger">Từ chối</span>',
        );
        return isset($labels[$status]) ? $labels[$status] : '<span class="badge bg-secondary">Chưa xác định</span>';
    }

    public static function getApprovalStatusOptions()
    {
        return array(
            self::APPROVAL_PENDING => 'Chờ duyệt',
            self::APPROVAL_APPROVED => 'Đã duyệt',
            self::APPROVAL_REJECTED => 'Từ chối',
        );
    }

    /**
     * Danh sách người tham dự của một lượt đăng ký.
     *
     * Backend không hỗ trợ lọc is_active nên phải loại bỏ người đã huỷ tư cách
     * (is_active = 0) ở phía PHP, tránh hiển thị họ trong danh sách phê duyệt/báo cáo.
     *
     * @param int $registrationId
     * @param bool $includeWithdrawn Cho phép lấy cả người đã huỷ tư cách
     * @return array
     */
    public static function getByRegistrationId($registrationId, $includeWithdrawn = false)
    {
        $result = ApiClient::get(ApiEndpoints::ATTENDEE_LIST, array('registration_id' => $registrationId, 'per_page' => 5000));
        if (!$result['success'] || !isset($result['data']['data'])) {
            return array();
        }
        $attendees = $result['data']['data'];
        if ($includeWithdrawn) {
            return $attendees;
        }
        return array_values(array_filter($attendees, function ($att) {
            $isActive = isset($att['is_active']) ? $att['is_active'] : 1;
            return (int)$isActive !== 0;
        }));
    }

    public function approveViaApi()
    {
        $url = ApiEndpoints::url(ApiEndpoints::ATTENDEE_UPDATE, array('id' => $this->id));
        return ApiClient::post($url, array_merge($this->requiredUpdateFields(), array(
            'approval_status' => self::APPROVAL_APPROVED,
            'approved_by' => $this->approved_by,
            'approved_at' => time(),
        )));
    }

    public function rejectViaApi($reason)
    {
        $url = ApiEndpoints::url(ApiEndpoints::ATTENDEE_UPDATE, array('id' => $this->id));
        return ApiClient::post($url, array_merge($this->requiredUpdateFields(), array(
            'approval_status' => self::APPROVAL_REJECTED,
            'rejection_reason' => $reason,
            'approved_by' => $this->approved_by,
            'approved_at' => time(),
        )));
    }

    /**
     * Các trường backend validate bắt buộc khi update người tham dự.
     * Mọi lời gọi ATTENDEE_UPDATE phải kèm các trường này, nếu không
     * backend trả về lỗi "Xác minh dữ liệu thất bại".
     *
     * @return array
     */
    protected function requiredUpdateFields()
    {
        return array(
            'event_id' => $this->event_id,
            'registration_id' => $this->registration_id,
            'property_id' => $this->property_id,
            'role_id' => $this->role_id,
            'full_name' => $this->full_name,
        );
    }

    /**
     * Reset rejected attendees to pending when registration is resubmitted
     * Approved attendees keep their status
     */
    public static function resetRejectedToPending($registrationId)
    {
        $attendees = self::getByRegistrationId($registrationId);
        $count = 0;
        $errors = array();
        $debug = array();
        foreach ($attendees as $att) {
            $status = isset($att['approval_status']) ? (int)$att['approval_status'] : self::APPROVAL_PENDING;
            $debug[] = array('id' => $att['id'], 'status' => $status);
            // Chỉ reset những attendee bị từ chối (status=2), giữ nguyên đã duyệt (status=1)
            if ($status == self::APPROVAL_REJECTED) {
                $url = ApiEndpoints::url(ApiEndpoints::ATTENDEE_UPDATE, array('id' => $att['id']));
                // Gửi đầy đủ data hiện tại + cập nhật approval_status
                $updateData = $att;
                $updateData['approval_status'] = self::APPROVAL_PENDING;
                $updateData['rejection_reason'] = null;
                $apiResult = ApiClient::post($url, $updateData);
                if (isset($apiResult['success']) && $apiResult['success']) {
                    $count++;
                } else {
                    $errors[] = array('id' => $att['id'], 'response' => $apiResult);
                }
            }
        }
        Yii::log('resetRejectedToPending debug: ' . CJSON::encode($debug), 'info', 'attendees');
        return array('success' => empty($errors), 'count' => $count, 'errors' => $errors, 'debug' => $debug);
    }

    /**
     * BR-REG-02: Validate tất cả documents đã upload
     */
    public function validateDocuments()
    {
        return RegistrationValidator::validateRequiredDocuments($this);
    }

    /**
     * BR-REG-05: Kiểm tra có thể đăng ký thêm môn thể thao không
     */
    public function canRegisterMoreSports()
    {
        return RegistrationValidator::canRegisterMoreSports($this->id, $this->event_id);
    }

    /**
     * BR-REG-06: Kiểm tra có đủ điều kiện thi nghiệp vụ không
     */
    public function canRegisterCompetition($competitionId)
    {
        return RegistrationValidator::canRegisterCompetition($this->id, $competitionId);
    }

    /**
     * Kiểm tra có đầy đủ documents không
     */
    public function hasAllDocuments()
    {
        return !empty($this->cccd_front_path)
            && !empty($this->cccd_back_path)
            && !empty($this->portrait_path)
            && !empty($this->contract_path);
    }

    public static function fetchFromApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::ATTENDEE_DETAIL, array('id' => $id));
        $result = ApiClient::get($url);
        if ($result['success'] && isset($result['data'])) {
            $data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
            $model = new self;
            $model->setAttributes($data, false);
            // Map photo_path from API to portrait_path
            if (isset($data['photo_path'])) {
                $model->portrait_path = $data['photo_path'];
            }
            // Map start_date from API to join_hotel_date
            if (isset($data['start_date'])) {
                $model->join_hotel_date = $data['start_date'];
            }
            // Map division properties (virtual fields)
            if (isset($data['division_code'])) {
                $model->division_code = $data['division_code'];
            }
            if (isset($data['division_name'])) {
                $model->division_name = $data['division_name'];
            }
            if (isset($data['division'])) {
                $model->division = $data['division'];
            }
            $model->id = $id;
            return $model;
        }
        return null;
    }

    public function storeViaApi()
    {
        $data = array_filter($this->attributes, function ($value) {
            return $value !== null && $value !== '';
        });
        $extraFields = array(
            'portrait_path',
            'cccd_front_path',
            'cccd_back_path',
            'contract_path',
            'approval_status',
            'rejection_reason',
            'join_hotel_date',
            'check_in_date',
            'check_out_date',
            'transport_id',
            'personal_email',
            'gender',
            'id_card',
            'phone_number',
            'staff_code',
            'position_code',
            'position_name',
            'department_code',
            'department_name',
            'end_starting_date'
        );
        foreach ($extraFields as $field) {
            if (isset($this->$field) && $this->$field !== null && $this->$field !== '') {
                $data[$field] = $this->$field;
            }
        }
        // Map portrait_path to photo_path for API
        if (isset($this->portrait_path) && $this->portrait_path !== null && $this->portrait_path !== '') {
            $data['photo_path'] = $this->portrait_path;
        }
        // Map join_hotel_date to start_date for API
        if (isset($this->join_hotel_date) && $this->join_hotel_date !== null && $this->join_hotel_date !== '') {
            $data['start_date'] = $this->join_hotel_date;
        }
        $result = ApiClient::post(ApiEndpoints::ATTENDEE_STORE, $data);
        return $result;
    }

    public function updateViaApi()
    {
        $data = array_filter($this->attributes, function ($value) {
            return $value !== null && $value !== '';
        });
        $extraFields = array(
            'portrait_path',
            'cccd_front_path',
            'cccd_back_path',
            'contract_path',
            'approval_status',
            'rejection_reason',
            'join_hotel_date',
            'check_in_date',
            'check_out_date',
            'transport_id',
            'personal_email',
            'gender',
            'id_card',
            'phone_number',
            'staff_code',
            'position_code',
            'position_name',
            'department_code',
            'department_name',
            'end_starting_date'
        );
        foreach ($extraFields as $field) {
            if (isset($this->$field) && $this->$field !== null && $this->$field !== '') {
                $data[$field] = $this->$field;
            }
        }
        // Map portrait_path to photo_path for API
        if (isset($this->portrait_path) && $this->portrait_path !== null && $this->portrait_path !== '') {
            $data['photo_path'] = $this->portrait_path;
        }
        // Map join_hotel_date to start_date for API
        if (isset($this->join_hotel_date) && $this->join_hotel_date !== null && $this->join_hotel_date !== '') {
            $data['start_date'] = $this->join_hotel_date;
        }
        $url = ApiEndpoints::url(ApiEndpoints::ATTENDEE_UPDATE, array('id' => $this->id));
        return ApiClient::post($url, $data);
    }




    public static function resolveRoleNames($roleIdField)
    {
        if (empty($roleIdField)) {
            return '';
        }
        if (is_array($roleIdField)) {
            $roleIds = array_map('trim', $roleIdField);
        } else {
            $roleIds = array_map('trim', explode(',', $roleIdField));
        }
        $roleNames = array();
        static $cachedRoles = null;
        if ($cachedRoles === null) {
            $rolesData = Roles::getApiDataProvider(array(), 100)->getData();
            $cachedRoles = array();
            foreach ($rolesData as $r) {
                $rId = isset($r['id']) ? $r['id'] : (isset($r->id) ? $r->id : null);
                $rName = isset($r['name']) ? $r['name'] : (isset($r->name) ? $r->name : '');
                if ($rId) $cachedRoles[$rId] = $rName;
            }
        }
        foreach ($roleIds as $rId) {
            if (isset($cachedRoles[$rId])) {
                $roleNames[] = $cachedRoles[$rId];
            }
        }
        return !empty($roleNames) ? implode(', ', $roleNames) : '';
    }

    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::ATTENDEE_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    public static function getApiDataProvider($params = array(), $pageSize = 10000)
    {
        return new ApiDataProvider(ApiEndpoints::ATTENDEE_LIST, array(
            'modelClass' => 'Attendees',
            'params' => $params,
            'pagination' => array('pageSize' => $pageSize),
        ));
    }

    /**
     * Đếm số người đăng ký (unique) theo logic báo cáo thống kê:
     * - Bản đăng ký active: không bị xóa, không phải nháp
     * - Người tham dự: đang active, không bị xóa, thuộc bản đăng ký active
     * - Gộp trùng: cùng mã nhân viên hoặc cùng số CCCD/CMND thì tính là 1 người
     */
    public static function countUniqueRegistered($eventId = null, $periodId = null)
    {
        // Bản đăng ký active
        $regParams = array('per_page' => 1000);
        if ($eventId) $regParams['event_id'] = $eventId;
        if ($periodId) $regParams['period_id'] = $periodId;

        $activeRegistrationIds = array();
        $registrations = Registrations::getApiDataProvider($regParams, 10000)->getData();
        foreach ($registrations as $reg) {
            $regDeletedAt = isset($reg->deleted_at) ? $reg->deleted_at : null;
            if ($regDeletedAt) continue;
            $regStatus = isset($reg->status) ? (int)$reg->status : 0;
            if ($regStatus === Registrations::STATUS_DRAFT) continue;
            $regId = isset($reg->id) ? $reg->id : null;
            if ($regId) {
                $activeRegistrationIds[$regId] = true;
            }
        }

        if (empty($activeRegistrationIds)) {
            return 0;
        }

        // Người tham dự hợp lệ, gộp trùng theo mã NV / CCCD
        $attParams = array('per_page' => 10000);
        if ($eventId) $attParams['event_id'] = $eventId;

        $count = 0;
        $staffCodeIndex = array();
        $idCardIndex = array();
        $rawAttendees = self::getApiDataProvider($attParams, 10000)->getData();
        foreach ($rawAttendees as $att) {
            $attId = isset($att->id) ? $att->id : null;
            if (!$attId) continue;

            $attDeletedAt = isset($att->deleted_at) ? $att->deleted_at : null;
            if ($attDeletedAt) continue;

            if (isset($att->is_active) && $att->is_active !== null && $att->is_active !== '' && !(int)$att->is_active) continue;

            $regId = isset($att->registration_id) ? $att->registration_id : null;
            if (!$regId || !isset($activeRegistrationIds[$regId])) continue;

            $staffCode = isset($att->staff_code) ? mb_strtoupper(trim((string)$att->staff_code), 'UTF-8') : '';
            $idCard = isset($att->id_card) ? trim((string)$att->id_card) : '';

            $isDuplicate = ($staffCode !== '' && isset($staffCodeIndex[$staffCode]))
                || ($idCard !== '' && isset($idCardIndex[$idCard]));

            if ($staffCode !== '') $staffCodeIndex[$staffCode] = true;
            if ($idCard !== '') $idCardIndex[$idCard] = true;

            if (!$isDuplicate) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Bản kê nội dung 1 người tham dự đang tham gia: đội thể thao, cuộc thi nghiệp vụ, vai trò.
     * Dùng cho modal Thay thế / Huỷ tư cách trên màn hình phê duyệt.
     *
     * @param int $attendeeId
     * @return array {sport_teams: [...], competitions: [...], roles: [...]}
     */
    /**
     * Trích danh sách từ response API, hỗ trợ cả 2 kiểu bọc:
     * data.data (paginated) hoặc data (mảng trực tiếp).
     */
    protected static function extractListData($result)
    {
        if (!isset($result['success']) || !$result['success']) {
            return array();
        }
        if (isset($result['data']['data']) && is_array($result['data']['data'])) {
            return $result['data']['data'];
        }
        if (isset($result['data']) && is_array($result['data'])) {
            return $result['data'];
        }
        return array();
    }

    public static function getParticipationSummary($attendeeId)
    {
        return array(
            'sport_teams' => self::collectSportTeams($attendeeId),
            'competitions' => self::collectCompetitions($attendeeId),
            'beauty_contests' => self::collectBeautyContests($attendeeId),
            'roles' => self::collectRoles($attendeeId),
        );
    }

    /**
     * Các đội thể thao mà người này là thành viên (kèm số áo, vị trí, đội trưởng, sĩ số đội, liên quân).
     */
    protected static function collectSportTeams($attendeeId)
    {
        // Lưu ý: endpoint sport-team-members KHÔNG lọc theo attendee_id (trả toàn bộ
        // rồi phân trang). Phải lấy per_page đủ lớn để không rớt các bản ghi cũ
        // (vd: đội liên quân tạo trước), sau đó lọc attendee_id ở phía client.
        $result = ApiClient::get(ApiEndpoints::SPORT_TEAM_MEMBER_LIST, array(
            'attendee_id' => $attendeeId,
            'per_page' => 10000,
        ));
        $items = self::extractListData($result);
        if (!is_array($items)) {
            return array();
        }

        $teams = array();
        foreach ($items as $item) {
            if (!isset($item['attendee_id']) || $item['attendee_id'] != $attendeeId) {
                continue;
            }
            $teamId = isset($item['sport_team_id']) ? $item['sport_team_id'] : null;
            if (!$teamId) {
                continue;
            }
            // Khử trùng lặp: cùng một đội chỉ hiện một lần dù có nhiều dòng membership.
            if (isset($teams[$teamId])) {
                continue;
            }

            $team = SportTeams::fetchFromApi($teamId);
            // Ẩn hẳn đội đã bị xoá mềm (deleted_at) — dữ liệu không còn hiệu lực.
            if ($team && !empty($team->deleted_at)) {
                continue;
            }
            // Ẩn hẳn đội đã bị xoá / dữ liệu rác (không còn team record và cũng
            // không có tên fallback) — tránh hiện block dropdown trống tiêu đề.
            $sportName = $team && $team->sport_name ? $team->sport_name : (isset($item['sport_name']) ? $item['sport_name'] : '');
            $teamName = $team ? $team->name : (isset($item['team_name']) ? $item['team_name'] : '');
            if (!$team && $sportName === '' && $teamName === '') {
                continue;
            }

            $teamMembers = SportTeamMembers::getTeamMemberBriefs($teamId);
            $otherMembers = array();
            foreach ($teamMembers as $tm) {
                if ($tm['attendee_id'] != $attendeeId) {
                    $otherMembers[] = $tm;
                }
            }

            $teams[$teamId] = array(
                'member_id' => isset($item['id']) ? $item['id'] : null,
                'sport_team_id' => $teamId,
                'sport_id' => $team ? $team->sport_id : (isset($item['sport_id']) ? $item['sport_id'] : null),
                'sport_name' => $sportName,
                'team_name' => $teamName,
                'jersey_number' => isset($item['jersey_number']) ? $item['jersey_number'] : null,
                'position' => isset($item['position']) ? $item['position'] : null,
                'is_captain' => isset($item['is_captain']) ? (int)$item['is_captain'] : 0,
                'is_alliance' => $team && isset($team->is_alliance) ? (int)$team->is_alliance : 0,
                'member_count' => count($teamMembers),
                'other_members' => $otherMembers,
            );
        }
        return array_values($teams);
    }

    /**
     * Các cuộc thi nghiệp vụ mà người này đã đăng ký.
     */
    protected static function collectCompetitions($attendeeId)
    {
        $result = ApiClient::get(ApiEndpoints::COMPETITION_REGISTRATION_LIST, array(
            'attendee_id' => $attendeeId,
            'per_page' => 500,
        ));
        $items = self::extractListData($result);
        if (!is_array($items)) {
            return array();
        }

        $competitions = array();
        foreach ($items as $item) {
            if (!isset($item['attendee_id']) || $item['attendee_id'] != $attendeeId) {
                continue;
            }
            $compId = isset($item['competition_id']) ? $item['competition_id'] : null;
            if (!$compId) {
                continue;
            }
            $name = isset($item['competition_name']) ? $item['competition_name'] : '';
            if (empty($name)) {
                $comp = Competitions::fetchFromApi($compId);
                $name = $comp ? $comp->name : '';
            }
            $competitions[] = array(
                'registration_id' => isset($item['id']) ? $item['id'] : null,
                'competition_id' => $compId,
                'competition_name' => $name,
                'candidate_number' => isset($item['candidate_number']) ? $item['candidate_number'] : '',
            );
        }
        return $competitions;
    }

    /**
     * Các cuộc thi sắc đẹp (Miss) mà người này đã đăng ký làm thí sinh.
     */
    protected static function collectBeautyContests($attendeeId)
    {
        // Endpoint beauty-contestants có thể không lọc chắc chắn theo attendee_id,
        // nên lấy per_page đủ lớn rồi lọc lại ở phía client (giống collectCompetitions).
        $result = ApiClient::get(ApiEndpoints::BEAUTY_CONTESTANT_LIST, array(
            'attendee_id' => $attendeeId,
            'per_page' => 500,
        ));
        $items = self::extractListData($result);
        if (!is_array($items)) {
            return array();
        }

        $contests = array();
        foreach ($items as $item) {
            if (!isset($item['attendee_id']) || $item['attendee_id'] != $attendeeId) {
                continue;
            }
            $contestId = isset($item['contest_id']) ? $item['contest_id'] : null;
            if (!$contestId) {
                continue;
            }
            $name = isset($item['contest_name']) ? $item['contest_name'] : '';
            if (empty($name)) {
                $contest = BeautyContests::fetchFromApi($contestId);
                $name = $contest ? $contest->name : '';
            }
            $contests[] = array(
                'contestant_id' => isset($item['id']) ? $item['id'] : null,
                'contest_id' => $contestId,
                'contest_name' => $name,
                'candidate_number' => isset($item['candidate_number']) ? $item['candidate_number'] : '',
            );
        }
        return $contests;
    }

    /**
     * Các vai trò được gán cho người này (attendee_roles).
     */
    protected static function collectRoles($attendeeId)
    {
        $result = ApiClient::get(ApiEndpoints::ATTENDEE_ROLE_LIST, array(
            'attendee_id' => $attendeeId,
            'per_page' => 500,
        ));
        $items = self::extractListData($result);
        if (!is_array($items)) {
            return array();
        }

        $roles = array();
        foreach ($items as $item) {
            if (!isset($item['attendee_id']) || $item['attendee_id'] != $attendeeId) {
                continue;
            }
            $roleId = isset($item['role_id']) ? $item['role_id'] : null;
            if (!$roleId) {
                continue;
            }
            $roles[] = array(
                'attendee_role_id' => isset($item['id']) ? $item['id'] : null,
                'role_id' => $roleId,
                'role_name' => isset($item['role_name']) ? $item['role_name'] : self::resolveRoleNames((string)$roleId),
            );
        }
        return $roles;
    }

    /**
     * Huỷ tư cách người tham dự: đánh dấu is_active=0 + ghi lý do vào note.
     *
     * Endpoint update của backend validate bắt buộc các trường
     * event_id, registration_id, property_id, role_id, full_name — nên phải
     * gửi kèm dữ liệu gốc của người tham dự, nếu không sẽ báo "Xác minh dữ liệu thất bại".
     *
     * @param int $id
     * @param string $reason
     * @param string|null $email Người thực hiện (để ghi vết)
     * @return array Kết quả ApiClient
     */
    public static function withdrawViaApi($id, $reason, $email = null)
    {
        $detail = ApiClient::get(ApiEndpoints::url(ApiEndpoints::ATTENDEE_DETAIL, array('id' => $id)));
        if (empty($detail['success'])) {
            return $detail;
        }
        $data = isset($detail['data']['data']) ? $detail['data']['data'] : $detail['data'];

        $noteParts = array('[Huỷ tư cách]');
        if ($reason !== null && $reason !== '') {
            $noteParts[] = $reason;
        }
        if ($email) {
            $noteParts[] = 'bởi ' . $email;
        }

        $url = ApiEndpoints::url(ApiEndpoints::ATTENDEE_UPDATE, array('id' => $id));
        return ApiClient::post($url, array(
            // Các trường bắt buộc — giữ nguyên giá trị gốc
            'event_id' => isset($data['event_id']) ? $data['event_id'] : null,
            'registration_id' => isset($data['registration_id']) ? $data['registration_id'] : null,
            'property_id' => isset($data['property_id']) ? $data['property_id'] : null,
            'role_id' => isset($data['role_id']) ? $data['role_id'] : null,
            'full_name' => isset($data['full_name']) ? $data['full_name'] : null,
            // Đánh dấu huỷ tư cách: is_active = 0 + deleted_at
            'is_active' => 0,
            'deleted_at' => date('Y-m-d H:i:s'),
            'note' => implode(' ', $noteParts),
        ));
    }

    public static function getRoleBadgeClass($roleName)
    {
        $roleNameLower = mb_strtolower(trim($roleName), 'UTF-8');
        if (strpos($roleNameLower, 'trưởng đoàn') !== false) {
            return 'bg-danger text-white';
        } elseif (strpos($roleNameLower, 'phó đoàn') !== false) {
            return 'bg-warning text-dark';
        } elseif (strpos($roleNameLower, 'huấn luyện viên') !== false || strpos($roleNameLower, 'hlv') !== false) {
            return 'bg-info text-dark';
        } elseif (strpos($roleNameLower, 'vận động viên') !== false || strpos($roleNameLower, 'vđv') !== false || strpos($roleNameLower, 'thi đấu') !== false) {
            return 'bg-primary text-white';
        } elseif (strpos($roleNameLower, 'cổ động viên') !== false || strpos($roleNameLower, 'cdv') !== false) {
            return 'bg-success text-white';
        } elseif (strpos($roleNameLower, 'khách') !== false) {
            return 'bg-dark text-white';
        } else {
            $hash = crc32($roleNameLower);
            $classes = array(
                'bg-primary text-white',
                'bg-secondary text-white',
                'bg-success text-white',
                'bg-danger text-white',
                'bg-warning text-dark',
                'bg-info text-dark',
                'bg-dark text-white'
            );
            return $classes[abs($hash) % count($classes)];
        }
    }
}
