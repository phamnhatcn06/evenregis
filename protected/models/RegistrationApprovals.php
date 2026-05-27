<?php

Yii::import('application.models._base.BaseRegistrationApprovals');

class RegistrationApprovals extends BaseRegistrationApprovals
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * Tạo approval tracking khi submit đăng ký
     */
    public static function createForRegistration($registrationId, $workflowId = null)
    {
        // Lấy workflow (mặc định nếu không chỉ định)
        if (!$workflowId) {
            $workflow = ApprovalWorkflows::getDefault();
            if (!$workflow) {
                return null;
            }
            $workflowId = $workflow->id;
        } else {
            $workflow = ApprovalWorkflows::model()->findByPk($workflowId);
        }

        if (!$workflow) {
            return null;
        }

        $model = new self;
        $model->registration_id = $registrationId;
        $model->workflow_id = $workflowId;
        $model->current_index = 1;
        $model->total_steps = $workflow->total_steps;
        $model->status = self::STATUS_PENDING;

        if ($model->save()) {
            // Log submitted
            RegistrationApprovalLogs::log($registrationId, 0, 'submitted', null, 'Nộp đăng ký');
            return $model;
        }
        return null;
    }

    /**
     * Duyệt bước hiện tại
     */
    public function approve($portalUserId, $approverName, $comment = null)
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $stepName = $this->workflow->getStepName($this->current_index);

        // Log approval
        RegistrationApprovalLogs::log(
            $this->registration_id,
            $this->current_index,
            'approved',
            $portalUserId,
            $comment,
            $stepName,
            $approverName
        );

        // Chuyển bước tiếp theo hoặc hoàn tất
        if ($this->current_index >= $this->total_steps) {
            $this->status = self::STATUS_APPROVED;
            $this->completed_at = time();
        } else {
            $this->current_index++;
        }

        return $this->save();
    }

    /**
     * Từ chối
     */
    public function reject($portalUserId, $approverName, $comment = null)
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $stepName = $this->workflow->getStepName($this->current_index);

        RegistrationApprovalLogs::log(
            $this->registration_id,
            $this->current_index,
            'rejected',
            $portalUserId,
            $comment,
            $stepName,
            $approverName
        );

        $this->status = self::STATUS_REJECTED;
        $this->completed_at = time();

        return $this->save();
    }

    /**
     * Yêu cầu chỉnh sửa - trả về cấp cụ thể
     * @param int $portalUserId
     * @param string $approverName
     * @param int $returnToIndex Trả về bước nào (0 = người tạo, 1 = bước 1...)
     * @param string $comment
     */
    public function requestRevision($portalUserId, $approverName, $returnToIndex = 0, $comment = null)
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        // Validate: chỉ được trả về bước trước đó
        if ($returnToIndex >= $this->current_index) {
            return false;
        }

        $stepName = $this->workflow->getStepName($this->current_index);

        RegistrationApprovalLogs::log(
            $this->registration_id,
            $this->current_index,
            'revision',
            $portalUserId,
            $comment,
            $stepName,
            $approverName,
            $returnToIndex
        );

        // Nếu trả về bước 0 (người tạo) → status = revision
        // Nếu trả về bước > 0 → current_index = returnToIndex, status vẫn pending
        if ($returnToIndex == 0) {
            $this->status = self::STATUS_REVISION;
        } else {
            $this->current_index = $returnToIndex;
            // status vẫn là pending, chờ bước đó duyệt lại
        }

        return $this->save();
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
            $stepName = $this->workflow->getStepName($i);
            $steps[$i] = "Bước {$i}: {$stepName}";
        }

        return $steps;
    }

    /**
     * Nộp lại sau khi chỉnh sửa
     */
    public function resubmit()
    {
        if ($this->status !== self::STATUS_REVISION) {
            return false;
        }

        RegistrationApprovalLogs::log(
            $this->registration_id,
            $this->current_index,
            'resubmitted',
            null,
            'Nộp lại sau chỉnh sửa'
        );

        $this->status = self::STATUS_PENDING;

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
