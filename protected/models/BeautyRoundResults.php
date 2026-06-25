<?php

Yii::import('application.models._base.BaseBeautyRoundResults');

class BeautyRoundResults extends BaseBeautyRoundResults
{
    const STATUS_PENDING = 0;
    const STATUS_QUALIFIED = 1;
    const STATUS_ELIMINATED = 2;

    public $contestant_name;
    public $contestant_number;
    public $property_name;
    public $photo_portrait;
    public $status;
    public $rank_in_round;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'registration_id' => 'Thí sinh',
            'round_id' => 'Vòng thi',
            'score' => 'Điểm',
            'status' => 'Trạng thái',
            'rank_in_round' => 'Xếp hạng',
            'note' => 'Ghi chú',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
        );
    }

    public static function fetchFromApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::BEAUTY_ROUND_RESULT_DETAIL, array('id' => $id));
        $result = ApiClient::get($url);
        if ($result['success'] && isset($result['data'])) {
            $data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
            $model = new self;
            $model->setAttributes($data, false);
            $model->contestant_name = isset($data['contestant_name']) ? $data['contestant_name'] : '';
            $model->contestant_number = isset($data['contestant_number']) ? $data['contestant_number'] : '';
            $model->property_name = isset($data['property_name']) ? $data['property_name'] : '';
            $model->photo_portrait = isset($data['photo_portrait']) ? $data['photo_portrait'] : '';
            $model->status = isset($data['status']) ? $data['status'] : self::STATUS_PENDING;
            $model->rank_in_round = isset($data['rank_in_round']) ? $data['rank_in_round'] : null;
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
        return ApiClient::post(ApiEndpoints::BEAUTY_ROUND_RESULT_STORE, $data);
    }

    public function updateViaApi()
    {
        $url = ApiEndpoints::url(ApiEndpoints::BEAUTY_ROUND_RESULT_UPDATE, array('id' => $this->id));
        return ApiClient::post($url, $this->attributes);
    }

    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::BEAUTY_ROUND_RESULT_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    public static function getApiDataProvider($params = array(), $pageSize = 10000)
    {
        return new ApiDataProvider(ApiEndpoints::BEAUTY_ROUND_RESULT_LIST, array(
            'modelClass' => 'BeautyRoundResults',
            'params' => $params,
            'pagination' => array('pageSize' => $pageSize),
        ));
    }

    public static function getAvailableContestants($roundId)
    {
        $result = ApiClient::get(ApiEndpoints::BEAUTY_ROUND_RESULT_AVAILABLE_CONTESTANTS, array(
            'round_id' => $roundId,
        ));
        if ($result['success'] && isset($result['data'])) {
            return isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
        }
        return array();
    }

    public static function assignContestants($roundId, $registrationIds)
    {
        return ApiClient::post(ApiEndpoints::BEAUTY_ROUND_RESULT_ASSIGN, array(
            'round_id' => $roundId,
            'registration_ids' => $registrationIds,
        ));
    }

    public static function qualifyContestants($roundId, $results, $nextRoundId = null)
    {
        $data = array(
            'round_id' => $roundId,
            'results' => $results,
        );
        if ($nextRoundId) {
            $data['next_round_id'] = $nextRoundId;
        }
        return ApiClient::post(ApiEndpoints::BEAUTY_ROUND_RESULT_QUALIFY, $data);
    }

    public static function getRanking($roundId)
    {
        $result = ApiClient::get(ApiEndpoints::BEAUTY_ROUND_RESULT_RANKING, array(
            'round_id' => $roundId,
        ));
        if ($result['success'] && isset($result['data'])) {
            return isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
        }
        return array();
    }

    public static function getStatusLabel($status)
    {
        $labels = array(
            self::STATUS_PENDING => '<span class="badge bg-secondary">Chờ chấm</span>',
            self::STATUS_QUALIFIED => '<span class="badge bg-success">Đi tiếp</span>',
            self::STATUS_ELIMINATED => '<span class="badge bg-danger">Bị loại</span>',
        );
        return isset($labels[$status]) ? $labels[$status] : $status;
    }

    public static function getStatusOptions()
    {
        return array(
            self::STATUS_PENDING => 'Chờ chấm',
            self::STATUS_QUALIFIED => 'Đi tiếp',
            self::STATUS_ELIMINATED => 'Bị loại',
        );
    }
}