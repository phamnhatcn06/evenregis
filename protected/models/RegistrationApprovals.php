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
                return array('success' => false, 'message' => 'Không tìm thấy workflow mặc định. Vui lòng tạo workflow và đánh dấu là mặc định.');
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

        $result = ApiClient::post(ApiEndpoints::REGISTRATION_APPROVAL_STORE, $data);

        // Trả về message chi tiết nếu API thất bại
        if (!$result['success']) {
            $errorMsg = 'Lỗi tạo luồng duyệt';
            if (isset($result['message'])) {
                $errorMsg .= ': ' . $result['message'];
            } elseif (isset($result['data']['message'])) {
                $errorMsg .= ': ' . $result['data']['message'];
            }
            return array('success' => false, 'message' => $errorMsg);
        }

        return $result;
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
    }

    /**
     * Lấy bản ghi approval đang active (pending/revision) của một registration
     */
    public static function getActiveByRegistrationId($registrationId)
    {
        $dataProvider = self::getApiDataProvider(
            array('registration_id' => $registrationId),
            1
        );
        $data = $dataProvider->getData();
        return !empty($data) ? $data[0] : null;
    }

    /**
     * Lấy danh sách đơn chờ duyệt của user - gọi API
     */
    public static function getPendingForApprover($portalUserId)
    {
        $url = ApiEndpoints::url(ApiEndpoints::REGISTRATION_APPROVAL_PENDING, array('portal_user_id' => $portalUserId));
        $result = ApiClient::get($url);

        $models = array();
        if ($result['success'] && !empty($result['data']['data'])) {
            foreach ($result['data']['data'] as $item) {
                $model = new self;
                $model->setAttributes($item, false);
                $models[] = $model;
            }
        }
        return $models;
    }

    /**
     * Đếm số đơn chờ duyệt
     */
    public static function countPendingForApprover($portalUserId)
    {
        $url = ApiEndpoints::url(ApiEndpoints::REGISTRATION_APPROVAL_PENDING, array('portal_user_id' => $portalUserId));
        $result = ApiClient::get($url, array('count_only' => 1));

        if ($result['success'] && isset($result['data']['total'])) {
            return $result['data']['total'];
        }
        return 0;
    }

    /**
     * Lấy tên bước hiện tại
     */
    public function getCurrentStepName()
    {
        if (isset($this->workflow) && $this->workflow) {
            return $this->workflow->getStepName($this->current_index);
        }
        return 'Bước ' . $this->current_index;
    }

    /**
     * Lấy tiến độ dạng text
     */
    public function getProgressText()
    {
        return $this->current_index . '/' . $this->total_steps;
    }
}
