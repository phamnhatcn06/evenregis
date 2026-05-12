<?php

Yii::import('application.models._base.BaseUser');

class User extends BaseUser
{
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}
    
    public static function label($n = 1) {
		return Yii::t('app', 'Ers', $n);
	}
}