<?php

Yii::import('application.models._base.BaseRegistrationDetails');

class RegistrationDetails extends BaseRegistrationDetails
{
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}
}