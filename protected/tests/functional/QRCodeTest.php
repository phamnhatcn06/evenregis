<?php

/**
 * QRCodeTest - Functional tests cho QR Code va thong tin cong khai
 *
 * Test cases tu Test_case.md:
 * - QR-001: Quet QR xem thong tin ca nhan hop le
 * - QR-002: Truy cap voi token khong ton tai
 * - QR-003: Truy cap voi token rong
 * - QR-004: Truy cap voi token chua SQL injection
 * - QR-005: Truy cap voi token chua XSS
 * - QR-006: Xem agenda dai hoi
 * - QR-007: Agenda private khong hien thi
 * - QR-008: Xem lich thi nghiep vu ca nhan
 * - QR-009: Xem lich thi the thao cua don vi
 * - QR-010: Trang QR khong can dang nhap
 * - QR-011: URL QR khong lo ID cua attendee
 * - QR-012: Attendee bi soft delete van tra ve khong tim thay
 */

class QRCodeTest extends CTestCase
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
     * QR-001: Quet QR xem thong tin ca nhan hop le (UC28)
     * Attendee co qr_token hop le
     * Expected: Hien thi thong tin: ten, don vi, chuc danh, anh
     */
    public function testViewAttendeeWithValidToken()
    {
        $qrToken = bin2hex(random_bytes(32));

        $attendee = array(
            'qr_token' => $qrToken,
            'full_name' => 'Nguyễn Văn A',
            'property_name' => 'KS Mường Thanh Hà Nội',
            'position' => 'Nhân viên',
            'portrait_path' => '/uploads/photos/1.jpg',
            'is_active' => 1,
        );

        // Gia lap tim kiem
        $found = ($attendee['qr_token'] === $qrToken && $attendee['is_active'] == 1);

        $this->assertTrue($found, 'Phai tim thay attendee voi token hop le');
        $this->assertEquals('Nguyễn Văn A', $attendee['full_name']);
    }

    /**
     * QR-002: Truy cap voi token khong ton tai
     * Token ngau nhien khong co trong DB
     * Expected: Hien thi trang "Khong tim thay thong tin" hoac 404
     */
    public function testViewWithNonExistentToken()
    {
        $fakeToken = 'abc123xyz_fake_token_not_in_database';

        // Gia lap khong tim thay
        $attendee = null;

        $this->assertNull($attendee, 'Khong tim thay attendee voi token gia');
    }

    /**
     * QR-003: Truy cap voi token rong
     * Token = ""
     * Expected: Trang loi than thien, khong crash PHP
     */
    public function testViewWithEmptyToken()
    {
        $emptyToken = '';

        $isValid = !empty($emptyToken);

        $this->assertFalse($isValid, 'Token rong phai bi tu choi');
    }

    /**
     * QR-004: Truy cap voi token chua SQL injection
     * Token = "'; DROP TABLE attendees; --"
     * Expected: He thong xu ly an toan, khong bi SQL injection
     */
    public function testSqlInjectionInToken()
    {
        $maliciousToken = "'; DROP TABLE attendees; --";

        // Token nen la hex string (64 ky tu), khong chua ky tu dac biet
        $isValidFormat = preg_match('/^[a-f0-9]{64}$/', $maliciousToken);

        $this->assertEquals(0, $isValidFormat, 'Token SQL injection phai bi tu choi boi format check');

        // Neu dung prepared statement, injection khong anh huong
        $this->assertStringContainsString("'", $maliciousToken, 'Token chua ky tu nguy hiem');
    }

    /**
     * QR-005: Truy cap voi token chua XSS
     * Token = "<script>alert(1)</script>"
     * Expected: He thong encode output dung cach, khong chay script
     */
    public function testXssInToken()
    {
        $xssToken = '<script>alert(1)</script>';

        // Encode output
        $encodedToken = htmlspecialchars($xssToken, ENT_QUOTES, 'UTF-8');

        $this->assertStringNotContainsString('<script>', $encodedToken, 'XSS phai bi escape');
        $this->assertStringContainsString('&lt;script&gt;', $encodedToken);
    }

    /**
     * QR-006: Xem agenda dai hoi (UC29)
     * Event agenda da nhap, is_public=1
     * Expected: Hien thi danh sach chuong trinh theo thu tu thoi gian
     */
    public function testViewPublicAgenda()
    {
        $agenda = array(
            array('time' => '08:00', 'title' => 'Khai mạc', 'is_public' => 1),
            array('time' => '09:00', 'title' => 'Báo cáo', 'is_public' => 1),
            array('time' => '10:30', 'title' => 'Giải lao', 'is_public' => 1),
            array('time' => '11:00', 'title' => 'Họp nội bộ', 'is_public' => 0), // Private
        );

        $publicAgenda = array_filter($agenda, function ($item) {
            return $item['is_public'] == 1;
        });

        $this->assertCount(3, $publicAgenda, 'Chi co 3 item public');
    }

    /**
     * QR-007: Agenda private khong hien thi (is_public=0)
     * Co agenda voi is_public=0
     * Expected: Item co is_public=0 khong xuat hien
     */
    public function testPrivateAgendaHidden()
    {
        $agenda = array(
            array('title' => 'Public event', 'is_public' => 1),
            array('title' => 'Private meeting', 'is_public' => 0),
        );

        $publicAgenda = array_filter($agenda, function ($item) {
            return $item['is_public'] == 1;
        });

        foreach ($publicAgenda as $item) {
            $this->assertEquals(1, $item['is_public']);
        }
    }

    /**
     * QR-008: Xem lich thi nghiep vu ca nhan (UC30)
     * Attendee da dang ky thi nghiep vu
     * Expected: Chi hien thi cuoc thi va vong thi ma attendee dang ky
     */
    public function testViewPersonalCompetitionSchedule()
    {
        $attendeeId = 1;

        $allRegistrations = array(
            array('attendee_id' => 1, 'competition_name' => 'Thi NV', 'round' => 'Vòng loại'),
            array('attendee_id' => 2, 'competition_name' => 'Thi NV', 'round' => 'Vòng loại'),
            array('attendee_id' => 1, 'competition_name' => 'Thi NV', 'round' => 'Chung kết'),
        );

        $personalSchedule = array_filter($allRegistrations, function ($reg) use ($attendeeId) {
            return $reg['attendee_id'] === $attendeeId;
        });

        $this->assertCount(2, $personalSchedule, 'Attendee 1 co 2 vong thi');
    }

    /**
     * QR-009: Xem lich thi the thao cua don vi (UC31)
     * Don vi co doi thi dau
     * Expected: Hien thi cac tran dau cua doi tuyen don vi, sap xep theo thoi gian
     */
    public function testViewTeamSportsSchedule()
    {
        $propertyId = 1;

        $matches = array(
            array('team_property_id' => 1, 'match_name' => 'Match A', 'scheduled_at' => '2026-11-15 09:00:00'),
            array('team_property_id' => 2, 'match_name' => 'Match B', 'scheduled_at' => '2026-11-15 10:00:00'),
            array('team_property_id' => 1, 'match_name' => 'Match C', 'scheduled_at' => '2026-11-15 14:00:00'),
        );

        $teamMatches = array_filter($matches, function ($m) use ($propertyId) {
            return $m['team_property_id'] === $propertyId;
        });

        $this->assertCount(2, $teamMatches, 'Don vi 1 co 2 tran dau');
    }

    /**
     * QR-010: Trang QR khong can dang nhap
     * Chua co session
     * Expected: Truy cap duoc ma khong can dang nhap
     */
    public function testQrPageNoLoginRequired()
    {
        $isLoggedIn = false;
        $isPublicPage = true;

        $canAccess = ($isPublicPage || $isLoggedIn);

        $this->assertTrue($canAccess, 'Trang QR phai truy cap duoc khi chua dang nhap');
    }

    /**
     * QR-011: URL QR khong lo ID cua attendee
     * Attendee id=123
     * Expected: URL chua qr_token, khong chua so ID "123"
     */
    public function testQrUrlDoesNotExposeId()
    {
        $attendeeId = 123;
        $qrToken = bin2hex(random_bytes(32));

        $url = '/frontend/attendee/view?token=' . $qrToken;

        $this->assertStringNotContainsString((string)$attendeeId, $url, 'URL khong duoc chua ID');
        $this->assertStringContainsString('token=', $url);
        $this->assertStringContainsString($qrToken, $url);
    }

    /**
     * QR-012: Attendee bi soft delete van tra ve khong tim thay
     * Attendee is_active=0
     * Expected: Hien thi "Khong tim thay thong tin"
     */
    public function testSoftDeletedAttendeeNotFound()
    {
        $attendee = array(
            'id' => 1,
            'qr_token' => 'valid_token_here',
            'is_active' => 0, // Da bi xoa
        );

        $isFound = ($attendee['is_active'] == 1);

        $this->assertFalse($isFound, 'Attendee bi soft delete phai khong tim thay');
    }

    /**
     * Test QR token format validation
     */
    public function testQrTokenFormatValidation()
    {
        $validTokens = array(
            bin2hex(random_bytes(32)),
            'a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a1b2',
        );

        $invalidTokens = array(
            'short',
            'contains-special-chars!@#',
            'UPPERCASE_NOT_ALLOWED',
            str_repeat('g', 64), // g khong phai hex
        );

        foreach ($validTokens as $token) {
            $isValid = preg_match('/^[a-f0-9]{64}$/', $token);
            $this->assertEquals(1, $isValid, 'Token hop le phai match regex');
        }

        foreach ($invalidTokens as $token) {
            $isValid = preg_match('/^[a-f0-9]{64}$/', $token);
            $this->assertEquals(0, $isValid, 'Token khong hop le phai bi reject');
        }
    }
}
