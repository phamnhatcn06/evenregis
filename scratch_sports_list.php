<?php
$yii = dirname(__FILE__) . '/framework/yii.php';
$config = dirname(__FILE__) . '/protected/config/main.php';
require_once($yii);
$app = Yii::createWebApplication($config);

$allSportsResult = ApiClient::get('/api/sports');
if ($allSportsResult['success'] && isset($allSportsResult['data']['data'])) {
    foreach ($allSportsResult['data']['data'] as $item) {
        echo "ID: " . $item['id'] . " | Name: " . $item['name'] . " | Parent ID: " . $item['parent_id'] . "\n";
    }
} else {
    echo "Failed to get sports.\n";
}
