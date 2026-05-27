<?php

/**
 * Base model for registration_approval_logs table
 *
 * @property integer $id
 * @property integer $registration_id
 * @property integer $step_index
 * @property string $step_name
 * @property string $action
 * @property integer $return_to_index
 * @property integer $approver_portal_id
 * @property string $approver_name
 * @property string $comment
 * @property integer $acted_at
 * @property integer $created_at
 *
 * @property Registrations $registration
 */
class BaseRegistrationApprovalLogs extends CActiveRecord
{
    const ACTION_APPROVED = 'approved';
    const ACTION_REJECTED = 'rejected';
    const ACTION_REVISION = 'revision';
    const ACTION_SUBMITTED = 'submitted';
    const ACTION_RESUBMITTED = 'resubmitted';

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'registration_approval_logs';
    }

    public function rules()
    {
        return array(
            array('registration_id, step_index, action, acted_at', 'required'),
            array('registration_id, step_index, approver_portal_id', 'numerical', 'integerOnly' => true),
            array('action', 'in', 'range' => array('approved', 'rejected', 'revision', 'submitted', 'resubmitted')),
            array('step_name, approver_name', 'length', 'max' => 255),
            array('comment', 'safe'),
            array('id, registration_id, step_index, action, approver_portal_id', 'safe', 'on' => 'search'),
        );
    }

    public function relations()
    {
        return array(
            'registration' => array(self::BELONGS_TO, 'Registrations', 'registration_id'),
        );
    }

    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'registration_id' => 'Đăng ký',
            'step_index' => 'Bước',
            'step_name' => 'Tên bước',
            'action' => 'Hành động',
            'approver_portal_id' => 'Người duyệt (Portal ID)',
            'approver_name' => 'Tên người duyệt',
            'comment' => 'Ghi chú',
            'acted_at' => 'Thời gian',
            'created_at' => 'Ngày tạo',
        );
    }

    public static function getActionLabel($action)
    {
        $labels = array(
            self::ACTION_APPROVED => '<span class="badge bg-success">Đã duyệt</span>',
            self::ACTION_REJECTED => '<span class="badge bg-danger">Từ chối</span>',
            self::ACTION_REVISION => '<span class="badge bg-info">Yêu cầu chỉnh sửa</span>',
            self::ACTION_SUBMITTED => '<span class="badge bg-primary">Nộp đăng ký</span>',
            self::ACTION_RESUBMITTED => '<span class="badge bg-primary">Nộp lại</span>',
        );
        return isset($labels[$action]) ? $labels[$action] : $action;
    }

    protected function beforeSave()
    {
        if ($this->isNewRecord) {
            $this->created_at = time();
        }
        return parent::beforeSave();
    }
}
