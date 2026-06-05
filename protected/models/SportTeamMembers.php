<?php

Yii::import('application.models._base.BaseSportTeamMembers');

class SportTeamMembers extends BaseSportTeamMembers
{
    const MAX_SPORTS_PER_ATTENDEE = 3;

    public $attendee_name;
    public $sport_name;
    public $team_name;
    public $property_name;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'sport_team_id' => 'Đội',
            'attendee_id' => 'Người tham dự',
            'jersey_number' => 'Số áo',
            'position' => 'Vị trí',
            'is_captain' => 'Đội trưởng',
            'created_at' => 'Ngày tạo',
        );
    }

    public static function fetchFromApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::SPORT_TEAM_MEMBER_DETAIL, array('id' => $id));
        $result = ApiClient::get($url);
        if ($result['success'] && isset($result['data'])) {
            $data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
            $model = new self;
            $model->setAttributes($data, false);
            $model->attendee_name = isset($data['attendee_name']) ? $data['attendee_name'] : '';
            $model->id = $id;
            return $model;
        }
        return null;
    }

    public function storeViaApi()
    {
        $data = $this->attributes;
        $data['sport_team_id'] = $this->sport_team_id;
        $data['attendee_id'] = $this->attendee_id;
        $data['name'] = $this->name;

        $data = array_filter($data, function ($value) {
            return $value !== null && $value !== '';
        });
        return ApiClient::post(ApiEndpoints::SPORT_TEAM_MEMBER_STORE, $data);
    }

    public function updateViaApi()
    {
        $url = ApiEndpoints::url(ApiEndpoints::SPORT_TEAM_MEMBER_UPDATE, array('id' => $this->id));
        return ApiClient::post($url, $this->attributes);
    }

    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::SPORT_TEAM_MEMBER_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    public static function getApiDataProvider($params = array(), $pageSize = 25)
    {
        return new ApiDataProvider(ApiEndpoints::SPORT_TEAM_MEMBER_LIST, array(
            'modelClass' => 'SportTeamMembers',
            'params' => $params,
            'pagination' => array('pageSize' => $pageSize),
        ));
    }

    /**
     * Map attendee_name từ nested attendee object
     */
    public function setAttendee($value)
    {
        if (is_array($value) && isset($value['full_name'])) {
            $this->attendee_name = $value['full_name'];
        }
    }

    /**
     * Đếm số bộ môn CHA mà người đó đã đăng ký
     * Ví dụ: Bóng bàn đơn nam + Bóng bàn đôi nam = 1 bộ môn (Bóng bàn)
     */
    public static function countParentSportsByAttendee($attendeeId)
    {
        $result = ApiClient::get(ApiEndpoints::SPORT_TEAM_MEMBER_LIST, array(
            'attendee_id' => $attendeeId,
            'per_page' => 500,
        ));

        if (!$result['success']) {
            return 0;
        }

        $items = isset($result['data']['data']) ? $result['data']['data'] : (isset($result['data']) ? $result['data'] : array());
        if (!is_array($items)) {
            return 0;
        }

        $parentSportIds = array();
        foreach ($items as $item) {
            if (!isset($item['attendee_id']) || $item['attendee_id'] != $attendeeId) {
                continue;
            }
            $sportId = isset($item['sport_id']) ? $item['sport_id'] : null;
            if (!$sportId && isset($item['sport_team_id'])) {
                $team = SportTeams::fetchFromApi($item['sport_team_id']);
                $sportId = $team ? $team->sport_id : null;
            }
            if ($sportId) {
                $sport = Sports::fetchFromApi($sportId);
                if ($sport) {
                    $parentId = $sport->parent_id ? $sport->parent_id : $sportId;
                    $parentSportIds[$parentId] = true;
                }
            }
        }

        return count($parentSportIds);
    }

    /**
     * Kiểm tra người đó đã có team trong cùng nội dung con (sport_id) chưa
     * Trả về team_id nếu đã có, null nếu chưa
     */
    public static function getExistingTeamInSport($attendeeId, $sportId)
    {
        $result = ApiClient::get(ApiEndpoints::SPORT_TEAM_MEMBER_LIST, array(
            'attendee_id' => $attendeeId,
            'per_page' => 500,
        ));

        if (!$result['success']) {
            return null;
        }

        $items = isset($result['data']['data']) ? $result['data']['data'] : (isset($result['data']) ? $result['data'] : array());
        if (!is_array($items)) {
            return null;
        }

        foreach ($items as $item) {
            if (!isset($item['attendee_id']) || $item['attendee_id'] != $attendeeId) {
                continue;
            }
            $itemSportId = isset($item['sport_id']) ? $item['sport_id'] : null;
            $teamId = isset($item['sport_team_id']) ? $item['sport_team_id'] : null;

            if (!$itemSportId && $teamId) {
                $team = SportTeams::fetchFromApi($teamId);
                $itemSportId = $team ? $team->sport_id : null;
            }

            if ($itemSportId == $sportId) {
                return $teamId;
            }
        }

        return null;
    }

    /**
     * Kiểm tra xem người đó có thể đăng ký thêm môn mới không
     * @param int $attendeeId
     * @param int $newSportId Sport ID mới muốn đăng ký
     * @return array ['can_register' => bool, 'error' => string|null]
     */
    public static function canRegisterSport($attendeeId, $newSportId)
    {
        $newSport = Sports::fetchFromApi($newSportId);
        if (!$newSport) {
            return array('can_register' => false, 'error' => 'Không tìm thấy môn thể thao.');
        }
        $newParentId = $newSport->parent_id ? $newSport->parent_id : $newSportId;

        $result = ApiClient::get(ApiEndpoints::SPORT_TEAM_MEMBER_LIST, array(
            'attendee_id' => $attendeeId,
            'per_page' => 500,
        ));

        if (!$result['success']) {
            return array('can_register' => true, 'error' => null);
        }

        $items = isset($result['data']['data']) ? $result['data']['data'] : (isset($result['data']) ? $result['data'] : array());
        if (!is_array($items)) {
            return array('can_register' => true, 'error' => null);
        }

        $parentSportIds = array();
        foreach ($items as $item) {
            if (!isset($item['attendee_id']) || $item['attendee_id'] != $attendeeId) {
                continue;
            }
            $sportId = isset($item['sport_id']) ? $item['sport_id'] : null;
            $teamId = isset($item['sport_team_id']) ? $item['sport_team_id'] : null;

            if (!$sportId && $teamId) {
                $team = SportTeams::fetchFromApi($teamId);
                $sportId = $team ? $team->sport_id : null;
            }

            if ($sportId == $newSportId) {
                return array(
                    'can_register' => false,
                    'error' => 'Người này đã đăng ký nội dung "' . $newSport->name . '" ở một đội khác.'
                );
            }

            if ($sportId) {
                $sport = Sports::fetchFromApi($sportId);
                if ($sport) {
                    $parentId = $sport->parent_id ? $sport->parent_id : $sportId;
                    $parentSportIds[$parentId] = true;
                }
            }
        }

        if (!isset($parentSportIds[$newParentId]) && count($parentSportIds) >= self::MAX_SPORTS_PER_ATTENDEE) {
            return array(
                'can_register' => false,
                'error' => 'Người này đã đăng ký tối đa ' . self::MAX_SPORTS_PER_ATTENDEE . ' bộ môn thể thao.'
            );
        }

        return array('can_register' => true, 'error' => null);
    }

    /**
     * @deprecated Use countParentSportsByAttendee instead
     */
    public static function countSportsByAttendee($attendeeId)
    {
        return self::countParentSportsByAttendee($attendeeId);
    }

    public static function canRegisterMore($attendeeId)
    {
        return self::countParentSportsByAttendee($attendeeId) < self::MAX_SPORTS_PER_ATTENDEE;
    }

    /**
     * Lấy danh sách attendee_id đã đăng ký team của 1 môn thể thao
     * @param int $sportId ID môn thể thao
     * @param int|null $registrationId Lọc theo registration (optional)
     * @return array Danh sách attendee_id
     */
    public static function getAttendeeIdsBySport($sportId, $registrationId = null)
    {
        $params = array(
            'sport_id' => $sportId,
            'per_page' => 500,
        );
        if ($registrationId) {
            $params['registration_id'] = $registrationId;
        }

        $result = ApiClient::get(ApiEndpoints::SPORT_TEAM_MEMBER_LIST, $params);

        if (!$result['success']) {
            return array();
        }

        $items = isset($result['data']['data']) ? $result['data']['data'] : (isset($result['data']) ? $result['data'] : array());
        if (!is_array($items)) {
            return array();
        }

        $attendeeIds = array();
        foreach ($items as $item) {
            if (isset($item['attendee_id'])) {
                $attendeeIds[] = $item['attendee_id'];
            }
        }

        return array_unique($attendeeIds);
    }
}
