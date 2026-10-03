<?php
/**
 * Harness kiểm thử S4: nạp dữ liệu thật từ API local rồi render view, bắt lỗi PHP trong view.
 * File tạm, xoá sau khi chạy.
 */
defined('YII_DEBUG') or define('YII_DEBUG', true);
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/admin.php';
$_SERVER['SCRIPT_NAME']     = '/admin.php';
$_SERVER['REQUEST_URI']     = '/admin.php';
$_SERVER['HTTP_HOST']       = '127.0.0.1:8099';
$_SERVER['SERVER_NAME']     = '127.0.0.1';
$_SERVER['REQUEST_METHOD']  = 'GET';

require_once __DIR__ . '/framework/yii.php';

$config = require(__DIR__ . '/protected/config/main.php');
$config['components']['errorHandler']['errorAction'] = null;
Yii::createWebApplication($config);

$ok = function ($label, $cond) { echo ($cond ? 'PASS' : 'FAIL') . " — $label\n"; };

$eventId  = 3;
$periodId = 4;

echo "=== Model gọi API ===\n";
$stats = FinalAttendeeRosters::getStats($eventId, $periodId);
$ok('getStats trả tổng 619', (int) $stats['total'] === 619);
$ok('getStats có đủ khoá', isset($stats['with_lucky'], $stats['pin_set'], $stats['withdrawn']));

$filterOptions = FinalAttendeeRosters::getFilterOptions($eventId, $periodId);
$ok('getFilterOptions trả 68 đơn vị', count($filterOptions['properties']) === 68);
$ok('getFilterOptions trả bộ phận', count($filterOptions['divisions']) > 0);
$ok('có mục "Chưa xác định" trong bộ phận', (bool) array_filter(
    $filterOptions['divisions'],
    function ($d) { return $d['code'] === FinalAttendeeRosters::FILTER_NONE; }
));

$dp = FinalAttendeeRosters::getApiDataProvider(
    array('event_id' => $eventId, 'period_id' => $periodId),
    25
);
$rows = $dp->getData();
$ok('DataProvider trả 25 dòng/trang', count($rows) === 25);
$ok('tổng số bản ghi = 619', (int) $dp->getTotalItemCount() === 619);
$ok('dòng là model FinalAttendeeRosters', $rows[0] instanceof FinalAttendeeRosters);
$ok('có position_display', property_exists($rows[0], 'position_display'));
$ok('login_pin KHÔNG có trong model', !property_exists($rows[0], 'login_pin'));

echo "\n=== Nhãn hiển thị ===\n";
$ok('badge loại finalist là tiếng Việt', strpos(FinalAttendeeRosters::getTypeBadge('finalist'), 'Vào chung kết') !== false);
$ok('badge huỷ tư cách', strpos(FinalAttendeeRosters::getStatusLabel(1, true), 'Đã huỷ tư cách') !== false);
$ok('badge HO thêm tay', strpos(FinalAttendeeRosters::getStatusLabel(3), 'HO thêm tay') !== false);
$ok('nhãn xung đột tiếng Việt', FinalAttendeeRosters::getConflictLabel('duplicate_lucky') === 'Trùng mã lucky');

echo "\n=== isOverridden / originalValue ===\n";
$m = new FinalAttendeeRosters;
$m->overridden_fields = array('position');
$m->source_snapshot   = array('position' => 'Chức danh gốc');
$ok('isOverridden đúng', $m->isOverridden('position') && !$m->isOverridden('note'));
$ok('originalValue đúng', $m->originalValue('position') === 'Chức danh gốc');
$ok('originalValue trường lạ trả null', $m->originalValue('khong_co') === null);

echo "\n=== Render view ===\n";
$controller = new FinalAttendeeRostersController('finalAttendeeRosters');
$controller->layout = false;
Yii::app()->controller = $controller;

$filters = array(
    'property_id' => null, 'division_code' => null, 'department_code' => null,
    'attendee_type' => null, 'has_lucky' => null, 'has_override' => null,
    'conflict_flag' => null, 'keyword' => null, 'with_trashed' => null,
);

try {
    $html = $controller->renderPartial('_filters', array(
        'eventId' => $eventId, 'periodId' => $periodId,
        'filterOptions' => $filterOptions, 'filters' => $filters,
        'pageSize' => 25, 'pageSizes' => array(25, 50, 100),
    ), true);
    $ok('_filters render không lỗi', strlen($html) > 500);
    $ok('_filters có dropdown đơn vị', strpos($html, 'filter-property') !== false);
    $ok('_filters có checkbox huỷ tư cách', strpos($html, 'filter-with-trashed') !== false);
    $ok('_filters text tiếng Việt có dấu', strpos($html, 'Bộ phận') !== false && strpos($html, 'Phòng ban') !== false);
} catch (Exception $e) {
    $ok('_filters render không lỗi', false);
    echo '   LỖI: ' . $e->getMessage() . "\n";
}

// Dòng đã sửa tay để kiểm ô có dấu override
$sample = $rows[0];
$sample->overridden_fields = array('position');
$sample->source_snapshot   = array('position' => 'Trưởng ca (gốc SMILE)');

try {
    $cell = $controller->renderPartial('_cell', array(
        'row' => $sample, 'field' => 'position',
        'value' => 'Trưởng bộ phận', 'overridden' => array('position'),
    ), true);
    $ok('_cell render ô đã sửa tay', strpos($cell, 'border-warning') !== false);
    $ok('_cell có tooltip giá trị gốc', strpos($cell, 'Trưởng ca (gốc SMILE)') !== false);

    $cellPlain = $controller->renderPartial('_cell', array(
        'row' => $sample, 'field' => 'staff_code',
        'value' => '', 'overridden' => array(),
    ), true);
    $ok('_cell ô rỗng hiện dấu gạch', strpos($cellPlain, '-') !== false);
    $ok('_cell ô thường không có viền cam', strpos($cellPlain, 'border-warning') === false);
} catch (Exception $e) {
    $ok('_cell render', false);
    echo '   LỖI: ' . $e->getMessage() . "\n";
}

try {
    $html = $controller->renderPartial('admin', array(
        'eventId' => $eventId, 'periodId' => $periodId,
        'eventList' => array(3 => 'Đại hội Mường Thanh 2026'),
        'periodList' => array(4 => 'Vòng chung kết'),
        'dataProvider' => $dp, 'stats' => $stats,
        'filterOptions' => $filterOptions, 'lastSyncedAt' => time(),
        'pageSize' => 25, 'pageSizes' => array(25, 50, 100), 'filters' => $filters,
    ), true);
    $ok('admin.php render không lỗi', strlen($html) > 5000);
    $ok('có dải thống kê', strpos($html, 'Tổng số người') !== false);
    $ok('có cột Mã lucky', strpos($html, 'Mã lucky') !== false);
    $ok('badge "Chưa cấp" cho người chưa có mã', strpos($html, 'Chưa cấp') !== false);
    $ok('có badge "Chưa xác định" cho bộ phận trống', strpos($html, 'Chưa xác định') !== false);
    $ok('đăng ký file JS riêng (không inline script)', strpos($html, '<script>') === false);
    $ok('có phân trang', strpos($html, 'pagination') !== false);
    $ok('login_pin KHÔNG xuất hiện trong HTML', strpos($html, 'login_pin') === false);
    $ok('số dòng render = 25', substr_count($html, 'js-cell" data-field="staff_code"') === 25
        || substr_count($html, 'data-field="staff_code"') === 25);
} catch (Exception $e) {
    $ok('admin.php render không lỗi', false);
    echo '   LỖI: ' . $e->getMessage() . "\n";
}

echo "\n=== Chưa chọn sự kiện thì hiện hướng dẫn, không gọi API ===\n";
try {
    $html = $controller->renderPartial('admin', array(
        'eventId' => null, 'periodId' => null,
        'eventList' => array(3 => 'Đại hội'), 'periodList' => array(),
        'dataProvider' => null, 'stats' => null,
        'filterOptions' => array('properties' => array(), 'divisions' => array(), 'departments' => array()),
        'lastSyncedAt' => null, 'pageSize' => 25, 'pageSizes' => array(25, 50, 100), 'filters' => $filters,
    ), true);
    $ok('render được khi chưa chọn sự kiện', strpos($html, 'Vui lòng chọn') !== false);
    $ok('không vẽ bảng khi chưa chọn', strpos($html, 'Mã lucky</th>') === false);
} catch (Exception $e) {
    $ok('render được khi chưa chọn sự kiện', false);
    echo '   LỖI: ' . $e->getMessage() . "\n";
}
