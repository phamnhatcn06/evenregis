<?php

class ReportByHotelController extends AdminController
{
    /**
     * Báo cáo tổng hợp theo khách sạn (property)
     */
    public function actionAdmin()
    {
        $user = AuthHandler::getUser();
        if (!$user) {
            throw new CHttpException(403, 'Bạn cần đăng nhập để xem báo cáo.');
        }

        PermissionHelper::requirePermission('reportByHotel', 'read');

        $userPropertyCode = isset($user['property_code']) ? $user['property_code'] : '';
        $isHO = ($userPropertyCode === '9999' || $userPropertyCode === 9999);
        $userPropertyId = isset($user['property_id']) ? $user['property_id'] : null;

        // Fetch events
        $eventsList = array();
        $eventsRes = Events::getApiDataProvider(array(), 100)->getData();
        foreach ($eventsRes as $event) {
            $eId = isset($event->id) ? $event->id : (isset($event['id']) ? $event['id'] : null);
            if ($eId) {
                $eventsList[$eId] = $event;
            }
        }

        // Determine selected event
        $selectedEventId = Yii::app()->request->getParam('event_id');
        if (empty($selectedEventId) && !empty($eventsList)) {
            foreach ($eventsList as $event) {
                $status = isset($event->status) ? $event->status : 0;
                if ($status == 1) {
                    $selectedEventId = isset($event->id) ? $event->id : $event['id'];
                    break;
                }
            }
            if (empty($selectedEventId)) {
                $firstEvent = reset($eventsList);
                $selectedEventId = isset($firstEvent->id) ? $firstEvent->id : $firstEvent['id'];
            }
        }

        $selectedEventName = '';
        if ($selectedEventId && isset($eventsList[$selectedEventId])) {
            $eventObj = $eventsList[$selectedEventId];
            $selectedEventName = isset($eventObj->name) ? $eventObj->name : (isset($eventObj['name']) ? $eventObj['name'] : '');
        }

        // Fetch properties
        $properties = array();
        if ($isHO) {
            $properties = Properties::getApiDataProvider(array('is_active' => 1), 1000)->getData();
        } else if ($userPropertyId) {
            $prop = Properties::fetchFromApi($userPropertyId);
            if ($prop) $properties = array($prop);
        }

        // Build property maps
        $propertyMap = array();
        foreach ($properties as $prop) {
            $propId = isset($prop->id) ? $prop->id : (isset($prop['id']) ? $prop['id'] : null);
            if ($propId) {
                $propertyMap[$propId] = array(
                    'code' => isset($prop->code) ? $prop->code : '',
                    'name' => isset($prop->name) ? $prop->name : '',
                );
            }
        }

        // Fetch active registrations
        $regParams = array('event_id' => $selectedEventId, 'per_page' => 1000);
        if (!$isHO && $userPropertyId) {
            $regParams['property_id'] = $userPropertyId;
        }
        $registrationsRes = Registrations::getApiDataProvider($regParams, 1000)->getData();
        $activeRegistrationIds = array();
        $propertyRegistrationMap = array();
        foreach ($registrationsRes as $reg) {
            $deletedAt = isset($reg->deleted_at) ? $reg->deleted_at : null;
            if ($deletedAt) continue;
            $status = isset($reg->status) ? (int)$reg->status : 0;
            if ($status !== Registrations::STATUS_DRAFT) {
                $regId = isset($reg->id) ? $reg->id : null;
                $regPropId = isset($reg->property_id) ? $reg->property_id : null;
                if ($regId) {
                    $activeRegistrationIds[$regId] = true;
                    if ($regPropId) {
                        $propertyRegistrationMap[$regPropId] = $reg;
                    }
                }
            }
        }

        // Fetch attendees
        $attParams = array('event_id' => $selectedEventId, 'per_page' => 5000);
        if (!$isHO && $userPropertyId) {
            $attParams['property_id'] = $userPropertyId;
        }
        $rawAttendees = Attendees::getApiDataProvider($attParams, 5000)->getData();

        $attendeesRes = array();
        $attendeeMap = array();
        $attendeePropertyMap = array();
        foreach ($rawAttendees as $att) {
            $attDeletedAt = isset($att->deleted_at) ? $att->deleted_at : null;
            if ($attDeletedAt) continue;
            $regId = isset($att->registration_id) ? $att->registration_id : null;
            if ($regId && isset($activeRegistrationIds[$regId])) {
                $attendeesRes[] = $att;
                $attId = isset($att->id) ? $att->id : null;
                if ($attId) {
                    $attendeeMap[$attId] = $att;
                    $attPropId = isset($att->property_id) ? $att->property_id : null;
                    if ($attPropId) {
                        $attendeePropertyMap[$attId] = $attPropId;
                    }
                }
            }
        }

        // Initialize report data per property
        $reportData = array();
        foreach ($propertyMap as $propId => $propInfo) {
            $reportData[$propId] = array(
                'code' => $propInfo['code'],
                'name' => $propInfo['name'],
                'sport_athletes' => array(),
                'competition_contestants' => array(),
                'beauty_contestants' => array(),
                'talent_entries' => array(),
            );
        }

        // 1. Sport Athletes
        $teamParams = array('event_id' => $selectedEventId, 'per_page' => 1000);
        if (!$isHO && $userPropertyId) {
            $teamParams['property_id'] = $userPropertyId;
        }
        $sportTeams = SportTeams::getApiDataProvider($teamParams, 1000)->getData();

        $sportNameMap = array();
        $sportsList = Sports::getApiDataProvider(array('is_active' => 1), 500)->getData();
        foreach ($sportsList as $sp) {
            $spId = isset($sp->id) ? $sp->id : null;
            $spName = isset($sp->name) ? $sp->name : '';
            if ($spId) $sportNameMap[$spId] = $spName;
        }

        $membersRes = ApiClient::get(ApiEndpoints::SPORT_TEAM_MEMBER_LIST, array(
            'event_id' => $selectedEventId,
            'per_page' => 5000,
        ));
        $teamMembers = array();
        if ($membersRes['success']) {
            $teamMembers = isset($membersRes['data']['data']) ? $membersRes['data']['data'] : $membersRes['data'];
            if (!is_array($teamMembers)) $teamMembers = array();
        }

        $athleteByProperty = array();
        foreach ($teamMembers as $m) {
            $attendeeId = isset($m['attendee_id']) ? $m['attendee_id'] : null;
            if (!$attendeeId) continue;
            $propId = isset($attendeePropertyMap[$attendeeId]) ? $attendeePropertyMap[$attendeeId] : null;
            if (!$propId || !isset($reportData[$propId])) continue;

            $sportTeamId = isset($m['sport_team_id']) ? $m['sport_team_id'] : null;
            $sportName = '';
            foreach ($sportTeams as $team) {
                $tId = isset($team->id) ? $team->id : null;
                if ($tId == $sportTeamId) {
                    $spId = isset($team->sport_id) ? $team->sport_id : null;
                    $sportName = isset($sportNameMap[$spId]) ? $sportNameMap[$spId] : '';
                    break;
                }
            }

            $att = isset($attendeeMap[$attendeeId]) ? $attendeeMap[$attendeeId] : null;
            $fullName = $att ? (isset($att->full_name) ? $att->full_name : '') : '';
            $position = $att ? (isset($att->position) ? $att->position : '') : '';

            $key = $attendeeId . '_' . $sportTeamId;
            if (!isset($athleteByProperty[$propId][$key])) {
                $athleteByProperty[$propId][$key] = array(
                    'attendee_id' => $attendeeId,
                    'full_name' => $fullName,
                    'position' => $position,
                    'sport_name' => $sportName,
                );
            }
        }

        foreach ($athleteByProperty as $propId => $athletes) {
            $reportData[$propId]['sport_athletes'] = array_values($athletes);
        }

        // 2. Competition Contestants
        $compRegParams = array('event_id' => $selectedEventId, 'per_page' => 2000);
        if (!$isHO && $userPropertyId) {
            $compRegParams['property_id'] = $userPropertyId;
        }
        $competitionRegs = CompetitionRegistrations::getApiDataProvider($compRegParams, 2000)->getData();

        foreach ($competitionRegs as $cr) {
            $crDeletedAt = isset($cr->deleted_at) ? $cr->deleted_at : null;
            if ($crDeletedAt) continue;

            $attendeeId = isset($cr->attendee_id) ? $cr->attendee_id : null;
            if (!$attendeeId) continue;

            $propId = isset($attendeePropertyMap[$attendeeId]) ? $attendeePropertyMap[$attendeeId] : null;
            if (!$propId || !isset($reportData[$propId])) continue;

            $att = isset($attendeeMap[$attendeeId]) ? $attendeeMap[$attendeeId] : null;
            $fullName = $att ? (isset($att->full_name) ? $att->full_name : '') : '';
            $position = $att ? (isset($att->position) ? $att->position : '') : '';
            $compName = isset($cr->competition_name) ? $cr->competition_name : '';

            $reportData[$propId]['competition_contestants'][] = array(
                'attendee_id' => $attendeeId,
                'full_name' => $fullName,
                'position' => $position,
                'competition_name' => $compName,
            );
        }

        // 3. Beauty Contestants
        $contests = BeautyContests::getApiDataProvider(array('event_id' => $selectedEventId), 100)->getData();
        foreach ($contests as $contest) {
            $contestId = isset($contest->id) ? $contest->id : null;
            $contestName = isset($contest->name) ? $contest->name : '';
            if (!$contestId) continue;

            $contestants = BeautyContestants::getApiDataProvider(array('contest_id' => $contestId), 1000)->getData();
            foreach ($contestants as $c) {
                $cDeletedAt = isset($c->deleted_at) ? $c->deleted_at : null;
                if ($cDeletedAt) continue;

                $attendeeId = isset($c->attendee_id) ? $c->attendee_id : null;
                if (!$attendeeId || !isset($attendeeMap[$attendeeId])) continue;

                $propId = isset($attendeePropertyMap[$attendeeId]) ? $attendeePropertyMap[$attendeeId] : null;
                if (!$propId || !isset($reportData[$propId])) continue;

                $att = $attendeeMap[$attendeeId];
                $fullName = isset($att->full_name) ? $att->full_name : '';
                $position = isset($att->position) ? $att->position : '';

                $reportData[$propId]['beauty_contestants'][] = array(
                    'attendee_id' => $attendeeId,
                    'full_name' => $fullName,
                    'position' => $position,
                    'contest_name' => $contestName,
                );
            }
        }

        // 4. Talent Entries
        $shows = TalentShows::getApiDataProvider(array('event_id' => $selectedEventId), 100)->getData();
        $showIds = array();
        $showNames = array();
        foreach ($shows as $show) {
            $sId = isset($show->id) ? $show->id : null;
            $sName = isset($show->name) ? $show->name : '';
            if ($sId) {
                $showIds[] = $sId;
                $showNames[$sId] = $sName;
            }
        }

        if (!empty($showIds)) {
            $entryParams = array('event_id' => $selectedEventId, 'per_page' => 1000);
            if (!$isHO && $userPropertyId) {
                $entryParams['property_id'] = $userPropertyId;
            }
            $allEntries = TalentEntries::getApiDataProvider($entryParams, 1000)->getData();

            foreach ($allEntries as $entry) {
                $entryDeletedAt = isset($entry->deleted_at) ? $entry->deleted_at : null;
                if ($entryDeletedAt) continue;

                $regId = isset($entry->registration_id) ? $entry->registration_id : null;
                if (!$regId || !isset($activeRegistrationIds[$regId])) continue;

                $eShowId = isset($entry->show_id) ? $entry->show_id : null;
                if (!$eShowId || !in_array($eShowId, $showIds)) continue;

                $propId = isset($entry->property_id) ? $entry->property_id : null;
                if (!$propId || !isset($reportData[$propId])) continue;

                $title = isset($entry->title) ? $entry->title : '';
                $categoryName = isset($entry->category_name) ? $entry->category_name : '';
                $participantCount = isset($entry->participant_count) ? (int)$entry->participant_count : 0;
                $showName = isset($showNames[$eShowId]) ? $showNames[$eShowId] : '';

                $reportData[$propId]['talent_entries'][] = array(
                    'title' => $title,
                    'category_name' => $categoryName,
                    'show_name' => $showName,
                    'participant_count' => $participantCount,
                );
            }
        }

        // Sort by property code
        uasort($reportData, function ($a, $b) {
            return strnatcasecmp($a['code'], $b['code']);
        });

        // Filter out properties with no data
        $reportDataFiltered = array();
        foreach ($reportData as $propId => $data) {
            if (!empty($data['sport_athletes']) || !empty($data['competition_contestants']) ||
                !empty($data['beauty_contestants']) || !empty($data['talent_entries'])) {
                $reportDataFiltered[$propId] = $data;
            }
        }

        $this->title = 'Báo cáo theo Khách sạn';
        $this->breadcrumbs = array(
            'Báo cáo' => array('/admin/reports/admin'),
            'Theo khách sạn'
        );

        $this->render('admin', array(
            'isHO' => $isHO,
            'user' => $user,
            'eventsList' => $eventsList,
            'selectedEventId' => $selectedEventId,
            'selectedEventName' => $selectedEventName,
            'reportData' => $reportDataFiltered,
            'propertyMap' => $propertyMap,
        ));
    }

    /**
     * Xuất Excel báo cáo theo khách sạn
     */
    public function actionExport($event_id = null, $property_id = null, $type = null)
    {
        $user = AuthHandler::getUser();
        if (!$user) {
            throw new CHttpException(403, 'Bạn cần đăng nhập để xuất báo cáo.');
        }

        PermissionHelper::requirePermission('reportByHotel', 'read');

        $userPropertyCode = isset($user['property_code']) ? $user['property_code'] : '';
        $isHO = ($userPropertyCode === '9999' || $userPropertyCode === 9999);
        $userPropertyId = isset($user['property_id']) ? $user['property_id'] : null;

        $selectedEventId = $event_id;
        $selectedPropertyId = $property_id;
        $exportType = $type;

        // Event info
        $eventName = '';
        if ($selectedEventId) {
            $event = Events::fetchFromApi($selectedEventId);
            $eventName = $event ? $event->name : '';
        }

        // Property info
        $propertyName = '';
        $propertyCode = '';
        if ($selectedPropertyId) {
            $prop = Properties::fetchFromApi($selectedPropertyId);
            if ($prop) {
                $propertyName = $prop->name;
                $propertyCode = $prop->code;
            }
        }

        // Fetch active registrations
        $regParams = array('event_id' => $selectedEventId, 'per_page' => 1000);
        if ($selectedPropertyId) {
            $regParams['property_id'] = $selectedPropertyId;
        } elseif (!$isHO && $userPropertyId) {
            $regParams['property_id'] = $userPropertyId;
        }
        $registrationsRes = Registrations::getApiDataProvider($regParams, 1000)->getData();
        $activeRegistrationIds = array();
        foreach ($registrationsRes as $reg) {
            $deletedAt = isset($reg->deleted_at) ? $reg->deleted_at : null;
            if ($deletedAt) continue;
            $status = isset($reg->status) ? (int)$reg->status : 0;
            if ($status !== Registrations::STATUS_DRAFT) {
                $regId = isset($reg->id) ? $reg->id : null;
                if ($regId) $activeRegistrationIds[$regId] = true;
            }
        }

        // Fetch attendees
        $attParams = array('event_id' => $selectedEventId, 'per_page' => 5000);
        if ($selectedPropertyId) {
            $attParams['property_id'] = $selectedPropertyId;
        } elseif (!$isHO && $userPropertyId) {
            $attParams['property_id'] = $userPropertyId;
        }
        $rawAttendees = Attendees::getApiDataProvider($attParams, 5000)->getData();

        $attendeeMap = array();
        $attendeePropertyMap = array();
        foreach ($rawAttendees as $att) {
            $attDeletedAt = isset($att->deleted_at) ? $att->deleted_at : null;
            if ($attDeletedAt) continue;
            $regId = isset($att->registration_id) ? $att->registration_id : null;
            if ($regId && isset($activeRegistrationIds[$regId])) {
                $attId = isset($att->id) ? $att->id : null;
                if ($attId) {
                    $attendeeMap[$attId] = $att;
                    $attPropId = isset($att->property_id) ? $att->property_id : null;
                    if ($attPropId) $attendeePropertyMap[$attId] = $attPropId;
                }
            }
        }

        // Initialize PHPExcel
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel.Classes');
        spl_autoload_unregister(array('YiiBase', 'autoload'));
        require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        $objPHPExcel = new PHPExcel();
        spl_autoload_register(array('YiiBase', 'autoload'));

        $objPHPExcel->getProperties()->setCreator("System")
            ->setTitle("Bao cao theo khach san");

        $headerStyle = array(
            'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF')),
            'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => '3A57E8')),
            'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
            'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
        );

        $borderStyle = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => 'E9ECEF'))));

        $sheetIndex = 0;

        // Sport Athletes
        if ($exportType === 'sport' || $exportType === 'all' || empty($exportType)) {
            $sheet = $sheetIndex === 0 ? $objPHPExcel->setActiveSheetIndex(0) : $objPHPExcel->createSheet($sheetIndex);
            $sheet->setTitle('VDV_The_thao');

            $teamParams = array('event_id' => $selectedEventId, 'per_page' => 1000);
            if ($selectedPropertyId) {
                $teamParams['property_id'] = $selectedPropertyId;
            }
            $sportTeams = SportTeams::getApiDataProvider($teamParams, 1000)->getData();

            $sportNameMap = array();
            $sportsList = Sports::getApiDataProvider(array('is_active' => 1), 500)->getData();
            foreach ($sportsList as $sp) {
                $spId = isset($sp->id) ? $sp->id : null;
                if ($spId) $sportNameMap[$spId] = isset($sp->name) ? $sp->name : '';
            }

            $membersRes = ApiClient::get(ApiEndpoints::SPORT_TEAM_MEMBER_LIST, array('event_id' => $selectedEventId, 'per_page' => 5000));
            $teamMembers = array();
            if ($membersRes['success']) {
                $teamMembers = isset($membersRes['data']['data']) ? $membersRes['data']['data'] : $membersRes['data'];
                if (!is_array($teamMembers)) $teamMembers = array();
            }

            $headers = array('STT', 'Họ tên VĐV', 'Phòng ban - Chức danh', 'Bộ môn thi đấu', 'Đơn vị');
            $col = 'A';
            foreach ($headers as $h) {
                $sheet->setCellValue($col . '1', $h);
                $sheet->getStyle($col . '1')->applyFromArray($headerStyle);
                $col++;
            }

            $row = 2;
            $stt = 1;
            foreach ($teamMembers as $m) {
                $attendeeId = isset($m['attendee_id']) ? $m['attendee_id'] : null;
                if (!$attendeeId || !isset($attendeeMap[$attendeeId])) continue;

                $propId = isset($attendeePropertyMap[$attendeeId]) ? $attendeePropertyMap[$attendeeId] : null;
                if ($selectedPropertyId && $propId != $selectedPropertyId) continue;

                $sportTeamId = isset($m['sport_team_id']) ? $m['sport_team_id'] : null;
                $sportName = '';
                $teamPropName = '';
                foreach ($sportTeams as $team) {
                    $tId = isset($team->id) ? $team->id : null;
                    if ($tId == $sportTeamId) {
                        $spId = isset($team->sport_id) ? $team->sport_id : null;
                        $sportName = isset($sportNameMap[$spId]) ? $sportNameMap[$spId] : '';
                        $teamPropName = isset($team->property_name) ? $team->property_name : '';
                        break;
                    }
                }

                $att = $attendeeMap[$attendeeId];
                $fullName = isset($att->full_name) ? $att->full_name : '';
                $position = isset($att->position) ? $att->position : '';
                $attPropName = isset($att->property_name) ? $att->property_name : $teamPropName;

                $sheet->setCellValue('A' . $row, $stt++);
                $sheet->setCellValue('B' . $row, $fullName);
                $sheet->setCellValue('C' . $row, $position);
                $sheet->setCellValue('D' . $row, $sportName);
                $sheet->setCellValue('E' . $row, $attPropName);
                $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray($borderStyle);
                $row++;
            }

            foreach (range('A', 'E') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }
            $sheetIndex++;
        }

        // Competition Contestants
        if ($exportType === 'competition' || $exportType === 'all' || empty($exportType)) {
            $sheet = $sheetIndex === 0 ? $objPHPExcel->setActiveSheetIndex(0) : $objPHPExcel->createSheet($sheetIndex);
            $sheet->setTitle('Thi_nghiep_vu');

            $compRegParams = array('event_id' => $selectedEventId, 'per_page' => 2000);
            if ($selectedPropertyId) {
                $compRegParams['property_id'] = $selectedPropertyId;
            }
            $competitionRegs = CompetitionRegistrations::getApiDataProvider($compRegParams, 2000)->getData();

            $headers = array('STT', 'Họ tên thí sinh', 'Phòng ban - Chức danh', 'Tên cuộc thi', 'Đơn vị');
            $col = 'A';
            foreach ($headers as $h) {
                $sheet->setCellValue($col . '1', $h);
                $sheet->getStyle($col . '1')->applyFromArray($headerStyle);
                $col++;
            }

            $row = 2;
            $stt = 1;
            foreach ($competitionRegs as $cr) {
                $crDeletedAt = isset($cr->deleted_at) ? $cr->deleted_at : null;
                if ($crDeletedAt) continue;

                $attendeeId = isset($cr->attendee_id) ? $cr->attendee_id : null;
                if (!$attendeeId || !isset($attendeeMap[$attendeeId])) continue;

                $propId = isset($attendeePropertyMap[$attendeeId]) ? $attendeePropertyMap[$attendeeId] : null;
                if ($selectedPropertyId && $propId != $selectedPropertyId) continue;

                $att = $attendeeMap[$attendeeId];
                $fullName = isset($att->full_name) ? $att->full_name : '';
                $position = isset($att->position) ? $att->position : '';
                $compName = isset($cr->competition_name) ? $cr->competition_name : '';
                $attPropName = isset($att->property_name) ? $att->property_name : '';

                $sheet->setCellValue('A' . $row, $stt++);
                $sheet->setCellValue('B' . $row, $fullName);
                $sheet->setCellValue('C' . $row, $position);
                $sheet->setCellValue('D' . $row, $compName);
                $sheet->setCellValue('E' . $row, $attPropName);
                $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray($borderStyle);
                $row++;
            }

            foreach (range('A', 'E') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }
            $sheetIndex++;
        }

        // Beauty Contestants
        if ($exportType === 'beauty' || $exportType === 'all' || empty($exportType)) {
            $sheet = $sheetIndex === 0 ? $objPHPExcel->setActiveSheetIndex(0) : $objPHPExcel->createSheet($sheetIndex);
            $sheet->setTitle('Thi_Miss');

            $contests = BeautyContests::getApiDataProvider(array('event_id' => $selectedEventId), 100)->getData();

            $headers = array('STT', 'Họ tên thí sinh', 'Phòng ban - Chức danh', 'Tên cuộc thi', 'Đơn vị');
            $col = 'A';
            foreach ($headers as $h) {
                $sheet->setCellValue($col . '1', $h);
                $sheet->getStyle($col . '1')->applyFromArray($headerStyle);
                $col++;
            }

            $row = 2;
            $stt = 1;
            foreach ($contests as $contest) {
                $contestId = isset($contest->id) ? $contest->id : null;
                $contestName = isset($contest->name) ? $contest->name : '';
                if (!$contestId) continue;

                $contestants = BeautyContestants::getApiDataProvider(array('contest_id' => $contestId), 1000)->getData();
                foreach ($contestants as $c) {
                    $cDeletedAt = isset($c->deleted_at) ? $c->deleted_at : null;
                    if ($cDeletedAt) continue;

                    $attendeeId = isset($c->attendee_id) ? $c->attendee_id : null;
                    if (!$attendeeId || !isset($attendeeMap[$attendeeId])) continue;

                    $propId = isset($attendeePropertyMap[$attendeeId]) ? $attendeePropertyMap[$attendeeId] : null;
                    if ($selectedPropertyId && $propId != $selectedPropertyId) continue;

                    $att = $attendeeMap[$attendeeId];
                    $fullName = isset($att->full_name) ? $att->full_name : '';
                    $position = isset($att->position) ? $att->position : '';
                    $attPropName = isset($att->property_name) ? $att->property_name : '';

                    $sheet->setCellValue('A' . $row, $stt++);
                    $sheet->setCellValue('B' . $row, $fullName);
                    $sheet->setCellValue('C' . $row, $position);
                    $sheet->setCellValue('D' . $row, $contestName);
                    $sheet->setCellValue('E' . $row, $attPropName);
                    $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray($borderStyle);
                    $row++;
                }
            }

            foreach (range('A', 'E') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }
            $sheetIndex++;
        }

        // Talent Entries
        if ($exportType === 'talent' || $exportType === 'all' || empty($exportType)) {
            $sheet = $sheetIndex === 0 ? $objPHPExcel->setActiveSheetIndex(0) : $objPHPExcel->createSheet($sheetIndex);
            $sheet->setTitle('Van_nghe');

            $shows = TalentShows::getApiDataProvider(array('event_id' => $selectedEventId), 100)->getData();
            $showIds = array();
            $showNames = array();
            foreach ($shows as $show) {
                $sId = isset($show->id) ? $show->id : null;
                if ($sId) {
                    $showIds[] = $sId;
                    $showNames[$sId] = isset($show->name) ? $show->name : '';
                }
            }

            $headers = array('STT', 'Tên tiết mục', 'Thể loại', 'Hội diễn', 'Số người', 'Đơn vị');
            $col = 'A';
            foreach ($headers as $h) {
                $sheet->setCellValue($col . '1', $h);
                $sheet->getStyle($col . '1')->applyFromArray($headerStyle);
                $col++;
            }

            $row = 2;
            $stt = 1;
            if (!empty($showIds)) {
                $entryParams = array('event_id' => $selectedEventId, 'per_page' => 1000);
                if ($selectedPropertyId) {
                    $entryParams['property_id'] = $selectedPropertyId;
                }
                $allEntries = TalentEntries::getApiDataProvider($entryParams, 1000)->getData();

                foreach ($allEntries as $entry) {
                    $entryDeletedAt = isset($entry->deleted_at) ? $entry->deleted_at : null;
                    if ($entryDeletedAt) continue;

                    $regId = isset($entry->registration_id) ? $entry->registration_id : null;
                    if (!$regId || !isset($activeRegistrationIds[$regId])) continue;

                    $eShowId = isset($entry->show_id) ? $entry->show_id : null;
                    if (!$eShowId || !in_array($eShowId, $showIds)) continue;

                    $title = isset($entry->title) ? $entry->title : '';
                    $categoryName = isset($entry->category_name) ? $entry->category_name : '';
                    $showName = isset($showNames[$eShowId]) ? $showNames[$eShowId] : '';
                    $participantCount = isset($entry->participant_count) ? (int)$entry->participant_count : 0;
                    $propName = isset($entry->property_name) ? $entry->property_name : '';

                    $sheet->setCellValue('A' . $row, $stt++);
                    $sheet->setCellValue('B' . $row, $title);
                    $sheet->setCellValue('C' . $row, $categoryName);
                    $sheet->setCellValue('D' . $row, $showName);
                    $sheet->setCellValue('E' . $row, $participantCount);
                    $sheet->setCellValue('F' . $row, $propName);
                    $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray($borderStyle);
                    $row++;
                }
            }

            foreach (range('A', 'F') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }
        }

        $objPHPExcel->setActiveSheetIndex(0);

        $safeName = $selectedPropertyId ? preg_replace('/[^A-Za-z0-9]/', '_', UrlTransliterate::cleanString($propertyName, '_')) : 'Tat_ca';
        $filename = "Bao_cao_theo_KS_" . $safeName . "_" . date('Ymd') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        Yii::app()->end();
    }
}
