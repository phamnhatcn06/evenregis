<?php
require_once dirname(__FILE__) . '/protected/vendor/autoload.php';
$yii=dirname(__FILE__).'/protected/vendor/yiisoft/yii/framework/yii.php';
$config=dirname(__FILE__).'/protected/config/main.php';
require_once($yii);
Yii::createWebApplication($config);

$url = 'https://portal-registration.muongthanh.vn/api/attendees/upload-documents/631';
$data = array(
    'contract_path' => '/uploads/test_contract.pdf'
);
$response = ApiClient::post($url, $data);
print_r($response);
