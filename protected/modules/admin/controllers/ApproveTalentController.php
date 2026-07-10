<?php

class ApproveTalentController extends AdminController
{
    public function init()
    {
        parent::init();
        $this->publicActions[] = 'index';
        $this->publicActions[] = 'getDetail';
        $this->publicActions[] = 'approve';
        $this->publicActions[] = 'reject';
    }

    public function actionAdmin()
    {
        $params = array(
            'with' => 'property,category,members',
        );

        if (isset($_GET['show_id']) && $_GET['show_id'] !== '') {
            $params['show_id'] = $_GET['show_id'];
        }
        if (isset($_GET['category_id']) && $_GET['category_id'] !== '') {
            $params['category_id'] = $_GET['category_id'];
        }
        if (isset($_GET['status']) && $_GET['status'] !== '') {
            $params['status'] = $_GET['status'];
        }

        $dataProvider = TalentEntries::getApiDataProvider($params, 1000);
        $entries = $dataProvider->getData();
        $shows = $this->getActiveShows();
        $categories = $this->getCategories();

        $this->render('index', array(
            'entries' => $entries,
            'shows' => $shows,
            'categories' => $categories,
        ));
    }

    public function actionGetDetail($id)
    {
        $model = TalentEntries::fetchFromApi($id);
        if ($model === null) {
            echo CJSON::encode(array('success' => false, 'message' => 'Không tìm thấy tiết mục'));
            Yii::app()->end();
        }

        $data = array(
            'id' => $model->id,
            'title' => $model->title,
            'property_name' => $model->property_name,
            'category_name' => $model->category_name,
            'show_name' => $model->show_name,
            'description' => $model->description,
            'content' => $model->content,
            'duration_seconds' => $model->duration_seconds,
            'director' => $model->director,
            'director_phone' => $model->director_phone,
            'origin' => $model->origin,
            'participant_count' => $model->participant_count,
            'is_alliance_team' => $model->is_alliance_team,
            'music_path' => $model->music_path,
            'video_path' => $this->getOptimizedVideoPath($model->video_path),
            'document' => $model->document,
            'status' => $model->status,
            'status_label' => TalentEntries::getStatusLabel($model->status),
            'note' => $model->note,
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

        $model = TalentEntries::fetchFromApi($id);
        if ($model === null) {
            echo CJSON::encode(array('success' => false, 'message' => 'Không tìm thấy tiết mục'));
            Yii::app()->end();
        }

        $model->status = TalentEntries::STATUS_APPROVED;
        $result = $model->updateViaApi();

        if ($result['success']) {
            echo CJSON::encode(array('success' => true, 'message' => 'Đã duyệt tiết mục'));
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

        $model = TalentEntries::fetchFromApi($id);
        if ($model === null) {
            echo CJSON::encode(array('success' => false, 'message' => 'Không tìm thấy tiết mục'));
            Yii::app()->end();
        }

        $model->status = TalentEntries::STATUS_REJECTED;
        if (!empty($reason)) {
            $model->note = $reason;
        }
        $result = $model->updateViaApi();

        if ($result['success']) {
            echo CJSON::encode(array('success' => true, 'message' => 'Đã từ chối tiết mục'));
        } else {
            echo CJSON::encode(array('success' => false, 'message' => $result['error'] ?: 'Có lỗi xảy ra'));
        }
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

    protected function getCategories()
    {
        $result = ApiClient::get(ApiEndpoints::TALENT_CATEGORY_LIST, array(
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
