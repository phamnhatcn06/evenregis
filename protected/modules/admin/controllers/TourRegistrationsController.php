<?php

/**
 * Quản trị đăng ký đi tham quan: danh sách theo sự kiện, xuất Excel, duyệt yêu cầu hủy.
 * Permission key: "tourregistrations".
 */
class TourRegistrationsController extends AdminController
{
    public function actionAdmin()
    {
        $eventId = isset($_GET['event_id']) && $_GET['event_id'] !== '' ? (int) $_GET['event_id'] : null;

        $registrations = $eventId ? TourRegistrations::listByEvent($eventId) : array();

        $this->render('admin', array(
            'eventId'       => $eventId,
            'eventList'     => $this->getEventList(),
            'registrations' => $registrations,
        ));
    }

    /** Danh sách yêu cầu hủy chờ duyệt theo sự kiện. */
    public function actionCancelRequests()
    {
        $eventId = isset($_GET['event_id']) && $_GET['event_id'] !== '' ? (int) $_GET['event_id'] : null;

        $requests = $eventId ? TourRegistrations::listCancelRequests($eventId) : array();

        $this->render('cancelRequests', array(
            'eventId'   => $eventId,
            'eventList' => $this->getEventList(),
            'requests'  => $requests,
        ));
    }

    public function actionApproveCancel($id)
    {
        if (!Yii::app()->request->isPostRequest) {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }
        if (!PermissionHelper::can('tourregistrations', 'update')) {
            throw new CHttpException(403, 'Bạn không có quyền thực hiện thao tác này.');
        }
        $res = TourRegistrations::approveCancelViaApi((int) $id);
        if ($res['success']) {
            Yii::app()->user->setFlash('success', isset($res['data']['message']) ? $res['data']['message'] : 'Đã duyệt hủy và hoàn suất.');
        } else {
            Yii::app()->user->setFlash('error', $res['error'] ?: 'Không thể duyệt hủy.');
        }
        $this->redirect(array('cancelRequests', 'event_id' => Yii::app()->request->getParam('event_id')));
    }

    public function actionRejectCancel($id)
    {
        if (!Yii::app()->request->isPostRequest) {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }
        if (!PermissionHelper::can('tourregistrations', 'update')) {
            throw new CHttpException(403, 'Bạn không có quyền thực hiện thao tác này.');
        }
        $res = TourRegistrations::rejectCancelViaApi((int) $id);
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
        $rows = TourRegistrations::listByEvent($eventId);

        $excel = $this->createPhpExcel();
        $sheet = $excel->getActiveSheet();
        $sheet->setTitle('Dang ky tham quan');

        $sheet->setCellValue('A1', 'DANH SÁCH ĐĂNG KÝ ĐI THAM QUAN');
        $sheet->mergeCells('A1:F1');

        $headers = array('A' => 'STT', 'B' => 'Đợt', 'C' => 'Khung giờ', 'D' => 'Họ và tên', 'E' => 'Đơn vị', 'F' => 'Số điện thoại');
        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col . '3', $label);
        }

        $row = 4;
        $stt = 1;
        foreach ($rows as $r) {
            $sheet->setCellValue('A' . $row, $stt++);
            $sheet->setCellValue('B' . $row, isset($r['tour_session_name']) ? $r['tour_session_name'] : '');
            $sheet->setCellValue('C' . $row, isset($r['start_time']) ? $r['start_time'] : '');
            $sheet->setCellValue('D' . $row, isset($r['full_name']) ? $r['full_name'] : '');
            $sheet->setCellValue('E' . $row, isset($r['unit_label']) ? $r['unit_label'] : '');
            $sheet->setCellValue('F' . $row, isset($r['phone_number']) ? $r['phone_number'] : '');
            $row++;
        }

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'dang_ky_tham_quan_su_kien_' . $eventId . '.xlsx';
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
