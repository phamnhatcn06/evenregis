<?php

Yii::import('application.models._base.BaseCompetitions');

class Competitions extends BaseCompetitions
{
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}
}