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
    private $_fetchedKey;

    public function __construct($endpoint, $config = array())
    {
        $this->endpoint = $endpoint;
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }
    }

    public function getPagination($className = 'CPagination')
    {
        $pagination = parent::getPagination($className);
        if ($pagination instanceof CPagination && $this->_totalItemCount === null) {
            $pagination->validateCurrentPage = false;
        }
        return $pagination;
    }

    public function getData($refresh = false)
    {
        if ($refresh) {
            $this->_data = null;
            $this->_totalItemCount = null;
            $this->_fetchedKey = null;
        }
        return parent::getData($refresh);
    }

    protected function fetchData()
    {
        $pagination = $this->getPagination();
        $sort = $this->getSort();

        $params = $this->params;

        if ($pagination !== false) {
            $pagination->validateCurrentPage = false;
            $params['page'] = $pagination->getCurrentPage(true) + 1;
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

        $cacheKey = md5(serialize(array($this->endpoint, $params)));
        if ($this->_fetchedKey === $cacheKey && $this->_data !== null) {
            return $this->_data;
        }

        $result = ApiClient::get($this->endpoint, $params);

        if ($result['success'] && isset($result['data'])) {
            $responseData = $result['data'];

            if (isset($responseData['data'])) {
                $this->_data = $this->createModels($responseData['data']);
                $this->_totalItemCount = isset($responseData['pagination']['total'])
                    ? (int) $responseData['pagination']['total']
                    : (isset($responseData['total']) ? (int) $responseData['total'] : count($responseData['data']));
            } else {
                $this->_data = $this->createModels($responseData);
                $this->_totalItemCount = count($responseData);
            }

            $this->_fetchedKey = $cacheKey;

            if ($pagination !== false) {
                $pagination->setItemCount($this->_totalItemCount);
                $pagination->validateCurrentPage = true;
            }
        } else {
            $this->_data = array();
            $this->_totalItemCount = 0;
            $this->_fetchedKey = $cacheKey;
            if ($pagination !== false) {
                $pagination->setItemCount(0);
                $pagination->validateCurrentPage = true;
            }
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
                // Set thêm các property (public hoặc magic)
                foreach ($item as $key => $value) {
                    try {
                        $model->$key = $value;
                    } catch (Exception $e) {
                        // Ignore if property not settable
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
