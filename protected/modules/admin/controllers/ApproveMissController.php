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

        // Filter theo property_id phía PHP
        $filterPropertyId = isset($_GET['property_id']) && $_GET['property_id'] !== '' ? $_GET['property_id'] : null;
        if ($filterPropertyId !== null) {
            $contestants = array_filter($contestants, function ($c) use ($filterPropertyId) {
                return isset($c->property_id) && $c->property_id == $filterPropertyId;
            });
            $contestants = array_values($contestants);
        }

        // Bổ sung bộ phận (department) và năm sinh cho từng thí sinh
        $this->enrichContestantsWithAttendeeInfo($contestants);

        // Map contestant id => object để tra cứu khi gom nhóm theo vòng
        $contestantMap = array();
        foreach ($contestants as $c) {
            $contestantMap[$c->id] = $c;
        }

        // Lấy danh sách vòng thi (sắp xếp theo round_order)
        $roundParams = array('sort' => 'round_order');
        if (isset($_GET['contest_id']) && $_GET['contest_id'] !== '') {
            $roundParams['contest_id'] = $_GET['contest_id'];
        }
        $rounds = BeautyRounds::getApiDataProvider($roundParams, 100)->getData();

        // Xác định vòng cao nhất mà mỗi thí sinh đang tham gia.
        // $rounds đã sắp theo round_order tăng dần nên vòng ghi nhận sau cùng
        // chính là vòng mới nhất -> thí sinh chỉ hiển thị ở vòng đó.
        $assignedIds = array();
        $latestRoundOf = array();
        foreach ($rounds as $round) {
            $results = BeautyRoundResults::getApiDataProvider(array(
                'round_id' => $round->id,
            ), 1000)->getData();

            foreach ($results as $res) {
                if (isset($contestantMap[$res->registration_id])) {
                    $latestRoundOf[$res->registration_id] = $round->id;
                    $assignedIds[$res->registration_id] = true;
                }
            }
        }

        // Gom nhóm thí sinh theo vòng cao nhất -> tabs
        $roundTabs = array();
        foreach ($rounds as $round) {
            $items = array();
            foreach ($contestants as $c) {
                if (isset($latestRoundOf[$c->id]) && $latestRoundOf[$c->id] == $round->id) {
                    $items[] = $c;
                }
            }

            $roundTabs[] = array(
                'id' => $round->id,
                'name' => $round->name,
                'contest_name' => isset($round->contest_name) ? $round->contest_name : '',
                'round_type' => isset($round->round_type) ? $round->round_type : '',
                'contestants' => $items,
            );
        }

        // Thí sinh chưa được gán vào vòng nào
        $unassigned = array();
        foreach ($contestants as $c) {
            if (!isset($assignedIds[$c->id])) {
                $unassigned[] = $c;
            }
        }

        $this->render('index', array(
            'contests' => $contests,
            'properties' => $properties,
            'roundTabs' => $roundTabs,
            'unassigned' => $unassigned,
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

        // Trả về tất cả vòng để có thể gán vào bất kỳ vòng nào; vòng đã gán được
        // đánh dấu bằng cờ `assigned` (vẫn chọn lại được).
        $data = array();
        foreach ($rounds as $r) {
            $data[] = array(
                'id' => $r->id,
                'name' => $r->name,
                'round_type' => $r->round_type,
                'round_order' => $r->round_order,
                'assigned' => in_array($r->id, $assignedRoundIds),
            );
        }

        echo CJSON::encode(array('success' => true, 'data' => $data));
        Yii::app()->end();
    }

    public function actionApprove()
    {
        if (!Yii::app()->request->isPostRequest) {
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

        $result = $model->updateStatusViaApi(BeautyContestants::STATUS_CONFIRMED);

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
        if (!Yii::app()->request->isPostRequest) {
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

        $result = $model->updateStatusViaApi(BeautyContestants::STATUS_DISQUALIFIED);

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

    /**
     * Bổ sung thông tin bộ phận (department) và năm sinh cho danh sách thí sinh.
     * Dữ liệu lấy từ người tham dự (attendees) tương ứng — chỉ cần 1 lần gọi API
     * rồi map theo attendee_id để tránh N+1.
     *
     * Lưu ý: năm sinh (birthday) lấy từ nhân viên (staff) tương ứng của attendee.
     * Field này chỉ hiển thị khi API attendees/staff trả về `birthday`.
     */
    protected function enrichContestantsWithAttendeeInfo(&$contestants)
    {
        if (empty($contestants)) {
            return;
        }

        // Gom attendee_id cần tra cứu
        $attendeeIds = array();
        foreach ($contestants as $c) {
            if (!empty($c->attendee_id)) {
                $attendeeIds[$c->attendee_id] = true;
            }
        }
        if (empty($attendeeIds)) {
            return;
        }

        // Lấy danh sách người tham dự (1 lần) rồi map theo id
        $attendees = Attendees::getApiDataProvider(array(), 10000)->getData();
        $attendeeMap = array();
        foreach ($attendees as $a) {
            $attendeeMap[$a->id] = $a;
        }

        foreach ($contestants as $c) {
            if (empty($c->attendee_id) || !isset($attendeeMap[$c->attendee_id])) {
                continue;
            }
            $a = $attendeeMap[$c->attendee_id];
            $c->department_name = !empty($a->department_name) ? $a->department_name : $a->division_name;
            $c->division_name = $a->division_name;
            $c->birthday = $a->birthday;
        }
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

        $pos = strpos($photoUrl, '/uploads/miss/');
        if ($pos !== false) {
            $cleanPath = substr($photoUrl, $pos + strlen('/uploads/miss/'));
            return Yii::app()->createUrl('/admin/missFile/view') . '?path=' . urlencode($cleanPath) . '&w=' . $width;
        }

        return $photoUrl;
    }
}
