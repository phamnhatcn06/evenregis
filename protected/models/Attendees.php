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
    public $join_hotel_date;
    public $check_in_date;
    public $check_out_date;
    public $transport_id;
    public $transport_name;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = array('cccd_front_path, cccd_back_path, portrait_path, contract_path', 'length', 'max' => 500);
        $rules[] = array('cccd_front_path, cccd_back_path, portrait_path, contract_path, approval_status, rejection_reason', 'safe');
        $rules[] = array('approval_status, transport_id', 'numerical', 'integerOnly' => true);
        $rules[] = array('join_hotel_date, check_in_date, check_out_date, transport_id, transport_name', 'safe');
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

    public static function getByRegistrationId($registrationId)
    {
        $result = ApiClient::get(ApiEndpoints::ATTENDEE_LIST, array('registration_id' => $registrationId, 'per_page' => 500));
        if ($result['success'] && isset($result['data']['data'])) {
            return $result['data']['data'];
        }
        return array();
    }

    public function approveViaApi()
    {
        $url = ApiEndpoints::url(ApiEndpoints::ATTENDEE_UPDATE, array('id' => $this->id));
        return ApiClient::post($url, array(
            'approval_status' => self::APPROVAL_APPROVED,
            'approved_by' => $this->approved_by,
            'approved_at' => time(),
        ));
    }

    public function rejectViaApi($reason)
    {
        $url = ApiEndpoints::url(ApiEndpoints::ATTENDEE_UPDATE, array('id' => $this->id));
        return ApiClient::post($url, array(
            'approval_status' => self::APPROVAL_REJECTED,
            'rejection_reason' => $reason,
            'approved_by' => $this->approved_by,
            'approved_at' => time(),
        ));
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
        foreach ($attendees as $att) {
            $status = isset($att['approval_status']) ? (int)$att['approval_status'] : self::APPROVAL_PENDING;
            // Chỉ reset những attendee bị từ chối (status=2), giữ nguyên đã duyệt (status=1)
            if ($status == self::APPROVAL_REJECTED) {
                $url = ApiEndpoints::url(ApiEndpoints::ATTENDEE_UPDATE, array('id' => $att['id']));
                $result = ApiClient::post($url, array(
                    'approval_status' => self::APPROVAL_PENDING,
                    'rejection_reason' => null,
                ));
                if (isset($result['success']) && $result['success']) {
                    $count++;
                } else {
                    $errors[] = $att['id'];
                }
            }
        }
        return array('success' => empty($errors), 'count' => $count, 'errors' => $errors);
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
        // Thêm các trường không nằm trong attributes
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
            'transport_id'
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
        Yii::log("Attendees storeViaApi - JSON data: " . json_encode($data), 'info', 'application.registration');

        return ApiClient::post(ApiEndpoints::ATTENDEE_STORE, $data);
    }

    public function updateViaApi()
    {
        $data = array_filter($this->attributes, function ($value) {
            return $value !== null && $value !== '';
        });
        // Thêm các trường không nằm trong attributes
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
            'transport_id'
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




    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::ATTENDEE_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    public static function getApiDataProvider($params = array(), $pageSize = 25)
    {
        return new ApiDataProvider(ApiEndpoints::ATTENDEE_LIST, array(
            'modelClass' => 'Attendees',
            'params' => $params,
            'pagination' => array('pageSize' => $pageSize),
        ));
    }
}
