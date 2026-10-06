<?php
// [TEMP] test parseUrl — xoá sau khi dùng
require_once __DIR__ . '/framework/yii.php';
Yii::import('application.components.RewriteUrlManager', true);

class FakeReq extends CHttpRequest {
    public $pi = 'run';
    public function getPathInfo() { return $this->pi; }
    public function getQueryString() { return ''; }
    public function getRequestUri() { return '/' . $this->pi; }
}

// Mô phỏng PRODUCTION: main.php KHÔNG có rule run (rules rỗng).
$um = new RewriteUrlManager();
$um->urlFormat = 'path';
$um->showScriptName = false;
$um->rules = array(); // giống production thiếu rule run
$um->init();

$tests = array('run', 'run/login', 'run/register', 'run/cancelRequestTour', 'admin/runEvents/admin', 'admin/tourSessions/create', 'login');
$r = new FakeReq();
foreach ($tests as $t) { $r->pi = $t; echo str_pad($t, 28) . ' => ' . $um->parseUrl($r) . PHP_EOL; }
