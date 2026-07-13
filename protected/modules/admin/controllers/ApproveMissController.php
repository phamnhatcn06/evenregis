<?php

class ApproveMissController extends AdminController
{
    public function init()
    {
        parent::init();
        $this->publicActions[] = 'index';
        $this->publicActions[] = 'getDetail';
        $this->publicActions[] = 'getRounds';
        $this->publicActions[] = 'approve';
        $this->publicActions[] = 'reject';
    }

    public function actionAdmin()
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
        if (isset($_GET['keyword']) && $_GET['keyword'] !== '') {
            $params['keyword'] = $_GET['keyword'];
        }

        $dataProvider = BeautyContestants::getApiDataProvider($params, 1000);
        $contestants = $dataProvider->getData();

        $contests = $this->getActiveContests();
        $properties = $this->getPropertiesWithContestants($contestants);
        $rounds = $this->getRoundsList();

        // Filter theo property_id phía PHP
        $filterPropertyId = isset($_GET['property_id']) && $_GET['property_id'] !== '' ? $_GET['property_id'] : null;
        if ($filterPropertyId !== null) {
            $contestants = array_filter($contestants, function ($c) use ($filterPropertyId) {
                return isset($c->property_id) && $c->property_id == $filterPropertyId;
            });
            $contestants = array_values($contestants);
        }

        // Filter theo round_id - chỉ lấy thí sinh đã gán vào vòng thi
        $filterRoundId = isset($_GET['round_id']) && $_GET['round_id'] !== '' ? $_GET['round_id'] : null;
        if ($filterRoundId !== null) {
            $roundResults = BeautyRoundResults::getApiDataProvider(array(
                'round_id' => $filterRoundId,
            ), 1000)->getData();
            $contestantIdsInRound = array();
            foreach ($roundResults as $r) {
                $contestantIdsInRound[] = $r->registration_id;
            }
            $contestants = array_filter($contestants, function ($c) use ($contestantIdsInRound) {
                return in_array($c->id, $contestantIdsInRound);
            });
            $contestants = array_values($contestants);
        }

        $this->render('index', array(
            'contestants' => $contestants,
            'contests' => $contests,
            'properties' => $properties,
            'rounds' => $rounds,
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
            'contest_id' => $model->contest_id,
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
            'photo_portrait' => $this->getOptimizedPhotoUrl($model->photo_portrait, 800),
            'photo_portrait_2' => $this->getOptimizedPhotoUrl($model->photo_portrait_2, 800),
            'photo_full_body' => $this->getOptimizedPhotoUrl($model->photo_full_body, 800),
            'photo_full_body_2' => $this->getOptimizedPhotoUrl($model->photo_full_body_2, 800),
            'video_path' => $this->getOptimizedVideoPath($model->video_path),
            'video_path_original' => $model->video_path,
            'submitted_at' => $model->submitted_at,
            'created_at' => $model->created_at,
        );

        echo CJSON::encode(array('success' => true, 'data' => $data));
        Yii::app()->end();
    }

    public function actionGetRounds($contest_id, $contestant_id = null)
    {
        $rounds = BeautyRounds::getApiDataProvider(array(
            'contest_id' => $contest_id,
            'sort' => 'round_order',
        ), 100)->getData();

        $assignedRoundIds = array();
        if ($contestant_id) {
            $results = BeautyRoundResults::getApiDataProvider(array(
                'registration_id' => $contestant_id,
            ), 100)->getData();
            foreach ($results as $r) {
                $assignedRoundIds[] = $r->round_id;
            }
        }

        $data = array();
        foreach ($rounds as $r) {
            if (in_array($r->id, $assignedRoundIds)) {
                continue;
            }
            $data[] = array(
                'id' => $r->id,
                'name' => $r->name,
                'round_type' => $r->round_type,
                'round_order' => $r->round_order,
            );
        }

        echo CJSON::encode(array('success' => true, 'data' => $data));
        Yii::app()->end();
    }

    public function actionApprove()
    {
        if (!Yii::app()->request->isPostRequest || !Yii::app()->request->isAjaxRequest) {
            throw new CHttpException(400, 'Yêu cầu không hợp lệ');
        }

        $id = Yii::app()->request->getPost('id');
        $roundId = Yii::app()->request->getPost('round_id');

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

        if (!$result['success']) {
            echo CJSON::encode(array('success' => false, 'message' => $result['error'] ?: 'Có lỗi xảy ra'));
            Yii::app()->end();
        }

        if (!empty($roundId)) {
            $assignResult = BeautyRoundResults::assignContestants($roundId, array($id));
            if (!$assignResult['success']) {
                echo CJSON::encode(array(
                    'success' => true,
                    'message' => 'Đã duyệt thí sinh nhưng không thể gán vào vòng thi: ' . ($assignResult['error'] ?: '')
                ));
                Yii::app()->end();
            }
        }

        echo CJSON::encode(array('success' => true, 'message' => 'Đã duyệt và gán thí sinh vào vòng thi'));
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

    protected function getPropertiesWithContestants($contestants = null)
    {
        $list = array();

        if ($contestants === null) {
            $result = ApiClient::get(ApiEndpoints::BEAUTY_CONTESTANT_LIST, array(
                'per_page' => 1000,
            ));
            if ($result['success'] && isset($result['data']['data'])) {
                foreach ($result['data']['data'] as $item) {
                    if (!empty($item['property_id']) && !empty($item['property_name'])) {
                        $list[$item['property_id']] = $item['property_name'];
                    }
                }
            }
        } else {
            foreach ($contestants as $c) {
                if (!empty($c->property_id) && !empty($c->property_name)) {
                    $list[$c->property_id] = $c->property_name;
                }
            }
        }

        asort($list);
        return $list;
    }

    protected function getRoundsList()
    {
        $result = ApiClient::get(ApiEndpoints::BEAUTY_ROUND_LIST, array(
            'per_page' => 100,
            'sort' => 'round_order',
        ));

        $list = array();
        if ($result['success'] && isset($result['data']['data'])) {
            foreach ($result['data']['data'] as $item) {
                $contestName = isset($item['contest_name']) ? $item['contest_name'] . ' - ' : '';
                $list[$item['id']] = $contestName . $item['name'];
            }
        }
        return $list;
    }

    /**
     * Lấy đường dẫn video đã tối ưu (_web) nếu tồn tại
     */
    protected function getOptimizedVideoPath($videoPath)
    {
        if (empty($videoPath)) {
            return $videoPath;
        }

        $basePath = Yii::getPathOfAlias('webroot');
        $relativePath = ltrim(str_replace(Yii::app()->baseUrl, '', $videoPath), '/');
        $fullPath = $basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

        $pathInfo = pathinfo($fullPath);
        $webFile = $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $pathInfo['filename'] . '_web.' . $pathInfo['extension'];

        if (file_exists($webFile)) {
            $webRelative = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_web.' . $pathInfo['extension'];
            return str_replace($basePath, Yii::app()->baseUrl, str_replace(DIRECTORY_SEPARATOR, '/', $webRelative));
        }

        return $videoPath;
    }

    /**
     * Lấy đường dẫn ảnh tối ưu (thumbnail) qua controller
     */
    protected function getOptimizedPhotoUrl($photoUrl, $width = 800)
    {
        if (empty($photoUrl)) {
            return $photoUrl;
        }

        if (strpos($photoUrl, '/uploads/miss/') === 0) {
            $cleanPath = str_replace('/uploads/miss/', '', $photoUrl);
            return Yii::app()->createUrl('/admin/missFile/view', array('path' => $cleanPath, 'w' => $width));
        }

        return $photoUrl;
    }
}
