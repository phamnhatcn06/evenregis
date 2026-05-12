<?php

Yii::import('application.models._base.BaseBanquetEvents');

class BanquetEvents extends BaseBanquetEvents
{
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}
}