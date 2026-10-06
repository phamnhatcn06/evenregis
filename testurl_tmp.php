<?php
// [TEMP] test parseUrl — xoá sau khi dùng
defined('YII_DEBUG') or define('YII_DEBUG', true);
$config = require(__DIR__ . '/protected/config/main.php');
require_once(__DIR__ . '/framework/yii.php');
Yii::createWebApplication($config);
$um = Yii::app()->getUrlManager();

class FakeReq extends CHttpRequest {
    public $pi = 'run';
    public function getPathInfo() { return $this->pi; }
    public function getQueryString() { return ''; }
    public function getRequestUri() { return '/' . $this->pi; }
}
$r = new FakeReq();
echo 'parse [run] => ' . $um->parseUrl($r) . PHP_EOL;
$r->pi = 'run/login';
echo 'parse [run/login] => ' . $um->parseUrl($r) . PHP_EOL;
$r->pi = 'daihoi';
echo 'parse [daihoi] => ' . $um->parseUrl($r) . PHP_EOL;
