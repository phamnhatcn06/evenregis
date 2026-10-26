<?php

/**
 * Survey — Khảo sát ý kiến (link Google Form) của Website công khai Đại hội.
 *
 * Bảng `surveys` được quản lý qua External API (không có trong DB local của Yii),
 * nên model này là CFormModel giữ attribute + gọi ApiClient theo ApiEndpoints.
 *
 * Hợp đồng API (REST style):
 *   - GET    /api/admin/surveys
 *   - POST   /api/admin/surveys
 *   - GET    /api/admin/surveys/{id}
 *   - PUT    /api/admin/surveys/{id}
 *   - DELETE /api/admin/surveys/{id}
 *   - POST   /api/admin/surveys/{id}/toggle
 *   - POST   /api/admin/surveys/reorder
 *   - GET    /api/daihoi/surveys        (public - khảo sát đang bật của sự kiện hiện tại)
 */
class Survey extends CFormModel
{
    const IS_ACTIVE = 1;
    const IS_INACTIVE = 0;

    public $id;
    public $event_id;
    public $title;
    public $description;
    public $form_url;
    public $icon;
    public $sort_order = 0;
    public $is_active = 0;
    public $created_by;
    public $updated_by;
    public $created_at;
    public $updated_at;
    public $deleted_at;

    public function rules()
    {
        return array(
            array('event_id, title, form_url', 'required'),
            array('event_id, sort_order, is_active', 'numerical', 'integerOnly' => true),
            array('form_url', 'url', 'defaultScheme' => 'https', 'message' => 'Link khảo sát phải là URL hợp lệ (http/https).'),
            array('form_url', 'length', 'max' => 1000),
            array('title', 'length', 'max' => 255),
            array('description', 'length', 'max' => 500),
            array('icon', 'length', 'max' => 100),
            array('id, created_by, updated_by, created_at, updated_at, deleted_at', 'safe'),
        );
    }

    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'event_id' => 'Sự kiện',
            'title' => 'Tiêu đề',
            'description' => 'Mô tả',
            'form_url' => 'Link Google Form',
            'icon' => 'Icon (Font Awesome)',
            'sort_order' => 'Thứ tự',
            'is_active' => 'Hiển thị',
            'created_by' => 'Người tạo',
            'updated_by' => 'Người cập nhật',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
        );
    }

    public static function fetchFromApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::SURVEY_DETAIL, array('id' => $id));
        $result = ApiClient::get($url);
        if (!empty($result['success']) && isset($result['data'])) {
            $data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
            $model = new self;
            $model->setAttributes($data, false);
            $model->id = $id;
            return $model;
        }
        return null;
    }

    public function storeViaApi()
    {
        $ssoUser = AuthHandler::getUser();
        $this->created_by = isset($ssoUser['email']) ? $ssoUser['email'] : null;

        $data = array_filter($this->getAttributes(), function ($value) {
            return $value !== null && $value !== '';
        });
        return ApiClient::post(ApiEndpoints::SURVEY_STORE, $data);
    }

    public function updateViaApi()
    {
        $ssoUser = AuthHandler::getUser();
        $this->updated_by = isset($ssoUser['email']) ? $ssoUser['email'] : null;

        $data = array_filter($this->getAttributes(), function ($value) {
            return $value !== null && $value !== '';
        });
        $url = ApiEndpoints::url(ApiEndpoints::SURVEY_UPDATE, array('id' => $this->id));
        return ApiClient::put($url, $data);
    }

    public static function deleteViaApi($id)
    {
        // ApiClient::delete tự đính kèm auth_email của người thực hiện (SSO).
        $url = ApiEndpoints::url(ApiEndpoints::SURVEY_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    /** Bật/tắt nhanh trạng thái hiển thị của một khảo sát. */
    public static function toggleViaApi($id)
    {
        $ssoUser = AuthHandler::getUser();
        $data = array();
        if (isset($ssoUser['email'])) {
            $data['updated_by'] = $ssoUser['email'];
        }
        $url = ApiEndpoints::url(ApiEndpoints::SURVEY_TOGGLE, array('id' => $id));
        return ApiClient::post($url, $data);
    }

    public static function getApiDataProvider($params = array(), $pageSize = 25)
    {
        return new ApiDataProvider(ApiEndpoints::SURVEY_LIST, array(
            'modelClass' => 'Survey',
            'params' => $params,
            'pagination' => array(
                'pageSize' => $pageSize,
            ),
        ));
    }
}
