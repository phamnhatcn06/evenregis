<?php
$yii = dirname(__FILE__) . '/framework/yii.php';
require_once($yii);
$app = Yii::createWebApplication(dirname(__FILE__).'/protected/config/main.php');
echo "REMOVE result_id=1: ";
echo json_encode(ApiClient::delete('/api/talent-finals/remove/1'), JSON_UNESCAPED_UNICODE)."\n";
