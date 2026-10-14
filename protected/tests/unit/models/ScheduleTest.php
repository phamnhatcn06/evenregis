<?php

/**
 * ScheduleTest - Unit tests cho logic dựng lịch trình cá nhân (Schedule).
 *
 * Test logic THUẦN (không phụ thuộc Yii/DB/API): buildTimeline, computeStatus,
 * pickNextUp, groupByDay, định dạng giờ Việt Nam.
 *
 * Test cases (spec docs/specs/portal-personal-schedule.md):
 * - SCH-001: Item không có giờ bị tách ra noTime, không vào next-up (AC1.4, AC6.1)
 * - SCH-002: next-up là item gần nhất ở tương lai (AC6.1)
 * - SCH-003: nhiều item cùng mốc gần nhất -> trả cả nhóm (AC6.4)
 * - SCH-004: trạng thái tính từ đúng start_time, không ngưỡng (AC6.3)
 * - SCH-005: qua end_time -> đã diễn ra
 * - SCH-006: item có giờ sắp xếp tăng dần
 * - SCH-007: không còn item tương lai -> next-up rỗng (AC6.5)
 * - SCH-008: định dạng giờ Việt Nam cố định Asia/Ho_Chi_Minh
 */
class ScheduleTest extends CTestCase
{
    /** @var int Mốc now cố định: 2026-11-01 08:00 giờ VN (01:00 UTC) */
    private $now;
    private $h = 3600;

    protected function setUp()
    {
        parent::setUp();
        $this->now = gmmktime(1, 0, 0, 11, 1, 2026);
    }

    private function sampleItems()
    {
        return array(
            array('type' => 'sport', 'title' => 'Trận bóng đá', 'startTime' => $this->now + 2 * $this->h, 'location' => 'Sân A'),
            array('type' => 'competition', 'title' => 'Vòng 1 Lễ tân', 'startTime' => $this->now + 2 * $this->h, 'location' => 'P.201'),
            array('type' => 'agenda', 'title' => 'Khai mạc', 'startTime' => $this->now - $this->h, 'endTime' => $this->now + $this->h),
            array('type' => 'meal', 'title' => 'Bữa trưa', 'startTime' => $this->now + 5 * $this->h),
            array('type' => 'banquet', 'title' => 'Tiệc tối', 'startTime' => $this->now - 10 * $this->h, 'endTime' => $this->now - 8 * $this->h),
            array('type' => 'sport', 'title' => 'Trận chờ xếp lịch', 'startTime' => null),
        );
    }

    // SCH-001
    public function testItemWithoutTimeGoesToNoTime()
    {
        $t = Schedule::buildTimeline($this->sampleItems(), $this->now);
        $this->assertEquals(1, count($t['noTime']));
        $this->assertEquals(Schedule::STATUS_NO_TIME, $t['noTime'][0]['status']);
    }

    // SCH-002 & SCH-003
    public function testNextUpPicksNearestFutureAndGroupsTies()
    {
        $t = Schedule::buildTimeline($this->sampleItems(), $this->now);
        $this->assertEquals(2, count($t['nextUp']));
        $this->assertEquals($this->now + 2 * $this->h, $t['nextUp'][0]['startTime']);
        $this->assertNotEquals('Khai mạc', $t['nextUp'][0]['title']);
    }

    // SCH-004 & SCH-005
    public function testComputeStatusFromExactStart()
    {
        $this->assertEquals(Schedule::STATUS_UPCOMING, Schedule::computeStatus($this->now + $this->h, null, $this->now));
        $this->assertEquals(Schedule::STATUS_ONGOING, Schedule::computeStatus($this->now, null, $this->now));
        $this->assertEquals(Schedule::STATUS_ONGOING, Schedule::computeStatus($this->now - $this->h, $this->now + $this->h, $this->now));
        $this->assertEquals(Schedule::STATUS_DONE, Schedule::computeStatus($this->now - 2 * $this->h, $this->now - $this->h, $this->now));
    }

    // SCH-006
    public function testTimedItemsSortedAscending()
    {
        $t = Schedule::buildTimeline($this->sampleItems(), $this->now);
        $times = array();
        foreach ($t['days'] as $d) {
            foreach ($d['items'] as $it) {
                $times[] = $it['startTime'];
            }
        }
        $sorted = $times;
        sort($sorted);
        $this->assertEquals($sorted, $times);
        $this->assertEquals(5, count($times));
    }

    // SCH-007
    public function testNextUpEmptyWhenNoFuture()
    {
        $past = array(array('type' => 'meal', 'title' => 'x', 'startTime' => $this->now - $this->h));
        $t = Schedule::buildTimeline($past, $this->now);
        $this->assertEquals(0, count($t['nextUp']));
    }

    // SCH-008
    public function testVietnamTimeFormatting()
    {
        $this->assertEquals('10:00 - 01/11/2026', Schedule::formatVnDateTime($this->now + 2 * $this->h));
        $this->assertTrue(strpos(Schedule::formatVnDate($this->now), '01/11/2026') !== false);
    }
}
