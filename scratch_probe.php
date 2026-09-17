<?php
$yii = dirname(__FILE__) . '/framework/yii.php';
require_once($yii);
$app = Yii::createWebApplication(dirname(__FILE__).'/protected/config/main.php');

function topKeys($rows){ return $rows? implode(', ', array_keys($rows[0])) : '(empty)'; }
function nestedObjKeys($rows){
  if(!$rows) return '';
  $out=array();
  foreach($rows[0] as $k=>$v){ if(is_array($v)&&isset($v['id'])) $out[]="$k{".implode(',',array_keys($v))."}"; }
  return implode(' | ',$out);
}

// SPORT: need a team in event=3
$teams = SportTeams::getApiDataProvider(array('event_id'=>3),50)->getData();
$sportId=null;$teamId=null;
foreach($teams as $t){ if(!empty($t->sport_id)){ $sportId=$t->sport_id; $teamId=$t->id; break; } }
echo "SPORT: event=3 sport=$sportId team=$teamId\n";
if($teamId){
  echo " add: ".json_encode(SportTeams::addToFinal(3,$sportId,array($teamId)),JSON_UNESCAPED_UNICODE)."\n";
  $r=ApiClient::get('/api/sport-finals/list',array('event_id'=>3,'sport_id'=>$sportId));
  $rows=$r['data']['data']??array();
  echo " list top=[".topKeys($rows)."]\n nested: ".nestedObjKeys($rows)."\n";
  if($rows){ $rid=$rows[0]['id']??null; echo " remove(id=$rid): ".json_encode(ApiClient::delete('/api/sport-finals/remove/'.$rid),JSON_UNESCAPED_UNICODE)."\n"; }
}

// COMPETITION 5
$regs = CompetitionRegistrations::getRawList(array('competition_id'=>5),5);
$rid0 = $regs[0]['id']??null;
echo "\nCOMPETITION: comp=5 reg=$rid0\n";
if($rid0){
  echo " add: ".json_encode(CompetitionRegistrations::addToFinal(5,array($rid0)),JSON_UNESCAPED_UNICODE)."\n";
  $r=ApiClient::get('/api/competition-finals/list/5'); $rows=$r['data']['data']??array();
  echo " list top=[".topKeys($rows)."]\n nested: ".nestedObjKeys($rows)."\n";
  if($rows){ $x=$rows[0]['id']??null; echo " remove(id=$x): ".json_encode(ApiClient::delete('/api/competition-finals/remove/'.$x),JSON_UNESCAPED_UNICODE)."\n"; }
}

// BEAUTY 1
$cs = BeautyContestants::getRawList(array('contest_id'=>1),5);
$cid0=$cs[0]['id']??null;
echo "\nBEAUTY: contest=1 contestant=$cid0\n";
if($cid0){
  echo " add: ".json_encode(BeautyContestants::addToFinal(1,array($cid0)),JSON_UNESCAPED_UNICODE)."\n";
  $r=ApiClient::get('/api/beauty-finals/list/1'); $rows=$r['data']['data']??array();
  echo " list top=[".topKeys($rows)."]\n nested: ".nestedObjKeys($rows)."\n";
  if($rows){ $x=$rows[0]['id']??null; echo " remove(id=$x): ".json_encode(ApiClient::delete('/api/beauty-finals/remove/'.$x),JSON_UNESCAPED_UNICODE)."\n"; }
}
echo "\nDONE\n";
