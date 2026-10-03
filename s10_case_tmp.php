<?php
/** Harness S10 FE: chạy MỘT ca actionCreate. File tạm. */
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

$perm   = isset($argv[1]) ? (bool) (int) $argv[1] : true;
$body   = isset($argv[2]) ? $argv[2] : '';
$method = !empty($argv[3]) ? $argv[3] : 'POST';
$_SERVER['REQUEST_METHOD'] = $method;
Yii::app()->session['sso_permissions'] = $perm
    ? array('*' => '1 1 1 1')
    : array('finalattendeerosters' => '0 1 1 0');
parse_str($body, $_POST);
$adminModule = Yii::app()->getModule('admin');
$controller  = new FinalAttendeeRostersController('finalAttendeeRosters', $adminModule);
Yii::app()->controller = $controller;
$controller->actionCreate();
