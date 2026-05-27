<?php

Yii::import('application.models._base.BaseRegistrationApprovalLogs');

class RegistrationApprovalLogs extends BaseRegistrationApprovalLogs
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * Ghi log
     */
    public static function log($registrationId, $stepIndex, $action, $portalUserId = null, $comment = null, $stepName = null, $approverName = null)
    {
        $log = new self;
        $log->registration_id = $registrationId;
        $log->step_index = $stepIndex;
        $log->step_name = $stepName;
        $log->action = $action;
        $log->approver_portal_id = $portalUserId;
        $log->approver_name = $approverName;
        $log->comment = $comment;
        $log->acted_at = time();

        return $log->save();
    }

    /**
     * Lấy lịch sử của một đăng ký
     */
    public static function getHistory($registrationId)
    {
        return self::model()->findAll(array(
            'condition' => 'registration_id = :rid',
            'params' => array(':rid' => $registrationId),
            'order' => 'acted_at DESC',
        ));
    }

    /**
     * Format thời gian
     */
    public function getActedAtFormatted()
    {
        return date('d/m/Y H:i', $this->acted_at);
    }
}
