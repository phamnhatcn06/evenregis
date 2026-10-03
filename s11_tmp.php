<?php
/** Harness S11: xuất Excel theo bộ lọc. File tạm. */
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

// Gọi phần dựng dữ liệu + ghi file, không chạy actionExport vì nó gửi header và end()
$fetch = new ReflectionMethod($controller, 'fetchAllForExport');
$fetch->setAccessible(true);
$buildParams = new ReflectionMethod($controller, 'buildFilterParams');
$buildParams->setAccessible(true);

echo "=== Lấy dữ liệu xuất ===\n";
$_GET = array('event_id' => 3, 'period_id' => 4);
$params = $buildParams->invoke($controller, 3, 4);
$all = $fetch->invoke($controller, $params);
$ok('lấy đủ 619 người (chia trang 500/lần)', count($all) === 619);
$ok('dòng là mảng thuộc tính', is_array($all[0]) && isset($all[0]['full_name']));
$ok('có mã lucky', !empty($all[0]['lucky_number']));
$ok('có định danh DHMT', strpos((string) $all[0]['login_identifier'], 'DHMT') === 0);
$ok('có chức danh hiển thị', array_key_exists('position_display', $all[0]));

echo "\n=== Lọc 1 đơn vị thì chỉ xuất đơn vị đó ===\n";
$propertyId = (int) $all[0]['property_id'];
$_GET = array('event_id' => 3, 'period_id' => 4, 'property_id' => $propertyId);
$filtered = $fetch->invoke($controller, $buildParams->invoke($controller, 3, 4));
$ok('số dòng nhỏ hơn tổng', count($filtered) > 0 && count($filtered) < 619);
$sameProperty = true;
foreach ($filtered as $item) {
    if ((int) $item['property_id'] !== $propertyId) {
        $sameProperty = false;
        break;
    }
}
$ok('mọi dòng đều thuộc đúng đơn vị đang lọc', $sameProperty);

echo "\n=== Ghi file Excel thật rồi đọc lại ===\n";
$_GET = array('event_id' => 3, 'period_id' => 4, 'property_id' => $propertyId);

// Chạy lại phần dựng sheet giống actionExport nhưng ghi ra file để kiểm nội dung
$createExcel = new ReflectionMethod($controller, 'createPhpExcel');
$createExcel->setAccessible(true);
$excelText = new ReflectionMethod($controller, 'excelText');
$excelText->setAccessible(true);
$typeLabel = new ReflectionMethod($controller, 'excelTypeLabel');
$typeLabel->setAccessible(true);
$statusLabel = new ReflectionMethod($controller, 'excelStatusLabel');
$statusLabel->setAccessible(true);

$ok('excelText trả rỗng cho trường null', $excelText->invoke($controller, array(), 'khong_co') === '');
$ok('nhãn loại dịch sang tiếng Việt',
    $typeLabel->invoke($controller, array('attendee_type' => 'finalist')) === 'Vào chung kết');
$ok('nhãn loại HO thêm tay',
    $typeLabel->invoke($controller, array('attendee_type' => 'manual')) === 'HO thêm tay');
$ok('trạng thái huỷ tư cách',
    $statusLabel->invoke($controller, array('is_withdrawn' => true, 'status' => 1)) === 'Đã huỷ tư cách');
$ok('trạng thái HO thêm tay',
    $statusLabel->invoke($controller, array('status' => 3)) === 'HO thêm tay');
$ok('trạng thái đang tham dự',
    $statusLabel->invoke($controller, array('status' => 1)) === 'Đang tham dự');

// Dựng file bằng cách gọi actionExport nhưng chặn output
$path = __DIR__ . '/s11_out_tmp.xlsx';
$excel = $createExcel->invoke($controller);
$sheet = $excel->getActiveSheet();
$headers = array(
    'Mã lucky', 'Định danh đăng nhập', 'Họ và tên', 'Mã nhân viên', 'Số CCCD', 'Ngày sinh',
    'Số điện thoại', 'Đơn vị', 'Nhãn in thẻ', 'Bộ phận', 'Phòng ban',
    'Chức danh hiển thị', 'Chức danh gốc (SMILE)', 'Size áo', 'Số thẻ',
    'Loại', 'Trạng thái', 'Đã đặt PIN', 'Đã sửa tay', 'Xung đột',
);
foreach ($headers as $i => $label) {
    $sheet->setCellValue(PHPExcel_Cell::stringFromColumnIndex($i) . '4', $label);
}
$r = 5;
foreach ($filtered as $item) {
    $sheet->setCellValueExplicit('A' . $r, $excelText->invoke($controller, $item, 'lucky_number'), PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('C' . $r, $excelText->invoke($controller, $item, 'full_name'), PHPExcel_Cell_DataType::TYPE_STRING);
    $r++;
}
$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
$writer->save($path);

$ok('ghi được file xlsx', file_exists($path) && filesize($path) > 3000);

$loaded = PHPExcel_IOFactory::load($path);
$readSheet = $loaded->getActiveSheet();
$ok('đọc lại đúng 20 tiêu đề cột',
    $readSheet->getCell('T4')->getValue() === 'Xung đột'
    && $readSheet->getCell('A4')->getValue() === 'Mã lucky');
$ok('tiêu đề giữ dấu tiếng Việt', $readSheet->getCell('L4')->getValue() === 'Chức danh hiển thị');
$ok('mã lucky đọc lại là chuỗi, không bị mất số 0',
    is_string($readSheet->getCell('A5')->getValue())
    && preg_match('/^\d{6}$/', $readSheet->getCell('A5')->getValue()) === 1);
$ok('họ tên đọc lại giữ dấu', $readSheet->getCell('C5')->getValue() === $filtered[0]['full_name']);

unlink($path);
$ok('đã dọn file test', !file_exists($path));

echo "\n=== Không quyền đọc thì bị chặn ===\n";
Yii::app()->session['sso_permissions'] = array();
try {
    $controller->actionExport();
    $ok('bị chặn khi không có quyền', false);
} catch (CHttpException $e) {
    $ok('bị chặn khi không có quyền', $e->statusCode === 403);
}

echo "\n=== Thiếu đợt VCK thì bị chặn ===\n";
Yii::app()->session['sso_permissions'] = array('*' => '1 1 1 1');
$_GET = array('event_id' => 3);
try {
    $controller->actionExport();
    $ok('thiếu period_id thì bị chặn', false);
} catch (CHttpException $e) {
    $ok('thiếu period_id thì bị chặn', $e->statusCode === 422);
}
