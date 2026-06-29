<?php

class BeautyRoundsController extends AdminController
{
    public function actionIndex()
    {
        $this->redirect(array('admin'));
    }

    public function actionView($id)
    {
        $model = $this->loadModelById($id);
        $contestants = BeautyRoundResults::getAssignedContestants($id);
        $availableContestants = BeautyRoundResults::getAvailableContestants($id);

        // DEBUG
        Yii::log('getAssignedContestants for round ' . $id . ': ' . print_r($contestants, true), 'info', 'beauty');

        $this->render('view', array(
            'model' => $model,
            'contestants' => $contestants,
            'availableContestants' => $availableContestants,
        ));
    }

    public function actionAssignContestants($id)
    {
        $model = $this->loadModelById($id);

        if (Yii::app()->request->isPostRequest) {
            $registrationIds = isset($_POST['registration_ids']) ? $_POST['registration_ids'] : array();
            if (!empty($registrationIds)) {
                $result = BeautyRoundResults::assignContestants($id, $registrationIds);
                Yii::log('AssignContestants result: ' . print_r($result, true), 'info', 'beauty');
                if ($result['success']) {
                    $count = isset($result['data']['count']) ? $result['data']['count'] : count($registrationIds);
                    $this->sendJsonResponse(array('success' => true, 'message' => 'Gắn ' . $count . ' thí sinh thành công.', 'debug' => $result));
                } else {
                    $errorMsg = isset($result['error']) ? $result['error'] : (isset($result['message']) ? $result['message'] : 'Không thể gắn thí sinh.');
                    $this->sendJsonResponse(array('success' => false, 'message' => $errorMsg, 'debug' => $result));
                }
            } else {
                $this->sendJsonResponse(array('success' => false, 'message' => 'Vui lòng chọn thí sinh.'));
            }
            return;
        }

        $availableContestants = BeautyRoundResults::getAvailableContestants($id);
        $assignedContestants = BeautyRoundResults::getAssignedContestants($id);

        $this->render('assignContestants', array(
            'model' => $model,
            'availableContestants' => $availableContestants,
            'assignedContestants' => $assignedContestants,
        ));
    }

    public function actionScoring($id)
    {
        $model = $this->loadModelById($id);
        $contestants = BeautyRoundResults::getApiDataProvider(array('round_id' => $id))->getData();

        $this->render('scoring', array(
            'model' => $model,
            'contestants' => $contestants,
        ));
    }

    public function actionSaveScore()
    {
        if (!Yii::app()->request->isPostRequest) {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ.');
        }

        $resultId = isset($_POST['result_id']) ? $_POST['result_id'] : null;
        $score = isset($_POST['score']) ? $_POST['score'] : null;
        $note = isset($_POST['note']) ? $_POST['note'] : '';

        if (!$resultId || $score === null) {
            $this->sendJsonResponse(array('success' => false, 'message' => 'Thiếu thông tin.'));
            return;
        }

        $url = ApiEndpoints::url(ApiEndpoints::BEAUTY_ROUND_RESULT_UPDATE, array('id' => $resultId));
        $result = ApiClient::post($url, array('score' => $score, 'note' => $note));

        if ($result['success']) {
            $this->sendJsonResponse(array('success' => true, 'message' => 'Lưu điểm thành công.'));
        } else {
            $this->sendJsonResponse(array('success' => false, 'message' => $result['error'] ?: 'Không thể lưu điểm.'));
        }
    }

    public function actionQualify($id)
    {
        $model = $this->loadModelById($id);

        if (Yii::app()->request->isPostRequest) {
            $registrationIds = isset($_POST['registration_ids']) ? $_POST['registration_ids'] : array();
            $nextRoundId = isset($_POST['next_round_id']) ? $_POST['next_round_id'] : null;

            if (!empty($registrationIds)) {
                $results = array();
                foreach ($registrationIds as $regId) {
                    $results[] = array('registration_id' => $regId, 'passed' => true);
                }
                $result = BeautyRoundResults::qualifyContestants($id, $results, $nextRoundId);
                if ($result['success']) {
                    $this->sendJsonResponse(array('success' => true, 'message' => 'Chọn thí sinh đi tiếp thành công.'));
                } else {
                    $this->sendJsonResponse(array('success' => false, 'message' => $result['error'] ?: 'Không thể cập nhật.'));
                }
            } else {
                $this->sendJsonResponse(array('success' => false, 'message' => 'Vui lòng chọn thí sinh.'));
            }
            return;
        }

        $ranking = BeautyRoundResults::getRanking($id);
        $nextRounds = BeautyRounds::getListForDropdown($model->contest_id);
        unset($nextRounds[$id]);

        $this->render('qualify', array(
            'model' => $model,
            'ranking' => $ranking,
            'nextRounds' => $nextRounds,
        ));
    }

    private function sendJsonResponse($data)
    {
        header('Content-Type: application/json');
        echo CJSON::encode($data);
        Yii::app()->end();
    }

    public function actionCreate()
    {
        $model = new BeautyRounds;

        if (isset($_POST['BeautyRounds'])) {
            $model->setAttributes($_POST['BeautyRounds']);
            if ($model->validate()) {
                $result = $model->storeViaApi();
                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Tạo vòng thi thành công.');
                    $newId = isset($result['data']['id']) ? $result['data']['id'] : null;
                    $this->redirect($newId ? array('view', 'id' => $newId) : array('admin'));
                } else {
                    $errorMsg = $result['error'] ?: 'Không thể tạo vòng thi.';
                    $model->addError('name', $errorMsg);
                }
            }
        }

        $contests = BeautyContests::getListForDropdown();

        $this->render('create', array(
            'model' => $model,
            'contests' => $contests,
        ));
    }

    public function actionUpdate($id)
    {
        $model = $this->loadModelById($id);

        if (isset($_POST['BeautyRounds'])) {
            $model->setAttributes($_POST['BeautyRounds']);

            if ($model->validate()) {
                $result = $model->updateViaApi();

                if ($result['success']) {
                    Yii::app()->user->setFlash('success', 'Cập nhật vòng thi thành công.');
                    $this->redirect(array('view', 'id' => $id));
                } else {
                    $model->addError('name', $result['error'] ?: 'Không thể cập nhật.');
                }
            }
        }

        $contests = BeautyContests::getListForDropdown();

        $this->render('update', array(
            'model' => $model,
            'contests' => $contests,
        ));
    }

    public function actionDelete($id)
    {
        if (Yii::app()->getRequest()->getIsPostRequest()) {
            $result = BeautyRounds::deleteViaApi($id);

            if ($result['success']) {
                Yii::app()->user->setFlash('success', 'Xóa vòng thi thành công.');
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
        $model = new BeautyRounds('search');
        $model->unsetAttributes();

        if (isset($_GET['BeautyRounds'])) {
            $model->setAttributes($_GET['BeautyRounds']);
        }

        $params = array();
        foreach ($model->attributes as $key => $value) {
            if ($value !== null && $value !== '') {
                $params[$key] = $value;
            }
        }

        $dataProvider = BeautyRounds::getApiDataProvider($params);
        $contests = BeautyContests::getListForDropdown();

        $this->render('admin', array(
            'model' => $model,
            'dataProvider' => $dataProvider,
            'contests' => $contests,
        ));
    }

    public function actionDebugResults($id)
    {
        $result = ApiClient::get(ApiEndpoints::BEAUTY_ROUND_RESULT_LIST, array(
            'round_id' => $id,
            'per_page' => 1000,
        ));
        header('Content-Type: application/json');
        echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        Yii::app()->end();
    }

    protected function loadModelById($id)
    {
        $model = BeautyRounds::fetchFromApi($id);
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy vòng thi.');
        }
        return $model;
    }
}
