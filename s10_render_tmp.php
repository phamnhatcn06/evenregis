<?php
/** Harness S10: render modal thêm người + người thủ công trên bảng. File tạm. */
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
$roleList = array();
foreach (Roles::getApiDataProvider(array(), 200)->getData() as $role) {
    $roleList[$role->id] = $role->name;
}

echo "=== Danh mục vai trò ===\n";
$ok('tải được danh mục vai trò', count($roleList) > 0);
$ok('có vai trò Ban tổ chức', (bool) array_filter($roleList, function ($n) {
    return mb_stripos($n, 'Ban tổ chức') !== false;
}));

echo "\n=== Modal thêm người ===\n";
$modal = $controller->renderPartial('_modal_add_person', array(
    'eventId' => 3, 'periodId' => 4, 'filterOptions' => $options, 'roleList' => $roleList,
), true);

$ok('render không lỗi', strlen($modal) > 1500);
$ok('có id modal_add_person', strpos($modal, 'id="modal_add_person"') !== false);
$ok('họ tên bắt buộc', strpos($modal, 'name="full_name" required') !== false);
$ok('đơn vị bắt buộc', strpos($modal, 'id="add_property_id"') !== false
    && strpos($modal, 'required') !== false);
$ok('có dropdown vai trò', strpos($modal, 'name="role_id"') !== false);
$ok('chọn sẵn vai trò Ban tổ chức',
    preg_match('/<option value="(\d+)" selected="selected">Ban tổ chức<\/option>/u', $modal) === 1);
$ok('KHÔNG có input mã lucky', strpos($modal, 'name="lucky_number"') === false);
$ok('KHÔNG có input số thẻ', strpos($modal, 'name="badge_number"') === false);
$ok('nhắc hệ thống tự cấp mã', strpos($modal, 'tự cấp') !== false);
$ok('nhắc không bị đồng bộ ghi đè', strpos($modal, 'không bị đồng bộ ghi đè hay xoá') !== false);
$ok('nhắc chức danh hiện trên thẻ và email', strpos($modal, 'Hiện trên thẻ tham dự và email') !== false);
$ok('gợi ý nhập mã NV để nhận trùng người', strpos($modal, 'nhận ra trùng người') !== false);
$ok('không có inline script', strpos($modal, '<script') === false);

echo "\n=== Nút trên header ===\n";
$dp = FinalAttendeeRosters::getApiDataProvider(array('event_id' => 3, 'period_id' => 4, 'status' => 3), 25);
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
    'audit' => FinalAttendeeRosters::getAudit(3), 'roleList' => $roleList,
    'filterOptions' => $options, 'lastSyncedAt' => time(),
    'pageSize' => 25, 'pageSizes' => array(25, 50, 100), 'filters' => $filters,
), true);

$ok('có nút Thêm người', strpos($html, 'data-bs-target="#modal_add_person"') !== false);
$ok('modal thêm người được nhúng', strpos($html, 'id="modal_add_person"') !== false);

echo "\n=== Người thủ công hiển thị đúng trên bảng ===\n";
$rows = $dp->getData();
if (empty($rows)) {
    echo "SKIP — không có người thủ công nào trong bảng\n";
} else {
    $manual = $rows[0];
    $ok('lọc được theo status MANUAL', (int) $manual->status === FinalAttendeeRosters::STATUS_MANUAL);
    $ok('badge hiện "HO thêm tay"',
        strpos(FinalAttendeeRosters::getStatusLabel($manual->status), 'HO thêm tay') !== false);
    $ok('badge loại hiện "HO thêm tay"',
        strpos(FinalAttendeeRosters::getTypeBadge($manual->attendee_type), 'HO thêm tay') !== false);
    $ok('có mã lucky', !empty($manual->lucky_number));
    $ok('có định danh DHMT', strpos((string) $manual->login_identifier, 'DHMT') === 0);
    $ok('có số thẻ MT', preg_match('/^MT\d{3}$/', (string) $manual->badge_number) === 1);
    $ok('mọi trường đã nhập đều đánh dấu sửa tay', (int) $manual->has_override === 1);
    $ok('KHÔNG có nút khôi phục gốc (không có nguồn)',
        !$manual->hasOriginalValue('position'));
}

echo "\n=== Quyền chỉ đọc thì không thấy nút thêm ===\n";
Yii::app()->session['sso_permissions'] = array('finalattendeerosters' => '0 1 0 0');
$htmlReadOnly = $controller->renderPartial('admin', array(
    'eventId' => 3, 'periodId' => 4,
    'eventList' => array(3 => 'Đại hội'), 'periodList' => array(4 => 'VCK'),
    'dataProvider' => $dp, 'stats' => FinalAttendeeRosters::getStats(3, 4),
    'audit' => null, 'roleList' => $roleList,
    'filterOptions' => $options, 'lastSyncedAt' => time(),
    'pageSize' => 25, 'pageSizes' => array(25, 50, 100), 'filters' => $filters,
), true);
$ok('không có nút Thêm người', strpos($htmlReadOnly, 'data-bs-target="#modal_add_person"') === false);
$ok('không nhúng modal thêm người', strpos($htmlReadOnly, 'id="modal_add_person"') === false);
