<?php

// This is the database connection configuration.
return array(
    //'connectionString' => 'sqlite:' . dirname(__FILE__) . '/../data/testdrive.db',
    // uncomment the following lines to use a MySQL database
    'connectionString' => 'mysql:host=localhost;dbname=yii_event',
    'emulatePrepare' => true,
    'username' => 'root',
    'password' => '123456a@',
    //'schemaCachingDuration'=>3600, // number of seconds
    //'password' => 'emga123',
    'charset' => 'utf8',
    'enableParamLogging' => true,
    'enableProfiling' => true,
);
