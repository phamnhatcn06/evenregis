<?php

Yii::import('application.models._base.BaseTalentEntries');

class TalentEntries extends BaseTalentEntries
{
    const STATUS_PENDING = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_CANCELLED = 2;

    public $property_name;
    public $category_name;
    public $show_name;
    public $member_count;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'show_id' => 'Hội diễn',
            'property_id' => 'Đơn vị',
            'category_id' => 'Thể loại',
            'title' => 'Tên tiết mục',
            'description' => 'Mô tả',
            'duration_seconds' => 'Thời lượng (giây)',
            'music_path' => 'File nhạc',
            'participant_count' => 'Số người tham gia',
            'status' => 'Trạng thái',
            'performance_order' => 'Thứ tự biểu diễn',
            'created_at' => 'Ngày đăng ký',
            'updated_at' => 'Ngày cập nhật',
        );
    }

    public static function fetchFromApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::TALENT_ENTRY_DETAIL, array('id' => $id));
        $result = ApiClient::get($url);
        if ($result['success'] && isset($result['data'])) {
            $data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
            $model = new self;
            $model->setAttributes($data, false);
            $model->property_name = isset($data['property_name']) ? $data['property_name'] : '';
            $model->category_name = isset($data['category_name']) ? $data['category_name'] : '';
            $model->show_name = isset($data['show_name']) ? $data['show_name'] : '';
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
        return ApiClient::post(ApiEndpoints::TALENT_ENTRY_STORE, $data);
    }

    public function updateViaApi()
    {
        $url = ApiEndpoints::url(ApiEndpoints::TALENT_ENTRY_UPDATE, array('id' => $this->id));
        return ApiClient::post($url, $this->attributes);
    }

    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::TALENT_ENTRY_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    public static function getApiDataProvider($params = array(), $pageSize = 25)
    {
        return new ApiDataProvider(ApiEndpoints::TALENT_ENTRY_LIST, array(
            'modelClass' => 'TalentEntries',
            'params' => $params,
            'pagination' => array('pageSize' => $pageSize),
        ));
    }

    public static function getStatusLabel($status)
    {
        $labels = array(
            self::STATUS_PENDING => '<span class="badge bg-warning text-dark">Chờ duyệt</span>',
            self::STATUS_CONFIRMED => '<span class="badge bg-success">Đã xác nhận</span>',
            self::STATUS_CANCELLED => '<span class="badge bg-danger">Đã hủy</span>',
        );
        return isset($labels[$status]) ? $labels[$status] : $status;
    }

    public static function getStatusOptions()
    {
        return array(
            self::STATUS_PENDING => 'Chờ duyệt',
            self::STATUS_CONFIRMED => 'Đã xác nhận',
            self::STATUS_CANCELLED => 'Đã hủy',
        );
    }
}
