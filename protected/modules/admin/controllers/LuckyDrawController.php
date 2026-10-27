<?php

/**
 * Quay số may mắn (Lucky Draw).
 *
 * Hai lớp bảo vệ: (1) quyền `luckydraw` từ Portal SSO, (2) mã truy cập (access code)
 * do lucky-show.js hỏi khi mở màn quay.
 *
 * Các action `api*` là lớp ADAPTER: nhận request từ lucky-show.js, gọi API Laravel qua
 * Model LuckyDraw, rồi reshape về dạng {ok,...} mà core JS mong đợi (KHÔNG sửa core JS).
 * Vì JS poll `apiCheckRemote` mỗi 1 giây nên các action api* KHÔNG gọi validateWithPortal
 * (chỉ kiểm tra session) để tránh nện Portal liên tục.
 */
class LuckyDrawController extends AdminController
{
    /** Màn quay số & remote hiển thị toàn màn hình, không layout. */
    public $layout = false;

    public function init()
    {
        // Chỉ kiểm tra session; KHÔNG validate Portal ở đây (xử lý trong beforeAction cho
        // action trang, bỏ qua cho action api để không gọi Portal mỗi giây khi polling).
        Controller::init();

        if (!AuthHandler::isAuthenticated()) {
            if ($this->isApiRoute()) {
                $this->jsonOut(array('ok' => false, 'msg' => 'Chưa đăng nhập'));
            }
            Yii::app()->user->setFlash('error', 'Vui lòng đăng nhập.');
            $this->redirect(array('/site/login'));
            return;
        }

        $this->user = AuthHandler::getUser();
    }

    public function beforeAction($action)
    {
        if (!Controller::beforeAction($action)) {
            return false;
        }

        $actionId = strtolower($action->id);
        $isApi = strpos($actionId, 'api') === 0;

        // Quyền luckydraw theo thao tác
        $operation = $this->luckyOperation($actionId);
        if (!PermissionHelper::can('luckydraw', $operation)) {
            if ($isApi) {
                $this->jsonOut(array('ok' => false, 'msg' => 'Không có quyền quay số'));
            }
            throw new CHttpException(403, 'Bạn không có quyền truy cập Quay số may mắn.');
        }

        // Chỉ validate Portal cho action trang (mỗi lần tải trang), bỏ qua action api polling.
        if (!$isApi && !AuthHandler::validateWithPortal()) {
            Yii::app()->user->setFlash('error', 'Phiên đăng nhập đã hết hạn.');
            $this->redirect(array('/site/login'));
            return false;
        }

        return true;
    }

    /** Map action → thao tác quyền. */
    protected function luckyOperation($actionId)
    {
        if (strpos($actionId, 'delete') !== false || strpos($actionId, 'clear') !== false) {
            return 'delete';
        }
        // Import danh sách trúng giải dùng chung quyền 'update' với cài đặt (admin lucky đã có).
        if (in_array($actionId, array('saveprize', 'savesettings', 'resetwinners', 'import', 'parseexcel', 'saveimport'), true)) {
            return 'update';
        }
        // index/admin/show/remote/downloadtemplate + toàn bộ api* → read
        return 'read';
    }

    protected function isApiRoute()
    {
        $route = strtolower(Yii::app()->getRequest()->getParam('r', ''));
        // Với URL path-format, suy ra từ pathInfo
        $path = strtolower(Yii::app()->getRequest()->getPathInfo());
        return strpos($route, 'luckydraw/api') !== false || strpos($path, 'luckydraw/api') !== false;
    }

    // ===================== TRANG =====================

    /** Quy ước menu admin → mặc định vào màn Import người trúng giải (không còn quay số). */
    public function actionAdmin()
    {
        $this->actionImport();
    }

    // ===================== IMPORT NGƯỜI TRÚNG GIẢI (THỦ CÔNG) =====================

    /** Cấu trúc cột file Excel import (0-based). Thứ tự phải khớp file mẫu. */
    protected function importColumns()
    {
        return array(
            'stt'        => 0,
            'full_name'  => 1,
            'lucky_code' => 2,
            'unit_label' => 3,
            'prize_tier' => 4,
            'prize_name' => 5,
        );
    }

    /** Màn Import: dropdown sự kiện + form upload + danh sách đã trúng. */
    public function actionImport()
    {
        $this->layout = '//layouts/column2';

        $eventList = Events::getListForDropdown();
        $eventId = (int) Yii::app()->getRequest()->getParam('event_id');
        if (!$eventId && !empty($eventList)) {
            $eventId = (int) array_keys($eventList)[0];
        }

        $winners = $eventId ? LuckyDraw::manualWinnersList($eventId) : array();

        $this->render('import', array(
            'eventList' => $eventList,
            'eventId' => $eventId,
            'winners' => $winners,
            'canDelete' => PermissionHelper::can('luckydraw', 'delete'),
        ));
    }

    /** Đọc file Excel upload, validate, trả JSON các dòng (chưa lưu) để preview. */
    public function actionParseExcel()
    {
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonOut(array('success' => false, 'message' => 'Chưa chọn file hoặc upload lỗi.'));
        }

        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, array('xlsx', 'xls'), true)) {
            $this->jsonOut(array('success' => false, 'message' => 'Chỉ chấp nhận file Excel (.xlsx, .xls).'));
        }

        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel.Classes');
        spl_autoload_unregister(array('YiiBase', 'autoload'));
        require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        try {
            $objPHPExcel = PHPExcel_IOFactory::load($_FILES['file']['tmp_name']);
        } catch (Exception $e) {
            spl_autoload_register(array('YiiBase', 'autoload'));
            $this->jsonOut(array('success' => false, 'message' => 'Không đọc được file Excel: ' . $e->getMessage()));
        }
        spl_autoload_register(array('YiiBase', 'autoload'));

        $sheet = $objPHPExcel->getActiveSheet();
        $highestRow = $sheet->getHighestDataRow();
        $cols = $this->importColumns();

        $rows = array();
        $errors = array();
        // Dòng 1 = tiêu đề → bắt đầu từ dòng 2.
        for ($r = 2; $r <= $highestRow; $r++) {
            $get = function ($key) use ($sheet, $cols, $r) {
                return trim((string) $sheet->getCellByColumnAndRow($cols[$key], $r)->getValue());
            };
            $fullName = $get('full_name');
            $luckyCode = $get('lucky_code');
            $unitLabel = $get('unit_label');
            $prizeTier = $get('prize_tier');
            $prizeName = $get('prize_name');

            // Bỏ qua dòng trống hoàn toàn.
            if ($fullName === '' && $luckyCode === '' && $unitLabel === '' && $prizeTier === '' && $prizeName === '') {
                continue;
            }

            $rowErrors = array();
            if ($fullName === '') {
                $rowErrors[] = 'thiếu Họ và tên';
            }
            if ($luckyCode === '') {
                $rowErrors[] = 'thiếu Mã lucky draw';
            }
            if ($unitLabel === '') {
                $rowErrors[] = 'thiếu Đơn vị';
            }
            if ($prizeTier === '') {
                $rowErrors[] = 'thiếu Hạng giải';
            }
            if (!empty($rowErrors)) {
                $errors[] = 'Dòng ' . $r . ': ' . implode(', ', $rowErrors) . '.';
                continue;
            }

            $rows[] = array(
                'full_name'  => $fullName,
                'lucky_code' => $luckyCode,
                'unit_label' => $unitLabel,
                'prize_tier' => $prizeTier,
                'prize_name' => $prizeName,
            );
        }

        if (empty($rows) && empty($errors)) {
            $this->jsonOut(array('success' => false, 'message' => 'File không có dữ liệu.'));
        }

        $this->jsonOut(array(
            'success' => true,
            'rows' => $rows,
            'errors' => $errors,
            'count' => count($rows),
        ));
    }

    /** Lưu các dòng đã preview vào sự kiện (cộng dồn). */
    public function actionSaveImport()
    {
        $body = $this->jsonBody();
        $eventId = isset($body['event_id']) ? (int) $body['event_id'] : 0;
        $rows = isset($body['rows']) && is_array($body['rows']) ? $body['rows'] : array();

        if (!$eventId) {
            $this->jsonOut(array('success' => false, 'message' => 'Vui lòng chọn sự kiện.'));
        }
        if (empty($rows)) {
            $this->jsonOut(array('success' => false, 'message' => 'Không có dữ liệu hợp lệ để import.'));
        }

        $ssoUser = AuthHandler::getUser();
        $createdBy = isset($ssoUser['email']) ? $ssoUser['email'] : null;

        $result = LuckyDraw::importManualWinners($eventId, $rows, $createdBy);
        if (!empty($result['success'])) {
            $data = isset($result['data']['data']) ? $result['data']['data'] : (isset($result['data']) ? $result['data'] : array());
            $this->jsonOut(array(
                'success' => true,
                'message' => isset($result['data']['message']) ? $result['data']['message'] : 'Import thành công.',
                'warnings' => isset($data['warnings']) ? $data['warnings'] : array(),
                'inserted' => isset($data['inserted']) ? $data['inserted'] : 0,
            ));
        }
        $this->jsonOut(array('success' => false, 'message' => LuckyDraw::errorMessage($result, 'Import thất bại')));
    }

    /** Xoá 1 người trúng giải. */
    public function actionDeleteWinner($id)
    {
        $result = LuckyDraw::deleteManualWinner($id);
        if (!empty($result['success'])) {
            $this->jsonOut(array('success' => true, 'message' => 'Đã xoá.'));
        }
        $this->jsonOut(array('success' => false, 'message' => LuckyDraw::errorMessage($result, 'Không xoá được')));
    }

    /** Xoá toàn bộ danh sách của 1 sự kiện. */
    public function actionClearWinners()
    {
        $body = $this->jsonBody();
        $eventId = isset($body['event_id']) ? (int) $body['event_id'] : 0;
        if (!$eventId) {
            $this->jsonOut(array('success' => false, 'message' => 'Thiếu sự kiện.'));
        }
        $result = LuckyDraw::clearManualWinners($eventId);
        if (!empty($result['success'])) {
            $this->jsonOut(array('success' => true, 'message' => 'Đã xoá toàn bộ danh sách của sự kiện.'));
        }
        $this->jsonOut(array('success' => false, 'message' => LuckyDraw::errorMessage($result, 'Không xoá được')));
    }

    /** Tải file Excel mẫu import người trúng giải. */
    public function actionDownloadTemplate()
    {
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel.Classes');
        spl_autoload_unregister(array('YiiBase', 'autoload'));
        require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        $objPHPExcel = new PHPExcel();
        spl_autoload_register(array('YiiBase', 'autoload'));

        $objPHPExcel->getProperties()->setCreator('System')->setTitle('Mau import nguoi trung giai');

        $sheet = $objPHPExcel->setActiveSheetIndex(0);
        $sheet->setTitle('Người trúng giải');

        $headers = array('STT', 'Họ và tên (*)', 'Mã lucky draw (*)', 'Đơn vị (*)', 'Hạng giải (*)', 'Tên giải thưởng');
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '1', $h);
            $col++;
        }
        $sheet->getStyle('A1:F1')->applyFromArray(array(
            'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF')),
            'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => '3A57E8')),
            'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER),
        ));

        $samples = array(
            array(1, 'Nguyễn Văn A', 'LD001', 'Khách sạn Mường Thanh Hà Nội', 'Giải Nhất', 'Xe máy Honda'),
            array(2, 'Trần Thị B', 'LD045', 'Mường Thanh Nha Trang', 'Giải Nhì', 'Tivi 55 inch'),
        );
        $r = 2;
        foreach ($samples as $s) {
            $sheet->setCellValueByColumnAndRow(0, $r, $s[0]);
            $sheet->setCellValueExplicitByColumnAndRow(1, $r, $s[1], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow(2, $r, $s[2], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow(3, $r, $s[3], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow(4, $r, $s[4], PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicitByColumnAndRow(5, $r, $s[5], PHPExcel_Cell_DataType::TYPE_STRING);
            $r++;
        }

        foreach (range('A', 'F') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $filename = 'mau_import_nguoi_trung_giai.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        Yii::app()->end();
    }

    /** Màn hình cài đặt cho admin (giải thưởng + cấu hình). */
    public function actionIndex()
    {
        $this->layout = '//layouts/column2';

        $prizes = LuckyDraw::prizes();
        $cfg = LuckyDraw::settings();

        $this->render('index', array(
            'prizes' => $prizes,
            'settings' => isset($cfg['settings']) ? $cfg['settings'] : array(),
            'types' => isset($cfg['types']) ? $cfg['types'] : array(),
            'eventList' => Events::getListForDropdown(),
            'canUpdate' => PermissionHelper::can('luckydraw', 'update'),
            'canDelete' => PermissionHelper::can('luckydraw', 'delete'),
        ));
    }

    /** Màn quay số toàn màn hình. */
    public function actionShow()
    {
        $this->layout = false;

        $prize = LuckyDraw::currentPrize();
        $winnerList = array();
        if ($prize && isset($prize['id'])) {
            $winnerList = LuckyDraw::confirmedWinners($prize['id']);
        }
        $cfg = LuckyDraw::settings();
        $settings = isset($cfg['settings']) ? $cfg['settings'] : array();

        $this->render('show', array(
            'prize' => $prize,
            'winnerList' => $winnerList,
            'backgroundPath' => isset($settings['background_path']) ? $settings['background_path'] : '',
            'eventTitle' => isset($settings['event_title']) ? $settings['event_title'] : 'Quay số may mắn',
        ));
    }

    /** Bảng điều khiển từ xa (remote) toàn màn hình. */
    public function actionRemote()
    {
        $this->layout = false;
        $this->render('remote', array(
            'prize' => LuckyDraw::currentPrize(),
        ));
    }

    // ===================== SETTINGS (POST) =====================

    public function actionSavePrize()
    {
        $input = $_POST;
        unset($input['YII_CSRF_TOKEN']);
        $result = LuckyDraw::savePrize($input);
        if (!empty($result['success'])) {
            $this->jsonOut(array('success' => true, 'message' => 'Lưu giải thành công', 'data' => LuckyDraw::prizes()));
        }
        $this->jsonOut(array('success' => false, 'message' => LuckyDraw::errorMessage($result, 'Không lưu được giải')));
    }

    public function actionDeletePrize($id)
    {
        $result = LuckyDraw::deletePrize($id);
        if (!empty($result['success'])) {
            $this->jsonOut(array('success' => true, 'message' => 'Đã xoá giải'));
        }
        $this->jsonOut(array('success' => false, 'message' => LuckyDraw::errorMessage($result, 'Không xoá được giải')));
    }

    public function actionSaveSettings()
    {
        $input = $_POST;
        unset($input['YII_CSRF_TOKEN']);
        // exclude_columns gửi dạng JSON string từ form → decode thành mảng
        if (isset($input['exclude_columns']) && is_string($input['exclude_columns'])) {
            $decoded = json_decode($input['exclude_columns'], true);
            $input['exclude_columns'] = is_array($decoded) ? $decoded : array();
        }
        $result = LuckyDraw::saveSettings($input);
        if (!empty($result['success'])) {
            $this->jsonOut(array('success' => true, 'message' => 'Lưu cấu hình thành công'));
        }
        $this->jsonOut(array('success' => false, 'message' => LuckyDraw::errorMessage($result, 'Không lưu được cấu hình')));
    }

    public function actionResetWinners()
    {
        $result = LuckyDraw::resetWinners();
        if (!empty($result['success'])) {
            $this->jsonOut(array('success' => true, 'message' => 'Đã reset danh sách trúng giải'));
        }
        $this->jsonOut(array('success' => false, 'message' => LuckyDraw::errorMessage($result)));
    }

    // ===================== API ADAPTER (cho lucky-show.js) =====================

    public function actionApiPrize()
    {
        $p = LuckyDraw::currentPrize();
        $this->jsonOut(array('ok' => true, 'data' => $p ?: null));
    }

    public function actionApiStatus()
    {
        $s = LuckyDraw::status();
        $remaining = (is_array($s) && isset($s['remaining'])) ? (int) $s['remaining'] : 0;
        $this->jsonOut(array('ok' => true, 'data' => array('remaining' => $remaining)));
    }

    public function actionApiWinners()
    {
        $prizeId = (int) Yii::app()->getRequest()->getParam('prize_id');
        $this->jsonOut(array('ok' => true, 'data' => LuckyDraw::confirmedWinners($prizeId)));
    }

    public function actionApiSpin()
    {
        $result = LuckyDraw::spin();
        if (!empty($result['success'])) {
            $payload = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
            $this->jsonOut(array('ok' => true, 'data' => $payload));
        }
        $this->jsonOut(array('ok' => false, 'error' => LuckyDraw::errorMessage($result, 'Quay thất bại')));
    }

    public function actionApiConfirmWinner()
    {
        $body = $this->jsonBody();
        $id = isset($body['id']) ? $body['id'] : null;
        $prizeId = isset($body['prize_id']) ? (int) $body['prize_id'] : 0;
        LuckyDraw::confirmWinner($id, $prizeId);
        $this->jsonOut(array('ok' => true));
    }

    public function actionApiCancelWinner()
    {
        $body = $this->jsonBody();
        $id = isset($body['id']) ? $body['id'] : null;
        $prizeId = isset($body['prize_id']) ? (int) $body['prize_id'] : 0;
        if ($id === null || $id === 'undefined') {
            $this->jsonOut(array('ok' => false, 'msg' => 'Missing params'));
        }
        $result = LuckyDraw::cancelWinner($id, $prizeId);
        if (!empty($result['success'])) {
            $this->jsonOut(array('ok' => true));
        }
        $this->jsonOut(array('ok' => false, 'error' => LuckyDraw::errorMessage($result)));
    }

    public function actionApiNextPrize()
    {
        $result = LuckyDraw::nextPrize();
        if (!empty($result['success'])) {
            $this->jsonOut(array('ok' => true));
        }
        $this->jsonOut(array('ok' => false, 'msg' => LuckyDraw::errorMessage($result, 'Đã hết toàn bộ giải')));
    }

    public function actionApiCheckAuth()
    {
        $body = $this->jsonBody();
        $code = isset($body['code']) ? $body['code'] : '';
        $result = LuckyDraw::checkAuth($code);
        if (!empty($result['success'])) {
            $this->jsonOut(array('ok' => true));
        }
        $this->jsonOut(array('ok' => false, 'msg' => LuckyDraw::errorMessage($result, 'Mã truy cập không đúng!')));
    }

    public function actionApiCheckRemote()
    {
        $r = LuckyDraw::checkRemote();
        $command = (is_array($r) && isset($r['command'])) ? $r['command'] : null;
        $this->jsonOut(array('ok' => true, 'command' => $command));
    }

    public function actionApiRemoteSpin()
    {
        $result = LuckyDraw::remoteSpin();
        if (!empty($result['success'])) {
            $this->jsonOut(array('ok' => true));
        }
        $this->jsonOut(array('ok' => false, 'msg' => LuckyDraw::errorMessage($result)));
    }

    // ===================== Helpers =====================

    protected function jsonBody()
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : array();
    }

    protected function jsonOut($arr)
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/json; charset=utf-8');
        echo CJSON::encode($arr);
        Yii::app()->end();
    }
}
