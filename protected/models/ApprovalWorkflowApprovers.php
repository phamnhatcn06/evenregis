<?php

Yii::import('application.models._base.BaseApprovalWorkflowApprovers');

class ApprovalWorkflowApprovers extends BaseApprovalWorkflowApprovers
{
    public $auth_email;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    // ==================== API Methods ====================

    public static function fetchFromApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::APPROVAL_WORKFLOW_APPROVER_DETAIL, array('id' => $id));
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
        return ApiClient::post(ApiEndpoints::APPROVAL_WORKFLOW_APPROVER_STORE, $data);
    }

    public function updateViaApi()
    {
        $url = ApiEndpoints::url(ApiEndpoints::APPROVAL_WORKFLOW_APPROVER_UPDATE, array('id' => $this->id));
        return ApiClient::post($url, $this->attributes);
    }

    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::APPROVAL_WORKFLOW_APPROVER_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    public static function getApiDataProvider($params = array(), $pageSize = 25)
    {
        return new ApiDataProvider(ApiEndpoints::APPROVAL_WORKFLOW_APPROVER_LIST, array(
            'modelClass' => 'ApprovalWorkflowApprovers',
            'params' => $params,
            'pagination' => array('pageSize' => $pageSize),
        ));
    }

    // ==================== Business Methods ====================

    /**
     * Lấy danh sách step mà user được phép duyệt
     */
    public static function getApproverSteps($portalUserId, $workflowId = null)
    {
        $url = ApiEndpoints::url(ApiEndpoints::APPROVAL_WORKFLOW_APPROVER_BY_USER, array('portal_user_id' => $portalUserId));
        $params = array('is_active' => 1);
        if ($workflowId) {
            $params['workflow_id'] = $workflowId;
        }
        $result = ApiClient::get($url, $params);

        $steps = array();
        if ($result['success'] && !empty($result['data']['data'])) {
            foreach ($result['data']['data'] as $item) {
                $model = new self;
                $model->setAttributes($item, false);
                $steps[] = $model;
            }
        }
        return $steps;
    }

    /**
     * Kiểm tra user có quyền duyệt không
     */
    public static function canApprove($portalUserId, $workflowId, $stepIndex, $organizationId = null)
    {
        $params = array(
            'portal_user_id' => $portalUserId,
            'workflow_id' => $workflowId,
            'step_index' => $stepIndex,
            'is_active' => 1,
        );
        if ($organizationId) {
            $params['organization_id'] = $organizationId;
        }

        $result = ApiClient::get(ApiEndpoints::APPROVAL_WORKFLOW_APPROVER_LIST, $params);
        return $result['success'] && !empty($result['data']['data']);
    }

    /**
     * Lấy tất cả portal_user_id của một bước
     */
    public static function getApproverIds($workflowId, $stepIndex, $organizationId = null)
    {
        $params = array(
            'workflow_id' => $workflowId,
            'step_index' => $stepIndex,
            'is_active' => 1,
            'per_page' => 100,
        );
        if ($organizationId) {
            $params['organization_id'] = $organizationId;
        }

        $result = ApiClient::get(ApiEndpoints::APPROVAL_WORKFLOW_APPROVER_LIST, $params);
        $ids = array();
        if ($result['success'] && !empty($result['data']['data'])) {
            foreach ($result['data']['data'] as $item) {
                $ids[] = $item['portal_user_id'];
            }
        }
        return $ids;
    }
}
