<?php

Yii::import('application.models._base.BaseAllianceRequests');

class AllianceRequests extends BaseAllianceRequests
{
	const STATUS_PENDING = 0;
	const STATUS_APPROVED = 1;
	const STATUS_REJECTED = 2;

	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	public static function label($n = 1)
	{
		return Yii::t('app', 'Yêu cầu liên quân|Yêu cầu liên quân', $n);
	}

	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'event_id' => 'Sự kiện',
			'requester_org_id' => 'Đơn vị yêu cầu',
			'target_org_id' => 'Đơn vị liên quân',
			'status' => 'Trạng thái',
			'requested_by' => 'Người yêu cầu',
			'requested_at' => 'Thời gian yêu cầu',
			'reviewed_by' => 'Người duyệt',
			'reviewed_at' => 'Thời gian duyệt',
			'rejection_reason' => 'Lý do từ chối',
			'note' => 'Ghi chú',
		);
	}

	public static function fetchFromApi($id)
	{
		$url = ApiEndpoints::url(ApiEndpoints::ALLIANCE_REQUEST_DETAIL, array('id' => $id));
		$result = ApiClient::get($url);
		if ($result['success'] && isset($result['data'])) {
			$data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
			$model = new self;
			$model->setAttributes($data, false);
			$model->id = $id;
			return $model;
		}
		return null;
	}

	public function storeViaApi()
	{
		$data = array(
			'event_id' => $this->event_id,
			'requester_org_id' => $this->requester_org_id,
			'target_org_id' => $this->target_org_id,
			'status' => self::STATUS_PENDING,
			'requested_by' => $this->requested_by,
			'requested_at' => date('Y-m-d H:i:s'),
			'note' => $this->note,
		);
		return ApiClient::post(ApiEndpoints::ALLIANCE_REQUEST_STORE, $data);
	}

	public function updateViaApi()
	{
		$data = array_filter($this->attributes, function ($value) {
			return $value !== null && $value !== '';
		});
		$url = ApiEndpoints::url(ApiEndpoints::ALLIANCE_REQUEST_UPDATE, array('id' => $this->id));
		return ApiClient::post($url, $data);
	}

	public static function deleteViaApi($id)
	{
		$url = ApiEndpoints::url(ApiEndpoints::ALLIANCE_REQUEST_DESTROY, array('id' => $id));
		return ApiClient::delete($url);
	}

	public static function getApiDataProvider($params = array(), $pageSize = 25)
	{
		return new ApiDataProvider(ApiEndpoints::ALLIANCE_REQUEST_LIST, array(
			'modelClass' => 'AllianceRequests',
			'params' => $params,
			'pagination' => array('pageSize' => $pageSize),
		));
	}

	public static function findByRegistration($eventId, $requesterOrgId, $targetOrgId)
	{
		$params = array(
			'event_id' => $eventId,
			'requester_org_id' => $requesterOrgId,
			'target_org_id' => $targetOrgId,
		);
		$items = self::getApiDataProvider($params, 1)->getData();
		return !empty($items) ? $items[0] : null;
	}

	public static function getStatusLabel($status)
	{
		$labels = array(
			self::STATUS_PENDING => '<span class="badge bg-warning">Chờ xác nhận</span>',
			self::STATUS_APPROVED => '<span class="badge bg-success">Đã xác nhận</span>',
			self::STATUS_REJECTED => '<span class="badge bg-danger">Từ chối</span>',
		);
		return isset($labels[$status]) ? $labels[$status] : '<span class="badge bg-secondary">Không xác định</span>';
	}
}