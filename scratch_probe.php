<?php
// Script chẩn đoán tạm thời — XÓA sau khi dùng.
defined('YII_DEBUG') or define('YII_DEBUG', true);
$yii = dirname(__FILE__) . '/framework/yii.php';
require_once($yii);
$config = require(dirname(__FILE__) . '/protected/config/console.php');
$config['params'] = require(dirname(__FILE__) . '/protected/config/params.php');
Yii::createConsoleApplication($config);

function dump($id){
    $r = ApiClient::get(ApiEndpoints::url(ApiEndpoints::ATTENDEE_DETAIL, array('id'=>$id)));
    if(!$r['success']){ echo "#$id FAIL\n"; return; }
    $d = isset($r['data']['data'])?$r['data']['data']:$r['data'];
    echo "#$id name=".($d['full_name']??'-')." reg=".($d['registration_id']??'-')
        ." staff_id=".($d['staff_id']??'-')." id_card=".json_encode($d['id_card']??null)."\n";
    foreach(array('photo_path','portrait_path','cccd_front_path','cccd_back_path','contract_path') as $f)
        echo "   $f = ".json_encode($d[$f]??null)."\n";
}
dump(323);
echo "---\n";
dump(2749);
