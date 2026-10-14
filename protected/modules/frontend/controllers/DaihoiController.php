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
                'actions' => array('index', 'agenda', 'schedule', 'tiecWelcome', 'lichThamQuan', 'tiecGala', 'hoTro', 'doanThuKy', 'soDoDiaDiem', 'news', 'newsDetail', 'jsonLive', 'jsonRecent', 'jsonRankings'),
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
            'newsCategories' => Daihoi::getNewsCategories(),
            'slides' => Daihoi::getSlides(),
            'albums' => Daihoi::getAlbums(8),
            'hasAdminAccess' => $hasAdminAccess,
        ));
    }

    /**
     * Trang Lịch trình đại hội đầy đủ (toàn bộ agenda theo ngày).
     */
    public function actionAgenda()
    {
        $this->frontTitle = 'Lịch trình Đại hội';
        $this->render('agenda', array(
            'event' => Daihoi::getEvent(),
            'agenda' => Daihoi::getAgenda(),
        ));
    }

    /**
     * Trang Lịch thi đấu: trận đang diễn ra, sắp/vừa diễn ra và bảng xếp hạng.
     */
    public function actionSchedule()
    {
        $this->frontTitle = 'Lịch thi đấu & Kết quả thể thao';
        $event = Daihoi::getEvent();
        $sportId = Yii::app()->request->getParam('sport_id');
        $status = Yii::app()->request->getParam('status');
        $date = Yii::app()->request->getParam('date');

        $tournament = Daihoi::getTournamentSchedule(isset($event['id']) ? $event['id'] : null, $sportId);

        $this->render('schedule', array(
            'event' => $event,
            'tournament' => $tournament,
            'liveMatches' => $tournament['liveMatches'],
            'recentMatches' => $tournament['recentMatches'],
            'upcomingMatches' => $tournament['upcomingMatches'],
            'rankings' => Daihoi::getRankings(10),
            'selectedSportId' => $sportId,
            'selectedStatus' => $status,
            'selectedDate' => $date,
        ));
    }

    /**
     * Chương trình Khai mạc Đại hội, thi Miss và Văn nghệ (Tiệc Welcome).
     */
    public function actionTiecWelcome()
    {
        $this->frontTitle = 'Khai mạc, thi Miss & Văn nghệ';
        $this->render('program', array(
            'event' => Daihoi::getEvent(),
            'agenda' => Daihoi::getAgenda(),
            'active' => 'welcome',
            'config' => array(
                'eyebrow' => 'Lễ khai mạc Đại hội',
                'title' => 'Khai mạc, thi Miss & Văn nghệ',
                'subtitle' => 'Chương trình Lễ khai mạc Đại hội, Chung kết thi Miss và đêm Văn nghệ chào mừng.',
                'icon' => 'celebration',
                'accent' => '#7c3aed',
                'info' => array(
                    array('icon' => 'event', 'label' => 'Sự kiện', 'value' => 'Tiệc Welcome - Khai mạc'),
                    array('icon' => 'schedule', 'label' => 'Thời gian', 'value' => '17:30 - 20:00, ngày 27/10/2026'),
                    array('icon' => 'location_on', 'label' => 'Địa điểm', 'value' => 'Hội trường tầng 2'),
                ),
                'keywords' => array('khai mạc', 'miss', 'văn nghệ', 'welcome', 'ceremony', 'gala'),
                'emptyText' => 'Chương trình chi tiết sẽ được Ban Tổ chức cập nhật.',
            ),
        ));
    }

    /**
     * Lịch tham quan và Tour Itinerary.
     */
    public function actionLichThamQuan()
    {
        $this->frontTitle = 'Lịch tham quan & Tour';
        $this->render('program', array(
            'event' => Daihoi::getEvent(),
            'agenda' => Daihoi::getAgenda(),
            'active' => 'tour',
            'config' => array(
                'eyebrow' => 'Trải nghiệm Đại hội',
                'title' => 'Lịch tham quan & Tour',
                'subtitle' => 'Hành trình tham quan và lịch trình tour dành cho Quý Đại biểu trong khuôn khổ Đại hội.',
                'icon' => 'tour',
                'accent' => '#0d9488',
                'info' => array(
                    array('icon' => 'event', 'label' => 'Hoạt động', 'value' => 'Tham quan & Tour Itinerary'),
                    array('icon' => 'place', 'label' => 'Điểm đến', 'value' => 'Theo thông báo của Ban Tổ chức'),
                ),
                'keywords' => array('tham quan', 'tour', 'itinerary', 'di chuyển', 'trải nghiệm'),
                'emptyText' => 'Lịch tham quan chi tiết sẽ được Ban Tổ chức cập nhật.',
            ),
        ));
    }

    /**
     * Chương trình Bế mạc Đại hội (Tiệc Gala).
     */
    public function actionTiecGala()
    {
        $this->frontTitle = 'Bế mạc Đại hội - Tiệc Gala';
        $this->render('program', array(
            'event' => Daihoi::getEvent(),
            'agenda' => Daihoi::getAgenda(),
            'active' => 'gala',
            'config' => array(
                'eyebrow' => 'Lễ bế mạc Đại hội',
                'title' => 'Bế mạc Đại hội - Tiệc Gala',
                'subtitle' => 'Chương trình Lễ bế mạc và đêm Tiệc Gala tổng kết Đại hội.',
                'icon' => 'nightlife',
                'accent' => '#be123c',
                'info' => array(
                    array('icon' => 'event', 'label' => 'Sự kiện', 'value' => 'Tiệc Gala - Bế mạc'),
                    array('icon' => 'schedule', 'label' => 'Thời gian', 'value' => '17:30 - 20:00, ngày 29/10/2026'),
                    array('icon' => 'location_on', 'label' => 'Địa điểm', 'value' => 'Hội trường tầng 2'),
                ),
                'keywords' => array('bế mạc', 'gala', 'tổng kết', 'closing'),
                'emptyText' => 'Chương trình chi tiết sẽ được Ban Tổ chức cập nhật.',
            ),
        ));
    }

    /**
     * Các đầu mối hỗ trợ (y tế, an ninh - PCCC, xe cấp cứu...).
     * Nội dung in trong Sổ tay Đại hội - slug /ho-tro.
     */
    public function actionHoTro()
    {
        $this->frontTitle = 'Các đầu mối hỗ trợ';
        $this->render('ho-tro', array(
            'event' => Daihoi::getEvent(),
        ));
    }

    /**
     * Danh sách Ban Thư Ký Đoàn - slug /doan-thu-ky.
     */
    public function actionDoanThuKy()
    {
        $this->frontTitle = 'Danh sách Ban Thư Ký Đoàn';
        $this->render('doan-thu-ky', array(
            'event' => Daihoi::getEvent(),
        ));
    }

    /**
     * Sơ đồ địa điểm Đại hội - slug /so-do-dia-diem.
     */
    public function actionSoDoDiaDiem()
    {
        $this->frontTitle = 'Sơ đồ địa điểm Đại hội';
        $this->render('so-do-dia-diem', array(
            'event' => Daihoi::getEvent(),
        ));
    }

    /**
     * Trang Tin tức chung - hiển thị toàn bộ tin tức, sidebar lọc theo danh mục.
     * slug: /tin-tuc  (?category_id=... để lọc)
     */
    public function actionNews()
    {
        $categoryId = Yii::app()->request->getParam('category_id');
        $this->frontTitle = 'Tin tức Đại hội';
        $this->render('news', array(
            'event' => Daihoi::getEvent(),
            'news' => Daihoi::getNewsList($categoryId, 60),
            'categories' => Daihoi::getNewsCategories(),
            'selectedCategoryId' => $categoryId,
        ));
    }

    /**
     * Trang chi tiết một bài tin - slug /tin-tuc/<id>.
     */
    public function actionNewsDetail($id)
    {
        $article = Daihoi::getNewsDetail($id);
        if (empty($article)) {
            throw new CHttpException(404, 'Không tìm thấy bài viết.');
        }
        $title = isset($article['title']) ? $article['title'] : 'Chi tiết tin tức';
        $this->frontTitle = $title;
        $this->render('news-detail', array(
            'event' => Daihoi::getEvent(),
            'article' => $article,
            'categories' => Daihoi::getNewsCategories(),
            'related' => Daihoi::getNewsList(isset($article['category_id']) ? $article['category_id'] : null, 6),
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
