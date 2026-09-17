<?php
$yii=dirname(__FILE__).'/framework/yii.php'; require_once($yii);
$app=Yii::createWebApplication(dirname(__FILE__).'/protected/config/main.php');
$teams=SportTeams::getApiDataProvider(array('event_id'=>3,'sport_id'=>24),50)->getData();
echo "sport 24 teams: ".count($teams)."\n";
foreach(array_slice($teams,0,6) as $t){
  echo json_encode(array('id'=>$t->id,'team_name'=>$t->team_name,'name'=>$t->name??null,'property_name'=>$t->property_name,'is_alliance'=>$t->is_alliance,'is_alliance_team'=>$t->is_alliance_team,'alliance_org_names'=>$t->alliance_org_names,'member_count'=>$t->member_count,'status'=>$t->status),JSON_UNESCAPED_UNICODE)."\n";
}
