<?php
$yii = dirname(__FILE__) . '/framework/yii.php';
$config = dirname(__FILE__) . '/protected/config/main.php';
require_once($yii);
$app = Yii::createWebApplication($config);

echo "Querying sport team members...\n";
$membersRes = ApiClient::get(ApiEndpoints::SPORT_TEAM_MEMBER_LIST, array('per_page' => 10));
if ($membersRes['success']) {
    $data = isset($membersRes['data']['data']) ? $membersRes['data']['data'] : $membersRes['data'];
    if (count($data) > 0) {
        echo "First member attributes:\n";
        print_r($data[0]);
    } else {
        echo "No members found.\n";
    }
} else {
    echo "API failed: " . $membersRes['error'] . "\n";
}
