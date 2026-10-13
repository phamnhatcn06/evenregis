<?php

/**
 * Custom URL Manager
 */
class RewriteUrlManager extends CUrlManager
{
    protected function processRules()
    {
        $defaultRules = array(
            // Cổng đăng ký hoạt động (Fun Run + Tham quan) — phải đứng TRƯỚC catch-all
            // '<controller>/<action>' bên dưới, nếu không 'run/<action>' sẽ bị nuốt.
            // Đặt ở đây (thay vì main.php) để deploy được qua file này khi main.php không track.
            'portal' => 'frontend/portal/login',
            'portal/<action:\w+>' => 'frontend/portal/<action>',
            'run' => 'frontend/run/index',
            'run/<action:\w+>' => 'frontend/run/<action>',

            // Admin module routes
            'admin/<controller:\w+>/<action:\w+>/<id:\d+>/<contentId:\d+>' => 'admin/<controller>/<action>',
            'admin/<controller:\w+>/<action:\w+>/<id:\d+>' => 'admin/<controller>/<action>',
            'admin/<controller:\w+>/<action:\w+>' => 'admin/<controller>/<action>',
            'admin/<controller:\w+>/<id:\d+>' => 'admin/<controller>/view',
            'admin/<controller:\w+>' => 'admin/<controller>/index',
            // Default controller routes
            '<controller:\w+>/<id:\d+>' => '<controller>/view',
            '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
            '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            'login' => 'site/login',
        );

        // Nạp rule khai báo trong protected/config/RewriteUrlManager.php (biến $urlRewrite).
        // Đây là file config CHÍNH cho url rule (thư mục config BÊN TRONG protected),
        // không phải config/ ở gốc dự án.
        $urlRewrite = array();
        $configFile = dirname(__FILE__) . '/../config/RewriteUrlManager.php';
        if (is_file($configFile)) {
            require $configFile;
        }
        if (!is_array($urlRewrite)) {
            $urlRewrite = array();
        }

        // Thứ tự: rule từ main.php -> rule khai báo trong config -> catch-all generic.
        // Các rule cụ thể phải đứng TRƯỚC catch-all generic ('<controller>/<action>'),
        // nếu không mọi route nhiều đoạn (vd 'run/<action>') hoặc slug có dấu "-"
        // (vd 'lich-thi-the-thao') sẽ bị nuốt / không giải quyết được.
        $this->rules = array_merge(
            is_array($this->rules) ? $this->rules : array(),
            $urlRewrite,
            $defaultRules
        );
        parent::processRules();
    }
}
