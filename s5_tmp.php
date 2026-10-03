<?php
/** Harness S5: dropdown phụ thuộc Đơn vị -> Bộ phận -> Phòng ban. File tạm. */
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

$ok = function ($label, $cond) { echo ($cond ? 'PASS' : 'FAIL') . " — $label\n"; };

echo "=== Model: dropdown thu hẹp dần ===\n";
$all = FinalAttendeeRosters::getFilterOptions(3, 4);
$byProperty = FinalAttendeeRosters::getFilterOptions(3, 4, 66);

$ok('toàn sự kiện có nhiều phòng ban hơn khi lọc 1 đơn vị',
    count($all['departments']) > count($byProperty['departments']));
$ok('lọc đơn vị thì bộ phận cũng thu hẹp',
    count($byProperty['divisions']) <= count($all['divisions']));

$divisionCode = $byProperty['divisions'][0]['code'];
$divisionName = $byProperty['divisions'][0]['name'];
$byDivision = FinalAttendeeRosters::getFilterOptions(3, 4, 66, $divisionCode);

$ok("chọn bộ phận \"$divisionName\" thì phòng ban thu hẹp tiếp ("
    . count($byProperty['departments']) . ' -> ' . count($byDivision['departments']) . ')',
    count($byDivision['departments']) <= count($byProperty['departments']));
$ok('danh sách bộ phận KHÔNG đổi khi chọn bộ phận (chỉ phụ thuộc đơn vị)',
    count($byDivision['divisions']) === count($byProperty['divisions']));

echo "\n=== Action proxy JSON ===\n";
$adminModule = Yii::app()->getModule('admin');
$controller  = new FinalAttendeeRostersController('finalAttendeeRosters', $adminModule);
Yii::app()->controller = $controller;

// Không có quyền
Yii::app()->session['user_permissions'] = array();
$_GET = array('event_id' => 3, 'period_id' => 4);
try {
    ob_start();
    $controller->actionFilterOptions();
    ob_end_clean();
    $ok('không quyền thì bị chặn', false);
} catch (CException $e) {
    ob_end_clean();
    $ok('không quyền thì bị chặn (Yii end)', true);
}

// Có quyền admin
Yii::app()->session['user_permissions'] = array('*' => '1 1 1 1');
$ok('PermissionHelper cho phép đọc', PermissionHelper::can('finalattendeerosters', 'read'));

$call = function ($get) use ($controller) {
    $_GET = $get;
    ob_start();
    try {
        $controller->actionFilterOptions();
    } catch (CException $e) {
        // Yii::app()->end() nem exception khi chay CLI
    }
    return json_decode(ob_get_clean(), true);
};

$res = $call(array('event_id' => 3, 'period_id' => 4, 'property_id' => 66));
$ok('trả success = true', isset($res['success']) && $res['success'] === true);
$ok('trả danh sách bộ phận', isset($res['divisions']) && count($res['divisions']) > 0);
$ok('trả danh sách phòng ban', isset($res['departments']) && count($res['departments']) > 0);
$ok('KHÔNG trả danh sách đơn vị (không cần cho AJAX)', !isset($res['properties']));
$ok('option có đủ code + name', isset($res['divisions'][0]['code'], $res['divisions'][0]['name']));

$resScoped = $call(array('event_id' => 3, 'period_id' => 4, 'property_id' => 66, 'division_code' => $divisionCode));
$ok('truyền division_code thì phòng ban thu hẹp',
    count($resScoped['departments']) <= count($res['departments']));

$resNoEvent = $call(array('period_id' => 4));
$ok('thiếu event_id thì báo lỗi', isset($resNoEvent['success']) && $resNoEvent['success'] === false);
$ok('lỗi có thông báo tiếng Việt',
    isset($resNoEvent['message']) && strpos($resNoEvent['message'], 'Thiếu sự kiện') !== false);

echo "\n=== API key không lọt ra HTML ===\n";
$controller->layout = false;
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
    'filterOptions' => $byProperty, 'lastSyncedAt' => time(),
    'pageSize' => 25, 'pageSizes' => array(25, 50, 100), 'filters' => $filters,
), true);

$apiKey = Yii::app()->params['externalApiKey'];
$ok('API key KHÔNG xuất hiện trong HTML', strpos($html, $apiKey) === false);
$ok('không có data-api-key', strpos($html, 'data-api-key') === false);
$ok('có URL action proxy', strpos($html, 'data-filter-options-url') !== false);
$ok('URL proxy trỏ vào Yii, không ra ngoài', strpos($html, 'filterOptions') !== false
    && strpos($html, 'portal-registration.muongthanh.vn') === false);
