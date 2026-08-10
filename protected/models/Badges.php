<?php

Yii::import('application.models._base.BaseBadges');
class Badges extends BaseBadges
{
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * Lấy các thẻ (badge) đang hiệu lực của 1 người tham dự.
	 *
	 * @param int $attendeeId
	 * @return array Danh sách bản ghi badge (mảng rỗng nếu không có)
	 */
	public static function getByAttendeeId($attendeeId)
	{
		$result = ApiClient::get(ApiEndpoints::BADGE_LIST, array(
			'attendee_id' => $attendeeId,
			'per_page' => 100,
		));
		if (!empty($result['success'])) {
			if (isset($result['data']['data']) && is_array($result['data']['data'])) {
				return $result['data']['data'];
			}
			if (isset($result['data']) && is_array($result['data'])) {
				return $result['data'];
			}
		}
		return array();
	}

	/**
	 * Vô hiệu (soft delete) toàn bộ thẻ của 1 người tham dự — dùng khi người này
	 * bị huỷ tư cách hoặc bị thay thế. Thẻ đã in trở nên không còn giá trị.
	 *
	 * @param int $attendeeId
	 * @return int Số thẻ đã vô hiệu
	 */
	public static function revokeByAttendee($attendeeId)
	{
		$revoked = 0;
		foreach (self::getByAttendeeId($attendeeId) as $badge) {
			$badgeId = is_array($badge)
				? (isset($badge['id']) ? $badge['id'] : null)
				: (isset($badge->id) ? $badge->id : null);
			$badgeAttId = is_array($badge)
				? (isset($badge['attendee_id']) ? $badge['attendee_id'] : null)
				: (isset($badge->attendee_id) ? $badge->attendee_id : null);
			// Chỉ vô hiệu đúng thẻ của người này (phòng khi API không lọc chuẩn)
			if ($badgeId && (string)$badgeAttId === (string)$attendeeId) {
				$url = ApiEndpoints::url(ApiEndpoints::BADGE_DESTROY, array('id' => $badgeId));
				$result = ApiClient::delete($url);
				if (!empty($result['success'])) {
					$revoked++;
				}
			}
		}
		return $revoked;
	}
}