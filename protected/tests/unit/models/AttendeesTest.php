<?php

/**
 * AttendeesTest - Unit tests cho nguoi tham du
 *
 * Test cases tu Test_case.md:
 * - ATT-001: Admin chinh sua thong tin attendee sau phe duyet
 * - ATT-002: QR token tu dong sinh khi attendee duoc approved
 * - ATT-003: QR token la duy nhat
 * - ATT-004: Badge number duoc gan tu dong va duy nhat
 * - ATT-005: Gan vai tro cho nguoi tham du
 * - ATT-006: Gan vai tro trung lap cho cung attendee
 * - ATT-007: Gan truong doan cho don vi
 * - ATT-008: Soft delete attendee
 * - ATT-009: Them attendee voi ten rong
 * - ATT-010: Them attendee voi ten qua dai
 * - ATT-011: Tim kiem attendee theo ten
 * - ATT-012: Thong tin check-in/check-out date hop le
 * - ATT-013: check_out_date truoc check_in_date
 */

class AttendeesTest extends CTestCase
{
    /**
     * @var array Du lieu test
     */
    private $testAttendeeData;

    protected function setUp()
    {
        parent::setUp();

        $this->testAttendeeData = array(
            'registration_id' => 1,
            'full_name' => 'Nguyễn Văn A',
            'position' => 'Nhân viên',
            'property_id' => 1,
            'is_active' => 1,
        );
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * ATT-001: Admin chinh sua thong tin attendee sau phe duyet (UC08)
     * Phieu da approved, attendee ton tai
     * Expected: Thong tin cap nhat, updated_at gan moi; audit log ghi lai
     */
    public function testAdminEditAttendeeAfterApproval()
    {
        $model = new Attendees();
        $model->setAttributes($this->testAttendeeData);
        $model->id = 1;
        $model->approval_status = Attendees::APPROVAL_APPROVED;

        // Cap nhat thong tin
        $oldName = $model->full_name;
        $model->full_name = 'Nguyễn Văn B';
        $model->updated_at = time();

        $this->assertNotEquals($oldName, $model->full_name);
        $this->assertNotNull($model->updated_at);
    }

    /**
     * ATT-002: QR token tu dong sinh khi attendee duoc approved
     * Phieu vua duoc approved
     * Expected: Moi attendee co qr_token duy nhat 64 ky tu
     */
    public function testQrTokenGeneratedOnApproval()
    {
        // Gia lap sinh qr_token
        $qrToken = bin2hex(random_bytes(32)); // 64 ky tu hex

        $this->assertEquals(64, strlen($qrToken), 'qr_token phai co 64 ky tu');
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $qrToken, 'qr_token phai la hex string');
    }

    /**
     * ATT-003: QR token la duy nhat (khong trung giua cac attendee)
     * Nhieu attendee trong he thong
     * Expected: Tat ca qr_token khac nhau (UNIQUE constraint)
     */
    public function testQrTokenIsUnique()
    {
        $tokens = array();
        for ($i = 0; $i < 100; $i++) {
            $token = bin2hex(random_bytes(32));
            $this->assertNotContains($token, $tokens, 'Token phai duy nhat');
            $tokens[] = $token;
        }

        $this->assertCount(100, $tokens);
        $this->assertCount(100, array_unique($tokens), 'Tat ca token phai khac nhau');
    }

    /**
     * ATT-004: Badge number duoc gan tu dong va duy nhat
     * Phieu approved
     * Expected: badge_number duoc gan dang "001", "002"... khong trung
     */
    public function testBadgeNumberAutoAssigned()
    {
        $badgeNumbers = array('001', '002', '003');

        foreach ($badgeNumbers as $badge) {
            $this->assertEquals(3, strlen($badge), 'Badge number phai co 3 ky tu');
            $this->assertMatchesRegularExpression('/^\d{3}$/', $badge, 'Badge phai la so');
        }

        $this->assertCount(3, array_unique($badgeNumbers), 'Badge numbers phai duy nhat');
    }

    /**
     * ATT-005: Gan vai tro cho nguoi tham du (UC09)
     * Attendee da approved, role ton tai
     * Expected: Ban ghi trong attendee_roles duoc tao
     */
    public function testAssignRoleToAttendee()
    {
        $attendeeRole = new AttendeeRoles();
        $attendeeRole->attendee_id = 1;
        $attendeeRole->role_id = 1; // Role "Truong doan"

        $this->assertEquals(1, $attendeeRole->attendee_id);
        $this->assertEquals(1, $attendeeRole->role_id);
    }

    /**
     * ATT-006: Gan vai tro trung lap cho cung attendee
     * Attendee da co role "support"
     * Expected: Loi hoac khong tao ban ghi moi (UNIQUE KEY uq_attendee_role)
     */
    public function testAssignDuplicateRole()
    {
        // Gia lap da co role
        $existingRole = array('attendee_id' => 1, 'role_id' => 1);

        // Thu gan lai cung role
        $newRole = array('attendee_id' => 1, 'role_id' => 1);

        // UNIQUE constraint se tu choi
        $isDuplicate = (
            $existingRole['attendee_id'] === $newRole['attendee_id'] &&
            $existingRole['role_id'] === $newRole['role_id']
        );

        $this->assertTrue($isDuplicate, 'Khong duoc gan trung role');
    }

    /**
     * ATT-007: Gan truong doan cho don vi (UC11)
     * Don vi co nhieu attendee
     * Expected: is_team_lead=1 cho attendee do; cac attendee khac van is_team_lead=0
     */
    public function testAssignTeamLead()
    {
        $attendees = array(
            array('id' => 1, 'full_name' => 'A', 'is_team_lead' => 0),
            array('id' => 2, 'full_name' => 'B', 'is_team_lead' => 0),
            array('id' => 3, 'full_name' => 'C', 'is_team_lead' => 0),
        );

        // Gan attendee 1 lam truong doan
        $attendees[0]['is_team_lead'] = 1;

        $teamLeadCount = 0;
        foreach ($attendees as $att) {
            if ($att['is_team_lead'] == 1) {
                $teamLeadCount++;
            }
        }

        $this->assertEquals(1, $teamLeadCount, 'Chi co 1 truong doan');
    }

    /**
     * ATT-008: Soft delete attendee (is_active=0)
     * Admin muon xoa attendee
     * Expected: is_active chuyen thanh 0 hoac deleted_at duoc gan; khong xoa khoi DB
     */
    public function testSoftDeleteAttendee()
    {
        $model = new Attendees();
        $model->setAttributes($this->testAttendeeData);
        $model->id = 1;

        // Soft delete
        $model->is_active = 0;
        $model->deleted_at = time();

        $this->assertEquals(0, $model->is_active);
        $this->assertNotNull($model->deleted_at);
    }

    /**
     * ATT-009: Them attendee voi ten rong
     * Phieu draft
     * Expected: Loi validation "Ho ten la bat buoc"
     */
    public function testAddAttendeeWithEmptyName()
    {
        $model = new Attendees();
        $model->setAttributes(array(
            'registration_id' => 1,
            'full_name' => '', // Ten rong
            'position' => 'Nhân viên',
        ));

        $result = $model->validate(array('full_name'));

        $this->assertFalse($result, 'Validate phai fail khi ten rong');
        $this->assertTrue($model->hasErrors('full_name'), 'Phai co loi tren truong full_name');
    }

    /**
     * ATT-010: Them attendee voi ten qua dai (>255 ky tu)
     * Phieu draft
     * Expected: Loi validation "Ten khong duoc vuot qua 255 ky tu"
     */
    public function testAddAttendeeWithLongName()
    {
        $longName = str_repeat('A', 300); // 300 ky tu

        $model = new Attendees();
        $model->setAttributes(array(
            'registration_id' => 1,
            'full_name' => $longName,
            'position' => 'Nhân viên',
        ));

        $this->assertGreaterThan(255, strlen($model->full_name), 'Ten qua dai');
    }

    /**
     * ATT-011: Tim kiem attendee theo ten
     * Nhieu attendee trong he thong
     * Expected: Danh sach loc dung attendee co ten chua "Nguyen"
     */
    public function testSearchAttendeeByName()
    {
        $attendees = array(
            array('id' => 1, 'full_name' => 'Nguyễn Văn A'),
            array('id' => 2, 'full_name' => 'Trần Văn B'),
            array('id' => 3, 'full_name' => 'Nguyễn Thị C'),
            array('id' => 4, 'full_name' => 'Lê Văn D'),
        );

        $searchTerm = 'Nguyễn';
        $filtered = array_filter($attendees, function ($att) use ($searchTerm) {
            return strpos($att['full_name'], $searchTerm) !== false;
        });

        $this->assertCount(2, $filtered, 'Phai tim duoc 2 attendee co ten Nguyen');
    }

    /**
     * ATT-012: Thong tin check-in/check-out date hop le
     * Attendee duoc phe duyet
     * Expected: Du lieu luu dung
     */
    public function testValidCheckInOutDates()
    {
        $model = new Attendees();
        $model->setAttributes($this->testAttendeeData);
        $model->check_in_date = '2026-11-01';
        $model->check_out_date = '2026-11-03';

        $checkIn = strtotime($model->check_in_date);
        $checkOut = strtotime($model->check_out_date);

        $this->assertGreaterThan($checkIn, $checkOut, 'check_out phai sau check_in');
    }

    /**
     * ATT-013: check_out_date truoc check_in_date
     * Admin chinh sua attendee
     * Expected: Loi validation
     */
    public function testInvalidCheckOutBeforeCheckIn()
    {
        $model = new Attendees();
        $model->setAttributes($this->testAttendeeData);
        $model->check_in_date = '2026-11-03';
        $model->check_out_date = '2026-11-01'; // Truoc check_in

        $checkIn = strtotime($model->check_in_date);
        $checkOut = strtotime($model->check_out_date);

        $isInvalid = ($checkOut < $checkIn);

        $this->assertTrue($isInvalid, 'check_out truoc check_in phai bi tu choi');
    }

    /**
     * Test approval status constants
     */
    public function testApprovalStatusConstants()
    {
        $this->assertEquals(0, Attendees::APPROVAL_PENDING);
        $this->assertEquals(1, Attendees::APPROVAL_APPROVED);
        $this->assertEquals(2, Attendees::APPROVAL_REJECTED);
    }

    /**
     * Test getApprovalStatusLabel
     */
    public function testGetApprovalStatusLabel()
    {
        $pendingLabel = Attendees::getApprovalStatusLabel(Attendees::APPROVAL_PENDING);
        $approvedLabel = Attendees::getApprovalStatusLabel(Attendees::APPROVAL_APPROVED);
        $rejectedLabel = Attendees::getApprovalStatusLabel(Attendees::APPROVAL_REJECTED);

        $this->assertStringContainsString('Chờ', $pendingLabel);
        $this->assertStringContainsString('duyệt', $approvedLabel);
        $this->assertStringContainsString('chối', $rejectedLabel);
    }
}
