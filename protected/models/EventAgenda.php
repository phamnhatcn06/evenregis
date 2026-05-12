<?php

Yii::import('application.models._base.BaseEventAgenda');

class EventAgenda extends BaseEventAgenda
{
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}
}