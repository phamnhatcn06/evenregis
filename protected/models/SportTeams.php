<?php

Yii::import('application.models._base.BaseSportTeams');

class SportTeams extends BaseSportTeams
{
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}
}