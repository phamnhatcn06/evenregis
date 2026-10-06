<?php

Yii::import('application.models._base.BaseSportStages');

class SportStages extends BaseSportStages
{
    // Thể thức thi đấu
    const FORMAT_ROUND_ROBIN = 'round_robin';
    const FORMAT_SINGLE_ELIMINATION = 'single_elimination';
    const FORMAT_ROUND_ROBIN_KNOCKOUT = 'round_robin_knockout';
    const FORMAT_HEAT_LANE = 'heat_lane';
    const FORMAT_TIME_TRIAL = 'time_trial';

    // Trạng thái bốc thăm
    const DRAW_NOT_DRAWN = 'not_drawn';
    const DRAW_DRAWN = 'drawn';
    const DRAW_LOCKED = 'locked';

    // Thuộc tính cấu hình bốc thăm (không có trong base model)
    public $format;
    public $num_groups;
    public $teams_per_group;
    public $advance_per_group;
    public $bracket_size;
    public $draw_status;
    public $draw_seed;
    public $drawn_by;
    public $drawn_at;

    // Thuộc tính hiển thị
    public $sport_name;
    public $event_name;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * Gán cả các public property (field chỉ có từ API, không phải cột DB)
     * ngoài các attribute AR mặc định.
     */
    public function setAttributes($values, $safeOnly = true)
    {
        if (is_array($values)) {
            $extra = array('format', 'num_groups', 'teams_per_group', 'advance_per_group',
                'bracket_size', 'draw_status', 'draw_seed', 'drawn_by', 'drawn_at',
                'sport_name', 'event_name');
            foreach ($extra as $name) {
                if (array_key_exists($name, $values)) {
                    $this->$name = $values[$name];
                }
            }
        }
        parent::setAttributes($values, $safeOnly);
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = array('format, num_groups, teams_per_group, advance_per_group, bracket_size, draw_status, draw_seed, drawn_by, drawn_at, sport_name, event_name', 'safe');
        return $rules;
    }

    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'event_id' => 'Sự kiện',
            'sport_id' => 'Nội dung thi đấu',
            'name' => 'Tên giai đoạn',
            'stage_type' => 'Loại giai đoạn',
            'format' => 'Thể thức',
            'num_groups' => 'Số bảng',
            'teams_per_group' => 'Số đội mỗi bảng',
            'advance_per_group' => 'Số suất đi tiếp mỗi bảng',
            'bracket_size' => 'Kích thước nhánh',
            'draw_status' => 'Trạng thái bốc thăm',
            'stage_order' => 'Thứ tự',
            'status' => 'Trạng thái',
        );
    }

    public static function fetchFromApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::SPORT_STAGE_DETAIL, array('id' => $id));
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

    public static function getApiDataProvider($params = array(), $pageSize = 1000)
    {
        return new ApiDataProvider(ApiEndpoints::SPORT_STAGE_LIST, array(
            'modelClass' => 'SportStages',
            'params' => $params,
            'pagination' => array('pageSize' => $pageSize),
        ));
    }

    /**
     * Cập nhật cấu hình thể thức/bốc thăm của giai đoạn.
     */
    public function updateConfigViaApi()
    {
        $data = array(
            'name' => $this->name,
            'format' => $this->format,
            'num_groups' => $this->num_groups !== '' ? $this->num_groups : null,
            'teams_per_group' => $this->teams_per_group !== '' ? $this->teams_per_group : null,
            'advance_per_group' => $this->advance_per_group !== '' ? $this->advance_per_group : null,
            'bracket_size' => $this->bracket_size !== '' ? $this->bracket_size : null,
        );
        $url = ApiEndpoints::url(ApiEndpoints::SPORT_STAGE_UPDATE, array('id' => $this->id));
        return ApiClient::post($url, $data);
    }

    // ===== Bốc thăm =====

    public static function drawGroups($stageId)
    {
        $url = ApiEndpoints::url(ApiEndpoints::SPORT_DRAW_GROUPS, array('stageId' => $stageId));
        return ApiClient::post($url, array('auth_email' => self::authEmail()));
    }

    public static function drawBracket($stageId)
    {
        $url = ApiEndpoints::url(ApiEndpoints::SPORT_DRAW_BRACKET, array('stageId' => $stageId));
        return ApiClient::post($url, array('auth_email' => self::authEmail()));
    }

    public static function lockDraw($stageId)
    {
        $url = ApiEndpoints::url(ApiEndpoints::SPORT_DRAW_LOCK, array('stageId' => $stageId));
        return ApiClient::post($url, array('auth_email' => self::authEmail()));
    }

    public static function fetchDrawPreview($stageId)
    {
        $url = ApiEndpoints::url(ApiEndpoints::SPORT_DRAW_PREVIEW, array('stageId' => $stageId));
        $result = ApiClient::get($url);
        if ($result['success'] && isset($result['data'])) {
            return isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
        }
        return null;
    }

    protected static function authEmail()
    {
        $ssoUser = AuthHandler::getUser();
        return isset($ssoUser['email']) ? $ssoUser['email'] : null;
    }

    // ===== Labels & options =====

    public static function getFormatOptions()
    {
        return array(
            self::FORMAT_ROUND_ROBIN => 'Vòng tròn tính điểm',
            self::FORMAT_ROUND_ROBIN_KNOCKOUT => 'Vòng bảng + loại trực tiếp',
            self::FORMAT_SINGLE_ELIMINATION => 'Loại trực tiếp',
            self::FORMAT_HEAT_LANE => 'Phân lượt/làn (bơi)',
            self::FORMAT_TIME_TRIAL => 'Tính thành tích',
        );
    }

    public static function getFormatLabel($format)
    {
        $options = self::getFormatOptions();
        return isset($options[$format]) ? $options[$format] : ($format ?: '—');
    }

    /**
     * Thể thức có chia bảng (round-robin) hay không.
     */
    public static function isGroupFormat($format)
    {
        return in_array($format, array(self::FORMAT_ROUND_ROBIN, self::FORMAT_ROUND_ROBIN_KNOCKOUT));
    }

    public static function isBracketFormat($format)
    {
        return $format === self::FORMAT_SINGLE_ELIMINATION || $format === self::FORMAT_ROUND_ROBIN_KNOCKOUT;
    }

    public static function getDrawStatusLabel($status)
    {
        $labels = array(
            self::DRAW_NOT_DRAWN => '<span class="badge bg-secondary">Chưa bốc thăm</span>',
            self::DRAW_DRAWN => '<span class="badge bg-success">Đã bốc thăm</span>',
            self::DRAW_LOCKED => '<span class="badge bg-dark">Đã khoá</span>',
        );
        return isset($labels[$status]) ? $labels[$status] : '<span class="badge bg-secondary">Chưa bốc thăm</span>';
    }
}
