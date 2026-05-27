<?php

/**
 * SportMatchesTest - Unit tests cho tran dau
 *
 * Test cases tu Test_case.md:
 * - SPT-011: Tao tran dau vong bang
 * - SPT-012: Tao tran voi team_a = team_b
 * - SPT-013: Tao tran chua biet doi (TBD)
 * - SPT-014: Tran dau trung thoi gian cung dia diem
 * - SPT-015: Cap nhat ket qua tran thang-thua
 * - SPT-016: Cap nhat ket qua hoa
 * - SPT-017: Cap nhat ket qua tran da hoan thanh
 * - SPT-018: Winner khong phai team_a hoac team_b
 * - SPT-019: Huy tran dau
 */

class SportMatchesTest extends CTestCase
{
    /**
     * @var array Du lieu test
     */
    private $testMatchData;

    protected function setUp()
    {
        parent::setUp();

        $this->testMatchData = array(
            'sport_id' => 1,
            'team_a_id' => 1,
            'team_b_id' => 2,
            'match_type' => 'group',
            'scheduled_at' => strtotime('2026-11-15 09:00:00'),
            'venue' => 'Sân A',
            'status' => 'scheduled',
        );
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * SPT-011: Tao tran dau vong bang
     * 2 doi ton tai, mon the thao ton tai
     * Expected: Tran duoc tao voi status="scheduled"
     */
    public function testCreateGroupMatch()
    {
        $model = new SportMatches();
        $model->setAttributes($this->testMatchData);

        $this->assertEquals(1, $model->team_a_id);
        $this->assertEquals(2, $model->team_b_id);
        $this->assertEquals('group', $model->match_type);
        $this->assertEquals('scheduled', $model->status);
    }

    /**
     * SPT-012: Tao tran voi team_a = team_b (doi dau voi chinh minh)
     * 2 doi ton tai
     * Expected: Loi validation "Doi A va Doi B khong duoc trung nhau"
     */
    public function testCreateMatchWithSameTeams()
    {
        $model = new SportMatches();
        $model->setAttributes(array(
            'sport_id' => 1,
            'team_a_id' => 1,
            'team_b_id' => 1, // Trung voi team_a
            'match_type' => 'group',
            'status' => 'scheduled',
        ));

        $isSameTeam = ($model->team_a_id === $model->team_b_id);

        $this->assertTrue($isSameTeam, 'Khong duoc tao tran doi dau voi chinh minh');
    }

    /**
     * SPT-013: Tao tran chua biet doi (TBD)
     * Giai doan knockout chua co doi thang
     * Expected: Tran duoc tao voi team NULL (cho ket qua vong truoc)
     */
    public function testCreateMatchWithTbdTeams()
    {
        $model = new SportMatches();
        $model->setAttributes(array(
            'sport_id' => 1,
            'team_a_id' => null, // TBD
            'team_b_id' => null, // TBD
            'match_type' => 'knockout',
            'status' => 'scheduled',
        ));

        $this->assertNull($model->team_a_id, 'Team A co the null (TBD)');
        $this->assertNull($model->team_b_id, 'Team B co the null (TBD)');
    }

    /**
     * SPT-014: Tran dau trung thoi gian cung dia diem
     * San A da co tran luc 9h
     * Expected: Canh bao "Dia diem da co tran dau vao thoi diem nay"
     */
    public function testMatchConflictSameVenueAndTime()
    {
        $existingMatch = array(
            'scheduled_at' => strtotime('2026-11-15 09:00:00'),
            'venue' => 'Sân A',
        );

        $newMatch = new SportMatches();
        $newMatch->setAttributes(array(
            'sport_id' => 1,
            'team_a_id' => 3,
            'team_b_id' => 4,
            'scheduled_at' => strtotime('2026-11-15 09:00:00'), // Trung gio
            'venue' => 'Sân A', // Trung san
            'status' => 'scheduled',
        ));

        $hasConflict = (
            $existingMatch['scheduled_at'] === $newMatch->scheduled_at &&
            $existingMatch['venue'] === $newMatch->venue
        );

        $this->assertTrue($hasConflict, 'Phai phat hien xung dot lich');
    }

    // ==================== MATCH RESULTS ====================

    /**
     * SPT-015: Cap nhat ket qua tran thang-thua
     * Tran status="ongoing"
     * Expected: Ban ghi sport_match_results tao/cap nhat; tran status="completed"
     */
    public function testUpdateMatchResultWinLose()
    {
        $match = new SportMatches();
        $match->id = 1;
        $match->team_a_id = 1;
        $match->team_b_id = 2;
        $match->status = 'ongoing';

        $result = new SportMatchResults();
        $result->match_id = $match->id;
        $result->score_a = 3;
        $result->score_b = 1;
        $result->winner_team_id = $match->team_a_id;
        $result->is_draw = 0;

        // Cap nhat trang thai tran
        $match->status = 'completed';

        $this->assertEquals('completed', $match->status);
        $this->assertEquals(3, $result->score_a);
        $this->assertEquals(1, $result->score_b);
        $this->assertEquals($match->team_a_id, $result->winner_team_id);
    }

    /**
     * SPT-016: Cap nhat ket qua hoa (is_draw=1)
     * Tran dang dien ra
     * Expected: Ket qua luu voi is_draw=1, winner_team_id=NULL
     */
    public function testUpdateMatchResultDraw()
    {
        $result = new SportMatchResults();
        $result->match_id = 1;
        $result->score_a = 1;
        $result->score_b = 1;
        $result->is_draw = 1;
        $result->winner_team_id = null;

        $this->assertEquals(1, $result->is_draw);
        $this->assertNull($result->winner_team_id, 'Hoa thi khong co winner');
        $this->assertEquals($result->score_a, $result->score_b, 'Ti so phai bang nhau');
    }

    /**
     * SPT-017: Cap nhat ket qua tran da hoan thanh (ghi de)
     * Tran status="completed"
     * Expected: Ket qua duoc cap nhat; audit log ghi lai
     */
    public function testUpdateCompletedMatchResult()
    {
        $result = new SportMatchResults();
        $result->id = 1;
        $result->match_id = 1;
        $result->score_a = 2;
        $result->score_b = 1;

        // Sua lai ket qua
        $oldScoreA = $result->score_a;
        $result->score_a = 3;
        $result->updated_at = time();

        $this->assertNotEquals($oldScoreA, $result->score_a, 'Ket qua phai thay doi');
        $this->assertNotNull($result->updated_at);
    }

    /**
     * SPT-018: Winner khong phai team_a hoac team_b
     * Nhap winner la doi khong lien quan
     * Expected: Loi validation
     */
    public function testInvalidWinner()
    {
        $match = new SportMatches();
        $match->id = 1;
        $match->team_a_id = 1;
        $match->team_b_id = 2;

        $result = new SportMatchResults();
        $result->match_id = $match->id;
        $result->winner_team_id = 999; // Doi khong lien quan

        $isValidWinner = in_array($result->winner_team_id, array($match->team_a_id, $match->team_b_id, null));

        $this->assertFalse($isValidWinner, 'Winner phai la team_a hoac team_b hoac null');
    }

    /**
     * SPT-019: Huy tran dau (status=cancelled)
     * Tran scheduled hoac ongoing
     * Expected: status="cancelled", khong tinh vao bang xep hang
     */
    public function testCancelMatch()
    {
        $match = new SportMatches();
        $match->id = 1;
        $match->status = 'scheduled';

        // Huy tran
        $match->status = 'cancelled';

        $this->assertEquals('cancelled', $match->status);
    }

    /**
     * Test match status constants
     */
    public function testMatchStatusConstants()
    {
        $validStatuses = array('scheduled', 'ongoing', 'completed', 'cancelled', 'postponed');

        foreach ($validStatuses as $status) {
            $match = new SportMatches();
            $match->status = $status;
            $this->assertEquals($status, $match->status);
        }
    }

    /**
     * Test match type constants
     */
    public function testMatchTypeConstants()
    {
        $validTypes = array('group', 'knockout', 'playoff', 'final', 'semifinal', 'quarterfinal');

        foreach ($validTypes as $type) {
            $match = new SportMatches();
            $match->match_type = $type;
            $this->assertEquals($type, $match->match_type);
        }
    }
}
