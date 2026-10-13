<?php

/**
 * Quản trị đăng ký bộ môn chạy: xem danh sách theo sự kiện, cấp số lucky, xuất Excel.
 * Permission key: "runregistrations".
 */
class RunRegistrationsController extends AdminController
{
    public function actionAdmin()
    {
        $eventId = isset($_GET['event_id']) && $_GET['event_id'] !== '' ? (int) $_GET['event_id'] : null;

        $allRegistrations = $eventId ? RunRegistrations::listByEvent($eventId) : array();
        $cancelRequestCount = $eventId ? count(RunRegistrations::listCancelRequests($eventId)) : 0;

        // Giá trị lọc hiện tại.
        $filters = array(
            'q'       => isset($_GET['q']) ? trim($_GET['q']) : '',
            'cu_ly'   => isset($_GET['cu_ly']) ? trim($_GET['cu_ly']) : '',
            'gender'  => isset($_GET['gender']) && $_GET['gender'] !== '' ? (string) $_GET['gender'] : '',
            'unit'    => isset($_GET['unit']) ? trim($_GET['unit']) : '',
        );

        // Danh sách tuỳ chọn cho dropdown (lấy từ TOÀN BỘ dữ liệu, trước khi lọc).
        $cuLyOptions = array();
        $unitOptions = array();
        foreach ($allRegistrations as $r) {
            if (!empty($r['run_event_name'])) {
                $cuLyOptions[$r['run_event_name']] = $r['run_event_name'];
            }
            if (!empty($r['unit_label'])) {
                $unitOptions[$r['unit_label']] = $r['unit_label'];
            }
        }
        ksort($cuLyOptions);
        ksort($unitOptions);

        // Áp dụng lọc.
        $registrations = array_filter($allRegistrations, function ($r) use ($filters) {
            if ($filters['cu_ly'] !== '' && (!isset($r['run_event_name']) || $r['run_event_name'] !== $filters['cu_ly'])) {
                return false;
            }
            if ($filters['unit'] !== '' && (!isset($r['unit_label']) || $r['unit_label'] !== $filters['unit'])) {
                return false;
            }
            if ($filters['gender'] !== '') {
                $g = isset($r['gender']) && $r['gender'] !== null ? (string) $r['gender'] : '';
                if ($g !== $filters['gender']) {
                    return false;
                }
            }
            if ($filters['q'] !== '') {
                $needle = function_exists('mb_strtolower') ? mb_strtolower($filters['q'], 'UTF-8') : strtolower($filters['q']);
                $haystack = implode(' ', array(
                    isset($r['full_name']) ? $r['full_name'] : '',
                    isset($r['bib_number']) ? $r['bib_number'] : '',
                    isset($r['phone_number']) ? $r['phone_number'] : '',
                    isset($r['lucky_number']) ? $r['lucky_number'] : '',
                ));
                $haystack = function_exists('mb_strtolower') ? mb_strtolower($haystack, 'UTF-8') : strtolower($haystack);
                if (strpos($haystack, $needle) === false) {
                    return false;
                }
            }
            return true;
        });
        $registrations = array_values($registrations);

        $this->render('admin', array(
            'eventId'            => $eventId,
            'eventList'          => $this->getEventList(),
            'registrations'      => $registrations,
            'totalCount'         => count($allRegistrations),
            'cancelRequestCount' => $cancelRequestCount,
            'filters'            => $filters,
            'cuLyOptions'        => $cuLyOptions,
            'unitOptions'        => $unitOptions,
            'runEventList'       => $eventId ? $this->getRunEventList($eventId) : array(),
            'canUpdate'          => PermissionHelper::can('runregistrations', 'update'),
        ));
    }

    /**
     * Tìm người (finalist đã có mã định danh) để admin gán thủ công vào nội dung chạy.
     * Trả JSON danh sách gọn theo từ khoá + sự kiện.
     */
    public function actionSearchPeople()
    {
        $eventId = (int) Yii::app()->request->getParam('event_id');
        $keyword = trim((string) Yii::app()->request->getParam('keyword'));

        $results = array();
        if ($eventId && $keyword !== '') {
            $rows = FinalAttendeeRosters::fetchPage(array(
                'event_id' => $eventId,
                'keyword'  => $keyword,
            ), 1, 25);

            foreach ($rows as $row) {
                $attendeeId = isset($row['attendee_id']) ? (int) $row['attendee_id'] : 0;
                if (!$attendeeId || !empty($row['is_withdrawn'])) {
                    continue; // Chỉ gán được người còn tư cách và đã gắn bản ghi gốc.
                }
                $unit = '';
                foreach (array('property_name', 'unit_label', 'badge_org_name') as $key) {
                    if (!empty($row[$key])) {
                        $unit = (string) $row[$key];
                        break;
                    }
                }
                $results[] = array(
                    'attendee_id'  => $attendeeId,
                    'full_name'    => isset($row['full_name']) ? $row['full_name'] : '',
                    'unit'         => $unit,
                    'lucky_number' => isset($row['lucky_number']) ? $row['lucky_number'] : '',
                    'birth_year'   => !empty($row['birth_year']) ? (int) $row['birth_year'] : '',
                    'gender'       => (isset($row['gender']) && $row['gender'] !== null && $row['gender'] !== '') ? (string) ((int) $row['gender']) : '',
                );
            }
        }

        $this->renderJson(array('success' => true, 'data' => $results));
    }

    /**
     * Admin gán thủ công một người vào một cự ly với mã BIB tự điền (POST, JSON).
     * Lưu năm sinh/giới tính trước rồi gọi API gán (kiểm tra trùng BIB ở backend).
     */
    public function actionManualAssign()
    {
        if (!Yii::app()->request->isPostRequest) {
            $this->renderJson(array('success' => false, 'message' => 'Yêu cầu không hợp lệ.'), 400);
            return;
        }
        if (!PermissionHelper::can('runregistrations', 'update')) {
            $this->renderJson(array('success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này.'), 403);
            return;
        }

        $request    = Yii::app()->request;
        $attendeeId = (int) $request->getPost('attendee_id');
        $runEventId = (int) $request->getPost('run_event_id');
        $bibNumber  = trim((string) $request->getPost('bib_number'));
        $birthYear  = $request->getPost('birth_year');
        $gender     = $request->getPost('gender');

        $birthYear = ($birthYear !== null && $birthYear !== '') ? (int) $birthYear : null;
        $gender    = ($gender !== null && $gender !== '') ? (int) $gender : null;

        if (!$attendeeId) {
            $this->renderJson(array('success' => false, 'message' => 'Vui lòng chọn người cần gán.'), 422);
            return;
        }
        if (!$runEventId) {
            $this->renderJson(array('success' => false, 'message' => 'Vui lòng chọn cự ly.'), 422);
            return;
        }
        if ($birthYear === null || $gender === null || !in_array($gender, array(0, 1), true)) {
            $this->renderJson(array('success' => false, 'message' => 'Vui lòng chọn năm sinh và giới tính.'), 422);
            return;
        }
        if ($bibNumber === '') {
            $this->renderJson(array('success' => false, 'message' => 'Vui lòng nhập mã BIB.'), 422);
            return;
        }

        $ssoUser   = AuthHandler::getUser();
        $authEmail = isset($ssoUser['email']) ? $ssoUser['email'] : null;

        // Lưu năm sinh + giới tính trước (để suy nhãn nội dung chi tiết).
        $profile = RunAuth::saveProfile($attendeeId, $birthYear, $gender, $authEmail);
        if (!$profile['success']) {
            $status = isset($profile['code']) && (int) $profile['code'] >= 400 ? (int) $profile['code'] : 500;
            $this->renderJson(array(
                'success' => false,
                'message' => $profile['error'] ?: 'Không lưu được năm sinh/giới tính.',
            ), $status);
            return;
        }

        $res = RunRegistrations::adminManualAssignViaApi($runEventId, $attendeeId, $bibNumber, $birthYear, $gender, $authEmail);
        if (!$res['success']) {
            $status = isset($res['code']) && (int) $res['code'] >= 400 ? (int) $res['code'] : 500;
            $this->renderJson(array('success' => false, 'message' => $res['error'] ?: 'Không gán được vào nội dung chạy.'), $status);
            return;
        }

        $data = isset($res['data']['data']) ? $res['data']['data'] : array();
        $bib  = isset($data['bib_number']) ? $data['bib_number'] : $bibNumber;
        $this->renderJson(array(
            'success' => true,
            'message' => 'Đã gán vào nội dung chạy (BIB ' . $bib . ').',
        ));
    }

    /**
     * Cấp số lucky cho người VCK của sự kiện — ĐÃ NGỪNG SỬ DỤNG.
     *
     * Lệnh này cấp mã theo TỪNG BẢN GHI attendee, nên một người có nhiều bản ghi sẽ nhận nhiều
     * mã khác nhau. Việc cấp mã đã chuyển sang màn Tổng hợp danh sách Vòng Chung Kết, nơi mã
     * được cấp theo NGƯỜI đã gộp (một người đúng một mã, không bao giờ đổi).
     *
     * Giữ lại phần thân code để còn đường cứu hộ nếu màn mới gặp sự cố, nhưng chặn mọi lời gọi.
     */
    public function actionGenLucky()
    {
        throw new CHttpException(
            410,
            'Chức năng cấp số lucky ở màn này đã ngừng sử dụng vì cấp mã theo từng bản ghi '
            . '(một người có thể nhận nhiều mã). Vui lòng cấp mã ở màn Tổng hợp danh sách '
            . 'Vòng Chung Kết: /admin/finalAttendeeRosters/admin'
        );

        if (!Yii::app()->request->isPostRequest) {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }
        if (!PermissionHelper::can('runregistrations', 'update')) {
            throw new CHttpException(403, 'Bạn không có quyền thực hiện thao tác này.');
        }
        $eventId = (int) Yii::app()->request->getPost('event_id');
        $res = RunAuth::genLucky($eventId);
        if ($res['success']) {
            $msg = isset($res['data']['message']) ? $res['data']['message'] : 'Đã cấp số lucky.';
            Yii::app()->user->setFlash('success', $msg);
        } else {
            Yii::app()->user->setFlash('error', $res['error'] ?: 'Không thể cấp số lucky.');
        }
        $this->redirect(array('admin', 'event_id' => $eventId));
    }

    /** Danh sách yêu cầu hủy chờ duyệt theo sự kiện. */
    public function actionCancelRequests()
    {
        $eventId = isset($_GET['event_id']) && $_GET['event_id'] !== '' ? (int) $_GET['event_id'] : null;

        $requests = $eventId ? RunRegistrations::listCancelRequests($eventId) : array();

        $this->render('cancelRequests', array(
            'eventId'   => $eventId,
            'eventList' => $this->getEventList(),
            'requests'  => $requests,
        ));
    }

    /** Duyệt hủy một đăng ký (POST). */
    public function actionApproveCancel($id)
    {
        if (!Yii::app()->request->isPostRequest) {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }
        if (!PermissionHelper::can('runregistrations', 'update')) {
            throw new CHttpException(403, 'Bạn không có quyền thực hiện thao tác này.');
        }
        $res = RunRegistrations::approveCancelViaApi((int) $id);
        if ($res['success']) {
            Yii::app()->user->setFlash('success', isset($res['data']['message']) ? $res['data']['message'] : 'Đã duyệt hủy và hoàn suất.');
        } else {
            Yii::app()->user->setFlash('error', $res['error'] ?: 'Không thể duyệt hủy.');
        }
        $this->redirect(array('cancelRequests', 'event_id' => Yii::app()->request->getParam('event_id')));
    }

    /** Từ chối hủy một đăng ký (POST). */
    public function actionRejectCancel($id)
    {
        if (!Yii::app()->request->isPostRequest) {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }
        if (!PermissionHelper::can('runregistrations', 'update')) {
            throw new CHttpException(403, 'Bạn không có quyền thực hiện thao tác này.');
        }
        $res = RunRegistrations::rejectCancelViaApi((int) $id);
        if ($res['success']) {
            Yii::app()->user->setFlash('success', isset($res['data']['message']) ? $res['data']['message'] : 'Đã từ chối yêu cầu hủy.');
        } else {
            Yii::app()->user->setFlash('error', $res['error'] ?: 'Không thể từ chối yêu cầu hủy.');
        }
        $this->redirect(array('cancelRequests', 'event_id' => Yii::app()->request->getParam('event_id')));
    }

    public function actionExport($event_id)
    {
        $eventId = (int) $event_id;
        $rows = RunRegistrations::listByEvent($eventId);

        $excel = $this->createPhpExcel();
        $sheet = $excel->getActiveSheet();
        $sheet->setTitle('Dang ky chay');

        $sheet->setCellValue('A1', 'DANH SÁCH ĐĂNG KÝ BỘ MÔN CHẠY');
        $sheet->mergeCells('A1:I1');

        $headers = array('A' => 'STT', 'B' => 'Số BIB', 'C' => 'Họ và tên', 'D' => 'Đơn vị', 'E' => 'Số điện thoại', 'F' => 'Năm sinh', 'G' => 'Giới tính', 'H' => 'Cự ly', 'I' => 'Nội dung');
        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col . '3', $label);
        }

        $row = 4;
        $stt = 1;
        foreach ($rows as $r) {
            $genderLabel = '';
            if (isset($r['gender']) && $r['gender'] !== null && $r['gender'] !== '') {
                $genderLabel = ((int) $r['gender'] === 1) ? 'Nam' : 'Nữ';
            }
            $sheet->setCellValue('A' . $row, $stt++);
            $sheet->setCellValue('B' . $row, isset($r['bib_number']) ? $r['bib_number'] : '');
            $sheet->setCellValue('C' . $row, isset($r['full_name']) ? $r['full_name'] : '');
            $sheet->setCellValue('D' . $row, isset($r['unit_label']) ? $r['unit_label'] : '');
            $sheet->setCellValue('E' . $row, isset($r['phone_number']) ? $r['phone_number'] : '');
            $sheet->setCellValue('F' . $row, !empty($r['birth_year']) ? $r['birth_year'] : '');
            $sheet->setCellValue('G' . $row, $genderLabel);
            $sheet->setCellValue('H' . $row, isset($r['run_event_name']) ? $r['run_event_name'] : '');
            $sheet->setCellValue('I' . $row, !empty($r['age_group_label']) ? $r['age_group_label'] : '');
            $row++;
        }

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'dang_ky_chay_su_kien_' . $eventId . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $writer->save('php://output');
        Yii::app()->end();
    }

    protected function getEventList()
    {
        $list = array();
        try {
            $events = Events::getApiDataProvider(array(), 200)->getData();
            foreach ($events as $e) {
                $list[$e->id] = $e->name;
            }
        } catch (Exception $e) {
            // bỏ qua
        }
        return $list;
    }

    /** Danh sách cự ly Fun Run của sự kiện cho popup gán thủ công. */
    protected function getRunEventList($eventId)
    {
        $list = array();
        try {
            foreach (RunEvents::getApiDataProvider(array('event_id' => $eventId), 1000)->getData() as $ev) {
                $list[] = array(
                    'id'   => (int) $ev->id,
                    'name' => $ev->name,
                    'code' => $ev->code,
                );
            }
        } catch (Exception $e) {
            Yii::log('Không tải được danh sách cự ly Fun Run: ' . $e->getMessage(), CLogger::LEVEL_WARNING);
        }
        return $list;
    }

    /** Trả JSON cho JS, giữ HTTP status thật. */
    protected function renderJson($payload, $status = 200)
    {
        header('Content-Type: application/json; charset=utf-8', true, $status);
        echo CJSON::encode($payload);
        Yii::app()->end();
    }

    protected function createPhpExcel()
    {
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel.Classes');
        spl_autoload_unregister(array('YiiBase', 'autoload'));
        require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        $excel = new PHPExcel();
        spl_autoload_register(array('YiiBase', 'autoload'));
        return $excel;
    }
}
