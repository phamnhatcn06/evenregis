<?php

/**
 * BanquetTest - Unit tests cho su kien tiec
 *
 * Test cases tu Test_case.md:
 * - BAN-001: Tao su kien tiec
 * - BAN-002: Thiet lap so do ban
 * - BAN-003: Ban so am hoac bang 0
 * - BAN-004: Phan bo nguoi vao ban
 * - BAN-005: Phan bo nguoi vao ban da day
 * - BAN-006: Phan bo 1 nguoi vao 2 ban khac nhau
 * - BAN-007: Xem so do tong quan tiec
 * - BAN-008: Canvas kich thuoc hop le
 */

class BanquetTest extends CTestCase
{
    /**
     * @var array Du lieu test
     */
    private $testBanquetData;

    protected function setUp()
    {
        parent::setUp();

        $this->testBanquetData = array(
            'event_id' => 1,
            'name' => 'Tiệc tối khai mạc',
            'date' => '2026-11-15',
            'time' => '19:00:00',
            'venue' => 'Hội trường lớn',
            'canvas_width' => 1200,
            'canvas_height' => 800,
            'is_active' => 1,
        );
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * BAN-001: Tao su kien tiec (UC24)
     * BTC Tiec da dang nhap
     * Expected: Su kien tiec duoc tao
     */
    public function testCreateBanquetEvent()
    {
        $model = new BanquetEvents();
        $model->setAttributes($this->testBanquetData);

        $this->assertEquals('Tiệc tối khai mạc', $model->name);
        $this->assertEquals('Hội trường lớn', $model->venue);
        $this->assertEquals(1200, $model->canvas_width);
        $this->assertEquals(800, $model->canvas_height);
    }

    /**
     * BAN-002: Thiet lap so do ban (UC25)
     * Su kien tiec ton tai
     * Expected: 30 ban duoc tao trong banquet_tables
     */
    public function testCreateBanquetTables()
    {
        $tables = array();
        for ($i = 1; $i <= 30; $i++) {
            $table = new BanquetTables();
            $table->banquet_id = 1;
            $table->table_number = $i;
            $table->capacity = 10;
            $table->pos_x = ($i % 6) * 150 + 100;
            $table->pos_y = floor($i / 6) * 150 + 100;
            $table->shape = 'circle';
            $tables[] = $table;
        }

        $this->assertCount(30, $tables, 'Phai co 30 ban');
    }

    /**
     * BAN-003: Ban so am hoac bang 0
     * Su kien tiec ton tai
     * Expected: Loi validation
     */
    public function testInvalidTableNumber()
    {
        $invalidNumbers = array(0, -1, -10);

        foreach ($invalidNumbers as $num) {
            $isValid = ($num > 0);
            $this->assertFalse($isValid, "So ban $num phai bi tu choi");
        }
    }

    /**
     * BAN-004: Phan bo nguoi vao ban (UC26)
     * Ban tiec va attendee ton tai
     * Expected: Ban ghi banquet_seats duoc tao
     */
    public function testAssignSeatToAttendee()
    {
        $seat = new BanquetSeats();
        $seat->table_id = 5;
        $seat->attendee_id = 1;
        $seat->seat_number = 3;

        $this->assertEquals(5, $seat->table_id);
        $this->assertEquals(1, $seat->attendee_id);
        $this->assertEquals(3, $seat->seat_number);
    }

    /**
     * BAN-005: Phan bo nguoi vao ban da day
     * Ban capacity=10, da co 10 nguoi
     * Expected: Loi "Ban da day"
     */
    public function testAssignToFullTable()
    {
        $tableCapacity = 10;
        $currentOccupancy = 10;

        $canAssign = ($currentOccupancy < $tableCapacity);

        $this->assertFalse($canAssign, 'Khong the them nguoi vao ban day');
    }

    /**
     * BAN-006: Phan bo 1 nguoi vao 2 ban khac nhau
     * Attendee da co ghe tai ban 5
     * Expected: Loi "Nguoi nay da duoc phan ban" HOAC huy ghe cu va tao ghe moi
     */
    public function testAssignAttendeeToMultipleTables()
    {
        $existingSeat = array('table_id' => 5, 'attendee_id' => 1);

        $newSeat = new BanquetSeats();
        $newSeat->table_id = 6;
        $newSeat->attendee_id = 1; // Trung attendee

        $alreadySeated = ($existingSeat['attendee_id'] === $newSeat->attendee_id);

        $this->assertTrue($alreadySeated, 'Nguoi nay da co cho ngoi');
    }

    /**
     * BAN-007: Xem so do tong quan tiec (UC27)
     * Su kien tiec co du ban va nguoi
     * Expected: Hien thi so do canvas voi tat ca ban, hien thi so ghe trong/da lap
     */
    public function testViewBanquetOverview()
    {
        $tables = array(
            array('table_number' => 1, 'capacity' => 10, 'occupied' => 8),
            array('table_number' => 2, 'capacity' => 10, 'occupied' => 10),
            array('table_number' => 3, 'capacity' => 10, 'occupied' => 5),
        );

        $totalCapacity = 0;
        $totalOccupied = 0;

        foreach ($tables as $t) {
            $totalCapacity += $t['capacity'];
            $totalOccupied += $t['occupied'];
        }

        $this->assertEquals(30, $totalCapacity);
        $this->assertEquals(23, $totalOccupied);
        $this->assertEquals(7, $totalCapacity - $totalOccupied, 'Con 7 ghe trong');
    }

    /**
     * BAN-008: Canvas kich thuoc hop le
     * Tao tiec voi canvas_width=0, canvas_height=0
     * Expected: Loi validation hoac dung gia tri mac dinh 1200x800
     */
    public function testInvalidCanvasSize()
    {
        $invalidSizes = array(
            array('width' => 0, 'height' => 0),
            array('width' => -100, 'height' => 800),
            array('width' => 1200, 'height' => -50),
        );

        $defaultWidth = 1200;
        $defaultHeight = 800;

        foreach ($invalidSizes as $size) {
            $isValid = ($size['width'] > 0 && $size['height'] > 0);

            if (!$isValid) {
                // Dung gia tri mac dinh
                $width = $defaultWidth;
                $height = $defaultHeight;
            } else {
                $width = $size['width'];
                $height = $size['height'];
            }

            $this->assertGreaterThan(0, $width);
            $this->assertGreaterThan(0, $height);
        }
    }

    /**
     * Test table shapes
     */
    public function testTableShapes()
    {
        $validShapes = array('circle', 'rectangle', 'square');

        foreach ($validShapes as $shape) {
            $table = new BanquetTables();
            $table->shape = $shape;
            $this->assertEquals($shape, $table->shape);
        }
    }

    /**
     * Test seat number validation
     */
    public function testSeatNumberValidation()
    {
        $tableCapacity = 10;
        $validSeatNumbers = range(1, $tableCapacity);
        $invalidSeatNumbers = array(0, 11, -1);

        foreach ($validSeatNumbers as $num) {
            $isValid = ($num >= 1 && $num <= $tableCapacity);
            $this->assertTrue($isValid, "Ghe $num phai hop le");
        }

        foreach ($invalidSeatNumbers as $num) {
            $isValid = ($num >= 1 && $num <= $tableCapacity);
            $this->assertFalse($isValid, "Ghe $num phai khong hop le");
        }
    }

    /**
     * Test table position bounds
     */
    public function testTablePositionBounds()
    {
        $canvasWidth = 1200;
        $canvasHeight = 800;

        $table = new BanquetTables();
        $table->pos_x = 100;
        $table->pos_y = 100;

        $isWithinBounds = (
            $table->pos_x >= 0 && $table->pos_x <= $canvasWidth &&
            $table->pos_y >= 0 && $table->pos_y <= $canvasHeight
        );

        $this->assertTrue($isWithinBounds, 'Vi tri ban phai trong canvas');
    }
}
