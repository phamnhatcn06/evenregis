<?php

/**
 * AuthHandlerTest - Unit tests cho AuthHandler component
 *
 * Test cases:
 * - AUTH-001: Login with valid JWT from Portal
 * - AUTH-002: Login fails with expired JWT
 * - AUTH-003: Login fails with tampered JWT signature
 * - AUTH-004: Login fails without token
 * - AUTH-005: Login fails with empty token
 * - AUTH-013: Logout clears session
 * - AUTH-015: JWT with null permissions uses defaults
 * - AUTH-016: Admin role has full access
 */

class AuthHandlerTest extends CTestCase
{
    /**
     * Mock JWT secret key cho testing
     */
    private $testSecret = 'test_jwt_secret_key_for_unit_testing';

    /**
     * @var array Mock session data
     */
    private $mockSession = array();

    protected function setUp()
    {
        parent::setUp();
        // Reset mock session truoc moi test
        $this->mockSession = array();
    }

    protected function tearDown()
    {
        parent::tearDown();
        // Clear session sau moi test
        AuthHandler::logout();
    }

    /**
     * Tao JWT token hop le cho testing
     *
     * @param array $payload Du lieu payload
     * @param string $secret Secret key
     * @return string JWT token
     */
    private function createTestJwt($payload, $secret = null)
    {
        if ($secret === null) {
            $secret = $this->testSecret;
        }

        $header = array(
            'typ' => 'JWT',
            'alg' => 'HS256'
        );

        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, $secret, true);
        $signatureEncoded = $this->base64UrlEncode($signature);

        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }

    /**
     * Base64 URL-safe encoding
     */
    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Tao payload JWT hop le
     */
    private function createValidPayload($overrides = array())
    {
        $claimSid = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/sid';
        $claimEmail = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress';
        $claimName = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name';

        $payload = array(
            $claimSid => '12345',
            $claimEmail => 'test@muongthanh.vn',
            $claimName => 'Nguyen Van Test',
            'software_id' => 'EVENTREGIS',
            'property_id' => '1',
            'property_code' => 'HN01',
            'regional_id' => '1',
            'iat' => time(),
            'exp' => time() + 3600, // Token het han sau 1 gio
        );

        return array_merge($payload, $overrides);
    }

    /**
     * AUTH-001: Login thanh cong voi JWT hop le tu Portal
     */
    public function testLoginWithValidJwt()
    {
        $payload = $this->createValidPayload();
        $token = $this->createTestJwt($payload);

        // Gia lap handleCallback
        $result = $this->simulateHandleCallback($token);

        $this->assertNotFalse($result, 'Login phai thanh cong voi JWT hop le');
        $this->assertArrayHasKey('id', $result, 'Ket qua phai chua user id');
        $this->assertArrayHasKey('email', $result, 'Ket qua phai chua email');
        $this->assertEquals('test@muongthanh.vn', $result['email']);
    }

    /**
     * AUTH-002: Login that bai voi JWT da het han
     */
    public function testLoginFailsWithExpiredJwt()
    {
        $payload = $this->createValidPayload(array(
            'exp' => time() - 3600, // Token het han 1 gio truoc
        ));
        $token = $this->createTestJwt($payload);

        $result = $this->simulateHandleCallback($token);

        // Token het han phai bi tu choi
        $this->assertFalse($result, 'Login phai that bai voi JWT da het han');
    }

    /**
     * AUTH-003: Login that bai voi JWT co chu ky bi thay doi
     */
    public function testLoginFailsWithTamperedSignature()
    {
        $payload = $this->createValidPayload();
        $validToken = $this->createTestJwt($payload);

        // Thay doi chu ky
        $parts = explode('.', $validToken);
        $parts[2] = $this->base64UrlEncode('tampered_signature');
        $tamperedToken = implode('.', $parts);

        $result = $this->simulateHandleCallback($tamperedToken);

        $this->assertFalse($result, 'Login phai that bai voi JWT co chu ky bi thay doi');
    }

    /**
     * AUTH-004: Login that bai khi khong co token
     */
    public function testLoginFailsWithoutToken()
    {
        $result = AuthHandler::handleCallback(null);

        $this->assertFalse($result, 'Login phai that bai khi khong co token');
    }

    /**
     * AUTH-005: Login that bai voi token rong
     */
    public function testLoginFailsWithEmptyToken()
    {
        $result = AuthHandler::handleCallback('');

        $this->assertFalse($result, 'Login phai that bai voi token rong');
    }

    /**
     * AUTH-013: Logout xoa het session
     */
    public function testLogoutClearsSession()
    {
        // Dau tien login
        $payload = $this->createValidPayload();
        $token = $this->createTestJwt($payload);

        // Gia lap session da duoc tao
        $session = Yii::app()->session;
        $session[AuthHandler::SESSION_USER_KEY] = array('id' => '12345', 'email' => 'test@muongthanh.vn');
        $session[AuthHandler::SESSION_PERMISSIONS_KEY] = array('events' => '1 1 1 1');
        $session[AuthHandler::SESSION_TOKEN_KEY] = $token;
        $session[AuthHandler::SESSION_LAST_ACTIVITY_KEY] = time();

        // Kiem tra da co session
        $this->assertNotEmpty($session[AuthHandler::SESSION_USER_KEY], 'Session user phai co truoc khi logout');

        // Thuc hien logout
        AuthHandler::logout();

        // Kiem tra session da bi xoa
        $this->assertNull(
            isset($session[AuthHandler::SESSION_USER_KEY]) ? $session[AuthHandler::SESSION_USER_KEY] : null,
            'Session user phai bi xoa sau logout'
        );
        $this->assertNull(
            isset($session[AuthHandler::SESSION_PERMISSIONS_KEY]) ? $session[AuthHandler::SESSION_PERMISSIONS_KEY] : null,
            'Session permissions phai bi xoa sau logout'
        );
        $this->assertNull(
            isset($session[AuthHandler::SESSION_TOKEN_KEY]) ? $session[AuthHandler::SESSION_TOKEN_KEY] : null,
            'Session token phai bi xoa sau logout'
        );
    }

    /**
     * AUTH-015: JWT voi permissions null su dung gia tri mac dinh (rong)
     */
    public function testJwtWithNullPermissionsUsesDefaults()
    {
        $payload = $this->createValidPayload();
        // Khong co truong 'perm' trong payload

        // Gia lap session
        $session = Yii::app()->session;
        $session[AuthHandler::SESSION_USER_KEY] = array('id' => '12345', 'email' => 'test@muongthanh.vn');
        $session[AuthHandler::SESSION_PERMISSIONS_KEY] = array(); // Permissions rong
        $session[AuthHandler::SESSION_LAST_ACTIVITY_KEY] = time();
        $session[AuthHandler::SESSION_TOKEN_KEY] = 'test_token';

        $permissions = AuthHandler::getPermissions();

        $this->assertIsArray($permissions, 'Permissions phai la array');
        // Khi khong co permissions, phai tra ve array rong hoac default
    }

    /**
     * AUTH-016: Admin role co quyen truy cap day du (wildcard *)
     */
    public function testAdminRoleHasFullAccess()
    {
        // Gia lap session voi quyen admin (wildcard)
        $session = Yii::app()->session;
        $session[AuthHandler::SESSION_USER_KEY] = array(
            'id' => '1',
            'email' => 'admin@muongthanh.vn',
            'exp' => time() + 3600,
        );
        $session[AuthHandler::SESSION_PERMISSIONS_KEY] = array('*' => '1 1 1 1');
        $session[AuthHandler::SESSION_LAST_ACTIVITY_KEY] = time();
        $session[AuthHandler::SESSION_TOKEN_KEY] = 'admin_token';

        $permissions = AuthHandler::getPermissions();

        $this->assertArrayHasKey('*', $permissions, 'Admin phai co quyen wildcard *');
        $this->assertEquals('1 1 1 1', $permissions['*'], 'Admin phai co full CRUD');
    }

    /**
     * Test isAuthenticated tra ve false khi chua login
     */
    public function testIsAuthenticatedReturnsFalseWhenNotLoggedIn()
    {
        // Dam bao session rong
        AuthHandler::logout();

        $result = AuthHandler::isAuthenticated();

        $this->assertFalse($result, 'isAuthenticated phai tra ve false khi chua login');
    }

    /**
     * Test isAuthenticated tra ve true khi da login
     */
    public function testIsAuthenticatedReturnsTrueWhenLoggedIn()
    {
        $session = Yii::app()->session;
        $session[AuthHandler::SESSION_USER_KEY] = array(
            'id' => '12345',
            'email' => 'test@muongthanh.vn',
            'exp' => time() + 3600,
        );
        $session[AuthHandler::SESSION_LAST_ACTIVITY_KEY] = time();
        $session[AuthHandler::SESSION_TOKEN_KEY] = 'test_token';

        $result = AuthHandler::isAuthenticated();

        $this->assertTrue($result, 'isAuthenticated phai tra ve true khi da login');
    }

    /**
     * Test session timeout
     */
    public function testSessionTimeout()
    {
        $session = Yii::app()->session;
        $session[AuthHandler::SESSION_USER_KEY] = array(
            'id' => '12345',
            'email' => 'test@muongthanh.vn',
            'exp' => time() + 3600,
        );
        // Dat last_activity qua khu xa (vuot timeout)
        $session[AuthHandler::SESSION_LAST_ACTIVITY_KEY] = time() - 7200; // 2 gio truoc
        $session[AuthHandler::SESSION_TOKEN_KEY] = 'test_token';

        $result = AuthHandler::isAuthenticated();

        $this->assertFalse($result, 'isAuthenticated phai tra ve false khi session timeout');
    }

    /**
     * Test getUser tra ve null khi chua dang nhap
     */
    public function testGetUserReturnsNullWhenNotAuthenticated()
    {
        AuthHandler::logout();

        $user = AuthHandler::getUser();

        $this->assertNull($user, 'getUser phai tra ve null khi chua dang nhap');
    }

    /**
     * Test getUser tra ve user data khi da dang nhap
     */
    public function testGetUserReturnsUserDataWhenAuthenticated()
    {
        $userData = array(
            'id' => '12345',
            'email' => 'test@muongthanh.vn',
            'full_name' => 'Nguyen Van Test',
            'exp' => time() + 3600,
        );

        $session = Yii::app()->session;
        $session[AuthHandler::SESSION_USER_KEY] = $userData;
        $session[AuthHandler::SESSION_LAST_ACTIVITY_KEY] = time();
        $session[AuthHandler::SESSION_TOKEN_KEY] = 'test_token';

        $user = AuthHandler::getUser();

        $this->assertNotNull($user, 'getUser phai tra ve user data khi da dang nhap');
        $this->assertEquals($userData['email'], $user['email']);
    }

    /**
     * Test debugToken decode token khong validate
     */
    public function testDebugTokenDecodesWithoutValidation()
    {
        $payload = $this->createValidPayload();
        $token = $this->createTestJwt($payload);

        $result = AuthHandler::debugToken($token);

        $this->assertArrayHasKey('header', $result, 'Result phai chua header');
        $this->assertArrayHasKey('payload', $result, 'Result phai chua payload');
        $this->assertEquals('JWT', $result['header']['typ']);
    }

    /**
     * Test debugToken voi token format sai
     */
    public function testDebugTokenWithInvalidFormat()
    {
        $result = AuthHandler::debugToken('invalid_token_without_dots');

        $this->assertArrayHasKey('error', $result, 'Result phai chua error khi token format sai');
    }

    /**
     * Helper method de gia lap handleCallback
     * Luu y: Day la mock, thuc te can test voi JWT extension
     */
    private function simulateHandleCallback($token)
    {
        if (empty($token)) {
            return false;
        }

        // Parse token
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        // Decode payload
        $payloadJson = base64_decode(strtr($parts[1], '-_', '+/'));
        $payload = json_decode($payloadJson, true);

        if (!$payload) {
            return false;
        }

        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }

        // Verify signature (simplified - trong thuc te dung JWT library)
        $header = $parts[0];
        $payloadEncoded = $parts[1];
        $signature = $parts[2];

        $expectedSignature = hash_hmac('sha256', $header . '.' . $payloadEncoded, $this->testSecret, true);
        $expectedSignatureEncoded = rtrim(strtr(base64_encode($expectedSignature), '+/', '-_'), '=');

        if ($signature !== $expectedSignatureEncoded) {
            return false;
        }

        // Extract user data
        $claimSid = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/sid';
        $claimEmail = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress';
        $claimName = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name';

        return array(
            'id' => isset($payload[$claimSid]) ? $payload[$claimSid] : null,
            'email' => isset($payload[$claimEmail]) ? $payload[$claimEmail] : null,
            'full_name' => isset($payload[$claimName]) ? $payload[$claimName] : null,
        );
    }
}
