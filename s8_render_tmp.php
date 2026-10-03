<?php
/** Harness S8: render nút cấp mã, badge đối soát, và kiểm nút cũ đã ẩn. File tạm. */
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
Yii::import('application.modules.admin.controllers.RunRegistrationsController');

$ok = function ($label, $cond) { echo ($cond ? 'PASS' : 'FAIL') . " — $label\n"; };

$adminModule = Yii::app()->getModule('admin');
$controller  = new FinalAttendeeRostersController('finalAttendeeRosters', $adminModule);
Yii::app()->controller = $controller;
$controller->layout = false;

$options = FinalAttendeeRosters::getFilterOptions(3, 4);
$stats   = FinalAttendeeRosters::getStats(3, 4);
$audit   = FinalAttendeeRosters::getAudit(3);
$dp      = FinalAttendeeRosters::getApiDataProvider(array('event_id' => 3, 'period_id' => 4), 25);
$dp->pagination->route = 'admin/finalAttendeeRosters/admin';
$filters = array(
    'property_id' => null, 'division_code' => null, 'department_code' => null,
    'attendee_type' => null, 'has_lucky' => null, 'has_override' => null,
    'conflict_flag' => null, 'keyword' => null, 'with_trashed' => null,
);

$html = $controller->renderPartial('admin', array(
    'eventId' => 3, 'periodId' => 4,
    'eventList' => array(3 => 'Đại hội'), 'periodList' => array(4 => 'VCK'),
    'dataProvider' => $dp, 'stats' => $stats, 'audit' => $audit,
    'filterOptions' => $options, 'lastSyncedAt' => time(),
    'pageSize' => 25, 'pageSizes' => array(25, 50, 100), 'filters' => $filters,
), true);

echo "=== Nút và modal cấp mã ===\n";
$ok('có nút Cấp mã lucky', strpos($html, 'data-bs-target="#modal_gen_lucky"') !== false);
$ok('tooltip nhắc mã không bao giờ đổi', strpos($html, 'Mã đã cấp không bao giờ đổi') !== false);
$ok('modal cấp mã được nhúng', strpos($html, 'id="modal_gen_lucky"') !== false);
$ok('modal nhắc chỉ cấp cho người chưa có mã', strpos($html, 'chưa có mã') !== false);
$ok('modal nói rõ không có chức năng cấp lại', strpos($html, 'không có chức năng cấp lại') !== false);
$ok('modal nêu định danh DHMT', strpos($html, 'DHMT') !== false);

echo "\n=== Badge đối soát ===\n";
$ok('có badge dải số thẻ MT', preg_match('/Đã dùng MT:\s*\d+\/999/u', $html) === 1);
$ok('có badge xung đột mã lucky', strpos($html, 'Xung đột mã lucky:') !== false);
$ok('badge xung đột = 0 và không màu đỏ',
    strpos($html, 'Xung đột mã lucky: 0') !== false);

echo "\n=== Mã lucky hiển thị trên bảng ===\n";
$ok('hiện mã lucky', preg_match('/<div class="fw-bold">\d{6}<\/div>/', $html) === 1);
$ok('hiện định danh DHMT + mã', preg_match('/DHMT\d{6}/', $html) === 1);
$ok('có nút sao chép định danh', strpos($html, 'js-copy') !== false);
$ok('KHÔNG còn badge "Chưa cấp"', strpos($html, 'Chưa cấp') === false);
$ok('KHÔNG có đường sửa mã lucky', strpos($html, 'data-field="lucky_number"') === false);
$ok('KHÔNG có input mã lucky trong modal sửa', strpos($html, 'name="fields[lucky_number]"') === false);

echo "\n=== Nút cấp mã cũ đã ẩn ===\n";
$runController = new RunRegistrationsController('runRegistrations', $adminModule);
Yii::app()->controller = $runController;
$runController->layout = false;

$runHtml = $runController->renderPartial('admin', array(
    'eventId' => 3,
    'eventList' => array(3 => 'Đại hội'),
    'registrations' => array(),
), true);

$ok('không còn form POST genLucky', strpos($runHtml, "createUrl('genLucky')") === false
    && strpos($runHtml, 'action="/admin.php?r=admin/runRegistrations/genLucky"') === false);
$ok('không còn nút "Cấp số lucky"', strpos($runHtml, 'Cấp số lucky') === false);
$ok('có link chỉ sang màn Tổng hợp VCK', strpos($runHtml, 'Cấp mã lucky ở màn Tổng hợp VCK') !== false);

echo "\n=== Action cũ trả 410 ===\n";
try {
    $runController->actionGenLucky();
    $ok('actionGenLucky bị chặn', false);
} catch (CHttpException $e) {
    $ok('actionGenLucky bị chặn', true);
    $ok('trả HTTP 410', $e->statusCode === 410);
    $ok('thông báo chỉ sang màn mới',
        strpos($e->getMessage(), 'finalAttendeeRosters') !== false);
    $ok('nêu lý do cấp theo bản ghi',
        strpos($e->getMessage(), 'từng bản ghi') !== false);
}
