<?php

Yii::import('application.models._base.BaseCompetitionRegistrations');

class CompetitionRegistrations extends BaseCompetitionRegistrations
{
    const STATUS_PENDING = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_CANCELLED = 2;
    const STATUS_NO_SHOW = 3;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'competition_id' => 'Cuộc thi',
            'attendee_id' => 'Người tham dự',
            'candidate_number' => 'Số báo danh',
            'status' => 'Trạng thái',
            'registered_at' => 'Ngày đăng ký',
            'confirmed_by' => 'Người xác nhận',
            'confirmed_at' => 'Ngày xác nhận',
            'note' => 'Ghi chú',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
        );
    }

    public static function fetchFromApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::COMPETITION_REGISTRATION_DETAIL, array('id' => $id));
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
        return ApiClient::post(ApiEndpoints::COMPETITION_REGISTRATION_STORE, $data);
    }

    public function updateViaApi()
    {
        $url = ApiEndpoints::url(ApiEndpoints::COMPETITION_REGISTRATION_UPDATE, array('id' => $this->id));
        return ApiClient::post($url, $this->attributes);
    }

    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::COMPETITION_REGISTRATION_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    public static function getApiDataProvider($params = array(), $pageSize = 25)
    {
        return new ApiDataProvider(ApiEndpoints::COMPETITION_REGISTRATION_LIST, array(
            'modelClass' => 'CompetitionRegistrations',
            'params' => $params,
            'pagination' => array(
                'pageSize' => $pageSize,
            ),
        ));
    }

    public static function confirmViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::COMPETITION_REGISTRATION_CONFIRM, array('id' => $id));
        return ApiClient::post($url, array());
    }

    public static function getStatusLabel($status)
    {
        $labels = array(
            self::STATUS_PENDING => '<span class="badge bg-warning text-dark">Chờ xác nhận</span>',
            self::STATUS_CONFIRMED => '<span class="badge bg-success">Đã xác nhận</span>',
            self::STATUS_CANCELLED => '<span class="badge bg-danger">Đã hủy</span>',
            self::STATUS_NO_SHOW => '<span class="badge bg-secondary">Vắng mặt</span>',
        );
        return isset($labels[$status]) ? $labels[$status] : $status;
    }

    public static function getStatusOptions()
    {
        return array(
            self::STATUS_PENDING => 'Chờ xác nhận',
            self::STATUS_CONFIRMED => 'Đã xác nhận',
            self::STATUS_CANCELLED => 'Đã hủy',
            self::STATUS_NO_SHOW => 'Vắng mặt',
        );
    }
}
