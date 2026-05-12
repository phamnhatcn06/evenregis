<?php

Yii::import('application.models._base.BaseRegistrations');

class Registrations extends BaseRegistrations
{
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}
}