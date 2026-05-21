<?php

Yii::import('application.models._base.BaseBeautyContests');

class BeautyContests extends BaseBeautyContests
{
    public $event_name;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'event_id' => 'Sự kiện',
            'name' => 'Tên cuộc thi',
            'description' => 'Mô tả',
            'gender' => 'Giới tính',
            'age_min' => 'Tuổi tối thiểu',
            'age_max' => 'Tuổi tối đa',
            'registration_open_at' => 'Mở đăng ký',
            'registration_close_at' => 'Đóng đăng ký',
            'contest_date' => 'Ngày thi',
            'location' => 'Địa điểm',
            'candidate_prefix' => 'Tiền tố SBD',
            'candidate_start' => 'SBD bắt đầu',
            'max_per_org' => 'Tối đa/đơn vị',
            'is_active' => 'Trạng thái',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
        );
    }

    public static function fetchFromApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::BEAUTY_CONTEST_DETAIL, array('id' => $id));
        $result = ApiClient::get($url);
        if ($result['success'] && isset($result['data'])) {
            $data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
            $model = new self;
            $model->setAttributes($data, false);
            $model->event_name = isset($data['event_name']) ? $data['event_name'] : '';
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
        return ApiClient::post(ApiEndpoints::BEAUTY_CONTEST_STORE, $data);
    }

    public function updateViaApi()
    {
        $url = ApiEndpoints::url(ApiEndpoints::BEAUTY_CONTEST_UPDATE, array('id' => $this->id));
        return ApiClient::post($url, $this->attributes);
    }

    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::BEAUTY_CONTEST_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    public static function getApiDataProvider($params = array(), $pageSize = 25)
    {
        return new ApiDataProvider(ApiEndpoints::BEAUTY_CONTEST_LIST, array(
            'modelClass' => 'BeautyContests',
            'params' => $params,
            'pagination' => array('pageSize' => $pageSize),
        ));
    }

    public static function getGenderOptions()
    {
        return array(
            'female' => 'Nữ',
            'male' => 'Nam',
        );
    }

    public static function getGenderLabel($gender)
    {
        $labels = self::getGenderOptions();
        return isset($labels[$gender]) ? $labels[$gender] : $gender;
    }

    public static function getActiveLabel($isActive)
    {
        return $isActive ? '<span class="badge bg-success">Hoạt động</span>' : '<span class="badge bg-secondary">Tạm dừng</span>';
    }

    public static function getListForDropdown($eventId = null)
    {
        $params = array('is_active' => 1, 'per_page' => 100);
        if ($eventId) {
            $params['event_id'] = $eventId;
        }
        $result = ApiClient::get(ApiEndpoints::BEAUTY_CONTEST_LIST, $params);

        $list = array();
        if ($result['success'] && isset($result['data']['data'])) {
            foreach ($result['data']['data'] as $item) {
                $list[$item['id']] = $item['name'];
            }
        }
        return $list;
    }
}
