<?php
return array(
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'name' => 'My Console Application',
    // preloading 'log' component
    'preload' => array('log'),
    'import' => array(
        'application.models.*',
        'application.components.*',
        'ext.giix-components.*',
        'application.models.mo.*',
    ),
    // application components
    'components' => array(
        'db' => require(dirname(__FILE__) . '/database.php'),
        // usefull for generating links in email etc...
        'urlManager' => array(
            'urlFormat' => 'path',
            'showScriptName' => FALSE,
            'rules' => array(),
        ),
    ),
);
