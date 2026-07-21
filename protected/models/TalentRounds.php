<?php

Yii::import('application.models._base.BaseTalentRounds');

class TalentRounds extends BaseTalentRounds
{
    public $talent_show_name;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'talent_show_id' => 'Cuộc thi văn nghệ',
            'name' => 'Tên vòng thi',
            'round_type' => 'Loại vòng',
            'round_order' => 'Thứ tự',
            'max_score' => 'Điểm tối đa',
            'weight' => 'Trọng số',
            'start_time' => 'Thời gian bắt đầu',
            'end_time' => 'Thời gian kết thúc',
            'note' => 'Ghi chú',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
        );
    }

    public static function fetchFromApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::TALENT_ROUND_DETAIL, array('id' => $id));
        $result = ApiClient::get($url);
        if ($result['success'] && isset($result['data'])) {
            $data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
            $model = new self;
            $model->setAttributes($data, false);
            $model->talent_show_name = isset($data['talent_show_name']) ? $data['talent_show_name'] : '';
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
        return ApiClient::post(ApiEndpoints::TALENT_ROUND_STORE, $data);
    }

    public function updateViaApi()
    {
        $url = ApiEndpoints::url(ApiEndpoints::TALENT_ROUND_UPDATE, array('id' => $this->id));
        return ApiClient::post($url, $this->attributes);
    }

    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::TALENT_ROUND_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    public static function getApiDataProvider($params = array(), $pageSize = 10000)
    {
        return new ApiDataProvider(ApiEndpoints::TALENT_ROUND_LIST, array(
            'modelClass' => 'TalentRounds',
            'params' => $params,
            'pagination' => array('pageSize' => $pageSize),
        ));
    }

    public static function getRoundTypeOptions()
    {
        return array(
            'qualification' => 'Vòng loại',
            'semifinal' => 'Bán kết',
            'final' => 'Chung kết',
        );
    }

    public static function getRoundTypeLabel($type)
    {
        $labels = self::getRoundTypeOptions();
        return isset($labels[$type]) ? $labels[$type] : $type;
    }

    public static function getListForDropdown($talentShowId = null)
    {
        $params = array('per_page' => 100);
        if ($talentShowId) {
            $params['talent_show_id'] = $talentShowId;
        }
        $result = ApiClient::get(ApiEndpoints::TALENT_ROUND_LIST, $params);

        $list = array();
        if ($result['success'] && isset($result['data']['data'])) {
            foreach ($result['data']['data'] as $item) {
                $list[$item['id']] = $item['name'];
            }
        }
        return $list;
    }
}
