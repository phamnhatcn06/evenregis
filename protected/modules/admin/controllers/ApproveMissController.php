<?php

class ApproveMissController extends AdminController
{
    public function init()
    {
        parent::init();
        $this->publicActions[] = 'index';
        $this->publicActions[] = 'getDetail';
        $this->publicActions[] = 'getRounds';
        $this->publicActions[] = 'approve';
        $this->publicActions[] = 'reject';
        $this->publicActions[] = 'exportPdf';
        $this->publicActions[] = 'exportExcel';
    }

    public function actionAdmin()
    {
        $grouping = $this->buildRoundGrouping(
            isset($_GET['contest_id']) && $_GET['contest_id'] !== '' ? $_GET['contest_id'] : null,
            isset($_GET['property_id']) && $_GET['property_id'] !== '' ? $_GET['property_id'] : null,
            isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : null,
            isset($_GET['keyword']) && $_GET['keyword'] !== '' ? $_GET['keyword'] : null
        );

        $this->render('index', array(
            'contests' => $this->getActiveContests(),
            'properties' => $this->getPropertiesWithContestants($grouping['allContestants']),
            'roundTabs' => $grouping['roundTabs'],
            'unassigned' => $grouping['unassigned'],
        ));
    }

    /**
     * Xuất PDF (in trình duyệt) danh sách thí sinh của một vòng thi.
     * Mỗi thí sinh là một trang landscape: bên trái ảnh đại diện, bên phải
     * toàn bộ thông tin. Header mỗi trang ghi rõ vòng đang được gán.
     *
     * @param mixed $round_id ID vòng thi, hoặc 'unassigned' cho nhóm chưa phân vòng.
     */
    public function actionExportPdf($round_id)
    {
        $grouping = $this->buildRoundGrouping(
            isset($_GET['contest_id']) && $_GET['contest_id'] !== '' ? $_GET['contest_id'] : null,
            isset($_GET['property_id']) && $_GET['property_id'] !== '' ? $_GET['property_id'] : null,
            isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : null,
            isset($_GET['keyword']) && $_GET['keyword'] !== '' ? $_GET['keyword'] : null
        );

        $roundName = '';
        $contestants = array();
        if ($round_id === 'unassigned') {
            $roundName = 'Chưa phân vòng';
            $contestants = $grouping['unassigned'];
        } else {
            foreach ($grouping['roundTabs'] as $tab) {
                if ($tab['id'] == $round_id) {
                    $roundName = $tab['name'];
                    $contestants = $tab['contestants'];
                    break;
                }
            }
        }

        $this->renderPartial('_export_pdf', array(
            'roundName' => $roundName,
            'contestants' => $contestants,
        ));
    }

    /**
     * Xuất Excel danh sách thí sinh của một vòng thi (theo tab đang xem),
     * bao gồm toàn bộ thông tin của mỗi thí sinh. Giữ nguyên bộ lọc hiện tại
     * để danh sách trùng khớp với những gì đang hiển thị.
     *
     * @param mixed $round_id ID vòng thi, hoặc 'unassigned' cho nhóm chưa phân vòng.
     */
    public function actionExportExcel($round_id)
    {
        // Export nạp nhiều record kèm quan hệ + ghi file Excel → nới giới hạn
        // để tránh lỗi 500 (exhausted memory / timeout) trên server.
        @ini_set('memory_limit', '512M');
        if (function_exists('set_time_limit')) {
            @set_time_limit(300);
        }

        // [DEBUG TẠM] Ghi tiến độ ra file để xác định action chết ở bước nào
        // khi worker bị kill/segfault (PHP error log không ghi được).
        $dbg = Yii::getPathOfAlias('application.runtime') . DIRECTORY_SEPARATOR . 'export_debug.log';
        $mark = function ($step) use ($dbg) {
            @file_put_contents($dbg, date('H:i:s') . " | " . $step . " | mem=" . round(memory_get_usage(true) / 1048576) . "MB\n", FILE_APPEND);
        };
        $mark('START round_id=' . $round_id);

        $grouping = $this->buildRoundGrouping(
            isset($_GET['contest_id']) && $_GET['contest_id'] !== '' ? $_GET['contest_id'] : null,
            isset($_GET['property_id']) && $_GET['property_id'] !== '' ? $_GET['property_id'] : null,
            isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : null,
            isset($_GET['keyword']) && $_GET['keyword'] !== '' ? $_GET['keyword'] : null
        );

        $mark('AFTER buildRoundGrouping');

        $roundName = '';
        $contestants = array();
        if ($round_id === 'unassigned') {
            $roundName = 'Chưa phân vòng';
            $contestants = $grouping['unassigned'];
        } else {
            foreach ($grouping['roundTabs'] as $tab) {
                if ($tab['id'] == $round_id) {
                    $roundName = $tab['name'];
                    $contestants = $tab['contestants'];
                    break;
                }
            }
        }

        $mark('CONTESTANTS count=' . count($contestants));

        // Khởi tạo PHPExcel
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel.Classes');
        spl_autoload_unregister(array('YiiBase', 'autoload'));
        require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        $mark('AFTER require PHPExcel');
        $objPHPExcel = new PHPExcel();
        spl_autoload_register(array('YiiBase', 'autoload'));
        $mark('AFTER new PHPExcel');

        $objPHPExcel->getProperties()->setCreator("System")
            ->setLastModifiedBy("System")
            ->setTitle("Danh sach thi sinh Miss")
            ->setSubject("Danh sach thi sinh Miss");

        $sheet = $objPHPExcel->setActiveSheetIndex(0);
        $sheet->setTitle('Miss');

        $headerStyle = array(
            'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF'), 'size' => 11),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => '3A57E8')
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => 'CCCCCC')
                )
            )
        );

        $borderStyle = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => 'E9ECEF')
                )
            )
        );

        $headers = array(
            'STT', 'Cuộc thi', 'Vòng thi', 'Đơn vị', 'Bộ phận', 'Thí sinh',
            'Ngày sinh', 'Tuổi', 'Chiều cao (cm)', 'Cân nặng (kg)', 'Số đo',
            'Năng khiếu', 'Tiểu sử', 'Email cá nhân', 'Trạng thái'
        );
        $lastCol = 'O';
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '1', $h);
            $sheet->getStyle($col . '1')->applyFromArray($headerStyle);
            $col++;
        }

        $statusOptions = BeautyContestants::getStatusOptions();

        $rowNum = 2;
        $stt = 1;
        foreach ($contestants as $c) {
            $unitName = '';
            if (!empty($c->property_name)) {
                $unitName = $c->property_name;
            } elseif (!empty($c->registration_id)) {
                $unitName = BeautyContestants::getPropertyNameByRegistrationId($c->registration_id);
            }

            $attendeeName = '';
            if (isset($c->members) && !empty($c->members)) {
                $attendeeName = $c->members[0]['attendee_name'];
            } elseif (!empty($c->attendee_name)) {
                $attendeeName = $c->attendee_name;
            }

            $birthDate = MyHelper::formatDate($c->birthday);
            $age = MyHelper::calculateAge($c->birthday);

            $statusText = isset($statusOptions[$c->status]) ? $statusOptions[$c->status] : $c->status;

            $sheet->setCellValue('A' . $rowNum, $stt++);
            $sheet->setCellValue('B' . $rowNum, $this->cleanCell(isset($c->contest_name) ? $c->contest_name : ''));
            $sheet->setCellValue('C' . $rowNum, $this->cleanCell($roundName));
            $sheet->setCellValue('D' . $rowNum, $this->cleanCell($unitName));
            $sheet->setCellValue('E' . $rowNum, $this->cleanCell(isset($c->department_name) ? $c->department_name : ''));
            $sheet->setCellValue('F' . $rowNum, $this->cleanCell($attendeeName));
            $sheet->setCellValueExplicit('G' . $rowNum, $this->cleanCell($birthDate), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('H' . $rowNum, $age !== null ? $age : '');
            $sheet->setCellValue('I' . $rowNum, $c->height_cm);
            $sheet->setCellValue('J' . $rowNum, $c->weight_kg);
            $sheet->setCellValueExplicit('K' . $rowNum, $this->cleanCell($c->measurements), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('L' . $rowNum, $this->cleanCell($c->talent));
            $sheet->setCellValue('M' . $rowNum, $this->cleanCell($c->bio));
            $sheet->setCellValue('N' . $rowNum, $this->cleanCell(isset($c->personal_email) ? $c->personal_email : ''));
            $sheet->setCellValue('O' . $rowNum, $this->cleanCell($statusText));

            $sheet->getStyle('A' . $rowNum . ':' . $lastCol . $rowNum)->applyFromArray($borderStyle);
            $rowNum++;
        }

        // Dùng độ rộng cố định thay vì setAutoSize(true) — autosize tính toán
        // rất tốn CPU/RAM với nhiều dòng, dễ gây timeout/500 trên server.
        $columnWidths = array(
            'A' => 6, 'B' => 22, 'C' => 18, 'D' => 22, 'E' => 20, 'F' => 24,
            'G' => 13, 'H' => 8, 'I' => 14, 'J' => 14, 'K' => 16, 'L' => 26,
            'M' => 32, 'N' => 26, 'O' => 16,
        );
        foreach ($columnWidths as $columnID => $width) {
            $sheet->getColumnDimension($columnID)->setWidth($width);
        }

        $safeRound = preg_replace('/[^A-Za-z0-9]+/', '_', $this->toAscii($roundName));
        $filename = "Danh_sach_Miss_" . ($safeRound !== '' ? $safeRound . '_' : '') . date('Ymd_His') . ".xlsx";

        // Xóa mọi output buffer để tránh file lỗi
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        $mark('BEFORE createWriter');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $mark('BEFORE save');
        $objWriter->save('php://output');
        $mark('AFTER save DONE');
        Yii::app()->end();
    }

    /**
     * Làm sạch chuỗi trước khi ghi vào cell Excel.
     *
     * PHPExcel/Excel2007 đẩy nội dung cell qua libxml; nếu chuỗi chứa byte
     * UTF-8 hỏng hoặc ký tự điều khiển bị cấm trong XML, một số bản PHP/libxml
     * sẽ segfault khi save (crash im lặng, không ghi PHP error log).
     */
    protected function cleanCell($value)
    {
        if ($value === null || $value === false) {
            return '';
        }
        $value = (string) $value;
        if ($value === '') {
            return '';
        }

        // Ép về UTF-8 hợp lệ, bỏ mọi byte hỏng
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'UTF-8//IGNORE', $value);
            if ($converted !== false) {
                $value = $converted;
            }
        }

        // Loại ký tự điều khiển không hợp lệ trong XML 1.0
        // (giữ lại TAB \x09, LF \x0A, CR \x0D)
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $value);

        return $value;
    }

    /**
     * Chuyển chuỗi tiếng Việt có dấu về không dấu để dùng trong tên file.
     */
    protected function toAscii($str)
    {
        $str = (string) $str;
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
            if ($converted !== false) {
                $str = $converted;
            }
        }
        return preg_replace('/[^A-Za-z0-9]+/', '_', $str);
    }

    /**
     * Gom nhóm thí sinh theo vòng thi cao nhất mà họ đang được gán.
     *
     * @return array Mảng gồm:
     *  - allContestants: toàn bộ thí sinh trước khi lọc theo đơn vị (dùng cho dropdown đơn vị)
     *  - rounds: danh sách vòng thi
     *  - roundTabs: các tab theo vòng, mỗi tab kèm danh sách thí sinh
     *  - unassigned: thí sinh chưa được gán vào vòng nào
     */
    protected function buildRoundGrouping($contestId = null, $propertyId = null, $status = null, $keyword = null)
    {
        $params = array(
            'with' => 'attendee,attendee.property,attendee.property.regional,contest',
            'sort' => 'attendee.property.regional.code',
        );
        if ($contestId !== null) {
            $params['contest_id'] = $contestId;
        }
        if ($status !== null) {
            $params['status'] = $status;
        }
        if ($keyword !== null) {
            $params['keyword'] = $keyword;
        }

        $allContestants = BeautyContestants::getApiDataProvider($params, 1000)->getData();

        // Filter theo property_id phía PHP
        $contestants = $allContestants;
        if ($propertyId !== null) {
            $contestants = array_values(array_filter($contestants, function ($c) use ($propertyId) {
                return isset($c->property_id) && $c->property_id == $propertyId;
            }));
        }

        // Bổ sung bộ phận (department) và năm sinh cho từng thí sinh
        $this->enrichContestantsWithAttendeeInfo($contestants);

        // Map contestant id => object để tra cứu khi gom nhóm theo vòng
        $contestantMap = array();
        foreach ($contestants as $c) {
            $contestantMap[$c->id] = $c;
        }

        // Lấy danh sách vòng thi (sắp xếp theo round_order)
        $roundParams = array('sort' => 'round_order');
        if ($contestId !== null) {
            $roundParams['contest_id'] = $contestId;
        }
        $rounds = BeautyRounds::getApiDataProvider($roundParams, 100)->getData();

        // Xác định vòng cao nhất mà mỗi thí sinh đang tham gia.
        // $rounds đã sắp theo round_order tăng dần nên vòng ghi nhận sau cùng
        // chính là vòng mới nhất -> thí sinh chỉ hiển thị ở vòng đó.
        $assignedIds = array();
        $latestRoundOf = array();
        foreach ($rounds as $round) {
            $results = BeautyRoundResults::getApiDataProvider(array(
                'round_id' => $round->id,
            ), 1000)->getData();

            foreach ($results as $res) {
                if (isset($contestantMap[$res->registration_id])) {
                    $latestRoundOf[$res->registration_id] = $round->id;
                    $assignedIds[$res->registration_id] = true;
                }
            }
        }

        // Gom nhóm thí sinh theo vòng cao nhất -> tabs
        $roundTabs = array();
        foreach ($rounds as $round) {
            $items = array();
            foreach ($contestants as $c) {
                if (isset($latestRoundOf[$c->id]) && $latestRoundOf[$c->id] == $round->id) {
                    $items[] = $c;
                }
            }

            $roundTabs[] = array(
                'id' => $round->id,
                'name' => $round->name,
                'contest_name' => isset($round->contest_name) ? $round->contest_name : '',
                'round_type' => isset($round->round_type) ? $round->round_type : '',
                'contestants' => $items,
            );
        }

        // Thí sinh chưa được gán vào vòng nào
        $unassigned = array();
        foreach ($contestants as $c) {
            if (!isset($assignedIds[$c->id])) {
                $unassigned[] = $c;
            }
        }

        return array(
            'allContestants' => $allContestants,
            'rounds' => $rounds,
            'roundTabs' => $roundTabs,
            'unassigned' => $unassigned,
        );
    }

    public function actionGetDetail($id)
    {
        $model = BeautyContestants::fetchFromApi($id);
        if ($model === null) {
            echo CJSON::encode(array('success' => false, 'message' => 'Không tìm thấy thí sinh'));
            Yii::app()->end();
        }

        // Bổ sung phòng ban + ngày sinh (ưu tiên attendee, fallback staff)
        $departmentName = '';
        $birthdayDisplay = '';
        if (!empty($model->attendee_id)) {
            $attendee = Attendees::fetchFromApi($model->attendee_id);
            if ($attendee !== null) {
                $departmentName = $this->resolveDepartmentName($attendee);
                $birthday = $this->resolveBirthday($attendee);
                $dateStr = MyHelper::formatDate($birthday);
                if ($dateStr !== '') {
                    $age = MyHelper::calculateAge($birthday);
                    $birthdayDisplay = $dateStr . ($age !== null ? ' (' . $age . ' tuổi)' : '');
                }
            }
        }

        $data = array(
            'id' => $model->id,
            'contest_id' => $model->contest_id,
            'attendee_name' => $model->attendee_name,
            'property_name' => $model->property_name,
            'department_name' => $departmentName,
            'birthday_display' => $birthdayDisplay,
            'contest_name' => $model->contest_name,
            'height_cm' => $model->height_cm,
            'weight_kg' => $model->weight_kg,
            'measurements' => $model->measurements,
            'talent' => $model->talent,
            'bio' => $model->bio,
            'personal_email' => $model->personal_email,
            'status' => $model->status,
            'status_label' => BeautyContestants::getStatusLabel($model->status),
            'photo_portrait' => $this->getOptimizedPhotoUrl($model->photo_portrait, 800),
            'photo_portrait_2' => $this->getOptimizedPhotoUrl($model->photo_portrait_2, 800),
            'photo_full_body' => $this->getOptimizedPhotoUrl($model->photo_full_body, 800),
            'photo_full_body_2' => $this->getOptimizedPhotoUrl($model->photo_full_body_2, 800),
            'video_path' => $this->getOptimizedVideoPath($model->video_path),
            'video_path_original' => $model->video_path,
            'submitted_at' => $model->submitted_at,
            'created_at' => $model->created_at,
        );

        echo CJSON::encode(array('success' => true, 'data' => $data));
        Yii::app()->end();
    }

    public function actionGetRounds($contest_id, $contestant_id = null)
    {
        $rounds = BeautyRounds::getApiDataProvider(array(
            'contest_id' => $contest_id,
            'sort' => 'round_order',
        ), 100)->getData();

        $assignedRoundIds = array();
        if ($contestant_id) {
            $results = BeautyRoundResults::getApiDataProvider(array(
                'registration_id' => $contestant_id,
            ), 100)->getData();
            foreach ($results as $r) {
                $assignedRoundIds[] = $r->round_id;
            }
        }

        // Trả về tất cả vòng để có thể gán vào bất kỳ vòng nào; vòng đã gán được
        // đánh dấu bằng cờ `assigned` (vẫn chọn lại được).
        $data = array();
        foreach ($rounds as $r) {
            $data[] = array(
                'id' => $r->id,
                'name' => $r->name,
                'round_type' => $r->round_type,
                'round_order' => $r->round_order,
                'assigned' => in_array($r->id, $assignedRoundIds),
            );
        }

        echo CJSON::encode(array('success' => true, 'data' => $data));
        Yii::app()->end();
    }

    public function actionApprove()
    {
        if (!Yii::app()->request->isPostRequest) {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ');
        }

        $id = Yii::app()->request->getPost('id');
        $roundId = Yii::app()->request->getPost('round_id');

        if (empty($id)) {
            echo CJSON::encode(array('success' => false, 'message' => 'Thiếu ID'));
            Yii::app()->end();
        }

        $model = BeautyContestants::fetchFromApi($id);
        if ($model === null) {
            echo CJSON::encode(array('success' => false, 'message' => 'Không tìm thấy thí sinh'));
            Yii::app()->end();
        }

        $result = $model->updateStatusViaApi(BeautyContestants::STATUS_CONFIRMED);

        if (!$result['success']) {
            echo CJSON::encode(array('success' => false, 'message' => $result['error'] ?: 'Có lỗi xảy ra'));
            Yii::app()->end();
        }

        if (!empty($roundId)) {
            $assignResult = BeautyRoundResults::assignExclusive($roundId, $id);
            if (!$assignResult['success']) {
                echo CJSON::encode(array(
                    'success' => true,
                    'message' => 'Đã duyệt thí sinh nhưng không thể gán vào vòng thi: ' . ($assignResult['error'] ?: '')
                ));
                Yii::app()->end();
            }
        }

        echo CJSON::encode(array('success' => true, 'message' => 'Đã duyệt và gán thí sinh vào vòng thi'));
        Yii::app()->end();
    }

    public function actionReject()
    {
        if (!Yii::app()->request->isPostRequest) {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ');
        }

        $id = Yii::app()->request->getPost('id');
        $reason = Yii::app()->request->getPost('reason', '');

        if (empty($id)) {
            echo CJSON::encode(array('success' => false, 'message' => 'Thiếu ID'));
            Yii::app()->end();
        }

        $model = BeautyContestants::fetchFromApi($id);
        if ($model === null) {
            echo CJSON::encode(array('success' => false, 'message' => 'Không tìm thấy thí sinh'));
            Yii::app()->end();
        }

        $result = $model->updateStatusViaApi(BeautyContestants::STATUS_DISQUALIFIED);

        if ($result['success']) {
            echo CJSON::encode(array('success' => true, 'message' => 'Đã từ chối thí sinh'));
        } else {
            echo CJSON::encode(array('success' => false, 'message' => $result['error'] ?: 'Có lỗi xảy ra'));
        }
        Yii::app()->end();
    }

    protected function getActiveContests()
    {
        $result = ApiClient::get(ApiEndpoints::BEAUTY_CONTEST_LIST, array(
            'is_active' => 1,
            'per_page' => 100,
        ));

        $list = array();
        if ($result['success'] && isset($result['data']['data'])) {
            foreach ($result['data']['data'] as $item) {
                $list[$item['id']] = $item['name'];
            }
        }
        return $list;
    }

    protected function getPropertiesWithContestants($contestants = null)
    {
        $list = array();

        if ($contestants === null) {
            $result = ApiClient::get(ApiEndpoints::BEAUTY_CONTESTANT_LIST, array(
                'per_page' => 1000,
            ));
            if ($result['success'] && isset($result['data']['data'])) {
                foreach ($result['data']['data'] as $item) {
                    if (!empty($item['property_id']) && !empty($item['property_name'])) {
                        $list[$item['property_id']] = $item['property_name'];
                    }
                }
            }
        } else {
            foreach ($contestants as $c) {
                if (!empty($c->property_id) && !empty($c->property_name)) {
                    $list[$c->property_id] = $c->property_name;
                }
            }
        }

        asort($list);
        return $list;
    }

    /**
     * Bổ sung thông tin bộ phận (department) và năm sinh cho danh sách thí sinh.
     * Dữ liệu lấy từ người tham dự (attendees) tương ứng — chỉ cần 1 lần gọi API
     * rồi map theo attendee_id để tránh N+1.
     *
     * Lưu ý: năm sinh (birthday) ưu tiên lấy từ attendee; nếu attendee không có
     * thì tra sang nhân viên (staff) tương ứng qua staff_id (có cache tránh gọi trùng).
     */
    protected function enrichContestantsWithAttendeeInfo(&$contestants)
    {
        if (empty($contestants)) {
            return;
        }

        // Gom attendee_id cần tra cứu
        $attendeeIds = array();
        foreach ($contestants as $c) {
            if (!empty($c->attendee_id)) {
                $attendeeIds[$c->attendee_id] = true;
            }
        }
        if (empty($attendeeIds)) {
            return;
        }

        // Lấy danh sách người tham dự (1 lần) rồi map theo id
        $attendees = Attendees::getApiDataProvider(array(), 10000)->getData();
        $attendeeMap = array();
        foreach ($attendees as $a) {
            $attendeeMap[$a->id] = $a;
        }

        $staffBirthdayCache = array();

        foreach ($contestants as $c) {
            if (empty($c->attendee_id) || !isset($attendeeMap[$c->attendee_id])) {
                continue;
            }
            $a = $attendeeMap[$c->attendee_id];
            $c->department_name = $this->resolveDepartmentName($a);
            $c->division_name = $a->division_name;
            $c->birthday = $this->resolveBirthday($a, $staffBirthdayCache);
        }
    }

    /**
     * Tên phòng ban của attendee: ưu tiên department_name, fallback division_name.
     */
    protected function resolveDepartmentName($attendee)
    {
        return !empty($attendee->department_name) ? $attendee->department_name : $attendee->division_name;
    }

    /**
     * Ngày sinh: ưu tiên từ attendee; nếu trống thì lấy từ staff qua staff_id.
     * @param array $staffBirthdayCache Cache theo staff_id để tránh gọi API trùng.
     */
    protected function resolveBirthday($attendee, &$staffBirthdayCache = array())
    {
        $birthday = $attendee->birthday;
        if (empty($birthday) && !empty($attendee->staff_id)) {
            $staffId = $attendee->staff_id;
            if (!array_key_exists($staffId, $staffBirthdayCache)) {
                $staff = Staffs::fetchFromApi($staffId);
                $staffBirthdayCache[$staffId] = ($staff !== null && !empty($staff->birthday)) ? $staff->birthday : null;
            }
            if (!empty($staffBirthdayCache[$staffId])) {
                $birthday = $staffBirthdayCache[$staffId];
            }
        }
        return $birthday;
    }

    /**
     * Lấy đường dẫn video đã tối ưu (_web) nếu tồn tại
     */
    protected function getOptimizedVideoPath($videoPath)
    {
        if (empty($videoPath)) {
            return $videoPath;
        }

        $basePath = Yii::getPathOfAlias('webroot');
        $relativePath = ltrim(str_replace(Yii::app()->baseUrl, '', $videoPath), '/');
        $fullPath = $basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

        $pathInfo = pathinfo($fullPath);
        $webFile = $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $pathInfo['filename'] . '_web.' . $pathInfo['extension'];

        if (file_exists($webFile)) {
            $webRelative = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_web.' . $pathInfo['extension'];
            return str_replace($basePath, Yii::app()->baseUrl, str_replace(DIRECTORY_SEPARATOR, '/', $webRelative));
        }

        return $videoPath;
    }

    /**
     * Lấy đường dẫn ảnh tối ưu (thumbnail) qua controller
     */
    protected function getOptimizedPhotoUrl($photoUrl, $width = 800)
    {
        if (empty($photoUrl)) {
            return $photoUrl;
        }

        $pos = strpos($photoUrl, '/uploads/miss/');
        if ($pos !== false) {
            $cleanPath = substr($photoUrl, $pos + strlen('/uploads/miss/'));
            return Yii::app()->createUrl('/admin/missFile/view') . '?path=' . urlencode($cleanPath) . '&w=' . $width;
        }

        return $photoUrl;
    }
}
