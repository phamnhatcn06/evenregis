<?php

Yii::import('ext.yii-mail.YiiMail');
Yii::import('ext.yii-mail.YiiMailMessage');

class MailComponent extends YiiMail
{
    public function init()
    {
        $mailConfig = Yii::app()->params['mail'];

        $this->transportType = 'smtp';
        $this->transportOptions = array(
            'host' => $mailConfig['host'],
            'username' => $mailConfig['username'],
            'password' => $mailConfig['password'],
            'port' => $mailConfig['port'],
            'encryption' => $mailConfig['encryption'],
        );

        parent::init();
    }
}
