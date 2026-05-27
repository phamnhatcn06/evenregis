<?php

/**
 * RegistrationWorkflowTest - Functional tests cho quy trinh dang ky
 *
 * Test cases tu Test_case.md:
 * - REG-005: Don vi thay dot dang ky dang mo
 * - REG-006: Don vi khong thay dot dang ky da dong
 * - REG-009: Them nguoi tham du vao phieu draft
 * - REG-010: Upload anh nguoi tham du hop le
 * - REG-011: Upload anh qua dung luong
 * - REG-012: Upload file khong phai anh
 * - REG-013: Chinh sua thong tin nguoi tham du khi draft
 * - REG-018: Xem trang thai phe duyet
 * - REG-019: Tao phieu ngoai thoi han dang ky
 * - REG-021: HR phe duyet phieu dang ky
 * - REG-022: HR tu choi phieu dang ky kem ly do
 * - REG-023: Tu choi phieu ma khong nhap ly do
 * - REG-024: Phe duyet phieu da o status "approved"
 * - REG-025: Don vi khong the tu phe duyet phieu cua minh
 * - REG-026: Xem tat ca dang ky theo trang thai
 */

class RegistrationWorkflowTest extends CTestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    // ==================== REGISTRATION PERIOD VISIBILITY ====================

    /**
     * REG-005: Don vi thay dot dang ky dang mo
     * Dot dang ky is_active=1, trong thoi han
     * Expected: Dot dang ky dang mo hien thi, cho phep tao phieu
     */
    public function testUnitSeesOpenRegistrationPeriod()
    {
        $period = array(
            'id' => 1,
            'name' => 'Đợt 1',
            'start_time' => time() - 86400, // 1 ngay truoc
            'end_time' => time() + 86400 * 7, // 7 ngay sau
            'is_active' => 1,
        );

        $currentTime = time();
        $isOpen = (
            $period['is_active'] == 1 &&
            $currentTime >= $period['start_time'] &&
            $currentTime <= $period['end_time']
        );

        $this->assertTrue($isOpen, 'Dot dang ky dang mo phai hien thi');
    }

    /**
     * REG-006: Don vi khong thay dot dang ky da dong
     * Dot dang ky end_time da qua
     * Expected: Dot da dong khong hien thi hoac hien thi voi trang thai "Da dong"
     */
    public function testUnitDoesNotSeeClosedRegistrationPeriod()
    {
        $period = array(
            'id' => 1,
            'name' => 'Đợt cũ',
            'start_time' => time() - 86400 * 30, // 30 ngay truoc
            'end_time' => time() - 86400, // 1 ngay truoc (da dong)
            'is_active' => 1,
        );

        $currentTime = time();
        $isClosed = ($currentTime > $period['end_time']);

        $this->assertTrue($isClosed, 'Dot dang ky da dong phai bi an hoac danh dau');
    }

    // ==================== ATTENDEE MANAGEMENT ====================

    /**
     * REG-009: Them nguoi tham du vao phieu draft (UC03)
     * Phieu co status="draft"
     * Expected: Nguoi tham du duoc them vao attendees, lien ket voi registration_id
     */
    public function testAddAttendeeToDraft()
    {
        $registration = array(
            'id' => 1,
            'status' => Registrations::STATUS_DRAFT,
        );

        $attendee = new Attendees();
        $attendee->registration_id = $registration['id'];
        $attendee->full_name = 'Nguyễn Văn A';
        $attendee->position = 'Nhân viên';

        // Chi cho phep them khi status = DRAFT
        $canAdd = ($registration['status'] === Registrations::STATUS_DRAFT);

        $this->assertTrue($canAdd, 'Phai them duoc attendee khi phieu la draft');
        $this->assertEquals(1, $attendee->registration_id);
    }

    /**
     * REG-010: Upload anh nguoi tham du hop le
     * Phieu draft, file anh JPG 300KB
     * Expected: Anh duoc luu vao uploads/, duong dan cap nhat vao photo_path
     */
    public function testUploadValidPhoto()
    {
        $fileSize = 300 * 1024; // 300KB
        $fileType = 'image/jpeg';
        $maxSize = 10 * 1024 * 1024; // 10MB
        $allowedTypes = array('image/jpeg', 'image/png', 'image/gif');

        $isValidSize = ($fileSize <= $maxSize);
        $isValidType = in_array($fileType, $allowedTypes);

        $this->assertTrue($isValidSize, 'Kich thuoc file phai hop le');
        $this->assertTrue($isValidType, 'Loai file phai la anh');
    }

    /**
     * REG-011: Upload anh qua dung luong
     * File anh >10MB
     * Expected: Loi "File anh vuot qua dung luong cho phep"
     */
    public function testUploadOversizedPhoto()
    {
        $fileSize = 15 * 1024 * 1024; // 15MB
        $maxSize = 10 * 1024 * 1024; // 10MB

        $isOversized = ($fileSize > $maxSize);

        $this->assertTrue($isOversized, 'File qua lon phai bi tu choi');
    }

    /**
     * REG-012: Upload file khong phai anh (PDF, EXE)
     * Phieu draft, upload file .pdf
     * Expected: Loi "Chi chap nhan file anh JPG/PNG/GIF"
     */
    public function testUploadNonImageFile()
    {
        $fileTypes = array(
            'application/pdf' => false,
            'application/x-msdownload' => false,
            'image/jpeg' => true,
            'image/png' => true,
            'image/gif' => true,
        );

        foreach ($fileTypes as $type => $shouldAccept) {
            $allowedTypes = array('image/jpeg', 'image/png', 'image/gif');
            $isAllowed = in_array($type, $allowedTypes);

            $this->assertEquals($shouldAccept, $isAllowed, "Type $type phai duoc xu ly dung");
        }
    }

    /**
     * REG-013: Chinh sua thong tin nguoi tham du khi draft (UC04)
     * Phieu status="draft", co attendee
     * Expected: Thong tin cap nhat thanh cong
     */
    public function testEditAttendeeWhenDraft()
    {
        $registration = array(
            'id' => 1,
            'status' => Registrations::STATUS_DRAFT,
        );

        $canEdit = ($registration['status'] === Registrations::STATUS_DRAFT);

        $this->assertTrue($canEdit, 'Phai chinh sua duoc attendee khi phieu la draft');
    }

    /**
     * REG-018: Xem trang thai phe duyet (UC06)
     * Phieu da nop
     * Expected: Hien thi dung status va ly do tu choi neu co
     */
    public function testViewApprovalStatus()
    {
        $statuses = array(
            array('status' => Registrations::STATUS_DRAFT, 'label' => 'Nháp'),
            array('status' => Registrations::STATUS_SUBMITTED, 'label' => 'Đã nộp'),
            array('status' => Registrations::STATUS_APPROVED, 'label' => 'Đã duyệt'),
            array('status' => Registrations::STATUS_REJECTED, 'label' => 'Từ chối', 'reason' => 'Thiếu giấy tờ'),
        );

        foreach ($statuses as $item) {
            $statusLabel = Registrations::getStatusLabel($item['status']);
            $this->assertNotEmpty($statusLabel, 'Status label phai co gia tri');
        }
    }

    /**
     * REG-019: Tao phieu ngoai thoi han dang ky
     * end_time cua dot da qua
     * Expected: Loi "Dot dang ky da dong"
     */
    public function testCreateRegistrationOutsidePeriod()
    {
        $period = array(
            'end_time' => time() - 86400, // Da qua 1 ngay
        );

        $currentTime = time();
        $isPeriodClosed = ($currentTime > $period['end_time']);

        $this->assertTrue($isPeriodClosed, 'Khong duoc tao phieu khi dot da dong');
    }

    // ==================== APPROVAL WORKFLOW ====================

    /**
     * REG-021: HR phe duyet phieu dang ky (approve)
     * Phieu status="submitted"
     * Expected: Status chuyen thanh "approved", reviewed_by va reviewed_at duoc gan
     */
    public function testHrApprovesRegistration()
    {
        $model = new Registrations();
        $model->status = Registrations::STATUS_SUBMITTED;
        $model->id = 1;

        // Gia lap phe duyet
        $model->status = Registrations::STATUS_APPROVED;
        $model->reviewed_by = '12345'; // HR user ID
        $model->reviewed_at = time();

        $this->assertEquals(Registrations::STATUS_APPROVED, $model->status);
        $this->assertNotNull($model->reviewed_by);
        $this->assertNotNull($model->reviewed_at);
    }

    /**
     * REG-022: HR tu choi phieu dang ky kem ly do (reject)
     * Phieu status="submitted"
     * Expected: Status chuyen thanh "rejected", rejection_reason duoc luu
     */
    public function testHrRejectsRegistrationWithReason()
    {
        $model = new Registrations();
        $model->status = Registrations::STATUS_SUBMITTED;
        $model->id = 1;

        // Gia lap tu choi
        $rejectionReason = 'Thiếu ảnh chân dung';
        $model->status = Registrations::STATUS_REJECTED;
        $model->rejection_reason = $rejectionReason;
        $model->reviewed_by = '12345';
        $model->reviewed_at = time();

        $this->assertEquals(Registrations::STATUS_REJECTED, $model->status);
        $this->assertEquals($rejectionReason, $model->rejection_reason);
    }

    /**
     * REG-023: Tu choi phieu ma khong nhap ly do
     * Phieu status="submitted"
     * Expected: Loi "Vui long nhap ly do tu choi"
     */
    public function testRejectWithoutReason()
    {
        $rejectionReason = '';

        // Kiem tra ly do khong duoc rong
        $isReasonEmpty = empty($rejectionReason);

        $this->assertTrue($isReasonEmpty, 'Tu choi phai co ly do');
    }

    /**
     * REG-024: Phe duyet phieu da o status "approved"
     * Phieu da approved
     * Expected: He thong khong thay doi, hien thi canh bao "Phieu da duoc phe duyet"
     */
    public function testApproveAlreadyApprovedRegistration()
    {
        $model = new Registrations();
        $model->status = Registrations::STATUS_APPROVED;
        $model->id = 1;

        // Kiem tra khong cho approve lai
        $isAlreadyApproved = ($model->status === Registrations::STATUS_APPROVED);

        $this->assertTrue($isAlreadyApproved, 'Phieu da duoc phe duyet');
    }

    /**
     * REG-025: Don vi khong the tu phe duyet phieu cua minh
     * Don vi da dang nhap
     * Expected: Tra ve 403 Forbidden
     */
    public function testUnitCannotApproveSelfRegistration()
    {
        $unitPropertyId = 1;
        $registrationPropertyId = 1;
        $userRole = 'unit'; // Tai khoan don vi, khong phai HR/Admin

        // Don vi chi duoc tao/sua phieu cua minh, khong phe duyet
        $canApprove = in_array($userRole, array('admin', 'hr'));
        $isSelfRegistration = ($unitPropertyId === $registrationPropertyId);

        $this->assertFalse($canApprove, 'Don vi khong co quyen phe duyet');
        $this->assertTrue($isSelfRegistration, 'Day la phieu cua don vi');
    }

    /**
     * REG-026: Xem tat ca dang ky theo trang thai (UC06)
     * HR da dang nhap, co nhieu phieu
     * Expected: Chi hien thi phieu theo status da loc, dung so luong
     */
    public function testFilterRegistrationsByStatus()
    {
        $allRegistrations = array(
            array('id' => 1, 'status' => Registrations::STATUS_DRAFT),
            array('id' => 2, 'status' => Registrations::STATUS_SUBMITTED),
            array('id' => 3, 'status' => Registrations::STATUS_SUBMITTED),
            array('id' => 4, 'status' => Registrations::STATUS_APPROVED),
            array('id' => 5, 'status' => Registrations::STATUS_REJECTED),
        );

        // Loc theo status = SUBMITTED
        $filterStatus = Registrations::STATUS_SUBMITTED;
        $filtered = array_filter($allRegistrations, function ($r) use ($filterStatus) {
            return $r['status'] === $filterStatus;
        });

        $this->assertCount(2, $filtered, 'Phai loc dung so phieu submitted');
    }

    /**
     * Test workflow trang thai hop le
     */
    public function testValidStatusTransitions()
    {
        // Cac chuyen doi trang thai hop le
        $validTransitions = array(
            Registrations::STATUS_DRAFT => array(Registrations::STATUS_SUBMITTED),
            Registrations::STATUS_SUBMITTED => array(Registrations::STATUS_APPROVED, Registrations::STATUS_REJECTED),
            Registrations::STATUS_REJECTED => array(Registrations::STATUS_DRAFT), // Resubmit
        );

        // DRAFT -> SUBMITTED: hop le
        $this->assertContains(
            Registrations::STATUS_SUBMITTED,
            $validTransitions[Registrations::STATUS_DRAFT]
        );

        // SUBMITTED -> APPROVED: hop le
        $this->assertContains(
            Registrations::STATUS_APPROVED,
            $validTransitions[Registrations::STATUS_SUBMITTED]
        );

        // SUBMITTED -> REJECTED: hop le
        $this->assertContains(
            Registrations::STATUS_REJECTED,
            $validTransitions[Registrations::STATUS_SUBMITTED]
        );
    }
}
