<?php

/**
 * Model đợt đi tham quan (Tour session). FE không có DB trực tiếp — CFormModel carrier,
 * mọi dữ liệu qua External API theo chuẩn MVC của dự án.
 */
class TourSessions extends CFormModel
{
    const STATUS_OPEN = 'open';
    const STATUS_CLOSED = 'closed';

    public $id;
    public $event_id;
    public $name;
    public $start_time;
    public $quota;
    public $registered_count;
    public $remaining;
    public $open_at;
    public $close_at;
    public $cancel_until;
    public $status;
    public $sort_order;
    public $is_active;
    public $created_at;
    public $updated_at;

    public function rules()
    {
        return array(
            array('name, quota', 'required'),
            array('quota, registered_count, sort_order, is_active, open_at, close_at, cancel_until, event_id', 'numerical', 'integerOnly' => true),
            array('name', 'length', 'max' => 255),
            array('start_time', 'length', 'max' => 100),
            array('status', 'in', 'range' => array(self::STATUS_OPEN, self::STATUS_CLOSED)),
            array('id, event_id, name, start_time, quota, registered_count, remaining, open_at, close_at, cancel_until, status, sort_order, is_active, created_at, updated_at', 'safe'),
        );
    }

    public function attributeLabels()
    {
        return array(
            'name'             => 'Tên đợt',
            'start_time'       => 'Khung giờ',
            'quota'            => 'Giới hạn số lượng',
            'registered_count' => 'Đã đăng ký',
            'remaining'        => 'Còn lại',
            'open_at'          => 'Mở lúc',
            'close_at'         => 'Đóng lúc',
            'cancel_until'     => 'Hạn chót xin hủy',
            'status'           => 'Trạng thái',
            'sort_order'       => 'Thứ tự',
            'is_active'        => 'Kích hoạt',
        );
    }

    public static function fetchFromApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::TOUR_SESSION_DETAIL, array('id' => $id));
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
        $data = array(
            'event_id'     => $this->event_id,
            'name'         => $this->name,
            'start_time'   => $this->start_time,
            'quota'        => $this->quota,
            'open_at'      => $this->open_at,
            'close_at'     => $this->close_at,
            'cancel_until' => $this->cancel_until,
            'status'       => $this->status ?: self::STATUS_OPEN,
            'sort_order'   => $this->sort_order ?: 0,
            'is_active'    => ($this->is_active === null || $this->is_active === '') ? 1 : $this->is_active,
        );
        return ApiClient::post(ApiEndpoints::TOUR_SESSION_STORE, $data);
    }

    public function updateViaApi()
    {
        $url = ApiEndpoints::url(ApiEndpoints::TOUR_SESSION_UPDATE, array('id' => $this->id));
        $data = array(
            'event_id'     => $this->event_id,
            'name'         => $this->name,
            'start_time'   => $this->start_time,
            'quota'        => $this->quota,
            'open_at'      => $this->open_at,
            'close_at'     => $this->close_at,
            'cancel_until' => $this->cancel_until,
            'status'       => $this->status,
            'sort_order'   => $this->sort_order,
            'is_active'    => $this->is_active,
        );
        return ApiClient::post($url, $data);
    }

    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::TOUR_SESSION_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    public static function getApiDataProvider($params = array(), $pageSize = 1000)
    {
        return new ApiDataProvider(ApiEndpoints::TOUR_SESSION_LIST, array(
            'modelClass' => 'TourSessions',
            'params' => $params,
            'pagination' => array('pageSize' => $pageSize),
        ));
    }

    /** Danh sách đợt đang mở (cho cổng công khai). Trả mảng assoc. */
    public static function listOpen($eventId = null)
    {
        $params = array();
        if ($eventId !== null) {
            $params['event_id'] = $eventId;
        }
        $result = ApiClient::get(ApiEndpoints::TOUR_SESSION_LIST_OPEN, $params);
        if ($result['success'] && isset($result['data']['data'])) {
            return $result['data']['data'];
        }
        return array();
    }

    public static function getStatusLabel($status)
    {
        $labels = array(
            self::STATUS_OPEN   => '<span class="badge bg-success">Đang mở</span>',
            self::STATUS_CLOSED => '<span class="badge bg-secondary">Đã đóng</span>',
        );
        return isset($labels[$status]) ? $labels[$status] : $status;
    }
}
