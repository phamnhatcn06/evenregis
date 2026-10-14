<?php

/**
 * BinhchonController - Cổng bình chọn Miss Vòng chung kết (PUBLIC).
 *
 * Khách đăng nhập bằng tài khoản Google (Google Sign-In) rồi bình chọn cho các
 * thí sinh vào chung kết. Mỗi tài khoản Google được tối đa N lượt, mỗi thí sinh
 * tối đa 1 lượt — chặn được việc đổi trình duyệt/thiết bị vì khóa định danh là
 * tài khoản Google, không phải cookie.
 *
 * Controller xác thực Google ID token (GoogleAuthHelper) rồi chuyển voter_key
 * (Google sub) xuống backend qua model BeautyVotes để áp hạn mức + lưu phiếu.
 */
class BinhchonController extends CController
{
    public $layout = '//layouts/frontend';

    public function filters()
    {
        return array();
    }

    /**
     * Trang danh sách thí sinh chung kết + nút bình chọn.
     */
    public function actionIndex()
    {
        $cfg = $this->voteConfig();
        $pageData = $this->loadVotePageData($cfg['contest_id']);
        $finalists = $pageData['finalists'];
        $gate = $pageData['gate'];

        // Thư viện Google Identity Services (bắt buộc tải từ máy chủ Google)
        Yii::app()->clientScript->registerScriptFile(
            'https://accounts.google.com/gsi/client',
            CClientScript::POS_HEAD
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->theme->baseUrl . '/assets/js/pages/binhchon-index.js',
            CClientScript::POS_END
        );

        $this->render('index', array(
            'finalists' => $finalists,
            'gate' => $gate,
            'googleClientId' => GoogleAuthHelper::getClientId(),
            'maxVotes' => $cfg['max_votes_per_voter'],
        ));
    }

    /**
     * Ghi nhận một lượt bình chọn (AJAX POST, trả JSON).
     * Yêu cầu: credential (Google ID token) + contestant_id.
     */
    public function actionVote()
    {
        if (!Yii::app()->request->isPostRequest) {
            $this->jsonResponse(false, 'Phương thức không hợp lệ.');
        }

        $account = $this->requireGoogleAccount();
        $cfg = $this->voteConfig();
        $contestantId = isset($_POST['contestant_id']) ? (int) $_POST['contestant_id'] : 0;

        if ($contestantId <= 0) {
            $this->jsonResponse(false, 'Thiếu thông tin thí sinh.');
        }

        $response = BeautyVotes::castVote(array(
            'contest_id' => $cfg['contest_id'],
            'contestant_id' => $contestantId,
            'voter_key' => $account['sub'],
            'voter_key_type' => 'google',
            'voter_email' => $account['email'],
            'voter_name' => $account['name'],
            'max_votes' => $cfg['max_votes_per_voter'],
        ));

        $success = !empty($response['success']);
        $this->jsonResponse($success, isset($response['message']) ? $response['message'] : '', array(
            'contestant_id' => $contestantId,
            'vote_count' => isset($response['vote_count']) ? (int) $response['vote_count'] : null,
            'remaining' => isset($response['remaining']) ? (int) $response['remaining'] : null,
            'voted_ids' => isset($response['voted_ids']) ? $response['voted_ids'] : array(),
        ));
    }

    /**
     * Trạng thái bình chọn của người vừa đăng nhập (AJAX POST, trả JSON).
     * Yêu cầu: credential (Google ID token).
     */
    public function actionStatus()
    {
        if (!Yii::app()->request->isPostRequest) {
            $this->jsonResponse(false, 'Phương thức không hợp lệ.');
        }

        $account = $this->requireGoogleAccount();
        $cfg = $this->voteConfig();

        $status = BeautyVotes::getVoterStatus($cfg['contest_id'], $account['sub'], $cfg['max_votes_per_voter']);

        $this->jsonResponse(true, '', array(
            'name' => $account['name'] ?: $account['email'],
            'voted_ids' => $status['voted_ids'],
            'remaining' => $status['remaining'],
        ));
    }

    // ===================== Helpers =====================

    protected function voteConfig()
    {
        $cfg = Yii::app()->params['missVote'];
        return array(
            'contest_id' => isset($cfg['contest_id']) ? (int) $cfg['contest_id'] : 1,
            'max_votes_per_voter' => isset($cfg['max_votes_per_voter']) ? (int) $cfg['max_votes_per_voter'] : 5,
            'max_per_contestant' => isset($cfg['max_per_contestant']) ? (int) $cfg['max_per_contestant'] : 1,
        );
    }

    /**
     * Xác thực tài khoản Google từ credential trong POST, dừng request nếu lỗi.
     * @return array thông tin tài khoản
     */
    protected function requireGoogleAccount()
    {
        $credential = isset($_POST['credential']) ? $_POST['credential'] : null;
        $account = GoogleAuthHelper::verify($credential);
        if ($account === null || empty($account['sub'])) {
            $this->jsonResponse(false, 'Xác thực Google thất bại. Vui lòng đăng nhập lại.');
        }
        return $account;
    }

    /**
     * Lấy danh sách thí sinh chung kết (chuẩn hoá field cho view).
     * Nếu backend chưa có dữ liệu → dùng danh sách demo để xem thử giao diện.
     */
    protected function loadVotePageData($contestId)
    {
        $gate = array('is_open' => true, 'message' => '');
        $raw = array();
        try {
            $pageData = BeautyVotes::getVotePageData($contestId);
            $raw = $pageData['finalists'];
            $gate = $pageData['gate'];
        } catch (Exception $e) {
            $raw = array();
        }

        $list = array();
        foreach ($raw as $r) {
            if (!isset($r['contestant_id'])) {
                continue;
            }
            $list[] = array(
                'contestant_id' => (int) $r['contestant_id'],
                'name' => isset($r['name']) ? $r['name'] : 'Thí sinh',
                'number' => isset($r['candidate_number']) ? $r['candidate_number'] : '',
                'unit' => isset($r['unit']) ? $r['unit'] : '',
                'photo' => $this->resolvePhoto(isset($r['photo_portrait']) ? $r['photo_portrait'] : ''),
                'vote_count' => isset($r['vote_count']) ? (int) $r['vote_count'] : 0,
            );
        }

        if (empty($list)) {
            $list = $this->demoFinalists();
        }
        return array('gate' => $gate, 'finalists' => $list);
    }

    protected function demoFinalists()
    {
        // Dữ liệu mẫu để xem thử giao diện khi backend chưa có thí sinh chung kết.
        $demo = array();
        $names = array('Nguyễn Thị An', 'Trần Thu Hà', 'Lê Minh Châu', 'Phạm Khánh Linh', 'Võ Mỹ Duyên', 'Đỗ Phương Thảo');
        foreach ($names as $i => $n) {
            $demo[] = array(
                'contestant_id' => 9000 + $i + 1,
                'name' => $n,
                'number' => 'SBD' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                'unit' => 'Đơn vị demo',
                'photo' => 'https://placehold.co/400x500/eaeaea/999?text=' . rawurlencode('Thí sinh ' . ($i + 1)),
                'vote_count' => 0,
            );
        }
        return $demo;
    }

    protected function resolvePhoto($path)
    {
        if (empty($path)) {
            return 'https://placehold.co/400x500/eaeaea/999?text=' . rawurlencode('Chưa có ảnh');
        }
        if (strpos($path, 'http') === 0) {
            return $path;
        }
        return Yii::app()->request->baseUrl . '/' . ltrim($path, '/');
    }

    protected function jsonResponse($success, $message, $extra = array())
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_merge(array(
            'success' => $success,
            'message' => $message,
        ), $extra));
        Yii::app()->end();
    }
}
