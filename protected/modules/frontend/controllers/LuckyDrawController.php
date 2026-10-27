<?php

/**
 * Trang công bố người trúng giải (Lucky Draw) - slug công khai /luckydraw.
 *
 * Hiển thị danh sách người trúng giải (BTC bốc thủ công, admin import) của sự kiện
 * mới nhất có dữ liệu, nhóm theo hạng giải. Dữ liệu lấy qua Model LuckyDraw (gọi API
 * Laravel bằng API key ở server, không yêu cầu đăng nhập).
 */
class LuckyDrawController extends FrontEndController
{
    public function init()
    {
        parent::init();
        $this->layout = 'daihoi';
    }

    public function accessRules()
    {
        return array(
            array('allow', 'actions' => array('index'), 'users' => array('*')),
            array('deny', 'users' => array('*')),
        );
    }

    public function actionIndex()
    {
        $this->frontTitle = 'Người trúng giải - Lucky Draw';

        $eventId = (int) Yii::app()->request->getParam('event_id');
        $result = LuckyDraw::publicManualWinners($eventId ?: null);
        $winners = isset($result['winners']) && is_array($result['winners']) ? $result['winners'] : array();

        // Nhóm theo hạng giải, giữ thứ tự xuất hiện (đã sắp ở API: tier -> sort_order -> id).
        $groups = array();
        foreach ($winners as $w) {
            $tier = isset($w['prize_tier']) && $w['prize_tier'] !== '' ? $w['prize_tier'] : 'Khác';
            if (!isset($groups[$tier])) {
                $groups[$tier] = array();
            }
            $groups[$tier][] = $w;
        }

        $event = Daihoi::getEvent();
        $eventName = isset($event['name']) ? $event['name'] : (isset($event['title']) ? $event['title'] : 'Đại hội Mường Thanh 2026');

        $this->render('index', array(
            'groups' => $groups,
            'total' => count($winners),
            'eventName' => $eventName,
        ));
    }
}
