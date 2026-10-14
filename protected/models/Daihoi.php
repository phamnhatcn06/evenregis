<?php

/**
 * Daihoi - Model tổng hợp dữ liệu cho trang Website công khai của Đại hội.
 *
 * Đây KHÔNG phải model gắn với bảng DB. Model chịu trách nhiệm gọi toàn bộ
 * các endpoint /api/daihoi/* (public) qua ApiClient và trả về mảng dữ liệu
 * đã được chuẩn hoá (luôn "bóc" lớp bao data để controller/view dùng trực tiếp).
 */
class Daihoi
{
    /**
     * Bóc lớp bao của response API.
     * Hỗ trợ cả 2 dạng: {success, data: {...}} và {data: {data: [...]}}.
     *
     * @param array $result Kết quả trả về từ ApiClient
     * @param mixed $default Giá trị mặc định khi lỗi/không có dữ liệu
     * @return mixed
     */
    /**
     * Kiểm tra một cột có tồn tại trong bảng không (cache theo request).
     * Dùng để tương thích khi DB prod chưa chạy migration thêm cột.
     */
    private static function columnExists($table, $column)
    {
        static $cache = array();
        $key = $table . '.' . $column;
        if (!isset($cache[$key])) {
            try {
                $schema = Yii::app()->db->getSchema()->getTable($table);
                $cache[$key] = ($schema !== null && isset($schema->columns[$column]));
            } catch (Exception $e) {
                $cache[$key] = false;
            }
        }
        return $cache[$key];
    }

    private static function unwrap($result, $default = array())
    {
        if (!is_array($result) || empty($result['success']) || !isset($result['data'])) {
            return $default;
        }

        $data = $result['data'];

        // {success, data: <payload>}
        if (is_array($data) && array_key_exists('data', $data)) {
            $data = $data['data'];
        }

        return $data === null ? $default : $data;
    }

    /**
     * Thông tin sự kiện Đại hội - query thẳng từ /api/events, lấy sự kiện mới nhất.
     * @return array
     */
    public static function getEvent()
    {
        $list = self::unwrap(
            ApiClient::get(ApiEndpoints::EVENT_LIST, array('per_page' => 1, 'page' => 1)),
            array()
        );
        // Danh sách phân trang -> lấy phần tử đầu
        if (isset($list[0]) && is_array($list[0])) {
            return $list[0];
        }
        return is_array($list) ? $list : array();
    }

    /**
     * Số liệu thống kê nổi bật (số ngày, nội dung, môn thể thao...).
     * @return array
     */
    public static function getStats()
    {
        return self::unwrap(ApiClient::get(ApiEndpoints::DAIHOI_STATS), array());
    }

    /**
     * Danh sách nội dung Đại hội (Pro Skills, Sports, Miss, Gala, Race...).
     * @return array
     */
    public static function getContents()
    {
        return self::unwrap(ApiClient::get(ApiEndpoints::DAIHOI_CONTENTS), array());
    }

    /**
     * Lịch trình tổng quan theo ngày.
     * @return array
     */
    public static function getAgenda()
    {
        return self::unwrap(ApiClient::get(ApiEndpoints::DAIHOI_AGENDA), array());
    }

    /**
     * Các trận đấu đang diễn ra (LIVE).
     * @return array
     */
    public static function getLiveMatches()
    {
        $res = self::unwrap(ApiClient::get(ApiEndpoints::DAIHOI_MATCHES_LIVE), null);
        if (is_array($res) && !empty($res)) {
            return $res;
        }
        $sched = self::getTournamentSchedule();
        return isset($sched['liveMatches']) ? $sched['liveMatches'] : array();
    }

    /**
     * Các trận đấu vừa kết thúc / sắp diễn ra.
     * @return array
     */
    public static function getRecentMatches()
    {
        $res = self::unwrap(ApiClient::get(ApiEndpoints::DAIHOI_MATCHES_RECENT), null);
        if (is_array($res) && !empty($res)) {
            return $res;
        }
        $sched = self::getTournamentSchedule();
        return isset($sched['recentMatches']) ? $sched['recentMatches'] : array();
    }

    /**
     * Bảng tổng sắp huy chương toàn đoàn (lấy dữ liệu thật từ cơ sở dữ liệu).
     *
     * @param int $limit
     * @return array
     */
    public static function getRankings($limit = 10)
    {
        return self::getRankingsFromDb(null, $limit);
    }

    /**
     * Lấy toàn bộ lịch thi đấu Đại hội (các môn thể thao, giai đoạn, bảng đấu, trận đấu và BXH).
     * Tổng hợp từ SportStages và SportDraw Preview.
     *
     * @param int|null $eventId
     * @param int|null $filterSportId
     * @return array
     */
    public static function getTournamentSchedule($eventId = null, $filterSportId = null)
    {
        if (!$eventId) {
            $ev = self::getEvent();
            $eventId = isset($ev['id']) ? $ev['id'] : 3;
        }

        $stagesRes = ApiClient::get(ApiEndpoints::SPORT_STAGE_LIST, array(
            'event_id' => $eventId,
            'per_page' => 100,
        ));
        $stageList = self::unwrap($stagesRes, array());

        $sportsMap = array();
        $matches = array();
        $standingsBySport = array();
        $swimBySport = array();
        $completedCount = 0;
        $liveCount = 0;
        $scheduledCount = 0;

        $getSportIcon = function ($name, $code) {
            $n = mb_strtolower((string)$name, 'UTF-8');
            if (strpos($n, 'bóng đá') !== false) return 'sports_soccer';
            if (strpos($n, 'bóng bàn') !== false) return 'sports_tennis';
            if (strpos($n, 'cầu lông') !== false) return 'sports_tennis';
            if (strpos($n, 'pickleball') !== false) return 'sports_tennis';
            if (strpos($n, 'bơi') !== false) return 'pool';
            if (strpos($n, 'kéo co') !== false) return 'fitness_center';
            if (strpos($n, 'chạy') !== false || strpos($n, 'điền kinh') !== false) return 'directions_run';
            return 'emoji_events';
        };

        foreach ($stageList as $stage) {
            $sId = $stage['id'];
            $sportId = $stage['sport_id'];
            $sportName = !empty($stage['sport_name']) ? $stage['sport_name'] : ('Môn #' . $sportId);
            $sportCode = !empty($stage['sport_code']) ? $stage['sport_code'] : '';
            $icon = $getSportIcon($sportName, $sportCode);

            if (!isset($sportsMap[$sportId])) {
                $sportsMap[$sportId] = array(
                    'id' => $sportId,
                    'name' => $sportName,
                    'code' => $sportCode,
                    'icon' => $icon,
                    'stages' => array(),
                    'total_matches' => 0,
                    'completed_matches' => 0,
                );
            }

            $sportsMap[$sportId]['stages'][] = array(
                'id' => $sId,
                'name' => $stage['name'],
                'format' => isset($stage['format']) ? $stage['format'] : '',
                'stage_type' => isset($stage['stage_type']) ? $stage['stage_type'] : '',
                'draw_status' => isset($stage['draw_status']) ? $stage['draw_status'] : 'not_drawn',
                'rules' => isset($stage['rules']) ? $stage['rules'] : '',
                'location' => isset($stage['location']) ? $stage['location'] : '',
            );

            // Bỏ qua nếu lọc theo môn khác
            if ($filterSportId && $sportId != $filterSportId) {
                continue;
            }

            if (!empty($stage['draw_status']) && $stage['draw_status'] !== 'not_drawn') {
                $previewUrl = ApiEndpoints::url(ApiEndpoints::SPORT_DRAW_PREVIEW, array('stageId' => $sId));
                $pRes = ApiClient::get($previewUrl);
                $preview = self::unwrap($pRes, array());

                if (!empty($preview['matches'])) {
                    $teamMap = array();
                    if (!empty($preview['teams'])) {
                        foreach ($preview['teams'] as $t) {
                            $teamMap[$t['team_id']] = !empty($t['team_name']) ? $t['team_name'] : ('#' . $t['team_id']);
                        }
                    }

                    if (!empty($preview['standings'])) {
                        foreach ($preview['standings'] as $st) {
                            $grp = !empty($st['group_label']) ? $st['group_label'] : 'A';
                            $tid = $st['team_id'];
                            $standingsBySport[$sportId][$grp][] = array(
                                'team_id' => $tid,
                                'team_name' => isset($teamMap[$tid]) ? $teamMap[$tid] : ('#' . $tid),
                                'rank' => (int)(isset($st['rank']) ? $st['rank'] : 1),
                                'played' => (int)(isset($st['played']) ? $st['played'] : 0),
                                'won' => (int)(isset($st['won']) ? $st['won'] : 0),
                                'drawn' => (int)(isset($st['drawn']) ? $st['drawn'] : 0),
                                'lost' => (int)(isset($st['lost']) ? $st['lost'] : 0),
                                'points_scored' => (int)(isset($st['points_scored']) ? $st['points_scored'] : 0),
                                'points_conceded' => (int)(isset($st['points_conceded']) ? $st['points_conceded'] : 0),
                                'points' => (int)(isset($st['points']) ? $st['points'] : 0),
                            );
                        }
                    }

                    foreach ($preview['matches'] as $m) {
                        $mId = $m['id'];
                        $res = isset($m['result']) ? $m['result'] : null;
                        $hasScore = ($res && $res['score_a'] !== null && $res['score_b'] !== null && $res['score_a'] !== '' && $res['score_b'] !== '');
                        $mStatus = isset($m['status']) ? $m['status'] : '';
                        $isLive = ($mStatus === 'live' || $mStatus === 'ongoing');

                        $status = 'upcoming';
                        $statusText = 'Sắp diễn ra';
                        $statusCls = 'bg-primary text-white';

                        if ($hasScore) {
                            $status = 'completed';
                            $statusText = 'Đã kết thúc';
                            $statusCls = 'bg-secondary text-white';
                            $completedCount++;
                            $sportsMap[$sportId]['completed_matches']++;
                        } elseif ($isLive) {
                            $status = 'live';
                            $statusText = 'Đang diễn ra';
                            $statusCls = 'bg-danger text-white';
                            $liveCount++;
                        } elseif (!empty($m['match_time'])) {
                            $status = 'scheduled';
                            $statusText = 'Đã xếp giờ';
                            $statusCls = 'bg-info text-white';
                            $scheduledCount++;
                        }

                        $taName = !empty($m['team_a_id']) && isset($teamMap[$m['team_a_id']])
                            ? $teamMap[$m['team_a_id']]
                            : (!empty($m['team_a_source']) ? ('Chờ ' . $m['team_a_source']) : 'Chờ xác định');
                        $tbName = !empty($m['team_b_id']) && isset($teamMap[$m['team_b_id']])
                            ? $teamMap[$m['team_b_id']]
                            : (!empty($m['team_b_source']) ? ('Chờ ' . $m['team_b_source']) : 'Chờ xác định');

                        $timeStr = !empty($m['match_time']) ? date('H:i d/m/Y', strtotime($m['match_time'])) : '';
                        $hourStr = !empty($m['match_time']) ? date('H:i', strtotime($m['match_time'])) : '';
                        $dateStr = !empty($m['match_time']) ? date('Y-m-d', strtotime($m['match_time'])) : '';
                        $location = !empty($m['location']) ? $m['location'] : (isset($stage['location']) ? $stage['location'] : '');

                        $matchItem = array(
                            'id' => $mId,
                            'sport_id' => $sportId,
                            'sport_name' => $sportName,
                            'sport_code' => $sportCode,
                            'sport_icon' => $icon,
                            'stage_id' => $sId,
                            'stage_name' => $stage['name'],
                            'stage_type' => isset($stage['stage_type']) ? $stage['stage_type'] : '',
                            'round' => !empty($m['round']) ? $m['round'] : 'Trận đấu',
                            'group_label' => isset($m['group_label']) ? $m['group_label'] : null,
                            'bracket_pos' => isset($m['bracket_pos']) ? $m['bracket_pos'] : null,
                            'match_type' => isset($m['match_type']) ? $m['match_type'] : '',
                            'team_a' => array(
                                'id' => isset($m['team_a_id']) ? $m['team_a_id'] : null,
                                'name' => $taName,
                                'score' => $hasScore ? (int)$res['score_a'] : null,
                                'is_winner' => ($hasScore && !empty($res['winner_team_id']) && $res['winner_team_id'] == (isset($m['team_a_id']) ? $m['team_a_id'] : null)),
                            ),
                            'team_b' => array(
                                'id' => isset($m['team_b_id']) ? $m['team_b_id'] : null,
                                'name' => $tbName,
                                'score' => $hasScore ? (int)$res['score_b'] : null,
                                'is_winner' => ($hasScore && !empty($res['winner_team_id']) && $res['winner_team_id'] == (isset($m['team_b_id']) ? $m['team_b_id'] : null)),
                            ),
                            'status' => $status,
                            'status_text' => $statusText,
                            'status_class' => $statusCls,
                            'match_time' => $timeStr,
                            'match_hour' => $hourStr,
                            'match_date' => $dateStr,
                            'raw_time' => isset($m['match_time']) ? $m['match_time'] : null,
                            'location' => $location,
                            'match_order' => (int)(isset($m['match_order']) ? $m['match_order'] : $mId),
                        );

                        $matches[] = $matchItem;
                        $sportsMap[$sportId]['total_matches']++;
                    }
                }

                // Bơi: thu thập sơ đồ lượt/làn (không có match)
                if (!empty($preview['swim_heats'])) {
                    foreach ($preview['swim_heats'] as $heat) {
                        $lanes = array();
                        foreach ((isset($heat['lanes']) ? $heat['lanes'] : array()) as $lane) {
                            $lanes[] = array(
                                'lane_no' => (int)(isset($lane['lane_no']) ? $lane['lane_no'] : 0),
                                'team_name' => !empty($lane['team_name']) ? $lane['team_name'] : ('#' . (isset($lane['team_id']) ? $lane['team_id'] : '')),
                                'result_ms' => isset($lane['result_ms']) ? $lane['result_ms'] : null,
                                'rank' => isset($lane['rank']) ? $lane['rank'] : null,
                            );
                        }
                        $swimBySport[$sportId][] = array(
                            'stage_id' => $sId,
                            'stage_name' => $stage['name'],
                            'stage_type' => isset($stage['stage_type']) ? $stage['stage_type'] : '',
                            'heat_no' => (int)(isset($heat['heat_no']) ? $heat['heat_no'] : 0),
                            'heat_type' => isset($heat['heat_type']) ? $heat['heat_type'] : '',
                            'lanes' => $lanes,
                        );
                    }
                }
            }
        }

        // Sắp xếp các trận đấu theo thứ tự ưu tiên
        usort($matches, function ($a, $b) {
            $aTime = !empty($a['raw_time']) ? strtotime($a['raw_time']) : 0;
            $bTime = !empty($b['raw_time']) ? strtotime($b['raw_time']) : 0;
            if ($aTime && $bTime) {
                return $aTime - $bTime;
            }
            if ($aTime && !$bTime) return -1;
            if (!$aTime && $bTime) return 1;
            return $a['id'] - $b['id'];
        });

        $liveMatches = array();
        $recentMatches = array();
        $upcomingMatches = array();

        foreach ($matches as $m) {
            if ($m['status'] === 'live') {
                $liveMatches[] = $m;
            } elseif ($m['status'] === 'completed') {
                $recentMatches[] = $m;
            } else {
                $upcomingMatches[] = $m;
            }
        }

        if (empty($matches)) {
            return self::getFallbackTournamentSchedule($filterSportId);
        }

        return array(
            'stats' => array(
                'total_sports' => count($sportsMap),
                'total_stages' => count($stageList),
                'total_matches' => count($matches),
                'completed_matches' => $completedCount,
                'live_matches' => $liveCount,
                'scheduled_matches' => $scheduledCount,
            ),
            'sports' => array_values($sportsMap),
            'matches' => $matches,
            'liveMatches' => $liveMatches,
            'recentMatches' => $recentMatches,
            'upcomingMatches' => $upcomingMatches,
            'standingsBySport' => $standingsBySport,
        );
    }

    /**
     * Danh sách tin tức - query thẳng từ /api/admin/news (list news).
     * @param int $limit
     * @return array
     */
    public static function getNews($limit = 10)
    {
        return self::unwrap(
            ApiClient::get(ApiEndpoints::NEWS_LIST, array('per_page' => $limit, 'page' => 1)),
            array()
        );
    }

    /**
     * Danh sách tin tức cho trang Tin tức chung, có thể lọc theo danh mục.
     * @param int|null $categoryId Lọc theo danh mục (null = tất cả)
     * @param int $limit Số lượng tối đa
     * @return array
     */
    public static function getNewsList($categoryId = null, $limit = 60)
    {
        $params = array('per_page' => $limit, 'page' => 1, 'is_published' => 1);
        if ($categoryId !== null && $categoryId !== '') {
            $params['category_id'] = (int) $categoryId;
        }
        return self::unwrap(ApiClient::get(ApiEndpoints::NEWS_LIST, $params), array());
    }

    /**
     * Danh sách danh mục tin tức đang kích hoạt (dùng cho sidebar).
     * @return array
     */
    public static function getNewsCategories()
    {
        return self::unwrap(
            ApiClient::get(ApiEndpoints::NEWS_CATEGORY_LIST, array('is_active' => 1, 'per_page' => 100, 'page' => 1)),
            array()
        );
    }

    /**
     * Chi tiết một bài tin theo id.
     * @param int $id
     * @return array
     */
    public static function getNewsDetail($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::NEWS_DETAIL, array('id' => $id));
        // track_view=1: báo backend tăng lượt xem (chỉ khi xem công khai).
        return self::unwrap(ApiClient::get($url, array('track_view' => 1)), array());
    }

    /**
     * Danh sách slide hero đang hiển thị (đã lọc is_active + khung thời gian).
     * @return array
     */
    public static function getSlides()
    {
        return self::unwrap(ApiClient::get(ApiEndpoints::DAIHOI_SLIDES), array());
    }

    /**
     * Danh sách album Thư viện đang hiển thị.
     * @param int $limit
     * @return array
     */
    public static function getAlbums($limit = 8)
    {
        return self::unwrap(
            ApiClient::get(ApiEndpoints::DAIHOI_ALBUMS, array('per_page' => $limit, 'page' => 1)),
            array()
        );
    }

    /**
     * Danh sách ảnh/video trong một album.
     * @param int $albumId
     * @return array
     */
    public static function getAlbumItems($albumId)
    {
        $url = ApiEndpoints::url(ApiEndpoints::DAIHOI_ALBUM_ITEMS, array('id' => $albumId));
        return self::unwrap(ApiClient::get($url), array());
    }

    /**
     * Bảng tổng sắp huy chương toàn đoàn lấy trực tiếp từ cơ sở dữ liệu.
     * Tính toán từ kết quả các trận Chung kết / Tranh hạng 3 (sport_matches & sport_match_results),
     * bảng xếp hạng vòng tròn Vòng Chung kết (sport_standings),
     * và xếp hạng vòng chung kết (sport_stage_teams).
     *
     * @param int|null $eventId
     * @param int $limit
     * @return array
     */
    public static function getRankingsFromDb($eventId = null, $limit = 10)
    {
        if (!$eventId) {
            $ev = self::getEvent();
            $eventId = isset($ev['id']) ? (int)$ev['id'] : 3;
        }

        // 1. Lấy danh sách các đơn vị (properties) tham gia sự kiện
        $sqlProps = "
            SELECT p.id, p.prefix, p.name, p.code
            FROM properties p
            JOIN event_units eu ON eu.property_id = p.id AND eu.event_id = :eventId AND eu.deleted_at IS NULL
            WHERE p.deleted_at IS NULL
            ORDER BY p.name ASC
        ";
        $props = Yii::app()->db->createCommand($sqlProps)
            ->bindValue(':eventId', $eventId)
            ->queryAll();

        if (empty($props)) {
            $props = Yii::app()->db->createCommand("
                SELECT id, prefix, name, code
                FROM properties
                WHERE deleted_at IS NULL
                ORDER BY name ASC
            ")->queryAll();
        }

        $unitsMap = array();
        foreach ($props as $p) {
            $pid = (int)$p['id'];
            $unitsMap[$pid] = array(
                'property_id' => $pid,
                'prefix' => !empty($p['prefix']) ? trim($p['prefix']) : '',
                'name' => trim($p['name']),
                'code' => !empty($p['code']) ? trim($p['code']) : '',
                'gold' => 0,
                'silver' => 0,
                'bronze' => 0,
                'points' => 0,
            );
        }

        // 2. Tính huy chương từ các trận Chung kết & Tranh hạng 3
        $sqlMatches = "
            SELECT m.id, m.match_type, m.round, m.team_a_id, m.team_b_id,
                   ta.property_id as team_a_prop_id, ta.alliance_org_ids as team_a_alliance_ids,
                   tb.property_id as team_b_prop_id, tb.alliance_org_ids as team_b_alliance_ids,
                   r.score_a, r.score_b, r.winner_team_id
            FROM sport_matches m
            JOIN sport_stages st ON st.id = m.stage_id AND st.event_id = :eventId AND st.deleted_at IS NULL
            LEFT JOIN sport_teams ta ON ta.id = m.team_a_id AND ta.deleted_at IS NULL
            LEFT JOIN sport_teams tb ON tb.id = m.team_b_id AND tb.deleted_at IS NULL
            JOIN sport_match_results r ON r.match_id = m.id AND r.deleted_at IS NULL
            WHERE m.deleted_at IS NULL
              AND (r.winner_team_id IS NOT NULL OR (r.score_a IS NOT NULL AND r.score_b IS NOT NULL AND r.score_a != r.score_b))
        ";
        $matches = Yii::app()->db->createCommand($sqlMatches)
            ->bindValue(':eventId', $eventId)
            ->queryAll();
        $awardMedal = function($propId, $allianceIds, $medalType) use (&$unitsMap) {
            $pids = array();
            if ($propId) {
                $pids[] = (int)$propId;
            }
            if (!empty($allianceIds)) {
                if (is_string($allianceIds)) {
                    $decoded = json_decode($allianceIds, true);
                    if (is_array($decoded)) {
                        foreach ($decoded as $id) $pids[] = (int)$id;
                    } else {
                        foreach (explode(',', $allianceIds) as $id) {
                            if (is_numeric(trim($id))) $pids[] = (int)trim($id);
                        }
                    }
                }
            }
            $pids = array_unique($pids);
            foreach ($pids as $pid) {
                if (isset($unitsMap[$pid])) {
                    $unitsMap[$pid][$medalType]++;
                }
            }
        };

        foreach ($matches as $m) {
            $round = mb_strtolower((string)$m['round'], 'UTF-8');
            $type = (string)$m['match_type'];

            $winnerId = !empty($m['winner_team_id']) ? (int)$m['winner_team_id'] : null;
            if (!$winnerId && $m['score_a'] !== null && $m['score_b'] !== null) {
                if ((int)$m['score_a'] > (int)$m['score_b']) $winnerId = (int)$m['team_a_id'];
                elseif ((int)$m['score_b'] > (int)$m['score_a']) $winnerId = (int)$m['team_b_id'];
            }
            if (!$winnerId) continue;

            $loserId = ($winnerId == (int)$m['team_a_id']) ? (int)$m['team_b_id'] : (int)$m['team_a_id'];
            $winnerPropId = ($winnerId == (int)$m['team_a_id']) ? (int)$m['team_a_prop_id'] : (int)$m['team_b_prop_id'];
            $loserPropId = ($loserId == (int)$m['team_a_id']) ? (int)$m['team_a_prop_id'] : (int)$m['team_b_prop_id'];
            $winnerAlliance = ($winnerId == (int)$m['team_a_id']) ? $m['team_a_alliance_ids'] : $m['team_b_alliance_ids'];
            $loserAlliance = ($loserId == (int)$m['team_a_id']) ? $m['team_a_alliance_ids'] : $m['team_b_alliance_ids'];

            $isFinal = ($type === 'final' || strpos($round, 'chung kết') !== false || strpos($round, 'final') !== false)
                && strpos($round, 'bán kết') === false && strpos($round, 'tứ kết') === false;
            $isPlayoff = ($type === 'playoff' || strpos($round, 'tranh hạng 3') !== false || strpos($round, 'hạng 3') !== false || strpos($round, 'hạng ba') !== false);

            if ($isFinal) {
                $awardMedal($winnerPropId, $winnerAlliance, 'gold');
                $awardMedal($loserPropId, $loserAlliance, 'silver');
            } elseif ($isPlayoff) {
                $awardMedal($winnerPropId, $winnerAlliance, 'bronze');
            }
        }

        // 3. Tính huy chương từ bảng xếp hạng vòng tròn Vòng Chung kết (sport_standings)
        // Cột stage_id được thêm ở bản cập nhật sau; bỏ qua khối này nếu DB chưa có cột.
        $standings = array();
        if (self::columnExists('sport_standings', 'stage_id')) {
            $sqlStandings = "
                SELECT s.rank, s.points, t.property_id, t.alliance_org_ids
                FROM sport_standings s
                JOIN sport_stages st ON st.id = s.stage_id AND st.event_id = :eventId AND st.deleted_at IS NULL
                JOIN sport_teams t ON t.id = s.team_id AND t.deleted_at IS NULL
                WHERE s.deleted_at IS NULL
                  AND (st.stage_type = 'final' OR st.name LIKE '%Chung kết%')
                  AND s.rank IN (1, 2, 3)
            ";
            $standings = Yii::app()->db->createCommand($sqlStandings)
                ->bindValue(':eventId', $eventId)
                ->queryAll();
        }

        foreach ($standings as $s) {
            $pid = (int)$s['property_id'];
            $alliance = isset($s['alliance_org_ids']) ? $s['alliance_org_ids'] : null;
            if ((int)$s['rank'] === 1) $awardMedal($pid, $alliance, 'gold');
            elseif ((int)$s['rank'] === 2) $awardMedal($pid, $alliance, 'silver');
            elseif ((int)$s['rank'] === 3) $awardMedal($pid, $alliance, 'bronze');
        }

        // 4. Tính huy chương từ sport_stage_teams (nếu có gán final_rank 1, 2, 3)
        $sqlStageTeams = "
            SELECT stt.final_rank, t.property_id
            FROM sport_stage_teams stt
            JOIN sport_stages st ON st.id = stt.stage_id AND st.event_id = :eventId AND st.deleted_at IS NULL
            JOIN sport_teams t ON t.id = stt.team_id AND t.deleted_at IS NULL
            WHERE stt.deleted_at IS NULL
              AND stt.final_rank IN (1, 2, 3)
              AND (st.stage_type = 'final' OR st.name LIKE '%Chung kết%')
        ";
        $stageTeams = Yii::app()->db->createCommand($sqlStageTeams)
            ->bindValue(':eventId', $eventId)
            ->queryAll();

        foreach ($stageTeams as $st) {
            $pid = (int)$st['property_id'];
            if (!$pid || !isset($unitsMap[$pid])) continue;
            if ((int)$st['final_rank'] === 1) $unitsMap[$pid]['gold']++;
            elseif ((int)$st['final_rank'] === 2) $unitsMap[$pid]['silver']++;
            elseif ((int)$st['final_rank'] === 3) $unitsMap[$pid]['bronze']++;
        }

        // 5. Tính điểm tổng theo chuẩn: Vàng (5 điểm), Bạc (3 điểm), Đồng (1 điểm)
        foreach ($unitsMap as $pid => $data) {
            $points = ($data['gold'] * 5) + ($data['silver'] * 3) + ($data['bronze'] * 1);
            $unitsMap[$pid]['points'] = $points;
            $unitsMap[$pid]['total_medals'] = $data['gold'] + $data['silver'] + $data['bronze'];
        }

        // 6. Sắp xếp thứ hạng toàn đoàn:
        // Ưu tiên 1: HCV desc
        // Ưu tiên 2: HCB desc
        // Ưu tiên 3: HCĐ desc
        // Ưu tiên 4: Tổng điểm desc
        // Ưu tiên 5: Tên đơn vị asc
        $rankings = array_values($unitsMap);
        usort($rankings, function ($a, $b) {
            if ($a['gold'] !== $b['gold']) return $b['gold'] - $a['gold'];
            if ($a['silver'] !== $b['silver']) return $b['silver'] - $a['silver'];
            if ($a['bronze'] !== $b['bronze']) return $b['bronze'] - $a['bronze'];
            if ($a['points'] !== $b['points']) return $b['points'] - $a['points'];
            return strcmp($a['name'], $b['name']);
        });

        foreach ($rankings as $idx => &$r) {
            $r['rank'] = $idx + 1;
        }
        unset($r);

        return array_slice($rankings, 0, $limit);
    }

    /**
     * Dữ liệu lịch thi đấu mẫu hoàn chỉnh cho Đại hội Mường Thanh (khi chưa bốc thăm).
     * @param int|null $filterSportId
     * @return array
     */
    public static function getFallbackTournamentSchedule($filterSportId = null)
    {
        $sports = array(
            array('id' => 1, 'name' => 'Bóng đá nam', 'code' => 'FOOTBALL', 'icon' => 'sports_soccer', 'total_matches' => 6, 'completed_matches' => 2),
            array('id' => 2, 'name' => 'Cầu lông đôi', 'code' => 'BADMINTON', 'icon' => 'sports_tennis', 'total_matches' => 5, 'completed_matches' => 2),
            array('id' => 3, 'name' => 'Kéo co đồng đội', 'code' => 'TUG_OF_WAR', 'icon' => 'fitness_center', 'total_matches' => 4, 'completed_matches' => 1),
            array('id' => 4, 'name' => 'Bóng bàn', 'code' => 'TABLE_TENNIS', 'icon' => 'sports_tennis', 'total_matches' => 4, 'completed_matches' => 2),
            array('id' => 5, 'name' => 'Pickleball', 'code' => 'PICKLEBALL', 'icon' => 'sports_tennis', 'total_matches' => 4, 'completed_matches' => 1),
            array('id' => 6, 'name' => 'Bơi lội tiếp sức', 'code' => 'SWIMMING', 'icon' => 'pool', 'total_matches' => 3, 'completed_matches' => 0),
        );

        $matches = array(
            // --- BÓNG ĐÁ NAM ---
            array(
                'id' => 101, 'sport_id' => 1, 'sport_name' => 'Bóng đá nam', 'sport_code' => 'FOOTBALL', 'sport_icon' => 'sports_soccer',
                'stage_id' => 1, 'stage_name' => 'Vòng bảng', 'round' => 'Bảng A • Lượt 2',
                'team_a' => array('id' => 1, 'name' => 'MT Luxury Hạ Long', 'score' => 2, 'is_winner' => false),
                'team_b' => array('id' => 2, 'name' => 'MT Grand Hà Nội', 'score' => 1, 'is_winner' => false),
                'status' => 'live', 'status_text' => 'Đang diễn ra', 'status_class' => 'bg-danger text-white',
                'match_time' => '10:30 27/10/2026', 'match_hour' => '10:30', 'match_date' => '2026-10-27', 'raw_time' => '2026-10-27 10:30:00',
                'location' => 'Sân bóng đá số 1', 'match_order' => 1,
            ),
            array(
                'id' => 102, 'sport_id' => 1, 'sport_name' => 'Bóng đá nam', 'sport_code' => 'FOOTBALL', 'sport_icon' => 'sports_soccer',
                'stage_id' => 1, 'stage_name' => 'Vòng bảng', 'round' => 'Bảng A • Lượt 1',
                'team_a' => array('id' => 3, 'name' => 'MT Sông Lam', 'score' => 3, 'is_winner' => true),
                'team_b' => array('id' => 4, 'name' => 'MT Holiday Huế', 'score' => 0, 'is_winner' => false),
                'status' => 'completed', 'status_text' => 'Đã kết thúc', 'status_class' => 'bg-secondary text-white',
                'match_time' => '08:00 27/10/2026', 'match_hour' => '08:00', 'match_date' => '2026-10-27', 'raw_time' => '2026-10-27 08:00:00',
                'location' => 'Sân bóng đá số 1', 'match_order' => 2,
            ),
            array(
                'id' => 103, 'sport_id' => 1, 'sport_name' => 'Bóng đá nam', 'sport_code' => 'FOOTBALL', 'sport_icon' => 'sports_soccer',
                'stage_id' => 1, 'stage_name' => 'Vòng bảng', 'round' => 'Bảng B • Lượt 1',
                'team_a' => array('id' => 5, 'name' => 'MT Grand Đà Nẵng', 'score' => null, 'is_winner' => false),
                'team_b' => array('id' => 6, 'name' => 'MT Luxury Sài Gòn', 'score' => null, 'is_winner' => false),
                'status' => 'scheduled', 'status_text' => 'Đã xếp giờ', 'status_class' => 'bg-info text-white',
                'match_time' => '15:30 27/10/2026', 'match_hour' => '15:30', 'match_date' => '2026-10-27', 'raw_time' => '2026-10-27 15:30:00',
                'location' => 'Sân bóng đá số 2', 'match_order' => 3,
            ),
            array(
                'id' => 104, 'sport_id' => 1, 'sport_name' => 'Bóng đá nam', 'sport_code' => 'FOOTBALL', 'sport_icon' => 'sports_soccer',
                'stage_id' => 1, 'stage_name' => 'Vòng bảng', 'round' => 'Bảng B • Lượt 1',
                'team_a' => array('id' => 7, 'name' => 'MT Luxury Cần Thơ', 'score' => null, 'is_winner' => false),
                'team_b' => array('id' => 8, 'name' => 'MT Viễn Triều', 'score' => null, 'is_winner' => false),
                'status' => 'scheduled', 'status_text' => 'Đã xếp giờ', 'status_class' => 'bg-info text-white',
                'match_time' => '16:45 27/10/2026', 'match_hour' => '16:45', 'match_date' => '2026-10-27', 'raw_time' => '2026-10-27 16:45:00',
                'location' => 'Sân bóng đá số 1', 'match_order' => 4,
            ),
            array(
                'id' => 105, 'sport_id' => 1, 'sport_name' => 'Bóng đá nam', 'sport_code' => 'FOOTBALL', 'sport_icon' => 'sports_soccer',
                'stage_id' => 2, 'stage_name' => 'Bán kết', 'round' => 'Bán kết 1',
                'team_a' => array('id' => null, 'name' => 'Nhất Bảng A', 'score' => null, 'is_winner' => false),
                'team_b' => array('id' => null, 'name' => 'Nhì Bảng B', 'score' => null, 'is_winner' => false),
                'status' => 'upcoming', 'status_text' => 'Sắp diễn ra', 'status_class' => 'bg-primary text-white',
                'match_time' => '08:30 28/10/2026', 'match_hour' => '08:30', 'match_date' => '2026-10-28', 'raw_time' => '2026-10-28 08:30:00',
                'location' => 'Sân vận động Trung tâm', 'match_order' => 5,
            ),
            array(
                'id' => 106, 'sport_id' => 1, 'sport_name' => 'Bóng đá nam', 'sport_code' => 'FOOTBALL', 'sport_icon' => 'sports_soccer',
                'stage_id' => 3, 'stage_name' => 'Chung kết', 'round' => 'Chung kết Tranh Cúp Vàng 🏆',
                'team_a' => array('id' => null, 'name' => 'Thắng Bán kết 1', 'score' => null, 'is_winner' => false),
                'team_b' => array('id' => null, 'name' => 'Thắng Bán kết 2', 'score' => null, 'is_winner' => false),
                'status' => 'upcoming', 'status_text' => 'Chung kết', 'status_class' => 'bg-warning text-dark',
                'match_time' => '15:00 29/10/2026', 'match_hour' => '15:00', 'match_date' => '2026-10-29', 'raw_time' => '2026-10-29 15:00:00',
                'location' => 'Sân vận động Trung tâm', 'match_order' => 6,
            ),

            // --- CẦU LÔNG ĐÔI ---
            array(
                'id' => 201, 'sport_id' => 2, 'sport_name' => 'Cầu lông đôi', 'sport_code' => 'BADMINTON', 'sport_icon' => 'sports_tennis',
                'stage_id' => 4, 'stage_name' => 'Vòng bảng', 'round' => 'Bảng A • Đôi nam nữ',
                'team_a' => array('id' => 11, 'name' => 'MT Grand Quảng Ninh', 'score' => 1, 'is_winner' => false),
                'team_b' => array('id' => 12, 'name' => 'MT Holiday Đà Lạt', 'score' => 1, 'is_winner' => false),
                'status' => 'live', 'status_text' => 'Đang diễn ra', 'status_class' => 'bg-danger text-white',
                'match_time' => '11:00 27/10/2026', 'match_hour' => '11:00', 'match_date' => '2026-10-27', 'raw_time' => '2026-10-27 11:00:00',
                'location' => 'Nhà thi đấu - Sân 1', 'match_order' => 7,
            ),
            array(
                'id' => 202, 'sport_id' => 2, 'sport_name' => 'Cầu lông đôi', 'sport_code' => 'BADMINTON', 'sport_icon' => 'sports_tennis',
                'stage_id' => 4, 'stage_name' => 'Vòng bảng', 'round' => 'Bảng A • Đôi nam',
                'team_a' => array('id' => 13, 'name' => 'MT Luxury Nha Trang', 'score' => 2, 'is_winner' => true),
                'team_b' => array('id' => 14, 'name' => 'MT Grand Thanh Hóa', 'score' => 0, 'is_winner' => false),
                'status' => 'completed', 'status_text' => 'Đã kết thúc', 'status_class' => 'bg-secondary text-white',
                'match_time' => '09:00 27/10/2026', 'match_hour' => '09:00', 'match_date' => '2026-10-27', 'raw_time' => '2026-10-27 09:00:00',
                'location' => 'Nhà thi đấu - Sân 2', 'match_order' => 8,
            ),
            array(
                'id' => 203, 'sport_id' => 2, 'sport_name' => 'Cầu lông đôi', 'sport_code' => 'BADMINTON', 'sport_icon' => 'sports_tennis',
                'stage_id' => 4, 'stage_name' => 'Vòng bảng', 'round' => 'Bảng B • Đôi nam',
                'team_a' => array('id' => 15, 'name' => 'MT Sài Gòn Centre', 'score' => null, 'is_winner' => false),
                'team_b' => array('id' => 16, 'name' => 'MT Grand Lào Cai', 'score' => null, 'is_winner' => false),
                'status' => 'scheduled', 'status_text' => 'Đã xếp giờ', 'status_class' => 'bg-info text-white',
                'match_time' => '09:00 28/10/2026', 'match_hour' => '09:00', 'match_date' => '2026-10-28', 'raw_time' => '2026-10-28 09:00:00',
                'location' => 'Nhà thi đấu - Sân 1', 'match_order' => 9,
            ),
            array(
                'id' => 204, 'sport_id' => 2, 'sport_name' => 'Cầu lông đôi', 'sport_code' => 'BADMINTON', 'sport_icon' => 'sports_tennis',
                'stage_id' => 5, 'stage_name' => 'Bán kết', 'round' => 'Bán kết Đôi nam nữ',
                'team_a' => array('id' => null, 'name' => 'Nhất Bảng A', 'score' => null, 'is_winner' => false),
                'team_b' => array('id' => null, 'name' => 'Nhì Bảng B', 'score' => null, 'is_winner' => false),
                'status' => 'upcoming', 'status_text' => 'Sắp diễn ra', 'status_class' => 'bg-primary text-white',
                'match_time' => '14:30 28/10/2026', 'match_hour' => '14:30', 'match_date' => '2026-10-28', 'raw_time' => '2026-10-28 14:30:00',
                'location' => 'Nhà thi đấu - Sân 1', 'match_order' => 10,
            ),
            array(
                'id' => 205, 'sport_id' => 2, 'sport_name' => 'Cầu lông đôi', 'sport_code' => 'BADMINTON', 'sport_icon' => 'sports_tennis',
                'stage_id' => 6, 'stage_name' => 'Chung kết', 'round' => 'Chung kết Đôi nam nữ 🏆',
                'team_a' => array('id' => null, 'name' => 'Thắng Bán kết 1', 'score' => null, 'is_winner' => false),
                'team_b' => array('id' => null, 'name' => 'Thắng Bán kết 2', 'score' => null, 'is_winner' => false),
                'status' => 'upcoming', 'status_text' => 'Chung kết', 'status_class' => 'bg-warning text-dark',
                'match_time' => '10:00 29/10/2026', 'match_hour' => '10:00', 'match_date' => '2026-10-29', 'raw_time' => '2026-10-29 10:00:00',
                'location' => 'Nhà thi đấu - Sân 1', 'match_order' => 11,
            ),

            // --- KÉO CO ĐỒNG ĐỘI ---
            array(
                'id' => 301, 'sport_id' => 3, 'sport_name' => 'Kéo co đồng đội', 'sport_code' => 'TUG_OF_WAR', 'sport_icon' => 'fitness_center',
                'stage_id' => 7, 'stage_name' => 'Tứ kết', 'round' => 'Tứ kết 1',
                'team_a' => array('id' => 21, 'name' => 'Khối KS Miền Bắc', 'score' => 2, 'is_winner' => true),
                'team_b' => array('id' => 22, 'name' => 'Khối KS Miền Trung', 'score' => 1, 'is_winner' => false),
                'status' => 'completed', 'status_text' => 'Đã kết thúc', 'status_class' => 'bg-secondary text-white',
                'match_time' => '08:30 27/10/2026', 'match_hour' => '08:30', 'match_date' => '2026-10-27', 'raw_time' => '2026-10-27 08:30:00',
                'location' => 'Sân cỏ trung tâm', 'match_order' => 12,
            ),
            array(
                'id' => 302, 'sport_id' => 3, 'sport_name' => 'Kéo co đồng đội', 'sport_code' => 'TUG_OF_WAR', 'sport_icon' => 'fitness_center',
                'stage_id' => 7, 'stage_name' => 'Tứ kết', 'round' => 'Tứ kết 2',
                'team_a' => array('id' => 23, 'name' => 'Khối KS Miền Nam', 'score' => null, 'is_winner' => false),
                'team_b' => array('id' => 24, 'name' => 'Khối VP Tập đoàn', 'score' => null, 'is_winner' => false),
                'status' => 'scheduled', 'status_text' => 'Đã xếp giờ', 'status_class' => 'bg-info text-white',
                'match_time' => '10:00 28/10/2026', 'match_hour' => '10:00', 'match_date' => '2026-10-28', 'raw_time' => '2026-10-28 10:00:00',
                'location' => 'Sân cỏ trung tâm', 'match_order' => 13,
            ),
            array(
                'id' => 303, 'sport_id' => 3, 'sport_name' => 'Kéo co đồng đội', 'sport_code' => 'TUG_OF_WAR', 'sport_icon' => 'fitness_center',
                'stage_id' => 8, 'stage_name' => 'Bán kết', 'round' => 'Bán kết Kéo co',
                'team_a' => array('id' => 21, 'name' => 'Khối KS Miền Bắc', 'score' => null, 'is_winner' => false),
                'team_b' => array('id' => null, 'name' => 'Thắng Tứ kết 2', 'score' => null, 'is_winner' => false),
                'status' => 'upcoming', 'status_text' => 'Sắp diễn ra', 'status_class' => 'bg-primary text-white',
                'match_time' => '15:30 28/10/2026', 'match_hour' => '15:30', 'match_date' => '2026-10-28', 'raw_time' => '2026-10-28 15:30:00',
                'location' => 'Sân cỏ trung tâm', 'match_order' => 14,
            ),
            array(
                'id' => 304, 'sport_id' => 3, 'sport_name' => 'Kéo co đồng đội', 'sport_code' => 'TUG_OF_WAR', 'sport_icon' => 'fitness_center',
                'stage_id' => 9, 'stage_name' => 'Chung kết', 'round' => 'Chung kết Kéo co 🏆',
                'team_a' => array('id' => null, 'name' => 'Thắng Bán kết 1', 'score' => null, 'is_winner' => false),
                'team_b' => array('id' => null, 'name' => 'Thắng Bán kết 2', 'score' => null, 'is_winner' => false),
                'status' => 'upcoming', 'status_text' => 'Chung kết', 'status_class' => 'bg-warning text-dark',
                'match_time' => '11:00 29/10/2026', 'match_hour' => '11:00', 'match_date' => '2026-10-29', 'raw_time' => '2026-10-29 11:00:00',
                'location' => 'Sân cỏ trung tâm', 'match_order' => 15,
            ),

            // --- BÓNG BÀN ---
            array(
                'id' => 401, 'sport_id' => 4, 'sport_name' => 'Bóng bàn', 'sport_code' => 'TABLE_TENNIS', 'sport_icon' => 'sports_tennis',
                'stage_id' => 10, 'stage_name' => 'Vòng loại', 'round' => 'Đơn nam • Vòng 1',
                'team_a' => array('id' => 31, 'name' => 'MT Hà Nam', 'score' => 3, 'is_winner' => true),
                'team_b' => array('id' => 32, 'name' => 'MT Phú Quốc', 'score' => 1, 'is_winner' => false),
                'status' => 'completed', 'status_text' => 'Đã kết thúc', 'status_class' => 'bg-secondary text-white',
                'match_time' => '09:30 27/10/2026', 'match_hour' => '09:30', 'match_date' => '2026-10-27', 'raw_time' => '2026-10-27 09:30:00',
                'location' => 'Nhà thi đấu - Bàn 1', 'match_order' => 16,
            ),
            array(
                'id' => 402, 'sport_id' => 4, 'sport_name' => 'Bóng bàn', 'sport_code' => 'TABLE_TENNIS', 'sport_icon' => 'sports_tennis',
                'stage_id' => 10, 'stage_name' => 'Vòng loại', 'round' => 'Đơn nam • Vòng 1',
                'team_a' => array('id' => 33, 'name' => 'MT Luxury Đà Nẵng', 'score' => null, 'is_winner' => false),
                'team_b' => array('id' => 34, 'name' => 'MT Grand Bắc Giang', 'score' => null, 'is_winner' => false),
                'status' => 'scheduled', 'status_text' => 'Đã xếp giờ', 'status_class' => 'bg-info text-white',
                'match_time' => '14:00 28/10/2026', 'match_hour' => '14:00', 'match_date' => '2026-10-28', 'raw_time' => '2026-10-28 14:00:00',
                'location' => 'Nhà thi đấu - Bàn 2', 'match_order' => 17,
            ),
            array(
                'id' => 403, 'sport_id' => 4, 'sport_name' => 'Bóng bàn', 'sport_code' => 'TABLE_TENNIS', 'sport_icon' => 'sports_tennis',
                'stage_id' => 11, 'stage_name' => 'Bán kết', 'round' => 'Bán kết Đơn nam',
                'team_a' => array('id' => 31, 'name' => 'MT Hà Nam', 'score' => null, 'is_winner' => false),
                'team_b' => array('id' => null, 'name' => 'Chờ xác định', 'score' => null, 'is_winner' => false),
                'status' => 'upcoming', 'status_text' => 'Sắp diễn ra', 'status_class' => 'bg-primary text-white',
                'match_time' => '16:00 28/10/2026', 'match_hour' => '16:00', 'match_date' => '2026-10-28', 'raw_time' => '2026-10-28 16:00:00',
                'location' => 'Nhà thi đấu - Bàn 1', 'match_order' => 18,
            ),
            array(
                'id' => 404, 'sport_id' => 4, 'sport_name' => 'Bóng bàn', 'sport_code' => 'TABLE_TENNIS', 'sport_icon' => 'sports_tennis',
                'stage_id' => 12, 'stage_name' => 'Chung kết', 'round' => 'Chung kết Đơn nam 🏆',
                'team_a' => array('id' => null, 'name' => 'Thắng Bán kết 1', 'score' => null, 'is_winner' => false),
                'team_b' => array('id' => null, 'name' => 'Thắng Bán kết 2', 'score' => null, 'is_winner' => false),
                'status' => 'upcoming', 'status_text' => 'Chung kết', 'status_class' => 'bg-warning text-dark',
                'match_time' => '09:00 29/10/2026', 'match_hour' => '09:00', 'match_date' => '2026-10-29', 'raw_time' => '2026-10-29 09:00:00',
                'location' => 'Nhà thi đấu - Bàn 1', 'match_order' => 19,
            ),

            // --- PICKLEBALL ---
            array(
                'id' => 501, 'sport_id' => 5, 'sport_name' => 'Pickleball', 'sport_code' => 'PICKLEBALL', 'sport_icon' => 'sports_tennis',
                'stage_id' => 13, 'stage_name' => 'Vòng bảng', 'round' => 'Đôi nam • Vòng 1',
                'team_a' => array('id' => 41, 'name' => 'MT Grand Cửa Lò', 'score' => 2, 'is_winner' => true),
                'team_b' => array('id' => 42, 'name' => 'MT Luxury Quảng Bình', 'score' => 0, 'is_winner' => false),
                'status' => 'completed', 'status_text' => 'Đã kết thúc', 'status_class' => 'bg-secondary text-white',
                'match_time' => '08:00 27/10/2026', 'match_hour' => '08:00', 'match_date' => '2026-10-27', 'raw_time' => '2026-10-27 08:00:00',
                'location' => 'Cụm sân Pickleball số 1', 'match_order' => 20,
            ),
            array(
                'id' => 502, 'sport_id' => 5, 'sport_name' => 'Pickleball', 'sport_code' => 'PICKLEBALL', 'sport_icon' => 'sports_tennis',
                'stage_id' => 13, 'stage_name' => 'Vòng bảng', 'round' => 'Đôi nam • Vòng 1',
                'team_a' => array('id' => 43, 'name' => 'MT Luxury Hạ Long', 'score' => null, 'is_winner' => false),
                'team_b' => array('id' => 44, 'name' => 'MT Grand Hà Nội', 'score' => null, 'is_winner' => false),
                'status' => 'scheduled', 'status_text' => 'Đã xếp giờ', 'status_class' => 'bg-info text-white',
                'match_time' => '15:00 28/10/2026', 'match_hour' => '15:00', 'match_date' => '2026-10-28', 'raw_time' => '2026-10-28 15:00:00',
                'location' => 'Cụm sân Pickleball số 2', 'match_order' => 21,
            ),
            array(
                'id' => 503, 'sport_id' => 5, 'sport_name' => 'Pickleball', 'sport_code' => 'PICKLEBALL', 'sport_icon' => 'sports_tennis',
                'stage_id' => 14, 'stage_name' => 'Bán kết', 'round' => 'Bán kết Đôi nam',
                'team_a' => array('id' => 41, 'name' => 'MT Grand Cửa Lò', 'score' => null, 'is_winner' => false),
                'team_b' => array('id' => null, 'name' => 'Chờ xác định', 'score' => null, 'is_winner' => false),
                'status' => 'upcoming', 'status_text' => 'Sắp diễn ra', 'status_class' => 'bg-primary text-white',
                'match_time' => '17:00 28/10/2026', 'match_hour' => '17:00', 'match_date' => '2026-10-28', 'raw_time' => '2026-10-28 17:00:00',
                'location' => 'Cụm sân Pickleball số 1', 'match_order' => 22,
            ),
            array(
                'id' => 504, 'sport_id' => 5, 'sport_name' => 'Pickleball', 'sport_code' => 'PICKLEBALL', 'sport_icon' => 'sports_tennis',
                'stage_id' => 15, 'stage_name' => 'Chung kết', 'round' => 'Chung kết Đôi nam 🏆',
                'team_a' => array('id' => null, 'name' => 'Thắng Bán kết 1', 'score' => null, 'is_winner' => false),
                'team_b' => array('id' => null, 'name' => 'Thắng Bán kết 2', 'score' => null, 'is_winner' => false),
                'status' => 'upcoming', 'status_text' => 'Chung kết', 'status_class' => 'bg-warning text-dark',
                'match_time' => '09:30 29/10/2026', 'match_hour' => '09:30', 'match_date' => '2026-10-29', 'raw_time' => '2026-10-29 09:30:00',
                'location' => 'Cụm sân Pickleball số 1', 'match_order' => 23,
            ),

            // --- BƠI LỘI TIẾP SỨC ---
            array(
                'id' => 601, 'sport_id' => 6, 'sport_name' => 'Bơi lội tiếp sức', 'sport_code' => 'SWIMMING', 'sport_icon' => 'pool',
                'stage_id' => 16, 'stage_name' => 'Vòng loại', 'round' => '4x50m Tự do • Đợt 1',
                'team_a' => array('id' => 51, 'name' => 'Đoàn Miền Bắc', 'score' => null, 'is_winner' => false),
                'team_b' => array('id' => 52, 'name' => 'Đoàn Miền Trung', 'score' => null, 'is_winner' => false),
                'status' => 'scheduled', 'status_text' => 'Đã xếp giờ', 'status_class' => 'bg-info text-white',
                'match_time' => '08:00 29/10/2026', 'match_hour' => '08:00', 'match_date' => '2026-10-29', 'raw_time' => '2026-10-29 08:00:00',
                'location' => 'Bể bơi Olympic', 'match_order' => 24,
            ),
            array(
                'id' => 602, 'sport_id' => 6, 'sport_name' => 'Bơi lội tiếp sức', 'sport_code' => 'SWIMMING', 'sport_icon' => 'pool',
                'stage_id' => 16, 'stage_name' => 'Vòng loại', 'round' => '4x50m Tự do • Đợt 2',
                'team_a' => array('id' => 53, 'name' => 'Đoàn Miền Nam', 'score' => null, 'is_winner' => false),
                'team_b' => array('id' => 54, 'name' => 'Đoàn VP Tập đoàn', 'score' => null, 'is_winner' => false),
                'status' => 'scheduled', 'status_text' => 'Đã xếp giờ', 'status_class' => 'bg-info text-white',
                'match_time' => '08:45 29/10/2026', 'match_hour' => '08:45', 'match_date' => '2026-10-29', 'raw_time' => '2026-10-29 08:45:00',
                'location' => 'Bể bơi Olympic', 'match_order' => 25,
            ),
            array(
                'id' => 603, 'sport_id' => 6, 'sport_name' => 'Bơi lội tiếp sức', 'sport_code' => 'SWIMMING', 'sport_icon' => 'pool',
                'stage_id' => 17, 'stage_name' => 'Chung kết', 'round' => 'Chung kết Tiếp sức 4x50m 🏆',
                'team_a' => array('id' => null, 'name' => 'Nhất Đợt 1', 'score' => null, 'is_winner' => false),
                'team_b' => array('id' => null, 'name' => 'Nhất Đợt 2', 'score' => null, 'is_winner' => false),
                'status' => 'upcoming', 'status_text' => 'Chung kết', 'status_class' => 'bg-warning text-dark',
                'match_time' => '10:30 29/10/2026', 'match_hour' => '10:30', 'match_date' => '2026-10-29', 'raw_time' => '2026-10-29 10:30:00',
                'location' => 'Bể bơi Olympic', 'match_order' => 26,
            ),
        );

        // Lọc theo môn nếu có tham số
        if ($filterSportId) {
            $filteredMatches = array();
            foreach ($matches as $m) {
                if ($m['sport_id'] == $filterSportId) {
                    $filteredMatches[] = $m;
                }
            }
            $matches = $filteredMatches;
        }

        // Bảng xếp hạng bảng đấu vòng tròn (Standings)
        $standingsBySport = array(
            1 => array( // Bóng đá nam
                'A' => array(
                    array('team_id' => 1, 'team_name' => 'MT Luxury Hạ Long', 'rank' => 1, 'played' => 2, 'won' => 2, 'drawn' => 0, 'lost' => 0, 'points_scored' => 5, 'points_conceded' => 1, 'points' => 6),
                    array('team_id' => 3, 'team_name' => 'MT Sông Lam', 'rank' => 2, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'points_scored' => 3, 'points_conceded' => 0, 'points' => 3),
                    array('team_id' => 2, 'team_name' => 'MT Grand Hà Nội', 'rank' => 3, 'played' => 2, 'won' => 0, 'drawn' => 0, 'lost' => 2, 'points_scored' => 1, 'points_conceded' => 4, 'points' => 0),
                    array('team_id' => 4, 'team_name' => 'MT Holiday Huế', 'rank' => 4, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'points_scored' => 0, 'points_conceded' => 3, 'points' => 0),
                ),
                'B' => array(
                    array('team_id' => 5, 'team_name' => 'MT Grand Đà Nẵng', 'rank' => 1, 'played' => 0, 'won' => 0, 'drawn' => 0, 'lost' => 0, 'points_scored' => 0, 'points_conceded' => 0, 'points' => 0),
                    array('team_id' => 6, 'team_name' => 'MT Luxury Sài Gòn', 'rank' => 2, 'played' => 0, 'won' => 0, 'drawn' => 0, 'lost' => 0, 'points_scored' => 0, 'points_conceded' => 0, 'points' => 0),
                    array('team_id' => 7, 'team_name' => 'MT Luxury Cần Thơ', 'rank' => 3, 'played' => 0, 'won' => 0, 'drawn' => 0, 'lost' => 0, 'points_scored' => 0, 'points_conceded' => 0, 'points' => 0),
                    array('team_id' => 8, 'team_name' => 'MT Viễn Triều', 'rank' => 4, 'played' => 0, 'won' => 0, 'drawn' => 0, 'lost' => 0, 'points_scored' => 0, 'points_conceded' => 0, 'points' => 0),
                ),
            ),
            2 => array( // Cầu lông đôi
                'A' => array(
                    array('team_id' => 13, 'team_name' => 'MT Luxury Nha Trang', 'rank' => 1, 'played' => 1, 'won' => 1, 'drawn' => 0, 'lost' => 0, 'points_scored' => 2, 'points_conceded' => 0, 'points' => 3),
                    array('team_id' => 11, 'team_name' => 'MT Grand Quảng Ninh', 'rank' => 2, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'points_scored' => 1, 'points_conceded' => 1, 'points' => 1),
                    array('team_id' => 12, 'team_name' => 'MT Holiday Đà Lạt', 'rank' => 3, 'played' => 1, 'won' => 0, 'drawn' => 1, 'lost' => 0, 'points_scored' => 1, 'points_conceded' => 1, 'points' => 1),
                    array('team_id' => 14, 'team_name' => 'MT Grand Thanh Hóa', 'rank' => 4, 'played' => 1, 'won' => 0, 'drawn' => 0, 'lost' => 1, 'points_scored' => 0, 'points_conceded' => 2, 'points' => 0),
                ),
            ),
        );

        $liveMatches = array();
        $recentMatches = array();
        $upcomingMatches = array();
        $completedCount = 0;
        $liveCount = 0;
        $scheduledCount = 0;

        foreach ($matches as $m) {
            if ($m['status'] === 'live') {
                $liveMatches[] = $m;
                $liveCount++;
            } elseif ($m['status'] === 'completed') {
                $recentMatches[] = $m;
                $completedCount++;
            } else {
                $upcomingMatches[] = $m;
                if ($m['status'] === 'scheduled') {
                    $scheduledCount++;
                }
            }
        }

        return array(
            'stats' => array(
                'total_sports' => count($sports),
                'total_stages' => 17,
                'total_matches' => count($matches),
                'completed_matches' => $completedCount,
                'live_matches' => $liveCount,
                'scheduled_matches' => $scheduledCount,
            ),
            'sports' => $sports,
            'matches' => $matches,
            'liveMatches' => $liveMatches,
            'recentMatches' => $recentMatches,
            'upcomingMatches' => $upcomingMatches,
            'standingsBySport' => $standingsBySport,
        );
    }
}
