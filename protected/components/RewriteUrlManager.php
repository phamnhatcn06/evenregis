<?php

/**
 * Custom URL Manager
 */
class RewriteUrlManager extends CUrlManager
{
    protected function processRules()
    {
        $defaultRules = array(
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

        // Rule khai báo trong config (main.php) phải được ưu tiên TRƯỚC các catch-all
        // generic (vd '<controller>/<action>'), nếu không mọi route nhiều đoạn như
        // 'run/<action>' sẽ bị catch-all nuốt và không giải quyết được.
        $this->rules = array_merge(is_array($this->rules) ? $this->rules : array(), $defaultRules);
        parent::processRules();
    }
}
