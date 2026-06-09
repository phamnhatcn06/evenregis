<?php

Yii::import('application.models._base.BaseTalentCategories');

class TalentCategories extends BaseTalentCategories
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'code' => 'Mã',
            'name' => 'Tên thể loại',
            'min_members' => 'Số người tối thiểu',
            'max_members' => 'Số người tối đa',
            'description' => 'Mô tả',
            'is_active' => 'Trạng thái',
            'created_at' => 'Ngày tạo',
        );
    }

    public static function fetchFromApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::TALENT_CATEGORY_DETAIL, array('id' => $id));
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
        return ApiClient::post(ApiEndpoints::TALENT_CATEGORY_STORE, $data);
    }

    public function updateViaApi()
    {
        $url = ApiEndpoints::url(ApiEndpoints::TALENT_CATEGORY_UPDATE, array('id' => $this->id));
        return ApiClient::post($url, $this->attributes);
    }

    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::TALENT_CATEGORY_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    public static function getApiDataProvider($params = array(), $pageSize = 10000)
    {
        return new ApiDataProvider(ApiEndpoints::TALENT_CATEGORY_LIST, array(
            'modelClass' => 'TalentCategories',
            'params' => $params,
            'pagination' => array('pageSize' => $pageSize),
        ));
    }

    public static function getListForDropdown()
    {
        $list = array();
        $items = self::getApiDataProvider(array('is_active' => 1), 100)->getData();
        foreach ($items as $item) {
            $list[$item->id] = $item->name;
        }
        return $list;
    }

    public static function getCategoryTypes()
    {
        return array(
            'solo_singing' => 'Đơn ca',
            'group_singing' => 'Tốp ca',
            'solo_dance' => 'Múa đơn',
            'group_dance' => 'Múa nhóm',
            'instrument' => 'Nhạc cụ',
            'comedy' => 'Hài kịch',
            'other' => 'Khác',
        );
    }
}
