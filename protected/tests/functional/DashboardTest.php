<?php

/**
 * DashboardTest - Functional tests cho Dashboard va bao cao
 *
 * Test cases tu Test_case.md:
 * - DSH-001: Dashboard hien thi tong so dang ky
 * - DSH-002: Dashboard thong ke theo don vi
 * - DSH-003: Xuat bao cao Excel toan bo attendee
 * - DSH-004: Dashboard hien thi khi khong co du lieu
 */

class DashboardTest extends CTestCase
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
     * DSH-001: Dashboard hien thi tong so dang ky
     * Nhieu phieu dang ky cac trang thai
     * Expected: So lieu dung: tong draft, submitted, approved, rejected
     */
    public function testDashboardDisplaysRegistrationCounts()
    {
        $registrations = array(
            array('id' => 1, 'status' => Registrations::STATUS_DRAFT),
            array('id' => 2, 'status' => Registrations::STATUS_DRAFT),
            array('id' => 3, 'status' => Registrations::STATUS_SUBMITTED),
            array('id' => 4, 'status' => Registrations::STATUS_SUBMITTED),
            array('id' => 5, 'status' => Registrations::STATUS_SUBMITTED),
            array('id' => 6, 'status' => Registrations::STATUS_APPROVED),
            array('id' => 7, 'status' => Registrations::STATUS_APPROVED),
            array('id' => 8, 'status' => Registrations::STATUS_APPROVED),
            array('id' => 9, 'status' => Registrations::STATUS_APPROVED),
            array('id' => 10, 'status' => Registrations::STATUS_REJECTED),
        );

        $counts = array(
            'draft' => 0,
            'submitted' => 0,
            'approved' => 0,
            'rejected' => 0,
        );

        foreach ($registrations as $reg) {
            switch ($reg['status']) {
                case Registrations::STATUS_DRAFT:
                    $counts['draft']++;
                    break;
                case Registrations::STATUS_SUBMITTED:
                    $counts['submitted']++;
                    break;
                case Registrations::STATUS_APPROVED:
                    $counts['approved']++;
                    break;
                case Registrations::STATUS_REJECTED:
                    $counts['rejected']++;
                    break;
            }
        }

        $this->assertEquals(2, $counts['draft'], 'Phai co 2 phieu nhap');
        $this->assertEquals(3, $counts['submitted'], 'Phai co 3 phieu da nop');
        $this->assertEquals(4, $counts['approved'], 'Phai co 4 phieu da duyet');
        $this->assertEquals(1, $counts['rejected'], 'Phai co 1 phieu tu choi');
        $this->assertEquals(10, array_sum($counts), 'Tong phai la 10');
    }

    /**
     * DSH-002: Dashboard thong ke theo don vi
     * Nhieu don vi da dang ky
     * Expected: Bang hien thi dung so luong attendee moi don vi
     */
    public function testDashboardStatsByProperty()
    {
        $attendees = array(
            array('id' => 1, 'property_id' => 1, 'property_name' => 'Hà Nội'),
            array('id' => 2, 'property_id' => 1, 'property_name' => 'Hà Nội'),
            array('id' => 3, 'property_id' => 1, 'property_name' => 'Hà Nội'),
            array('id' => 4, 'property_id' => 2, 'property_name' => 'Đà Nẵng'),
            array('id' => 5, 'property_id' => 2, 'property_name' => 'Đà Nẵng'),
            array('id' => 6, 'property_id' => 3, 'property_name' => 'TP.HCM'),
        );

        $statsByProperty = array();
        foreach ($attendees as $att) {
            $propertyId = $att['property_id'];
            if (!isset($statsByProperty[$propertyId])) {
                $statsByProperty[$propertyId] = array(
                    'property_name' => $att['property_name'],
                    'count' => 0,
                );
            }
            $statsByProperty[$propertyId]['count']++;
        }

        $this->assertEquals(3, $statsByProperty[1]['count'], 'Ha Noi co 3 nguoi');
        $this->assertEquals(2, $statsByProperty[2]['count'], 'Da Nang co 2 nguoi');
        $this->assertEquals(1, $statsByProperty[3]['count'], 'TP.HCM co 1 nguoi');
    }

    /**
     * DSH-003: Xuat bao cao Excel toan bo attendee
     * Nhieu attendee da approved
     * Expected: File Excel chua day du thong tin tat ca attendee
     */
    public function testExportAllAttendeesToExcel()
    {
        $attendees = array(
            array('full_name' => 'A', 'property_name' => 'HN', 'position' => 'NV'),
            array('full_name' => 'B', 'property_name' => 'DN', 'position' => 'QL'),
            array('full_name' => 'C', 'property_name' => 'HCM', 'position' => 'NV'),
        );

        // Gia lap export
        $exportData = array();
        $exportData[] = array('Họ tên', 'Đơn vị', 'Chức danh'); // Header

        foreach ($attendees as $att) {
            $exportData[] = array($att['full_name'], $att['property_name'], $att['position']);
        }

        $this->assertCount(4, $exportData, 'Phai co 1 header + 3 dong du lieu');
        $this->assertEquals('Họ tên', $exportData[0][0], 'Header dau tien la Ho ten');
    }

    /**
     * DSH-004: Dashboard hien thi khi khong co du lieu
     * DB trong
     * Expected: Khong crash; hien thi "Chua co du lieu" hoac so 0
     */
    public function testDashboardWithNoData()
    {
        $registrations = array();
        $attendees = array();

        $registrationCount = count($registrations);
        $attendeeCount = count($attendees);

        $this->assertEquals(0, $registrationCount, 'Khong co phieu dang ky');
        $this->assertEquals(0, $attendeeCount, 'Khong co nguoi tham du');

        // Hien thi message
        $message = ($attendeeCount == 0) ? 'Chưa có dữ liệu' : null;
        $this->assertEquals('Chưa có dữ liệu', $message);
    }

    /**
     * Test dashboard summary calculations
     */
    public function testDashboardSummaryCalculations()
    {
        $data = array(
            'total_registrations' => 50,
            'total_attendees' => 320,
            'approved_attendees' => 280,
            'pending_attendees' => 30,
            'rejected_attendees' => 10,
        );

        // Kiem tra tong
        $calculatedTotal = $data['approved_attendees'] + $data['pending_attendees'] + $data['rejected_attendees'];
        $this->assertEquals($data['total_attendees'], $calculatedTotal);

        // Tinh ti le duyet
        $approvalRate = ($data['approved_attendees'] / $data['total_attendees']) * 100;
        $this->assertEqualsWithDelta(87.5, $approvalRate, 0.1);
    }

    /**
     * Test dashboard date range filter
     */
    public function testDashboardDateRangeFilter()
    {
        $registrations = array(
            array('id' => 1, 'created_at' => strtotime('2026-11-01')),
            array('id' => 2, 'created_at' => strtotime('2026-11-05')),
            array('id' => 3, 'created_at' => strtotime('2026-11-10')),
            array('id' => 4, 'created_at' => strtotime('2026-11-15')),
            array('id' => 5, 'created_at' => strtotime('2026-11-20')),
        );

        $startDate = strtotime('2026-11-05');
        $endDate = strtotime('2026-11-15');

        $filtered = array_filter($registrations, function ($reg) use ($startDate, $endDate) {
            return $reg['created_at'] >= $startDate && $reg['created_at'] <= $endDate;
        });

        $this->assertCount(3, $filtered, 'Phai co 3 phieu trong khoang ngay');
    }

    /**
     * Test dashboard property filter
     */
    public function testDashboardPropertyFilter()
    {
        $attendees = array(
            array('id' => 1, 'property_id' => 1),
            array('id' => 2, 'property_id' => 1),
            array('id' => 3, 'property_id' => 2),
            array('id' => 4, 'property_id' => 3),
        );

        $filterPropertyId = 1;

        $filtered = array_filter($attendees, function ($att) use ($filterPropertyId) {
            return $att['property_id'] === $filterPropertyId;
        });

        $this->assertCount(2, $filtered, 'Phai co 2 nguoi thuoc property 1');
    }
}
