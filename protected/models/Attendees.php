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

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = array('cccd_front_path, cccd_back_path, portrait_path, contract_path', 'length', 'max' => 500);
        $rules[] = array('cccd_front_path, cccd_back_path, portrait_path, contract_path', 'safe');
        return $rules;
    }

    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        $labels['cccd_front_path'] = Yii::t('app', 'Ảnh CCCD mặt trước');
        $labels['cccd_back_path'] = Yii::t('app', 'Ảnh CCCD mặt sau');
        $labels['portrait_path'] = Yii::t('app', 'Ảnh chân dung');
        $labels['contract_path'] = Yii::t('app', 'Hợp đồng lao động');
        return $labels;
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
        return ApiClient::post(ApiEndpoints::ATTENDEE_STORE, $data);
    }

    public function updateViaApi()
    {
        $data = array_filter($this->attributes, function ($value) {
            return $value !== null && $value !== '';
        });
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