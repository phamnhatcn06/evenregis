<?php

class BeautyContestantsController extends AdminController
{
    public function init()
    {
        parent::init();
        $this->publicActions[] = 'export';
        $this->publicActions[] = 'generateAllTokens';
    }

    public function actionIndex()
    {
        $this->redirect(array('admin'));
    }

    public function actionView($id)
    {
        $model = $this->loadModelById($id);

        $this->render('view', array(
            'model' => $model,
        ));
    }

    public function actionCreate()
    {
        $model = new BeautyContestants;

        if (isset($_POST['BeautyContestants'])) {
            $model->setAttributes($_POST['BeautyContestants']);
            if ($model->validate()) {
                $model->status = BeautyContestants::STATUS_REGISTERED;
                $result = $model->storeViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Đăng ký thí sinh thành công.');
                    $newId = isset($result['data']['id']) ? $result['data']['id'] : null;
                    $this->redirect($newId ? array('view', 'id' => $newId) : array('admin'));
                } else {
                    $errorMsg = $result['error'] ?: 'Không thể đăng ký.';
                    $model->addError('attendee_id', $errorMsg);
                }
            }
        }

        $contests = $this->getActiveContests();
        $properties = Properties::getListForDropdown();

        $this->render('create', array(
            'model' => $model,
            'contests' => $contests,
            'properties' => $properties,
        ));
    }

    public function actionUpdate($id)
    {
        $model = $this->loadModelById($id);

        if (isset($_POST['BeautyContestants'])) {
            $model->setAttributes($_POST['BeautyContestants']);

            if ($model->validate()) {
                $result = $model->updateViaApi();

                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Cập nhật thí sinh thành công.');
                    $this->redirect(array('view', 'id' => $id));
                } else {
                    $model->addError('attendee_id', $result['error'] ?: 'Không thể cập nhật.');
                }
            }
        }

        $contests = $this->getActiveContests();

        $this->render('update', array(
            'model' => $model,
            'contests' => $contests,
        ));
    }

    public function actionDelete($id)
    {
        if (Yii::app()->getRequest()->getIsPostRequest()) {
            $result = BeautyContestants::deleteViaApi($id);

            if ($result['success']) {
                Yii::app()->user->setFlash('success', 'Xóa thí sinh thành công.');
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
        $model = new BeautyContestants('search');
        $model->unsetAttributes();

        if (isset($_GET['BeautyContestants'])) {
            $model->setAttributes($_GET['BeautyContestants']);
        }

        $params = array(
            'with' => 'attendee,attendee.property,attendee.property.regional,contest',
            'sort' => 'attendee.property.regional.code',
        );
        foreach ($model->attributes as $key => $value) {
            if ($value !== null && $value !== '') {
                $params[$key] = $value;
            }
        }

        $dataProvider = BeautyContestants::getApiDataProvider($params);
        $contests = $this->getActiveContests();

        $this->render('admin', array(
            'model' => $model,
            'dataProvider' => $dataProvider,
            'contests' => $contests,
        ));
    }

    public function actionExport()
    {
        $model = new BeautyContestants('search');
        $model->unsetAttributes();

        if (isset($_GET['BeautyContestants'])) {
            $model->setAttributes($_GET['BeautyContestants']);
        }

        $params = array(
            'with' => 'attendee,attendee.property,attendee.property.regional,contest',
            'sort' => 'attendee.property.regional.code',
            'per_page' => 5000,
        );
        foreach ($model->attributes as $key => $value) {
            if ($value !== null && $value !== '') {
                $params[$key] = $value;
            }
        }

        $dataProvider = BeautyContestants::getApiDataProvider($params, 5000);
        $data = $dataProvider->getData();

        // Initialize PHPExcel
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel.Classes');
        spl_autoload_unregister(array('YiiBase', 'autoload'));
        require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        $objPHPExcel = new PHPExcel();
        spl_autoload_register(array('YiiBase', 'autoload'));

        $objPHPExcel->getProperties()->setCreator("System")
            ->setLastModifiedBy("System")
            ->setTitle("Danh sach thi sinh Miss")
            ->setSubject("Danh sach thi sinh Miss");

        $sheet = $objPHPExcel->setActiveSheetIndex(0);
        $sheet->setTitle('Miss_Contestants');

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

        $headers = array('STT', 'Sự kiện', 'Cuộc thi', 'Đơn vị', 'Thí sinh', 'Chiều cao (cm)', 'Cân nặng (kg)', 'Số đo', 'Năng khiếu', 'Tiểu sử', 'Trạng thái');
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '1', $h);
            $sheet->getStyle($col . '1')->applyFromArray($headerStyle);
            $col++;
        }

        $rowNum = 2;
        $stt = 1;
        foreach ($data as $item) {
            $eventName = '';
            if (!empty($item->event_name)) {
                $eventName = $item->event_name;
            } elseif (isset($item->contest) && isset($item->contest->event)) {
                $eventName = $item->contest->event->name;
            }

            $contestName = '';
            if (!empty($item->contest_name)) {
                $contestName = $item->contest_name;
            } elseif (isset($item->contest)) {
                $contestName = $item->contest->name;
            } else {
                $contestName = $item->contest_id;
            }

            $unitName = '';
            if (!empty($item->registration_id)) {
                $unitName = BeautyContestants::getPropertyNameByRegistrationId($item->registration_id);
            }
            if (empty($unitName) && !empty($item->property_name)) {
                $unitName = $item->property_name;
            }
            if (empty($unitName) && isset($item->attendee)) {
                if (isset($item->attendee->property)) {
                    $unitName = $item->attendee->property->name;
                } elseif (!empty($item->attendee->unit_label)) {
                    $unitName = $item->attendee->unit_label;
                }
            }

            $attendeeName = '';
            if (isset($item->members) && !empty($item->members)) {
                $attendeeName = $item->members[0]['attendee_name'];
            } elseif (isset($item->attendee)) {
                $attendeeName = $item->attendee->full_name;
            } else {
                $attendeeName = $item->attendee_id;
            }

            $statusText = '';
            if ($item->status == BeautyContestants::STATUS_REGISTERED) {
                $statusText = 'Đã đăng ký';
            } elseif ($item->status == BeautyContestants::STATUS_CONFIRMED) {
                $statusText = 'Đã xác nhận';
            } elseif ($item->status == BeautyContestants::STATUS_DISQUALIFIED) {
                $statusText = 'Loại';
            } else {
                $statusText = $item->status;
            }

            $sheet->setCellValue('A' . $rowNum, $stt++);
            $sheet->setCellValue('B' . $rowNum, $eventName);
            $sheet->setCellValue('C' . $rowNum, $contestName);
            $sheet->setCellValue('D' . $rowNum, $unitName);
            $sheet->setCellValue('E' . $rowNum, $attendeeName);
            $sheet->setCellValue('F' . $rowNum, $item->height_cm);
            $sheet->setCellValue('G' . $rowNum, $item->weight_kg);
            $sheet->setCellValue('H' . $rowNum, $item->measurements);
            $sheet->setCellValue('I' . $rowNum, $item->talent);
            $sheet->setCellValue('J' . $rowNum, $item->bio);
            $sheet->setCellValue('K' . $rowNum, $statusText);

            $sheet->getStyle('A' . $rowNum . ':K' . $rowNum)->applyFromArray($borderStyle);
            $rowNum++;
        }

        foreach (range('A', 'K') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $filename = "Danh_sach_thi_sinh_Miss_" . date('Ymd_His') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        Yii::app()->end();
    }

    public function actionGetFemaleAttendees($propertyId)
    {
        $attendees = Attendees::getApiDataProvider(array(
            'property_id' => $propertyId,
            'approval_status' => Attendees::APPROVAL_APPROVED,
            'gender' => 'female',
        ), 500)->getData();

        $result = array();
        foreach ($attendees as $att) {
            $result[] = array(
                'id' => $att->id,
                'name' => $att->full_name,
                'staff_code' => isset($att->staff_code) ? $att->staff_code : '',
            );
        }

        header('Content-Type: application/json');
        echo json_encode(array('success' => true, 'data' => $result));
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

    public function actionSendInviteEmail()
    {
        if (!Yii::app()->request->isPostRequest || !Yii::app()->request->isAjaxRequest) {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ');
        }

        $id = Yii::app()->request->getPost('id');
        if (empty($id)) {
            echo CJSON::encode(array('success' => false, 'message' => 'Thiếu ID thí sinh'));
            Yii::app()->end();
        }

        $result = BeautyContestants::generateSubmissionToken($id);

        if ($result['success']) {
            echo CJSON::encode(array('success' => true, 'message' => 'Đã gửi email thành công'));
        } else {
            $msg = isset($result['error']) ? $result['error'] : 'Không thể gửi email';
            echo CJSON::encode(array('success' => false, 'message' => $msg));
        }
        Yii::app()->end();
    }

    public function actionSendBulkInviteEmail()
    {
        if (!Yii::app()->request->isPostRequest || !Yii::app()->request->isAjaxRequest) {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ');
        }

        $dataProvider = BeautyContestants::getApiDataProvider(array(
            'submitted_at' => 'null',
        ), 1000);
        $contestants = $dataProvider->getData();

        $sent = 0;
        $failed = 0;

        foreach ($contestants as $contestant) {
            if (!empty($contestant->submitted_at)) {
                continue;
            }

            $result = BeautyContestants::generateSubmissionToken($contestant->id);
            if ($result['success']) {
                $sent++;
            } else {
                $failed++;
            }
        }

        echo CJSON::encode(array(
            'success' => true,
            'sent' => $sent,
            'failed' => $failed,
        ));
        Yii::app()->end();
    }

    public function actionGenerateAllTokens()
    {
        if (!Yii::app()->request->isPostRequest || !Yii::app()->request->isAjaxRequest) {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ');
        }

        $expiresAt = Yii::app()->request->getPost('expires_at');
        if (empty($expiresAt)) {
            echo CJSON::encode(array('success' => false, 'message' => 'Thiếu thời gian hết hạn'));
            Yii::app()->end();
        }

        $result = BeautyContestants::generateAllSubmissionTokens($expiresAt);

        if ($result['success']) {
            $data = isset($result['data']) ? $result['data'] : array();
            $generated = isset($data['generated']) ? $data['generated'] : 0;
            $skipped = isset($data['skipped']) ? $data['skipped'] : 0;
            echo CJSON::encode(array(
                'success' => true,
                'message' => 'Tạo token thành công',
                'generated' => $generated,
                'skipped' => $skipped,
            ));
        } else {
            $msg = isset($result['error']) ? $result['error'] : 'Không thể tạo token';
            echo CJSON::encode(array('success' => false, 'message' => $msg));
        }
        Yii::app()->end();
    }

    protected function loadModelById($id)
    {
        $model = BeautyContestants::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy thí sinh.');
        }
        return $model;
    }
}
