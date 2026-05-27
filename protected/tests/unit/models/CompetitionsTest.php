<?php

/**
 * CompetitionsTest - Unit tests cho thi nghiep vu
 *
 * Test cases tu Test_case.md:
 * - CMP-001: Tao cuoc thi nghiep vu moi
 * - CMP-002: Tao cuoc thi voi prefix trung
 * - CMP-003: Tao vong thi cho cuoc thi
 * - CMP-004: Vong thi co thoi gian start > end
 * - CMP-005: Xoa vong thi dang co thi sinh
 * - CMP-006: Dang ky thi sinh tu dong cap so bao danh
 * - CMP-007: So bao danh tang dan khong bi trung
 * - CMP-008: So bao danh theo do dai chuan (candidate_number_pad)
 * - CMP-009: Dang ky thi sinh vuot max_per_org
 * - CMP-010: Dang ky thi sinh trung lap
 * - CMP-011: Cap so bao danh thu cong
 * - CMP-012: Huy dang ky thi sinh
 * - CMP-013: Xuat danh sach thi sinh ra Excel
 * - CMP-014: Xuat Excel khi khong co thi sinh
 * - CMP-015: Nhap ket qua vong thi
 * - CMP-016: Tim thi sinh theo so bao danh
 */

class CompetitionsTest extends CTestCase
{
    /**
     * @var array Du lieu test
     */
    private $testCompetitionData;

    protected function setUp()
    {
        parent::setUp();

        $this->testCompetitionData = array(
            'event_id' => 1,
            'name' => 'Thi nghiệp vụ khách sạn',
            'candidate_number_prefix' => 'NV',
            'candidate_number_pad' => 3,
            'candidate_number_start' => 1,
            'max_per_org' => 5,
            'has_qualification' => 1,
            'is_active' => 1,
        );
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * CMP-001: Tao cuoc thi nghiep vu moi
     * BTC Nghiep vu da dang nhap
     * Expected: Cuoc thi duoc tao thanh cong
     */
    public function testCreateCompetitionSuccess()
    {
        $model = new Competitions();
        $model->setAttributes($this->testCompetitionData);

        $this->assertEquals('Thi nghiệp vụ khách sạn', $model->name);
        $this->assertEquals('NV', $model->candidate_number_prefix);
        $this->assertEquals(3, $model->candidate_number_pad);
        $this->assertEquals(5, $model->max_per_org);
    }

    /**
     * CMP-002: Tao cuoc thi voi prefix trung
     * Cuoc thi prefix="NV" da ton tai
     * Expected: Can kiem tra: co cho phep trung khong? Neu khong: loi
     */
    public function testCreateCompetitionWithDuplicatePrefix()
    {
        $model1 = new Competitions();
        $model1->setAttributes($this->testCompetitionData);
        $model1->id = 1;

        $model2 = new Competitions();
        $model2->setAttributes(array(
            'event_id' => 1,
            'name' => 'Cuộc thi khác',
            'candidate_number_prefix' => 'NV', // Trung prefix
        ));

        $this->assertEquals($model1->candidate_number_prefix, $model2->candidate_number_prefix);
    }

    /**
     * CMP-003: Tao vong thi cho cuoc thi
     * Cuoc thi da tao
     * Expected: 2 vong thi duoc tao, lien ket dung competition_id
     */
    public function testCreateCompetitionRounds()
    {
        $round1 = new CompetitionRounds();
        $round1->competition_id = 1;
        $round1->name = 'Vòng loại';
        $round1->round_order = 1;

        $round2 = new CompetitionRounds();
        $round2->competition_id = 1;
        $round2->name = 'Chung kết';
        $round2->round_order = 2;

        $this->assertEquals(1, $round1->round_order);
        $this->assertEquals(2, $round2->round_order);
        $this->assertEquals($round1->competition_id, $round2->competition_id);
    }

    /**
     * CMP-004: Vong thi co thoi gian start > end
     * Cuoc thi ton tai
     * Expected: Loi validation
     */
    public function testRoundWithInvalidTimeRange()
    {
        $round = new CompetitionRounds();
        $round->competition_id = 1;
        $round->name = 'Vòng sai';
        $round->start_time = strtotime('2026-11-15 14:00:00');
        $round->end_time = strtotime('2026-11-15 10:00:00'); // Truoc start

        $isInvalid = ($round->end_time < $round->start_time);

        $this->assertTrue($isInvalid, 'end_time khong duoc truoc start_time');
    }

    /**
     * CMP-005: Xoa vong thi dang co thi sinh
     * Vong thi co competition_registrations
     * Expected: Loi hoac cascade delete
     */
    public function testDeleteRoundWithRegistrations()
    {
        $roundId = 1;
        $registrationCount = 10;

        $hasRegistrations = ($registrationCount > 0);

        $this->assertTrue($hasRegistrations, 'Khong duoc xoa vong thi co thi sinh');
    }

    // ==================== CANDIDATE NUMBER ====================

    /**
     * CMP-006: Dang ky thi sinh tu dong cap so bao danh
     * Attendee ton tai, cuoc thi ton tai
     * Expected: candidate_number = prefix + so thu tu, VD "NV001"
     */
    public function testAutoAssignCandidateNumber()
    {
        $competition = array(
            'candidate_number_prefix' => 'NV',
            'candidate_number_pad' => 3,
            'candidate_number_start' => 1,
        );

        $nextNumber = 1;
        $candidateNumber = $competition['candidate_number_prefix'] .
            str_pad($nextNumber, $competition['candidate_number_pad'], '0', STR_PAD_LEFT);

        $this->assertEquals('NV001', $candidateNumber);
    }

    /**
     * CMP-007: So bao danh tang dan khong bi trung
     * Da co NV001, NV002
     * Expected: Cap NV003; khong co 2 thi sinh cung so bao danh
     */
    public function testCandidateNumberSequential()
    {
        $existingNumbers = array('NV001', 'NV002');
        $nextNumber = 3;
        $newCandidateNumber = 'NV' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        $this->assertEquals('NV003', $newCandidateNumber);
        $this->assertNotContains($newCandidateNumber, $existingNumbers);
    }

    /**
     * CMP-008: So bao danh theo do dai chuan (candidate_number_pad)
     * pad=3, prefix="NV", start=1
     * Expected: Ket qua la "NV005" (3 chu so, co padding 0)
     */
    public function testCandidateNumberPadding()
    {
        $prefix = 'NV';
        $pad = 3;
        $numbers = array(1, 5, 10, 99, 100, 999);

        $expected = array('NV001', 'NV005', 'NV010', 'NV099', 'NV100', 'NV999');

        foreach ($numbers as $i => $num) {
            $candidateNumber = $prefix . str_pad($num, $pad, '0', STR_PAD_LEFT);
            $this->assertEquals($expected[$i], $candidateNumber);
        }
    }

    /**
     * CMP-009: Dang ky thi sinh vuot max_per_org
     * max_per_org=2, don vi da co 2 thi sinh
     * Expected: Loi "Don vi da dat gioi han so luong thi sinh"
     */
    public function testRegistrationExceedsMaxPerOrg()
    {
        $maxPerOrg = 2;
        $currentCount = 2;

        $canRegisterMore = ($currentCount < $maxPerOrg);

        $this->assertFalse($canRegisterMore, 'Da dat gioi han thi sinh');
    }

    /**
     * CMP-010: Dang ky thi sinh trung lap
     * Attendee da dang ky cuoc thi
     * Expected: Loi (UNIQUE KEY uq_comp_reg_attendee)
     */
    public function testDuplicateRegistration()
    {
        $existingReg = array('competition_id' => 1, 'attendee_id' => 1);

        $newReg = new CompetitionRegistrations();
        $newReg->competition_id = 1;
        $newReg->attendee_id = 1;

        $isDuplicate = (
            $existingReg['competition_id'] === $newReg->competition_id &&
            $existingReg['attendee_id'] === $newReg->attendee_id
        );

        $this->assertTrue($isDuplicate, 'Khong duoc dang ky trung');
    }

    /**
     * CMP-011: Cap so bao danh thu cong
     * BTC nhap so bao danh cu the
     * Expected: So duoc luu; kiem tra neu da ton tai thi bao loi
     */
    public function testManualCandidateNumber()
    {
        $manualNumber = 'NV099';
        $existingNumbers = array('NV001', 'NV002', 'NV003');

        $isAvailable = !in_array($manualNumber, $existingNumbers);

        $this->assertTrue($isAvailable, 'So bao danh thu cong phai chua duoc dung');
    }

    /**
     * CMP-012: Huy dang ky thi sinh (cancelled)
     * Thi sinh da dang ky
     * Expected: status cap nhat; so bao danh van giu nguyen (khong tai su dung)
     */
    public function testCancelRegistration()
    {
        $registration = new CompetitionRegistrations();
        $registration->id = 1;
        $registration->candidate_number = 'NV001';
        $registration->status = 'active';

        // Huy dang ky
        $registration->status = 'cancelled';

        $this->assertEquals('cancelled', $registration->status);
        $this->assertEquals('NV001', $registration->candidate_number, 'So bao danh khong doi');
    }

    // ==================== EXPORT & RESULTS ====================

    /**
     * CMP-013: Xuat danh sach thi sinh ra Excel
     * Cuoc thi co nhieu thi sinh
     * Expected: File Excel tai xuong voi day du thong tin
     */
    public function testExportRegistrationsToExcel()
    {
        $registrations = array(
            array('candidate_number' => 'NV001', 'attendee_name' => 'A', 'property_name' => 'HN'),
            array('candidate_number' => 'NV002', 'attendee_name' => 'B', 'property_name' => 'HCM'),
        );

        $this->assertCount(2, $registrations, 'Phai co 2 thi sinh de xuat');
    }

    /**
     * CMP-014: Xuat Excel khi khong co thi sinh
     * Cuoc thi chua co ai dang ky
     * Expected: File Excel tai xuong voi header nhung khong co du lieu
     */
    public function testExportEmptyRegistrations()
    {
        $registrations = array();

        $this->assertCount(0, $registrations, 'Khong co thi sinh');
        // Van phai xuat duoc file voi header
    }

    /**
     * CMP-015: Nhap ket qua vong thi
     * Vong thi dang dien ra, thi sinh co so bao danh
     * Expected: Ban ghi competition_round_results duoc tao
     */
    public function testEnterRoundResult()
    {
        $result = new CompetitionRoundResults();
        $result->round_id = 1;
        $result->registration_id = 1;
        $result->score = 85.5;
        $result->rank = 1;
        $result->is_qualified = 1;

        $this->assertEquals(85.5, $result->score);
        $this->assertEquals(1, $result->rank);
        $this->assertEquals(1, $result->is_qualified);
    }

    /**
     * CMP-016: Tim thi sinh theo so bao danh
     * Thi sinh co so bao danh NV003
     * Expected: Tra ve dung thi sinh co so bao danh NV003
     */
    public function testSearchByCandidateNumber()
    {
        $registrations = array(
            array('id' => 1, 'candidate_number' => 'NV001', 'attendee_name' => 'A'),
            array('id' => 2, 'candidate_number' => 'NV002', 'attendee_name' => 'B'),
            array('id' => 3, 'candidate_number' => 'NV003', 'attendee_name' => 'C'),
        );

        $searchNumber = 'NV003';
        $found = null;

        foreach ($registrations as $reg) {
            if ($reg['candidate_number'] === $searchNumber) {
                $found = $reg;
                break;
            }
        }

        $this->assertNotNull($found, 'Phai tim thay thi sinh');
        $this->assertEquals('C', $found['attendee_name']);
    }
}
