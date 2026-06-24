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

        // Set default stream context to bypass SSL peer verification for PHP socket stream/crypto
        stream_context_set_default(array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            )
        ));

        parent::init();
    }
}
