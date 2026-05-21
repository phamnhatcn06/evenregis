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
	 * Lấy danh sách phòng ban theo code (distinct)
	 * @return array(code => "code - name")
	 */
	public static function getActiveList()
	{
		$list = array();
		$sql = "SELECT DISTINCT code, name FROM departments
				WHERE deleted_at IS NULL AND status = 1
				ORDER BY code ASC";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		foreach ($rows as $row) {
			$list[$row['code']] = $row['code'] . ' - ' . $row['name'];
		}
		return $list;
	}
}