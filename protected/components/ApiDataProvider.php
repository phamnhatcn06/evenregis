<?php

/**
 * ApiDataProvider - Data provider for API-based data
 * Works with EDataTables and other Yii widgets
 */
class ApiDataProvider extends CDataProvider
{
    public $endpoint;
    public $params = array();
    public $modelClass;
    public $keyField = 'id';

    private $_data;
    private $_totalItemCount;

    public function __construct($endpoint, $config = array())
    {
        $this->endpoint = $endpoint;
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }
    }

    protected function fetchData()
    {
        if ($this->_data !== null) {
            return $this->_data;
        }

        $pagination = $this->getPagination();
        $sort = $this->getSort();

        $params = $this->params;

        if ($pagination !== false) {
            $params['page'] = $pagination->getCurrentPage() + 1;
            $params['per_page'] = $pagination->getPageSize();
        }

        if ($sort !== false) {
            $order = $sort->getOrderBy();
            if (!empty($order) && is_array($order)) {
                foreach ($order as $field => $direction) {
                    $params['sort_by'] = $field;
                    $params['sort_order'] = $direction === CSort::SORT_DESC ? 'desc' : 'asc';
                    break;
                }
            }
        }

        $result = ApiClient::get($this->endpoint, $params);

        if ($result['success'] && isset($result['data'])) {
            $responseData = $result['data'];

            if (isset($responseData['data'])) {
                $this->_data = $this->createModels($responseData['data']);
                $this->_totalItemCount = isset($responseData['pagination']['total'])
                    ? $responseData['pagination']['total']
                    : count($responseData['data']);
            } else {
                $this->_data = $this->createModels($responseData);
                $this->_totalItemCount = count($responseData);
            }

            if ($pagination !== false) {
                $pagination->setItemCount($this->_totalItemCount);
            }
        } else {
            $this->_data = array();
            $this->_totalItemCount = 0;
            Yii::log('API Error: ' . $result['error'], CLogger::LEVEL_ERROR, 'api');
        }

        return $this->_data;
    }

    protected function createModels($items)
    {
        $models = array();
        foreach ($items as $item) {
            if ($this->modelClass) {
                $model = new $this->modelClass;
                $model->setAttributes($item, false);
                // Set thêm các property không phải DB column
                foreach ($item as $key => $value) {
                    if (property_exists($model, $key)) {
                        $model->$key = $value;
                    }
                }
                $models[] = $model;
            } else {
                $models[] = (object) $item;
            }
        }
        return $models;
    }

    protected function fetchKeys()
    {
        $keys = array();
        foreach ($this->getData() as $item) {
            if (is_object($item)) {
                $keys[] = isset($item->{$this->keyField}) ? $item->{$this->keyField} : null;
            } else {
                $keys[] = isset($item[$this->keyField]) ? $item[$this->keyField] : null;
            }
        }
        return $keys;
    }

    protected function calculateTotalItemCount()
    {
        if ($this->_totalItemCount === null) {
            $this->fetchData();
        }
        return $this->_totalItemCount;
    }
}
