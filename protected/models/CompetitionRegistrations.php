<?php

Yii::import('application.models._base.BaseCompetitionRegistrations');

class CompetitionRegistrations extends BaseCompetitionRegistrations
{
    const STATUS_PENDING = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_CANCELLED = 2;
    const STATUS_NO_SHOW = 3;

    public $registration_id;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'competition_id' => 'Cuộc thi',
            'registration_id' => 'Phiếu đăng ký',
            'attendee_id' => 'Người tham dự',
            'candidate_number' => 'Số báo danh',
            'status' => 'Trạng thái',
            'registered_at' => 'Ngày đăng ký',
            'confirmed_by' => 'Người xác nhận',
            'confirmed_at' => 'Ngày xác nhận',
            'note' => 'Ghi chú',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
        );
    }

    public static function fetchFromApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::COMPETITION_REGISTRATION_DETAIL, array('id' => $id));
        $result = ApiClient::get($url);
        if ($result['success'] && isset($result['data'])) {
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
        $data = array_filter($this->attributes, function ($value) {
            return $value !== null && $value !== '';
        });
        return ApiClient::post(ApiEndpoints::COMPETITION_REGISTRATION_STORE, $data);
    }

    public function updateViaApi()
    {
        $url = ApiEndpoints::url(ApiEndpoints::COMPETITION_REGISTRATION_UPDATE, array('id' => $this->id));
        return ApiClient::post($url, $this->attributes);
    }

    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::COMPETITION_REGISTRATION_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    public static function getApiDataProvider($params = array(), $pageSize = 10000)
    {
        return new ApiDataProvider(ApiEndpoints::COMPETITION_REGISTRATION_LIST, array(
            'modelClass' => 'CompetitionRegistrations',
            'params' => $params,
            'pagination' => array(
                'pageSize' => $pageSize,
            ),
        ));
    }

    /**
     * Lấy danh sách raw đăng ký thi nghiệp vụ theo sự kiện (dạng mảng từ API)
     */
    public static function getRawListByEvent($eventId, $perPage = 10000)
    {
        $result = ApiClient::get(ApiEndpoints::COMPETITION_REGISTRATION_LIST, array(
            'event_id' => $eventId,
            'per_page' => $perPage,
        ));
        if ($result['success']) {
            $data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
            if (is_array($data)) {
                return $data;
            }
        }
        return array();
    }

    public static function confirmViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::COMPETITION_REGISTRATION_CONFIRM, array('id' => $id));
        return ApiClient::post($url, array());
    }

    public static function getStatusLabel($status)
    {
        $labels = array(
            self::STATUS_PENDING => '<span class="badge bg-warning text-dark">Chờ xác nhận</span>',
            self::STATUS_CONFIRMED => '<span class="badge bg-success">Đã xác nhận</span>',
            self::STATUS_CANCELLED => '<span class="badge bg-danger">Đã hủy</span>',
            self::STATUS_NO_SHOW => '<span class="badge bg-secondary">Vắng mặt</span>',
        );
        return isset($labels[$status]) ? $labels[$status] : $status;
    }

    public static function getStatusOptions()
    {
        return array(
            self::STATUS_PENDING => 'Chờ xác nhận',
            self::STATUS_CONFIRMED => 'Đã xác nhận',
            self::STATUS_CANCELLED => 'Đã hủy',
            self::STATUS_NO_SHOW => 'Vắng mặt',
        );
    }

    public static function getByRegistrationId($registrationId)
    {
        return self::getApiDataProvider(array('registration_id' => $registrationId), 500)->getData();
    }

    public static function getListByProperty($params = array())
    {
        $result = ApiClient::get(ApiEndpoints::COMPETITION_REGISTRATION_LIST_BY_PROPERTY, $params);
        if ($result['success'] && isset($result['data'])) {
            return $result['data'];
        }
        return array();
    }

    /**
     * Lấy danh sách đăng ký dạng mảng thô (giữ nguyên field từ API).
     * @param array $params
     * @param int $perPage
     * @return array
     */
    public static function getRawList($params = array(), $perPage = 10000)
    {
        $params['per_page'] = $perPage;
        $result = ApiClient::get(ApiEndpoints::COMPETITION_REGISTRATION_LIST, $params);
        if ($result['success']) {
            $data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
            if (is_array($data)) {
                return $data;
            }
        }
        return array();
    }

    // ===== Vòng chung kết =====

    /**
     * Thêm các thí sinh (đăng ký) vào vòng chung kết của cuộc thi nghiệp vụ.
     * @param int $competitionId
     * @param array $registrationIds competition_registrations.id
     * @return array
     */
    public static function addToFinal($competitionId, $registrationIds)
    {
        return ApiClient::post(ApiEndpoints::COMPETITION_FINAL_ADD, array(
            'competition_id' => $competitionId,
            'registration_ids' => array_values($registrationIds),
        ));
    }

    /**
     * Danh sách thí sinh đã vào chung kết của cuộc thi.
     * @param int $competitionId
     * @return array
     */
    public static function getFinalists($competitionId)
    {
        $url = ApiEndpoints::url(ApiEndpoints::COMPETITION_FINAL_LIST, array('id' => $competitionId));
        $result = ApiClient::get($url);
        if ($result['success'] && isset($result['data'])) {
            $data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
            return is_array($data) ? $data : array();
        }
        return array();
    }

    /**
     * Gỡ một thí sinh khỏi vòng chung kết.
     * @param int $finalistId id bản ghi chung kết (competition_round_results.id)
     * @return array
     */
    public static function removeFromFinal($finalistId)
    {
        $url = ApiEndpoints::url(ApiEndpoints::COMPETITION_FINAL_REMOVE, array('id' => $finalistId));
        return ApiClient::delete($url);
    }
}
