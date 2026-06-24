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

        $model = BeautyContestants::fetchByToken($token);

        if ($model === null) {
            $this->render('error', array(
                'message' => 'Link đã hết hạn hoặc không hợp lệ. Vui lòng liên hệ Ban tổ chức.',
            ));
            return;
        }

        if (!empty($model->submitted_at)) {
            $this->render('already_submitted', array(
                'model' => $model,
            ));
            return;
        }

        if (Yii::app()->request->isPostRequest) {
            $postData = $_POST['BeautyContestants'];

            // Upload files to contestant's folder
            $uploadedPaths = $this->uploadContestantFiles($model->attendee_name, $model->id);
            $postData = array_merge($postData, $uploadedPaths);

            $result = BeautyContestants::submitByToken($token, $postData);

            if ($result['success']) {
                // Gửi email xác nhận
                $updatedModel = BeautyContestants::fetchByToken($token);
                if ($updatedModel) {
                    EmailHelper::sendMissConfirmation($updatedModel);
                }
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
}
