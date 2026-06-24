<?php

class MissController extends CController
{
    public $layout = '//layouts/frontend';

    public function filters()
    {
        return array();
    }

    public function actionSubmit($token = null)
    {
        if (empty($token)) {
            $this->render('error', array(
                'message' => 'Link không hợp lệ. Vui lòng kiểm tra lại email.',
            ));
            return;
        }

        // Check token via API
        $url = ApiEndpoints::url(ApiEndpoints::BEAUTY_CONTESTANT_BY_TOKEN, array('token' => $token));
        $result = ApiClient::get($url);

        if (!$result['success'] || !isset($result['data'])) {
            $this->render('error', array(
                'message' => 'Link đã hết hạn hoặc không hợp lệ. Vui lòng liên hệ Ban tổ chức.',
            ));
            return;
        }

        // Check if token invalid/expired (code 400 = already submitted)
        $dataCode = isset($result['data']['code']) ? $result['data']['code'] : null;
        if ($dataCode == 400) {
            $this->redirect(array('alreadySubmitted', 'token' => $token));
            return;
        }

        // Map API data to model (không gọi API lần 2)
        $data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
        $model = new BeautyContestants;
        $model->setAttributes($data, false);
        $model->attendee_name = isset($data['attendee_name']) ? $data['attendee_name'] : '';
        $model->property_name = isset($data['property_name']) ? $data['property_name'] : '';
        $model->contest_name = isset($data['contest_name']) ? $data['contest_name'] : '';
        $model->event_name = isset($data['event_name']) ? $data['event_name'] : '';
        $model->personal_email = isset($data['personal_email']) ? $data['personal_email'] : '';
        $model->submitted_at = isset($data['submitted_at']) ? $data['submitted_at'] : '';

        if (Yii::app()->request->isPostRequest) {
            $postData = $_POST['BeautyContestants'];

            // Upload files to contestant's folder
            $uploadedPaths = $this->uploadContestantFiles($model->attendee_name, $model->id);
            $postData = array_merge($postData, $uploadedPaths);

            $result = BeautyContestants::submitByToken($token, $postData);

            if ($result['success']) {
                // Gửi email xác nhận - dùng model đã có, set email từ postData
                // $model->personal_email = isset($postData['personal_email']) ? $postData['personal_email'] : '';
                EmailHelper::sendMissConfirmation($model);
                $this->redirect(array('thankyou', 'token' => $token));
            } else {
                $errorMsg = isset($result['error']) ? $result['error'] : 'Có lỗi xảy ra. Vui lòng thử lại.';
                Yii::app()->user->setFlash('error', $errorMsg);
            }
        }

        $this->render('submit', array(
            'model' => $model,
            'token' => $token,
        ));
    }

    /**
     * Upload contestant files to dedicated folder
     * @param string $attendeeName Contestant name (Vietnamese)
     * @param int $contestantId Contestant ID
     * @return array Uploaded file paths
     */
    private function uploadContestantFiles($attendeeName, $contestantId)
    {
        $uploadedPaths = array();
        $fileFields = array('photo_portrait', 'photo_portrait_2', 'photo_full_body', 'photo_full_body_2', 'video_path');

        // Create folder name from contestant name (no accents)
        $folderName = MyHelper::toSlug($attendeeName);
        if (empty($folderName)) {
            $folderName = 'contestant-' . $contestantId;
        }

        $uploadDir = Yii::getPathOfAlias('webroot') . '/uploads/miss/' . $folderName;

        // Create directory if not exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        foreach ($fileFields as $field) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES[$field];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                // Generate unique filename
                $filename = $field . '-' . time() . '.' . $ext;
                $targetPath = $uploadDir . '/' . $filename;

                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    // Store relative path
                    $uploadedPaths[$field] = 'uploads/miss/' . $folderName . '/' . $filename;
                }
            }
        }

        return $uploadedPaths;
    }

    public function actionThankyou($token = null)
    {
        $model = null;
        if ($token) {
            $model = BeautyContestants::fetchByToken($token);
        }

        $this->render('thankyou', array(
            'model' => $model,
        ));
    }

    public function actionAlreadySubmitted($token = null)
    {
        if (empty($token)) {
            $this->redirect(array('submit'));
            return;
        }

        $model = BeautyContestants::fetchByToken($token);

        if ($model === null) {
            $this->render('error', array(
                'message' => 'Link không hợp lệ.',
            ));
            return;
        }

        $this->render('already_submitted', array(
            'model' => $model,
        ));
    }
}
