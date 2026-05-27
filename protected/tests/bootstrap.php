<?php

/**
 * PHPUnit Bootstrap file cho Yii 1.x
 *
 * File nay duoc load truoc khi chay tat ca tests
 * Khoi tao Yii framework va cac dependencies
 */

// Dinh nghia duong dan
$yiiPath = dirname(__FILE__) . '/../../../yii/framework/yii.php';
$configPath = dirname(__FILE__) . '/../config/test.php';

// Kiem tra Yii framework
if (!file_exists($yiiPath)) {
    // Thu duong dan khac
    $yiiPath = dirname(__FILE__) . '/../../vendor/yiisoft/yii/framework/yii.php';
}

if (!file_exists($yiiPath)) {
    die("Khong tim thay Yii framework. Hay kiem tra duong dan: $yiiPath\n");
}

// Load Yii framework
require_once($yiiPath);

// Load config
if (file_exists($configPath)) {
    $config = require($configPath);
} else {
    // Fallback config neu khong co file test.php
    $config = array(
        'basePath' => dirname(__FILE__) . '/..',
        'name' => 'Event Registration Tests',
        'components' => array(
            'session' => array(
                'class' => 'CHttpSession',
            ),
        ),
    );
}

// Tao Yii application
Yii::createWebApplication($config);

// Import cac components can thiet
Yii::import('application.components.*');
Yii::import('application.models.*');
