<?php

Yii::import('application.models._base.BaseSportTeams');

class SportTeams extends BaseSportTeams
{
    const STATUS_PENDING = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_CANCELLED = 2;

    public $sport_name;
    public $property_name;
    public $alliance_org_names;
    public $member_count;
    public $team_name;
    public $is_alliance;
    public $alliance_property_ids;
    public $status;
    public $registration_id;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = array('sport_name, property_name, alliance_org_names, member_count, team_name, is_alliance, alliance_property_ids, status, registration_id', 'safe');
        return $rules;
    }

    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'event_id' => 'Sự kiện',
            'sport_id' => 'Môn thể thao',
            'property_id' => 'Đơn vị',
            'team_name' => 'Tên đội',
            'is_alliance' => 'Liên quân',
            'status' => 'Trạng thái',
            'registration_id' => 'Phiếu đăng ký',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
        );
    }

    public static function fetchFromApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::SPORT_TEAM_DETAIL, array('id' => $id));
        $result = ApiClient::get($url);
        if ($result['success'] && isset($result['data'])) {
            $data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
            $model = new self;
            $model->setAttributes($data, false);
            $model->sport_name = isset($data['sport_name']) ? $data['sport_name'] : '';
            $model->property_name = isset($data['property_name']) ? $data['property_name'] : '';
            $model->registration_id = isset($data['registration_id']) ? $data['registration_id'] : null;
            $model->id = $id;
            return $model;
        }
        return null;
    }

    public function storeViaApi()
    {
        $data = $this->attributes;
        $data['team_name'] = $this->team_name;
        $data['is_alliance'] = $this->is_alliance;
        $data['alliance_property_ids'] = $this->alliance_property_ids;
        $data['status'] = $this->status;
        $data['registration_id'] = $this->registration_id;

        $data = array_filter($data, function ($value) {
            return $value !== null && $value !== '';
        });
        return ApiClient::post(ApiEndpoints::SPORT_TEAM_STORE, $data);
    }

    public function updateViaApi()
    {
        $data = $this->attributes;
        $data['team_name'] = $this->team_name;
        $data['is_alliance'] = $this->is_alliance;
        $data['alliance_property_ids'] = $this->alliance_property_ids;
        $data['status'] = $this->status;
        $data['registration_id'] = $this->registration_id;

        $url = ApiEndpoints::url(ApiEndpoints::SPORT_TEAM_UPDATE, array('id' => $this->id));
        return ApiClient::post($url, $data);
    }

    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::SPORT_TEAM_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    public static function getApiDataProvider($params = array(), $pageSize = 25)
    {
        return new ApiDataProvider(ApiEndpoints::SPORT_TEAM_LIST, array(
            'modelClass' => 'SportTeams',
            'params' => $params,
            'pagination' => array('pageSize' => $pageSize),
        ));
    }

    public static function getStatusLabel($status)
    {
        $labels = array(
            self::STATUS_PENDING => '<span class="badge bg-warning text-dark">Chờ xác nhận</span>',
            self::STATUS_CONFIRMED => '<span class="badge bg-success">Đã xác nhận</span>',
            self::STATUS_CANCELLED => '<span class="badge bg-danger">Đã hủy</span>',
        );
        return isset($labels[$status]) ? $labels[$status] : $status;
    }

    public static function getStatusOptions()
    {
        return array(
            self::STATUS_PENDING => 'Chờ xác nhận',
            self::STATUS_CONFIRMED => 'Đã xác nhận',
            self::STATUS_CANCELLED => 'Đã hủy',
        );
    }
}
