<?php
$yii = dirname(__FILE__) . '/framework/yii.php';
$config = dirname(__FILE__) . '/protected/config/main.php';
require_once($yii);
$app = Yii::createWebApplication($config);

$sports = EventSports::getByEventId(3);
echo "Count of assigned sports for Event 3: " . count($sports) . "\n";
foreach ($sports as $sport) {
    echo "Sport ID: " . $sport['sport_id'] . " | Name: " . $sport['sport_name'] . "\n";
}
