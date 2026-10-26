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
            array(
                'allow',
                'actions' => array('index', 'agenda', 'schedule', 'tiecWelcome', 'lichThamQuan', 'tiecGala', 'funRun', 'hoTro', 'doanThuKy', 'soDoDiaDiem', 'soTayDaiHoi', 'khaoSat', 'news', 'newsDetail', 'jsonLive', 'jsonRecent', 'jsonRankings'),
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
        $hasPortalToken = !empty(Yii::app()->session['sso_token']) || AuthHandler::isAuthenticated();
        $hasAdminAccess = $hasPortalToken;

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
            'hasPortalToken' => $hasPortalToken,
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
                    array('icon' => 'event', 'label' => 'Sự kiện', 'value' => 'Khai mạc Đại hội Mường Thanh 2026'),
                    array('icon' => 'schedule', 'label' => 'Thời gian', 'value' => '18:30 - 22:30, ngày 27/10/2026'),
                    array('icon' => 'location_on', 'label' => 'Địa điểm', 'value' => 'Hoa Lư Ballroom - Tầng 1, Mường Thanh Luxury Ninh Bình'),
                ),
                'keywords' => array('khai mạc', 'miss', 'văn nghệ', 'welcome', 'ceremony', 'gala'),
                'emptyText' => 'Chương trình chi tiết sẽ được Ban Tổ chức cập nhật.',
                // Chương trình chi tiết lấy từ poster Agenda Khai mạc (docs/Agenda_khaimac.jpg).
                'program' => array(
                    array('time' => '18:30 - 18:55', 'title' => 'Đón khách'),
                    array('time' => '19:00', 'title' => 'Bắt đầu chương trình'),
                    array('time' => '19:00 - 19:05', 'title' => 'Tiết mục mở màn "Mãnh liệt tinh thần Mường Thanh"'),
                    array('time' => '19:05 - 19:10', 'title' => 'Giới thiệu chương trình & đại biểu'),
                    array('time' => '19:10 - 19:13', 'title' => 'Clip "Hành trình ngọn lửa di sản"'),
                    array('time' => '19:13 - 19:18', 'title' => 'Nghi thức thắp đuốc'),
                    array('time' => '19:18 - 19:30', 'title' => 'Phát biểu khai mạc'),
                    array('time' => '19:30 - 20:30', 'title' => 'Chương trình Chung kết Miss Mường Thanh 2026', 'sub' => array(
                        'Tiết mục mở màn (Màn đồng diễn TOP 30 Miss Mường Thanh 2026)',
                        'Công bố TOP 15',
                        'Tiết mục nghệ thuật (Ca sĩ khách mời: Oplus Band)',
                        'Trình diễn (Trình diễn Trang phục bản sắc Top 15)',
                        'Tiết mục nghệ thuật (Ca sĩ khách mời: Oplus Band)',
                        'Công bố TOP 5',
                        'Phần thi ứng xử của TOP 5',
                    )),
                    array('time' => '20:30 - 21:30', 'title' => 'Chương trình Chung kết Cuộc thi Văn nghệ Việt Nam Gấm Hoa', 'sub' => array(
                        'Đội thi - Liên quân MT Green Land Diễn Lâm (Diễn Lâm đất Nghệ - Kiêu hãnh Việt Nam)',
                        'Đội thi - Liên quân MT Luxury Điện Biên - MT Grand Điện Biên Phủ (Sắc lanh xuống chợ)',
                        'Đội thi - MT Luxury Bắc Giang (Về miền di sản)',
                        'Đội thi - Liên quân MT Luxury Viễn Triều - MT Grand Nha Trang - Chung cư MT Viễn Triều (Champa thức giấc)',
                        'Đội thi - Trung tâm Giải trí VRC (Giọng Nghệ tìm về)',
                        'Đội thi - Liên quân MT Grand Bãi Cháy & MT Grand Hạ Long (Quảng Ninh vươn mình cất cánh)',
                    )),
                    array('time' => '21:45 - 22:00', 'title' => 'Công bố & trao giải Miss Mường Thanh 2026'),
                    array('time' => '22:00 - 22:20', 'title' => 'Công bố & trao giải Cuộc thi Văn nghệ Việt Nam Gấm Hoa'),
                    array('time' => '22:20 - 22:30', 'title' => 'Kết thúc chương trình'),
                ),
            ),
        ));
    }

    /**
     * Lịch tham quan và Tour Itinerary.
     */
    public function actionLichThamQuan()
    {
        $this->frontTitle = 'Tour tham quan Di sản Tràng An';
        $this->render('tham-quan', array(
            'event' => Daihoi::getEvent(),
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
                    array('icon' => 'event', 'label' => 'Sự kiện', 'value' => 'Bế mạc Đại hội Mường Thanh 2026'),
                    array('icon' => 'schedule', 'label' => 'Thời gian', 'value' => '18:30 - 22:00, ngày 29/10/2026'),
                    array('icon' => 'location_on', 'label' => 'Địa điểm', 'value' => 'Hoa Lư Ballroom - Tầng 1, Mường Thanh Luxury Ninh Bình'),
                ),
                'keywords' => array('bế mạc', 'gala', 'tổng kết', 'closing'),
                'emptyText' => 'Chương trình chi tiết sẽ được Ban Tổ chức cập nhật.',
                // Chương trình chi tiết lấy từ poster Agenda Bế mạc (docs/Agenda_Be_Mac.jpg).
                'program' => array(
                    array('time' => '18:30 - 19:00', 'title' => 'Đón khách'),
                    array('time' => '19:00 - 19:05', 'title' => 'Tiết mục mở màn - Beyond the next'),
                    array('time' => '19:05 - 19:10', 'title' => 'Giới thiệu chương trình & đại biểu'),
                    array('time' => '19:10 - 19:20', 'title' => 'Clip "Dấu ấn Đại hội Mường Thanh 2026"'),
                    array('time' => '19:20 - 19:25', 'title' => 'Phát biểu bế mạc Đại hội Mường Thanh 2026'),
                    array('time' => '19:25 - 19:40', 'title' => 'Trao kỷ niệm chương & cờ cho 88 đoàn tham dự Đại hội, chụp hình tập thể'),
                    array('section' => 'Chương 1 - Theo những cánh bay'),
                    array('time' => '19:40 - 20:00', 'title' => 'Chương trình nghệ thuật - Ca sĩ An Thu An'),
                    array('section' => 'Chương 2 - Về miền di sản'),
                    array('time' => '20:00 - 20:15', 'title' => 'Chương trình nghệ thuật - Ca sĩ Hà Myo'),
                    array('section' => 'Chương 3 - Về phía mặt trời'),
                    array('time' => '20:15 - 20:30', 'title' => 'Chương trình nghệ thuật - Ca sĩ Phạm Anh Duy'),
                    array('time' => '20:30 - 20:45', 'title' => 'Lucky Draw - Bốc thăm 04 Giải Nhì, 02 Giải Nhất'),
                    array('time' => '20:35 - 21:00', 'title' => 'DJ Performance Set 1'),
                    array('time' => '21:00 - 21:10', 'title' => 'Lucky Draw - Bốc thăm Giải Đặc biệt'),
                    array('time' => '21:10 - 21:40', 'title' => 'DJ Performance Set 2'),
                    array('time' => '21:40 - 22:00', 'title' => 'Kết thúc chương trình'),
                ),
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
        $event = Daihoi::getEvent();
        $eventId = isset($event['id']) ? (int) $event['id'] : null;
        $this->render('doan-thu-ky', array(
            'event' => $event,
            'secretaries' => Daihoi::getSecretaries($eventId),
        ));
    }

    /**
     * Trang thông tin Giải chạy Fun Run (Điều lệ + Sơ đồ đường chạy) - slug /fun-run.
     * Nội dung công khai, tách biệt với cổng đăng ký /run.
     */
    public function actionFunRun()
    {
        $this->frontTitle = 'Giải chạy Mường Thanh Fun Run';
        $this->render('fun-run', array(
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
     * Sổ tay Đại hội - toàn bộ thông tin hướng dẫn Đại biểu - slug /so-tay-dai-hoi.
     */
    public function actionSoTayDaiHoi()
    {
        $this->frontTitle = 'Sổ tay Đại hội';
        $this->render('so-tay-dai-hoi', array(
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
