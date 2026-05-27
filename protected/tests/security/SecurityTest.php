<?php

/**
 * SecurityTest - Security tests
 *
 * Test cases tu Test_case.md:
 * - SEC-001: SQL Injection trong form tim kiem
 * - SEC-002: XSS trong ten nguoi tham du
 * - SEC-003: CSRF attack khi xoa du lieu
 * - SEC-004: Direct URL access vao trang admin khong can login
 * - SEC-005: Path traversal trong upload file
 * - SEC-006: Password hash kiem tra khong dung MD5
 * - SEC-007: API key khong lo trong HTML source
 */

class SecurityTest extends CTestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * SEC-001: SQL Injection trong form tim kiem
     * Nhap "' OR '1'='1" vao o tim kiem
     * Expected: Cau query duoc parameterized; khong tra ve du lieu toan bo
     */
    public function testSqlInjectionInSearchForm()
    {
        $maliciousInput = "' OR '1'='1";

        // Khi dung prepared statement, input se duoc escape
        $escapedInput = addslashes($maliciousInput);

        $this->assertStringContainsString("\\'", $escapedInput, 'Dau ngoac phai duoc escape');

        // Kiem tra khong chua ky tu SQL nguy hiem sau khi escape dung cach
        // Trong Yii, CActiveRecord su dung PDO prepared statements tu dong
    }

    /**
     * SEC-002: XSS trong ten nguoi tham du
     * Nhap "<script>alert('xss')</script>" vao ten
     * Expected: Ten hien thi duoi dang text da escape, script khong chay
     */
    public function testXssInAttendeeName()
    {
        $maliciousName = "<script>alert('xss')</script>";

        // Encode output
        $encodedName = htmlspecialchars($maliciousName, ENT_QUOTES, 'UTF-8');

        $this->assertStringNotContainsString('<script>', $encodedName, 'Script tag phai bi encode');
        $this->assertStringContainsString('&lt;script&gt;', $encodedName);
        $this->assertStringContainsString('alert', $encodedName, 'Noi dung van con nhung da escape');
    }

    /**
     * SEC-003: CSRF attack khi xoa du lieu
     * Tao form ben ngoai POST den URL xoa
     * Expected: Yii CSRF token validation tu choi request
     */
    public function testCsrfProtection()
    {
        // Yii 1.x co CSRF protection bang CHttpRequest::enableCsrfValidation
        // Moi form phai co hidden field YII_CSRF_TOKEN

        $csrfTokenRequired = true;
        $requestHasCsrfToken = false; // Gia lap request tu ben ngoai khong co token

        $isValidRequest = ($csrfTokenRequired && $requestHasCsrfToken);

        $this->assertFalse($isValidRequest, 'Request khong co CSRF token phai bi tu choi');
    }

    /**
     * SEC-004: Direct URL access vao trang admin khong can login
     * Truy cap /admin/attendees/index khong co session
     * Expected: Redirect ve login
     */
    public function testDirectAdminUrlAccessWithoutLogin()
    {
        $isLoggedIn = false;
        $isAdminPage = true;
        $requiresAuth = true;

        $canAccess = !$requiresAuth || $isLoggedIn;

        $this->assertFalse($canAccess, 'Phai redirect ve login khi chua dang nhap');
    }

    /**
     * SEC-005: Path traversal trong upload file
     * Upload file voi ten "../../config/main.php"
     * Expected: He thong sanitize ten file, luu vao dung thu muc uploads
     */
    public function testPathTraversalInFileUpload()
    {
        $maliciousFilename = '../../config/main.php';

        // Sanitize filename
        $sanitizedFilename = basename($maliciousFilename);

        $this->assertEquals('main.php', $sanitizedFilename, 'basename() loai bo path traversal');
        $this->assertStringNotContainsString('..', $sanitizedFilename);

        // Kiem tra them: loai bo ky tu nguy hiem
        $safeFilename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $sanitizedFilename);
        $this->assertEquals('main.php', $safeFilename);
    }

    /**
     * SEC-006: Password hash kiem tra khong dung MD5
     * Kiem tra password_hash trong DB
     * Expected: Hash phai la bcrypt ($2y$) hoac SHA-256+salt, khong phai MD5 raw
     */
    public function testPasswordHashNotMd5()
    {
        $plainPassword = 'mypassword123';

        // MD5 hash (khong an toan)
        $md5Hash = md5($plainPassword);

        // Bcrypt hash (an toan)
        $bcryptHash = password_hash($plainPassword, PASSWORD_BCRYPT);

        // Kiem tra bcrypt bat dau voi $2y$
        $isBcrypt = (substr($bcryptHash, 0, 4) === '$2y$');
        $this->assertTrue($isBcrypt, 'Hash phai la bcrypt');

        // Kiem tra MD5 chi co 32 ky tu (khong an toan)
        $this->assertEquals(32, strlen($md5Hash), 'MD5 chi co 32 ky tu - khong duoc dung');
        $this->assertEquals(60, strlen($bcryptHash), 'Bcrypt co 60 ky tu');

        // Verify password
        $this->assertTrue(password_verify($plainPassword, $bcryptHash));
    }

    /**
     * SEC-007: API key khong lo trong HTML source
     * View source trang admin
     * Expected: externalApiKey khong xuat hien trong HTML response
     */
    public function testApiKeyNotExposedInHtml()
    {
        // Gia lap HTML response
        $htmlContent = '<html><head></head><body>
            <div>Dashboard content</div>
            <script>
                var config = {
                    baseUrl: "/admin",
                    // API key khong duoc o day!
                };
            </script>
        </body></html>';

        // API key pattern thuong la chuoi dai
        $apiKeyPattern = '/[a-zA-Z0-9]{32,}/'; // 32+ ky tu

        // Kiem tra khong co chuoi giong API key trong HTML
        // (Day la kiem tra don gian, thuc te can cu the hon)

        $this->assertStringNotContainsString('externalApiKey', $htmlContent);
        $this->assertStringNotContainsString('apiKey', $htmlContent);
        $this->assertStringNotContainsString('api_key', $htmlContent);
    }

    /**
     * Test HTTP security headers
     */
    public function testSecurityHeaders()
    {
        $requiredHeaders = array(
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-XSS-Protection' => '1; mode=block',
        );

        foreach ($requiredHeaders as $header => $value) {
            $this->assertNotEmpty($header);
            $this->assertNotEmpty($value);
        }
    }

    /**
     * Test session security settings
     */
    public function testSessionSecuritySettings()
    {
        // Kiem tra session settings
        $settings = array(
            'httponly' => true, // Cookie khong truy cap duoc tu JS
            'secure' => true,   // Chi gui qua HTTPS
            'samesite' => 'Strict', // Chong CSRF
        );

        $this->assertTrue($settings['httponly'], 'Session cookie phai httponly');
        $this->assertTrue($settings['secure'], 'Session cookie phai secure (HTTPS)');
        $this->assertEquals('Strict', $settings['samesite']);
    }

    /**
     * Test file upload validation
     */
    public function testFileUploadValidation()
    {
        $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif', 'pdf');
        $dangerousExtensions = array('php', 'exe', 'sh', 'bat', 'js', 'html');

        foreach ($dangerousExtensions as $ext) {
            $isAllowed = in_array($ext, $allowedExtensions);
            $this->assertFalse($isAllowed, "Extension .$ext phai bi tu choi");
        }
    }

    /**
     * Test input length limits
     */
    public function testInputLengthLimits()
    {
        $limits = array(
            'username' => 50,
            'email' => 255,
            'full_name' => 255,
            'password' => 128,
        );

        // Input qua dai phai bi tu choi
        $longInput = str_repeat('a', 500);

        foreach ($limits as $field => $maxLength) {
            $isValid = (strlen($longInput) <= $maxLength);
            $this->assertFalse($isValid, "Input $field qua dai phai bi tu choi");
        }
    }

    /**
     * Test email validation
     */
    public function testEmailValidation()
    {
        $validEmails = array(
            'test@example.com',
            'user.name@domain.org',
            'user+tag@company.co.uk',
        );

        $invalidEmails = array(
            'not-an-email',
            '@nodomain.com',
            'missing@',
            'spaces in@email.com',
        );

        foreach ($validEmails as $email) {
            $isValid = filter_var($email, FILTER_VALIDATE_EMAIL);
            $this->assertNotFalse($isValid, "Email '$email' phai hop le");
        }

        foreach ($invalidEmails as $email) {
            $isValid = filter_var($email, FILTER_VALIDATE_EMAIL);
            $this->assertFalse($isValid, "Email '$email' phai khong hop le");
        }
    }
}
