<?php

Yii::import('application.models._base.BaseSportMatchResults');

class SportMatchResults extends BaseSportMatchResults
{
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * Ghi kết quả 1 trận (tạo mới hoặc cập nhật theo match_id).
	 * Backend tự đẩy đội thắng sang vòng kế tiếp.
	 *
	 * @param array $data [match_id, score_a, score_b, winner_team_id?, is_draw?, note?]
	 * @return array kết quả ApiClient
	 */
	public static function recordViaApi($data)
	{
		$ssoUser = AuthHandler::getUser();
		$data['auth_email'] = isset($ssoUser['email']) ? $ssoUser['email'] : null;
		return ApiClient::post(ApiEndpoints::SPORT_MATCH_RESULT_RECORD, $data);
	}
}