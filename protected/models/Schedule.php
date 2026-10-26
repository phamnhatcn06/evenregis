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
     * Phân loại nâng cao và xác định màu sắc, nhãn, icon cho item lịch trình.
     * Tự động nhận diện theo loại nguồn (type) hoặc từ khoá trong tiêu đề, phụ đề, địa điểm.
     *
     * @param array $item
     * @return array
     */
    public static function getItemMeta($item)
    {
        $type = isset($item['type']) ? strtolower(trim((string)$item['type'])) : '';
        $category = isset($item['category']) ? strtolower(trim((string)$item['category'])) : '';
        $title = isset($item['title']) ? mb_strtolower((string)$item['title'], 'UTF-8') : '';
        $sub = isset($item['subtitle']) ? mb_strtolower((string)$item['subtitle'], 'UTF-8') : '';
        $desc = isset($item['description']) ? mb_strtolower((string)$item['description'], 'UTF-8') : '';
        $combined = $type . ' ' . $category . ' ' . $title . ' ' . $sub . ' ' . $desc;

        // 1. Ẩm thực (Bữa ăn, ăn sáng, trưa, tối, buffet, coffee break...)
        if ($type === self::TYPE_MEAL
            || strpos($combined, 'ăn sáng') !== false
            || strpos($combined, 'ăn trưa') !== false
            || strpos($combined, 'ăn tối') !== false
            || strpos($combined, 'buffet') !== false
            || strpos($combined, 'ẩm thực') !== false
            || strpos($combined, 'bữa ăn') !== false
            || strpos($combined, 'coffee break') !== false
            || strpos($combined, 'tiệc trà') !== false
            || strpos($combined, 'cơm trưa') !== false) {
            return array(
                'key' => 'meal',
                'label' => 'ẨM THỰC',
                'color' => '#ea580c',
                'bg_light' => '#fff7ed',
                'text_color' => '#ea580c',
                'icon_type' => 'cutlery',
            );
        }

        // 2. Thể thao
        if ($type === self::TYPE_SPORT
            || strpos($combined, 'thể thao') !== false
            || strpos($combined, 'kéo co') !== false
            || strpos($combined, 'bóng đá') !== false
            || strpos($combined, 'cầu lông') !== false
            || strpos($combined, 'tennis') !== false
            || strpos($combined, 'bơi') !== false
            || strpos($combined, 'chạy') !== false
            || strpos($combined, 'bóng bàn') !== false
            || strpos($combined, 'pickleball') !== false
            || strpos($combined, 'thi đấu') !== false) {
            return array(
                'key' => 'sport',
                'label' => 'THỂ THAO',
                'color' => '#16a34a',
                'bg_light' => '#f0fdf4',
                'text_color' => '#16a34a',
                'icon_type' => 'sport',
            );
        }

        // 3. Tham quan / Thắng cảnh / Tour
        if ($category === 'tour'
            || strpos($combined, 'tham quan') !== false
            || strpos($combined, 'thắng cảnh') !== false
            || strpos($combined, 'du lịch') !== false
            || strpos($combined, 'dã ngoại') !== false
            || strpos($combined, 'tour') !== false
            || strpos($combined, 'tràng an') !== false
            || strpos($combined, 'bái đính') !== false) {
            return array(
                'key' => 'tour',
                'label' => 'THAM QUAN',
                'color' => '#0d9488',
                'bg_light' => '#f0fdfa',
                'text_color' => '#0d9488',
                'icon_type' => 'mountain',
            );
        }

        // 4. Check-in / Thông tin / Đón tiếp
        if (strpos($combined, 'check-in') !== false
            || strpos($combined, 'checkin') !== false
            || strpos($combined, 'đón tiếp') !== false
            || strpos($combined, 'phát thẻ') !== false
            || strpos($combined, 'hướng dẫn') !== false
            || strpos($combined, 'thông tin') !== false
            || strpos($combined, 'nhận phòng') !== false
            || strpos($combined, 'trả phòng') !== false) {
            return array(
                'key' => 'info',
                'label' => 'THÔNG TIN',
                'color' => '#334155',
                'bg_light' => '#f1f5f9',
                'text_color' => '#475569',
                'icon_type' => 'info',
            );
        }

        // 5. Gala / Tiệc
        if ($type === self::TYPE_BANQUET
            || strpos($combined, 'gala') !== false
            || strpos($combined, 'tiệc') !== false
            || strpos($combined, 'liên hoan') !== false) {
            return array(
                'key' => 'banquet',
                'label' => 'GALA TIỆC',
                'color' => '#db2777',
                'bg_light' => '#fdf2f8',
                'text_color' => '#db2777',
                'icon_type' => 'celebration',
            );
        }

        // 6. Thi nghiệp vụ / Tay nghề / Cuộc thi
        if ($type === self::TYPE_COMPETITION
            || strpos($combined, 'thi nghiệp vụ') !== false
            || strpos($combined, 'nghiệp vụ') !== false
            || strpos($combined, 'tay nghề') !== false
            || strpos($combined, 'vòng thi') !== false
            || strpos($combined, 'miss') !== false) {
            return array(
                'key' => 'competition',
                'label' => 'THI NGHIỆP VỤ',
                'color' => '#7c3aed',
                'bg_light' => '#f5f3ff',
                'text_color' => '#7c3aed',
                'icon_type' => 'competition',
            );
        }

        // 7. Nghi thức
        if (strpos($combined, 'khai mạc') !== false
            || strpos($combined, 'bế mạc') !== false
            || strpos($combined, 'chào cờ') !== false
            || strpos($combined, 'nghi thức') !== false
            || strpos($combined, 'trao giải') !== false) {
            return array(
                'key' => 'ceremony',
                'label' => 'NGHI THỨC',
                'color' => '#2563eb',
                'bg_light' => '#eff6ff',
                'text_color' => '#2563eb',
                'icon_type' => 'ceremony',
            );
        }

        // Mặc định: Agenda / Chương trình chung
        return array(
            'key' => 'agenda',
            'label' => 'CHƯƠNG TRÌNH',
            'color' => '#2563eb',
            'bg_light' => '#eff6ff',
            'text_color' => '#2563eb',
            'icon_type' => 'agenda',
        );
    }

    /**
     * Render SVG icon tương ứng cho timeline node.
     *
     * @param string $iconType
     * @return string HTML/SVG
     */
    public static function renderIcon($iconType)
    {
        switch ($iconType) {
            case 'cutlery':
                return '<svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M11 9H9V2H7v7H5V2H3v7c0 2.12 1.66 3.84 3.75 3.97V22h2.5v-9.03C11.34 12.84 13 11.12 13 9V2h-2v7zm5-3v8h2.5v8H21V2c-2.76 0-5 2.24-5 4z"/></svg>';
            case 'info':
                return '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>';
            case 'sport':
                return '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.68 2.08c1.39.42 2.61 1.22 3.54 2.27l-2.02 2.39-1.52-.49v-4.17zm-3.36 0v4.17l-1.52.49-2.02-2.39c.93-1.05 2.15-1.85 3.54-2.27zm-5.07 4.25 2.19 2.59-.44 1.54-3.04.65c-.09-.59-.14-1.2-.14-1.82 0-1.09.22-2.13.62-3.08l.81.12zm1.5 8.1 1.15-1.15 1.58.33 1.1 3.86c-1.46-.24-2.78-.96-3.83-2.04v-1zm7.49 3.04 1.1-3.86 1.58-.33 1.15 1.15c-1.05 1.08-2.37 1.8-3.83 2.04zm4.51-4.94-3.04-.65-.44-1.54 2.19-2.59.81-.12c.4.95.62 1.99.62 3.08 0 .62-.05 1.23-.14 1.82zm-4.71-4.83.67 2.05-1.63 1.19-1.63-1.19.67-2.05h1.92z"/></svg>';
            case 'mountain':
                return '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="m14 6-4.25 5.67 3.08 4.11L11.5 17 6 10l-4 5.33V20h20v-8.67L14 6z"/></svg>';
            case 'celebration':
                return '<i class="bi bi-balloon-heart-fill"></i>';
            case 'competition':
                return '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3 1 9l11 6 9-4.91V17h2V9L12 3zm0 8.55L4.5 8.2 12 4.13l7.5 4.07L12 11.55zM6 13.09v3.83c0 2.26 2.69 4.08 6 4.08s6-1.82 6-4.08v-3.83l-6 3.27-6-3.27z"/></svg>';
            case 'ceremony':
                return '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M14.4 6 14 4H5v17h2v-7h5.6l.4 2h7V6h-5.6z"/></svg>';
            case 'agenda':
            default:
                return '<svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/></svg>';
        }
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
        // Chỉ gộp các nguồn gắn với cá nhân người đăng nhập (trận đấu, thi nghiệp
        // vụ, bữa ăn, tiệc). Agenda là chương trình chung toàn đại hội, không thuộc
        // lịch cá nhân nên hiển thị ở trang chương trình chung, không gộp vào đây.
        $items = array();
        $items = array_merge($items, self::getSportItems($attendeeId, $eventId));
        $items = array_merge($items, self::getCompetitionItems($attendeeId, $eventId));
        $items = array_merge($items, self::getMealItems($attendeeId, $eventId));
        $items = array_merge($items, self::getBanquetItems($attendeeId, $eventId));
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
     * Nguồn Thi nghiệp vụ: các vòng thi của cuộc thi người tham dự đã đăng ký (kèm SBD).
     *
     * @param string|int $attendeeId
     * @param string|int $eventId
     * @return array danh sách item chuẩn hoá (chưa gán status)
     */
    public static function getCompetitionItems($attendeeId, $eventId)
    {
        $result = ApiClient::get(ApiEndpoints::SCHEDULE_COMPETITION, array(
            'attendee_id' => $attendeeId,
            'event_id' => $eventId,
        ));
        return self::unwrapList($result);
    }

    /**
     * Nguồn Agenda chung: chương trình đại hội công khai (áp dụng cho mọi người).
     *
     * @param string|int $eventId
     * @return array danh sách item chuẩn hoá (chưa gán status)
     */
    public static function getAgendaItems($eventId)
    {
        $result = ApiClient::get(ApiEndpoints::SCHEDULE_AGENDA, array(
            'event_id' => $eventId,
        ));
        return self::unwrapList($result);
    }

    /**
     * Nguồn Bữa ăn: các bữa người tham dự được phân (kèm cờ đã báo cắt).
     *
     * @param string|int $attendeeId
     * @param string|int $eventId
     * @return array danh sách item chuẩn hoá (chưa gán status)
     */
    public static function getMealItems($attendeeId, $eventId)
    {
        $result = ApiClient::get(ApiEndpoints::SCHEDULE_MEAL, array(
            'attendee_id' => $attendeeId,
            'event_id' => $eventId,
        ));
        return self::unwrapList($result);
    }

    /**
     * Nguồn Tiệc: chỗ ngồi tiệc của người tham dự (bàn + ghế).
     *
     * @param string|int $attendeeId
     * @param string|int $eventId
     * @return array danh sách item chuẩn hoá (chưa gán status)
     */
    public static function getBanquetItems($attendeeId, $eventId)
    {
        $result = ApiClient::get(ApiEndpoints::SCHEDULE_BANQUET, array(
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
