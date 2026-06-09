<?php

Yii::import('application.models._base.BaseTalentShows');

class TalentShows extends BaseTalentShows
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
            'registration_open_at' => 'Mở đăng ký',
            'registration_close_at' => 'Đóng đăng ký',
            'show_date' => 'Ngày biểu diễn',
            'location' => 'Địa điểm',
            'max_entries_per_org' => 'Tối đa tiết mục/đơn vị',
            'is_active' => 'Trạng thái',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
        );
    }

    public static function fetchFromApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::TALENT_SHOW_DETAIL, array('id' => $id));
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
        return ApiClient::post(ApiEndpoints::TALENT_SHOW_STORE, $data);
    }

    public function updateViaApi()
    {
        $url = ApiEndpoints::url(ApiEndpoints::TALENT_SHOW_UPDATE, array('id' => $this->id));
        return ApiClient::post($url, $this->attributes);
    }

    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::TALENT_SHOW_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    public static function getApiDataProvider($params = array(), $pageSize = 10000)
    {
        return new ApiDataProvider(ApiEndpoints::TALENT_SHOW_LIST, array(
            'modelClass' => 'TalentShows',
            'params' => $params,
            'pagination' => array('pageSize' => $pageSize),
        ));
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
        $result = ApiClient::get(ApiEndpoints::TALENT_SHOW_LIST, $params);

        $list = array();
        if ($result['success'] && isset($result['data']['data'])) {
            foreach ($result['data']['data'] as $item) {
                $list[$item['id']] = $item['name'];
            }
        }
        return $list;
    }
}
