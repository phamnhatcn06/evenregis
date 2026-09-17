<?php
$yii = dirname(__FILE__) . '/framework/yii.php';
require_once($yii);
$app = Yii::createWebApplication(dirname(__FILE__).'/protected/config/main.php');

$showId = 1;
echo "1) TRUOC: ";
$before = TalentEntries::getFinalists($showId);
echo count($before)." finalist\n";
$existing=array(); foreach($before as $f) if(isset($f['entry_id'])) $existing[$f['entry_id']]=true;

$raw = TalentEntries::getRawList(array('show_id'=>$showId),50);
$pick=null; foreach($raw as $r){ if(isset($r['id'])&&!isset($existing[$r['id']])){ $pick=$r; break; } }
if(!$pick){ echo "Khong co entry de test.\n"; exit; }
echo "2) Chon entry id={$pick['id']} title={$pick['title']}\n";

echo "3) ADD: ";
echo json_encode(TalentEntries::addToFinal($showId, array($pick['id'])), JSON_UNESCAPED_UNICODE)."\n";

echo "4) SAU add: ";
$after = TalentEntries::getFinalists($showId);
echo count($after)." finalist\n";
if($after){ echo "   keys=[".implode(', ',array_keys($after[0]))."]\n"; foreach($after as $f){ echo "   ".json_encode($f,JSON_UNESCAPED_UNICODE)."\n"; } }

$removeId=null;
foreach($after as $f){ if(isset($f['entry_id'])&&$f['entry_id']==$pick['id']){ $removeId=isset($f['id'])?$f['id']:null; break; } }
echo "5) removeId=".var_export($removeId,true)."\n";
if($removeId!==null){
  echo "6) REMOVE: ".json_encode(TalentEntries::removeFromFinal($removeId),JSON_UNESCAPED_UNICODE)."\n";
  echo "7) SAU remove: ".count(TalentEntries::getFinalists($showId))." (ky vong ".count($before).")\n";
} else { echo "!! CAN GO THU CONG entry_id={$pick['id']}\n"; }
echo "DONE\n";
