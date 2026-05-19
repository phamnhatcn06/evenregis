<?php

Yii::import('application.models._base.BaseTalentEntryMembers');

class TalentEntryMembers extends BaseTalentEntryMembers
{
    public $attendee_name;
    public $entry_title;
    public $property_name;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'entry_id' => 'Tiết mục',
            'attendee_id' => 'Người tham gia',
            'role' => 'Vai trò',
            'created_at' => 'Ngày thêm',
        );
    }

    public static function fetchFromApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::TALENT_ENTRY_MEMBER_DETAIL, array('id' => $id));
        $result = ApiClient::get($url);
        if ($result['success'] && isset($result['data'])) {
            $data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
            $model = new self;
            $model->setAttributes($data, false);
            $model->attendee_name = isset($data['attendee_name']) ? $data['attendee_name'] : '';
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
        return ApiClient::post(ApiEndpoints::TALENT_ENTRY_MEMBER_STORE, $data);
    }

    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::TALENT_ENTRY_MEMBER_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    public static function getApiDataProvider($params = array(), $pageSize = 25)
    {
        return new ApiDataProvider(ApiEndpoints::TALENT_ENTRY_MEMBER_LIST, array(
            'modelClass' => 'TalentEntryMembers',
            'params' => $params,
            'pagination' => array('pageSize' => $pageSize),
        ));
    }
}
