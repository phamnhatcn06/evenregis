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
     * Bảng xếp hạng tạm thời.
     * @param int $limit
     * @return array
     */
    public static function getRankings($limit = 10)
    {
        $res = self::unwrap(
            ApiClient::get(ApiEndpoints::DAIHOI_RANKINGS, array('limit' => $limit)),
            null
        );
        if (is_array($res) && !empty($res)) {
            return $res;
        }
        return array();
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
     * Chi tiết một bài tin theo id.
     * @param int $id
     * @return array
     */
    public static function getNewsDetail($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::NEWS_DETAIL, array('id' => $id));
        return self::unwrap(ApiClient::get($url), array());
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
}
