<?php

/**
 * MissVotesController - Quản trị kết quả bình chọn Miss.
 * Hiển thị bảng xếp hạng số phiếu + cấu hình khung giờ mở/đóng cổng + export Excel.
 */
class MissVotesController extends AdminController
{
    public function actionAdmin()
    {
        // Menu sidebar trỏ tới action 'admin' theo quy ước; chuyển về index.
        $this->redirect(array('index', 'contest_id' => Yii::app()->request->getParam('contest_id')));
    }

    public function actionIndex()
    {
        $contests = BeautyContests::getListForDropdown();
        $contestId = $this->resolveContestId($contests);

        $ranking = array('total_votes' => 0, 'voter_count' => 0, 'ranking' => array());
        $settings = array();
        if ($contestId) {
            $ranking = BeautyVotes::getRanking($contestId);
            $settings = BeautyVotes::getSettings($contestId);
        }

        $this->render('index', array(
            'contests' => $contests,
            'contestId' => $contestId,
            'ranking' => $ranking,
            'settings' => $settings,
        ));
    }

    public function actionSaveWindow()
    {
        if (!Yii::app()->request->isPostRequest) {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }

        $contestId = (int) Yii::app()->request->getPost('contest_id');
        $startAt = trim(Yii::app()->request->getPost('vote_start_at', ''));
        $endAt = trim(Yii::app()->request->getPost('vote_end_at', ''));

        $result = BeautyVotes::updateSettings($contestId, $startAt, $endAt);
        if (!empty($result['success'])) {
            Yii::app()->user->setFlash('success', 'Đã lưu khung giờ bình chọn.');
        } else {
            $msg = isset($result['error']) ? $result['error'] : 'Không thể lưu cấu hình.';
            Yii::app()->user->setFlash('error', $msg);
        }
        $this->redirect(array('index', 'contest_id' => $contestId));
    }

    public function actionResetVotes()
    {
        if (!Yii::app()->request->isPostRequest) {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }

        $contestId = (int) Yii::app()->request->getPost('contest_id');
        $result = BeautyVotes::resetVotes($contestId);
        if (!empty($result['success'])) {
            $message = isset($result['data']['message']) ? $result['data']['message'] : 'Đã reset toàn bộ phiếu bình chọn.';
            Yii::app()->user->setFlash('success', $message);
        } else {
            $msg = isset($result['error']) && $result['error'] ? $result['error'] : 'Không thể reset bình chọn.';
            Yii::app()->user->setFlash('error', $msg);
        }
        $this->redirect(array('index', 'contest_id' => $contestId));
    }

    public function actionExportExcel($contest_id)
    {
        @ini_set('memory_limit', '512M');
        if (function_exists('set_time_limit')) {
            @set_time_limit(300);
        }

        // [DEBUG TẠM] Ghi tiến độ ra file để xác định action chết ở bước nào.
        $dbg = Yii::getPathOfAlias('application.runtime') . DIRECTORY_SEPARATOR . 'missvotes_debug.log';
        $mark = function ($step) use ($dbg) {
            @file_put_contents($dbg, date('H:i:s') . " | " . $step . " | mem=" . round(memory_get_usage(true) / 1048576) . "MB\n", FILE_APPEND);
        };
        register_shutdown_function(function () use ($dbg) {
            $e = error_get_last();
            if ($e !== null) {
                @file_put_contents($dbg, date('H:i:s') . " | SHUTDOWN error: [" . $e['type'] . "] " . $e['message'] . " @ " . $e['file'] . ":" . $e['line'] . "\n", FILE_APPEND);
            }
        });
        $mark('START contest_id=' . $contest_id);

        $contestId = (int) $contest_id;
        $data = BeautyVotes::getRanking($contestId);
        $mark('AFTER getRanking ranking=' . (isset($data['ranking']) ? count($data['ranking']) : -1));
        $ranking = isset($data['ranking']) ? $data['ranking'] : array();
        $totalVotes = isset($data['total_votes']) ? (int) $data['total_votes'] : 0;

        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel.Classes');
        spl_autoload_unregister(array('YiiBase', 'autoload'));
        require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        $objPHPExcel = new PHPExcel();
        spl_autoload_register(array('YiiBase', 'autoload'));

        $objPHPExcel->getProperties()->setCreator('System')->setTitle('Ket qua binh chon Miss');
        $sheet = $objPHPExcel->setActiveSheetIndex(0);
        $sheet->setTitle('Binh chon');

        $headerStyle = array(
            'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF'), 'size' => 11),
            'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => '3A57E8')),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ),
        );

        $headers = array('Hạng', 'Số báo danh', 'Thí sinh', 'Đơn vị', 'Số phiếu', 'Tỷ lệ (%)');
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '1', $h);
            $sheet->getStyle($col . '1')->applyFromArray($headerStyle);
            $col++;
        }

        $rowNum = 2;
        foreach ($ranking as $r) {
            $sheet->setCellValue('A' . $rowNum, isset($r['rank']) ? $r['rank'] : '');
            $sheet->setCellValueExplicit('B' . $rowNum, isset($r['candidate_number']) ? $r['candidate_number'] : '', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $rowNum, isset($r['name']) ? $r['name'] : '');
            $sheet->setCellValue('D' . $rowNum, isset($r['unit']) ? $r['unit'] : '');
            $voteCount = isset($r['vote_count']) ? (int) $r['vote_count'] : 0;
            $sheet->setCellValue('E' . $rowNum, $voteCount);
            $sheet->setCellValue('F' . $rowNum, $totalVotes > 0 ? round($voteCount * 100 / $totalVotes, 2) : 0);
            $rowNum++;
        }

        if (!empty($ranking)) {
            $sheet->setCellValue('D' . $rowNum, 'Tổng cộng');
            $sheet->setCellValue('E' . $rowNum, $totalVotes);
            $sheet->setCellValue('F' . $rowNum, $totalVotes > 0 ? 100 : 0);
            $sheet->getStyle('A' . $rowNum . ':F' . $rowNum)->getFont()->setBold(true);
        }

        foreach (array('A' => 8, 'B' => 16, 'C' => 30, 'D' => 30, 'E' => 12, 'F' => 12) as $c => $w) {
            $sheet->getColumnDimension($c)->setWidth($w);
        }

        $filename = 'Ket_qua_binh_chon_Miss_' . date('Ymd_His') . '.xlsx';
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        Yii::app()->end();
    }

    /**
     * Xác định contest_id đang xem: từ GET, nếu không có thì lấy mặc định trong
     * params hoặc cuộc thi đầu danh sách.
     */
    protected function resolveContestId($contests)
    {
        $contestId = (int) Yii::app()->request->getParam('contest_id');
        if ($contestId) {
            return $contestId;
        }
        $params = Yii::app()->params['missVote'];
        if (isset($params['contest_id']) && $params['contest_id']) {
            return (int) $params['contest_id'];
        }
        if (is_array($contests) && !empty($contests)) {
            $keys = array_keys($contests);
            return (int) $keys[0];
        }
        return 0;
    }
}
