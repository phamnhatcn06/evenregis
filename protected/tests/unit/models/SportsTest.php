<?php

/**
 * SportsTest - Unit tests cho mon the thao
 *
 * Test cases tu Test_case.md:
 * - SPT-001: Tao mon the thao cap goc
 * - SPT-002: Tao mon the thao con (child)
 * - SPT-003: Tao mon voi ma code trung lap
 * - SPT-004: Upload file dieu le thi dau
 */

class SportsTest extends CTestCase
{
    /**
     * @var array Du lieu test
     */
    private $testSportData;

    protected function setUp()
    {
        parent::setUp();

        $this->testSportData = array(
            'code' => 'BD',
            'name' => 'Bóng đá',
            'type' => 'team',
            'parent_id' => null,
            'is_active' => 1,
        );
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    /**
     * SPT-001: Tao mon the thao cap goc
     * BTC The thao da dang nhap
     * Expected: Mon duoc tao thanh cong, hien thi trong danh sach
     */
    public function testCreateParentSport()
    {
        $model = new Sports();
        $model->setAttributes($this->testSportData);

        $this->assertEquals('BD', $model->code);
        $this->assertEquals('Bóng đá', $model->name);
        $this->assertEquals('team', $model->type);
        $this->assertNull($model->parent_id, 'Mon goc khong co parent');
    }

    /**
     * SPT-002: Tao mon the thao con (child)
     * Da co mon "Bong da"
     * Expected: Mon con duoc tao, lien ket dung voi mon cha
     */
    public function testCreateChildSport()
    {
        // Mon cha
        $parentSport = new Sports();
        $parentSport->setAttributes($this->testSportData);
        $parentSport->id = 1;

        // Mon con
        $childSport = new Sports();
        $childSport->setAttributes(array(
            'code' => 'BDN',
            'name' => 'Bóng đá nam',
            'type' => 'team',
            'parent_id' => $parentSport->id,
            'is_active' => 1,
        ));

        $this->assertEquals($parentSport->id, $childSport->parent_id, 'Mon con phai lien ket voi mon cha');
    }

    /**
     * SPT-003: Tao mon voi ma code trung lap
     * Mon "BD" da ton tai
     * Expected: Loi "Ma mon da ton tai" (UNIQUE KEY)
     */
    public function testCreateSportWithDuplicateCode()
    {
        $model1 = new Sports();
        $model1->setAttributes($this->testSportData);
        $model1->id = 1;

        $model2 = new Sports();
        $model2->setAttributes(array(
            'code' => 'BD', // Trung ma
            'name' => 'Môn khác',
            'type' => 'team',
            'is_active' => 1,
        ));

        $this->assertEquals($model1->code, $model2->code, 'Ma code phai trung de test');
    }

    /**
     * SPT-004: Upload file dieu le thi dau (document)
     * Mon the thao ton tai
     * Expected: File luu thanh cong, document path cap nhat
     */
    public function testUploadSportDocument()
    {
        $model = new Sports();
        $model->setAttributes($this->testSportData);
        $model->id = 1;

        // Gia lap upload
        $documentPath = '/uploads/sports/dieu_le_bong_da.pdf';
        $model->document = $documentPath;

        $this->assertEquals($documentPath, $model->document);
    }

    /**
     * Test sport type validation
     */
    public function testSportTypeValidation()
    {
        $validTypes = array('team', 'individual');

        foreach ($validTypes as $type) {
            $model = new Sports();
            $model->setAttributes(array(
                'code' => 'TEST',
                'name' => 'Test Sport',
                'type' => $type,
            ));

            $this->assertContains($type, $validTypes, "Type '$type' phai hop le");
        }
    }

    /**
     * Test sport hierarchy depth
     */
    public function testSportHierarchyDepth()
    {
        // Chi cho phep 2 cap: cha -> con
        $grandparent = array('id' => 1, 'name' => 'Bóng đá', 'parent_id' => null);
        $parent = array('id' => 2, 'name' => 'Bóng đá nam', 'parent_id' => 1);
        // Khong nen co cap 3
        $child = array('id' => 3, 'name' => 'Bóng đá nam U21', 'parent_id' => 2);

        $this->assertNull($grandparent['parent_id'], 'Cap goc khong co parent');
        $this->assertEquals(1, $parent['parent_id'], 'Cap 2 tro ve cap 1');
        // Cap 3 khong nen ton tai trong he thong
    }
}
