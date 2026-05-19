<?php
require_once('index-test.php');
// Or just mock $_SERVER['REQUEST_URI']
$_SERVER['REQUEST_URI'] = '/';
require_once('index.php');
$app = Yii::createWebApplication('protected/config/main.php');
$att = Attendees::fetchFromApi(1);
if (!$att) { die("Attendee 1 not found\n"); }
$att->contract_path = 'http://test.com/test.pdf';
$res = $att->updateViaApi();
print_r($res);
