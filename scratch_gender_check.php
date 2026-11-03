<?php
// READ-ONLY: kiểm tra 27 người có trong danh sách sửa giới tính không + có phải finalist không.
$_SERVER['REQUEST_URI'] = '/';
require_once('index.php');
$app = Yii::createWebApplication('protected/config/main.php');

$eventId = isset($argv[1]) ? $argv[1] : 1;
$targets = array(4013,1419,1740,1363,1423,1416,1424,1428,2841,471,453,516,517,1422,483,899,430,480,271,292,294,295,224,261,241,223,222);
$targets = array_flip($targets);

$rows = Attendees::getApiDataProvider(array('event_id' => $eventId, 'per_page' => 10000), 10000)->getData();
echo "total attendees fetched: " . count($rows) . "\n";
$found = array();
foreach ($rows as $a) {
    if (!isset($a->id) || !isset($targets[$a->id])) continue;
    $found[$a->id] = array(
        'name' => isset($a->full_name) ? $a->full_name : '',
        'gender' => isset($a->gender) ? $a->gender : null,
        'type' => isset($a->attendee_type) ? $a->attendee_type : null,
        'reg' => isset($a->registration_id) ? $a->registration_id : null,
        'prop_id' => isset($a->property_id) ? $a->property_id : null,
        'role' => isset($a->role_id) ? (is_array($a->role_id) ? implode(',', $a->role_id) : $a->role_id) : null,
        'deleted' => isset($a->deleted_at) ? $a->deleted_at : null,
        'join_hotel_date' => isset($a->join_hotel_date) ? $a->join_hotel_date : null,
    );
}
echo "matched in attendees list: " . count($found) . "\n";
foreach ($found as $id => $f) {
    echo $id . " | " . $f['name'] . " | gender=" . var_export($f['gender'], true)
        . " | type=" . var_export($f['type'], true)
        . " | reg=" . var_export($f['reg'], true)
        . " | prop=" . var_export($f['prop_id'], true)
        . " | role=" . var_export($f['role'], true)
        . " | jhd=" . var_export($f['join_hotel_date'], true)
        . " | del=" . var_export($f['deleted'], true) . "\n";
}
$missing = array_diff_key($targets, $found);
echo "NOT in attendees list (ids): " . implode(',', array_keys($missing)) . "\n";
