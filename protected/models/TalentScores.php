<?php

Yii::import('application.models._base.BaseTalentScores');

class TalentScores extends BaseTalentScores
{
    public $entry_title;
    public $judge_name;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'entry_id' => 'Tiết mục',
            'judge_id' => 'Giám khảo',
            'score' => 'Điểm',
            'criteria' => 'Tiêu chí',
            'note' => 'Nhận xét',
            'scored_at' => 'Thời gian chấm',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
        );
    }

    public static function fetchFromApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::TALENT_SCORE_DETAIL, array('id' => $id));
        $result = ApiClient::get($url);
        if ($result['success'] && isset($result['data'])) {
            $data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
            $model = new self;
            $model->setAttributes($data, false);
            $model->entry_title = isset($data['entry_title']) ? $data['entry_title'] : '';
            $model->judge_name = isset($data['judge_name']) ? $data['judge_name'] : '';
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
        return ApiClient::post(ApiEndpoints::TALENT_SCORE_STORE, $data);
    }

    public function updateViaApi()
    {
        $url = ApiEndpoints::url(ApiEndpoints::TALENT_SCORE_UPDATE, array('id' => $this->id));
        return ApiClient::post($url, $this->attributes);
    }

    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::TALENT_SCORE_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    public static function getApiDataProvider($params = array(), $pageSize = 10000)
    {
        return new ApiDataProvider(ApiEndpoints::TALENT_SCORE_LIST, array(
            'modelClass' => 'TalentScores',
            'params' => $params,
            'pagination' => array('pageSize' => $pageSize),
        ));
    }

    /**
     * Lấy danh sách điểm của một tiết mục
     * @param string $entryId
     * @return TalentScores[]
     */
    public static function getByEntry($entryId)
    {
        return self::getApiDataProvider(array('entry_id' => $entryId), 500)->getData();
    }

    /**
     * Tính điểm trung bình từ danh sách điểm
     * @param TalentScores[] $scores
     * @return float|null
     */
    public static function computeAverage($scores)
    {
        if (empty($scores)) {
            return null;
        }
        $total = 0;
        $count = 0;
        foreach ($scores as $s) {
            if ($s->score !== null && $s->score !== '') {
                $total += (float)$s->score;
                $count++;
            }
        }
        return $count > 0 ? round($total / $count, 2) : null;
    }
}
