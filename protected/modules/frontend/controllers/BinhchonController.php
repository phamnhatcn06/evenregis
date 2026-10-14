<?php

/**
 * BinhchonController - Cổng bình chọn Miss Vòng chung kết (PUBLIC).
 *
 * Khách đăng nhập bằng tài khoản Google (Google Sign-In) rồi bình chọn cho các
 * thí sinh vào chung kết. Mỗi tài khoản Google được tối đa N lượt, mỗi thí sinh
 * tối đa 1 lượt — chặn được việc đổi trình duyệt/thiết bị vì khóa định danh là
 * tài khoản Google, không phải cookie.
 *
 * LƯU Ý (prototype): phiếu bình chọn hiện lưu tạm trong SESSION để chạy thử
 * luồng. Khi backend có bảng/endpoint beauty_votes, thay phần lưu/đếm bằng
 * BeautyVotes::castVote() / getTallies() qua ApiClient (xem TODO bên dưới).
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
        $contestId = $cfg['contest_id'];

        $finalists = $this->loadFinalists($contestId);
        $tallies = $this->getTallies($contestId);

        // Gắn số phiếu hiện tại vào từng thí sinh
        foreach ($finalists as &$f) {
            $cid = $f['contestant_id'];
            $f['vote_count'] = isset($tallies[$cid]) ? $tallies[$cid] : 0;
        }
        unset($f);

        $voter = $this->getVoter();
        $votedIds = $this->getVotedContestantIds($contestId);

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
            'googleClientId' => GoogleAuthHelper::getClientId(),
            'maxVotes' => $cfg['max_votes_per_voter'],
            'voter' => $voter,
            'votedIds' => $votedIds,
            'remaining' => max(0, $cfg['max_votes_per_voter'] - count($votedIds)),
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

        $cfg = $this->voteConfig();
        $contestId = $cfg['contest_id'];
        $contestantId = isset($_POST['contestant_id']) ? (int) $_POST['contestant_id'] : 0;
        $credential = isset($_POST['credential']) ? $_POST['credential'] : null;

        // 1. Xác thực tài khoản Google
        $account = GoogleAuthHelper::verify($credential);
        if ($account === null || empty($account['sub'])) {
            $this->jsonResponse(false, 'Xác thực Google thất bại. Vui lòng đăng nhập lại.');
        }
        $this->setVoter($account);

        if ($contestantId <= 0) {
            $this->jsonResponse(false, 'Thiếu thông tin thí sinh.');
        }

        // 2. Kiểm tra hạn mức
        $votedIds = $this->getVotedContestantIds($contestId);
        if (in_array($contestantId, $votedIds)) {
            $this->jsonResponse(false, 'Bạn đã bình chọn cho thí sinh này rồi.');
        }
        if (count($votedIds) >= $cfg['max_votes_per_voter']) {
            $this->jsonResponse(false, 'Bạn đã dùng hết ' . $cfg['max_votes_per_voter'] . ' lượt bình chọn.');
        }

        // 3. Ghi phiếu
        // TODO (khi có backend): BeautyVotes::castVote($contestId, $contestantId,
        //      $account['sub'], 'google', $account['email']);
        $this->recordVote($contestId, $contestantId);

        $tallies = $this->getTallies($contestId);
        $votedIds = $this->getVotedContestantIds($contestId);

        $this->jsonResponse(true, 'Bình chọn thành công!', array(
            'contestant_id' => $contestantId,
            'vote_count' => isset($tallies[$contestantId]) ? $tallies[$contestantId] : 1,
            'remaining' => max(0, $cfg['max_votes_per_voter'] - count($votedIds)),
            'voter' => $this->getVoter(),
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
     * Lấy danh sách thí sinh chung kết, chuẩn hoá field cần cho view.
     * Nếu backend chưa chạy/chưa có dữ liệu → dùng danh sách demo để chạy thử.
     */
    protected function loadFinalists($contestId)
    {
        $raw = array();
        try {
            $raw = BeautyContestants::getFinalists($contestId);
        } catch (Exception $e) {
            $raw = array();
        }

        $list = array();
        foreach ($raw as $r) {
            $cid = isset($r['contestant_id']) ? $r['contestant_id'] : (isset($r['id']) ? $r['id'] : null);
            if ($cid === null) {
                continue;
            }
            $list[] = array(
                'contestant_id' => (int) $cid,
                'name' => isset($r['attendee_name']) ? $r['attendee_name'] : (isset($r['name']) ? $r['name'] : 'Thí sinh'),
                'number' => isset($r['candidate_number']) ? $r['candidate_number'] : (isset($r['contestant_number']) ? $r['contestant_number'] : ''),
                'unit' => isset($r['property_name']) ? $r['property_name'] : (isset($r['unit_label']) ? $r['unit_label'] : ''),
                'photo' => $this->resolvePhoto(isset($r['photo_portrait']) ? $r['photo_portrait'] : ''),
            );
        }

        if (empty($list)) {
            $list = $this->demoFinalists();
        }
        return $list;
    }

    protected function demoFinalists()
    {
        // PROTOTYPE: dữ liệu mẫu để xem thử giao diện khi chưa có dữ liệu thật.
        $demo = array();
        $names = array('Nguyễn Thị An', 'Trần Thu Hà', 'Lê Minh Châu', 'Phạm Khánh Linh', 'Võ Mỹ Duyên', 'Đỗ Phương Thảo');
        foreach ($names as $i => $n) {
            $demo[] = array(
                'contestant_id' => 9000 + $i + 1,
                'name' => $n,
                'number' => 'SBD' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                'unit' => 'Đơn vị demo',
                'photo' => 'https://placehold.co/400x500/eaeaea/999?text=' . rawurlencode('Thí sinh ' . ($i + 1)),
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

    // ---- Lưu trữ tạm trong session (prototype) ----

    protected function sessionKey($suffix, $contestId)
    {
        $voter = $this->getVoter();
        $sub = $voter ? $voter['sub'] : 'anon';
        return 'missvote.' . $contestId . '.' . $suffix . '.' . $sub;
    }

    protected function getVotedContestantIds($contestId)
    {
        $voter = $this->getVoter();
        if (!$voter) {
            return array();
        }
        $key = $this->sessionKey('voted', $contestId);
        $ids = Yii::app()->session[$key];
        return is_array($ids) ? $ids : array();
    }

    protected function recordVote($contestId, $contestantId)
    {
        // Đánh dấu người này đã bình chọn thí sinh
        $key = $this->sessionKey('voted', $contestId);
        $ids = $this->getVotedContestantIds($contestId);
        $ids[] = $contestantId;
        Yii::app()->session[$key] = $ids;

        // Tăng tổng phiếu (prototype: chung cho session)
        $tallies = $this->getTallies($contestId);
        $tallies[$contestantId] = (isset($tallies[$contestantId]) ? $tallies[$contestantId] : 0) + 1;
        Yii::app()->session['missvote.' . $contestId . '.tallies'] = $tallies;
    }

    protected function getTallies($contestId)
    {
        // TODO (khi có backend): lấy tổng phiếu realtime qua BeautyVotes::getTallies($contestId)
        $t = Yii::app()->session['missvote.' . $contestId . '.tallies'];
        return is_array($t) ? $t : array();
    }

    protected function getVoter()
    {
        $v = Yii::app()->session['missvote.voter'];
        return is_array($v) ? $v : null;
    }

    protected function setVoter($account)
    {
        Yii::app()->session['missvote.voter'] = array(
            'sub' => $account['sub'],
            'email' => $account['email'],
            'name' => $account['name'],
            'picture' => $account['picture'],
        );
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
