<?php

Yii::import('application.models._base.BaseTalentEntries');

class TalentEntries extends BaseTalentEntries
{
    const STATUS_DRAFT = 1;
    const STATUS_SUBMITTED = 2;
    const STATUS_APPROVED = 3;
    const STATUS_REJECTED = 4;
    const STATUS_PENDING = 5;

    public $property_name;
    public $category_name;
    public $show_name;
    public $member_count;
    public $registration_id;
    public $alliance_property_ids;

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
            'content' => 'Nội dung chi tiết',
            'duration_seconds' => 'Thời lượng (giây)',
            'music_path' => 'File nhạc',
            'video_path' => 'Video',
            'director' => 'Đạo diễn/Biên đạo',
            'director_phone' => 'SĐT đạo diễn',
            'origin' => 'Nguồn gốc/Xuất xứ',
            'document' => 'Tài liệu',
            'is_alliance_team' => 'Đội liên quân',
            'participant_count' => 'Số người tham gia',
            'status' => 'Trạng thái',
            'performance_order' => 'Thứ tự biểu diễn',
            'note' => 'Ghi chú',
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
        if ($this->registration_id) {
            $data['registration_id'] = $this->registration_id;
        }
        if ($this->alliance_property_ids) {
            $data['alliance_property_ids'] = $this->alliance_property_ids;
        }
        print_r(json_encode($data));die;
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
            self::STATUS_DRAFT => '<span class="badge bg-secondary">Nháp</span>',
            self::STATUS_SUBMITTED => '<span class="badge bg-info">Đã nộp</span>',
            self::STATUS_APPROVED => '<span class="badge bg-success">Đã duyệt</span>',
            self::STATUS_REJECTED => '<span class="badge bg-danger">Từ chối</span>',
            self::STATUS_PENDING => '<span class="badge bg-warning text-dark">Chờ xử lý</span>',
        );
        return isset($labels[$status]) ? $labels[$status] : '<span class="badge bg-secondary">Không xác định</span>';
    }

    public static function getStatusOptions()
    {
        return array(
            self::STATUS_DRAFT => 'Nháp',
            self::STATUS_SUBMITTED => 'Đã nộp',
            self::STATUS_APPROVED => 'Đã duyệt',
            self::STATUS_REJECTED => 'Từ chối',
            self::STATUS_PENDING => 'Chờ xử lý',
        );
    }
}
