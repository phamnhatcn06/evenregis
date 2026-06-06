<?php

Yii::import('application.models._base.BaseCompetitions');

class Competitions extends BaseCompetitions
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'name' => 'Tên cuộc thi',
            'description' => 'Mô tả',
            'registration_open_at' => 'Mở đăng ký',
            'registration_close_at' => 'Đóng đăng ký',
            'candidate_number_prefix' => 'Tiền tố SBD',
            'candidate_number_start' => 'SBD bắt đầu',
            'candidate_number_pad' => 'Độ dài SBD',
            'max_per_org' => 'Số lượng thành viên tối đa/đơn vị',
            'has_qualification' => 'Có vòng loại',
            'allow_direct_final' => 'Cho phép ghi danh thẳng vòng chung kết',
            'is_active' => 'Trạng thái',
            'created_by' => 'Người tạo',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
        );
    }

    public static function fetchFromApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::COMPETITION_DETAIL, array('id' => $id));
        $result = ApiClient::get($url);
        if ($result['success'] && isset($result['data'])) {
            $data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
            $model = new self;
            $model->setAttributes($data, false);
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
        return ApiClient::post(ApiEndpoints::COMPETITION_STORE, $data);
    }

    public function updateViaApi()
    {
        $url = ApiEndpoints::url(ApiEndpoints::COMPETITION_UPDATE, array('id' => $this->id));
        $data = array_filter($this->attributes, function ($value) {
            return $value !== null && $value !== '';
        });
        return ApiClient::post($url, $data);
    }

    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::COMPETITION_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    public static function getApiDataProvider($params = array(), $pageSize = 25)
    {
        return new ApiDataProvider(ApiEndpoints::COMPETITION_LIST, array(
            'modelClass' => 'Competitions',
            'params' => $params,
            'pagination' => array(
                'pageSize' => $pageSize,
            ),
        ));
    }

    public static function getActiveList()
    {
        return CacheHelper::getDropdown('competitions_active', function () {
            $list = array();
            $competitions = self::getApiDataProvider(array('is_active' => 1), 100)->getData();
            foreach ($competitions as $comp) {
                $list[$comp->id] = $comp->name;
            }
            return $list;
        });
    }

    public static function clearCache()
    {
        CacheHelper::clearDropdownCache('competitions_active');
    }

    public static function assignCandidateNumbers($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::COMPETITION_ASSIGN_NUMBERS, array('id' => $id));
        return ApiClient::post($url, array());
    }

    public static function getStatusLabel($status)
    {
        $labels = array(
            self::STATUS_INACTIVE => '<span class="badge bg-secondary">Không hoạt động</span>',
            self::STATUS_ACTIVE => '<span class="badge bg-success">Hoạt động</span>',
        );
        return isset($labels[$status]) ? $labels[$status] : $status;
    }

    public function isRegistrationOpen()
    {
        $now = time();
        $open = $this->registration_open_at ? (int)$this->registration_open_at : 0;
        $close = $this->registration_close_at ? (int)$this->registration_close_at : PHP_INT_MAX;
        return $now >= $open && $now <= $close;
    }

    /**
     * BR-REG-06: Lấy danh sách phòng ban được phép thi
     */
    public function getAllowedDepartments()
    {
        return CompetitionDepartments::getDepartmentCodes($this->id);
    }

    /**
     * BR-REG-06: Kiểm tra phòng ban có được phép thi không
     */
    public function isDepartmentAllowed($departmentCode)
    {
        $allowed = $this->getAllowedDepartments();
        if (empty($allowed)) {
            return true;
        }
        return in_array($departmentCode, $allowed);
    }

    /**
     * BR-REG-06: Đồng bộ danh sách phòng ban cho cuộc thi
     */
    public function syncDepartments($departmentCodes)
    {
        CompetitionDepartments::syncDepartments($this->id, $departmentCodes);
    }

    /**
     * BR-REG-06: Kiểm tra attendee có đủ điều kiện thi không
     */
    public function canAttendeeRegister($attendeeId)
    {
        return RegistrationValidator::canRegisterCompetition($attendeeId, $this->id);
    }
}
