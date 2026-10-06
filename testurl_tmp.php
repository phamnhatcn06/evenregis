<?php
require_once __DIR__ . '/framework/yii.php';
$config = require __DIR__ . '/protected/config/main.php';
// Mô phỏng PRODUCTION: main.php KHÔNG có rule run/daihoi.
$config['components']['urlManager']['rules'] = array();
Yii::createWebApplication($config);
$um = Yii::app()->getUrlManager();
class FakeReq extends CHttpRequest {
    public $pi = 'run';
    public function getPathInfo() { return $this->pi; }
    public function getQueryString() { return ''; }
    public function getRequestUri() { return '/' . $this->pi; }
}
$r = new FakeReq();
foreach (array('run','run/login','run/register','run/cancelRequestTour','admin/runEvents/admin','login') as $t) {
    $r->pi = $t; echo $t . ' ==> ' . $um->parseUrl($r) . "\n";
}
