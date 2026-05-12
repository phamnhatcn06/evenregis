<?php
/**
 * Admin Entry Script
 * Yii1 Framework - PHP 7.4 Compatible
 */

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 3);

$yii = dirname(__FILE__) . '/framework/yii.php';
$config = dirname(__FILE__) . '/protected/config/admin.php';

require_once($yii);
Yii::createWebApplication($config)->run();
