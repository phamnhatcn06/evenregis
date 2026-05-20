<?php
$yii = dirname(__FILE__) . '/framework/yii.php';
$config = dirname(__FILE__) . '/protected/config/main.php';
require_once($yii);
$app = Yii::createWebApplication($config);

echo "\nFetching all sports from /api/sports:\n";
$allSportsResult = ApiClient::get('/api/sports');
print_r($allSportsResult);
