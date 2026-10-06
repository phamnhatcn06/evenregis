<?php

/**
 * Model nội dung chạy (Run event). FE không có DB trực tiếp — dùng CFormModel làm carrier,
 * mọi dữ liệu qua External API theo chuẩn MVC của dự án.
 */
class RunEvents extends CFormModel
{
    const STATUS_OPEN = 'open';
    const STATUS_CLOSED = 'closed';

    public $id;
    public $event_id;
    public $name;
    public $code;
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
            array('name, code, quota', 'required'),
            array('quota, registered_count, sort_order, is_active, open_at, close_at, cancel_until, event_id', 'numerical', 'integerOnly' => true),
            array('name', 'length', 'max' => 255),
            array('code', 'length', 'max' => 30),
            array('status', 'in', 'range' => array(self::STATUS_OPEN, self::STATUS_CLOSED)),
            // Cho phép gán tự do khi map từ API
            array('id, event_id, name, code, quota, registered_count, remaining, open_at, close_at, cancel_until, status, sort_order, is_active, created_at, updated_at', 'safe'),
        );
    }

    public function attributeLabels()
    {
        return array(
            'name'             => 'Tên nội dung',
            'code'             => 'Mã (prefix BIB)',
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
        $url = ApiEndpoints::url(ApiEndpoints::RUN_EVENT_DETAIL, array('id' => $id));
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
            'event_id'   => $this->event_id,
            'name'       => $this->name,
            'code'       => $this->code,
            'quota'      => $this->quota,
            'open_at'    => $this->open_at,
            'close_at'   => $this->close_at,
            'status'     => $this->status ?: self::STATUS_OPEN,
            'sort_order' => $this->sort_order ?: 0,
            'is_active'  => ($this->is_active === null || $this->is_active === '') ? 1 : $this->is_active,
        );
        return ApiClient::post(ApiEndpoints::RUN_EVENT_STORE, $data);
    }

    public function updateViaApi()
    {
        $url = ApiEndpoints::url(ApiEndpoints::RUN_EVENT_UPDATE, array('id' => $this->id));
        $data = array(
            'event_id'   => $this->event_id,
            'name'       => $this->name,
            'code'       => $this->code,
            'quota'      => $this->quota,
            'open_at'    => $this->open_at,
            'close_at'   => $this->close_at,
            'status'     => $this->status,
            'sort_order' => $this->sort_order,
            'is_active'  => $this->is_active,
        );
        return ApiClient::post($url, $data);
    }

    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::RUN_EVENT_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    public static function getApiDataProvider($params = array(), $pageSize = 1000)
    {
        return new ApiDataProvider(ApiEndpoints::RUN_EVENT_LIST, array(
            'modelClass' => 'RunEvents',
            'params' => $params,
            'pagination' => array('pageSize' => $pageSize),
        ));
    }

    /** Danh sách nội dung đang mở (cho cổng công khai). Trả mảng assoc. */
    public static function listOpen($eventId = null)
    {
        $params = array();
        if ($eventId !== null) {
            $params['event_id'] = $eventId;
        }
        $result = ApiClient::get(ApiEndpoints::RUN_EVENT_LIST_OPEN, $params);
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
