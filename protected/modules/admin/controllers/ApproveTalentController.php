<?php

class ApproveTalentController extends AdminController
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

        // Filter by video upload status
        $hasVideo = isset($_GET['has_video']) ? $_GET['has_video'] : '';
        if ($hasVideo !== '') {
            $entries = array_filter($entries, function($e) use ($hasVideo) {
                $hasVideoPath = !empty($e->video_path);
                return $hasVideo === '1' ? $hasVideoPath : !$hasVideoPath;
            });
            $entries = array_values($entries);
        }

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
            'show_id' => $model->show_id,
            'round_id' => $model->round_id,
            'round_name' => $model->round_name,
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
            'video_path_original' => $model->video_path,
            'document' => $model->document,
            'status' => $model->status,
            'status_label' => TalentEntries::getStatusLabel($model->status),
            'note' => $model->note,
            'created_at' => $model->created_at,
        );

        echo CJSON::encode(array('success' => true, 'data' => $data));
        Yii::app()->end();
    }

    /**
     * Lấy danh sách vòng thi của hội diễn chứa tiết mục (để gán khi duyệt)
     * show_id được tra từ chính tiết mục vì API list không trả về show_id
     */
    public function actionGetRounds($entry_id, $show_id = null)
    {
        $entry = TalentEntries::fetchFromApi($entry_id);
        if ($entry === null) {
            echo CJSON::encode(array('success' => false, 'message' => 'Không tìm thấy tiết mục'));
            Yii::app()->end();
        }
        $currentRoundId = $entry->round_id;

        // Tiết mục không lưu show_id trực tiếp -> suy ra hội diễn từ vòng hiện tại.
        // Fallback: show_id do người dùng đang lọc trên màn hình.
        if (empty($show_id) && !empty($currentRoundId)) {
            $currentRound = TalentRounds::fetchFromApi($currentRoundId);
            if ($currentRound !== null) {
                $show_id = $currentRound->talent_show_id;
            }
        }

        if (empty($show_id)) {
            echo CJSON::encode(array(
                'success' => true,
                'data' => array(),
                'message' => 'Chưa xác định được hội diễn. Hãy lọc theo hội diễn ở trên rồi thử lại.',
            ));
            Yii::app()->end();
        }

        $rounds = TalentRounds::getApiDataProvider(array(
            'talent_show_id' => $show_id,
            'sort' => 'round_order',
        ), 100)->getData();

        $data = array();
        foreach ($rounds as $r) {
            $data[] = array(
                'id' => $r->id,
                'name' => $r->name,
                'round_type' => TalentRounds::getRoundTypeLabel($r->round_type),
                'round_order' => $r->round_order,
                'is_current' => ($currentRoundId !== null && $r->id == $currentRoundId),
            );
        }

        echo CJSON::encode(array('success' => true, 'data' => $data));
        Yii::app()->end();
    }

    public function actionDebugRounds($entry_id)
    {
        header('Content-Type: application/json');
        $detailUrl = ApiEndpoints::url(ApiEndpoints::TALENT_ENTRY_DETAIL, array('id' => $entry_id));
        $detailRaw = ApiClient::get($detailUrl);
        $entry = TalentEntries::fetchFromApi($entry_id);
        $showId = $entry ? $entry->show_id : null;
        $roundsRaw = ApiClient::get(ApiEndpoints::TALENT_ROUND_LIST, array('talent_show_id' => $showId, 'per_page' => 100));
        $roundsAll = ApiClient::get(ApiEndpoints::TALENT_ROUND_LIST, array('per_page' => 100));
        echo json_encode(array(
            'entry_show_id' => $showId,
            'entry_round_id' => $entry ? $entry->round_id : null,
            'detail_raw_keys' => $entry && isset($detailRaw['data']) ? array_keys(isset($detailRaw['data']['data']) ? $detailRaw['data']['data'] : $detailRaw['data']) : null,
            'rounds_filtered' => $roundsRaw,
            'rounds_all' => $roundsAll,
        ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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

        $result = TalentEntries::approveWithRound($id, $roundId);

        if ($result['success']) {
            $message = !empty($roundId) ? 'Đã duyệt và gán tiết mục vào vòng thi' : 'Đã duyệt tiết mục';
            echo CJSON::encode(array('success' => true, 'message' => $message));
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
}
