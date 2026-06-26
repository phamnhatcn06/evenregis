<?php

class ApproveMissController extends AdminController
{
    public function init()
    {
        parent::init();
        $this->publicActions[] = 'index';
        $this->publicActions[] = 'getDetail';
        $this->publicActions[] = 'approve';
        $this->publicActions[] = 'reject';
    }

    public function actionIndex()
    {
        $params = array(
            'with' => 'attendee,attendee.property,attendee.property.regional,contest',
            'sort' => 'attendee.property.regional.code',
        );

        if (isset($_GET['contest_id']) && $_GET['contest_id'] !== '') {
            $params['contest_id'] = $_GET['contest_id'];
        }
        if (isset($_GET['status']) && $_GET['status'] !== '') {
            $params['status'] = $_GET['status'];
        }

        $dataProvider = BeautyContestants::getApiDataProvider($params, 1000);
        $contestants = $dataProvider->getData();
        $contests = $this->getActiveContests();

        $this->render('index', array(
            'contestants' => $contestants,
            'contests' => $contests,
        ));
    }

    public function actionGetDetail($id)
    {
        $model = BeautyContestants::fetchFromApi($id);
        if ($model === null) {
            echo CJSON::encode(array('success' => false, 'message' => 'Không tìm thấy thí sinh'));
            Yii::app()->end();
        }

        $data = array(
            'id' => $model->id,
            'attendee_name' => $model->attendee_name,
            'property_name' => $model->property_name,
            'contest_name' => $model->contest_name,
            'height_cm' => $model->height_cm,
            'weight_kg' => $model->weight_kg,
            'measurements' => $model->measurements,
            'talent' => $model->talent,
            'bio' => $model->bio,
            'personal_email' => $model->personal_email,
            'status' => $model->status,
            'status_label' => BeautyContestants::getStatusLabel($model->status),
            'photo_portrait' => $model->photo_portrait,
            'photo_portrait_2' => $model->photo_portrait_2,
            'photo_full_body' => $model->photo_full_body,
            'photo_full_body_2' => $model->photo_full_body_2,
            'video_path' => $model->video_path,
            'submitted_at' => $model->submitted_at,
            'created_at' => $model->created_at,
        );

        echo CJSON::encode(array('success' => true, 'data' => $data));
        Yii::app()->end();
    }

    public function actionApprove()
    {
        if (!Yii::app()->request->isPostRequest || !Yii::app()->request->isAjaxRequest) {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ');
        }

        $id = Yii::app()->request->getPost('id');
        if (empty($id)) {
            echo CJSON::encode(array('success' => false, 'message' => 'Thiếu ID'));
            Yii::app()->end();
        }

        $model = BeautyContestants::fetchFromApi($id);
        if ($model === null) {
            echo CJSON::encode(array('success' => false, 'message' => 'Không tìm thấy thí sinh'));
            Yii::app()->end();
        }

        $model->status = BeautyContestants::STATUS_CONFIRMED;
        $result = $model->updateViaApi();

        if ($result['success']) {
            echo CJSON::encode(array('success' => true, 'message' => 'Đã duyệt thí sinh'));
        } else {
            echo CJSON::encode(array('success' => false, 'message' => $result['error'] ?: 'Có lỗi xảy ra'));
        }
        Yii::app()->end();
    }

    public function actionReject()
    {
        if (!Yii::app()->request->isPostRequest || !Yii::app()->request->isAjaxRequest) {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ');
        }

        $id = Yii::app()->request->getPost('id');
        $reason = Yii::app()->request->getPost('reason', '');

        if (empty($id)) {
            echo CJSON::encode(array('success' => false, 'message' => 'Thiếu ID'));
            Yii::app()->end();
        }

        $model = BeautyContestants::fetchFromApi($id);
        if ($model === null) {
            echo CJSON::encode(array('success' => false, 'message' => 'Không tìm thấy thí sinh'));
            Yii::app()->end();
        }

        $model->status = BeautyContestants::STATUS_DISQUALIFIED;
        $result = $model->updateViaApi();

        if ($result['success']) {
            echo CJSON::encode(array('success' => true, 'message' => 'Đã từ chối thí sinh'));
        } else {
            echo CJSON::encode(array('success' => false, 'message' => $result['error'] ?: 'Có lỗi xảy ra'));
        }
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
}
