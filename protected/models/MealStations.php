<?php

/**
 * Model Trạm chấm ăn (mỗi cửa nhà hàng = 1 trạm, có mã truy cập riêng).
 * Carrier CFormModel — dữ liệu qua External API.
 */
class MealStations extends CFormModel
{
    public $id;
    public $event_id;
    public $name;
    public $code;
    public $location;
    public $passcode;
    public $is_active;
    public $has_passcode;
    public $created_at;
    public $updated_at;

    public function rules()
    {
        return array(
            array('event_id, name', 'required'),
            array('name, location', 'length', 'max' => 255),
            array('code', 'length', 'max' => 50),
            array('passcode', 'length', 'min' => 4, 'max' => 255),
            // Passcode bắt buộc khi tạo mới (không có id).
            array('passcode', 'required', 'on' => 'insert'),
            array('event_id, is_active', 'numerical', 'integerOnly' => true),
            array('id, event_id, name, code, location, passcode, is_active, has_passcode, created_at, updated_at', 'safe'),
        );
    }

    public function attributeLabels()
    {
        return array(
            'event_id'  => 'Sự kiện',
            'name'      => 'Tên trạm / cửa',
            'code'      => 'Mã ngắn',
            'location'  => 'Địa điểm',
            'passcode'  => 'Mã truy cập',
            'is_active' => 'Kích hoạt',
        );
    }

    public static function fetchFromApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::MEAL_STATION_DETAIL, array('id' => $id));
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
        return ApiClient::post(ApiEndpoints::MEAL_STATION_STORE, array(
            'event_id'  => $this->event_id,
            'name'      => $this->name,
            'code'      => $this->code,
            'location'  => $this->location,
            'passcode'  => $this->passcode,
            'is_active' => ($this->is_active === null || $this->is_active === '') ? 1 : $this->is_active,
        ));
    }

    public function updateViaApi()
    {
        $url = ApiEndpoints::url(ApiEndpoints::MEAL_STATION_UPDATE, array('id' => $this->id));
        $data = array(
            'event_id'  => $this->event_id,
            'name'      => $this->name,
            'code'      => $this->code,
            'location'  => $this->location,
            'is_active' => $this->is_active,
        );
        // Chỉ gửi passcode khi nhập mới (để trống = giữ nguyên).
        if ($this->passcode !== null && $this->passcode !== '') {
            $data['passcode'] = $this->passcode;
        }
        return ApiClient::post($url, $data);
    }

    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::MEAL_STATION_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    public static function getApiDataProvider($params = array(), $pageSize = 200)
    {
        return new ApiDataProvider(ApiEndpoints::MEAL_STATION_LIST, array(
            'modelClass' => 'MealStations',
            'params'     => $params,
            'pagination' => array('pageSize' => $pageSize),
        ));
    }
}
