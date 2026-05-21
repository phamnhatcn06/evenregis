<?php

Yii::import('application.models._base.BaseDepartments');

class Departments extends BaseDepartments
{
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	public static function findByPropertyAndDivision($propertyCode, $divisionCode)
	{
		if (empty($propertyCode) || empty($divisionCode)) {
			return null;
		}
		return self::model()->find('property_code=:pc AND division_code=:dc AND deleted_at IS NULL', array(
			':pc' => $propertyCode,
			':dc' => $divisionCode,
		));
	}

	public static function getDepartmentName($propertyCode, $divisionCode)
	{
		$dept = self::findByPropertyAndDivision($propertyCode, $divisionCode);
		return $dept ? $dept->name : '';
	}

	/**
	 * Lấy danh sách tất cả phòng ban dạng array(unique_code => name)
	 */
	public static function getActiveList()
	{
		$list = array();
		$models = self::model()->findAll(array(
			'condition' => 'deleted_at IS NULL AND status = 1',
			'order' => 'name ASC',
		));
		foreach ($models as $model) {
			$list[$model->unique_code] = $model->name;
		}
		return $list;
	}
}