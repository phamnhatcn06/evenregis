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

            $files = array();
            $fileFields = array('photo_portrait', 'photo_portrait_2', 'photo_full_body', 'photo_full_body_2', 'video_path');
            foreach ($fileFields as $field) {
                if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                    $files[$field] = $_FILES[$field];
                }
            }

            $result = BeautyContestants::submitByToken($token, $postData, $files);

            if ($result['success']) {
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
