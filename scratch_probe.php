<?php
// Script chẩn đoán tạm thời — XÓA sau khi dùng.
defined('YII_DEBUG') or define('YII_DEBUG', true);
$yii = dirname(__FILE__) . '/framework/yii.php';
require_once($yii);
$config = require(dirname(__FILE__) . '/protected/config/console.php');
Yii::createConsoleApplication($config);

function dumpAtt($id) {
    $r = ApiClient::get(ApiEndpoints::url(ApiEndpoints::ATTENDEE_DETAIL, array('id' => $id)));
    if (!$r['success']) { echo "  #$id -> FAIL: " . json_encode($r) . "\n"; return; }
    $d = isset($r['data']['data']) ? $r['data']['data'] : $r['data'];
    echo sprintf("  #%s name=%s | staff_id=%s staff_code=%s id_card=%s | photo=%s cccd_f=%s cccd_b=%s contract=%s\n",
        $id,
        isset($d['full_name'])?$d['full_name']:'-',
        isset($d['staff_id'])?json_encode($d['staff_id']):'(none)',
        isset($d['staff_code'])?json_encode($d['staff_code']):'(none)',
        isset($d['id_card'])?json_encode($d['id_card']):'(none)',
        !empty($d['photo_path'])?'Y':'-',
        !empty($d['cccd_front_path'])?'Y':'-',
        !empty($d['cccd_back_path'])?'Y':'-',
        !empty($d['contract_path'])?'Y':'-'
    );
}

echo "=== Attendee details ===\n";
foreach (array(2737, 2748, 2749) as $id) { dumpAtt($id); }

echo "\n=== ATTENDEE_LIST staff_code=30010098 ===\n";
$ar = ApiClient::get(ApiEndpoints::ATTENDEE_LIST, array('staff_code' => '30010098', 'per_page' => 50));
echo "success=" . json_encode($ar['success']) . "\n";
$list = isset($ar['data']['data']) ? $ar['data']['data'] : (isset($ar['data']) ? $ar['data'] : array());
echo "count=" . (is_array($list) ? count($list) : 0) . "\n";
if (is_array($list)) foreach ($list as $a) {
    echo sprintf("  id=%s name=%s staff_code=%s photo=%s cccd_f=%s\n",
        isset($a['id'])?$a['id']:'-', isset($a['full_name'])?$a['full_name']:'-',
        isset($a['staff_code'])?$a['staff_code']:'(none)',
        !empty($a['photo_path'])?'Y':'-',
        isset($a['cccd_front_path'])?(!empty($a['cccd_front_path'])?'Y':'-'):'(nofield)');
}
