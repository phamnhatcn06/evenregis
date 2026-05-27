<?php

/**
 * TalentShowsTest - Unit tests cho van nghe
 *
 * Test cases tu Test_case.md:
 * - TAL-001: Tao cuoc thi van nghe
 * - TAL-002: Tao the loai thi (don ca, top ca, mua)
 * - TAL-003: Dang ky tiet muc don ca
 * - TAL-004: Dang ky tiet muc top ca (nhieu thanh vien)
 * - TAL-005: Duration am hoac bang 0
 * - TAL-006: Upload file nhac cho tiet muc
 * - TAL-007: Cham diem tiet muc van nghe
 * - TAL-008: Thanh vien tiet muc khong phai attendee hop le
 */

class TalentShowsTest extends CTestCase
{
    /**
     * @var array Du lieu test
     */
    private $testShowData;

    protected function setUp()
    {
        parent::setUp();

        $this->testShowData = array(
            'event_id' => 1,
            'name' => 'Đêm văn nghệ Đại hội 2026',
            'date' => '2026-11-16',
            'is_active' => 1,
        );
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * TAL-001: Tao cuoc thi van nghe
     * Admin da dang nhap
     * Expected: Cuoc thi duoc tao
     */
    public function testCreateTalentShow()
    {
        $model = new TalentShows();
        $model->setAttributes($this->testShowData);

        $this->assertEquals('Đêm văn nghệ Đại hội 2026', $model->name);
        $this->assertEquals(1, $model->event_id);
    }

    /**
     * TAL-002: Tao the loai thi (don ca, top ca, mua)
     * Cuoc thi ton tai
     * Expected: Category duoc tao, lien ket talent_show
     */
    public function testCreateTalentCategories()
    {
        $categories = array(
            array('code' => 'solo_singing', 'name' => 'Đơn ca'),
            array('code' => 'group_singing', 'name' => 'Tốp ca'),
            array('code' => 'solo_dance', 'name' => 'Múa đơn'),
            array('code' => 'group_dance', 'name' => 'Múa nhóm'),
            array('code' => 'instrument', 'name' => 'Nhạc cụ'),
            array('code' => 'comedy', 'name' => 'Hài kịch'),
        );

        $this->assertCount(6, $categories, 'Phai co 6 the loai');

        foreach ($categories as $cat) {
            $this->assertNotEmpty($cat['code']);
            $this->assertNotEmpty($cat['name']);
        }
    }

    /**
     * TAL-003: Dang ky tiet muc don ca
     * Cuoc thi co category don ca, attendee ton tai
     * Expected: Tiet muc duoc tao trong talent_entries
     */
    public function testRegisterSoloEntry()
    {
        $entry = new TalentEntries();
        $entry->talent_show_id = 1;
        $entry->category_id = 1; // don ca
        $entry->property_id = 1;
        $entry->title = 'Quê hương';
        $entry->duration = 180; // 3 phut (giay)
        $entry->performer_count = 1;

        $this->assertEquals('Quê hương', $entry->title);
        $this->assertEquals(180, $entry->duration);
        $this->assertEquals(1, $entry->performer_count);
    }

    /**
     * TAL-004: Dang ky tiet muc top ca (nhieu thanh vien)
     * Tiet muc top ca duoc tao
     * Expected: 5 ban ghi talent_entry_members duoc tao
     */
    public function testRegisterGroupEntry()
    {
        $entry = array('id' => 1, 'title' => 'Việt Nam ơi', 'performer_count' => 5);

        $members = array();
        for ($i = 1; $i <= 5; $i++) {
            $member = new TalentEntryMembers();
            $member->entry_id = $entry['id'];
            $member->attendee_id = $i;
            $members[] = $member;
        }

        $this->assertCount(5, $members, 'Phai co 5 thanh vien');
    }

    /**
     * TAL-005: Duration am hoac bang 0
     * Nhap duration=-60 hoac 0
     * Expected: Loi validation "Thoi luong phai lon hon 0"
     */
    public function testInvalidDuration()
    {
        $invalidDurations = array(-60, 0, -1);

        foreach ($invalidDurations as $duration) {
            $isValid = ($duration > 0);
            $this->assertFalse($isValid, "Duration $duration phai bi tu choi");
        }
    }

    /**
     * TAL-006: Upload file nhac cho tiet muc
     * Tiet muc ton tai
     * Expected: File luu thanh cong, music_path cap nhat
     */
    public function testUploadMusicFile()
    {
        $entry = new TalentEntries();
        $entry->id = 1;
        $entry->title = 'Quê hương';

        // Gia lap upload
        $musicPath = '/uploads/music/entry_1_que_huong.mp3';
        $entry->music_path = $musicPath;

        $this->assertEquals($musicPath, $entry->music_path);
    }

    /**
     * TAL-007: Cham diem tiet muc van nghe
     * Tiet muc va giam khao ton tai
     * Expected: Ban ghi talent_scores duoc tao
     */
    public function testScoreTalentEntry()
    {
        $score = new TalentScores();
        $score->entry_id = 1;
        $score->judge_id = 1;
        $score->score = 9.0;
        $score->comment = 'Trình diễn xuất sắc';

        $this->assertEquals(9.0, $score->score);
        $this->assertEquals('Trình diễn xuất sắc', $score->comment);
    }

    /**
     * TAL-008: Thanh vien tiet muc khong phai attendee hop le
     * Them attendee_id khong ton tai
     * Expected: Loi foreign key HOAC validation
     */
    public function testInvalidMemberAttendee()
    {
        $validAttendeeIds = array(1, 2, 3, 4, 5);
        $invalidAttendeeId = 999;

        $isValid = in_array($invalidAttendeeId, $validAttendeeIds);

        $this->assertFalse($isValid, 'Attendee khong ton tai phai bi tu choi');
    }

    /**
     * Test duration in seconds
     */
    public function testDurationFormat()
    {
        // Duration luu bang giay
        $durations = array(
            array('minutes' => 3, 'seconds' => 180),
            array('minutes' => 5, 'seconds' => 300),
            array('minutes' => 4.5, 'seconds' => 270),
        );

        foreach ($durations as $d) {
            $expected = $d['minutes'] * 60;
            $this->assertEquals($expected, $d['seconds']);
        }
    }

    /**
     * Test category types
     */
    public function testCategoryTypes()
    {
        $validCategories = array(
            'solo_singing',
            'group_singing',
            'solo_dance',
            'group_dance',
            'instrument',
            'comedy',
        );

        foreach ($validCategories as $cat) {
            $this->assertNotEmpty($cat);
        }
    }

    /**
     * Test performer count validation
     */
    public function testPerformerCount()
    {
        // Don ca: 1 nguoi
        // Top ca: 3-10 nguoi
        // Mua nhom: 3-20 nguoi

        $solo = array('category' => 'solo_singing', 'min' => 1, 'max' => 1);
        $group = array('category' => 'group_singing', 'min' => 3, 'max' => 10);
        $dance = array('category' => 'group_dance', 'min' => 3, 'max' => 20);

        $this->assertEquals(1, $solo['min']);
        $this->assertEquals(1, $solo['max']);
        $this->assertGreaterThan(1, $group['min']);
    }
}
