<?php
// Script chẩn đoán tạm thời — XÓA sau khi dùng.
defined('YII_DEBUG') or define('YII_DEBUG', true);
$yii = dirname(__FILE__) . '/framework/yii.php';
require_once($yii);
$config = require(dirname(__FILE__) . '/protected/config/console.php');
$config['params'] = require(dirname(__FILE__) . '/protected/config/params.php');
Yii::createConsoleApplication($config);

$r = ApiClient::get(ApiEndpoints::ATTENDEE_LIST, array('per_page' => 5000));
$list = isset($r['data']['data']) ? $r['data']['data'] : array();

function show($a){
    echo sprintf("  id=%s reg=%s name=%s staff_id=%s staff_code=%s id_card=%s photo=%s\n",
        $a['id']??'-', $a['registration_id']??'-', $a['full_name']??'-',
        $a['staff_id']??'-', $a['staff_code']??'-', $a['id_card']??'-', !empty($a['photo_path'])?'Y':'-');
}
echo "=== staff_id=9786 ===\n";
foreach ($list as $a){ if((string)($a['staff_id']??'')==='9786') show($a); }
echo "=== id_card=066300008203 (Quynh) ===\n";
foreach ($list as $a){ if((string)($a['id_card']??'')==='066300008203') show($a); }
echo "=== name contains 'Lê  Hoàng' or 'Hoàng' with photo ===\n";
$c=0; foreach ($list as $a){ if(mb_strpos(($a['full_name']??''),'Hoàng')!==false){ show($a); if(++$c>15)break; } }
