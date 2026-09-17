<?php
$yii = dirname(__FILE__) . '/framework/yii.php';
$config = dirname(__FILE__) . '/protected/config/main.php';
require_once($yii);
$app = Yii::createWebApplication($config);

function pickName($row,$fb){
    foreach(array('attendee_name','full_name','name') as $k) if(!empty($row[$k])) return $row[$k];
    if(isset($row['attendee']['full_name'])&&$row['attendee']['full_name']!=='') return $row['attendee']['full_name'];
    return $fb;
}
function pickProp($row){
    if(!empty($row['property_name'])) return $row['property_name'];
    if(isset($row['attendee']['property']['name'])) return $row['attendee']['property']['name'];
    return '';
}

echo "== COMPETITION candidates (comp=5) ==\n";
$raw = CompetitionRegistrations::getRawList(array('competition_id'=>5),5);
foreach($raw as $r){
    echo sprintf("  [%s] %s | %s\n", isset($r['candidate_number'])?$r['candidate_number']:'-', pickName($r,'Thí sinh #'.$r['id']), pickProp($r));
}

echo "== BEAUTY candidates (contest=1) ==\n";
$raw = BeautyContestants::getRawList(array('contest_id'=>1),5);
foreach($raw as $r){
    $code=!empty($r['contestant_number'])?$r['contestant_number']:(!empty($r['candidate_number'])?$r['candidate_number']:'-');
    echo sprintf("  [%s] %s | %s\n", $code, pickName($r,'#'.$r['id']), pickProp($r));
}

echo "== TALENT candidates (show=1) ==\n";
$raw = TalentEntries::getRawList(array('show_id'=>1),5);
foreach($raw as $r){
    if(isset($r['status'])&&(string)$r['status']===(string)TalentEntries::STATUS_REJECTED){ echo "  (skip rejected)\n"; continue; }
    $sub=isset($r['property_name'])?$r['property_name']:'';
    if(!empty($r['category_name'])) $sub.=($sub?' · ':'').$r['category_name'];
    echo sprintf("  %s | %s\n", isset($r['title'])?$r['title']:'#'.$r['id'], $sub);
}
echo "DONE\n";
