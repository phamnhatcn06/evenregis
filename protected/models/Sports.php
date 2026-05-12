<?php

Yii::import('application.models._base.BaseSports');

class Sports extends BaseSports
{
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}
}