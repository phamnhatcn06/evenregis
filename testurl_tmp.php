<?php
require_once __DIR__ . '/framework/yii.php';
require_once __DIR__ . '/protected/components/RewriteUrlManager.php';
class FakeReq extends CHttpRequest {
    public $pi = 'run';
    public function getPathInfo() { return $this->pi; }
    public function getQueryString() { return ''; }
    public function getRequestUri() { return '/' . $this->pi; }
}
$um = new RewriteUrlManager();
$um->urlFormat = 'path';
$um->showScriptName = false;
$um->rules = array();
$um->init();
$tests = array('run', 'run/login', 'run/register', 'admin/runEvents/admin', 'login');
$r = new FakeReq();
foreach ($tests as $t) { $r->pi = $t; echo $t . ' ==> ' . $um->parseUrl($r) . "\n"; }
