<?php

Yii::import('application.models._base.BaseEvents');

class Events extends BaseEvents
{
    public $max_sports_per_attendee;
    public $slogan;
    public $destination;
    public $duration_days;
    public $duration_nights;
    public $organizer;
    public $hero_description;
    public $cover_image;
    public $mascot_image;
    public $mascot_link;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = array('max_sports_per_attendee, duration_days, duration_nights', 'numerical', 'integerOnly' => true, 'min' => 0);
        $rules[] = array('max_sports_per_attendee', 'default', 'setOnEmpty' => true, 'value' => 3);
        $rules[] = array('duration_days', 'default', 'setOnEmpty' => true, 'value' => 1);
        $rules[] = array('duration_nights', 'default', 'setOnEmpty' => true, 'value' => 0);
        $rules[] = array('slogan, destination, organizer, cover_image, mascot_image, mascot_link', 'length', 'max' => 255);
        $rules[] = array('destination', 'length', 'max' => 100);
        $rules[] = array('slogan, destination, duration_days, duration_nights, organizer, hero_description, cover_image, mascot_image, mascot_link', 'safe');
        return $rules;
    }

    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        $labels['max_sports_per_attendee'] = Yii::t('app', 'Số môn thể thao tối đa/người');
        $labels['slogan'] = Yii::t('app', 'Khẩu hiệu sự kiện');
        $labels['destination'] = Yii::t('app', 'Điểm đến');
        $labels['duration_days'] = Yii::t('app', 'Số ngày');
        $labels['duration_nights'] = Yii::t('app', 'Số đêm');
        $labels['organizer'] = Yii::t('app', 'Đơn vị tổ chức');
        $labels['hero_description'] = Yii::t('app', 'Mô tả ngắn (Hero)');
        $labels['cover_image'] = Yii::t('app', 'Ảnh bìa sự kiện');
        $labels['mascot_image'] = Yii::t('app', 'Ảnh linh vật');
        $labels['mascot_link'] = Yii::t('app', 'Link đặt linh vật');
        return $labels;
    }

	public static function fetchFromApi($id)
	{
		$url = ApiEndpoints::url(ApiEndpoints::EVENT_DETAIL, array('id' => $id));
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

	/**
	 * Các trường ảo (không nằm trong bảng local) cần gửi kèm lên API.
	 */
	protected function getApiPayload()
	{
		$extraFields = array(
			'max_sports_per_attendee', 'slogan', 'destination',
			'duration_days', 'duration_nights', 'organizer',
			'hero_description', 'cover_image', 'mascot_image', 'mascot_link',
		);
		$data = $this->attributes;
		foreach ($extraFields as $field) {
			$data[$field] = $this->$field;
		}
		return $data;
	}

	public function storeViaApi()
	{
		$data = array_filter($this->getApiPayload(), function ($value) {
			return $value !== null && $value !== '';
		});
		return ApiClient::post(ApiEndpoints::EVENT_STORE, $data);
	}

	public function updateViaApi()
	{
		$url = ApiEndpoints::url(ApiEndpoints::EVENT_UPDATE, array('id' => $this->id));
		return ApiClient::post($url, $this->getApiPayload());
	}

	public static function deleteViaApi($id)
	{
		$url = ApiEndpoints::url(ApiEndpoints::EVENT_DESTROY, array('id' => $id));
		return ApiClient::delete($url);
	}

	public static function getApiDataProvider($params = array(), $pageSize = 10000)
	{
		return new ApiDataProvider(ApiEndpoints::EVENT_LIST, array(
			'modelClass' => 'Events',
			'params' => $params,
			'pagination' => array(
				'pageSize' => $pageSize,
			),
		));
	}

	public static function getActiveList()
	{
		return CacheHelper::getDropdown('events_active', function () {
			$list = array();
			$items = self::getApiDataProvider(array('is_active' => 1), 100)->getData();
			foreach ($items as $item) {
				$list[$item->id] = $item->name;
			}
			return $list;
		});
	}

	public static function getListForDropdown()
	{
		return self::getActiveList();
	}

	public static function clearCache()
	{
		CacheHelper::clearDropdownCache('events_active');
	}
}
