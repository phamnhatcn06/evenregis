<?php
$_SERVER['REQUEST_URI'] = '/';
require_once('index.php');
$app = Yii::createWebApplication('protected/config/main.php');
$url = ApiEndpoints::url(ApiEndpoints::ATTENDEE_DETAIL, array('id' => 1));
$res = ApiClient::get($url);
print_r(json_encode($res, JSON_PRETTY_PRINT));
