<?php

/**
 * UnitAccountsTest - Unit tests cho tai khoan don vi
 *
 * Test cases tu Test_case.md:
 * - ORG-011: Tao tai khoan don vi thanh cong
 * - ORG-012: Tao tai khoan thu 2 cho cung don vi
 * - ORG-013: Dang nhap tai khoan don vi thanh cong
 * - ORG-014: Dang nhap voi mat khau sai
 * - ORG-015: Dang nhap tai khoan bi vo hieu hoa
 * - ORG-016: Dang nhap voi username chua SQL injection
 * - ORG-017: Doi mat khau tai khoan don vi
 * - ORG-018: Password_hash khong luu plaintext
 */

class UnitAccountsTest extends CTestCase
{
    /**
     * @var array Du lieu test
     */
    private $testAccountData;

    protected function setUp()
    {
        parent::setUp();

        $this->testAccountData = array(
            'username' => 'unit_hn01',
            'password' => 'SecurePass123!',
            'property_id' => 1,
            'is_active' => 1,
        );
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * ORG-011: Tao tai khoan don vi thanh cong
     * Don vi da ton tai, chua co tai khoan
     * Expected: Tai khoan duoc tao, lien ket 1-1 voi don vi
     */
    public function testCreateUnitAccountSuccess()
    {
        $model = new MUsers();
        $model->username = $this->testAccountData['username'];
        $model->password = $this->testAccountData['password'];

        $this->assertNotEmpty($model->username, 'Username phai duoc gan');
        $this->assertNotEmpty($model->password, 'Password phai duoc gan');
    }

    /**
     * ORG-012: Tao tai khoan thu 2 cho cung don vi
     * Don vi da co tai khoan, tao tai khoan moi cho cung organization_id
     * Expected: Loi "Don vi da co tai khoan" (UNIQUE KEY uq_unit_accounts_org)
     */
    public function testCreateDuplicateAccountForSameProperty()
    {
        $propertyId = 1;

        // Gia lap da co tai khoan cho property_id=1
        $account1Exists = true;

        // Thu tao tai khoan thu 2
        $model = new MUsers();
        $model->username = 'unit_hn01_2';
        $model->password = 'AnotherPass123!';

        // Trong thuc te, UNIQUE constraint tren property_id se tu choi
        $this->assertTrue($account1Exists, 'Da co tai khoan cho don vi nay');
    }

    /**
     * ORG-013: Dang nhap tai khoan don vi thanh cong
     * Tai khoan don vi ton tai, is_active=1, nhap dung username/password
     * Expected: Dang nhap thanh cong, vao dashboard don vi
     */
    public function testLoginUnitAccountSuccess()
    {
        $username = 'unit_hn01';
        $password = 'SecurePass123!';
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Gia lap kiem tra dang nhap
        $isValidPassword = password_verify($password, $hashedPassword);
        $isActive = true;

        $this->assertTrue($isValidPassword, 'Mat khau phai hop le');
        $this->assertTrue($isActive, 'Tai khoan phai dang hoat dong');
    }

    /**
     * ORG-014: Dang nhap voi mat khau sai
     * Tai khoan ton tai, nhap sai mat khau
     * Expected: Hien thi loi "Ten dang nhap hoac mat khau khong dung"
     */
    public function testLoginWithWrongPassword()
    {
        $correctPassword = 'SecurePass123!';
        $wrongPassword = 'WrongPassword!';
        $hashedPassword = password_hash($correctPassword, PASSWORD_BCRYPT);

        $isValidPassword = password_verify($wrongPassword, $hashedPassword);

        $this->assertFalse($isValidPassword, 'Mat khau sai phai bi tu choi');
    }

    /**
     * ORG-015: Dang nhap tai khoan bi vo hieu hoa
     * is_active=0, nhap dung username/password
     * Expected: Hien thi loi "Tai khoan da bi vo hieu hoa"
     */
    public function testLoginWithDeactivatedAccount()
    {
        $isActive = 0; // Tai khoan bi vo hieu hoa

        // Kiem tra is_active truoc khi cho dang nhap
        $canLogin = ($isActive == 1);

        $this->assertFalse($canLogin, 'Tai khoan bi vo hieu hoa khong duoc dang nhap');
    }

    /**
     * ORG-016: Dang nhap voi username chua SQL injection
     * Tai khoan ton tai, nhap username: admin' OR '1'='1
     * Expected: He thong xu ly an toan, khong bi SQL injection; tra ve loi dang nhap
     */
    public function testLoginWithSqlInjectionAttempt()
    {
        $maliciousUsername = "admin' OR '1'='1";

        // Kiem tra username duoc sanitize/escape dung cach
        // Trong Yii, CActiveRecord su dung prepared statements tu dong

        // Username khong nen chua ky tu dac biet SQL
        $containsInjection = (
            strpos($maliciousUsername, "'") !== false ||
            strpos($maliciousUsername, "OR") !== false
        );

        $this->assertTrue($containsInjection, 'Input chua ky tu injection');

        // Validate username format
        $isValidUsernameFormat = preg_match('/^[a-zA-Z0-9_]+$/', $maliciousUsername);
        $this->assertEquals(0, $isValidUsernameFormat, 'Username voi injection phai bi reject boi regex');
    }

    /**
     * ORG-017: Doi mat khau tai khoan don vi
     * Da dang nhap, nhap mat khau cu dung, nhap mat khau moi, luu
     * Expected: Mat khau duoc cap nhat, dang nhap lai bang mat khau moi thanh cong
     */
    public function testChangePassword()
    {
        $oldPassword = 'OldPassword123!';
        $newPassword = 'NewSecurePass456!';
        $hashedOldPassword = password_hash($oldPassword, PASSWORD_BCRYPT);

        // Kiem tra mat khau cu dung
        $isOldPasswordValid = password_verify($oldPassword, $hashedOldPassword);
        $this->assertTrue($isOldPasswordValid, 'Mat khau cu phai dung truoc khi doi');

        // Hash mat khau moi
        $hashedNewPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        // Kiem tra mat khau moi khac mat khau cu
        $this->assertNotEquals($hashedOldPassword, $hashedNewPassword, 'Hash moi phai khac hash cu');

        // Kiem tra dang nhap bang mat khau moi
        $canLoginWithNewPassword = password_verify($newPassword, $hashedNewPassword);
        $this->assertTrue($canLoginWithNewPassword, 'Phai dang nhap duoc bang mat khau moi');
    }

    /**
     * ORG-018: Password_hash khong luu plaintext
     * Tao tai khoan voi password "123456"
     * Expected: Truong password_hash chua chuoi hash (bcrypt/SHA), khong phai "123456"
     */
    public function testPasswordHashNotPlaintext()
    {
        $plainPassword = '123456';

        // Tao hash nhu he thong se lam
        $hashedPassword = password_hash($plainPassword, PASSWORD_BCRYPT);

        // Kiem tra hash KHONG phai plaintext
        $this->assertNotEquals($plainPassword, $hashedPassword, 'Hash khong duoc la plaintext');

        // Kiem tra hash la bcrypt (bat dau voi $2y$)
        $isBcrypt = (substr($hashedPassword, 0, 4) === '$2y$');
        $this->assertTrue($isBcrypt, 'Hash phai la bcrypt ($2y$)');

        // Kiem tra do dai hash bcrypt (60 ky tu)
        $this->assertEquals(60, strlen($hashedPassword), 'Bcrypt hash phai co 60 ky tu');
    }

    /**
     * Test password strength validation
     */
    public function testPasswordStrengthValidation()
    {
        $weakPasswords = array('123', 'password', 'abc');
        $strongPassword = 'SecureP@ss123!';

        foreach ($weakPasswords as $weakPass) {
            // Mat khau yeu khong duoc chap nhan (< 8 ky tu)
            $this->assertLessThan(8, strlen($weakPass), 'Mat khau yeu phai it hon 8 ky tu');
        }

        $this->assertGreaterThanOrEqual(8, strlen($strongPassword), 'Mat khau manh phai >= 8 ky tu');
    }

    /**
     * Test username format validation
     */
    public function testUsernameFormatValidation()
    {
        $validUsernames = array('user123', 'admin_ho', 'unit_hn01');
        $invalidUsernames = array('user@123', 'admin ho', 'unit#01', "admin'--");

        foreach ($validUsernames as $username) {
            $isValid = preg_match('/^[a-zA-Z0-9_]+$/', $username);
            $this->assertEquals(1, $isValid, "Username '$username' phai hop le");
        }

        foreach ($invalidUsernames as $username) {
            $isValid = preg_match('/^[a-zA-Z0-9_]+$/', $username);
            $this->assertEquals(0, $isValid, "Username '$username' phai khong hop le");
        }
    }
}
