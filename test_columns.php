<?php
// Bootstrap Yii
$yii = dirname(__FILE__) . '/framework/yii.php';
$config = dirname(__FILE__) . '/protected/config/main.php';
require_once($yii);
Yii::createWebApplication($config);

$queries = array(
    array('attendee_id' => 'Yến'),
    array('attendee_name' => 'Yến'),
    array('full_name' => 'Yến'),
    array('search' => 'Yến')
);

foreach ($queries as $q) {
    $result = ApiClient::get(ApiEndpoints::BEAUTY_CONTESTANT_LIST, $q);
    $count = 0;
    if ($result['success'] && isset($result['data']['data'])) {
        $count = count($result['data']['data']);
    }
    echo "Query " . json_encode($q) . " returned: $count records\n";
}
