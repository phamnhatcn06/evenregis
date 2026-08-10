<?php

Yii::import('application.models._base.BaseAttendeeRoles');

class AttendeeRoles extends BaseAttendeeRoles
{
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * Danh sách vai trò (raw) của 1 người tham dự.
	 * @param int $attendeeId
	 * @return array
	 */
	public static function getByAttendeeId($attendeeId)
	{
		$result = ApiClient::get(ApiEndpoints::ATTENDEE_ROLE_LIST, array(
			'attendee_id' => $attendeeId,
			'per_page' => 500,
		));
		$items = ($result['success'] && isset($result['data']['data'])) ? $result['data']['data'] : array();
		if (!is_array($items)) {
			return array();
		}
		$roles = array();
		foreach ($items as $item) {
			if (isset($item['attendee_id']) && $item['attendee_id'] == $attendeeId) {
				$roles[] = $item;
			}
		}
		return $roles;
	}

	public function storeViaApi()
	{
		$data = array_filter($this->attributes, function ($value) {
			return $value !== null && $value !== '';
		});
		return ApiClient::post(ApiEndpoints::ATTENDEE_ROLE_STORE, $data);
	}

	public static function deleteViaApi($id)
	{
		$url = ApiEndpoints::url(ApiEndpoints::ATTENDEE_ROLE_DESTROY, array('id' => $id));
		return ApiClient::delete($url);
	}
}
