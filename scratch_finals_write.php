<?php
// GHI THỬ có kiểm soát: thêm 1 tiết mục vào chung kết rồi GỠ ngay để khôi phục.
$yii = dirname(__FILE__) . '/framework/yii.php';
$config = dirname(__FILE__) . '/protected/config/main.php';
require_once($yii);
$app = Yii::createWebApplication($config);

$showId = 1;

echo "1) Danh sách chung kết TRƯỚC:\n";
$before = TalentEntries::getFinalists($showId);
echo "   count=".count($before)."\n";

$existing = array();
foreach ($before as $f) if (isset($f['entry_id'])) $existing[$f['entry_id']] = true;

// Chọn 1 entry chưa vào chung kết
$raw = TalentEntries::getRawList(array('show_id'=>$showId), 50);
$pick = null;
foreach ($raw as $r) {
    if (isset($r['id']) && !isset($existing[$r['id']])) { $pick = $r; break; }
}
if (!$pick) { echo "Không có entry để test.\n"; exit; }
echo "2) Chọn entry id={$pick['id']} title={$pick['title']}\n";

echo "3) ADD vào chung kết...\n";
$add = TalentEntries::addToFinal($showId, array($pick['id']));
echo "   ".json_encode($add, JSON_UNESCAPED_UNICODE)."\n";

echo "4) Danh sách chung kết SAU khi add:\n";
$after = TalentEntries::getFinalists($showId);
echo "   count=".count($after)."\n";
if ($after) echo "   keys=[".implode(', ', array_keys($after[0]))."]\n   ".json_encode($after[0], JSON_UNESCAPED_UNICODE)."\n";

// Tìm bản ghi vừa thêm để gỡ
$removeId = null;
foreach ($after as $f) {
    if (isset($f['entry_id']) && $f['entry_id'] == $pick['id']) { $removeId = isset($f['id']) ? $f['id'] : null; break; }
}
echo "5) removeId = ".var_export($removeId, true)."\n";

if ($removeId !== null) {
    echo "6) REMOVE để khôi phục...\n";
    $rm = TalentEntries::removeFromFinal($removeId);
    echo "   ".json_encode($rm, JSON_UNESCAPED_UNICODE)."\n";
    $final = TalentEntries::getFinalists($showId);
    echo "7) Danh sách chung kết SAU khi remove: count=".count($final)." (kỳ vọng = ".count($before).")\n";
} else {
    echo "!! Không xác định được id để gỡ — CẦN GỠ THỦ CÔNG entry_id={$pick['id']} khỏi chung kết.\n";
}
echo "DONE\n";
