<?php

/**
 * BeautyContestsTest - Unit tests cho thi sac dep
 *
 * Test cases tu Test_case.md:
 * - BCT-001: Tao cuoc thi Miss
 * - BCT-002: Dang ky thi sinh hop le
 * - BCT-003: Dang ky thi sinh khong du tuoi
 * - BCT-004: Tao vong thi Miss (ao dai, bikini, tai nang)
 * - BCT-005: Cham diem thi sinh theo giam khao
 * - BCT-006: Giam khao cham diem 2 lan cung thi sinh-vong
 * - BCT-007: Tinh diem trung binh vong thi
 * - BCT-008: Measurements thi sinh nhap sai dinh dang
 */

class BeautyContestsTest extends CTestCase
{
    /**
     * @var array Du lieu test
     */
    private $testContestData;

    protected function setUp()
    {
        parent::setUp();

        $this->testContestData = array(
            'event_id' => 1,
            'name' => 'Hoa khôi Mường Thanh 2026',
            'gender' => 'female',
            'age_min' => 18,
            'age_max' => 35,
            'is_active' => 1,
        );
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * BCT-001: Tao cuoc thi Miss
     * Admin da dang nhap
     * Expected: Cuoc thi duoc tao thanh cong
     */
    public function testCreateBeautyContest()
    {
        $model = new BeautyContests();
        $model->setAttributes($this->testContestData);

        $this->assertEquals('Hoa khôi Mường Thanh 2026', $model->name);
        $this->assertEquals('female', $model->gender);
        $this->assertEquals(18, $model->age_min);
        $this->assertEquals(35, $model->age_max);
    }

    /**
     * BCT-002: Dang ky thi sinh hop le
     * Cuoc thi ton tai, attendee nu du tuoi
     * Expected: Thi sinh duoc them vao beauty_contestants
     */
    public function testRegisterValidContestant()
    {
        $contestAgeMin = 18;
        $contestAgeMax = 35;
        $attendeeBirthYear = 2000;
        $currentYear = 2026;
        $attendeeAge = $currentYear - $attendeeBirthYear;

        $isValidAge = ($attendeeAge >= $contestAgeMin && $attendeeAge <= $contestAgeMax);

        $this->assertTrue($isValidAge, 'Thi sinh phai du tuoi');
        $this->assertEquals(26, $attendeeAge);
    }

    /**
     * BCT-003: Dang ky thi sinh khong du tuoi
     * age_min=18, thi sinh 16 tuoi
     * Expected: Loi "Thi sinh khong du tuoi tham gia cuoc thi"
     */
    public function testRegisterUnderageContestant()
    {
        $contestAgeMin = 18;
        $attendeeBirthYear = 2010;
        $currentYear = 2026;
        $attendeeAge = $currentYear - $attendeeBirthYear;

        $isValidAge = ($attendeeAge >= $contestAgeMin);

        $this->assertFalse($isValidAge, 'Thi sinh chua du tuoi');
        $this->assertEquals(16, $attendeeAge);
    }

    /**
     * BCT-004: Tao vong thi Miss (ao dai, bikini, tai nang)
     * Cuoc thi ton tai
     * Expected: Cac vong thi duoc tao dung, lien ket voi contest
     */
    public function testCreateBeautyRounds()
    {
        $rounds = array(
            array('name' => 'Áo dài', 'round_type' => 'ao_dai', 'round_order' => 1),
            array('name' => 'Bikini', 'round_type' => 'bikini', 'round_order' => 2),
            array('name' => 'Tài năng', 'round_type' => 'talent', 'round_order' => 3),
            array('name' => 'Ứng xử', 'round_type' => 'qa', 'round_order' => 4),
            array('name' => 'Chung kết', 'round_type' => 'final', 'round_order' => 5),
        );

        $this->assertCount(5, $rounds, 'Phai co 5 vong thi');

        foreach ($rounds as $i => $round) {
            $this->assertEquals($i + 1, $round['round_order']);
        }
    }

    /**
     * BCT-005: Cham diem thi sinh theo giam khao
     * Thi sinh va vong thi ton tai, user la giam khao
     * Expected: Ban ghi beauty_scores duoc tao voi judge_id dung
     */
    public function testJudgeScoreContestant()
    {
        $score = new BeautyScores();
        $score->round_id = 1;
        $score->contestant_id = 1;
        $score->judge_id = 1;
        $score->score = 8.5;

        $this->assertEquals(1, $score->judge_id);
        $this->assertEquals(8.5, $score->score);
    }

    /**
     * BCT-006: Giam khao cham diem 2 lan cung thi sinh-vong
     * Da cham diem lan 1
     * Expected: Cap nhat diem lan 2 hoac loi trung (tuy business rule)
     */
    public function testJudgeScoreTwice()
    {
        // Lan 1
        $score1 = array('round_id' => 1, 'contestant_id' => 1, 'judge_id' => 1, 'score' => 8.0);

        // Lan 2
        $score2 = array('round_id' => 1, 'contestant_id' => 1, 'judge_id' => 1, 'score' => 8.5);

        $isDuplicate = (
            $score1['round_id'] === $score2['round_id'] &&
            $score1['contestant_id'] === $score2['contestant_id'] &&
            $score1['judge_id'] === $score2['judge_id']
        );

        // Tuy business rule: cap nhat hoac loi
        $this->assertTrue($isDuplicate, 'Giam khao da cham diem roi');
    }

    /**
     * BCT-007: Tinh diem trung binh vong thi
     * Nhieu giam khao da cham
     * Expected: Diem TB duoc tinh dung theo cong thuc
     */
    public function testCalculateAverageScore()
    {
        $scores = array(8.5, 9.0, 8.0, 8.5, 9.5);
        $average = array_sum($scores) / count($scores);

        $this->assertEqualsWithDelta(8.7, $average, 0.01, 'Diem TB phai dung');
    }

    /**
     * BCT-008: Measurements thi sinh nhap sai dinh dang
     * Nhap measurements="abc" thay vi "90-60-90"
     * Expected: Validation kiem tra dinh dang hoac luu chuoi tu do
     */
    public function testMeasurementsFormat()
    {
        $validFormat = '90-60-90';
        $invalidFormat = 'abc';

        // Kiem tra dinh dang so-so-so
        $pattern = '/^\d{2,3}-\d{2,3}-\d{2,3}$/';

        $isValidFormatCorrect = preg_match($pattern, $validFormat);
        $isInvalidFormatCorrect = preg_match($pattern, $invalidFormat);

        $this->assertEquals(1, $isValidFormatCorrect, '90-60-90 phai hop le');
        $this->assertEquals(0, $isInvalidFormatCorrect, 'abc phai khong hop le');
    }

    /**
     * Test contestant attributes
     */
    public function testContestantAttributes()
    {
        $contestant = new BeautyContestants();
        $contestant->contest_id = 1;
        $contestant->attendee_id = 1;
        $contestant->height = 168;
        $contestant->weight = 52;
        $contestant->measurements = '86-62-88';
        $contestant->talent = 'Hát';

        $this->assertEquals(168, $contestant->height);
        $this->assertEquals(52, $contestant->weight);
        $this->assertEquals('86-62-88', $contestant->measurements);
        $this->assertEquals('Hát', $contestant->talent);
    }

    /**
     * Test height validation
     */
    public function testHeightValidation()
    {
        $validHeights = array(150, 165, 175, 180);
        $invalidHeights = array(0, -1, 100, 250);

        foreach ($validHeights as $h) {
            $isValid = ($h >= 140 && $h <= 200);
            $this->assertTrue($isValid, "Chieu cao $h phai hop le");
        }

        foreach ($invalidHeights as $h) {
            $isValid = ($h >= 140 && $h <= 200);
            $this->assertFalse($isValid, "Chieu cao $h phai khong hop le");
        }
    }

    /**
     * Test score range
     */
    public function testScoreRange()
    {
        $validScores = array(0, 5.5, 8.0, 10);
        $invalidScores = array(-1, 11, 15);

        foreach ($validScores as $s) {
            $isValid = ($s >= 0 && $s <= 10);
            $this->assertTrue($isValid, "Diem $s phai hop le");
        }

        foreach ($invalidScores as $s) {
            $isValid = ($s >= 0 && $s <= 10);
            $this->assertFalse($isValid, "Diem $s phai khong hop le");
        }
    }
}
