<?php

Yii::import('application.models._base.BaseLogs');

class Logs extends BaseLogs
{
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}
}