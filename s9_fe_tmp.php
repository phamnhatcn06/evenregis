<?php
/** Harness S9 FE: chức danh sửa tay phải tới được email. File tạm. */
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

$ok = function ($label, $cond) { echo ($cond ? 'PASS' : 'FAIL') . " — $label\n"; };

echo "=== Resolver chức danh của EmailHelper ===\n";
$resolve = new ReflectionMethod('EmailHelper', 'resolveAttendeePosition');
$resolve->setAccessible(true);
$call = function ($info) use ($resolve) { return $resolve->invoke(null, $info); };

$ok('ưu tiên position (HO sửa) hơn position_name (gốc SMILE)',
    $call(array('position' => 'Trưởng bộ phận (HO sửa)', 'position_name' => 'Trưởng ca'))
        === 'Trưởng bộ phận (HO sửa)');
$ok('không có position thì dùng position_name',
    $call(array('position' => null, 'position_name' => 'Trưởng ca')) === 'Trưởng ca');
$ok('position rỗng/khoảng trắng cũng rơi về position_name',
    $call(array('position' => '   ', 'position_name' => 'Trưởng ca')) === 'Trưởng ca');
$ok('không có gì thì trả rỗng', $call(array()) === '');
$ok('tham số không phải mảng thì trả rỗng', $call(null) === '');

echo "\n=== Mọi chỗ dựng dữ liệu email đều dùng resolver ===\n";
$source = file_get_contents(__DIR__ . '/protected/components/EmailHelper.php');
$ok('không còn chỗ nào ưu tiên position_name theo cách cũ',
    strpos($source, "isset(\$fa['position_name']) ? \$fa['position_name'] : (isset(\$fa['position'])") === false);
$ok('không còn chỗ đọc thẳng position_name của attendeeInfo',
    strpos($source, "isset(\$attendeeInfo['position_name']) ? \$attendeeInfo['position_name']") === false);
$ok('không còn chỗ đọc thẳng position_name của info',
    strpos($source, "isset(\$info['position_name']) ? \$info['position_name']") === false);
$ok('resolver được dùng ở đúng 7 chỗ dựng dữ liệu',
    substr_count($source, 'self::resolveAttendeePosition(') === 7);

echo "\n=== Dữ liệu thật: chức danh sửa tay có tới email không ===\n";
$rows = FinalAttendeeRosters::getApiDataProvider(
    array('event_id' => 3, 'period_id' => 4, 'has_override' => 1),
    5
)->getData();

if (empty($rows)) {
    echo "SKIP — hiện không có dòng nào đang sửa tay để kiểm (dữ liệu đã được dọn sạch)\n";
} else {
    $row = $rows[0];
    $attendee = Attendees::fetchFromApi($row->attendee_id);
    $ok('attendees.position = giá trị HO sửa', $attendee && $attendee->position === $row->position);
    $ok('resolver trả về giá trị HO sửa',
        $call(array('position' => $attendee->position, 'position_name' => $attendee->position_name))
            === $row->position);
}
