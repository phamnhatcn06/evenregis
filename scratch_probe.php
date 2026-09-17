<?php
$yii = dirname(__FILE__) . '/framework/yii.php';
require_once($yii);
$app = Yii::createWebApplication(dirname(__FILE__).'/protected/config/main.php');
function nested($rows){ if(!$rows)return ''; $o=array(); foreach($rows[0] as $k=>$v){ if(is_array($v)&&isset($v['id'])) $o[]="$k{".implode(',',array_keys($v))."}"; } return implode(' | ',$o); }

echo "SPORT list (event3,sport35):\n";
$r=ApiClient::get('/api/sport-finals/list',array('event_id'=>3,'sport_id'=>35));
$rows=$r['data']['data']??array();
echo " count=".count($rows)." top=[".($rows?implode(', ',array_keys($rows[0])):'')."]\n nested: ".nested($rows)."\n";
foreach($rows as $row){ $id=$row['id']??null; echo " cleanup remove id=$id: ".json_encode(ApiClient::delete('/api/sport-finals/remove/'.$id),JSON_UNESCAPED_UNICODE)."\n"; }

echo "\nCOMPETITION list (5):\n";
$r=ApiClient::get('/api/competition-finals/list/5');
$rows=$r['data']['data']??array();
echo " count=".count($rows)." top=[".($rows?implode(', ',array_keys($rows[0])):'')."]\n nested: ".nested($rows)."\n";
foreach($rows as $row){ $id=$row['id']??null; echo " cleanup remove id=$id: ".json_encode(ApiClient::delete('/api/competition-finals/remove/'.$id),JSON_UNESCAPED_UNICODE)."\n"; }

echo "\nTALENT list (1) [state check]:\n";
$r=ApiClient::get('/api/talent-finals/list/1');
$rows=$r['data']['data']??array();
echo " count=".count($rows)."\n";
foreach($rows as $row){ $id=$row['id']??null; echo " leftover remove id=$id: ".json_encode(ApiClient::delete('/api/talent-finals/remove/'.$id),JSON_UNESCAPED_UNICODE)."\n"; }
echo "DONE\n";
