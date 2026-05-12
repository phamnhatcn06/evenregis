<?php

Yii::import('application.models._base.BaseAttendees');

class Attendees extends BaseAttendees
{
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}
}