<?php
// Probe READ-ONLY cho tính năng chung kết. Không gọi add/remove.
$yii = dirname(__FILE__) . '/framework/yii.php';
$config = dirname(__FILE__) . '/protected/config/main.php';
require_once($yii);
$app = Yii::createWebApplication($config);

function head($t){ echo "\n==================== $t ====================\n"; }
function keysOf($arr){
    if (!is_array($arr) || empty($arr)) return '(rỗng)';
    $first = $arr[0];
    return is_array($first) ? implode(', ', array_keys($first)) : gettype($first);
}
function firstList($endpoint, $params = array()){
    $r = ApiClient::get($endpoint, $params);
    if (!$r['success']) return array();
    $d = isset($r['data']['data']) ? $r['data']['data'] : $r['data'];
    return is_array($d) ? $d : array();
}

// ---- Thể thao ----
head('SPORT');
$events = firstList('/api/events', array('per_page'=>50));
$sports = firstList('/api/sports', array('per_page'=>100));
echo "events: ".count($events)." | sports: ".count($sports)."\n";
if ($events && $sports){
    $eid = $events[0]['id'];
    foreach ($sports as $sp){
        $sid = $sp['id'];
        $fin = SportTeams::getFinalists($eid, $sid);
        if ($fin){ echo "event=$eid sport=$sid finalists=".count($fin)." keys=[".keysOf($fin)."]\n"; echo json_encode($fin[0], JSON_UNESCAPED_UNICODE)."\n"; break; }
    }
    $teams = SportTeams::getApiDataProvider(array('event_id'=>$eid,'sport_id'=>$sports[0]['id']),20)->getData();
    echo "candidate teams(event=$eid,sport=".$sports[0]['id']."): ".count($teams)."\n";
    if ($teams){ $t=$teams[0]; echo "team sample: id={$t->id} name={$t->team_name} prop={$t->property_name} status={$t->status} members={$t->member_count}\n"; }
}

// ---- Thi nghiệp vụ ----
head('COMPETITION');
$comps = firstList('/api/competitions', array('per_page'=>50));
echo "competitions: ".count($comps)."\n";
if ($comps){
    $cid = $comps[0]['id'];
    $fin = CompetitionRegistrations::getFinalists($cid);
    echo "finalists(comp=$cid)=".count($fin)." keys=[".keysOf($fin)."]\n";
    if ($fin) echo json_encode($fin[0], JSON_UNESCAPED_UNICODE)."\n";
    $raw = CompetitionRegistrations::getRawList(array('competition_id'=>$cid), 20);
    echo "raw regs=".count($raw)." keys=[".keysOf($raw)."]\n";
    if ($raw) echo json_encode($raw[0], JSON_UNESCAPED_UNICODE)."\n";
}

// ---- Sắc đẹp ----
head('BEAUTY');
$contests = firstList('/api/beauty-contests', array('per_page'=>50));
echo "contests: ".count($contests)."\n";
if ($contests){
    $bid = $contests[0]['id'];
    $fin = BeautyContestants::getFinalists($bid);
    echo "finalists(contest=$bid)=".count($fin)." keys=[".keysOf($fin)."]\n";
    if ($fin) echo json_encode($fin[0], JSON_UNESCAPED_UNICODE)."\n";
    $raw = BeautyContestants::getRawList(array('contest_id'=>$bid), 20);
    echo "raw contestants=".count($raw)." keys=[".keysOf($raw)."]\n";
    if ($raw) echo json_encode($raw[0], JSON_UNESCAPED_UNICODE)."\n";
}

// ---- Văn nghệ ----
head('TALENT');
$shows = firstList('/api/talent-shows', array('per_page'=>50));
echo "shows: ".count($shows)."\n";
if ($shows){
    $shid = $shows[0]['id'];
    $fin = TalentEntries::getFinalists($shid);
    echo "finalists(show=$shid)=".count($fin)." keys=[".keysOf($fin)."]\n";
    if ($fin) echo json_encode($fin[0], JSON_UNESCAPED_UNICODE)."\n";
    $raw = TalentEntries::getRawList(array('show_id'=>$shid), 20);
    echo "raw entries=".count($raw)." keys=[".keysOf($raw)."]\n";
    if ($raw) echo json_encode($raw[0], JSON_UNESCAPED_UNICODE)."\n";
}

echo "\nDONE\n";
