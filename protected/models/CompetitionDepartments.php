<?php

Yii::import('application.models._base.BaseCompetitionDepartments');

class CompetitionDepartments extends BaseCompetitionDepartments
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function beforeSave()
    {
        if ($this->isNewRecord && !$this->created_at) {
            $this->created_at = time();
        }
        return parent::beforeSave();
    }

    /**
     * Lấy danh sách department_code của một competition (từ API)
     */
    public static function getDepartmentCodes($competitionId)
    {
        $codes = array();
        $dataProvider = self::getApiDataProvider(array('competition_id' => $competitionId), 100);
        $models = $dataProvider->getData();
        foreach ($models as $model) {
            $codes[] = $model->department_code;
        }
        return $codes;
    }

    /**
     * Kiểm tra department_code có được phép tham gia competition không
     */
    public static function isAllowed($competitionId, $departmentCode)
    {
        if (empty($departmentCode)) {
            return false;
        }

        return self::model()->exists(
            'competition_id = :cid AND department_code = :dcode',
            array(':cid' => $competitionId, ':dcode' => $departmentCode)
        );
    }

    /**
     * Đồng bộ danh sách department_code cho competition (qua API)
     */
    public static function syncDepartments($competitionId, $departmentCodes)
    {
        // Xóa tất cả department cũ qua API
        $existing = self::getApiDataProvider(array('competition_id' => $competitionId), 100)->getData();
        foreach ($existing as $item) {
            self::deleteViaApi($item->id);
        }

        // Thêm mới qua API
        foreach ($departmentCodes as $code) {
            $model = new self;
            $model->competition_id = $competitionId;
            $model->department_code = $code;
            $model->storeViaApi();
        }
    }

    public static function fetchFromApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::COMPETITION_DEPARTMENT_DETAIL, array('id' => $id));
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
        return ApiClient::post(ApiEndpoints::COMPETITION_DEPARTMENT_STORE, $data);
    }

    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::COMPETITION_DEPARTMENT_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    public static function getApiDataProvider($params = array(), $pageSize = 10000)
    {
        return new ApiDataProvider(ApiEndpoints::COMPETITION_DEPARTMENT_LIST, array(
            'modelClass' => 'CompetitionDepartments',
            'params' => $params,
            'pagination' => array('pageSize' => $pageSize),
        ));
    }
}
