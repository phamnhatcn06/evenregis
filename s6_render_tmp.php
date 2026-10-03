<?php
/** Harness S6: render modal đồng bộ. File tạm. */
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

$options = FinalAttendeeRosters::getFilterOptions(3, 4);

echo "=== Modal đồng bộ ===\n";
$modal = $controller->renderPartial('_modal_sync', array(
    'eventId' => 3, 'periodId' => 4, 'filterOptions' => $options,
), true);

$ok('render không lỗi', strlen($modal) > 1000);
$ok('có id modal_sync', strpos($modal, 'id="modal_sync"') !== false);
$ok('có nút Xem trước', strpos($modal, 'btn_sync_preview') !== false);
$ok('nút Ghi thật mặc định bị disable', preg_match('/id="btn_sync_apply"[^>]*disabled/', $modal) === 1);
$ok('có URL xem trước riêng', strpos($modal, 'data-preview-url') !== false);
$ok('dropdown phạm vi có "Toàn bộ sự kiện"', strpos($modal, 'Toàn bộ sự kiện') !== false);
$ok('dropdown liệt kê đủ 68 đơn vị', substr_count($modal, '<option value="') >= 68);
$ok('có nhắc không ghi đè trường sửa tay', strpos($modal, 'không bao giờ ghi đè') !== false);
$ok('có nhắc không chạm mã lucky', strpos($modal, 'không chạm vào mã lucky') !== false);
$ok('có đủ 7 ô số liệu', strpos($modal, 'sync_sum_inserted') !== false
    && strpos($modal, 'sync_sum_restored') !== false
    && strpos($modal, 'sync_sum_updated') !== false
    && strpos($modal, 'sync_sum_unchanged') !== false
    && strpos($modal, 'sync_sum_skipped') !== false
    && strpos($modal, 'sync_sum_deleted') !== false
    && strpos($modal, 'sync_sum_conflicts') !== false);
$ok('có 5 khối chi tiết', strpos($modal, 'sync_list_skipped') !== false
    && strpos($modal, 'sync_list_restored') !== false
    && strpos($modal, 'sync_list_deleted') !== false
    && strpos($modal, 'sync_list_conflicts') !== false
    && strpos($modal, 'sync_list_inserted') !== false);
$ok('text tiếng Việt có dấu', strpos($modal, 'Huỷ tư cách') !== false
    && strpos($modal, 'Bỏ qua do đã sửa tay') !== false);
$ok('không có inline script', strpos($modal, '<script') === false);

echo "\n=== View chính nhúng modal + bật nút ===\n";
$dp = FinalAttendeeRosters::getApiDataProvider(array('event_id' => 3, 'period_id' => 4), 25);
$dp->pagination->route = 'admin/finalAttendeeRosters/admin';
$filters = array(
    'property_id' => null, 'division_code' => null, 'department_code' => null,
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

$ok('nút đồng bộ KHÔNG còn bị disable', strpos($html, 'Chức năng đồng bộ sẽ bật') === false);
$ok('nút mở modal bằng data-bs-target', strpos($html, 'data-bs-target="#modal_sync"') !== false);
$ok('modal được nhúng trong trang', strpos($html, 'id="modal_sync"') !== false);
$ok('API key vẫn không lọt ra HTML', strpos($html, Yii::app()->params['externalApiKey']) === false);

echo "\n=== Không có quyền tạo thì không có modal ===\n";
Yii::app()->session['sso_permissions'] = array('finalattendeerosters' => '0 1 0 0');
$htmlReadOnly = $controller->renderPartial('admin', array(
    'eventId' => 3, 'periodId' => 4,
    'eventList' => array(3 => 'Đại hội'), 'periodList' => array(4 => 'VCK'),
    'dataProvider' => $dp, 'stats' => FinalAttendeeRosters::getStats(3, 4),
    'filterOptions' => $options, 'lastSyncedAt' => time(),
    'pageSize' => 25, 'pageSizes' => array(25, 50, 100), 'filters' => $filters,
), true);

$ok('không hiện nút đồng bộ', strpos($htmlReadOnly, 'data-bs-target="#modal_sync"') === false);
$ok('không nhúng modal', strpos($htmlReadOnly, 'id="modal_sync"') === false);
$ok('vẫn xem được bảng danh sách', strpos($htmlReadOnly, 'Mã lucky') !== false);
