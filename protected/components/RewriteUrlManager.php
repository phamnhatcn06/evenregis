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
            'login' => 'admin/users/login',
        );

        $this->rules = array_merge($defaultRules, is_array($this->rules) ? $this->rules : array());
        parent::processRules();
    }
}
