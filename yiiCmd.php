<?php
/**
 * Created by PhpStorm.
 * User: NhatThao
 * Date: 11/25/2018
 * Time: 10:08 AM
 */
// change the following paths if necessary
// change the following paths if necessary
$yii=dirname(__FILE__).'/framework/yii.php';
$config=dirname(__FILE__).'/protected/config/console.php';
// remove the following line when in production mode
defined('YII_DEBUG') or define('YII_DEBUG', true);

// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 3);

require_once($yii);
Yii::createConsoleApplication($config)->run();
