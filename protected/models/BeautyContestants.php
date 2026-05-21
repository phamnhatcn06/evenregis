<?php

Yii::import('application.models._base.BaseBeautyContestants');

class BeautyContestants extends BaseBeautyContestants
{
    const STATUS_REGISTERED = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_DISQUALIFIED = 2;

    public $attendee_name;
    public $property_name;
    public $contest_name;
    public $registration_id;
    public $note;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function rules()
    {
        return array(
            array('contest_id, attendee_id', 'required'),
            array('final_rank', 'numerical', 'integerOnly' => true),
            array('height_cm, weight_kg', 'numerical'),
            array('contest_id, attendee_id, candidate_number', 'length', 'max' => 20),
            array('measurements', 'length', 'max' => 50),
            array('talent, award', 'length', 'max' => 255),
            array('photo_portrait, photo_full_body', 'length', 'max' => 500),
            array('status', 'length', 'max' => 12),
            array('candidate_number, height_cm, weight_kg, measurements, talent, bio, photo_portrait, photo_full_body, award, final_rank, registered_at, status', 'safe'),
        );
    }

    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'contest_id' => 'Cuộc thi',
            'attendee_id' => 'Thí sinh',
            'contestant_number' => 'Số báo danh',
            'height_cm' => 'Chiều cao (cm)',
            'weight_kg' => 'Cân nặng (kg)',
            'measurements' => 'Số đo 3 vòng',
            'talent' => 'Tài năng',
            'bio' => 'Tiểu sử',
            'photo_portrait' => 'Ảnh chân dung',
            'photo_full_body' => 'Ảnh toàn thân',
            'status' => 'Trạng thái',
            'created_at' => 'Ngày đăng ký',
            'updated_at' => 'Ngày cập nhật',
        );
    }

    public static function fetchFromApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::BEAUTY_CONTESTANT_DETAIL, array('id' => $id));
        $result = ApiClient::get($url);
        if ($result['success'] && isset($result['data'])) {
            $data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
            $model = new self;
            $model->setAttributes($data, false);
            $model->attendee_name = isset($data['attendee_name']) ? $data['attendee_name'] : '';
            $model->property_name = isset($data['property_name']) ? $data['property_name'] : '';
            $model->contest_name = isset($data['contest_name']) ? $data['contest_name'] : '';
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
        if ($this->registration_id) {
            $data['registration_id'] = $this->registration_id;
        }
        if ($this->note) {
            $data['note'] = $this->note;
        }
        return ApiClient::post(ApiEndpoints::BEAUTY_CONTESTANT_STORE, $data);
    }

    public function updateViaApi()
    {
        $url = ApiEndpoints::url(ApiEndpoints::BEAUTY_CONTESTANT_UPDATE, array('id' => $this->id));
        return ApiClient::post($url, $this->attributes);
    }

    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::BEAUTY_CONTESTANT_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    public static function getApiDataProvider($params = array(), $pageSize = 25)
    {
        return new ApiDataProvider(ApiEndpoints::BEAUTY_CONTESTANT_LIST, array(
            'modelClass' => 'BeautyContestants',
            'params' => $params,
            'pagination' => array('pageSize' => $pageSize),
        ));
    }

    public static function getStatusLabel($status)
    {
        $labels = array(
            self::STATUS_REGISTERED => '<span class="badge bg-info">Đã đăng ký</span>',
            self::STATUS_CONFIRMED => '<span class="badge bg-success">Đã xác nhận</span>',
            self::STATUS_DISQUALIFIED => '<span class="badge bg-danger">Loại</span>',
        );
        return isset($labels[$status]) ? $labels[$status] : $status;
    }

    public static function getStatusOptions()
    {
        return array(
            self::STATUS_REGISTERED => 'Đã đăng ký',
            self::STATUS_CONFIRMED => 'Đã xác nhận',
            self::STATUS_DISQUALIFIED => 'Loại',
        );
    }
}
