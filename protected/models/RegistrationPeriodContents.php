<?php

Yii::import('application.models._base.BaseRegistrationPeriodContents');

class RegistrationPeriodContents extends BaseRegistrationPeriodContents
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public static function fetchFromApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::REGISTRATION_PERIOD_CONTENT_DETAIL, array('id' => $id));
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
        return ApiClient::post(ApiEndpoints::REGISTRATION_PERIOD_CONTENT_STORE, $data);
    }

    public function updateViaApi()
    {
        $data = array_filter($this->attributes, function ($value) {
            return $value !== null && $value !== '';
        });
        $url = ApiEndpoints::url(ApiEndpoints::REGISTRATION_PERIOD_CONTENT_UPDATE, array('id' => $this->id));
        return ApiClient::post($url, $data);
    }

    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::REGISTRATION_PERIOD_CONTENT_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    public static function getApiDataProvider($params = array(), $pageSize = 10000)
    {
        return new ApiDataProvider(ApiEndpoints::REGISTRATION_PERIOD_CONTENT_LIST, array(
            'modelClass' => 'RegistrationPeriodContents',
            'params' => $params,
            'pagination' => array(
                'pageSize' => $pageSize,
            ),
        ));
    }

    /**
     * Lấy danh sách content_id của một period
     */
    public static function getContentIdsByPeriod($periodId)
    {
        $params = array('period_id' => $periodId, 'per_page' => 100);
        $dataProvider = self::getApiDataProvider($params, 100);
        $data = $dataProvider->getData();

        $contentIds = array();
        foreach ($data as $item) {
            $cid = is_array($item) ? (isset($item['content_id']) ? $item['content_id'] : null) : (isset($item->content_id) ? $item->content_id : null);
            if ($cid) {
                $contentIds[] = $cid;
            }
        }
        return $contentIds;
    }

    /**
     * Lấy danh sách contents của một period với tên
     */
    public static function getContentsByPeriod($periodId)
    {
        $params = array('period_id' => $periodId, 'per_page' => 100);
        $dataProvider = self::getApiDataProvider($params, 100);
        return $dataProvider->getData();
    }

    /**
     * Sync contents cho một period (xóa cũ, thêm mới)
     */
    public static function syncContentsForPeriod($periodId, $contentIds)
    {
        $currentIds = self::getContentIdsByPeriod($periodId);
        $contentIds = is_array($contentIds) ? $contentIds : array();

        $toDelete = array_diff($currentIds, $contentIds);
        $toAdd = array_diff($contentIds, $currentIds);

        foreach ($toDelete as $cid) {
            $params = array('period_id' => $periodId, 'content_id' => $cid);
            $dp = self::getApiDataProvider($params, 1);
            $items = $dp->getData();
            if (!empty($items)) {
                $item = reset($items);
                $id = is_array($item) ? $item['id'] : $item->id;
                self::deleteViaApi($id);
            }
        }

        foreach ($toAdd as $cid) {
            $model = new self;
            $model->period_id = $periodId;
            $model->content_id = $cid;
            $model->storeViaApi();
        }

        return true;
    }
}
