<?php
$yii = dirname(__FILE__) . '/framework/yii.php';
$config = dirname(__FILE__) . '/protected/config/main.php';
require_once($yii);
$app = Yii::createWebApplication($config);

$result = ApiClient::get(ApiEndpoints::COMPETITION_REGISTRATION_LIST_BY_PROPERTY, array(
    'event_id' => 3,
));

echo "Success: " . ($result['success'] ? 'yes' : 'no') . "\n";
if ($result['success']) {
    var_dump($result['data']);
} else {
    echo "Error: " . $result['error'] . "\n";
}
