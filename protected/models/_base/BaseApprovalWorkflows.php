<?php

/**
 * Base model for approval_workflows table
 *
 * @property integer $id
 * @property string $code
 * @property string $name
 * @property string $description
 * @property integer $total_steps
 * @property integer $is_default
 * @property integer $is_active
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 *
 * @property ApprovalWorkflowApprovers[] $approvers
 * @property RegistrationApprovals[] $registrationApprovals
 */
class BaseApprovalWorkflows extends CActiveRecord
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'approval_workflows';
    }

    public function rules()
    {
        return array(
            array('code, name', 'required'),
            array('code', 'length', 'max' => 50),
            array('code', 'unique'),
            array('name', 'length', 'max' => 255),
            array('total_steps', 'numerical', 'integerOnly' => true, 'min' => 1, 'max' => 20),
            array('is_default, is_active', 'boolean'),
            array('description', 'safe'),
            array('id, code, name, total_steps, is_default, is_active', 'safe', 'on' => 'search'),
        );
    }

    public function relations()
    {
        return array(
            'approvers' => array(self::HAS_MANY, 'ApprovalWorkflowApprovers', 'workflow_id',
                'order' => 'approvers.step_index ASC'),
            'registrationApprovals' => array(self::HAS_MANY, 'RegistrationApprovals', 'workflow_id'),
        );
    }

    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'code' => 'Mã workflow',
            'name' => 'Tên workflow',
            'description' => 'Mô tả',
            'total_steps' => 'Số bước duyệt',
            'is_default' => 'Mặc định',
            'is_active' => 'Trạng thái',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
            'created_by' => 'Người tạo',
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
