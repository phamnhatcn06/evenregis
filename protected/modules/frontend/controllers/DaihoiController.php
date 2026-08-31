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
        $this->render('index', array(
            'event' => Daihoi::getEvent(),
            'countdown' => Daihoi::getCountdown(),
            'stats' => Daihoi::getStats(),
            'contents' => Daihoi::getContents(),
            'agenda' => Daihoi::getAgenda(),
            'liveMatches' => Daihoi::getLiveMatches(),
            'recentMatches' => Daihoi::getRecentMatches(),
            'rankings' => Daihoi::getRankings(5),
            'news' => Daihoi::getNews(6),
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

    public function actionJsonCountdown()
    {
        $this->renderJson(Daihoi::getCountdown());
    }

    private function renderJson($data)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo CJSON::encode(array('success' => true, 'data' => $data));
        Yii::app()->end();
    }
}
