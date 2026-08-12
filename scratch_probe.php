<?php
// Script chẩn đoán tạm thời — dump staff & attendee search. XÓA sau khi dùng.
defined('YII_DEBUG') or define('YII_DEBUG', true);
$yii = dirname(__FILE__) . '/framework/yii.php';
require_once($yii);
$config = require(dirname(__FILE__) . '/protected/config/console.php');
Yii::createConsoleApplication($config);

echo "=== STAFF 9786 (raw API) ===\n";
$r = ApiClient::get(ApiEndpoints::url(ApiEndpoints::STAFF_DETAIL, array('id' => 9786)));
$d = isset($r['data']['data']) ? $r['data']['data'] : (isset($r['data']) ? $r['data'] : $r);
foreach (array('id','code','unique_code','staff_code','id_card','full_name','position_name') as $k) {
    echo str_pad($k, 16) . ': ' . (isset($d[$k]) ? json_encode($d[$k]) : '(missing)') . "\n";
}

echo "\n=== ATTENDEE search staff_code=" . (isset($d['code']) ? $d['code'] : '?') . " ===\n";
$code = isset($d['code']) ? $d['code'] : '';
if ($code !== '') {
    $ar = ApiClient::get(ApiEndpoints::ATTENDEE_LIST, array('staff_code' => $code, 'per_page' => 50));
    $list = isset($ar['data']['data']) ? $ar['data']['data'] : (isset($ar['data']) ? $ar['data'] : array());
    echo "count=" . (is_array($list) ? count($list) : 0) . "\n";
    if (is_array($list)) {
        foreach ($list as $a) {
            echo sprintf("  id=%s name=%s staff_id=%s staff_code=%s id_card=%s photo=%s cccd_f=%s\n",
                isset($a['id'])?$a['id']:'-', isset($a['full_name'])?$a['full_name']:'-',
                isset($a['staff_id'])?$a['staff_id']:'-', isset($a['staff_code'])?$a['staff_code']:'-',
                isset($a['id_card'])?$a['id_card']:'-',
                !empty($a['photo_path'])?'Y':'-', isset($a['cccd_front_path'])?(!empty($a['cccd_front_path'])?'Y':'-'):'(no field)');
        }
    }
}

echo "\n=== ATTENDEE search staff_id=9786 ===\n";
$ar2 = ApiClient::get(ApiEndpoints::ATTENDEE_LIST, array('staff_id' => 9786, 'per_page' => 50));
$list2 = isset($ar2['data']['data']) ? $ar2['data']['data'] : (isset($ar2['data']) ? $ar2['data'] : array());
echo "count=" . (is_array($list2) ? count($list2) : 0) . "\n";
if (is_array($list2)) {
    foreach ($list2 as $a) {
        echo sprintf("  id=%s name=%s staff_id=%s staff_code=%s\n",
            isset($a['id'])?$a['id']:'-', isset($a['full_name'])?$a['full_name']:'-',
            isset($a['staff_id'])?$a['staff_id']:'-', isset($a['staff_code'])?$a['staff_code']:'-');
    }
}
