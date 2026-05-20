<?php
$yii = dirname(__FILE__) . '/framework/yii.php';
$config = dirname(__FILE__) . '/protected/config/main.php';
require_once($yii);
$app = Yii::createWebApplication($config);

echo "Fetching all sports...\n";
$allSportsResult = ApiClient::get('/api/sports');
if (!$allSportsResult['success'] || !isset($allSportsResult['data']['data'])) {
    die("Failed to fetch sports.\n");
}

$sports = $allSportsResult['data']['data'];
$eventId = 3; // Event: Đại hội Mường Thanh 2026

echo "Assigning child sports to Event {$eventId}:\n";
$assignedCount = 0;
foreach ($sports as $sport) {
    $sportId = $sport['id'];
    $parentId = $sport['parent_id'];
    $sportName = $sport['name'];

    // Only assign child sports (parent_id > 0)
    if ($parentId > 0) {
        echo "Assigning Sport ID: {$sportId} | {$sportName}...\n";
        $result = EventSports::storeViaApi($eventId, $sportId);
        if ($result['success']) {
            echo "-> Success!\n";
            $assignedCount++;
        } else {
            echo "-> Failed: " . (isset($result['error']) ? $result['error'] : 'Unknown error') . "\n";
        }
    }
}

echo "Finished! Total sports assigned: {$assignedCount}\n";
