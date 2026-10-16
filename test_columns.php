<?php
// Bootstrap Yii
$yii = dirname(__FILE__) . '/framework/yii.php';
$config = dirname(__FILE__) . '/protected/config/main.php';
require_once($yii);
Yii::createWebApplication($config);

// Kiểm tra lịch thi đấu
$schedule = Daihoi::getTournamentSchedule();
echo "Schedule loaded: " . count($schedule['matches']) . " matches across " . count($schedule['sports']) . " sports.\n";
