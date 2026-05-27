<?php

/**
 * Base model for approval_workflow_approvers table
 *
 * @property integer $id
 * @property integer $workflow_id
 * @property integer $step_index
 * @property string $step_name
 * @property integer $portal_user_id
 * @property string $portal_user_name
 * @property string $portal_user_email
 * @property integer $organization_id
 * @property integer $is_active
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property ApprovalWorkflows $workflow
 * @property Organizations $organization
 */
class BaseApprovalWorkflowApprovers extends CActiveRecord
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'approval_workflow_approvers';
    }

    public function rules()
    {
        return array(
            array('workflow_id, step_index, step_name, portal_user_id', 'required'),
            array('workflow_id, step_index, portal_user_id, organization_id', 'numerical', 'integerOnly' => true),
            array('step_name, portal_user_name, portal_user_email', 'length', 'max' => 255),
            array('is_active', 'boolean'),
            array('id, workflow_id, step_index, portal_user_id, organization_id, is_active', 'safe', 'on' => 'search'),
        );
    }

    public function relations()
    {
        return array(
            'workflow' => array(self::BELONGS_TO, 'ApprovalWorkflows', 'workflow_id'),
            'organization' => array(self::BELONGS_TO, 'Organizations', 'organization_id'),
        );
    }

    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'workflow_id' => 'Workflow',
            'step_index' => 'Bước',
            'step_name' => 'Tên bước',
            'portal_user_id' => 'User ID (Portal)',
            'portal_user_name' => 'Tên người duyệt',
            'portal_user_email' => 'Email',
            'organization_id' => 'Đơn vị',
            'is_active' => 'Trạng thái',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
        );
    }

    protected function beforeSave()
    {
        if ($this->isNewRecord) {
            $this->created_at = time();
        }
        $this->updated_at = time();
        return parent::beforeSave();
    }
}
