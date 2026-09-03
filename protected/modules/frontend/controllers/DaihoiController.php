<?php

/**
 * DaihoiController - Trang Website công khai của Đại hội Mường Thanh.
 *
 * Toàn bộ dữ liệu lấy qua Model Daihoi (gọi /api/daihoi/*).
 * Các action AJAX (jsonLive, jsonRankings...) làm proxy để giữ API key ở server,
 * phục vụ tự động làm mới các khối realtime (kết quả LIVE, bảng xếp hạng).
 */
class DaihoiController extends FrontEndController
{
    public function init()
    {
        parent::init();
        $this->layout = 'daihoi';
    }

    /**
     * Cho phép mọi người xem trang công khai và gọi các endpoint AJAX.
     */
    public function accessRules()
    {
        return array(
            array('allow',
                'actions' => array('index', 'jsonLive', 'jsonRecent', 'jsonRankings'),
                'users' => array('*'),
            ),
            array('deny', 'users' => array('*')),
        );
    }

    public function actionIndex()
    {
        // Portal SSO redirect về root kèm token (/?sso_token=...).
        // Lưu token vào SESSION rồi redirect PHÍA SERVER để token biến mất khỏi URL.
        $ssoToken = Yii::app()->request->getParam('sso_token');
        if ($ssoToken) {
            AuthHandler::logout();
            $userData = AuthHandler::handleCallback($ssoToken);
            if ($userData) {
                AuthHandler::fetchPermissions($ssoToken);
                AuthHandler::updateSessionWithProfile(AuthHandler::fetchUserProfile($ssoToken));
                Yii::app()->user->setFlash('success', 'Đăng nhập thành công. Xin chào ' . $userData['full_name']);
            } else {
                Yii::app()->user->setFlash('error', 'Token không hợp lệ hoặc đã hết hạn.');
            }
            // Về trang chủ công khai với URL sạch (không còn sso_token)
            $this->redirect(Yii::app()->homeUrl);
            return;
        }

        // Trạng thái đăng nhập để hiển thị nút phù hợp (chỉ kiểm tra session,
        // KHÔNG gọi Portal ở trang công khai để tránh làm chậm trang).
        $hasAdminAccess = AuthHandler::isAuthenticated() && PermissionHelper::hasAnyPermission();

        $this->render('index', array(
            'event' => Daihoi::getEvent(),
            'stats' => Daihoi::getStats(),
            'contents' => Daihoi::getContents(),
            'agenda' => Daihoi::getAgenda(),
            'liveMatches' => Daihoi::getLiveMatches(),
            'recentMatches' => Daihoi::getRecentMatches(),
            'rankings' => Daihoi::getRankings(5),
            'news' => Daihoi::getNews(6),
            'hasAdminAccess' => $hasAdminAccess,
        ));
    }

    public function actionJsonLive()
    {
        $this->renderJson(Daihoi::getLiveMatches());
    }

    public function actionJsonRecent()
    {
        $this->renderJson(Daihoi::getRecentMatches());
    }

    public function actionJsonRankings()
    {
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 5;
        $this->renderJson(Daihoi::getRankings($limit));
    }

    private function renderJson($data)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo CJSON::encode(array('success' => true, 'data' => $data));
        Yii::app()->end();
    }
}
