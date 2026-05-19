<?php

Yii::import('application.models._base.BaseEvents');

class Events extends BaseEvents
{
    public $max_sports_per_attendee;

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = array('max_sports_per_attendee', 'numerical', 'integerOnly' => true, 'min' => 1);
        $rules[] = array('max_sports_per_attendee', 'default', 'setOnEmpty' => true, 'value' => 3);
        return $rules;
    }

    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        $labels['max_sports_per_attendee'] = Yii::t('app', 'Số môn thể thao tối đa/người');
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

	public function storeViaApi()
	{
		$data = array_filter($this->attributes, function ($value) {
			return $value !== null && $value !== '';
		});
		return ApiClient::post(ApiEndpoints::EVENT_STORE, $data);
	}

	public function updateViaApi()
	{
		$url = ApiEndpoints::url(ApiEndpoints::EVENT_UPDATE, array('id' => $this->id));
		return ApiClient::post($url, $this->attributes);
	}

	public static function deleteViaApi($id)
	{
		$url = ApiEndpoints::url(ApiEndpoints::EVENT_DESTROY, array('id' => $id));
		return ApiClient::delete($url);
	}

	public static function getApiDataProvider($params = array(), $pageSize = 25)
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
		$list = array();
		$items = self::getApiDataProvider(array('is_active' => 1), 100)->getData();
		foreach ($items as $item) {
			$list[$item->id] = $item->name;
		}
		return $list;
	}
}
