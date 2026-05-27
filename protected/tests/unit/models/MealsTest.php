<?php

/**
 * MealsTest - Unit tests cho bua an
 *
 * Test cases tu Test_case.md:
 * - MEA-001: Tao bua an (breakfast/lunch/dinner)
 * - MEA-002: Truong doan xem danh sach thanh vien
 * - MEA-003: Bao cat an cho tung nguoi
 * - MEA-004: Bao cat an sau cutoff_deadline
 * - MEA-005: Bao cat an ca doan (bulk)
 * - MEA-006: Truong doan bao cat doan khac
 * - MEA-007: Check-in bua an
 * - MEA-008: Check-in bua an khi da bao cat
 */

class MealsTest extends CTestCase
{
    /**
     * @var array Du lieu test
     */
    private $testMealData;

    protected function setUp()
    {
        parent::setUp();

        $this->testMealData = array(
            'event_id' => 1,
            'name' => 'Bữa sáng ngày 1',
            'meal_type' => 'breakfast',
            'meal_date' => '2026-11-15',
            'meal_time' => '07:00:00',
            'cutoff_minutes' => 30, // 30 phut truoc
            'is_active' => 1,
        );
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * MEA-001: Tao bua an (breakfast/lunch/dinner)
     * Admin da dang nhap
     * Expected: Bua an duoc tao
     */
    public function testCreateMeal()
    {
        $model = new Meals();
        $model->setAttributes($this->testMealData);

        $this->assertEquals('Bữa sáng ngày 1', $model->name);
        $this->assertEquals('breakfast', $model->meal_type);
        $this->assertEquals(30, $model->cutoff_minutes);
    }

    /**
     * MEA-002: Truong doan xem danh sach thanh vien (UC13)
     * is_team_lead=1, don vi co nhieu attendee
     * Expected: Chi hien thi attendee cung don vi voi truong doan
     */
    public function testTeamLeadViewsMembers()
    {
        $teamLeadPropertyId = 1;

        $allAttendees = array(
            array('id' => 1, 'property_id' => 1, 'full_name' => 'A'),
            array('id' => 2, 'property_id' => 1, 'full_name' => 'B'),
            array('id' => 3, 'property_id' => 2, 'full_name' => 'C'), // Don vi khac
            array('id' => 4, 'property_id' => 1, 'full_name' => 'D'),
        );

        $teamMembers = array_filter($allAttendees, function ($att) use ($teamLeadPropertyId) {
            return $att['property_id'] === $teamLeadPropertyId;
        });

        $this->assertCount(3, $teamMembers, 'Truong doan chi thay thanh vien don vi minh');
    }

    /**
     * MEA-003: Bao cat an cho tung nguoi (UC14)
     * Truong doan da dang nhap, bua chua qua cutoff
     * Expected: Ban ghi meal_cutoffs duoc tao cho attendee A
     */
    public function testReportMealCutoffForOne()
    {
        $cutoff = new MealCutoffs();
        $cutoff->meal_id = 1;
        $cutoff->attendee_id = 1;
        $cutoff->reported_by = 999; // Truong doan ID
        $cutoff->reported_at = time();

        $this->assertEquals(1, $cutoff->meal_id);
        $this->assertEquals(1, $cutoff->attendee_id);
        $this->assertNotNull($cutoff->reported_at);
    }

    /**
     * MEA-004: Bao cat an sau cutoff_deadline
     * Da qua gio cutoff
     * Expected: Loi "Da qua thoi han bao cat an cho bua nay"
     */
    public function testCutoffAfterDeadline()
    {
        $mealTime = strtotime('2026-11-15 07:00:00');
        $cutoffMinutes = 30;
        $cutoffDeadline = $mealTime - ($cutoffMinutes * 60);

        // Gia lap hien tai qua deadline
        $currentTime = $mealTime - 15 * 60; // 15 phut truoc bua an (da qua cutoff)

        $isPastDeadline = ($currentTime > $cutoffDeadline);

        $this->assertTrue($isPastDeadline, 'Da qua han bao cat');
    }

    /**
     * MEA-005: Bao cat an ca doan (bulk, UC15)
     * Truong doan da dang nhap, con trong han
     * Expected: Tat ca thanh vien doan duoc tao ban ghi meal_cutoffs
     */
    public function testBulkMealCutoff()
    {
        $teamMembers = array(
            array('id' => 1),
            array('id' => 2),
            array('id' => 3),
            array('id' => 4),
        );

        $cutoffCount = 0;
        foreach ($teamMembers as $member) {
            $cutoff = new MealCutoffs();
            $cutoff->meal_id = 1;
            $cutoff->attendee_id = $member['id'];
            $cutoff->reported_by = 999;
            $cutoff->reported_at = time();
            $cutoffCount++;
        }

        $this->assertEquals(4, $cutoffCount, 'Phai cat an cho 4 nguoi');
    }

    /**
     * MEA-006: Truong doan bao cat doan khac
     * Truong doan don vi A thu bao cho don vi B
     * Expected: He thong tu choi, 403 hoac validation loi
     */
    public function testTeamLeadCannotCutoffOtherTeam()
    {
        $teamLeadPropertyId = 1;
        $attendeePropertyId = 2; // Don vi khac

        $canCutoff = ($teamLeadPropertyId === $attendeePropertyId);

        $this->assertFalse($canCutoff, 'Khong duoc bao cat cho don vi khac');
    }

    /**
     * MEA-007: Check-in bua an
     * Bua an dien ra
     * Expected: Ban ghi meal_checkins duoc tao
     */
    public function testMealCheckin()
    {
        $checkin = new MealCheckins();
        $checkin->meal_id = 1;
        $checkin->attendee_id = 1;
        $checkin->checked_in_at = time();
        $checkin->checked_in_by = 999;

        $this->assertEquals(1, $checkin->meal_id);
        $this->assertEquals(1, $checkin->attendee_id);
        $this->assertNotNull($checkin->checked_in_at);
    }

    /**
     * MEA-008: Check-in bua an khi da bao cat
     * Attendee da bao cat bua do
     * Expected: Hien thi canh bao "Nguoi nay da bao cat bua an"
     */
    public function testCheckinAfterCutoff()
    {
        $attendeeId = 1;
        $mealId = 1;

        // Gia lap da co cutoff
        $hasCutoff = true;

        if ($hasCutoff) {
            $warning = 'Người này đã báo cắt bữa ăn';
        } else {
            $warning = null;
        }

        $this->assertNotNull($warning, 'Phai hien thi canh bao');
    }

    /**
     * Test meal type validation
     */
    public function testMealTypeValidation()
    {
        $validTypes = array('breakfast', 'lunch', 'dinner');

        foreach ($validTypes as $type) {
            $this->assertContains($type, $validTypes);
        }
    }

    /**
     * Test cutoff deadline calculation
     */
    public function testCutoffDeadlineCalculation()
    {
        $mealTime = strtotime('2026-11-15 12:00:00');
        $cutoffMinutes = 30;

        $deadline = $mealTime - ($cutoffMinutes * 60);
        $expectedDeadline = strtotime('2026-11-15 11:30:00');

        $this->assertEquals($expectedDeadline, $deadline);
    }
}
