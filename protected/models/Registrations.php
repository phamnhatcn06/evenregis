<?php

Yii::import('application.models._base.BaseRegistrations');

class Registrations extends BaseRegistrations
{
	const STATUS_DRAFT     = 0;
	const STATUS_SUBMITTED = 1;
	const STATUS_APPROVED  = 2;
	const STATUS_REJECTED  = 3;
	const STATUS_RETURNED  = 4;

	// Virtual properties từ API (joined data)
	public $event_name;
	public $property_name;
	public $property_code;
	public $relation_property_name;
	public $period_name;
	public $submitted_by_name;
	public $reviewed_by_name;

	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	public function rules()
	{
		return array_merge(parent::rules(), array(
			array('property_id', 'validateUniqueRegistration'),
		));
	}

	/**
	 * Validate mỗi đơn vị chỉ được đăng ký 1 lần cho mỗi đợt đăng ký của sự kiện
	 */
	public function validateUniqueRegistration($attribute, $params)
	{
		if ($this->hasErrors()) {
			return;
		}

		// Chỉ validate khi tạo mới (không có id)
		if ($this->id) {
			return;
		}

		if (!$this->event_id || !$this->property_id || !$this->period_id) {
			return;
		}

		$existing = self::findExisting($this->event_id, $this->property_id, $this->period_id);
		if ($existing) {
			$this->addError($attribute, 'Đơn vị này đã có phiếu đăng ký cho đợt đăng ký này. Mỗi đợt đăng ký chỉ được đăng ký 1 lần.');
		}
	}

	/**
	 * Kiểm tra xem đã tồn tại đăng ký với event_id, property_id, period_id chưa
	 */
	public static function findExisting($eventId, $propertyId, $periodId)
	{
		$params = array(
			'event_id' => $eventId,
			'property_id' => $propertyId,
			'period_id' => $periodId,
		);
		$dataProvider = self::getApiDataProvider($params, 100);
		$data = $dataProvider->getData();

		// Double-check vì API có thể không filter chính xác
		foreach ($data as $item) {
			$itemPropertyId = is_object($item) ? $item->property_id : (isset($item['property_id']) ? $item['property_id'] : null);
			$itemPeriodId = is_object($item) ? $item->period_id : (isset($item['period_id']) ? $item['period_id'] : null);

			if ($itemPropertyId == $propertyId && $itemPeriodId == $periodId) {
				return $item;
			}
		}
		return null;
	}

	public static function fetchFromApi($id)
	{
		$url = ApiEndpoints::url(ApiEndpoints::REGISTRATION_DETAIL, array('id' => $id));
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
		$data = array_filter($this->attributes, function ($value) {
			return $value !== null && $value !== '';
		});
		return ApiClient::post(ApiEndpoints::REGISTRATION_STORE, $data);
	}

	public function updateViaApi($additionalData = array())
	{
		$data = array_filter($this->attributes, function ($value) {
			return $value !== null && $value !== '';
		});
		$data = array_merge($data, $additionalData);
		$url = ApiEndpoints::url(ApiEndpoints::REGISTRATION_UPDATE, array('id' => $this->id));
		return ApiClient::post($url, $data);
	}

	public static function deleteViaApi($id)
	{
		$url = ApiEndpoints::url(ApiEndpoints::REGISTRATION_DESTROY, array('id' => $id));
		return ApiClient::delete($url);
	}

	public static function getApiDataProvider($params = array(), $pageSize = 10000)
	{
		return new ApiDataProvider(ApiEndpoints::REGISTRATION_LIST, array(
			'modelClass' => 'Registrations',
			'params' => $params,
			'pagination' => array('pageSize' => $pageSize),
		));
	}

	public static function getStatusLabel($status)
	{
		$labels = array(
			self::STATUS_DRAFT     => '<span class="badge bg-secondary">Nháp</span>',
			self::STATUS_SUBMITTED => '<span class="badge bg-info">Đã nộp</span>',
			self::STATUS_APPROVED  => '<span class="badge bg-success">Đã duyệt</span>',
			self::STATUS_REJECTED  => '<span class="badge bg-danger">Từ chối</span>',
			self::STATUS_RETURNED  => '<span class="badge bg-warning text-dark">Trả về</span>',
		);
		return isset($labels[$status]) ? $labels[$status] : $status;
	}

	public static function getStatusList()
	{
		return array(
			self::STATUS_DRAFT     => 'Nháp',
			self::STATUS_SUBMITTED => 'Đã nộp',
			self::STATUS_APPROVED  => 'Đã duyệt',
			self::STATUS_REJECTED  => 'Từ chối',
			self::STATUS_RETURNED  => 'Trả về',
		);
	}

	public function isEditable()
	{
		return in_array($this->status, array(self::STATUS_DRAFT, self::STATUS_RETURNED, self::STATUS_REJECTED));
	}

	public function isSubmittable()
	{
		return in_array($this->status, array(self::STATUS_DRAFT, self::STATUS_RETURNED, self::STATUS_REJECTED));
	}
}
