<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/admin.php';
$_SERVER['SCRIPT_NAME'] = '/admin.php';
$_SERVER['REQUEST_URI'] = '/admin.php';
$_SERVER['HTTP_HOST'] = '127.0.0.1:8099';
$_SERVER['SERVER_NAME'] = '127.0.0.1';
require_once __DIR__ . '/framework/yii.php';
$config = require(__DIR__ . '/protected/config/main.php');
unset($config['components']['log']);
Yii::createWebApplication($config);
$params = array('event_id' => 3, 'period_id' => 4);
for ($page = 1; $page <= 3; $page++) {
    $dp = FinalAttendeeRosters::getApiDataProvider($params, 500);
    $dp->pagination->setCurrentPage($page - 1);
    $data = $dp->getData();
    echo "page=$page | setCurrentPage=" . ($page-1)
       . " | getCurrentPage=" . $dp->pagination->getCurrentPage()
       . " | rows=" . count($data)
       . " | id dau=" . (isset($data[0]) ? $data[0]->id : '-') . "\n";
}
