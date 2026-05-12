<?php

Yii::import('application.models._base.BaseAttendeeRoles');

class AttendeeRoles extends BaseAttendeeRoles
{
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}
}