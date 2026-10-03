<?php
/** Harness S7: render bảng có nút sửa + modal sửa + nút khôi phục. File tạm. */
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

$adminModule = Yii::app()->getModule('admin');
$controller  = new FinalAttendeeRostersController('finalAttendeeRosters', $adminModule);
Yii::app()->controller = $controller;
$controller->layout = false;

$options = FinalAttendeeRosters::getFilterOptions(3, 4);
$filters = array(
    'property_id' => null, 'division_code' => null, 'department_code' => null,
    'attendee_type' => null, 'has_lucky' => null, 'has_override' => '1',
    'conflict_flag' => null, 'keyword' => null, 'with_trashed' => null,
);

$render = function () use ($controller, $options, $filters) {
    $dp = FinalAttendeeRosters::getApiDataProvider(
        array('event_id' => 3, 'period_id' => 4, 'has_override' => 1),
        25
    );
    $dp->pagination->route = 'admin/finalAttendeeRosters/admin';

    return $controller->renderPartial('admin', array(
        'eventId' => 3, 'periodId' => 4,
        'eventList' => array(3 => 'Đại hội'), 'periodList' => array(4 => 'VCK'),
        'dataProvider' => $dp, 'stats' => FinalAttendeeRosters::getStats(3, 4),
        'filterOptions' => $options, 'lastSyncedAt' => time(),
        'pageSize' => 25, 'pageSizes' => array(25, 50, 100), 'filters' => $filters,
    ), true);
};

echo "=== Có quyền sửa ===\n";
Yii::app()->session['sso_permissions'] = array('*' => '1 1 1 1');
$html = $render();

$ok('có cột Thao tác', strpos($html, '>Thao tác</th>') !== false);
$ok('có nút sửa từng dòng', strpos($html, 'js-edit-row') !== false);
$ok('nút sửa mang dữ liệu dòng', strpos($html, 'data-row=') !== false);
$ok('modal sửa được nhúng', strpos($html, 'id="modal_edit_row"') !== false);
$ok('modal KHÔNG có input mã lucky', strpos($html, 'name="fields[lucky_number]"') === false);
$ok('modal có cảnh báo lan sang thẻ và email', strpos($html, 'email xác nhận') !== false);
$ok('có nút khôi phục trong ô đã sửa tay', strpos($html, 'js-cell-reset') !== false);
$ok('có nút khôi phục toàn bộ', strpos($html, 'btn_reset_all') !== false);
$ok('config có URL sửa trường', strpos($html, 'data-update-field-url') !== false);
$ok('config có URL khôi phục', strpos($html, 'data-reset-field-url') !== false);
$ok('config có nhãn trường tiếng Việt', strpos($html, 'data-field-labels') !== false);
$ok('tooltip hiện giá trị gốc', strpos($html, 'Đã sửa tay — Gốc:') !== false);
$ok('badge đếm số trường đã sửa', preg_match('/Đã sửa tay \(\d+ trường\)/u', $html) === 1);
$ok('không có inline script', strpos($html, '<script>') === false);
$ok('API key không lọt ra HTML', strpos($html, Yii::app()->params['externalApiKey']) === false);

echo "\n=== Chỉ có quyền đọc ===\n";
Yii::app()->session['sso_permissions'] = array('finalattendeerosters' => '0 1 0 0');
$htmlReadOnly = $render();

$ok('không có cột Thao tác', strpos($htmlReadOnly, '>Thao tác</th>') === false);
$ok('không có nút sửa', strpos($htmlReadOnly, 'js-edit-row') === false);
$ok('không nhúng modal sửa', strpos($htmlReadOnly, 'id="modal_edit_row"') === false);
$ok('không có nút khôi phục trong ô', strpos($htmlReadOnly, 'js-cell-reset') === false);
$ok('vẫn thấy dấu đã sửa tay (chỉ đọc)', strpos($htmlReadOnly, 'border-warning') !== false);
$ok('vẫn xem được bảng', strpos($htmlReadOnly, 'Mã lucky') !== false);
