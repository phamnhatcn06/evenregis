<?php
/** Harness S5: API key không được lọt ra HTML. File tạm. */
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
Yii::app()->session['sso_permissions'] = array('*' => '1 1 1 1');
Yii::import('application.modules.admin.controllers.FinalAttendeeRostersController');

$ok = function ($label, $cond) { echo ($cond ? 'PASS' : 'FAIL') . " — $label\n"; };

$adminModule = Yii::app()->getModule('admin');
$controller  = new FinalAttendeeRostersController('finalAttendeeRosters', $adminModule);
Yii::app()->controller = $controller;
$controller->layout = false;

$options = FinalAttendeeRosters::getFilterOptions(3, 4, 66);
$dp = FinalAttendeeRosters::getApiDataProvider(array('event_id' => 3, 'period_id' => 4), 25);
$dp->pagination->route = 'admin/finalAttendeeRosters/admin';

$filters = array(
    'property_id' => 66, 'division_code' => null, 'department_code' => null,
    'attendee_type' => null, 'has_lucky' => null, 'has_override' => null,
    'conflict_flag' => null, 'keyword' => null, 'with_trashed' => null,
);

$html = $controller->renderPartial('admin', array(
    'eventId' => 3, 'periodId' => 4,
    'eventList' => array(3 => 'Đại hội'), 'periodList' => array(4 => 'VCK'),
    'dataProvider' => $dp, 'stats' => FinalAttendeeRosters::getStats(3, 4),
    'filterOptions' => $options, 'lastSyncedAt' => time(),
    'pageSize' => 25, 'pageSizes' => array(25, 50, 100), 'filters' => $filters,
), true);

$apiKey = Yii::app()->params['externalApiKey'];
$apiUrl = Yii::app()->params['externalApiUrl'];

$ok('API key KHÔNG xuất hiện trong HTML', strpos($html, $apiKey) === false);
$ok('không dùng data-api-key', strpos($html, 'data-api-key') === false);
$ok('không nhúng URL External API vào HTML', strpos($html, $apiUrl) === false);
$ok('có URL action proxy của Yii', strpos($html, 'data-filter-options-url') !== false);
$ok('dropdown bộ phận render đúng option', strpos($html, 'An ninh - Kỹ thuật - CNTT') !== false);
$ok('dropdown có mục "Chưa xác định"', strpos($html, 'Chưa xác định') !== false);
