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
        $round = BeautyRounds::fetchFromApi($roundId);
        if (!$round || !$round->contest_id) {
            return array();
        }

        $contestantsResult = ApiClient::get(ApiEndpoints::BEAUTY_CONTESTANT_LIST, array(
            'contest_id' => $round->contest_id,
            'per_page' => 1000,
        ));

        if (!$contestantsResult['success'] || !isset($contestantsResult['data']['data'])) {
            return array();
        }

        $allContestants = $contestantsResult['data']['data'];

        $assignedResult = ApiClient::get(ApiEndpoints::BEAUTY_ROUND_RESULT_LIST, array(
            'round_id' => $roundId,
            'per_page' => 1000,
        ));

        $assignedIds = array();
        if ($assignedResult['success'] && isset($assignedResult['data']['data'])) {
            foreach ($assignedResult['data']['data'] as $r) {
                $assignedIds[$r['registration_id']] = true;
            }
        }

        $available = array();
        foreach ($allContestants as $c) {
            if (!isset($assignedIds[$c['id']])) {
                $available[] = array(
                    'registration_id' => $c['id'],
                    'contestant_number' => isset($c['candidate_number']) ? $c['candidate_number'] : '',
                    'contestant_name' => isset($c['attendee_name']) ? $c['attendee_name'] : '',
                    'property_name' => isset($c['property_name']) ? $c['property_name'] : '',
                    'photo_portrait' => isset($c['photo_portrait']) ? $c['photo_portrait'] : '',
                );
            }
        }

        return $available;
    }

    public static function assignContestants($roundId, $registrationIds)
    {
        $url = ApiEndpoints::url(ApiEndpoints::BEAUTY_ROUND_RESULT_ASSIGN, array('round_id' => $roundId));
        return ApiClient::post($url, array('registration_ids' => $registrationIds));
    }

    public static function qualifyContestants($roundId, $registrationIds, $nextRoundId = null)
    {
        $url = ApiEndpoints::url(ApiEndpoints::BEAUTY_ROUND_RESULT_QUALIFY, array('round_id' => $roundId));
        $data = array('registration_ids' => $registrationIds);
        if ($nextRoundId) {
            $data['next_round_id'] = $nextRoundId;
        }
        return ApiClient::post($url, $data);
    }

    public static function getRanking($roundId)
    {
        $url = ApiEndpoints::url(ApiEndpoints::BEAUTY_ROUND_RESULT_RANKING, array('round_id' => $roundId));
        $result = ApiClient::get($url);
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