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
    const FORMAT_PLAYOFF = 'playoff';

    // Tiêu chí phụ khi bằng điểm
    const TIEBREAK_HEAD_TO_HEAD = 'head_to_head';
    const TIEBREAK_GOAL_DIFFERENCE = 'goal_difference';

    // Cách ghép đội từ bảng vào loại trực tiếp
    const PAIRING_CROSS = 'cross';
    const PAIRING_PLACEMENT = 'placement';

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
    public $win_points;
    public $draw_points;
    public $loss_points;
    public $tiebreak;
    public $ko_pairing;
    public $playoff_source_stage_id;

    // Thuộc tính hiển thị
    public $sport_name;
    public $sport_code;
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
                'win_points', 'draw_points', 'loss_points', 'tiebreak', 'ko_pairing',
                'playoff_source_stage_id',
                'sport_name', 'sport_code', 'event_name');
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
        $rules[] = array('format, num_groups, teams_per_group, advance_per_group, bracket_size, draw_status, draw_seed, drawn_by, drawn_at, win_points, draw_points, loss_points, tiebreak, ko_pairing, playoff_source_stage_id, sport_name, event_name', 'safe');
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
     * Tạo mới giai đoạn qua API (dùng cho seed).
     * Gửi cả cột DB và cấu hình thể thức/bốc thăm.
     */
    public function storeViaApi()
    {
        $data = array(
            'event_id' => $this->event_id,
            'sport_id' => $this->sport_id,
            'name' => $this->name,
            'stage_type' => $this->stage_type,
            'stage_order' => $this->stage_order,
            'location' => $this->location,
            'rules' => $this->rules,
            'status' => $this->status,
            'format' => $this->format,
            'num_groups' => $this->num_groups !== '' ? $this->num_groups : null,
            'teams_per_group' => $this->teams_per_group !== '' ? $this->teams_per_group : null,
            'advance_per_group' => $this->advance_per_group !== '' ? $this->advance_per_group : null,
            'bracket_size' => $this->bracket_size !== '' ? $this->bracket_size : null,
        );
        $data = array_filter($data, function ($value) {
            return $value !== null && $value !== '';
        });
        return self::apiPost(ApiEndpoints::SPORT_STAGE_STORE, $data);
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
        // Không gửi stage_type/stage_order ở đây: model tạo bằng `new` mang giá trị mặc định của cột
        // (qualification / 1) sẽ ghi đè cấu trúc thật. Đổi cấu trúc dùng updateStructureViaApi().
        foreach (array('win_points', 'draw_points', 'loss_points', 'tiebreak', 'ko_pairing') as $name) {
            if ($this->$name !== null && $this->$name !== '') {
                $data[$name] = $this->$name;
            }
        }
        if ($this->rules !== null && $this->rules !== '') {
            $data['rules'] = $this->rules;
        }
        if ($this->location !== null && $this->location !== '') {
            $data['location'] = $this->location;
        }
        $url = ApiEndpoints::url(ApiEndpoints::SPORT_STAGE_UPDATE, array('id' => $this->id));
        return self::apiPost($url, $data);
    }

    /**
     * Cập nhật CẤU TRÚC giai đoạn (tên, loại, thứ tự) — chỉ gửi đúng các trường được truyền vào.
     *
     * @param string $id
     * @param array $fields Một phần của ['name' => , 'stage_type' => , 'stage_order' => ]
     */
    public static function updateStructureViaApi($id, $fields)
    {
        $model = self::fetchFromApi($id);
        if ($model === null) {
            return array('success' => false, 'code' => 404, 'data' => null, 'error' => 'Không tìm thấy giai đoạn.');
        }
        // name là trường bắt buộc khi update -> luôn gửi (giữ tên cũ nếu không đổi)
        $data = array('name' => isset($fields['name']) ? $fields['name'] : $model->name);
        foreach (array('stage_type', 'stage_order') as $name) {
            if (isset($fields[$name])) {
                $data[$name] = $fields[$name];
            }
        }
        $url = ApiEndpoints::url(ApiEndpoints::SPORT_STAGE_UPDATE, array('id' => $id));
        return self::apiPost($url, $data);
    }

    // ===== Bốc thăm =====

    public static function drawGroups($stageId)
    {
        $url = ApiEndpoints::url(ApiEndpoints::SPORT_DRAW_GROUPS, array('stageId' => $stageId));
        return self::apiPost($url, array('auth_email' => self::authEmail()));
    }

    public static function drawBracket($stageId)
    {
        $url = ApiEndpoints::url(ApiEndpoints::SPORT_DRAW_BRACKET, array('stageId' => $stageId));
        return self::apiPost($url, array('auth_email' => self::authEmail()));
    }

    public static function lockDraw($stageId)
    {
        $url = ApiEndpoints::url(ApiEndpoints::SPORT_DRAW_LOCK, array('stageId' => $stageId));
        return self::apiPost($url, array('auth_email' => self::authEmail()));
    }

    public static function drawPlayoff($stageId)
    {
        $url = ApiEndpoints::url(ApiEndpoints::SPORT_DRAW_PLAYOFF, array('stageId' => $stageId));
        return self::apiPost($url, array('auth_email' => self::authEmail()));
    }

    /**
     * Chuyển các đội/VĐV từ vòng chung kết sang giai đoạn play-off.
     *
     * @param string $stageId giai đoạn play-off đích
     * @param array $teamIds danh sách team_id lấy từ vòng chung kết
     */
    public static function movePlayoffEntrants($stageId, $teamIds)
    {
        $url = ApiEndpoints::url(ApiEndpoints::SPORT_DRAW_PLAYOFF_ENTRANTS, array('stageId' => $stageId));
        return self::apiPost($url, array(
            'team_ids' => array_values($teamIds),
            'auth_email' => self::authEmail(),
        ));
    }

    /**
     * Chuyển người/đội thắng play-off vào giai đoạn vòng bảng đích.
     *
     * @param string $playoffStageId giai đoạn play-off (nguồn)
     * @param string $targetStageId giai đoạn vòng bảng đích
     */
    public static function promotePlayoffWinners($playoffStageId, $targetStageId)
    {
        $url = ApiEndpoints::url(ApiEndpoints::SPORT_DRAW_PROMOTE_PLAYOFF, array('stageId' => $playoffStageId));
        return self::apiPost($url, array(
            'target_stage_id' => $targetStageId,
            'auth_email' => self::authEmail(),
        ));
    }

    /**
     * Chuyển đội đi tiếp của vòng loại chia bảng sang giai đoạn kế tiếp.
     */
    public static function promoteGroupWinners($qualStageId, $targetStageId)
    {
        $url = ApiEndpoints::url(ApiEndpoints::SPORT_DRAW_PROMOTE_GROUPS, array('stageId' => $qualStageId));
        return self::apiPost($url, array(
            'target_stage_id' => $targetStageId,
            'auth_email' => self::authEmail(),
        ));
    }

    // ===== Bơi (heat/lane) =====

    public static function drawSwimHeats($stageId, $laneCount = 6)
    {
        $url = ApiEndpoints::url(ApiEndpoints::SPORT_DRAW_SWIM_HEATS, array('stageId' => $stageId));
        return self::apiPost($url, array('lane_count' => $laneCount, 'auth_email' => self::authEmail()));
    }

    /**
     * Ghi thành tích bơi theo làn.
     *
     * @param string $stageId
     * @param array $times Mảng [lane_id => result_ms]
     * @param array $lanes Mảng [lane_id => lane_no] (sửa làn thủ công, tuỳ chọn)
     */
    public static function recordSwimTimes($stageId, $times, $lanes = array())
    {
        $url = ApiEndpoints::url(ApiEndpoints::SPORT_DRAW_SWIM_TIMES, array('stageId' => $stageId));
        return self::apiPost($url, array(
            'times' => (object) $times,
            'lanes' => (object) $lanes,
            'auth_email' => self::authEmail(),
        ));
    }

    public static function promoteSwimTop($qualStageId, $finalStageId, $topN = 6)
    {
        $url = ApiEndpoints::url(ApiEndpoints::SPORT_DRAW_PROMOTE_SWIM, array('stageId' => $qualStageId));
        return self::apiPost($url, array(
            'final_stage_id' => $finalStageId,
            'top_n' => $topN,
            'auth_email' => self::authEmail(),
        ));
    }

    /**
     * Nạp thành tích hạt giống bơi (khớp theo tên VĐV / mã đơn vị ở backend).
     *
     * @param string $stageId giai đoạn vòng loại bơi
     * @param array $entries Mảng ['name' => , 'unit' => , 'time_ms' => ]
     * @param bool $dryRun true = chỉ xem kết quả khớp, không ghi
     */
    public static function importSwimSeeds($stageId, $entries, $dryRun = false)
    {
        $url = ApiEndpoints::url(ApiEndpoints::SPORT_DRAW_SWIM_SEEDS, array('stageId' => $stageId));
        return self::apiPost($url, array(
            'entries' => $entries,
            'dry_run' => $dryRun ? 1 : 0,
            'auth_email' => self::authEmail(),
        ));
    }

    public static function isSwimFormat($format)
    {
        return $format === self::FORMAT_HEAT_LANE;
    }

    public static function computeStandings($stageId)
    {
        $url = ApiEndpoints::url(ApiEndpoints::SPORT_DRAW_STANDINGS, array('stageId' => $stageId));
        return self::apiPost($url, array('auth_email' => self::authEmail()));
    }

    public static function generateKnockout($stageId)
    {
        $url = ApiEndpoints::url(ApiEndpoints::SPORT_DRAW_GENERATE_KNOCKOUT, array('stageId' => $stageId));
        return self::apiPost($url, array('auth_email' => self::authEmail()));
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

    /**
     * Lưu kết quả gán thủ công (kéo-thả) đội vào bảng / vị trí seed.
     *
     * @param string $stageId
     * @param array $assignments Mảng phần tử:
     *   - với vòng bảng:  ['team_id' => , 'group_label' => 'A', 'group_order' => 1]
     *   - với nhánh:      ['team_id' => , 'seed' => 1]
     * @return array kết quả ApiClient
     */
    public static function saveManualDraw($stageId, $assignments)
    {
        $url = ApiEndpoints::url(ApiEndpoints::SPORT_DRAW_ASSIGN, array('stageId' => $stageId));
        return self::apiPost($url, array(
            'assignments' => $assignments,
            'auth_email' => self::authEmail(),
        ));
    }

    /**
     * POST tới API và nhận diện lỗi nghiệp vụ: backend trả HTTP 200 nhưng body có code >= 400
     * (vd "Cần ít nhất 2 đội") -> đánh dấu success=false + error = message của backend.
     * Giữ nguyên 'code' HTTP để phân biệt với lỗi kết nối (code 0) / lỗi máy chủ (>= 500).
     */
    protected static function apiPost($url, $data)
    {
        $result = ApiClient::post($url, $data);
        if (!empty($result['success']) && isset($result['data']['code']) && (int) $result['data']['code'] >= 400) {
            $result['success'] = false;
            $result['error'] = isset($result['data']['message']) ? $result['data']['message'] : 'Lỗi từ máy chủ API';
        }
        return $result;
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
            self::FORMAT_PLAYOFF => 'Play-off (trận độc lập)',
        );
    }

    public static function getTiebreakOptions()
    {
        return array(
            self::TIEBREAK_HEAD_TO_HEAD => 'Đối đầu → Hiệu số → Số ghi được',
            self::TIEBREAK_GOAL_DIFFERENCE => 'Hiệu số → Số ghi được → Đối đầu',
        );
    }

    public static function getPairingOptions()
    {
        return array(
            self::PAIRING_CROSS => 'Bán kết chéo (1A-2B, 1B-2A)',
            self::PAIRING_PLACEMENT => 'Xếp hạng (Nhất tranh 1-2, Nhì tranh 3-4)',
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
