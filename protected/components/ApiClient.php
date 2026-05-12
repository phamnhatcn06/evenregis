<?php

/**
 * ApiClient - HTTP client for calling External APIs
 *
 * Usage:
 *   ApiClient::get('/events');
 *   ApiClient::post('/attendees', $data);
 *   ApiClient::put('/attendees/123', $data);
 *   ApiClient::delete('/attendees/123');
 */
class ApiClient extends CComponent
{
    private static $baseUrl;
    private static $apiKey;
    private static $timeout = 30;

    /**
     * Initialize API configuration from params
     */
    private static function init()
    {
        if (self::$baseUrl === null) {
            $params = Yii::app()->params;
            self::$baseUrl = isset($params['externalApiUrl']) ? $params['externalApiUrl'] : '';
            self::$apiKey = isset($params['externalApiKey']) ? $params['externalApiKey'] : '';
        }
    }

    /**
     * GET request
     * @param string $endpoint
     * @param array $params Query parameters
     * @return array
     */
    public static function get($endpoint, $params = array())
    {
        return self::request('GET', $endpoint, $params);
    }

    /**
     * POST request
     * @param string $endpoint
     * @param array $data Request body
     * @return array
     */
    public static function post($endpoint, $data = array())
    {
        return self::request('POST', $endpoint, array(), $data);
    }

    /**
     * PUT request
     * @param string $endpoint
     * @param array $data Request body
     * @return array
     */
    public static function put($endpoint, $data = array())
    {
        return self::request('PUT', $endpoint, array(), $data);
    }

    /**
     * PATCH request
     * @param string $endpoint
     * @param array $data Request body
     * @return array
     */
    public static function patch($endpoint, $data = array())
    {
        return self::request('PATCH', $endpoint, array(), $data);
    }

    /**
     * DELETE request
     * @param string $endpoint
     * @return array
     */
    public static function delete($endpoint)
    {
        return self::request('DELETE', $endpoint);
    }

    /**
     * Execute HTTP request
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $params Query parameters
     * @param array|null $data Request body
     * @return array ['success' => bool, 'code' => int, 'data' => mixed, 'error' => string|null]
     * @throws CException
     */
    private static function request($method, $endpoint, $params = array(), $data = null)
    {
        self::init();

        $url = rtrim(self::$baseUrl, '/') . '/' . ltrim($endpoint, '/');
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init($url);

        $headers = array(
            'Content-Type: application/json',
            'Accept: application/json',
        );

        if (!empty(self::$apiKey)) {
            $headers[] = 'Authorization: Bearer ' . self::$apiKey;
        }

        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => self::$timeout,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true,
        );

        switch ($method) {
            case 'POST':
                $options[CURLOPT_POST] = true;
                if ($data !== null) {
                    $options[CURLOPT_POSTFIELDS] = json_encode($data);
                }
                break;
            case 'PUT':
            case 'PATCH':
            case 'DELETE':
                $options[CURLOPT_CUSTOMREQUEST] = $method;
                if ($data !== null) {
                    $options[CURLOPT_POSTFIELDS] = json_encode($data);
                }
                break;
        }

        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        curl_close($ch);

        // Log request
        Yii::log(
            sprintf('API %s %s -> %d', $method, $endpoint, $httpCode),
            CLogger::LEVEL_INFO,
            'api'
        );

        if ($errno) {
            Yii::log('API Error: ' . $error, CLogger::LEVEL_ERROR, 'api');
            return array(
                'success' => false,
                'code' => 0,
                'data' => null,
                'error' => $error,
            );
        }

        $decoded = json_decode($response, true);

        return array(
            'success' => $httpCode >= 200 && $httpCode < 300,
            'code' => $httpCode,
            'data' => $decoded,
            'error' => $httpCode >= 400 ? self::extractError($decoded, $httpCode) : null,
        );
    }

    /**
     * Extract error message from response
     */
    private static function extractError($data, $httpCode)
    {
        if (is_array($data)) {
            if (isset($data['error']['message'])) {
                return $data['error']['message'];
            }
            if (isset($data['message'])) {
                return $data['message'];
            }
        }
        return 'HTTP Error ' . $httpCode;
    }

    /**
     * Set custom timeout
     * @param int $seconds
     */
    public static function setTimeout($seconds)
    {
        self::$timeout = $seconds;
    }

    /**
     * Override base URL (useful for testing)
     * @param string $url
     */
    public static function setBaseUrl($url)
    {
        self::$baseUrl = $url;
    }

    /**
     * Override API key
     * @param string $key
     */
    public static function setApiKey($key)
    {
        self::$apiKey = $key;
    }
}
