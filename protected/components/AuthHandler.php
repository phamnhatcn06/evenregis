<?php

/**
 * AuthHandler - Handle SSO authentication from Portal
 *
 * JWT Payload format from Portal:
 * {
 *   "sub": "12345",
 *   "username": "nguyenvana",
 *   "full_name": "Nguyen Van A",
 *   "email": "nguyenvana@muongthanh.vn",
 *   "unit_code": "HN01",
 *   "permissions": {
 *     "event": "1 1 1 1",
 *     "registration": "1 1 1 0",
 *     ...
 *   },
 *   "iat": 1714838400,
 *   "exp": 1714842000
 * }
 */
class AuthHandler extends CApplicationComponent
{
    const SESSION_USER_KEY = 'sso_user';
    const SESSION_PERMISSIONS_KEY = 'sso_permissions';
    const SESSION_TOKEN_KEY = 'sso_token';
    const SESSION_LAST_ACTIVITY_KEY = 'sso_last_activity';

    /**
     * Handle SSO callback with JWT token
     * @param string $token JWT token from Portal
     * @return array|false User data or false on failure
     */
    public static function handleCallback($token)
    {
        if (empty($token)) {
            return false;
        }

        $params = self::getParams();
        $secret = $params['portal']['jwt_secret'];
        $algorithm = $params['portal']['jwt_algorithm'];

        require_once Yii::getPathOfAlias('application.extensions.jwt') . '/JWT.php';

        $payload = JWT::decode($token, $secret, $algorithm);
        if (!$payload) {
            Yii::log('JWT decode failed', CLogger::LEVEL_WARNING, 'auth');
            return false;
        }

        // Create session
        $userData = self::createSession($payload, $token);
        return $userData;
    }

    /**
     * Create user session from JWT payload
     */
    private static function createSession($payload, $token)
    {
        $session = Yii::app()->session;

        // Convert to array for easier access with special claim keys
        $data = (array)$payload;

        // SAML-style claim keys from Portal
        $claimSid = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/sid';
        $claimEmail = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress';
        $claimName = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name';

        $userData = array(
            'id' => isset($data[$claimSid]) ? $data[$claimSid] : null,
            'email' => isset($data[$claimEmail]) ? $data[$claimEmail] : null,
            'full_name' => isset($data[$claimName]) ? $data[$claimName] : null,
            'software_id' => isset($data['software_id']) ? $data['software_id'] : null,
            'exp' => isset($data['exp']) ? $data['exp'] : null,
            'property_id' => isset($data['property_id']) ? $data['property_id'] : null,
            'property_code' => isset($data['property_code']) ? $data['property_code'] : null,
            'regional_id' => isset($data['regional_id']) ? $data['regional_id'] : null,
        );

        // Decode permissions (encrypted/encoded string from Portal)
        $permissionsData = isset($data['perm']) ? self::decodePermissions($data['perm']) : array();

        // Extract CRUD permissions (key => "C R U D") and menu permissions (array format)
        $crudPermissions = array();
        $menuPermissions = array();

        // Check if permission is "*" (full access)
        if ($permissionsData === '*' || (is_array($permissionsData) && count($permissionsData) === 1 && reset($permissionsData) === '*')) {
            $crudPermissions = array('*' => '1 1 1 1');
        } elseif (is_array($permissionsData)) {
            foreach ($permissionsData as $item) {
                if (is_array($item) && isset($item['controller'])) {
                    // New format: {name, module, controller, action, root}
                    $menuPermissions[] = $item;
                    // Also create CRUD entry if action is defined
                    if (isset($item['action'])) {
                        $crudPermissions[$item['controller']] = $item['action'];
                    }
                } else {
                    // Old format: key => "C R U D"
                    // Keep as is
                }
            }

            // If no menu permissions extracted, assume old format
            if (empty($menuPermissions) && !empty($permissionsData)) {
                $crudPermissions = $permissionsData;
            }
        }

        // Auto-inherit permissions for related controllers
        $crudPermissions = self::inheritRelatedPermissions($crudPermissions);

        $session[self::SESSION_USER_KEY] = $userData;
        $session[self::SESSION_PERMISSIONS_KEY] = $crudPermissions;
        $session['sso_menu_permissions'] = $menuPermissions;
        $session[self::SESSION_TOKEN_KEY] = $token;
        $session[self::SESSION_LAST_ACTIVITY_KEY] = time();

        Yii::log('SSO login successful for user: ' . $userData['email'], CLogger::LEVEL_INFO, 'auth');

        return $userData;
    }

    /**
     * Update session with SSO profile data
     * @param array $profile Profile data from SSO /me API
     */
    public static function updateSessionWithProfile($profile)
    {
        if (!$profile || !is_array($profile)) {
            return;
        }

        $session = Yii::app()->session;
        $userData = isset($session[self::SESSION_USER_KEY]) ? $session[self::SESSION_USER_KEY] : array();

        // Merge profile fields into session
        $fieldsToMerge = array(
            'property_id', 'property_code', 'regional_id',
            'hotel_code', 'hotel_id', 'hotel_name',
            'department_id', 'department_name', 'position_name',
        );

        foreach ($fieldsToMerge as $field) {
            if (isset($profile[$field]) && !empty($profile[$field])) {
                $userData[$field] = $profile[$field];
            }
        }

        // Also check for HotelCode (might be different key)
        if (isset($profile['HotelCode'])) {
            $userData['property_code'] = $profile['HotelCode'];
        }
        if (isset($profile['HotelId'])) {
            $userData['property_id'] = $profile['HotelId'];
        }

        $session[self::SESSION_USER_KEY] = $userData;
        Yii::log('Session updated with SSO profile: ' . json_encode(array_keys($profile)), CLogger::LEVEL_INFO, 'auth');
    }

    /**
     * Check if user is authenticated via SSO
     * @return bool
     */
    public static function isAuthenticated()
    {
        $session = Yii::app()->session;
        $userData = isset($session[self::SESSION_USER_KEY]) ? $session[self::SESSION_USER_KEY] : null;

        if (!$userData) {
            return false;
        }

        // Check session timeout
        $params = self::getParams();
        $timeout = $params['session']['timeout'];
        $lastActivity = isset($session[self::SESSION_LAST_ACTIVITY_KEY]) ? $session[self::SESSION_LAST_ACTIVITY_KEY] : 0;

        if (time() - $lastActivity > $timeout) {
            self::logout();
            return false;
        }

        // Check token expiration
        if (isset($userData['exp']) && $userData['exp'] < time()) {
            self::logout();
            return false;
        }

        // Update last activity
        $session[self::SESSION_LAST_ACTIVITY_KEY] = time();

        return true;
    }

    /**
     * Get current authenticated user data
     * @return array|null
     */
    public static function getUser()
    {
        if (!self::isAuthenticated()) {
            return null;
        }
        return Yii::app()->session[self::SESSION_USER_KEY];
    }

    /**
     * Get user permissions
     * @return array
     */
    public static function getPermissions()
    {
        $session = Yii::app()->session;
        $permissions = isset($session[self::SESSION_PERMISSIONS_KEY]) ? $session[self::SESSION_PERMISSIONS_KEY] : array();
        return self::inheritRelatedPermissions($permissions);
    }

    /**
     * Auto-inherit permissions for related controllers
     * Controllers that are sub-modules of a main controller inherit its permissions
     * @param array $permissions
     * @return array
     */
    private static function inheritRelatedPermissions($permissions)
    {
        // If already has wildcard, no need to inherit
        if (isset($permissions['*'])) {
            return $permissions;
        }

        // Define inheritance rules: child => parent
        $inheritanceRules = array(
            'registrationperiods' => 'events',
            'registrations' => 'events',
            'eventsports' => 'events',
            'eventcontents' => 'events',
            'registrationdetails' => 'registrations',
        );

        foreach ($inheritanceRules as $child => $parent) {
            if (!isset($permissions[$child]) && isset($permissions[$parent])) {
                $permissions[$child] = $permissions[$parent];
            }
        }

        return $permissions;
    }

    /**
     * Logout - destroy SSO session
     */
    public static function logout()
    {
        $session = Yii::app()->session;
        unset($session[self::SESSION_USER_KEY]);
        unset($session[self::SESSION_PERMISSIONS_KEY]);
        unset($session[self::SESSION_TOKEN_KEY]);
        unset($session[self::SESSION_LAST_ACTIVITY_KEY]);
    }

    /**
     * Get params from config
     */
    private static function getParams()
    {
        static $params = null;
        if ($params === null) {
            $params = require Yii::getPathOfAlias('application.config') . '/params.php';
        }
        return $params;
    }

    /**
     * Decode permissions string from Portal (AES encrypted)
     * @param string $permString Base64 encoded AES encrypted string
     * @return array
     */
    private static function decodePermissions($permString)
    {
        $params = self::getParams();
        $secretKey = $params['portal']['jwt_secret'];

        try {
            // Derive key using SHA256 (same as C# DeriveKey)
            $key = hash('sha256', $secretKey, true);

            // Decode base64
            $encrypted = base64_decode($permString);
            if ($encrypted === false || strlen($encrypted) < 16) {
                return array();
            }

            // Extract IV (first 16 bytes) and ciphertext
            $iv = substr($encrypted, 0, 16);
            $ciphertext = substr($encrypted, 16);

            // Decrypt using AES-256-CBC
            $decrypted = openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
            if ($decrypted === false) {
                Yii::log('Permission decryption failed', CLogger::LEVEL_WARNING, 'auth');
                return array();
            }

            // Parse JSON
            $permissions = json_decode($decrypted, true);
            if (!is_array($permissions)) {
                return array();
            }

            return $permissions;
        } catch (Exception $e) {
            Yii::log('Permission decode error: ' . $e->getMessage(), CLogger::LEVEL_ERROR, 'auth');
            return array();
        }
    }

    /**
     * Debug: decrypt and show permissions
     * @param string $permString
     * @return array
     */
    public static function debugPermissions($permString)
    {
        return self::decodePermissions($permString);
    }

    /**
     * Debug: decode token without validation to see raw payload
     * @param string $token
     * @return array
     */
    public static function debugToken($token)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return array('error' => 'Invalid token format');
        }

        $header = json_decode(base64_decode(strtr($parts[0], '-_', '+/')), true);
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

        return array(
            'header' => $header,
            'payload' => $payload,
        );
    }

    /**
     * Redirect to Portal login page
     */
    public static function redirectToPortal()
    {
        $params = self::getParams();
        $returnUrl = Yii::app()->request->hostInfo . Yii::app()->request->baseUrl;
        $portalUrl = $params['portal']['url'] . '/login?redirect=' . urlencode($returnUrl);
        Yii::app()->request->redirect($portalUrl);
    }

    /**
     * Fetch full user profile from SSO API
     * @param string $token JWT token
     * @return array|null User profile data or null on failure
     */
    public static function fetchUserProfile($token)
    {
        $params = self::getParams();
        $url = rtrim($params['portal']['api_url'], '/') . $params['portal']['sso_me_endpoint'];

        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $token,
                'Accept: application/json',
            ),
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // Debug log
        Yii::log('SSO /me API response: HTTP ' . $httpCode . ' | Error: ' . $error . ' | Body: ' . substr($response, 0, 500), CLogger::LEVEL_INFO, 'auth');

        if ($error || $httpCode !== 200) {
            Yii::log('SSO /me API failed: ' . ($error ?: 'HTTP ' . $httpCode), CLogger::LEVEL_WARNING, 'auth');
            return null;
        }

        $data = json_decode($response, true);

        // Try different response formats
        if (is_array($data)) {
            if (isset($data['data'])) {
                return $data['data'];
            }
            // Maybe data is at root level
            return $data;
        }

        Yii::log('SSO /me API invalid response: ' . $response, CLogger::LEVEL_WARNING, 'auth');
        return null;
    }

    /**
     * Get user profile for localStorage (call after successful login)
     * @return array|null
     */
    public static function getUserProfileForClient()
    {
        $session = Yii::app()->session;
        $token = isset($session[self::SESSION_TOKEN_KEY]) ? $session[self::SESSION_TOKEN_KEY] : null;

        if (!$token) {
            return null;
        }

        return self::fetchUserProfile($token);
    }
}
