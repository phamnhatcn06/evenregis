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

        // Đếm tổng số người đăng ký theo từng nội dung (cự ly) từ TOÀN BỘ dữ liệu.
        $countByContent = array();
        foreach ($allRegistrations as $r) {
            $name = !empty($r['run_event_name']) ? $r['run_event_name'] : 'Khác';
            if (!isset($countByContent[$name])) {
                $countByContent[$name] = 0;
            }
            $countByContent[$name]++;
        }
        ksort($countByContent);

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

        // Sắp xếp theo số BIB tăng dần (so sánh phần số; BIB cùng cự ly có chung tiền tố km).
        usort($registrations, function ($a, $b) {
            $na = isset($a['bib_number']) ? (int) preg_replace('/\D/', '', (string) $a['bib_number']) : 0;
            $nb = isset($b['bib_number']) ? (int) preg_replace('/\D/', '', (string) $b['bib_number']) : 0;
            if ($na === $nb) {
                return 0;
            }
            return ($na < $nb) ? -1 : 1;
        });

        $this->render('admin', array(
            'eventId'            => $eventId,
            'eventList'          => $this->getEventList(),
            'registrations'      => $registrations,
            'totalCount'         => count($allRegistrations),
            'cancelRequestCount' => $cancelRequestCount,
            'filters'            => $filters,
            'cuLyOptions'        => $cuLyOptions,
            'unitOptions'        => $unitOptions,
            'countByContent'     => $countByContent,
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
                // Năm sinh: ưu tiên cột birth_year; nếu trống thì suy từ ngày sinh gốc (nếu có).
                $birthYear = '';
                if (!empty($row['birth_year'])) {
                    $birthYear = (int) $row['birth_year'];
                } elseif (!empty($row['birthday'])) {
                    $ts = strtotime((string) $row['birthday']);
                    if ($ts !== false) {
                        $y = (int) date('Y', $ts);
                        if ($y >= 1950 && $y <= (int) date('Y')) {
                            $birthYear = $y;
                        }
                    }
                }

                $results[] = array(
                    'attendee_id'  => $attendeeId,
                    'full_name'    => isset($row['full_name']) ? $row['full_name'] : '',
                    'unit'         => $unit,
                    'lucky_number' => isset($row['lucky_number']) ? $row['lucky_number'] : '',
                    'birth_year'   => $birthYear,
                    'gender'       => (isset($row['gender']) && $row['gender'] !== null && $row['gender'] !== '') ? (string) ((int) $row['gender']) : '',
                );
            }
        }

        $this->renderJson(array('success' => true, 'data' => $results));
    }

    /**
     * Kiểm tra đăng ký chạy hiện có của một người (GET, JSON) — để cảnh báo tránh cấp lần 2.
     * Trả về bib_number + tên cự ly nếu người này đã có đăng ký đang hiệu lực.
     */
    public function actionCheckRegistration()
    {
        $attendeeId = (int) Yii::app()->request->getParam('attendee_id');
        if (!$attendeeId) {
            $this->renderJson(array('success' => false, 'message' => 'Thiếu attendee_id.'), 422);
            return;
        }

        $reg = RunRegistrations::findMine($attendeeId);
        if (!empty($reg) && !empty($reg['bib_number'])) {
            $this->renderJson(array(
                'success'     => true,
                'registered'  => true,
                'bib_number'  => $reg['bib_number'],
                'run_event_name' => isset($reg['run_event_name']) ? $reg['run_event_name'] : '',
            ));
            return;
        }

        $this->renderJson(array('success' => true, 'registered' => false));
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
        $isGuest    = (int) $request->getPost('is_guest') === 1;
        $attendeeId = (int) $request->getPost('attendee_id');
        $fullName   = trim((string) $request->getPost('full_name'));
        $company    = trim((string) $request->getPost('company'));
        $runEventId = (int) $request->getPost('run_event_id');
        $bibNumber  = trim((string) $request->getPost('bib_number'));
        $birthYear  = $request->getPost('birth_year');
        $gender     = $request->getPost('gender');

        $birthYear = ($birthYear !== null && $birthYear !== '') ? (int) $birthYear : null;
        $gender    = ($gender !== null && $gender !== '') ? (int) $gender : null;

        // Khách ngoài: không gắn attendee, chỉ cần họ tên. Finalist: bắt buộc attendee_id.
        if ($isGuest) {
            if ($fullName === '') {
                $this->renderJson(array('success' => false, 'message' => 'Vui lòng nhập họ tên khách.'), 422);
                return;
            }
        } elseif (!$attendeeId) {
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
        // Mã BIB tùy chọn: để trống thì backend tự cấp theo quy luật.

        $ssoUser   = AuthHandler::getUser();
        $authEmail = isset($ssoUser['email']) ? $ssoUser['email'] : null;

        // Finalist: lưu năm sinh + giới tính vào attendee trước (để suy nhãn nội dung chi tiết).
        // Khách ngoài không có attendee nên bỏ qua; năm sinh/giới tính gửi thẳng khi gán.
        if (!$isGuest) {
            $profile = RunAuth::saveProfile($attendeeId, $birthYear, $gender, $authEmail);
            if (!$profile['success']) {
                $status = isset($profile['code']) && (int) $profile['code'] >= 400 ? (int) $profile['code'] : 500;
                $this->renderJson(array(
                    'success' => false,
                    'message' => $profile['error'] ?: 'Không lưu được năm sinh/giới tính.',
                ), $status);
                return;
            }
        }

        $res = RunRegistrations::adminManualAssignViaApi(
            $runEventId,
            $isGuest ? null : $attendeeId,
            $bibNumber,
            $birthYear,
            $gender,
            $authEmail,
            $isGuest,
            $isGuest ? $fullName : null,
            $isGuest ? $company : null
        );
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
     * Admin đổi nội dung (cự ly) cho một đăng ký đã có (POST, JSON). Thu hồi BIB cũ, cấp lại
     * ở cự ly mới (để trống BIB thì tự cấp). Lưu năm sinh/giới tính trước.
     */
    public function actionChangeContent()
    {
        if (!Yii::app()->request->isPostRequest) {
            $this->renderJson(array('success' => false, 'message' => 'Yêu cầu không hợp lệ.'), 400);
            return;
        }
        if (!PermissionHelper::can('runregistrations', 'update')) {
            $this->renderJson(array('success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này.'), 403);
            return;
        }

        $request        = Yii::app()->request;
        $registrationId = (int) $request->getPost('registration_id');
        $runEventId     = (int) $request->getPost('run_event_id');
        $bibNumber      = trim((string) $request->getPost('bib_number'));
        $birthYear      = $request->getPost('birth_year');
        $gender         = $request->getPost('gender');

        $birthYear = ($birthYear !== null && $birthYear !== '') ? (int) $birthYear : null;
        $gender    = ($gender !== null && $gender !== '') ? (int) $gender : null;

        if (!$registrationId) {
            $this->renderJson(array('success' => false, 'message' => 'Thiếu đăng ký cần đổi.'), 422);
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

        $ssoUser   = AuthHandler::getUser();
        $authEmail = isset($ssoUser['email']) ? $ssoUser['email'] : null;

        $res = RunRegistrations::adminChangeContentViaApi($registrationId, $runEventId, $bibNumber, $birthYear, $gender, $authEmail);
        if (!$res['success']) {
            $status = isset($res['code']) && (int) $res['code'] >= 400 ? (int) $res['code'] : 500;
            $this->renderJson(array('success' => false, 'message' => $res['error'] ?: 'Không đổi được nội dung chạy.'), $status);
            return;
        }

        $data = isset($res['data']['data']) ? $res['data']['data'] : array();
        $bib  = isset($data['bib_number']) ? $data['bib_number'] : $bibNumber;
        $this->renderJson(array(
            'success' => true,
            'message' => 'Đã đổi nội dung chạy (BIB mới ' . $bib . ').',
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
            if (!empty($r['is_guest'])) {
                $unitCell = !empty($r['company']) ? ($r['company'] . ' (Khách ngoài)') : 'Khách ngoài';
            } else {
                $unitCell = !empty($r['badge_org_name']) ? $r['badge_org_name'] : (isset($r['unit_label']) ? $r['unit_label'] : '');
            }
            $sheet->setCellValue('D' . $row, $unitCell);
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

    /** Thư mục chứa phôi BIB chạy (PNG) theo quy ước tên file. */
    const BIB_TEMPLATE_DIR = '/themes/hope-ui/phoianh/bibrun/';

    /**
     * Xuất thẻ BIB (ảnh PNG) cho thí sinh chạy.
     * - reg_id: xuất 1 người → tải thẳng PNG.
     * - cu_ly: xuất toàn bộ một nội dung → ZIP.
     * - không có cu_ly: xuất tất cả, chia thư mục theo nội dung → ZIP.
     */
    public function actionExportBib($event_id)
    {
        if (!PermissionHelper::can('runregistrations', 'read')) {
            throw new CHttpException(403, 'Bạn không có quyền xuất thẻ BIB.');
        }

        $eventId = (int) $event_id;
        if (!$eventId) {
            throw new CHttpException(422, 'Vui lòng chọn sự kiện.');
        }

        // Cần extension GD để tạo ảnh. Thiếu GD thì các hàm image* undefined → tiến trình chết
        // không kịp ghi log (biểu hiện 503/trắng trang), nên chặn sớm với thông báo rõ ràng.
        if (!function_exists('imagecreatefrompng') || !function_exists('imagettftext')) {
            throw new CHttpException(500, 'Máy chủ chưa bật extension GD (php-gd) để tạo ảnh BIB. Vui lòng bật php-gd (kèm hỗ trợ FreeType) rồi thử lại.');
        }

        // Dựng ảnh BIB hàng loạt (ảnh 2481×1754) tốn CPU/RAM; nới giới hạn để tránh 503/timeout.
        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');
        @ini_set('display_errors', '0'); // Không in warning ra output kẻo lẫn vào ZIP → hỏng file.
        if (function_exists('ignore_user_abort')) {
            ignore_user_abort(true);
        }

        $templateDir = Yii::app()->basePath . '/..' . self::BIB_TEMPLATE_DIR;
        if (!is_dir($templateDir) || !is_file($templateDir . 'blank_bib.png')) {
            throw new CHttpException(500, 'Không tìm thấy thư mục phôi BIB trên máy chủ (themes/hope-ui/phoianh/bibrun). Kiểm tra đã deploy phôi chưa.');
        }

        $cuLy = trim((string) Yii::app()->request->getParam('cu_ly'));

        $rows = RunRegistrations::listByEvent($eventId);
        if ($cuLy !== '') {
            $rows = array_values(array_filter($rows, function ($r) use ($cuLy) {
                return isset($r['run_event_name']) && $r['run_event_name'] === $cuLy;
            }));
        }

        if (empty($rows)) {
            throw new CHttpException(404, 'Không có thí sinh phù hợp để xuất thẻ BIB.');
        }

        $workDir = Yii::app()->basePath . '/../uploads/run_bibs';

        // Dựng ảnh BIB, gom theo nội dung (cự ly) để chia thư mục trong ZIP.
        $groups  = array();
        $created = array();
        foreach ($rows as $r) {
            $contentName = !empty($r['run_event_name']) ? $r['run_event_name'] : 'Khac';
            $outDir      = $workDir . '/' . MyHelper::toSlug($contentName);

            $path = MyHelper::RunBibCard($this->bibCardData($r), $templateDir, $outDir);
            if (!$path || !is_file($path)) {
                continue;
            }
            $created[]              = $path;
            $groups[$contentName][] = $path;
        }

        if (empty($created)) {
            throw new CHttpException(500, 'Không tạo được thẻ BIB nào. Kiểm tra phôi và dữ liệu đăng ký.');
        }

        if (!class_exists('ZipArchive')) {
            throw new CHttpException(500, 'Máy chủ chưa bật extension Zip (php-zip).');
        }

        $stamp   = date('Ymd_His') . '_' . substr(md5(uniqid('', true)), 0, 6);
        $zipPath = $workDir . '/_bib_' . $eventId . '_' . $stamp . '.zip';
        $zip     = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            foreach ($created as $f) {
                @unlink($f);
            }
            throw new CHttpException(500, 'Không mở được file ZIP để ghi.');
        }
        foreach ($groups as $contentName => $files) {
            $folder = MyHelper::toSlug($contentName);
            foreach ($files as $file) {
                $zip->addFile($file, $folder . '/' . basename($file));
            }
        }
        $zip->close();

        foreach ($created as $f) {
            @unlink($f);
        }

        $suffix       = $cuLy !== '' ? '_' . MyHelper::toSlug($cuLy) : '';
        $downloadName = 'The_BIB_' . $eventId . $suffix . '_' . date('Ymd_His') . '.zip';
        $this->sendFileThenDelete($zipPath, $downloadName, 'application/zip');
    }

    /**
     * Xuất thẻ BIB của ĐÚNG MỘT người (tải thẳng PNG, không bao giờ đóng ZIP).
     * Tách riêng khỏi actionExportBib để nút "xuất 1 thẻ" không thể rơi vào nhánh xuất hàng loạt.
     */
    public function actionExportBibOne($event_id, $id)
    {
        if (!PermissionHelper::can('runregistrations', 'read')) {
            throw new CHttpException(403, 'Bạn không có quyền xuất thẻ BIB.');
        }
        $eventId = (int) $event_id;
        $regId   = (int) $id;
        if (!$eventId || !$regId) {
            throw new CHttpException(422, 'Thiếu sự kiện hoặc người cần xuất.');
        }

        if (!function_exists('imagecreatefrompng') || !function_exists('imagettftext')) {
            throw new CHttpException(500, 'Máy chủ chưa bật extension GD (php-gd) để tạo ảnh BIB.');
        }
        @set_time_limit(120);
        @ini_set('memory_limit', '512M');

        $templateDir = Yii::app()->basePath . '/..' . self::BIB_TEMPLATE_DIR;
        if (!is_dir($templateDir) || !is_file($templateDir . 'blank_bib.png')) {
            throw new CHttpException(500, 'Không tìm thấy thư mục phôi BIB trên máy chủ.');
        }

        $target = null;
        foreach (RunRegistrations::listByEvent($eventId) as $r) {
            if ((int) (isset($r['id']) ? $r['id'] : 0) === $regId) {
                $target = $r;
                break;
            }
        }
        if ($target === null) {
            throw new CHttpException(404, 'Không tìm thấy người cần xuất thẻ BIB.');
        }

        $outDir = Yii::app()->basePath . '/../uploads/run_bibs/'
            . MyHelper::toSlug(!empty($target['run_event_name']) ? $target['run_event_name'] : 'Khac');
        $path = MyHelper::RunBibCard($this->bibCardData($target), $templateDir, $outDir);
        if (!$path || !is_file($path)) {
            throw new CHttpException(500, 'Không tạo được thẻ BIB. Kiểm tra phôi và dữ liệu đăng ký.');
        }

        $this->sendFileThenDelete($path, basename($path), 'image/png');
    }

    /** Chuẩn hoá một dòng đăng ký thành dữ liệu dựng thẻ BIB. */
    protected function bibCardData($r)
    {
        return array(
            'bib_number'      => isset($r['bib_number']) ? $r['bib_number'] : '',
            'full_name'       => isset($r['full_name']) ? $r['full_name'] : '',
            'position'        => $this->resolveBibUnit($r),
            'run_event_name'  => isset($r['run_event_name']) ? $r['run_event_name'] : '',
            'age_group_label' => isset($r['age_group_label']) ? $r['age_group_label'] : '',
            'gender'          => isset($r['gender']) ? $r['gender'] : null,
            'birth_year'      => isset($r['birth_year']) ? $r['birth_year'] : null,
        );
    }

    /**
     * Giá trị "đơn vị" in lên BIB và nhúng QR, lấy từ badge_org_name (tên tổ chức in trên thẻ).
     * Thiếu thì lùi về unit_label, rồi company (khách ngoài).
     */
    protected function resolveBibUnit($row)
    {
        foreach (array('badge_org_name', 'unit_label', 'company') as $key) {
            if (!empty($row[$key])) {
                return (string) $row[$key];
            }
        }
        return '';
    }

    /** Gửi file cho trình duyệt rồi xoá file tạm. */
    protected function sendFileThenDelete($path, $downloadName, $contentType)
    {
        // Xả sạch mọi output buffer để warning/notice (nếu có khi dựng ảnh) không lẫn vào
        // dữ liệu nhị phân làm hỏng ZIP/PNG. Tắt nén output để Content-Length khớp số byte thật.
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', '1');
        }
        @ini_set('zlib.output_compression', '0');
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('Content-Length: ' . filesize($path));
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: max-age=0');
        readfile($path);
        @unlink($path);
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
