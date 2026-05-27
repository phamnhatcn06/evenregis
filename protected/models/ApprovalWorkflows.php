<?php

Yii::import('application.models._base.BaseApprovalWorkflows');

class ApprovalWorkflows extends BaseApprovalWorkflows
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * Lấy workflow mặc định
     */
    public static function getDefault()
    {
        return self::model()->find('is_default = 1 AND is_active = 1');
    }

    /**
     * Lấy danh sách dropdown
     */
    public static function getList()
    {
        $models = self::model()->findAll(array(
            'condition' => 'is_active = 1',
            'order' => 'name ASC',
        ));
        return CHtml::listData($models, 'id', 'name');
    }

    /**
     * Lấy approvers theo step_index
     */
    public function getApproversByStep($stepIndex, $organizationId = null)
    {
        $criteria = new CDbCriteria();
        $criteria->condition = 'workflow_id = :wid AND step_index = :step AND is_active = 1';
        $criteria->params = array(':wid' => $this->id, ':step' => $stepIndex);

        if ($organizationId) {
            $criteria->addCondition('(organization_id IS NULL OR organization_id = :org)');
            $criteria->params[':org'] = $organizationId;
        } else {
            $criteria->addCondition('organization_id IS NULL');
        }

        return ApprovalWorkflowApprovers::model()->findAll($criteria);
    }

    /**
     * Lấy tên bước theo index
     */
    public function getStepName($stepIndex)
    {
        $approver = ApprovalWorkflowApprovers::model()->find(array(
            'condition' => 'workflow_id = :wid AND step_index = :step',
            'params' => array(':wid' => $this->id, ':step' => $stepIndex),
        ));
        return $approver ? $approver->step_name : 'Bước ' . $stepIndex;
    }

    /**
     * Lấy danh sách các step (unique)
     */
    public function getSteps()
    {
        return Yii::app()->db->createCommand()
            ->selectDistinct('step_index, step_name')
            ->from('approval_workflow_approvers')
            ->where('workflow_id = :wid', array(':wid' => $this->id))
            ->order('step_index ASC')
            ->queryAll();
    }
}
