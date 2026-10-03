<?php
/** Harness S5: chạy MỘT ca gọi actionFilterOptions rồi in JSON. File tạm. */
defined('YII_DEBUG') or define('YII_DEBUG', true);
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/admin.php';
$_SERVER['SCRIPT_NAME']     = '/admin.php';
$_SERVER['REQUEST_URI']     = '/admin.php';
$_SERVER['HTTP_HOST']       = '127.0.0.1:8099';
$_SERVER['SERVER_NAME']     = '127.0.0.1';
$_SERVER['REQUEST_METHOD']  = 'GET';

require_once __DIR__ . '/framework/yii.php';
$config = require(__DIR__ . '/protected/config/main.php');
unset($config['components']['log']);
$config['components']['errorHandler']['errorAction'] = null;
Yii::createWebApplication($config);
Yii::app()->session->open();
Yii::import('application.modules.admin.controllers.FinalAttendeeRostersController');

// argv: 1 = có quyền (1/0), 2 = query string
$hasPermission = isset($argv[1]) ? (bool) (int) $argv[1] : true;
$query         = isset($argv[2]) ? $argv[2] : '';

Yii::app()->session['sso_permissions'] = $hasPermission ? array('*' => '1 1 1 1') : array();

parse_str($query, $_GET);

$adminModule = Yii::app()->getModule('admin');
$controller  = new FinalAttendeeRostersController('finalAttendeeRosters', $adminModule);
Yii::app()->controller = $controller;

$controller->actionFilterOptions();
