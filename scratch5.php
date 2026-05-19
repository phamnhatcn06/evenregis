<?php
$_SERVER['REQUEST_URI'] = '/';
require_once('index.php');
$app = Yii::createWebApplication('protected/config/main.php');
$url = ApiEndpoints::url(ApiEndpoints::ATTENDEE_UPDATE, array('id' => 1));
$data = array('document' => 'http://test.com/test_doc.pdf');
$res = ApiClient::post($url, $data);
var_dump($res);
