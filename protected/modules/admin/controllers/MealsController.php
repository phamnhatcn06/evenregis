<?php

/**
 * Quản trị Bữa ăn (cấu hình chấm ăn). Bữa ăn sinh tự động từ agenda
 * (sáng/trưa/tối); BTC chỉ cấu hình khung giờ chấm, địa điểm, bật/tắt.
 * Dữ liệu qua External API (model Meals). Permission key: "meal".
 */
class MealsController extends AdminController
{
    public function actionAdmin()
    {
        $params = array();
        if (isset($_GET['event_id']) && $_GET['event_id'] !== '') {
            $params['event_id'] = (int) $_GET['event_id'];
        }

        $dataProvider = Meals::getApiDataProvider($params);

        $this->render('admin', array(
            'dataProvider' => $dataProvider,
            'eventList'    => $this->getEventList(),
            'eventId'      => isset($params['event_id']) ? $params['event_id'] : '',
        ));
    }

    public function actionCreate()
    {
        $model = new Meals;
        $model->event_id = isset($_GET['event_id']) ? (int) $_GET['event_id'] : null;
        $model->is_active = 1;

        if (isset($_POST['Meals'])) {
            $this->bindModel($model, $_POST['Meals']);
            if ($model->validate()) {
                $result = $model->storeViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Thêm bữa ăn thành công.');
                    $this->redirect(array('admin', 'event_id' => $model->event_id));
                } else {
                    $model->addError('name', $result['error'] ?: 'Không thể thêm bữa ăn.');
                }
            }
        }

        $this->render('create', array(
            'model'     => $model,
            'eventList' => $this->getEventList(),
        ));
    }

    public function actionUpdate($id)
    {
        $model = $this->loadModelById($id);

        if (isset($_POST['Meals'])) {
            $this->bindModel($model, $_POST['Meals']);
            if ($model->validate()) {
                $result = $model->updateViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Cập nhật bữa ăn thành công.');
                    $this->redirect(array('admin', 'event_id' => $model->event_id));
                } else {
                    $model->addError('name', $result['error'] ?: 'Không thể cập nhật bữa ăn.');
                }
            }
        }

        $this->render('update', array(
            'model'     => $model,
            'eventList' => $this->getEventList(),
        ));
    }

    /**
     * Báo cáo chấm ăn: tổng hợp số người ăn theo bữa + danh sách chi tiết
     * (người ăn, đơn vị, trạm chấm, giờ chấm).
     */
    public function actionReport()
    {
        $eventId = isset($_GET['event_id']) && $_GET['event_id'] !== '' ? (int) $_GET['event_id'] : null;
        $mealId  = isset($_GET['meal_id']) && $_GET['meal_id'] !== '' ? (int) $_GET['meal_id'] : null;

        $summary = $eventId ? Meals::fetchReportSummary($eventId) : null;
        $detail  = $eventId ? Meals::fetchReportDetail($eventId, $mealId) : null;

        $this->render('report', array(
            'eventList' => $this->getEventList(),
            'eventId'   => $eventId,
            'mealId'    => $mealId,
            'summary'   => $summary,
            'detail'    => $detail,
        ));
    }

    /** Xuất Excel báo cáo chấm ăn: sheet Tổng hợp + sheet Chi tiết. */
    public function actionExport($event_id, $meal_id = null)
    {
        $eventId = (int) $event_id;
        $mealId  = ($meal_id !== null && $meal_id !== '') ? (int) $meal_id : null;

        $summary = Meals::fetchReportSummary($eventId);
        $detail  = Meals::fetchReportDetail($eventId, $mealId);

        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel.Classes');
        spl_autoload_unregister(array('YiiBase', 'autoload'));
        require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        $objPHPExcel = new PHPExcel();
        spl_autoload_register(array('YiiBase', 'autoload'));

        $objPHPExcel->getProperties()->setCreator('System')->setTitle('Bao cao cham an');

        $headerStyle = array(
            'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF')),
            'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => '3A57E8')),
            'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
            'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),
        );
        $borderStyle = array('borders' => array('allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => 'E9ECEF'))));

        // Sheet 1: Tổng hợp số người ăn theo bữa
        $sheet = $objPHPExcel->setActiveSheetIndex(0);
        $sheet->setTitle('Tong_hop');
        $headers = array('STT', 'Tên bữa ăn', 'Loại', 'Ngày', 'Địa điểm', 'Số người ăn');
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '1', $h);
            $sheet->getStyle($col . '1')->applyFromArray($headerStyle);
            $col++;
        }
        $row = 2;
        $stt = 1;
        $meals = ($summary && isset($summary['meals'])) ? $summary['meals'] : array();
        foreach ($meals as $m) {
            $sheet->setCellValue('A' . $row, $stt++);
            $sheet->setCellValue('B' . $row, isset($m['meal_name']) ? $m['meal_name'] : '');
            $sheet->setCellValue('C' . $row, Meals::getTypeLabel(isset($m['meal_type']) ? $m['meal_type'] : ''));
            $sheet->setCellValue('D' . $row, isset($m['meal_date']) ? $m['meal_date'] : '');
            $sheet->setCellValue('E' . $row, isset($m['location']) ? $m['location'] : '');
            $sheet->setCellValue('F' . $row, isset($m['total']) ? (int) $m['total'] : 0);
            $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray($borderStyle);
            $row++;
        }
        $sheet->setCellValue('E' . $row, 'TỔNG CỘNG');
        $sheet->setCellValue('F' . $row, $summary && isset($summary['grand_total']) ? (int) $summary['grand_total'] : 0);
        $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray($headerStyle);
        foreach (range('A', 'F') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        // Sheet 2: Chi tiết danh sách người chấm
        $sheet2 = $objPHPExcel->createSheet(1);
        $sheet2->setTitle('Chi_tiet');
        $headers2 = array('STT', 'Họ tên', 'Đơn vị', 'Bộ phận', 'Bữa ăn', 'Trạm chấm', 'Giờ chấm', 'Nguồn');
        $col = 'A';
        foreach ($headers2 as $h) {
            $sheet2->setCellValue($col . '1', $h);
            $sheet2->getStyle($col . '1')->applyFromArray($headerStyle);
            $col++;
        }
        $row = 2;
        $stt = 1;
        $rows = ($detail && isset($detail['rows'])) ? $detail['rows'] : array();
        foreach ($rows as $r) {
            $sheet2->setCellValue('A' . $row, $stt++);
            $sheet2->setCellValue('B' . $row, isset($r['full_name']) ? $r['full_name'] : '');
            $sheet2->setCellValue('C' . $row, isset($r['org']) ? $r['org'] : '');
            $sheet2->setCellValue('D' . $row, isset($r['unit']) ? $r['unit'] : '');
            $sheet2->setCellValue('E' . $row, isset($r['meal_name']) ? $r['meal_name'] : '');
            $sheet2->setCellValue('F' . $row, isset($r['checked_by']) ? $r['checked_by'] : '');
            $sheet2->setCellValue('G' . $row, isset($r['check_in_time']) ? $r['check_in_time'] : '');
            $sheet2->setCellValue('H' . $row, self::getSourceLabel(isset($r['source']) ? $r['source'] : ''));
            $sheet2->getStyle('A' . $row . ':H' . $row)->applyFromArray($borderStyle);
            $row++;
        }
        foreach (range('A', 'H') as $c) {
            $sheet2->getColumnDimension($c)->setAutoSize(true);
        }

        $objPHPExcel->setActiveSheetIndex(0);
        $filename = 'Bao_cao_cham_an_' . $eventId . '_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        Yii::app()->end();
    }

    public static function getSourceLabel($source)
    {
        $labels = array(
            'qr_scan'      => 'Quét QR',
            'offline_sync' => 'Đồng bộ offline',
            'manual'       => 'Thủ công',
        );
        return isset($labels[$source]) ? $labels[$source] : $source;
    }

    /** Đồng bộ danh sách bữa ăn từ agenda của sự kiện. */
    public function actionSyncFromAgenda()
    {
        if (!Yii::app()->getRequest()->getIsPostRequest()) {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }
        $eventId = isset($_POST['event_id']) ? (int) $_POST['event_id'] : 0;
        if (!$eventId) {
            Yii::app()->user->setFlash('error', 'Vui lòng chọn sự kiện trước khi đồng bộ.');
            $this->redirect(array('admin'));
        }

        $result = Meals::syncFromAgenda($eventId);
        if ($result['success']) {
            $msg = isset($result['data']['message']) ? $result['data']['message'] : 'Đồng bộ bữa ăn thành công.';
            Yii::app()->user->setFlash('success', $msg);
        } else {
            Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể đồng bộ bữa ăn.');
        }
        $this->redirect(array('admin', 'event_id' => $eventId));
    }

    /** Bật/tắt nhanh một bữa ăn (is_active). */
    public function actionToggle($id)
    {
        if (!Yii::app()->getRequest()->getIsPostRequest()) {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }
        $model = $this->loadModelById($id);
        $model->is_active = $model->is_active ? 0 : 1;
        $result = $model->updateViaApi();
        if ($result['success']) {
            Yii::app()->user->setFlash('success', 'Đã cập nhật trạng thái bữa ăn.');
        } else {
            Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể cập nhật trạng thái.');
        }
        $this->redirect(array('admin', 'event_id' => $model->event_id));
    }

    public function actionDelete($id)
    {
        if (!Yii::app()->getRequest()->getIsPostRequest()) {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }
        $result = Meals::deleteViaApi($id);
        if ($result['success']) {
            Yii::app()->user->setFlash('success', 'Xóa bữa ăn thành công.');
        } else {
            Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể xóa bữa ăn.');
        }
        if (!Yii::app()->getRequest()->getIsAjaxRequest()) {
            $this->redirect(array('admin'));
        }
    }

    protected function loadModelById($id)
    {
        $model = Meals::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy bữa ăn.');
        }
        return $model;
    }

    /** Gán form vào model; chuyển datetime-local -> 'Y-m-d H:i:s'. */
    protected function bindModel(Meals $model, array $post)
    {
        $model->setAttributes($post);
        $model->checkin_start = $this->toDateTime(isset($post['checkin_start']) ? $post['checkin_start'] : null);
        $model->checkin_end   = $this->toDateTime(isset($post['checkin_end']) ? $post['checkin_end'] : null);
        if (isset($post['serving_time'])) {
            $model->serving_time = $this->toDateTime($post['serving_time']);
        }
    }

    protected function toDateTime($value)
    {
        if (empty($value)) {
            return null;
        }
        $ts = strtotime($value);
        return $ts ? date('Y-m-d H:i:s', $ts) : null;
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
}
