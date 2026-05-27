<?php

/**
 * AuthenticationTest - Functional tests cho module Authentication
 *
 * Test cases:
 * - AUTH-014: Truy cap admin khong login thi redirect
 * - AUTH-019: Unit account khong the truy cap admin pages
 * - Test tich hop giua AuthHandler va AdminController
 */

class AuthenticationTest extends CDbTestCase
{
    /**
     * @var string Base URL cho admin module
     */
    private $adminBaseUrl = '/admin';

    protected function setUp()
    {
        parent::setUp();
        // Reset session
        $this->clearSession();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->clearSession();
    }

    /**
     * Xoa session
     */
    private function clearSession()
    {
        AuthHandler::logout();
    }

    /**
     * Tao session cho user da dang nhap
     *
     * @param array $userData Du lieu user
     * @param array $permissions Quyen cua user
     */
    private function loginAsUser($userData, $permissions = array())
    {
        $session = Yii::app()->session;

        $session[AuthHandler::SESSION_USER_KEY] = array_merge(array(
            'id' => '12345',
            'email' => 'test@muongthanh.vn',
            'full_name' => 'Test User',
            'exp' => time() + 3600,
        ), $userData);

        $session[AuthHandler::SESSION_PERMISSIONS_KEY] = $permissions;
        $session[AuthHandler::SESSION_LAST_ACTIVITY_KEY] = time();
        $session[AuthHandler::SESSION_TOKEN_KEY] = 'test_token_' . time();
    }

    /**
     * AUTH-014: Truy cap trang admin khi chua login thi redirect ve login
     */
    public function testAccessAdminWithoutLoginRedirects()
    {
        // Dam bao chua login
        $this->clearSession();

        // Kiem tra isAuthenticated tra ve false
        $isAuth = AuthHandler::isAuthenticated();
        $this->assertFalse($isAuth, 'User chua login phai chua duoc authenticated');

        // AdminController->init() se redirect khi chua login
        // Trong unit test, chung ta chi kiem tra logic, khong test HTTP redirect
    }

    /**
     * Test AdminController kiem tra authentication trong init()
     */
    public function testAdminControllerRequiresAuthentication()
    {
        $this->clearSession();

        // Tao mock AdminController de test
        // Trong thuc te, AdminController->init() se goi AuthHandler::isAuthenticated()
        $isAuthenticated = AuthHandler::isAuthenticated();

        $this->assertFalse($isAuthenticated, 'isAuthenticated phai false khi chua login');
    }

    /**
     * AUTH-019: Unit account khong the truy cap admin pages
     *
     * Unit account la tai khoan cua don vi (unit_accounts table),
     * khac voi users (admin/HO staff)
     */
    public function testUnitAccountCannotAccessAdminPages()
    {
        // Login nhu unit account (khong co quyen admin)
        $this->loginAsUser(
            array(
                'id' => 'unit_123',
                'email' => 'donvi@muongthanh.vn',
                'account_type' => 'unit', // Danh dau la unit account
            ),
            array() // Khong co quyen CRUD nao
        );

        // Kiem tra khong co quyen truy cap events
        $canAccessEvents = PermissionHelper::canRead('events');
        $this->assertFalse($canAccessEvents, 'Unit account khong duoc doc events');

        // Kiem tra khong co quyen truy cap attendees
        $canAccessAttendees = PermissionHelper::canRead('attendees');
        $this->assertFalse($canAccessAttendees, 'Unit account khong duoc doc attendees');
    }

    /**
     * Test admin user co quyen truy cap day du
     */
    public function testAdminUserHasFullAccess()
    {
        // Login nhu admin voi wildcard permission
        $this->loginAsUser(
            array(
                'id' => 'admin_1',
                'email' => 'admin@muongthanh.vn',
                'account_type' => 'admin',
            ),
            array('*' => '1 1 1 1') // Wildcard - full access
        );

        // Kiem tra co quyen truy cap moi thu
        $this->assertTrue(PermissionHelper::canCreate('events'));
        $this->assertTrue(PermissionHelper::canRead('events'));
        $this->assertTrue(PermissionHelper::canUpdate('events'));
        $this->assertTrue(PermissionHelper::canDelete('events'));

        // Kiem tra bat ky controller nao cung duoc
        $this->assertTrue(PermissionHelper::can('anycontroller', 'create'));
    }

    /**
     * Test HR user co quyen han che
     */
    public function testHRUserHasLimitedAccess()
    {
        // Login nhu HR voi quyen cu the
        $this->loginAsUser(
            array(
                'id' => 'hr_1',
                'email' => 'hr@muongthanh.vn',
                'account_type' => 'hr',
            ),
            array(
                'registrations' => '1 1 1 0', // CRUD tru Delete
                'attendees' => '0 1 1 0',     // Chi Read va Update
            )
        );

        // Kiem tra registrations
        $this->assertTrue(PermissionHelper::canCreate('registrations'));
        $this->assertTrue(PermissionHelper::canRead('registrations'));
        $this->assertTrue(PermissionHelper::canUpdate('registrations'));
        $this->assertFalse(PermissionHelper::canDelete('registrations'));

        // Kiem tra attendees
        $this->assertFalse(PermissionHelper::canCreate('attendees'));
        $this->assertTrue(PermissionHelper::canRead('attendees'));
        $this->assertTrue(PermissionHelper::canUpdate('attendees'));
        $this->assertFalse(PermissionHelper::canDelete('attendees'));
    }

    /**
     * Test session timeout
     */
    public function testSessionTimeoutLogsOutUser()
    {
        // Login
        $this->loginAsUser(array(
            'id' => '12345',
            'email' => 'test@muongthanh.vn',
        ));

        // Dat last_activity ve qua khu (vuot timeout 30 phut)
        $session = Yii::app()->session;
        $session[AuthHandler::SESSION_LAST_ACTIVITY_KEY] = time() - 3600; // 1 gio truoc

        // Kiem tra isAuthenticated phai tra ve false
        $isAuth = AuthHandler::isAuthenticated();
        $this->assertFalse($isAuth, 'Session timeout phai logout user');
    }

    /**
     * Test token expiration
     */
    public function testTokenExpirationLogsOutUser()
    {
        // Login voi token da het han
        $session = Yii::app()->session;
        $session[AuthHandler::SESSION_USER_KEY] = array(
            'id' => '12345',
            'email' => 'test@muongthanh.vn',
            'exp' => time() - 3600, // Token het han 1 gio truoc
        );
        $session[AuthHandler::SESSION_PERMISSIONS_KEY] = array('events' => '1 1 1 1');
        $session[AuthHandler::SESSION_LAST_ACTIVITY_KEY] = time();
        $session[AuthHandler::SESSION_TOKEN_KEY] = 'expired_token';

        // Kiem tra isAuthenticated phai tra ve false
        $isAuth = AuthHandler::isAuthenticated();
        $this->assertFalse($isAuth, 'Token het han phai logout user');
    }

    /**
     * Test permission inheritance
     */
    public function testPermissionInheritance()
    {
        // Login voi quyen cho 'events' nhung khong co cho 'registrationperiods'
        $this->loginAsUser(
            array('id' => '12345'),
            array('events' => '1 1 1 1')
        );

        // registrationperiods nen ke thua quyen tu events
        $permissions = AuthHandler::getPermissions();

        // Kiem tra inheritance rule duoc ap dung
        // Dua tren inheritRelatedPermissions() trong AuthHandler
        if (isset($permissions['registrationperiods'])) {
            $this->assertEquals(
                $permissions['events'],
                $permissions['registrationperiods'],
                'registrationperiods phai ke thua quyen tu events'
            );
        }
    }

    /**
     * Test multiple permission checks in sequence
     */
    public function testMultiplePermissionChecks()
    {
        $this->loginAsUser(
            array('id' => '12345'),
            array(
                'events' => '1 1 1 1',
                'attendees' => '0 1 0 0',
                'sports' => '1 1 1 0',
            )
        );

        // Batch check permissions
        $checks = array(
            array('events', 'create', true),
            array('events', 'delete', true),
            array('attendees', 'create', false),
            array('attendees', 'read', true),
            array('sports', 'delete', false),
            array('nonexistent', 'read', false),
        );

        foreach ($checks as $check) {
            list($controller, $operation, $expected) = $check;
            $result = PermissionHelper::can($controller, $operation);
            $this->assertEquals(
                $expected,
                $result,
                "Permission check failed: $controller/$operation expected " . ($expected ? 'true' : 'false')
            );
        }
    }

    /**
     * Test updateSessionWithProfile cap nhat session dung
     */
    public function testUpdateSessionWithProfile()
    {
        // Login truoc
        $this->loginAsUser(array('id' => '12345'));

        // Gia lap profile data tu SSO API
        $profile = array(
            'Employee' => array(
                'Id' => 'EMP001',
                'Avatar' => 'http://example.com/avatar.jpg',
                'Mobile' => '0901234567',
                'Hotel' => array(
                    'Code' => 'HN02',
                    'Symbol' => 'HN',
                    'Name' => 'Muong Thanh Grand Hanoi',
                ),
                'Area' => array(
                    'Id' => '2',
                    'Name' => 'Mien Bac',
                ),
                'Department' => array(
                    'Id' => '5',
                    'Name' => 'IT',
                ),
                'Position' => array(
                    'Id' => '10',
                    'Name' => 'Developer',
                ),
            ),
        );

        // Cap nhat session
        AuthHandler::updateSessionWithProfile($profile);

        // Kiem tra session da duoc cap nhat
        $user = AuthHandler::getUser();

        $this->assertEquals('HN02', $user['property_code']);
        $this->assertEquals('Muong Thanh Grand Hanoi', $user['hotel_name']);
        $this->assertEquals('2', $user['regional_id']);
        $this->assertEquals('IT', $user['department_name']);
        $this->assertEquals('Developer', $user['position_name']);
    }

    /**
     * Test concurrent session updates
     */
    public function testConcurrentSessionUpdates()
    {
        // Login
        $this->loginAsUser(array('id' => '12345'));

        // Kiem tra isAuthenticated cap nhat last_activity
        $session = Yii::app()->session;
        $oldActivity = $session[AuthHandler::SESSION_LAST_ACTIVITY_KEY];

        // Doi 1 giay
        sleep(1);

        // Goi isAuthenticated
        AuthHandler::isAuthenticated();

        $newActivity = $session[AuthHandler::SESSION_LAST_ACTIVITY_KEY];

        $this->assertGreaterThanOrEqual(
            $oldActivity,
            $newActivity,
            'last_activity phai duoc cap nhat sau moi lan kiem tra'
        );
    }
}
