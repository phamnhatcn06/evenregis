<?php
/** Harness S6: chạy MỘT ca actionSyncPreview/actionSync rồi in JSON. File tạm. */
defined('YII_DEBUG') or define('YII_DEBUG', true);
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/admin.php';
$_SERVER['SCRIPT_NAME']     = '/admin.php';
$_SERVER['REQUEST_URI']     = '/admin.php';
$_SERVER['HTTP_HOST']       = '127.0.0.1:8099';
$_SERVER['SERVER_NAME']     = '127.0.0.1';
$_SERVER['REQUEST_METHOD']  = 'POST';

require_once __DIR__ . '/framework/yii.php';
$config = require(__DIR__ . '/protected/config/main.php');
unset($config['components']['log']);
$config['components']['errorHandler']['errorAction'] = null;
Yii::createWebApplication($config);
Yii::app()->session->open();
Yii::import('application.modules.admin.controllers.FinalAttendeeRostersController');

// argv: 1 = action (preview|apply), 2 = có quyền create (1/0), 3 = body, 4 = method
$action        = isset($argv[1]) ? $argv[1] : 'preview';
$hasPermission = isset($argv[2]) ? (bool) (int) $argv[2] : true;
$body          = isset($argv[3]) ? $argv[3] : '';
$method        = !empty($argv[4]) ? $argv[4] : 'POST';

$_SERVER['REQUEST_METHOD'] = $method;
Yii::app()->session['sso_permissions'] = $hasPermission
    ? array('*' => '1 1 1 1')
    : array('finalattendeerosters' => '0 1 0 0'); // chỉ đọc, không được tạo

parse_str($body, $_POST);

$adminModule = Yii::app()->getModule('admin');
$controller  = new FinalAttendeeRostersController('finalAttendeeRosters', $adminModule);
Yii::app()->controller = $controller;

if ($action === 'apply') {
    $controller->actionSync();
} else {
    $controller->actionSyncPreview();
}
