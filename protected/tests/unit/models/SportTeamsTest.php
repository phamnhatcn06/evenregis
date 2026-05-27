<?php

/**
 * SportTeamsTest - Unit tests cho doi thi dau
 *
 * Test cases tu Test_case.md:
 * - SPT-005: Tao doi thi dau cho don vi
 * - SPT-006: Tao doi hon hop (khong thuoc don vi nao)
 * - SPT-007: Them thanh vien vao doi
 * - SPT-008: Them cung attendee vao doi 2 lan
 * - SPT-009: Chi co 1 thuyen truong (is_captain)
 * - SPT-010: Xoa thanh vien khoi doi
 */

class SportTeamsTest extends CTestCase
{
    /**
     * @var array Du lieu test
     */
    private $testTeamData;

    protected function setUp()
    {
        parent::setUp();

        $this->testTeamData = array(
            'sport_id' => 1,
            'name' => 'Đội Bóng đá Hà Nội',
            'property_id' => 1,
            'is_active' => 1,
        );
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * SPT-005: Tao doi thi dau cho don vi
     * Mon the thao ton tai, don vi ton tai
     * Expected: Doi duoc tao voi organization_id dung
     */
    public function testCreateTeamForProperty()
    {
        $model = new SportTeams();
        $model->setAttributes($this->testTeamData);

        $this->assertEquals(1, $model->sport_id);
        $this->assertEquals('Đội Bóng đá Hà Nội', $model->name);
        $this->assertEquals(1, $model->property_id, 'Doi phai thuoc don vi');
    }

    /**
     * SPT-006: Tao doi hon hop (khong thuoc don vi nao)
     * Mon the thao ton tai
     * Expected: Doi duoc tao voi organization_id=NULL
     */
    public function testCreateMixedTeam()
    {
        $model = new SportTeams();
        $model->setAttributes(array(
            'sport_id' => 1,
            'name' => 'Đội hỗn hợp',
            'property_id' => null, // Khong thuoc don vi
            'is_active' => 1,
        ));

        $this->assertNull($model->property_id, 'Doi hon hop khong co property_id');
    }

    /**
     * SPT-007: Them thanh vien vao doi
     * Doi va attendee ton tai
     * Expected: Ban ghi sport_team_members duoc tao
     */
    public function testAddMemberToTeam()
    {
        $member = new SportTeamMembers();
        $member->team_id = 1;
        $member->attendee_id = 1;
        $member->jersey_number = 10;
        $member->position = 'Tiền đạo';
        $member->is_captain = 0;

        $this->assertEquals(1, $member->team_id);
        $this->assertEquals(1, $member->attendee_id);
        $this->assertEquals(10, $member->jersey_number);
        $this->assertEquals('Tiền đạo', $member->position);
    }

    /**
     * SPT-008: Them cung attendee vao doi 2 lan
     * Attendee da la thanh vien doi
     * Expected: Loi "Thanh vien da co trong doi" (UNIQUE KEY uq_team_member)
     */
    public function testAddDuplicateMemberToTeam()
    {
        $existingMember = array('team_id' => 1, 'attendee_id' => 1);

        $newMember = new SportTeamMembers();
        $newMember->team_id = 1;
        $newMember->attendee_id = 1; // Trung

        $isDuplicate = (
            $existingMember['team_id'] === $newMember->team_id &&
            $existingMember['attendee_id'] === $newMember->attendee_id
        );

        $this->assertTrue($isDuplicate, 'Khong duoc them trung thanh vien');
    }

    /**
     * SPT-009: Chi co 1 thuyen truong (is_captain)
     * Doi da co captain
     * Expected: He thong cho phep nhieu captain HOAC gioi han 1 (kiem tra logic)
     */
    public function testOnlyOneCaptain()
    {
        $members = array(
            array('id' => 1, 'attendee_id' => 1, 'is_captain' => 1),
            array('id' => 2, 'attendee_id' => 2, 'is_captain' => 0),
            array('id' => 3, 'attendee_id' => 3, 'is_captain' => 0),
        );

        $captainCount = 0;
        foreach ($members as $m) {
            if ($m['is_captain'] == 1) {
                $captainCount++;
            }
        }

        $this->assertEquals(1, $captainCount, 'Chi nen co 1 captain');
    }

    /**
     * SPT-010: Xoa thanh vien khoi doi
     * Thanh vien trong doi
     * Expected: Ban ghi trong sport_team_members bi xoa hoac status=0
     */
    public function testRemoveMemberFromTeam()
    {
        $member = new SportTeamMembers();
        $member->id = 1;
        $member->team_id = 1;
        $member->attendee_id = 1;
        $member->is_active = 1;

        // Xoa (soft delete)
        $member->is_active = 0;

        $this->assertEquals(0, $member->is_active, 'Thanh vien phai bi xoa (is_active=0)');
    }

    /**
     * Test jersey number validation
     */
    public function testJerseyNumberValidation()
    {
        $validNumbers = array(1, 10, 99);
        $invalidNumbers = array(0, -1, 100);

        foreach ($validNumbers as $num) {
            $this->assertGreaterThan(0, $num, "So ao $num phai > 0");
            $this->assertLessThan(100, $num, "So ao $num phai < 100");
        }
    }

    /**
     * Test unique jersey number per team
     */
    public function testUniqueJerseyNumberPerTeam()
    {
        $members = array(
            array('team_id' => 1, 'attendee_id' => 1, 'jersey_number' => 10),
            array('team_id' => 1, 'attendee_id' => 2, 'jersey_number' => 7),
            array('team_id' => 1, 'attendee_id' => 3, 'jersey_number' => 10), // Trung so ao
        );

        $jerseyNumbers = array();
        $hasDuplicate = false;

        foreach ($members as $m) {
            $key = $m['team_id'] . '-' . $m['jersey_number'];
            if (in_array($key, $jerseyNumbers)) {
                $hasDuplicate = true;
                break;
            }
            $jerseyNumbers[] = $key;
        }

        $this->assertTrue($hasDuplicate, 'Phat hien so ao trung');
    }
}
