<?php
$_SERVER['REQUEST_URI'] = '/';
require_once('index.php');
$app = Yii::createWebApplication('protected/config/main.php');

$id = 224; // Trần Thị Trà
$att = Attendees::fetchFromApi($id);
if (!$att) { die("not found\n"); }
echo "before gender=" . var_export($att->gender, true) . "\n";
$res = $att->updateGenderOnlyViaApi(0); // Nữ
echo "update result:\n";
print_r($res);
$att2 = Attendees::fetchFromApi($id);
echo "after gender=" . var_export($att2->gender, true) . "\n";
