<?php
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['HTTP_HOST'] = 'localhost';
require_once('index.php');
$app = Yii::createWebApplication('protected/config/main.php');
$url = ApiEndpoints::url(ApiEndpoints::ATTENDEE_UPLOAD_DOCUMENTS, array('id' => 1));
$data = array(
    'contract_path' => 'http://test.com/test.pdf'
);
$res = ApiClient::post($url, $data);
print_r($res);
