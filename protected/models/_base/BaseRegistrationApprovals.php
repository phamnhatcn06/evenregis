<?php

/**
 * Base model for registration_approvals table
 *
 * @property integer $id
 * @property integer $registration_id
 * @property integer $workflow_id
 * @property integer $current_index
 * @property integer $total_steps
 * @property string $status
 * @property integer $started_at
 * @property integer $completed_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Registrations $registration
 * @property ApprovalWorkflows $workflow
 * @property RegistrationApprovalLogs[] $logs
 */
class BaseRegistrationApprovals extends CActiveRecord
{
    const STATUS_PENDING = 1;
    const STATUS_APPROVED = 2;
    const STATUS_REJECTED = 3;
    const STATUS_REVISION = 4;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return 'registration_approvals';
    }

    public function rules()
    {
        return array(
            array('registration_id, workflow_id, total_steps', 'required'),
            array('registration_id, workflow_id, current_index, total_steps', 'numerical', 'integerOnly' => true),
            array('status', 'in', 'range' => array(self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED, self::STATUS_REVISION)),
            array('started_at, completed_at', 'safe'),
            array('id, registration_id, workflow_id, current_index, status', 'safe', 'on' => 'search'),
        );
    }

    public function relations()
    {
        return array(
            'registration' => array(self::BELONGS_TO, 'Registrations', 'registration_id'),
            'workflow' => array(self::BELONGS_TO, 'ApprovalWorkflows', 'workflow_id'),
            'logs' => array(self::HAS_MANY, 'RegistrationApprovalLogs', 'registration_id',
                'order' => 'logs.acted_at DESC'),
        );
    }

    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'registration_id' => 'Đăng ký',
            'workflow_id' => 'Workflow',
            'current_index' => 'Bước hiện tại',
            'total_steps' => 'Tổng số bước',
            'status' => 'Trạng thái',
            'started_at' => 'Bắt đầu',
            'completed_at' => 'Hoàn tất',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
        );
    }

    public static function getStatusOptions()
    {
        return array(
            self::STATUS_PENDING => 'Đang chờ duyệt',
            self::STATUS_APPROVED => 'Đã duyệt',
            self::STATUS_REJECTED => 'Từ chối',
            self::STATUS_REVISION => 'Yêu cầu chỉnh sửa',
        );
    }

    public static function getStatusLabel($status)
    {
        $labels = array(
            self::STATUS_PENDING => '<span class="badge bg-warning">Đang chờ duyệt</span>',
            self::STATUS_APPROVED => '<span class="badge bg-success">Đã duyệt</span>',
            self::STATUS_REJECTED => '<span class="badge bg-danger">Từ chối</span>',
            self::STATUS_REVISION => '<span class="badge bg-info">Yêu cầu chỉnh sửa</span>',
        );
        return isset($labels[$status]) ? $labels[$status] : $status;
    }

    protected function beforeSave()
    {
        if ($this->isNewRecord) {
            $this->created_at = time();
            $this->started_at = time();
        }
        $this->updated_at = time();
        return parent::beforeSave();
    }
}
