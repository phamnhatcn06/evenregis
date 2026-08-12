<?php
// Script chẩn đoán tạm thời — XÓA sau khi dùng.
defined('YII_DEBUG') or define('YII_DEBUG', true);
$yii = dirname(__FILE__) . '/framework/yii.php';
require_once($yii);
$config = require(dirname(__FILE__) . '/protected/config/console.php');
$config['params'] = require(dirname(__FILE__) . '/protected/config/params.php');
Yii::createConsoleApplication($config);

foreach (array(50, 200, 1000, 5000) as $pp) {
    $r = ApiClient::get(ApiEndpoints::ATTENDEE_LIST, array('per_page' => $pp));
    $list = isset($r['data']['data']) ? $r['data']['data'] : array();
    // pagination meta?
    $meta = isset($r['data']['meta']) ? $r['data']['meta'] : (isset($r['data']['pagination']) ? $r['data']['pagination'] : null);
    echo "per_page=$pp -> count=" . (is_array($list)?count($list):0) . " meta=" . json_encode($meta) . "\n";
}

// Full scan by staff_id client-side
$r = ApiClient::get(ApiEndpoints::ATTENDEE_LIST, array('per_page' => 5000));
$list = isset($r['data']['data']) ? $r['data']['data'] : array();
$total = is_array($list) ? count($list) : 0;
echo "\nTOTAL fetched=$total\n";
$byStaff = array();
foreach ($list as $a) {
    $sid = isset($a['staff_id']) ? (string)$a['staff_id'] : '';
    if ($sid==='') continue;
    if (!isset($byStaff[$sid])) $byStaff[$sid]=array('n'=>0,'withPhoto'=>0);
    $byStaff[$sid]['n']++;
    if (!empty($a['photo_path'])) $byStaff[$sid]['withPhoto']++;
}
echo "distinct staff_id=" . count($byStaff) . "\n";
// staff_ids with a documented record
$docs=0; foreach($byStaff as $s=>$v){ if($v['withPhoto']>0)$docs++; }
echo "staff_id with >=1 photo record=$docs\n";
echo "staff_id=9786 -> " . json_encode(isset($byStaff['9786'])?$byStaff['9786']:'NONE') . "\n";
echo "staff_id=640499 (Quynh) -> " . json_encode(isset($byStaff['640499'])?$byStaff['640499']:'NONE') . "\n";
