<?php

Yii::import('application.models._base.BaseRegistrationDetails');

class RegistrationDetails extends BaseRegistrationDetails
{
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	public static function getByRegistrationId($registrationId)
	{
		$result = ApiClient::get(ApiEndpoints::REGISTRATION_DETAIL_LIST, array('registration_id' => $registrationId));
		if ($result['success'] && isset($result['data']['data'])) {
			return $result['data']['data'];
		}
		return array();
	}

	public static function storeViaApi($data)
	{
		return ApiClient::post(ApiEndpoints::REGISTRATION_DETAIL_STORE, $data);
	}

	public static function deleteViaApi($id)
	{
		$url = ApiEndpoints::url(ApiEndpoints::REGISTRATION_DETAIL_DESTROY, array('id' => $id));
		return ApiClient::delete($url);
	}
}
