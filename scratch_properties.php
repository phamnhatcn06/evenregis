<?php
$yii = dirname(__FILE__) . '/framework/yii.php';
$config = dirname(__FILE__) . '/protected/config/main.php';
require_once($yii);
$app = Yii::createWebApplication($config);

$properties = Properties::getApiDataProvider(array(), 500)->getData();
echo "Total properties: " . count($properties) . "\n";
foreach (array_slice($properties, 0, 15) as $p) {
    echo "ID: {$p->id}, Name: {$p->name}, Region ID: " . ($p->region_id ?? 'N/A') . "\n";
}
