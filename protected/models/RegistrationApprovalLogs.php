<?php

Yii::import('application.models._base.BaseRegistrationApprovalLogs');

class RegistrationApprovalLogs extends BaseRegistrationApprovalLogs
{
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    // ==================== API Methods ====================

    public static function getApiDataProvider($params = array(), $pageSize = 25)
    {
        return new ApiDataProvider(ApiEndpoints::REGISTRATION_APPROVAL_LOG_LIST, array(
            'modelClass' => 'RegistrationApprovalLogs',
            'params' => $params,
            'pagination' => array('pageSize' => $pageSize),
        ));
    }

    /**
     * Lấy lịch sử của một đăng ký - gọi API
     */
    public static function getHistory($registrationId)
    {
        $url = ApiEndpoints::url(ApiEndpoints::REGISTRATION_APPROVAL_LOG_BY_REGISTRATION, array('registration_id' => $registrationId));
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
     * Format thời gian
     */
    public function getActedAtFormatted()
    {
        return date('d/m/Y H:i', $this->acted_at);
    }

    /**
     * Lấy tên action hiển thị
     */
    public function getActionText()
    {
        $texts = array(
            self::ACTION_APPROVED => 'Đã duyệt',
            self::ACTION_REJECTED => 'Từ chối',
            self::ACTION_REVISION => 'Yêu cầu chỉnh sửa',
            self::ACTION_SUBMITTED => 'Nộp đăng ký',
            self::ACTION_RESUBMITTED => 'Nộp lại',
        );
        return isset($texts[$this->action]) ? $texts[$this->action] : $this->action;
    }
}
