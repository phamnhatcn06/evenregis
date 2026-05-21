<?php
// Bootstrap Yii
$yii = dirname(__FILE__) . '/framework/yii.php';
$config = dirname(__FILE__) . '/protected/config/main.php';
require_once($yii);
Yii::createWebApplication($config);

$columns = Yii::app()->db->createCommand("DESCRIBE beauty_contestants")->queryAll();
foreach ($columns as $col) {
    echo $col['Field'] . " - " . $col['Type'] . "\n";
}
