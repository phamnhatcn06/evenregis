<?php

/**
 * Model Bữa ăn. FE không truy vấn DB trực tiếp — dùng CFormModel làm carrier,
 * mọi dữ liệu đi qua External API (chuẩn MVC của dự án).
 *
 * Bữa ăn được sinh TỰ ĐỘNG từ agenda (sáng/trưa/tối). BTC chỉ cấu hình thêm
 * khung giờ chấm (checkin_start/checkin_end), địa điểm và bật/tắt.
 */
class Meals extends CFormModel
{
    const TYPE_BREAKFAST = 'breakfast';
    const TYPE_LUNCH = 'lunch';
    const TYPE_DINNER = 'dinner';
    const TYPE_SNACK = 'snack';
    const TYPE_BANQUET = 'banquet';

    public $id;
    public $event_id;
    public $agenda_id;
    public $name;
    public $meal_type;
    public $meal_date;
    public $serving_time;
    public $checkin_start;
    public $checkin_end;
    public $cutoff_deadline;
    public $location;
    public $total_count;
    public $description;
    public $is_active;
    public $created_at;
    public $updated_at;

    public function rules()
    {
        return array(
            array('name, meal_date, meal_type', 'required'),
            array('name, location', 'length', 'max' => 255),
            array('event_id, agenda_id, total_count, is_active', 'numerical', 'integerOnly' => true),
            array('id, event_id, agenda_id, name, meal_type, meal_date, serving_time, checkin_start, checkin_end, cutoff_deadline, location, total_count, description, is_active, created_at, updated_at', 'safe'),
        );
    }

    public function attributeLabels()
    {
        return array(
            'name'          => 'Tên bữa ăn',
            'meal_type'     => 'Loại bữa',
            'meal_date'     => 'Ngày',
            'serving_time'  => 'Giờ phục vụ',
            'checkin_start' => 'Bắt đầu chấm ăn',
            'checkin_end'   => 'Kết thúc chấm ăn',
            'location'      => 'Địa điểm',
            'is_active'     => 'Kích hoạt',
        );
    }

    public static function fetchFromApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::MEAL_DETAIL, array('id' => $id));
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
        return ApiClient::post(ApiEndpoints::MEAL_STORE, array(
            'event_id'      => $this->event_id,
            'name'          => $this->name,
            'meal_type'     => $this->meal_type,
            'meal_date'     => $this->meal_date,
            'serving_time'  => $this->serving_time ?: null,
            'checkin_start' => $this->checkin_start ?: null,
            'checkin_end'   => $this->checkin_end ?: null,
            'location'      => $this->location,
            'is_active'     => ($this->is_active === null || $this->is_active === '') ? 1 : $this->is_active,
        ));
    }

    public function updateViaApi()
    {
        $url = ApiEndpoints::url(ApiEndpoints::MEAL_UPDATE, array('id' => $this->id));
        return ApiClient::post($url, array(
            'event_id'      => $this->event_id,
            'name'          => $this->name,
            'meal_type'     => $this->meal_type,
            'meal_date'     => $this->meal_date,
            'serving_time'  => $this->serving_time,
            'checkin_start' => $this->checkin_start ?: null,
            'checkin_end'   => $this->checkin_end ?: null,
            'location'      => $this->location,
            'is_active'     => ($this->is_active === null || $this->is_active === '') ? 1 : $this->is_active,
        ));
    }

    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::MEAL_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    public static function getApiDataProvider($params = array(), $pageSize = 200)
    {
        return new ApiDataProvider(ApiEndpoints::MEAL_LIST, array(
            'modelClass' => 'Meals',
            'params'     => $params,
            'pagination' => array('pageSize' => $pageSize),
        ));
    }

    /** Sinh/đồng bộ bữa ăn từ agenda của sự kiện. Trả mảng kết quả API. */
    public static function syncFromAgenda($eventId)
    {
        return ApiClient::post(ApiEndpoints::MEAL_SYNC_FROM_AGENDA, array('event_id' => $eventId));
    }

    public static function getTypeLabel($type)
    {
        $labels = array(
            self::TYPE_BREAKFAST => 'Ăn sáng',
            self::TYPE_LUNCH     => 'Ăn trưa',
            self::TYPE_DINNER    => 'Ăn tối',
            self::TYPE_SNACK     => 'Ăn nhẹ',
            self::TYPE_BANQUET   => 'Tiệc',
        );
        return isset($labels[$type]) ? $labels[$type] : $type;
    }
}
