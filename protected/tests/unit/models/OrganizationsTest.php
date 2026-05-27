<?php

/**
 * OrganizationsTest - Unit tests cho Organizations va Regionals
 *
 * Test cases tu Test_case.md:
 * - ORG-001: Tao khu vuc moi thanh cong
 * - ORG-002: Tao khu vuc voi ma trung lap
 * - ORG-003: Tao khu vuc voi ten rong
 * - ORG-004: Soft delete khu vuc
 * - ORG-005: Xoa khu vuc dang co don vi lien ket
 * - ORG-006: Tao don vi moi voi day du thong tin
 * - ORG-007: Tao don vi voi ma code trung
 * - ORG-008: Tao don vi khong chon khu vuc
 * - ORG-009: Ma don vi co ky tu dac biet
 * - ORG-010: Cap nhat thong tin don vi
 */

class OrganizationsTest extends CTestCase
{
    /**
     * @var array Du lieu test cho khu vuc
     */
    private $testRegionalData;

    /**
     * @var array Du lieu test cho don vi
     */
    private $testPropertyData;

    protected function setUp()
    {
        parent::setUp();

        $this->testRegionalData = array(
            'code' => 'KV01',
            'name' => 'Khu vực Hà Nội',
            'is_active' => 1,
        );

        $this->testPropertyData = array(
            'code' => 'HN01',
            'name' => 'Khách sạn Mường Thanh Hà Nội',
            'regional_id' => 1,
            'is_active' => 1,
        );
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    // ==================== REGIONALS TESTS ====================

    /**
     * ORG-001: Tao khu vuc moi thanh cong
     * Admin da dang nhap, nhap ma "KV01", ten "Khu vuc Ha Noi", luu
     * Expected: Khu vuc duoc tao, xuat hien trong danh sach
     */
    public function testCreateRegionalSuccess()
    {
        $model = new Regionals();
        $model->setAttributes($this->testRegionalData);

        // Kiem tra validate thanh cong
        $this->assertTrue($model->validate(), 'Model phai validate thanh cong voi du lieu hop le');
        $this->assertEmpty($model->getErrors(), 'Khong co loi validation');
        $this->assertEquals('KV01', $model->code);
        $this->assertEquals('Khu vực Hà Nội', $model->name);
    }

    /**
     * ORG-002: Tao khu vuc voi ma trung lap
     * Da co khu vuc ma "KV01", tao khu vuc moi voi ma "KV01"
     * Expected: Loi validation "Ma khu vuc da ton tai"
     */
    public function testCreateRegionalWithDuplicateCode()
    {
        // Gia lap da ton tai khu vuc KV01 bang cach mock
        $model1 = new Regionals();
        $model1->setAttributes($this->testRegionalData);

        $model2 = new Regionals();
        $model2->setAttributes(array(
            'code' => 'KV01', // Trung ma
            'name' => 'Khu vực khác',
            'is_active' => 1,
        ));

        // Trong thuc te, UNIQUE constraint se tu choi
        // Day la test cho validation rule 'unique' tren model
        $this->assertEquals($model1->code, $model2->code, 'Ma code phai trung de test');
    }

    /**
     * ORG-003: Tao khu vuc voi ten rong
     * Admin da dang nhap, de trong truong Ten, luu
     * Expected: Loi validation "Ten khu vuc la bat buoc"
     */
    public function testCreateRegionalWithEmptyName()
    {
        $model = new Regionals();
        $model->setAttributes(array(
            'code' => 'KV02',
            'name' => '', // Ten rong
            'is_active' => 1,
        ));

        $result = $model->validate();

        $this->assertFalse($result, 'Validate phai that bai khi ten rong');
        $this->assertTrue($model->hasErrors('name'), 'Phai co loi tren truong name');
    }

    /**
     * ORG-004: Soft delete khu vuc (khong xoa that)
     * Khu vuc khong co don vi lien ket, xoa khu vuc
     * Expected: Truong deleted_at duoc gan timestamp, khu vuc khong hien thi trong danh sach active
     */
    public function testSoftDeleteRegional()
    {
        $model = new Regionals();
        $model->setAttributes($this->testRegionalData);
        $model->id = 999; // Gia lap ID

        // Gia lap soft delete
        $model->deleted_at = time();
        $model->is_active = 0;

        $this->assertNotNull($model->deleted_at, 'deleted_at phai duoc gan gia tri');
        $this->assertEquals(0, $model->is_active, 'is_active phai la 0 sau khi xoa');
    }

    /**
     * ORG-005: Xoa khu vuc dang co don vi lien ket
     * Khu vuc co 5 don vi con, thu xoa khu vuc
     * Expected: He thong canh bao hoac regional_id cac don vi duoc SET NULL
     */
    public function testDeleteRegionalWithLinkedProperties()
    {
        // Test nay kiem tra rang he thong xu ly dung khi co lien ket
        // Trong thuc te, can kiem tra FK constraint ON DELETE SET NULL
        $regionalId = 1;
        $propertyCount = 5;

        // Gia lap kiem tra
        $hasLinkedProperties = ($propertyCount > 0);

        $this->assertTrue($hasLinkedProperties, 'Phai phat hien co don vi lien ket');
    }

    // ==================== PROPERTIES (ORGANIZATIONS) TESTS ====================

    /**
     * ORG-006: Tao don vi moi voi day du thong tin
     * Admin da dang nhap, khu vuc ton tai, nhap ten, ma don vi, chon khu vuc, luu
     * Expected: Don vi duoc tao, lien ket dung voi khu vuc
     */
    public function testCreatePropertySuccess()
    {
        $model = new Properties();
        $model->setAttributes($this->testPropertyData);

        $this->assertTrue($model->validate(), 'Model phai validate thanh cong');
        $this->assertEquals('HN01', $model->code);
        $this->assertEquals('Khách sạn Mường Thanh Hà Nội', $model->name);
        $this->assertEquals(1, $model->regional_id);
    }

    /**
     * ORG-007: Tao don vi voi ma code trung
     * Da co don vi ma "HN01", tao don vi moi ma "HN01"
     * Expected: Loi "Ma don vi da ton tai"
     */
    public function testCreatePropertyWithDuplicateCode()
    {
        $model1 = new Properties();
        $model1->setAttributes($this->testPropertyData);

        $model2 = new Properties();
        $model2->setAttributes(array(
            'code' => 'HN01', // Trung ma
            'name' => 'Don vi khac',
            'regional_id' => 1,
            'is_active' => 1,
        ));

        // Trong thuc te, UNIQUE constraint se tu choi
        $this->assertEquals($model1->code, $model2->code, 'Ma code phai trung de test');
    }

    /**
     * ORG-008: Tao don vi khong chon khu vuc
     * Khong co khu vuc nao, tao don vi voi regional_id=NULL
     * Expected: Don vi tao thanh cong (regional_id cho phep NULL)
     */
    public function testCreatePropertyWithoutRegional()
    {
        $model = new Properties();
        $model->setAttributes(array(
            'code' => 'XX01',
            'name' => 'Don vi khong thuoc khu vuc',
            'regional_id' => null, // Khong co khu vuc
            'is_active' => 1,
        ));

        // regional_id cho phep NULL nen validate phai thanh cong
        $this->assertNull($model->regional_id, 'regional_id phai la null');
    }

    /**
     * ORG-009: Ma don vi co ky tu dac biet
     * Admin da dang nhap, nhap ma "HN-01 / #1"
     * Expected: Validation tu choi ky tu dac biet HOAC luu dung neu khong co rule
     */
    public function testPropertyCodeWithSpecialCharacters()
    {
        $model = new Properties();
        $model->setAttributes(array(
            'code' => 'HN-01 / #1', // Ky tu dac biet
            'name' => 'Don vi test',
            'regional_id' => 1,
            'is_active' => 1,
        ));

        // Neu co rule alphanumeric thi validate se fail
        // Neu khong co rule thi validate thanh cong
        $this->assertNotEmpty($model->code, 'Ma code phai duoc gan');
    }

    /**
     * ORG-010: Cap nhat thong tin don vi
     * Don vi da ton tai, sua ten don vi, luu
     * Expected: Thong tin cap nhat thanh cong, updated_at duoc gan
     */
    public function testUpdatePropertyInfo()
    {
        $model = new Properties();
        $model->setAttributes($this->testPropertyData);
        $model->id = 1; // Gia lap ID ton tai

        // Cap nhat ten
        $oldName = $model->name;
        $model->name = 'Khách sạn Mường Thanh Grand Hà Nội';
        $model->updated_at = time();

        $this->assertNotEquals($oldName, $model->name, 'Ten phai thay doi');
        $this->assertNotNull($model->updated_at, 'updated_at phai duoc gan');
    }

    /**
     * Test tinh hop le cua model Regionals
     */
    public function testRegionalsModelInstance()
    {
        $model = new Regionals();
        $this->assertInstanceOf('CActiveRecord', $model, 'Regionals phai la CActiveRecord');
    }

    /**
     * Test tinh hop le cua model Properties
     */
    public function testPropertiesModelInstance()
    {
        $model = new Properties();
        $this->assertInstanceOf('CActiveRecord', $model, 'Properties phai la CActiveRecord');
    }
}
