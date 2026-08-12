<?php
// Script chẩn đoán tạm thời — XÓA sau khi dùng.
defined('YII_DEBUG') or define('YII_DEBUG', true);
$yii = dirname(__FILE__) . '/framework/yii.php';
require_once($yii);
$config = require(dirname(__FILE__) . '/protected/config/console.php');
$config['params'] = require(dirname(__FILE__) . '/protected/config/params.php');
Yii::createConsoleApplication($config);

function testFilter($label, $params) {
    $r = ApiClient::get(ApiEndpoints::ATTENDEE_LIST, $params);
    $list = isset($r['data']['data']) ? $r['data']['data'] : (isset($r['data']) ? $r['data'] : array());
    $n = is_array($list) ? count($list) : 0;
    // distinct staff_id / id_card among results
    $sids = array(); $ics = array();
    if (is_array($list)) foreach ($list as $a) {
        if (isset($a['staff_id'])) $sids[(string)$a['staff_id']] = 1;
        if (isset($a['id_card'])) $ics[(string)$a['id_card']] = 1;
    }
    echo sprintf("%-40s success=%s count=%d distinct_staff_id=%d distinct_id_card=%d\n",
        $label, json_encode($r['success']), $n, count($sids), count($ics));
}

// Quỳnh: staff_id=640499 staff_code=40160220 id_card=066300008203 (attendee 2737)
testFilter("staff_id=640499", array('staff_id' => 640499, 'per_page' => 50));
testFilter("staff_code=40160220", array('staff_code' => '40160220', 'per_page' => 50));
testFilter("id_card=066300008203", array('id_card' => '066300008203', 'per_page' => 50));
testFilter("id_card=241737728 (Linh)", array('id_card' => '241737728', 'per_page' => 50));

echo "\n-- Does id_card filter return ONLY that id_card? Dump results for id_card=066300008203 --\n";
$r = ApiClient::get(ApiEndpoints::ATTENDEE_LIST, array('id_card' => '066300008203', 'per_page' => 50));
$list = isset($r['data']['data']) ? $r['data']['data'] : array();
if (is_array($list)) foreach ($list as $a) {
    echo sprintf("  id=%s name=%s staff_id=%s id_card=%s photo=%s\n",
        $a['id']?? '-', $a['full_name']??'-', $a['staff_id']??'-', $a['id_card']??'-', !empty($a['photo_path'])?'Y':'-');
}
