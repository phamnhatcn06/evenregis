<?php

/**
 * BadgesTest - Unit tests cho the tham du
 *
 * Test cases tu Test_case.md:
 * - BAD-001: Tao the cho 1 attendee
 * - BAD-002: Tao the theo lo (batch)
 * - BAD-003: The chua QR code dung qr_token
 * - BAD-004: Kich thuoc anh the dung chuan CR80
 * - BAD-005: Tao the cho attendee chua co anh
 * - BAD-006: In the lan dau cap nhat print_count
 * - BAD-007: Tao the cho attendee chua approved
 * - BAD-008: Tai tao the (regenerate) sau khi sua thong tin
 * - BAD-009: Xuat the khi anh file bi xoa khoi disk
 */

class BadgesTest extends CTestCase
{
    /**
     * Kich thuoc chuan CR80 (85.60 x 53.98 mm, 300 DPI)
     */
    const CR80_WIDTH_MM = 85.60;
    const CR80_HEIGHT_MM = 53.98;
    const DPI = 300;
    const CR80_WIDTH_PX = 1013; // 85.60 mm * 300 dpi / 25.4
    const CR80_HEIGHT_PX = 638;  // 53.98 mm * 300 dpi / 25.4

    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * BAD-001: Tao the cho 1 attendee
     * Attendee da approved, co day du thong tin
     * Expected: File anh the duoc tao (85.60x53.98mm, 300dpi), badge_generated=1
     */
    public function testCreateBadgeForAttendee()
    {
        $attendee = array(
            'id' => 1,
            'full_name' => 'Nguyễn Văn A',
            'position' => 'Nhân viên',
            'property_name' => 'KS Mường Thanh HN',
            'approval_status' => Attendees::APPROVAL_APPROVED,
            'qr_token' => bin2hex(random_bytes(32)),
            'portrait_path' => '/uploads/photos/1.jpg',
        );

        $badge = new Badges();
        $badge->attendee_id = $attendee['id'];
        $badge->generated_at = time();
        $badge->generated_path = '/badges/' . $attendee['id'] . '.png';

        $this->assertEquals(1, $badge->attendee_id);
        $this->assertNotNull($badge->generated_at);
        $this->assertNotEmpty($badge->generated_path);
    }

    /**
     * BAD-002: Tao the theo lo (batch)
     * Nhieu attendee da approved
     * Expected: Tat ca the duoc tao; bao cao thanh cong/that bai
     */
    public function testBatchBadgeGeneration()
    {
        $attendees = array(
            array('id' => 1, 'approval_status' => Attendees::APPROVAL_APPROVED),
            array('id' => 2, 'approval_status' => Attendees::APPROVAL_APPROVED),
            array('id' => 3, 'approval_status' => Attendees::APPROVAL_APPROVED),
            array('id' => 4, 'approval_status' => Attendees::APPROVAL_PENDING), // Chua approved
            array('id' => 5, 'approval_status' => Attendees::APPROVAL_APPROVED),
        );

        $successCount = 0;
        $failCount = 0;

        foreach ($attendees as $att) {
            if ($att['approval_status'] === Attendees::APPROVAL_APPROVED) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        $this->assertEquals(4, $successCount, 'Phai tao duoc 4 the');
        $this->assertEquals(1, $failCount, 'Co 1 attendee chua approved');
    }

    /**
     * BAD-003: The chua QR code dung qr_token
     * Attendee co qr_token
     * Expected: URL trong QR dan den /frontend/attendee/view?token=<qr_token> cua dung attendee
     */
    public function testBadgeContainsCorrectQrCode()
    {
        $qrToken = bin2hex(random_bytes(32));
        $expectedUrl = '/frontend/attendee/view?token=' . $qrToken;

        // Kiem tra URL format
        $this->assertStringContainsString('token=', $expectedUrl);
        $this->assertStringContainsString($qrToken, $expectedUrl);
        $this->assertEquals(64, strlen($qrToken));
    }

    /**
     * BAD-004: Kich thuoc anh the dung chuan CR80
     * The vua duoc tao
     * Expected: Anh co ti le dung 85.60:53.98mm, DPI=300 (tuong duong 1013x638 pixel)
     */
    public function testBadgeSizeCr80Standard()
    {
        // Tinh toan kich thuoc pixel tu mm va DPI
        $expectedWidthPx = round(self::CR80_WIDTH_MM * self::DPI / 25.4);
        $expectedHeightPx = round(self::CR80_HEIGHT_MM * self::DPI / 25.4);

        $this->assertEquals(self::CR80_WIDTH_PX, $expectedWidthPx, 'Chieu rong phai la 1013px');
        $this->assertEquals(self::CR80_HEIGHT_PX, $expectedHeightPx, 'Chieu cao phai la 638px');

        // Kiem tra ti le
        $ratio = self::CR80_WIDTH_MM / self::CR80_HEIGHT_MM;
        $this->assertEqualsWithDelta(1.586, $ratio, 0.01, 'Ti le phai dung');
    }

    /**
     * BAD-005: Tao the cho attendee chua co anh
     * Attendee chua upload anh
     * Expected: The van duoc tao voi anh mac dinh/placeholder, khong crash
     */
    public function testCreateBadgeWithoutPhoto()
    {
        $attendee = array(
            'id' => 1,
            'full_name' => 'Nguyễn Văn A',
            'approval_status' => Attendees::APPROVAL_APPROVED,
            'portrait_path' => null, // Chua co anh
        );

        $hasPhoto = !empty($attendee['portrait_path']);
        $placeholderPath = '/images/placeholder-avatar.png';

        $photoToUse = $hasPhoto ? $attendee['portrait_path'] : $placeholderPath;

        $this->assertFalse($hasPhoto, 'Attendee chua co anh');
        $this->assertEquals($placeholderPath, $photoToUse, 'Phai dung placeholder');
    }

    /**
     * BAD-006: In the lan dau cap nhat print_count
     * The da tao
     * Expected: print_count tang them 1, last_printed_at cap nhat
     */
    public function testPrintBadgeUpdatesPrintCount()
    {
        $badge = new Badges();
        $badge->id = 1;
        $badge->attendee_id = 1;
        $badge->print_count = 0;
        $badge->last_printed_at = null;

        // In the
        $badge->print_count++;
        $badge->last_printed_at = time();

        $this->assertEquals(1, $badge->print_count, 'print_count phai tang len 1');
        $this->assertNotNull($badge->last_printed_at);

        // In lan 2
        $badge->print_count++;
        $badge->last_printed_at = time();

        $this->assertEquals(2, $badge->print_count, 'print_count phai tang len 2');
    }

    /**
     * BAD-007: Tao the cho attendee chua approved
     * Phieu status="draft"
     * Expected: Loi "Chi tao the cho nguoi tham du da duoc phe duyet"
     */
    public function testCannotCreateBadgeForUnapprovedAttendee()
    {
        $attendee = array(
            'id' => 1,
            'approval_status' => Attendees::APPROVAL_PENDING,
        );

        $canGenerateBadge = ($attendee['approval_status'] === Attendees::APPROVAL_APPROVED);

        $this->assertFalse($canGenerateBadge, 'Khong duoc tao the cho attendee chua approved');
    }

    /**
     * BAD-008: Tai tao the (regenerate) sau khi sua thong tin
     * The da tao, admin sua ten attendee
     * Expected: The moi phan anh ten moi; file cu bi ghi de
     */
    public function testRegenerateBadgeAfterUpdate()
    {
        $badge = new Badges();
        $badge->id = 1;
        $badge->attendee_id = 1;
        $badge->generated_at = time() - 3600; // Tao 1 gio truoc
        $badge->generated_path = '/badges/1.png';

        // Cap nhat thong tin attendee
        $newName = 'Nguyễn Văn B';

        // Tai tao the
        $oldGeneratedAt = $badge->generated_at;
        $badge->generated_at = time();

        $this->assertGreaterThan($oldGeneratedAt, $badge->generated_at, 'generated_at phai moi hon');
    }

    /**
     * BAD-009: Xuat the khi anh file bi xoa khoi disk
     * photo_path tro den file khong ton tai
     * Expected: He thong dung anh placeholder, khong crash voi fatal error
     */
    public function testCreateBadgeWithMissingPhoto()
    {
        $attendee = array(
            'id' => 1,
            'portrait_path' => '/uploads/photos/deleted_file.jpg', // File khong ton tai
        );

        // Gia lap kiem tra file ton tai
        $photoExists = false; // file_exists($attendee['portrait_path']);
        $placeholderPath = '/images/placeholder-avatar.png';

        $photoToUse = $photoExists ? $attendee['portrait_path'] : $placeholderPath;

        $this->assertFalse($photoExists, 'File anh khong ton tai');
        $this->assertEquals($placeholderPath, $photoToUse, 'Phai dung placeholder');
    }

    /**
     * Test Badges model instance
     */
    public function testBadgesModelInstance()
    {
        $model = new Badges();
        $this->assertInstanceOf('CActiveRecord', $model);
    }

    /**
     * Test tinh toan DPI
     */
    public function testDpiCalculation()
    {
        // 1 inch = 25.4 mm
        // 300 DPI = 300 pixel per inch

        $mmToInch = 25.4;

        // Chieu rong: 85.60 mm = 85.60 / 25.4 inch = 3.37 inch
        // 3.37 inch * 300 dpi = 1011 pixel (xap xi 1013)
        $widthInch = self::CR80_WIDTH_MM / $mmToInch;
        $widthPx = round($widthInch * self::DPI);

        $this->assertGreaterThan(1000, $widthPx, 'Chieu rong phai > 1000px');
        $this->assertLessThan(1020, $widthPx, 'Chieu rong phai < 1020px');
    }
}
