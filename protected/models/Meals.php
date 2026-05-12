<?php

Yii::import('application.models._base.BaseMeals');

class Meals extends BaseMeals
{
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}
}