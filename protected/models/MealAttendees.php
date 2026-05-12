<?php

Yii::import('application.models._base.BaseMealAttendees');

class MealAttendees extends BaseMealAttendees
{
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}
}