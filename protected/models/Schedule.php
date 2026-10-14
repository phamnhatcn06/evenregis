<?php

/**
 * Schedule - Model tổng hợp "lịch trình cá nhân" của người tham dự trên Portal.
 *
 * Đây KHÔNG phải model gắn bảng DB. Nhiệm vụ: gọi các endpoint /api/schedule/*
 * (Phương án A — endpoint chuyên dụng ở backend) qua ApiClient cho từng nguồn
 * (thể thao, thi nghiệp vụ, agenda, bữa ăn, tiệc), chuẩn hoá về "item lịch" chung,
 * rồi gộp - sắp xếp - chia ngày - tính "Sự kiện tiếp theo" ở tầng Model (BE).
 *
 * Toàn bộ thời gian là Unix timestamp (INT). Hiển thị theo giờ Việt Nam cố định.
 *
 * Cấu trúc item chuẩn hoá:
 *   array(
 *     'type'     => self::TYPE_*,          // loại nguồn
 *     'title'    => string,                // tiêu đề chính
 *     'subtitle' => string,                // phụ đề (bộ môn/vòng/số báo danh...)
 *     'startTime'=> int|null,              // Unix ts; null = chưa có lịch
 *     'endTime'  => int|null,
 *     'location' => string,
 *     'status'   => self::STATUS_*,        // tính tại buildTimeline
 *     'refId'    => string|null,
 *   )
 */
class Schedule
{
    const TZ = 'Asia/Ho_Chi_Minh';

    // Loại nguồn lịch
    const TYPE_SPORT = 'sport';
    const TYPE_COMPETITION = 'competition';
    const TYPE_AGENDA = 'agenda';
    const TYPE_MEAL = 'meal';
    const TYPE_BANQUET = 'banquet';

    // Trạng thái item (gán khi dựng timeline)
    const STATUS_UPCOMING = 0; // Sắp tới
    const STATUS_ONGOING = 1;  // Đang diễn ra (now >= startTime)
    const STATUS_DONE = 2;     // Đã diễn ra (now >= endTime, nếu có)
    const STATUS_NO_TIME = 3;  // Chưa có giờ cụ thể

    /**
     * Nhãn tiếng Việt + badge cho trạng thái.
     */
    public static function getStatusLabel($status)
    {
        $labels = array(
            self::STATUS_UPCOMING => '<span class="badge bg-primary">Sắp tới</span>',
            self::STATUS_ONGOING => '<span class="badge bg-success">Đang diễn ra</span>',
            self::STATUS_DONE => '<span class="badge bg-secondary">Đã diễn ra</span>',
            self::STATUS_NO_TIME => '<span class="badge bg-warning text-dark">Chưa có giờ</span>',
        );
        return isset($labels[$status]) ? $labels[$status] : $status;
    }

    /**
     * Toàn bộ lịch cá nhân của một người tham dự trong một sự kiện.
     * Phase 0: các nguồn trả rỗng; Phase 1 nối dần getSportItems()... vào đây.
     *
     * @param string|int $attendeeId
     * @param string|int $eventId
     * @return array cấu trúc timeline (xem buildTimeline)
     */
    public static function getForAttendee($attendeeId, $eventId)
    {
        $items = array();
        $items = array_merge($items, self::getSportItems($attendeeId, $eventId));
        $items = array_merge($items, self::getCompetitionItems($attendeeId, $eventId));
        // Phase 1 sẽ bổ sung tiếp:
        // $items = array_merge($items, self::getAgendaItems($eventId));
        // $items = array_merge($items, self::getMealItems($attendeeId, $eventId));
        // $items = array_merge($items, self::getBanquetItems($attendeeId, $eventId));
        return self::buildTimeline($items);
    }

    /**
     * Nguồn Thể thao: các trận đấu người tham dự có tham gia (qua đội của họ).
     *
     * @param string|int $attendeeId
     * @param string|int $eventId
     * @return array danh sách item chuẩn hoá (chưa gán status)
     */
    public static function getSportItems($attendeeId, $eventId)
    {
        $result = ApiClient::get(ApiEndpoints::SCHEDULE_SPORT, array(
            'attendee_id' => $attendeeId,
            'event_id' => $eventId,
        ));
        return self::unwrapList($result);
    }

    /**
     * Bóc danh sách item từ response ApiClient ({success, data:{code,message,data:[...]}}).
     *
     * @param array $result kết quả ApiClient
     * @return array
     */
    private static function unwrapList($result)
    {
        if (empty($result['success']) || !isset($result['data'])) {
            return array();
        }
        $data = $result['data'];
        if (isset($data['data']) && is_array($data['data'])) {
            return $data['data'];
        }
        return is_array($data) ? $data : array();
    }

    /**
     * Dựng timeline từ danh sách item đã chuẩn hoá — PHẦN LOGIC THUẦN, không phụ
     * thuộc Yii/DB, để test độc lập được.
     *
     * - Tách item không có giờ (startTime null) ra "noTime".
     * - Gán trạng thái theo mốc now.
     * - Sắp xếp item có giờ tăng dần theo startTime.
     * - Chia nhóm theo ngày (giờ VN).
     * - nextUp = (các) item gần nhất có startTime >= now.
     *
     * @param array $items danh sách item chuẩn hoá
     * @param int|null $now mốc hiện tại (Unix); null = time() — cho test
     * @return array { serverTime, nextUp[], days[], noTime[] }
     */
    public static function buildTimeline(array $items, $now = null)
    {
        if ($now === null) {
            $now = time();
        }

        $timed = array();
        $noTime = array();

        foreach ($items as $item) {
            $item = self::fillDefaults($item);
            if ($item['startTime'] === null || $item['startTime'] === '' || (int) $item['startTime'] <= 0) {
                $item['startTime'] = null;
                $item['status'] = self::STATUS_NO_TIME;
                $noTime[] = $item;
                continue;
            }
            $item['startTime'] = (int) $item['startTime'];
            $item['endTime'] = ($item['endTime'] !== null && $item['endTime'] !== '') ? (int) $item['endTime'] : null;
            $item['status'] = self::computeStatus($item['startTime'], $item['endTime'], $now);
            $item['startLabel'] = self::formatVnDateTime($item['startTime']);
            $timed[] = $item;
        }

        usort($timed, array(__CLASS__, 'compareByStartTime'));

        return array(
            'serverTime' => $now,
            'nextUp' => self::pickNextUp($timed, $now),
            'days' => self::groupByDay($timed),
            'noTime' => $noTime,
        );
    }

    /**
     * Trạng thái của một item theo mốc now.
     * "Đang diễn ra" tính từ ĐÚNG startTime (không ngưỡng). Nếu có endTime và đã qua
     * endTime thì coi là đã diễn ra xong.
     */
    public static function computeStatus($startTime, $endTime, $now)
    {
        if ($now < $startTime) {
            return self::STATUS_UPCOMING;
        }
        if ($endTime !== null && $now >= $endTime) {
            return self::STATUS_DONE;
        }
        return self::STATUS_ONGOING;
    }

    /**
     * (Các) item gần nhất có startTime >= now. Nhiều item cùng mốc gần nhất → trả cả nhóm.
     * Danh sách đầu vào PHẢI đã sắp xếp tăng dần.
     */
    public static function pickNextUp(array $sortedTimed, $now)
    {
        $next = array();
        foreach ($sortedTimed as $item) {
            if ($item['startTime'] < $now) {
                continue;
            }
            if (empty($next)) {
                $next[] = $item;
            } elseif ($item['startTime'] === $next[0]['startTime']) {
                $next[] = $item;
            } else {
                break;
            }
        }
        return $next;
    }

    /**
     * Gom item (đã sắp xếp) theo ngày lịch giờ VN.
     * @return array danh sách { date (Y-m-d), dateLabel, items[] }
     */
    public static function groupByDay(array $sortedTimed)
    {
        $tz = new DateTimeZone(self::TZ);
        $groups = array();
        $order = array();
        foreach ($sortedTimed as $item) {
            $dt = new DateTime('@' . $item['startTime']);
            $dt->setTimezone($tz);
            $key = $dt->format('Y-m-d');
            if (!isset($groups[$key])) {
                $groups[$key] = array(
                    'date' => $key,
                    'dateLabel' => self::formatVnDate($item['startTime']),
                    'items' => array(),
                );
                $order[] = $key;
            }
            $groups[$key]['items'][] = $item;
        }
        $result = array();
        foreach ($order as $key) {
            $result[] = $groups[$key];
        }
        return $result;
    }

    /** So sánh 2 item theo startTime tăng dần (cho usort). */
    public static function compareByStartTime($a, $b)
    {
        if ($a['startTime'] === $b['startTime']) {
            return 0;
        }
        return ($a['startTime'] < $b['startTime']) ? -1 : 1;
    }

    /** Điền giá trị mặc định cho item để tránh thiếu khoá. */
    private static function fillDefaults(array $item)
    {
        return array_merge(array(
            'type' => '',
            'title' => '',
            'subtitle' => '',
            'startTime' => null,
            'endTime' => null,
            'location' => '',
            'status' => self::STATUS_NO_TIME,
            'refId' => null,
        ), $item);
    }

    /** Định dạng ngày giờ VN, ví dụ "08:30 - 01/11/2026". */
    public static function formatVnDateTime($timestamp)
    {
        $dt = new DateTime('@' . (int) $timestamp);
        $dt->setTimezone(new DateTimeZone(self::TZ));
        return $dt->format('H:i - d/m/Y');
    }

    /** Định dạng ngày VN, ví dụ "Chủ nhật, 01/11/2026". */
    public static function formatVnDate($timestamp)
    {
        $dt = new DateTime('@' . (int) $timestamp);
        $dt->setTimezone(new DateTimeZone(self::TZ));
        $weekdays = array(
            'Sunday' => 'Chủ nhật', 'Monday' => 'Thứ Hai', 'Tuesday' => 'Thứ Ba',
            'Wednesday' => 'Thứ Tư', 'Thursday' => 'Thứ Năm', 'Friday' => 'Thứ Sáu',
            'Saturday' => 'Thứ Bảy',
        );
        $wd = $weekdays[$dt->format('l')];
        return $wd . ', ' . $dt->format('d/m/Y');
    }
}
