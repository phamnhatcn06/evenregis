<?php

Yii::import('application.models._base.BaseAlliances');

class Alliances extends BaseAlliances
{
	// Virtual property từ API
	public $event_content_id;

	public static function model($className=__CLASS__) {
		return parent::model($className);
	}
}