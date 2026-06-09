<?php

Yii::import('application.models._base.BaseApprovalWorkflows');

class ApprovalWorkflows extends BaseApprovalWorkflows
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    // ==================== API Methods ====================

    /**
     * Lấy chi tiết từ API
     */
    public static function fetchFromApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::APPROVAL_WORKFLOW_DETAIL, array('id' => $id));
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

    /**
     * Tạo mới qua API
     */
    public function storeViaApi()
    {
        $data = array_filter($this->attributes, function ($value) {
            return $value !== null && $value !== '';
        });
        return ApiClient::post(ApiEndpoints::APPROVAL_WORKFLOW_STORE, $data);
    }

    /**
     * Cập nhật qua API
     */
    public function updateViaApi()
    {
        $url = ApiEndpoints::url(ApiEndpoints::APPROVAL_WORKFLOW_UPDATE, array('id' => $this->id));
        return ApiClient::post($url, $this->attributes);
    }

    /**
     * Xóa qua API
     */
    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::APPROVAL_WORKFLOW_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    /**
     * DataProvider cho danh sách
     */
    public static function getApiDataProvider($params = array(), $pageSize = 10000)
    {
        return new ApiDataProvider(ApiEndpoints::APPROVAL_WORKFLOW_LIST, array(
            'modelClass' => 'ApprovalWorkflows',
            'params' => $params,
            'pagination' => array('pageSize' => $pageSize),
        ));
    }

    // ==================== Business Methods ====================

    /**
     * Lấy workflow mặc định
     */
    public static function getDefault()
    {
        $result = ApiClient::get(ApiEndpoints::APPROVAL_WORKFLOW_LIST, array(
            'is_default' => 1,
            'is_active' => 1,
            'per_page' => 1,
        ));
        if ($result['success'] && !empty($result['data']['data'])) {
            $data = $result['data']['data'][0];
            $model = new self;
            $model->setAttributes($data, false);
            return $model;
        }
        return null;
    }

    /**
     * Lấy danh sách dropdown
     */
    public static function getList()
    {
        $result = ApiClient::get(ApiEndpoints::APPROVAL_WORKFLOW_LIST, array(
            'is_active' => 1,
            'per_page' => 100,
        ));
        $list = array();
        if ($result['success'] && !empty($result['data']['data'])) {
            foreach ($result['data']['data'] as $item) {
                $list[$item['id']] = $item['name'];
            }
        }
        return $list;
    }

    /**
     * Lấy tên bước theo index (từ cache hoặc relation)
     */
    public function getStepName($stepIndex)
    {
        if (!empty($this->approvers)) {
            foreach ($this->approvers as $approver) {
                if ($approver->step_index == $stepIndex) {
                    return $approver->step_name;
                }
            }
        }
        return 'Bước ' . $stepIndex;
    }

    /**
     * Lấy danh sách các step (unique)
     */
    public function getSteps()
    {
        $steps = array();
        if (!empty($this->approvers)) {
            $seen = array();
            foreach ($this->approvers as $approver) {
                if (!isset($seen[$approver->step_index])) {
                    $steps[] = array(
                        'step_index' => $approver->step_index,
                        'step_name' => $approver->step_name,
                    );
                    $seen[$approver->step_index] = true;
                }
            }
        }
        return $steps;
    }
}
