<?php

Yii::import('application.models._base.BaseRegistrationApprovals');

class RegistrationApprovals extends BaseRegistrationApprovals
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    // ==================== API Methods ====================

    public static function fetchFromApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::REGISTRATION_APPROVAL_DETAIL, array('id' => $id));
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

    public static function getApiDataProvider($params = array(), $pageSize = 25)
    {
        return new ApiDataProvider(ApiEndpoints::REGISTRATION_APPROVAL_LIST, array(
            'modelClass' => 'RegistrationApprovals',
            'params' => $params,
            'pagination' => array('pageSize' => $pageSize),
        ));
    }

    // ==================== Business Methods ====================

    /**
     * Tạo approval tracking khi submit đăng ký
     */
    public static function createForRegistration($registrationId, $workflowId = null)
    {
        // Lấy workflow (mặc định nếu không chỉ định)
        if (!$workflowId) {
            $workflow = ApprovalWorkflows::getDefault();
            if (!$workflow) {
                return array('success' => false, 'message' => 'Không tìm thấy workflow mặc định');
            }
            $workflowId = $workflow->id;
            $totalSteps = $workflow->total_steps;
        } else {
            $workflow = ApprovalWorkflows::fetchFromApi($workflowId);
            if (!$workflow) {
                return array('success' => false, 'message' => 'Không tìm thấy workflow');
            }
            $totalSteps = $workflow->total_steps;
        }

        $data = array(
            'registration_id' => $registrationId,
            'workflow_id' => $workflowId,
            'current_index' => 1,
            'total_steps' => $totalSteps,
            'status' => self::STATUS_PENDING,
        );

        return ApiClient::post(ApiEndpoints::REGISTRATION_APPROVAL_STORE, $data);
    }

    /**
     * Duyệt bước hiện tại - gọi API
     */
    public static function approveViaApi($id, $portalUserId, $approverName, $comment = null)
    {
        $url = ApiEndpoints::url(ApiEndpoints::REGISTRATION_APPROVAL_APPROVE, array('id' => $id));
        return ApiClient::post($url, array(
            'approver_portal_id' => $portalUserId,
            'approver_name' => $approverName,
            'comment' => $comment,
        ));
    }

    /**
     * Từ chối - gọi API
     */
    public static function rejectViaApi($id, $portalUserId, $approverName, $comment = null)
    {
        $url = ApiEndpoints::url(ApiEndpoints::REGISTRATION_APPROVAL_REJECT, array('id' => $id));
        return ApiClient::post($url, array(
            'approver_portal_id' => $portalUserId,
            'approver_name' => $approverName,
            'comment' => $comment,
        ));
    }

    /**
     * Yêu cầu chỉnh sửa - trả về cấp cụ thể - gọi API
     * @param int $id Registration approval ID
     * @param int $portalUserId
     * @param string $approverName
     * @param int $returnToIndex Trả về bước nào (0 = người tạo, 1 = bước 1...)
     * @param string $comment
     */
    public static function revisionViaApi($id, $portalUserId, $approverName, $returnToIndex = 0, $comment = null)
    {
        $url = ApiEndpoints::url(ApiEndpoints::REGISTRATION_APPROVAL_REVISION, array('id' => $id));
        return ApiClient::post($url, array(
            'approver_portal_id' => $portalUserId,
            'approver_name' => $approverName,
            'return_to_index' => $returnToIndex,
            'comment' => $comment,
        ));
    }

    /**
     * Lấy danh sách các bước có thể trả về
     */
    public function getReturnableSteps()
    {
        $steps = array(
            0 => 'Người tạo đăng ký'
        );

        for ($i = 1; $i < $this->current_index; $i++) {
            $stepName = isset($this->workflow) ? $this->workflow->getStepName($i) : 'Bước ' . $i;
            $steps[$i] = "Bước {$i}: {$stepName}";
        }

        return $steps;
    }

    /**
     * Nộp lại sau khi chỉnh sửa - gọi API
     */
    public static function resubmitViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::REGISTRATION_APPROVAL_RESUBMIT, array('id' => $id));
        return ApiClient::post($url, array());

        return $this->save();
    }

    /**
     * Lấy danh sách đơn chờ duyệt của user
     */
    public static function getPendingForApprover($portalUserId)
    {
        // Lấy các step mà user được phép duyệt
        $approverSteps = ApprovalWorkflowApprovers::getApproverSteps($portalUserId);

        if (empty($approverSteps)) {
            return array();
        }

        // Build điều kiện
        $conditions = array();
        $params = array();
        $i = 0;
        foreach ($approverSteps as $step) {
            $conditions[] = "(workflow_id = :wid{$i} AND current_index = :step{$i})";
            $params[":wid{$i}"] = $step->workflow_id;
            $params[":step{$i}"] = $step->step_index;
            $i++;
        }

        $criteria = new CDbCriteria();
        $criteria->condition = 'status = :status AND (' . implode(' OR ', $conditions) . ')';
        $criteria->params = array_merge(array(':status' => self::STATUS_PENDING), $params);
        $criteria->with = array('registration', 'workflow');
        $criteria->order = 'started_at ASC';

        return self::model()->findAll($criteria);
    }

    /**
     * Đếm số đơn chờ duyệt
     */
    public static function countPendingForApprover($portalUserId)
    {
        return count(self::getPendingForApprover($portalUserId));
    }

    /**
     * Lấy tên bước hiện tại
     */
    public function getCurrentStepName()
    {
        return $this->workflow ? $this->workflow->getStepName($this->current_index) : 'Bước ' . $this->current_index;
    }

    /**
     * Lấy tiến độ dạng text
     */
    public function getProgressText()
    {
        return $this->current_index . '/' . $this->total_steps;
    }
}
