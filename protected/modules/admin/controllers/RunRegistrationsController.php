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

        $registrations = $eventId ? RunRegistrations::listByEvent($eventId) : array();

        $this->render('admin', array(
            'eventId'       => $eventId,
            'eventList'     => $this->getEventList(),
            'registrations' => $registrations,
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

    public function actionExport($event_id)
    {
        $eventId = (int) $event_id;
        $rows = RunRegistrations::listByEvent($eventId);

        $excel = $this->createPhpExcel();
        $sheet = $excel->getActiveSheet();
        $sheet->setTitle('Dang ky chay');

        $sheet->setCellValue('A1', 'DANH SÁCH ĐĂNG KÝ BỘ MÔN CHẠY');
        $sheet->mergeCells('A1:F1');

        $headers = array('A' => 'STT', 'B' => 'Số BIB', 'C' => 'Họ và tên', 'D' => 'Đơn vị', 'E' => 'Số điện thoại', 'F' => 'Nội dung');
        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col . '3', $label);
        }

        $row = 4;
        $stt = 1;
        foreach ($rows as $r) {
            $sheet->setCellValue('A' . $row, $stt++);
            $sheet->setCellValue('B' . $row, isset($r['bib_number']) ? $r['bib_number'] : '');
            $sheet->setCellValue('C' . $row, isset($r['full_name']) ? $r['full_name'] : '');
            $sheet->setCellValue('D' . $row, isset($r['unit_label']) ? $r['unit_label'] : '');
            $sheet->setCellValue('E' . $row, isset($r['phone_number']) ? $r['phone_number'] : '');
            $sheet->setCellValue('F' . $row, isset($r['run_event_name']) ? $r['run_event_name'] : '');
            $row++;
        }

        foreach (range('A', 'F') as $col) {
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
