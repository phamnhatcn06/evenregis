<?php

class m20260516_000001_add_attendee_documents extends CDbMigration
{
    public function up()
    {
        // 1. Thêm cột file đính kèm vào attendees
        $this->addColumn('attendees', 'cccd_front_path', 'VARCHAR(500) COMMENT "Ảnh mặt trước CCCD" AFTER photo_full_path');
        $this->addColumn('attendees', 'cccd_back_path', 'VARCHAR(500) COMMENT "Ảnh mặt sau CCCD" AFTER cccd_front_path');
        $this->addColumn('attendees', 'portrait_path', 'VARCHAR(500) COMMENT "Ảnh chân dung 530x530px" AFTER cccd_back_path');
        $this->addColumn('attendees', 'contract_path', 'VARCHAR(500) COMMENT "File scan hợp đồng lao động" AFTER portrait_path');

        // 2. Thêm cấu hình giới hạn môn thể thao vào events
        $this->addColumn('events', 'max_sports_per_attendee', 'INT NOT NULL DEFAULT 3 COMMENT "Số môn thể thao tối đa mỗi người (tính root sports)"');

        // 3. Thêm mã phòng ban vào staff
        $this->addColumn('staff', 'department_code', 'VARCHAR(50) COMMENT "Mã phòng ban (để filter thi nghiệp vụ)" AFTER department');
        $this->createIndex('idx_staff_dept_code', 'staff', 'department_code');

        // 4. Tạo bảng competition_departments
        $this->createTable('competition_departments', array(
            'id' => 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'competition_id' => 'INT UNSIGNED NOT NULL',
            'department_code' => 'VARCHAR(50) NOT NULL COMMENT "Mã phòng ban được phép thi"',
            'created_at' => 'INT UNSIGNED',
        ), 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT="Phòng ban được phép tham gia thi nghiệp vụ"');

        $this->createIndex('uq_comp_dept', 'competition_departments', array('competition_id', 'department_code'), true);
        $this->createIndex('idx_cd_dept', 'competition_departments', 'department_code');
        $this->addForeignKey('fk_cd_comp', 'competition_departments', 'competition_id', 'competitions', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->dropForeignKey('fk_cd_comp', 'competition_departments');
        $this->dropTable('competition_departments');

        $this->dropIndex('idx_staff_dept_code', 'staff');
        $this->dropColumn('staff', 'department_code');

        $this->dropColumn('events', 'max_sports_per_attendee');

        $this->dropColumn('attendees', 'contract_path');
        $this->dropColumn('attendees', 'portrait_path');
        $this->dropColumn('attendees', 'cccd_back_path');
        $this->dropColumn('attendees', 'cccd_front_path');
    }
}
