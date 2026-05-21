<?php

Yii::import('application.models._base.BaseSportTeamMembers');

class SportTeamMembers extends BaseSportTeamMembers
{
    const MAX_SPORTS_PER_ATTENDEE = 3;

    public $attendee_name;
    public $sport_name;
    public $team_name;
    public $property_name;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'sport_team_id' => 'Đội',
            'attendee_id' => 'Người tham dự',
            'jersey_number' => 'Số áo',
            'position' => 'Vị trí',
            'is_captain' => 'Đội trưởng',
            'created_at' => 'Ngày tạo',
        );
    }

    public static function fetchFromApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::SPORT_TEAM_MEMBER_DETAIL, array('id' => $id));
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
        $data = $this->attributes;
        $data['sport_team_id'] = $this->sport_team_id;
        $data['attendee_id'] = $this->attendee_id;
        $data['code'] = $this->code;
        $data['name'] = $this->name;

        $data = array_filter($data, function ($value) {
            return $value !== null && $value !== '';
        });
        return ApiClient::post(ApiEndpoints::SPORT_TEAM_MEMBER_STORE, $data);
    }

    public function updateViaApi()
    {
        $url = ApiEndpoints::url(ApiEndpoints::SPORT_TEAM_MEMBER_UPDATE, array('id' => $this->id));
        return ApiClient::post($url, $this->attributes);
    }

    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::SPORT_TEAM_MEMBER_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    public static function getApiDataProvider($params = array(), $pageSize = 25)
    {
        return new ApiDataProvider(ApiEndpoints::SPORT_TEAM_MEMBER_LIST, array(
            'modelClass' => 'SportTeamMembers',
            'params' => $params,
            'pagination' => array('pageSize' => $pageSize),
        ));
    }

    public static function countSportsByAttendee($attendeeId)
    {
        $url = ApiEndpoints::url(ApiEndpoints::SPORT_TEAM_MEMBER_COUNT_BY_ATTENDEE, array('attendee_id' => $attendeeId));
        $result = ApiClient::get($url);
        if ($result['success'] && isset($result['data']['count'])) {
            return (int) $result['data']['count'];
        }
        return 0;
    }

    public static function canRegisterMore($attendeeId)
    {
        return self::countSportsByAttendee($attendeeId) < self::MAX_SPORTS_PER_ATTENDEE;
    }
}
