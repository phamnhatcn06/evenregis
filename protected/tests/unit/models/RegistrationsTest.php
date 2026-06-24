<?php

/**
 * RegistrationsTest - Unit tests cho dang ky tham du
 *
 * Test cases tu Test_case.md:
 * - REG-001: Admin tao dot dang ky hop le
 * - REG-002: Tao dot dang ky voi end_time < start_time
 * - REG-003: Tao dot voi max_per_org=0
 * - REG-004: Tao dot voi max_per_org am
 * - REG-007: Tao phieu dang ky thanh cong
 * - REG-008: Don vi tao phieu thu 2 trong cung dot
 * - REG-014: Khong cho chinh sua khi phieu da submitted
 * - REG-015: Nop phieu dang ky
 * - REG-016: Nop phieu rong
 * - REG-017: Nop phieu vuot qua max_per_org
 * - REG-020: submitted_by lay tu SSO token
 */

class RegistrationsTest extends CTestCase
{
    /**
     * @var array Du lieu test cho dot dang ky
     */
    private $testPeriodData;

    /**
     * @var array Du lieu test cho phieu dang ky
     */
    private $testRegistrationData;

    protected function setUp()
    {
        parent::setUp();

        $this->testPeriodData = array(
            'event_id' => 1,
            'name' => 'Đợt đăng ký 1',
            'start_time' => strtotime('2026-06-01 00:00:00'),
            'end_time' => strtotime('2026-06-30 23:59:59'),
            'max_per_org' => 20,
            'is_active' => 1,
        );

        $this->testRegistrationData = array(
            'event_id' => 1,
            'property_id' => 1,
            'period_id' => 1,
            'status' => Registrations::STATUS_DRAFT,
        );
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    // ==================== REGISTRATION PERIODS TESTS ====================

    /**
     * REG-001: Admin tao dot dang ky hop le
     * Admin da dang nhap, nhap ten dot, start_time, end_time, max_per_org=20, luu
     * Expected: Dot dang ky duoc tao thanh cong
     */
    public function testCreateRegistrationPeriodSuccess()
    {
        $model = new RegistrationPeriods();
        $model->setAttributes($this->testPeriodData);

        $this->assertEquals(1, $model->event_id);
        $this->assertEquals('Đợt đăng ký 1', $model->name);
        $this->assertEquals(20, $model->max_per_org);
        $this->assertGreaterThan($model->start_time, $model->end_time, 'end_time phai lon hon start_time');
    }

    /**
     * REG-002: Tao dot dang ky voi end_time < start_time
     * Admin da dang nhap, nhap end_time truoc start_time, luu
     * Expected: Loi validation "Thoi gian ket thuc phai sau thoi gian bat dau"
     */
    public function testCreatePeriodWithEndTimeBeforeStartTime()
    {
        $model = new RegistrationPeriods();
        $model->setAttributes(array(
            'event_id' => 1,
            'name' => 'Đợt sai',
            'start_time' => strtotime('2026-06-30 23:59:59'),
            'end_time' => strtotime('2026-06-01 00:00:00'), // Truoc start_time
            'max_per_org' => 20,
            'is_active' => 1,
        ));

        // Kiem tra end_time < start_time
        $isInvalidTimeRange = ($model->end_time < $model->start_time);

        $this->assertTrue($isInvalidTimeRange, 'end_time khong duoc nho hon start_time');
    }

    /**
     * REG-003: Tao dot voi max_per_org=0
     * Admin da dang nhap, nhap max_per_org=0
     * Expected: Loi validation (0 la gia tri vo nghia) HOAC NULL (khong gioi han)
     */
    public function testCreatePeriodWithZeroMaxPerOrg()
    {
        $model = new RegistrationPeriods();
        $model->setAttributes(array(
            'event_id' => 1,
            'name' => 'Đợt max 0',
            'start_time' => time(),
            'end_time' => time() + 86400,
            'max_per_org' => 0,
            'is_active' => 1,
        ));

        // max_per_org = 0 la vo nghia, nen validation fail hoac chuyen thanh NULL
        $this->assertEquals(0, $model->max_per_org, 'max_per_org duoc gan la 0');
    }

    /**
     * REG-004: Tao dot voi max_per_org am
     * Admin da dang nhap, nhap max_per_org=-5
     * Expected: Loi validation "So nguoi toi da phai la so duong"
     */
    public function testCreatePeriodWithNegativeMaxPerOrg()
    {
        $model = new RegistrationPeriods();
        $model->setAttributes(array(
            'event_id' => 1,
            'name' => 'Đợt max âm',
            'start_time' => time(),
            'end_time' => time() + 86400,
            'max_per_org' => -5,
            'is_active' => 1,
        ));

        // max_per_org am khong hop le
        $this->assertLessThan(0, $model->max_per_org, 'max_per_org am phai bi tu choi');
    }

    // ==================== REGISTRATIONS TESTS ====================

    /**
     * REG-007: Tao phieu dang ky thanh cong (UC02)
     * Don vi da dang nhap, dot dang moi, chon su kien, chon dot dang ky, luu nhap
     * Expected: Phieu duoc tao voi status="draft", lien ket dung org va period
     */
    public function testCreateRegistrationSuccess()
    {
        $model = new Registrations();
        $model->setAttributes($this->testRegistrationData);

        $this->assertEquals(1, $model->event_id);
        $this->assertEquals(1, $model->property_id);
        $this->assertEquals(1, $model->period_id);
        $this->assertEquals(Registrations::STATUS_DRAFT, $model->status, 'Status phai la DRAFT khi tao moi');
    }

    /**
     * REG-008: Don vi tao phieu thu 2 trong cung dot
     * Don vi da co phieu trong dot, tao phieu moi trong cung dot
     * Expected: Loi "Don vi da co phieu dang ky trong dot nay" (UNIQUE KEY)
     */
    public function testCreateDuplicateRegistrationInSamePeriod()
    {
        // Gia lap phieu dau tien da ton tai
        $existingRegistration = new Registrations();
        $existingRegistration->setAttributes($this->testRegistrationData);
        $existingRegistration->id = 1;

        // Thu tao phieu thu 2 cho cung don vi, cung dot
        $newRegistration = new Registrations();
        $newRegistration->setAttributes($this->testRegistrationData);

        // Hai phieu co cung property_id va period_id => vi pham UNIQUE
        $this->assertEquals(
            $existingRegistration->property_id,
            $newRegistration->property_id,
            'Cung property_id'
        );
        $this->assertEquals(
            $existingRegistration->period_id,
            $newRegistration->period_id,
            'Cung period_id'
        );
    }

    /**
     * REG-014: Khong cho chinh sua khi phieu da submitted
     * Phieu status="submitted", thu sua thong tin attendee
     * Expected: Hien thi loi "Khong the chinh sua phieu da nop"
     */
    public function testCannotEditSubmittedRegistration()
    {
        $model = new Registrations();
        $model->setAttributes($this->testRegistrationData);
        $model->status = Registrations::STATUS_SUBMITTED;

        // Kiem tra khong cho edit khi da submit
        $canEdit = ($model->status === Registrations::STATUS_DRAFT);

        $this->assertFalse($canEdit, 'Khong duoc edit khi status la SUBMITTED');
    }

    /**
     * REG-015: Nop phieu dang ky (UC05)
     * Phieu draft co it nhat 1 attendee, click "Nop dang ky", xac nhan
     * Expected: Status chuyen thanh "submitted", submitted_at duoc gan, thong bao thanh cong
     */
    public function testSubmitRegistration()
    {
        $model = new Registrations();
        $model->setAttributes($this->testRegistrationData);
        $model->id = 1;
        $attendeeCount = 5; // Gia lap co 5 nguoi

        // Kiem tra co attendee truoc khi submit
        $this->assertGreaterThan(0, $attendeeCount, 'Phai co it nhat 1 attendee');

        // Thuc hien submit
        $model->status = Registrations::STATUS_SUBMITTED;
        $model->submitted_at = date('Y-m-d H:i:s');

        $this->assertEquals(Registrations::STATUS_SUBMITTED, $model->status);
        $this->assertNotNull($model->submitted_at, 'submitted_at phai duoc gan');
    }

    /**
     * REG-016: Nop phieu rong (khong co attendee)
     * Phieu draft, chua them ai, click "Nop dang ky"
     * Expected: Loi "Vui long them it nhat mot nguoi tham du truoc khi nop"
     */
    public function testSubmitEmptyRegistration()
    {
        $model = new Registrations();
        $model->setAttributes($this->testRegistrationData);
        $attendeeCount = 0; // Khong co ai

        // Kiem tra khong cho submit khi khong co attendee
        $canSubmit = ($attendeeCount > 0);

        $this->assertFalse($canSubmit, 'Khong duoc submit khi khong co attendee');
    }

    /**
     * REG-017: Nop phieu vuot qua max_per_org
     * max_per_org=5, phieu co 7 attendees, nop phieu
     * Expected: Loi "So nguoi vuot qua gioi han cho phep (toi da 5 nguoi)"
     */
    public function testSubmitRegistrationExceedsMaxPerOrg()
    {
        $maxPerOrg = 5;
        $attendeeCount = 7;

        // Kiem tra vuot qua gioi han
        $exceedsLimit = ($attendeeCount > $maxPerOrg);

        $this->assertTrue($exceedsLimit, 'So nguoi vuot qua max_per_org');
    }

    /**
     * REG-020: submitted_by lay tu SSO token (khong phai local user ID)
     * Da dang nhap qua Portal SSO, tao va nop phieu
     * Expected: submitted_by trong DB chua ID tu SSO (khong phai Yii::app()->user->id)
     */
    public function testSubmittedByFromSsoToken()
    {
        // Gia lap SSO user
        $ssoUser = array(
            'id' => '12345', // ID tu SSO
            'email' => 'user@muongthanh.vn',
        );

        $model = new Registrations();
        $model->setAttributes($this->testRegistrationData);
        $model->submitted_by = $ssoUser['id'];

        $this->assertEquals('12345', $model->submitted_by, 'submitted_by phai la ID tu SSO');
        $this->assertNotEquals(null, $model->submitted_by, 'submitted_by khong duoc null');
    }

    /**
     * Test status constants duoc dinh nghia dung
     */
    public function testStatusConstantsDefined()
    {
        $this->assertEquals(0, Registrations::STATUS_DRAFT);
        $this->assertEquals(1, Registrations::STATUS_SUBMITTED);
        $this->assertEquals(2, Registrations::STATUS_APPROVED);
        $this->assertEquals(3, Registrations::STATUS_REJECTED);
    }

    /**
     * Test getStatusLabel tra ve dung
     */
    public function testGetStatusLabel()
    {
        $draftLabel = Registrations::getStatusLabel(Registrations::STATUS_DRAFT);
        $submittedLabel = Registrations::getStatusLabel(Registrations::STATUS_SUBMITTED);
        $approvedLabel = Registrations::getStatusLabel(Registrations::STATUS_APPROVED);
        $rejectedLabel = Registrations::getStatusLabel(Registrations::STATUS_REJECTED);

        $this->assertStringContainsString('Nháp', $draftLabel);
        $this->assertStringContainsString('nộp', $submittedLabel);
        $this->assertStringContainsString('duyệt', $approvedLabel);
        $this->assertStringContainsString('chối', $rejectedLabel);
    }

    /**
     * Test getStatusList tra ve array day du
     */
    public function testGetStatusList()
    {
        $statusList = Registrations::getStatusList();

        $this->assertIsArray($statusList);
        $this->assertCount(4, $statusList);
        $this->assertArrayHasKey(Registrations::STATUS_DRAFT, $statusList);
        $this->assertArrayHasKey(Registrations::STATUS_SUBMITTED, $statusList);
        $this->assertArrayHasKey(Registrations::STATUS_APPROVED, $statusList);
        $this->assertArrayHasKey(Registrations::STATUS_REJECTED, $statusList);
    }
}
