<?php

class TalentEntriesController extends AdminController
{
    public function init()
    {
        parent::init();
        $this->publicActions[] = 'export';
    }

    public function actionIndex()
    {
        $this->redirect(array('admin'));
    }

    public function actionView($id)
    {
        $model = $this->loadModelById($id);
        $members = TalentEntryMembers::getApiDataProvider(array('entry_id' => $id), 100)->getData();

        $this->render('view', array(
            'model' => $model,
            'members' => $members,
        ));
    }

    public function actionCreate()
    {
        $model = new TalentEntries;

        if (isset($_POST['TalentEntries'])) {
            $model->setAttributes($_POST['TalentEntries']);
            if ($model->validate()) {
                $model->status = TalentEntries::STATUS_PENDING;
                $result = $model->storeViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Đăng ký tiết mục thành công.');
                    $newId = isset($result['data']['id']) ? $result['data']['id'] : null;
                    $this->redirect($newId ? array('view', 'id' => $newId) : array('admin'));
                } else {
                    $errorMsg = $result['error'] ?: 'Không thể đăng ký.';
                    $model->addError('title', $errorMsg);
                }
            }
        }

        $shows = $this->getActiveShows();
        $categories = TalentCategories::getListForDropdown();
        $properties = Properties::getListForDropdown();

        $this->render('create', array(
            'model' => $model,
            'shows' => $shows,
            'categories' => $categories,
            'properties' => $properties,
        ));
    }

    public function actionUpdate($id)
    {
        $model = $this->loadModelById($id);

        if (isset($_POST['TalentEntries'])) {
            $model->setAttributes($_POST['TalentEntries']);

            if ($model->validate()) {
                $result = $model->updateViaApi();

                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Cập nhật tiết mục thành công.');
                    $this->redirect(array('view', 'id' => $id));
                } else {
                    $model->addError('title', $result['error'] ?: 'Không thể cập nhật.');
                }
            }
        }

        $shows = $this->getActiveShows();
        $categories = TalentCategories::getListForDropdown();
        $properties = Properties::getListForDropdown();

        $this->render('update', array(
            'model' => $model,
            'shows' => $shows,
            'categories' => $categories,
            'properties' => $properties,
        ));
    }

    public function actionDelete($id)
    {
        if (Yii::app()->getRequest()->getIsPostRequest()) {
            $result = TalentEntries::deleteViaApi($id);

            if ($result['success']) {
                Yii::app()->user->setFlash('success', 'Xóa tiết mục thành công.');
            } else {
                Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể xóa.');
            }

            if (!Yii::app()->getRequest()->getIsAjaxRequest()) {
                $this->redirect(array('admin'));
            }
        } else {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }
    }

    public function actionAdmin()
    {
        $model = new TalentEntries('search');
        $model->unsetAttributes();

        if (isset($_GET['TalentEntries'])) {
            $model->setAttributes($_GET['TalentEntries']);
        }

        $params = array();
        foreach ($model->attributes as $key => $value) {
            if ($value !== null && $value !== '') {
                $params[$key] = $value;
            }
        }

        $dataProvider = TalentEntries::getApiDataProvider($params);
        $shows = $this->getActiveShows();
        $categories = TalentCategories::getListForDropdown();
        $properties = Properties::getListForDropdown();

        $this->render('admin', array(
            'model' => $model,
            'dataProvider' => $dataProvider,
            'shows' => $shows,
            'categories' => $categories,
            'properties' => $properties,
        ));
    }

    public function actionExport()
    {
        $model = new TalentEntries('search');
        $model->unsetAttributes();

        if (isset($_GET['TalentEntries'])) {
            $model->setAttributes($_GET['TalentEntries']);
        }

        $params = array(
            'per_page' => 5000,
        );
        foreach ($model->attributes as $key => $value) {
            if ($value !== null && $value !== '') {
                $params[$key] = $value;
            }
        }

        $dataProvider = TalentEntries::getApiDataProvider($params, 5000);
        $data = $dataProvider->getData();

        // Initialize PHPExcel
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel.Classes');
        spl_autoload_unregister(array('YiiBase', 'autoload'));
        require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        $objPHPExcel = new PHPExcel();
        spl_autoload_register(array('YiiBase', 'autoload'));

        $objPHPExcel->getProperties()->setCreator("System")
            ->setLastModifiedBy("System")
            ->setTitle("Danh sach tiet muc van nghe")
            ->setSubject("Danh sach tiet muc van nghe");

        $sheet = $objPHPExcel->setActiveSheetIndex(0);
        $sheet->setTitle('Tiet_Muc_Van_Nghe');

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

        $headers = array('STT', 'Tên tiết mục', 'Thể loại', 'Đơn vị', 'Thời lượng (giây)', 'Đạo diễn/Biên đạo', 'SĐT đạo diễn', 'Nguồn gốc/Xuất xứ', 'Trạng thái');
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '1', $h);
            $sheet->getStyle($col . '1')->applyFromArray($headerStyle);
            $col++;
        }

        $rowNum = 2;
        $stt = 1;
        foreach ($data as $item) {
            $statusText = '';
            if ($item->status == TalentEntries::STATUS_DRAFT) {
                $statusText = 'Nháp';
            } elseif ($item->status == TalentEntries::STATUS_SUBMITTED) {
                $statusText = 'Đã nộp';
            } elseif ($item->status == TalentEntries::STATUS_APPROVED) {
                $statusText = 'Đã duyệt';
            } elseif ($item->status == TalentEntries::STATUS_REJECTED) {
                $statusText = 'Từ chối';
            } elseif ($item->status == TalentEntries::STATUS_PENDING) {
                $statusText = 'Chờ xử lý';
            } else {
                $statusText = $item->status;
            }

            $sheet->setCellValue('A' . $rowNum, $stt++);
            $sheet->setCellValue('B' . $rowNum, $item->title);
            $sheet->setCellValue('C' . $rowNum, isset($item->category_name) ? $item->category_name : $item->category_id);
            $sheet->setCellValue('D' . $rowNum, isset($item->property_name) ? $item->property_name : $item->property_id);
            $sheet->setCellValue('E' . $rowNum, $item->duration_seconds);
            $sheet->setCellValue('F' . $rowNum, $item->director);
            $sheet->setCellValue('G' . $rowNum, $item->director_phone);
            $sheet->setCellValue('H' . $rowNum, $item->origin);
            $sheet->setCellValue('I' . $rowNum, $statusText);

            $sheet->getStyle('A' . $rowNum . ':I' . $rowNum)->applyFromArray($borderStyle);
            $rowNum++;
        }

        foreach (range('A', 'I') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $filename = "Danh_sach_tiet_muc_van_nghe_" . date('Ymd_His') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        Yii::app()->end();
    }

    public function actionAddMember($entryId)
    {
        $entry = $this->loadModelById($entryId);
        $model = new TalentEntryMembers;

        if (isset($_POST['TalentEntryMembers'])) {
            $model->setAttributes($_POST['TalentEntryMembers']);
            $model->entry_id = $entryId;

            if ($model->validate()) {
                $result = $model->storeViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Thêm thành viên thành công.');
                    $this->redirect(array('view', 'id' => $entryId));
                } else {
                    $model->addError('attendee_id', $result['error'] ?: 'Không thể thêm thành viên.');
                }
            }
        }

        $attendees = Attendees::getApiDataProvider(array(
            'property_id' => $entry->property_id,
            'approval_status' => Attendees::APPROVAL_APPROVED,
        ), 500)->getData();

        $this->render('add_member', array(
            'model' => $model,
            'entry' => $entry,
            'attendees' => $attendees,
        ));
    }

    public function actionRemoveMember($id)
    {
        if (Yii::app()->getRequest()->getIsPostRequest()) {
            $member = TalentEntryMembers::fetchFromApi($id);
            $entryId = $member ? $member->entry_id : null;

            $result = TalentEntryMembers::deleteViaApi($id);

            if ($result['success']) {
                Yii::app()->user->setFlash('success', 'Xóa thành viên thành công.');
            } else {
                Yii::app()->user->setFlash('error', $result['error'] ?: 'Không thể xóa thành viên.');
            }

            if ($entryId) {
                $this->redirect(array('view', 'id' => $entryId));
            } else {
                $this->redirect(array('admin'));
            }
        } else {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }
    }

    public function actionScoring($id)
    {
        $entry = $this->loadModelById($id);
        $scores = TalentScores::getByEntry($id);
        $average = TalentScores::computeAverage($scores);

        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->theme->baseUrl . '/assets/js/pages/talent-entries-scoring.js',
            CClientScript::POS_END
        );

        $this->render('scoring', array(
            'entry' => $entry,
            'scores' => $scores,
            'average' => $average,
        ));
    }

    public function actionSaveScore()
    {
        if (!Yii::app()->request->isPostRequest) {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }

        $scoreId = isset($_POST['id']) && $_POST['id'] !== '' ? $_POST['id'] : null;
        $entryId = isset($_POST['entry_id']) ? $_POST['entry_id'] : null;
        $judgeId = isset($_POST['judge_id']) ? $_POST['judge_id'] : null;
        $score = isset($_POST['score']) ? $_POST['score'] : null;
        $criteria = isset($_POST['criteria']) ? $_POST['criteria'] : '';
        $note = isset($_POST['note']) ? $_POST['note'] : '';

        if (!$entryId || !$judgeId || $score === null || $score === '') {
            $this->sendJsonResponse(array('success' => false, 'message' => 'Vui lòng nhập đầy đủ giám khảo và điểm.'));
            return;
        }

        $model = $scoreId ? TalentScores::fetchFromApi($scoreId) : new TalentScores;
        if ($model === null) {
            $this->sendJsonResponse(array('success' => false, 'message' => 'Không tìm thấy phiếu điểm.'));
            return;
        }

        $model->entry_id = $entryId;
        $model->judge_id = $judgeId;
        $model->score = $score;
        $model->criteria = $criteria;
        $model->note = $note;

        $result = $scoreId ? $model->updateViaApi() : $model->storeViaApi();

        if ($result['success']) {
            $this->sendJsonResponse(array('success' => true, 'message' => 'Lưu điểm thành công.'));
        } else {
            $this->sendJsonResponse(array('success' => false, 'message' => $result['error'] ?: 'Không thể lưu điểm.'));
        }
    }

    public function actionDeleteScore()
    {
        if (!Yii::app()->request->isPostRequest) {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }

        $scoreId = isset($_POST['id']) ? $_POST['id'] : null;
        if (!$scoreId) {
            $this->sendJsonResponse(array('success' => false, 'message' => 'Thiếu thông tin.'));
            return;
        }

        $result = TalentScores::deleteViaApi($scoreId);
        if ($result['success']) {
            $this->sendJsonResponse(array('success' => true, 'message' => 'Xóa điểm thành công.'));
        } else {
            $this->sendJsonResponse(array('success' => false, 'message' => $result['error'] ?: 'Không thể xóa điểm.'));
        }
    }

    private function sendJsonResponse($data)
    {
        header('Content-Type: application/json');
        echo CJSON::encode($data);
        Yii::app()->end();
    }

    protected function getActiveShows()
    {
        $result = ApiClient::get(ApiEndpoints::TALENT_SHOW_LIST, array(
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

    protected function loadModelById($id)
    {
        $model = TalentEntries::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy tiết mục.');
        }
        return $model;
    }
}
