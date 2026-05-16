<?php

class AttendeesController extends AdminController
{
    public function actionAdmin()
    {
        $params = array();

        if (isset($_GET['event_id']) && $_GET['event_id']) {
            $params['event_id'] = $_GET['event_id'];
        }
        if (isset($_GET['property_id']) && $_GET['property_id']) {
            $params['property_id'] = $_GET['property_id'];
        }
        if (isset($_GET['registration_id']) && $_GET['registration_id']) {
            $params['registration_id'] = $_GET['registration_id'];
        }
        if (isset($_GET['full_name']) && $_GET['full_name']) {
            $params['full_name'] = $_GET['full_name'];
        }

        $dataProvider = Attendees::getApiDataProvider($params);

        $events = Events::getApiDataProvider(array('status' => 1), 100)->getData();
        $properties = Properties::getApiDataProvider(array(), 100)->getData();

        $this->render('admin', array(
            'dataProvider' => $dataProvider,
            'events' => $events,
            'properties' => $properties,
        ));
    }

    public function actionView($id)
    {
        $model = $this->loadModelById($id);

        if ($model->event_id) {
            $event = Events::fetchFromApi($model->event_id);
            $model->event_name = $event ? $event->name : '';
        }
        if ($model->property_id) {
            $property = Properties::fetchFromApi($model->property_id);
            $model->property_name = $property ? $property->name : '';
        }
        if ($model->staff_id) {
            $staff = Staffs::fetchFromApi($model->staff_id);
            $model->staff_name = $staff ? $staff->full_name : '';
        }

        $this->render('view', array(
            'model' => $model,
        ));
    }

    public function actionCreate()
    {
        $model = new Attendees;

        $user = AuthHandler::getUser();
        $userPropertyId = isset($user['property_id']) ? $user['property_id'] : null;
        $userPropertyCode = isset($user['property_code']) ? $user['property_code'] : null;
        $isAdmin = ($userPropertyCode === '9999');

        $events = $this->getEventList();
        $properties = $this->getPropertyList($isAdmin, $userPropertyId);
        $staffList = $this->getStaffList($userPropertyId);

        if ($userPropertyId && !$model->property_id) {
            $model->property_id = $userPropertyId;
        }

        if (isset($_POST['Attendees'])) {
            $model->setAttributes($_POST['Attendees']);

            $uploadResult = $this->handleDocumentUploads($model);
            if (!empty($uploadResult['errors'])) {
                foreach ($uploadResult['errors'] as $field => $error) {
                    $model->addError($field, $error);
                }
            }

            if (!$model->hasErrors() && $model->validate()) {
                $result = $model->storeViaApi();

                if ($result['success']) {
                    $newId = isset($result['data']['id']) ? $result['data']['id'] : null;
                    Yii::app()->user->setFlash('success', 'Thêm người tham dự thành công.');
                    $this->redirect($newId ? array('view', 'id' => $newId) : array('admin'));
                } else {
                    $errorMsg = isset($result['error']) ? $result['error'] : 'Không thể thêm người tham dự.';
                    $model->addError('full_name', $errorMsg);
                }
            }
        }

        $this->render('create', array(
            'model' => $model,
            'events' => $events,
            'properties' => $properties,
            'staffList' => $staffList,
        ));
    }

    public function actionUpdate($id)
    {
        $model = $this->loadModelById($id);

        $user = AuthHandler::getUser();
        $userPropertyId = isset($user['property_id']) ? $user['property_id'] : null;
        $userPropertyCode = isset($user['property_code']) ? $user['property_code'] : null;
        $isAdmin = ($userPropertyCode === '9999');

        $events = $this->getEventList();
        $properties = $this->getPropertyList($isAdmin, $userPropertyId);
        $staffList = $this->getStaffList($model->property_id);

        if (isset($_POST['Attendees'])) {
            $model->setAttributes($_POST['Attendees']);

            $uploadResult = $this->handleDocumentUploads($model);
            if (!empty($uploadResult['errors'])) {
                foreach ($uploadResult['errors'] as $field => $error) {
                    $model->addError($field, $error);
                }
            }

            if (!$model->hasErrors() && $model->validate()) {
                $result = $model->updateViaApi();

                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Cập nhật thành công.');
                    $this->redirect(array('view', 'id' => $model->id));
                } else {
                    $errorMsg = isset($result['error']) ? $result['error'] : 'Không thể cập nhật.';
                    $model->addError('full_name', $errorMsg);
                }
            }
        }

        $this->render('update', array(
            'model' => $model,
            'events' => $events,
            'properties' => $properties,
            'staffList' => $staffList,
        ));
    }

    public function actionDelete($id)
    {
        if (Yii::app()->request->isPostRequest) {
            $result = Attendees::deleteViaApi($id);

            if ($result['success']) {
                Yii::app()->user->setFlash('success', 'Xóa thành công.');
            } else {
                Yii::app()->user->setFlash('error', 'Không thể xóa.');
            }

            $this->redirect(array('admin'));
        } else {
            throw new CHttpException(400, 'Invalid request.');
        }
    }

    /**
     * BR-REG-02, BR-REG-03: Xử lý upload documents
     */
    protected function handleDocumentUploads($model)
    {
        $result = array('errors' => array());
        $uploadDir = Yii::getPathOfAlias('webroot') . '/uploads/attendees/' . date('Y/m');

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $docFields = array(
            'cccd_front' => array('type' => 'image', 'label' => 'CCCD mặt trước'),
            'cccd_back' => array('type' => 'image', 'label' => 'CCCD mặt sau'),
            'portrait' => array('type' => 'portrait', 'label' => 'Ảnh chân dung'),
            'contract' => array('type' => 'contract', 'label' => 'Hợp đồng lao động'),
        );

        foreach ($docFields as $field => $config) {
            $fileKey = $field . '_file';

            if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES[$fileKey];

                $errors = RegistrationValidator::validateUploadedFile($file, $config['type']);
                if (!empty($errors)) {
                    $result['errors'][$field . '_path'] = $config['label'] . ': ' . implode(', ', $errors);
                    continue;
                }

                if ($config['type'] === 'portrait') {
                    $dimErrors = RegistrationValidator::validatePortraitDimension($file['tmp_name']);
                    if (!empty($dimErrors)) {
                        $result['errors'][$field . '_path'] = $config['label'] . ': ' . implode(', ', $dimErrors);
                        continue;
                    }
                }

                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = $field . '_' . uniqid() . '.' . strtolower($ext);
                $targetPath = $uploadDir . '/' . $filename;

                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $relativePath = '/uploads/attendees/' . date('Y/m') . '/' . $filename;
                    $model->{$field . '_path'} = $relativePath;
                } else {
                    $result['errors'][$field . '_path'] = $config['label'] . ': Không thể lưu file.';
                }
            }
        }

        return $result;
    }

    /**
     * AJAX: Kiểm tra giới hạn môn thể thao
     */
    public function actionCheckSportLimit()
    {
        if (!Yii::app()->request->isAjaxRequest) {
            throw new CHttpException(400, 'Invalid request.');
        }

        $attendeeId = Yii::app()->request->getParam('attendee_id');
        $sportId = Yii::app()->request->getParam('sport_id');
        $eventId = Yii::app()->request->getParam('event_id');

        $result = RegistrationValidator::wouldExceedSportLimit($attendeeId, $sportId, $eventId);

        header('Content-Type: application/json');
        echo CJSON::encode($result);
        Yii::app()->end();
    }

    /**
     * AJAX: Kiểm tra phòng ban thi nghiệp vụ
     */
    public function actionCheckCompetitionEligibility()
    {
        if (!Yii::app()->request->isAjaxRequest) {
            throw new CHttpException(400, 'Invalid request.');
        }

        $attendeeId = Yii::app()->request->getParam('attendee_id');
        $competitionId = Yii::app()->request->getParam('competition_id');

        $result = RegistrationValidator::canRegisterCompetition($attendeeId, $competitionId);

        header('Content-Type: application/json');
        echo CJSON::encode($result);
        Yii::app()->end();
    }

    protected function loadModelById($id)
    {
        $model = Attendees::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy người tham dự.');
        }
        return $model;
    }

    protected function getEventList()
    {
        $list = array();
        $events = Events::getApiDataProvider(array('status' => 1), 100)->getData();
        foreach ($events as $e) {
            $id = isset($e->id) ? $e->id : (isset($e['id']) ? $e['id'] : null);
            $name = isset($e->name) ? $e->name : (isset($e['name']) ? $e['name'] : '');
            if ($id) {
                $list[$id] = $name;
            }
        }
        return $list;
    }

    protected function getPropertyList($isAdmin, $userPropertyId)
    {
        $list = array();
        if ($isAdmin) {
            $properties = Properties::getApiDataProvider(array(), 100)->getData();
        } else {
            $properties = $userPropertyId ? Properties::getApiDataProvider(array('id' => $userPropertyId), 100)->getData() : array();
        }
        foreach ($properties as $p) {
            $id = isset($p->id) ? $p->id : (isset($p['id']) ? $p['id'] : null);
            $code = isset($p->code) ? $p->code : (isset($p['code']) ? $p['code'] : '');
            $name = isset($p->name) ? $p->name : (isset($p['name']) ? $p['name'] : '');
            if ($id) {
                $list[$id] = "{$code} - {$name}";
            }
        }
        asort($list);
        return $list;
    }

    protected function getStaffList($propertyId)
    {
        $list = array();
        if (!$propertyId) {
            return $list;
        }

        $staffs = Staffs::getApiDataProvider(array('property_id' => $propertyId), 500)->getData();
        foreach ($staffs as $s) {
            $id = isset($s->id) ? $s->id : (isset($s['id']) ? $s['id'] : null);
            $name = isset($s->full_name) ? $s->full_name : (isset($s['full_name']) ? $s['full_name'] : '');
            $position = isset($s->position_name) ? $s->position_name : (isset($s['position_name']) ? $s['position_name'] : '');
            if ($id) {
                $list[$id] = $name . ($position ? " - {$position}" : '');
            }
        }
        return $list;
    }
}
