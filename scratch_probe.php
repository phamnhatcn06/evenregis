<?php
// Script chẩn đoán tạm thời — XÓA sau khi dùng.
// Mô phỏng resolveExistingProfile để xác nhận chọn đúng hồ sơ.
defined('YII_DEBUG') or define('YII_DEBUG', true);
$yii = dirname(__FILE__) . '/framework/yii.php';
require_once($yii);
$config = require(dirname(__FILE__) . '/protected/config/console.php');
$config['params'] = require(dirname(__FILE__) . '/protected/config/params.php');
Yii::createConsoleApplication($config);

function identityMatches($a, $staffId, $staffCode, $idCard) {
    if ($staffCode !== '' && !empty($a['staff_code']) && strtolower(trim((string)$a['staff_code'])) === strtolower(trim((string)$staffCode))) return true;
    if ($staffId && !empty($a['staff_id']) && (string)$a['staff_id'] === (string)$staffId) return true;
    if ($idCard !== '' && !empty($a['id_card']) && trim((string)$a['id_card']) === trim((string)$idCard)) return true;
    return false;
}

function resolve($all, $staffId, $staffCode, $idCard, $excludeId, $excludeReg) {
    $cands = array();
    foreach ($all as $a) {
        $aid = isset($a['id']) ? (string)$a['id'] : '';
        if ($aid === '' || $aid === (string)$excludeId) continue;
        if ($excludeReg !== null && isset($a['registration_id']) && (string)$a['registration_id'] === (string)$excludeReg) continue;
        if (!identityMatches($a, $staffId, $staffCode, $idCard)) continue;
        $cands[$aid] = !empty($a['photo_path']) || !empty($a['portrait_path']);
    }
    if (!$cands) return "NONE";
    uksort($cands, function($x,$y) use ($cands){ if($cands[$x]!==$cands[$y]) return $cands[$x]?-1:1; return ((int)$y)-((int)$x); });
    return $cands;
}

$r = ApiClient::get(ApiEndpoints::ATTENDEE_LIST, array('per_page' => 5000));
$all = isset($r['data']['data']) ? $r['data']['data'] : array();

echo "Staff 9786, NO reg exclusion:\n";
var_export(resolve($all, '9786', '', '', null, null)); echo "\n\n";

echo "Staff 9786, EXCLUDE reg 104 (current):\n";
var_export(resolve($all, '9786', '', '', null, '104')); echo "\n";
echo "=> top candidate should be 323 (Lê Hoàng)\n\n";

echo "Staff 640499 (Quynh real), EXCLUDE reg 76:\n";
var_export(resolve($all, '640499', '', '', null, '76')); echo "\n";
