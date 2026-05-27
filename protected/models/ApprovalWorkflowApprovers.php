<?php

Yii::import('application.models._base.BaseApprovalWorkflowApprovers');

class ApprovalWorkflowApprovers extends BaseApprovalWorkflowApprovers
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * Kiểm tra user có quyền duyệt ở workflow/step không
     */
    public static function canApprove($portalUserId, $workflowId, $stepIndex, $organizationId = null)
    {
        $criteria = new CDbCriteria();
        $criteria->condition = 'portal_user_id = :uid AND workflow_id = :wid AND step_index = :step AND is_active = 1';
        $criteria->params = array(
            ':uid' => $portalUserId,
            ':wid' => $workflowId,
            ':step' => $stepIndex,
        );

        if ($organizationId) {
            $criteria->addCondition('(organization_id IS NULL OR organization_id = :org)');
            $criteria->params[':org'] = $organizationId;
        }

        return self::model()->exists($criteria);
    }

    /**
     * Lấy danh sách step_index mà user được phép duyệt
     */
    public static function getApproverSteps($portalUserId, $workflowId = null)
    {
        $criteria = new CDbCriteria();
        $criteria->select = 'DISTINCT step_index, step_name, workflow_id';
        $criteria->condition = 'portal_user_id = :uid AND is_active = 1';
        $criteria->params = array(':uid' => $portalUserId);
        $criteria->order = 'workflow_id, step_index';

        if ($workflowId) {
            $criteria->addCondition('workflow_id = :wid');
            $criteria->params[':wid'] = $workflowId;
        }

        return self::model()->findAll($criteria);
    }

    /**
     * Lấy tất cả portal_user_id của một bước
     */
    public static function getApproverIds($workflowId, $stepIndex, $organizationId = null)
    {
        $criteria = new CDbCriteria();
        $criteria->select = 'portal_user_id';
        $criteria->condition = 'workflow_id = :wid AND step_index = :step AND is_active = 1';
        $criteria->params = array(':wid' => $workflowId, ':step' => $stepIndex);

        if ($organizationId) {
            $criteria->addCondition('(organization_id IS NULL OR organization_id = :org)');
            $criteria->params[':org'] = $organizationId;
        }

        $models = self::model()->findAll($criteria);
        return array_map(function ($m) {
            return $m->portal_user_id;
        }, $models);
    }
}
