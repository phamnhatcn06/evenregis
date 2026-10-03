<?php
/** Harness S4 phần 2: lọc qua model FE + hiển thị trạng thái override/huỷ tư cách. File tạm. */
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
$total = function ($params) {
    $dp = FinalAttendeeRosters::getApiDataProvider($params, 1);
    return (int) $dp->getTotalItemCount();
};

$base = array('event_id' => 3, 'period_id' => 4);

echo "=== Lọc qua model FE ===\n";
$all = $total($base);
$ok("không lọc = 619 (được $all)", $all === 619);

$opts = FinalAttendeeRosters::getFilterOptions(3, 4);
$firstProperty = $opts['properties'][0];
$byProperty = $total(array_merge($base, array('property_id' => $firstProperty['id'])));
$ok("lọc đơn vị \"{$firstProperty['name']}\" ra $byProperty người, nhỏ hơn tổng", $byProperty > 0 && $byProperty < $all);

$noneDivision = $total(array_merge($base, array('division_code' => FinalAttendeeRosters::FILTER_NONE)));
$ok("lọc bộ phận \"Chưa xác định\" ra $noneDivision người", $noneDivision > 0);

// Option bộ phận gộp nhiều mã (BE gộp các mã cùng tên)
$multiCode = null;
foreach ($opts['departments'] as $d) {
    if (strpos($d['code'], ',') !== false) { $multiCode = $d; break; }
}
if ($multiCode) {
    $codes = explode(',', $multiCode['code']);
    $sum = 0;
    foreach ($codes as $c) {
        $sum += $total(array_merge($base, array('department_code' => $c)));
    }
    $grouped = $total(array_merge($base, array('department_code' => $multiCode['code'])));
    $ok("option gộp \"{$multiCode['name']}\" ({$multiCode['code']}): gộp $grouped = tổng từng mã $sum", $grouped === $sum && $grouped > 0);
} else {
    echo "SKIP — không có option bộ phận/phòng ban nào gộp nhiều mã\n";
}

$finalist = $total(array_merge($base, array('attendee_type' => 'finalist')));
$ok("lọc loại finalist ra $finalist người", $finalist > 0 && $finalist < $all);

$keyword = $total(array_merge($base, array('keyword' => 'Nguyễn')));
$ok("tìm từ khoá \"Nguyễn\" ra $keyword người", $keyword > 0 && $keyword < $all);

$ok('lọc has_lucky=1 ra 0 (chưa cấp mã)', $total(array_merge($base, array('has_lucky' => 1))) === 0);
$ok('lọc has_lucky=0 ra đủ 619', $total(array_merge($base, array('has_lucky' => 0))) === 619);

echo "\n=== Hiển thị trạng thái đã sửa tay / huỷ tư cách ===\n";
$adminModule = Yii::app()->getModule('admin');
$controller  = new FinalAttendeeRostersController('finalAttendeeRosters', $adminModule);
$controller->layout = false;
Yii::app()->controller = $controller;

$dp = FinalAttendeeRosters::getApiDataProvider($base, 25);
$dp->pagination->route = 'admin/finalAttendeeRosters/admin';

$rows = $dp->getData();
$rows[0]->overridden_fields = array('position', 'division_name');
$rows[0]->source_snapshot   = array('position' => 'Trưởng ca', 'division_name' => 'Bộ phận cũ');
$rows[1]->is_withdrawn      = true;
$rows[1]->lucky_number      = '930001';
$rows[1]->login_identifier  = 'DHMT930001';
$rows[2]->conflict_flag     = 'duplicate_lucky';

$stats = array(
    'total' => 619, 'with_lucky' => 1, 'without_lucky' => 618, 'pin_set' => 0,
    'with_override' => 1, 'conflicts' => 1, 'manual' => 0, 'withdrawn' => 1,
);
$filters = array(
    'property_id' => null, 'division_code' => null, 'department_code' => null,
    'attendee_type' => null, 'has_lucky' => null, 'has_override' => null,
    'conflict_flag' => null, 'keyword' => null, 'with_trashed' => null,
);

$html = $controller->renderPartial('admin', array(
    'eventId' => 3, 'periodId' => 4,
    'eventList' => array(3 => 'Đại hội'), 'periodList' => array(4 => 'VCK'),
    'dataProvider' => $dp, 'stats' => $stats,
    'filterOptions' => $opts, 'lastSyncedAt' => time() - 90000,
    'pageSize' => 25, 'pageSizes' => array(25, 50, 100), 'filters' => $filters,
), true);

$ok('badge "Đã sửa tay (2 trường)"', strpos($html, 'Đã sửa tay (2 trường)') !== false);
$ok('ô override có viền cam', strpos($html, 'border-warning') !== false);
$ok('tooltip hiện giá trị gốc "Trưởng ca"', strpos($html, 'Trưởng ca') !== false);
$ok('dòng huỷ tư cách có badge đỏ', strpos($html, 'Đã huỷ tư cách') !== false);
$ok('dòng huỷ tư cách bị làm mờ', strpos($html, 'table-secondary opacity-75') !== false);
$ok('người huỷ tư cách VẪN hiện mã lucky', strpos($html, 'DHMT930001') !== false);
$ok('badge xung đột "Trùng mã lucky"', strpos($html, 'Trùng mã lucky') !== false);
$ok('nhắc dữ liệu cũ sau 24h', strpos($html, 'Dữ liệu có thể đã cũ') !== false);
$ok('nhắc có người bị ẩn + link hiện họ', strpos($html, 'đã huỷ tư cách đang bị ẩn') !== false);
$ok('nút sao chép định danh', strpos($html, 'js-copy') !== false);
$ok('KHÔNG có đường sửa mã lucky', strpos($html, 'data-field="lucky_number"') === false);
