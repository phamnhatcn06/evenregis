<?php

Yii::import('application.models._base.BaseAuditLogs');

class AuditLogs extends BaseAuditLogs
{
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}
}