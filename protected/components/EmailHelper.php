<?php

class EmailHelper
{
    public static function send($to, $subject, $view, $data = array(), $attachments = array())
    {
        return MyHelper::sendMail($to, $subject, $view, $data, $attachments);
    }

    public static function sendMissInvitation($contestant)
    {
        $email = $contestant->personal_email;
        if (empty($email)) {
            return false;
        }

        $token = isset($contestant->submission_token) ? $contestant->submission_token : '';
        if (empty($token)) {
            return false;
        }

        $submissionUrl = Yii::app()->createAbsoluteUrl('/frontend/miss/submit', array('token' => $token));

        $expiresAt = isset($contestant->submission_token_expires_at)
            ? date('d/m/Y H:i', $contestant->submission_token_expires_at)
            : date('d/m/Y H:i', strtotime('+7 days'));

        $data = array(
            'contestant' => $contestant,
            'submissionUrl' => $submissionUrl,
            'expiresAt' => $expiresAt,
        );

        return self::send(
            $email,
            '[Đại hội Mường Thanh 2026] Mời gửi hồ sơ dự thi Miss',
            'miss_invitation',
            $data
        );
    }

    public static function sendMissConfirmation($contestant)
    {
        $email = $contestant->personal_email;
        if (empty($email)) {
            return false;
        }

        $data = array(
            'contestant' => $contestant,
        );

        return self::send(
            $email,
            '[Đại hội 2026] Xác nhận đã nhận hồ sơ dự thi Miss',
            'miss_confirmation',
            $data
        );
    }

    /**
     * Tách và làm sạch danh sách email từ chuỗi hoặc mảng
     * @param mixed $raw Chuỗi email (phân cách bằng dấu phẩy, chấm phẩy, xuống dòng) hoặc mảng
     * @return array Danh sách email hợp lệ đã loại trùng
     */
    public static function parseEmailList($raw)
    {
        if (empty($raw)) {
            return array();
        }
        // Chuẩn hoá về mảng các phần tử. Mỗi phần tử có thể vẫn là chuỗi nhiều email
        // (phân cách bằng dấu phẩy, chấm phẩy, khoảng trắng, xuống dòng) nên phải tách tiếp.
        $items = is_array($raw) ? $raw : array($raw);
        $validEmails = array();
        foreach ($items as $item) {
            if (is_array($item)) {
                $parts = $item;
            } else {
                $parts = preg_split('/[\s,;]+/', (string) $item);
            }
            if (!is_array($parts)) {
                continue;
            }
            foreach ($parts as $part) {
                $email = trim((string) $part);
                if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $validEmails[] = strtolower($email);
                }
            }
        }
        return array_values(array_unique($validEmails));
    }

    /**
     * Lấy đường dẫn ảnh chân dung của người tham dự để hiển thị trong phiếu xác nhận.
     * Ưu tiên portrait_path (ảnh chân dung), sau đó photo_full_path, cuối cùng photo_path.
     *
     * @param array $attInfo Thông tin người tham dự lấy từ Attendees::getByRegistrationId
     * @return string Đường dẫn/URL ảnh, hoặc chuỗi rỗng nếu không có
     */
    private static function resolveAttendeePhoto($attInfo)
    {
        if (empty($attInfo) || !is_array($attInfo)) {
            return '';
        }
        $raw = '';
        foreach (array('portrait_path', 'photo_full_path', 'photo_path') as $field) {
            if (!empty($attInfo[$field])) {
                $raw = $attInfo[$field];
                break;
            }
        }
        if ($raw === '') {
            return '';
        }

        // Ảnh là URL tuyệt đối → dùng trực tiếp (dompdf đã bật isRemoteEnabled)
        if (preg_match('#^https?://#i', $raw)) {
            return $raw;
        }

        // Ảnh lưu cục bộ (đường dẫn tương đối webroot) → trả về đường dẫn hệ thống tuyệt đối
        // để dompdf đọc trực tiếp từ ổ đĩa, tránh phụ thuộc mạng.
        $rel = ltrim($raw, '/');
        $absolute = Yii::getPathOfAlias('webroot') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
        if (is_file($absolute)) {
            return $absolute;
        }

        return '';
    }

    /**
     * Gửi mail xác nhận phiếu đăng ký (Đợt 1 - Thể thao / Đợt 2 - Thi nghiệp vụ)
     * Tự động lấy danh sách email từ `mail_confirm` của đơn vị (Property), `submitted_by`, và customRecipient.
     *
     * @param int|string $registrationId ID phiếu đăng ký
     * @param mixed $customRecipient Email bổ sung từ giao diện (nếu có)
     * @return array Kết quả gửi mail array('success' => bool, 'message' => string, 'recipients' => array)
     */
    public static function sendRegistrationConfirmation($registrationId, $customRecipient = null)
    {
        if (!$registrationId) {
            return array('success' => false, 'error' => 'Thiếu ID phiếu đăng ký.');
        }

        $model = Registrations::fetchFromApi($registrationId);
        if (!$model) {
            return array('success' => false, 'error' => 'Không tìm thấy phiếu đăng ký.');
        }

        // 1. Tải thông tin Property và lấy cột mail_confirm từ API trả về
        $propertyMailConfirm = null;
        if ($model->property_id) {
            $property = Properties::fetchFromApi($model->property_id);
            if ($property) {
                if (empty($model->property_name)) {
                    $model->property_name = $property->name;
                }
                // Mã đơn vị (cột prefix) — dùng cho tên file PDF đính kèm
                $model->property_code = !empty($property->prefix) ? $property->prefix : $property->code;
                if (!empty($property->mail_confirm)) {
                    $propertyMailConfirm = $property->mail_confirm;
                }
            }
        }

        // Tải các thông tin hiển thị còn thiếu
        if (empty($model->event_name) && $model->event_id) {
            $event = Events::fetchFromApi($model->event_id);
            $model->event_name = $event ? $event->name : '';
        }
        if (empty($model->period_name) && $model->period_id) {
            $period = RegistrationPeriods::fetchFromApi($model->period_id);
            $model->period_name = $period ? $period->name : '';
        }

        // 2. Thu thập và làm sạch danh sách email nhận
        $rawRecipients = array();
        if (!empty($customRecipient)) {
            $rawRecipients[] = $customRecipient;
        }
        if (!empty($propertyMailConfirm)) {
            $rawRecipients[] = $propertyMailConfirm;
        }
        if (isset($model->mail_confirm) && !empty($model->mail_confirm)) {
            $rawRecipients[] = $model->mail_confirm;
        }
        // Tạm thời KHÔNG gửi cho submitted_by (người nộp phiếu)
        // if (!empty($model->submitted_by)) {
        //     $rawRecipients[] = $model->submitted_by;
        // }

        $recipients = self::parseEmailList($rawRecipients);

        // Nếu không có email nào hợp lệ, fallback về email mặc định cswm@muongthanh.vn
        if (empty($recipients)) {
            $recipients = array('cswm@muongthanh.vn');
        }

        // 3. Tải cấu hình đợt đăng ký — lấy content_id của period rồi map sang code qua bảng Contents
        $periodContentCodes = array();
        if ($model->period_id) {
            $contentIds = RegistrationPeriodContents::getContentIdsByPeriod($model->period_id);
            if (!empty($contentIds)) {
                $contentsList = Contents::getApiDataProvider(array(), 200)->getData();
                $contentCodeMap = array();
                foreach ($contentsList as $ct) {
                    $ctId = is_object($ct) ? $ct->id : (isset($ct['id']) ? $ct['id'] : null);
                    $ctCode = is_object($ct) ? $ct->code : (isset($ct['code']) ? $ct['code'] : null);
                    if ($ctId !== null) {
                        $contentCodeMap[$ctId] = $ctCode;
                    }
                }
                foreach ($contentIds as $cid) {
                    $code = isset($contentCodeMap[$cid]) ? $contentCodeMap[$cid] : '';
                    if ($code) {
                        if ($code === 'sport') $code = 'sports';
                        if ($code === 'competitions') $code = 'competition';
                        if ($code === 'talents') $code = 'talent';
                        if ($code === 'beauty_contests') $code = 'miss';
                        $periodContentCodes[] = $code;
                    }
                }
            }
        }

        // Cờ đợt để hiển thị (fallback theo tên period khi không lấy được content codes)
        $periodNameLower = mb_strtolower((string)$model->period_name);
        $noCodes = empty($periodContentCodes);
        $isDot1 = in_array('sports', $periodContentCodes) || ($noCodes && (strpos($periodNameLower, 'đợt 1') !== false || strpos($periodNameLower, 'dot 1') !== false));
        $isDot2 = in_array('competition', $periodContentCodes) || ($noCodes && (strpos($periodNameLower, 'đợt 2') !== false || strpos($periodNameLower, 'dot 2') !== false));

        // Điều kiện hiển thị từng nội dung theo cấu hình period (fallback khi thiếu codes)
        $showSports = in_array('sports', $periodContentCodes) || ($noCodes && $isDot1);
        $showTalent = in_array('talent', $periodContentCodes) || ($noCodes && $isDot1);
        $showMiss = in_array('miss', $periodContentCodes) || ($noCodes && $isDot1);
        $showCompetition = in_array('competition', $periodContentCodes) || ($noCodes && $isDot2);

        // 4. Tải danh sách người tham dự
        $attendees = Attendees::getByRegistrationId($registrationId);
        $attendeesMap = array();
        foreach ($attendees as $att) {
            $attId = isset($att['id']) ? $att['id'] : null;
            if ($attId) {
                $attendeesMap[$attId] = $att;
            }
        }

        // 5. Tải danh sách Thi nghiệp vụ (Đợt 2)
        $competitionRegistrations = array();
        if ($showCompetition) {
            $compRegsData = CompetitionRegistrations::getApiDataProvider(array('registration_id' => $registrationId), 200)->getData();
            foreach ($compRegsData as $reg) {
                $compId = isset($reg->competition_id) ? $reg->competition_id : (isset($reg['competition_id']) ? $reg['competition_id'] : null);
                if (!$compId) continue;

                if (!isset($competitionRegistrations[$compId])) {
                    $compName = isset($reg->competition_name) ? $reg->competition_name : (isset($reg['competition_name']) ? $reg['competition_name'] : '');
                    if (empty($compName)) {
                        $comp = Competitions::fetchFromApi($compId);
                        $compName = $comp ? $comp->name : '';
                    }
                    $competitionRegistrations[$compId] = array(
                        'competition_id' => $compId,
                        'competition_name' => $compName,
                        'attendees' => array(),
                    );
                }

                $attendeeId = isset($reg->attendee_id) ? $reg->attendee_id : (isset($reg['attendee_id']) ? $reg['attendee_id'] : null);
                $attendeeInfo = isset($attendeesMap[$attendeeId]) ? $attendeesMap[$attendeeId] : array();

                $competitionRegistrations[$compId]['attendees'][] = array(
                    'id' => isset($reg->id) ? $reg->id : (isset($reg['id']) ? $reg['id'] : null),
                    'attendee_id' => $attendeeId,
                    'attendee_name' => isset($attendeeInfo['full_name']) ? $attendeeInfo['full_name'] : '',
                    'staff_code' => isset($attendeeInfo['staff_code']) ? $attendeeInfo['staff_code'] : '',
                    'gender' => isset($attendeeInfo['gender']) ? $attendeeInfo['gender'] : null,
                    'position_name' => isset($attendeeInfo['position_name']) ? $attendeeInfo['position_name'] : '',
                    'division_name' => isset($attendeeInfo['division_name']) ? $attendeeInfo['division_name'] : '',
                    'start_working_date' => isset($attendeeInfo['end_starting_date']) ? $attendeeInfo['end_starting_date'] : '',
                    'photo_path' => self::resolveAttendeePhoto($attendeeInfo),
                );
            }
        }

        // 6. Tải danh sách Đội thi thể thao & VĐV (Đợt 1)
        $sportTeamsData = array();
        $activeSportCategories = array(); // Danh sách tên môn thể thao active của sự kiện (theo thứ tự) — dùng cho MẪU SỐ 1
        if ($model->event_id && $model->property_id && $showSports) {
            // Lấy danh sách môn thể thao được cấu hình cho sự kiện
            $eventSportsList = EventSports::getByEventId($model->event_id);
            $activeEventSportIdsMap = array();
            foreach ($eventSportsList as $es) {
                $sId = is_object($es) ? (isset($es->sport_id) ? $es->sport_id : null) : (isset($es['sport_id']) ? $es['sport_id'] : null);
                if ($sId) {
                    $activeEventSportIdsMap[$sId] = true;
                }
            }

            // Lấy tất cả môn thể thao đang active
            $allActiveSports = Sports::getApiDataProvider(array('is_active' => 1), 500)->getData();
            $sportOrderMap = array();   // sport_id => vị trí sắp xếp
            $sportNameMap = array();    // sport_id => tên môn thể thao (đã lọc theo event)
            $allSportNameMap = array(); // sport_id => tên (toàn bộ, để tra tên bộ môn cha)
            $sportParentMap = array();  // sport_id => parent_id (bộ môn cha)
            $sportIndex = 0;
            foreach ($allActiveSports as $sp) {
                $spId = is_object($sp) ? $sp->id : $sp['id'];
                $spName = is_object($sp) ? $sp->name : $sp['name'];
                $spParent = is_object($sp)
                    ? (isset($sp->parent_id) ? $sp->parent_id : null)
                    : (isset($sp['parent_id']) ? $sp['parent_id'] : null);

                $allSportNameMap[$spId] = $spName;
                $sportParentMap[$spId] = $spParent;

                // Chỉ giữ môn thể thao nằm trong cấu hình event_sports của sự kiện (nếu có cấu hình)
                if (!empty($activeEventSportIdsMap) && !isset($activeEventSportIdsMap[$spId])) {
                    continue;
                }

                $sportOrderMap[$spId] = $sportIndex++;
                $sportNameMap[$spId] = $spName;
                $activeSportCategories[] = $spName;
            }

            $apiResult = ApiClient::get(ApiEndpoints::SPORT_TEAM_LIST_BY_PROPERTY, array(
                'property_id' => $model->property_id,
                'event_id' => $model->event_id,
            ));
            $teamsData = array();
            if ($apiResult['success'] && isset($apiResult['data']['data'])) {
                $teamsData = $apiResult['data']['data'];
            } elseif ($apiResult['success'] && isset($apiResult['data']) && is_array($apiResult['data'])) {
                $teamsData = $apiResult['data'];
            }

            foreach ($teamsData as $team) {
                $isObject = is_object($team);
                $teamId = $isObject ? (isset($team->id) ? $team->id : null) : (isset($team['id']) ? $team['id'] : null);
                if (!$teamId) continue;

                $sportId = $isObject ? (isset($team->sport_id) ? $team->sport_id : null) : (isset($team['sport_id']) ? $team['sport_id'] : null);

                // Loại bỏ nếu môn thể thao này không thuộc danh sách môn đang active của sự kiện
                if (!empty($sportOrderMap) && ($sportId === null || !isset($sportOrderMap[$sportId]))) {
                    continue;
                }

                $sportName = $isObject ? (isset($team->sport_name) ? $team->sport_name : '') : (isset($team['sport_name']) ? $team['sport_name'] : '');
                if (empty($sportName) && $sportId && isset($sportNameMap[$sportId])) {
                    $sportName = $sportNameMap[$sportId];
                } elseif (empty($sportName) && $sportId) {
                    $sport = Sports::fetchFromApi($sportId);
                    $sportName = $sport ? $sport->name : '';
                }

                $teamName = $isObject ? (isset($team->team_name) ? $team->team_name : (isset($team->name) ? $team->name : '')) : (isset($team['team_name']) ? $team['team_name'] : (isset($team['name']) ? $team['name'] : ''));

                $membersData = array();
                if (isset($team->members) && is_array($team->members)) {
                    $membersData = $team->members;
                } elseif (isset($team['members']) && is_array($team['members'])) {
                    $membersData = $team['members'];
                } else {
                    $membersData = SportTeamMembers::getApiDataProvider(array('sport_team_id' => $teamId), 100)->getData();
                }

                $enrichedMembers = array();
                $allianceProperties = array();
                foreach ($membersData as $member) {
                    $memberIsObj = is_object($member);
                    $attId = $memberIsObj ? (isset($member->attendee_id) ? $member->attendee_id : null) : (isset($member['attendee_id']) ? $member['attendee_id'] : null);

                    // Chỉ lấy VĐV thuộc đơn vị hiện tại (có trong danh sách người tham dự của phiếu đăng ký này);
                    // bỏ qua VĐV liên quân đến từ đơn vị khác.
                    if ($attId === null || !isset($attendeesMap[$attId])) {
                        continue;
                    }
                    $attInfo = $attendeesMap[$attId];

                    $attName = $memberIsObj ? (isset($member->attendee_name) ? $member->attendee_name : '') : (isset($member['attendee_name']) ? $member['attendee_name'] : '');
                    if (empty($attName) && !empty($attInfo['full_name'])) {
                        $attName = $attInfo['full_name'];
                    }
                    $gender = $memberIsObj ? (isset($member->gender) ? $member->gender : null) : (isset($member['gender']) ? $member['gender'] : null);
                    if ($gender === null && isset($attInfo['gender'])) {
                        $gender = $attInfo['gender'];
                    }
                    $propName = $memberIsObj ? (isset($member->property_name) ? $member->property_name : '') : (isset($member['property_name']) ? $member['property_name'] : '');
                    if (empty($propName) && !empty($attInfo['property_name'])) {
                        $propName = $attInfo['property_name'];
                    }
                    if (empty($propName)) {
                        $propName = $model->property_name;
                    }

                    $staffCode = $memberIsObj ? (isset($member->staff_code) ? $member->staff_code : '') : (isset($member['staff_code']) ? $member['staff_code'] : '');
                    if (empty($staffCode) && !empty($attInfo['staff_code'])) {
                        $staffCode = $attInfo['staff_code'];
                    }

                    $enrichedMembers[] = array(
                        'attendee_name' => $attName,
                        'staff_code' => $staffCode,
                        'gender' => $gender,
                        'property_name' => $propName,
                        'position_name' => isset($attInfo['position_name']) ? $attInfo['position_name'] : '',
                        'division_name' => isset($attInfo['division_name']) ? $attInfo['division_name'] : '',
                        'start_working_date' => isset($attInfo['end_starting_date']) ? $attInfo['end_starting_date'] : '',
                        'photo_path' => self::resolveAttendeePhoto($attInfo),
                    );

                    if (!empty($propName) && $propName !== $model->property_name && !in_array($propName, $allianceProperties)) {
                        $allianceProperties[] = $propName;
                    }
                }

                // Bỏ qua đội không còn VĐV nào thuộc đơn vị hiện tại (sau khi lọc liên quân).
                if (empty($enrichedMembers)) {
                    continue;
                }

                // Xác định bộ môn cha (group). Nếu môn không có cha thì chính nó là bộ môn.
                $parentId = ($sportId && isset($sportParentMap[$sportId])) ? $sportParentMap[$sportId] : null;
                if ($parentId && isset($allSportNameMap[$parentId])) {
                    $groupId = $parentId;
                    $groupName = $allSportNameMap[$parentId];
                } else {
                    $groupId = $sportId;
                    $groupName = $sportName;
                }

                $sportTeamsData[] = array(
                    'sport_id' => $sportId,
                    'sport_name' => $sportName,
                    'sport_order' => ($sportId && isset($sportOrderMap[$sportId])) ? $sportOrderMap[$sportId] : 999,
                    'group_id' => $groupId,
                    'group_name' => $groupName,
                    'team_name' => $teamName,
                    'members' => $enrichedMembers,
                    'alliance_properties' => $allianceProperties,
                    'is_alliance' => !empty($allianceProperties),
                );
            }

            // Thứ tự của mỗi bộ môn = vị trí nhỏ nhất trong các nội dung thuộc bộ môn đó
            $groupMinOrder = array();
            foreach ($sportTeamsData as $t) {
                $gid = $t['group_id'];
                if (!isset($groupMinOrder[$gid]) || $t['sport_order'] < $groupMinOrder[$gid]) {
                    $groupMinOrder[$gid] = $t['sport_order'];
                }
            }

            // Sắp xếp: gom theo bộ môn (group) → nội dung (sport) → tên đội, giữ các đội cùng bộ môn liền nhau
            usort($sportTeamsData, function ($a, $b) use ($groupMinOrder) {
                $goA = isset($groupMinOrder[$a['group_id']]) ? $groupMinOrder[$a['group_id']] : 999;
                $goB = isset($groupMinOrder[$b['group_id']]) ? $groupMinOrder[$b['group_id']] : 999;
                if ($goA != $goB) {
                    return $goA - $goB;
                }
                if ((string)$a['group_id'] !== (string)$b['group_id']) {
                    return strcmp((string)$a['group_id'], (string)$b['group_id']);
                }
                $orderA = isset($a['sport_order']) ? $a['sport_order'] : 999;
                $orderB = isset($b['sport_order']) ? $b['sport_order'] : 999;
                if ($orderA != $orderB) {
                    return $orderA - $orderB;
                }
                $cmpSport = strcmp($a['sport_name'], $b['sport_name']);
                if ($cmpSport != 0) {
                    return $cmpSport;
                }
                return strcmp($a['team_name'], $b['team_name']);
            });
        }

        // 6b. Tải danh sách tiết mục Văn nghệ — lọc theo registration_id (Đợt 3 mới có chi tiết người tham dự)
        $talentEntriesData = array();
        if ($showTalent) {
            $talentList = TalentEntries::getApiDataProvider(array('registration_id' => $registrationId), 1000)->getData();
            if (!empty($talentList)) {
                // API không lọc theo entry_id nên tải toàn bộ rồi lọc client-side
                $allTalentMembers = TalentEntryMembers::getApiDataProvider(array(), 5000)->getData();
                foreach ($talentList as $entry) {
                    $entryId = $entry->id;
                    $members = array();
                    foreach ($allTalentMembers as $tm) {
                        if ($tm->entry_id != $entryId) {
                            continue;
                        }
                        $aid = $tm->attendee_id;
                        $info = isset($attendeesMap[$aid]) ? $attendeesMap[$aid] : array();
                        $members[] = array(
                            'attendee_name' => !empty($info['full_name']) ? $info['full_name'] : ('#' . $aid),
                            'staff_code' => isset($info['staff_code']) ? $info['staff_code'] : '',
                            'gender' => isset($info['gender']) ? $info['gender'] : null,
                            'position_name' => isset($info['position_name']) ? $info['position_name'] : '',
                            'division_name' => isset($info['division_name']) ? $info['division_name'] : '',
                            'start_working_date' => isset($info['end_starting_date']) ? $info['end_starting_date'] : '',
                            'photo_path' => self::resolveAttendeePhoto($info),
                        );
                    }
                    $talentEntriesData[] = array(
                        'category_name' => $entry->category_name,
                        'title' => $entry->title,
                        'members' => $members,
                    );
                }
            }
        }

        // 6c. Tải danh sách thí sinh Miss — lọc theo registration_id của phiếu đang xuất
        // (filter server-side của API không hoạt động nên tải toàn bộ rồi lọc client-side)
        $beautyContestantsData = array();
        if ($showMiss) {
            $allContestants = BeautyContestants::getApiDataProvider(array(), 5000)->getData();
            foreach ($allContestants as $c) {
                if ($c->registration_id != $registrationId) {
                    continue;
                }
                $aid = $c->attendee_id;
                $info = isset($attendeesMap[$aid]) ? $attendeesMap[$aid] : array();
                $beautyContestantsData[] = array(
                    'attendee_name' => !empty($c->attendee_name) ? $c->attendee_name : (isset($info['full_name']) ? $info['full_name'] : ''),
                    'staff_code' => isset($info['staff_code']) ? $info['staff_code'] : '',
                    'candidate_number' => $c->candidate_number,
                    'contest_name' => $c->contest_name,
                    'position_name' => isset($info['position_name']) ? $info['position_name'] : '',
                    'division_name' => isset($info['division_name']) ? $info['division_name'] : '',
                    'start_working_date' => isset($info['end_starting_date']) ? $info['end_starting_date'] : '',
                    'photo_path' => self::resolveAttendeePhoto($info),
                );
            }
        }

        // 7. Tổng hợp Hạng mục đã đăng ký & Nội dung tóm tắt cho Bảng tổng quan
        $categoriesMap = array();
        if ($isDot1 || !empty($sportTeamsData)) {
            $categoriesMap['sports'] = 'Thể thao';
        }
        if (in_array('talent', $periodContentCodes) || !empty($talentEntriesData)) {
            $categoriesMap['talent'] = 'Văn nghệ';
        }
        if (in_array('miss', $periodContentCodes) || !empty($beautyContestantsData)) {
            $categoriesMap['miss'] = 'Miss Mường Thanh';
        }
        if ($isDot2 || !empty($competitionRegistrations)) {
            $categoriesMap['competition'] = 'Thi nghiệp vụ';
        }
        $registeredCategories = implode(', ', array_values($categoriesMap));
        if (empty($registeredCategories)) {
            $registeredCategories = 'Đăng ký tham dự';
        }

        // Tổng hợp danh sách các dòng nội dung
        $contentSummaryLines = array();

        // Thống kê môn thể thao (Đợt 1)
        if (!empty($sportTeamsData)) {
            $sportGroups = array();
            foreach ($sportTeamsData as $team) {
                $sName = $team['sport_name'];
                if (!isset($sportGroups[$sName])) {
                    $sportGroups[$sName] = array(
                        'team_count' => 0,
                        'athlete_count' => 0,
                    );
                }
                $sportGroups[$sName]['team_count'] += 1;
                $sportGroups[$sName]['athlete_count'] += count($team['members']);
            }

            foreach ($sportGroups as $sName => $stats) {
                $line = $sName;
                $parts = array();
                if ($stats['team_count'] > 0) {
                    $parts[] = $stats['team_count'] . ' đội';
                }
                if ($stats['athlete_count'] > 0) {
                    $parts[] = $stats['athlete_count'] . ' VĐV';
                }
                if (!empty($parts)) {
                    $line .= ' - ' . implode(' - ', $parts);
                }
                $contentSummaryLines[] = $line;
            }
        }

        // Thống kê Văn nghệ (Đợt 1)
        if (!empty($talentEntriesData)) {
            foreach ($talentEntriesData as $te) {
                $line = 'Văn nghệ';
                if (!empty($te['category_name'])) {
                    $line .= ' (' . $te['category_name'] . ')';
                }
                if (!empty($te['title'])) {
                    $line .= ': ' . $te['title'];
                }
                $mCount = count($te['members']);
                if ($mCount > 0) {
                    $line .= ' - ' . $mCount . ' người';
                }
                $contentSummaryLines[] = $line;
            }
        }

        // Thống kê Miss (Đợt 1)
        if (!empty($beautyContestantsData)) {
            $contentSummaryLines[] = 'Miss Mường Thanh - ' . count($beautyContestantsData) . ' thí sinh';
        }

        // Thống kê thi nghiệp vụ (Đợt 2)
        if (!empty($competitionRegistrations)) {
            foreach ($competitionRegistrations as $compData) {
                $cName = $compData['competition_name'];
                $cCount = count($compData['attendees']);
                $line = $cName;
                if ($cCount > 0) {
                    $line .= ' - ' . $cCount . ' thí sinh';
                }
                $contentSummaryLines[] = $line;
            }
        }

        // Data truyền sang email view & PDF view
        $data = array(
            'model' => $model,
            'periodContentCodes' => $periodContentCodes,
            'isDot1' => $isDot1,
            'isDot2' => $isDot2,
            'registeredCategories' => $registeredCategories,
            'contentSummaryLines' => $contentSummaryLines,
            'sportTeams' => $sportTeamsData,
            'sportCategories' => $activeSportCategories,
            'talentEntries' => $talentEntriesData,
            'beautyContestants' => $beautyContestantsData,
            'competitionRegistrations' => $competitionRegistrations,
            'attendeesCount' => count($attendees),
        );

        // 8. Tạo file PDF đính kèm
        $attachments = array();
        $pdfError = null;
        try {
            $pdfPath = PdfHelper::generateRegistrationPdf($registrationId, $data);
            if (!empty($pdfPath) && file_exists($pdfPath)) {
                $attachments[] = $pdfPath;
            } else {
                $pdfError = 'File PDF không tồn tại sau khi tạo.';
            }
        } catch (Exception $e) {
            $pdfError = $e->getMessage();
            Yii::log('Generate PDF error (registration ' . $registrationId . '): ' . $e->getMessage(), CLogger::LEVEL_ERROR, 'application.components.EmailHelper');
        }

        $subject = '[Đại hội Mường Thanh 2026] Xác nhận thông tin đăng ký - ' . $model->property_name;

        try {
            $sent = self::send($recipients, $subject, 'registration_confirmation', $data, $attachments);
            if ($sent) {
                $hasPdf = !empty($attachments);
                $message = $hasPdf
                    ? 'Đã gửi email xác nhận kèm file PDF thành công tới: ' . implode(', ', $recipients)
                    : 'Đã gửi email xác nhận (KHÔNG có PDF đính kèm) tới: ' . implode(', ', $recipients)
                        . ($pdfError ? '. Lý do lỗi PDF: ' . $pdfError : '');
                return array(
                    'success' => true,
                    'has_pdf' => $hasPdf,
                    'message' => $message,
                    'recipients' => $recipients,
                );
            } else {
                return array('success' => false, 'error' => 'Không thể gửi email. Vui lòng kiểm tra cấu hình SMTP.');
            }
        } catch (Exception $e) {
            Yii::log('sendRegistrationConfirmation error: ' . $e->getMessage(), 'error', 'application.components.EmailHelper');
            return array('success' => false, 'error' => 'Lỗi gửi mail: ' . $e->getMessage());
        }
    }
}
