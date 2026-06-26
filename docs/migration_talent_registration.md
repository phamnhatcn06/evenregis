# Migration: Tách đăng ký Văn nghệ sang Registration riêng

## 1. Mục tiêu

Chuyển dữ liệu văn nghệ (`talent_entries`) từ đợt đăng ký chính (period_id=1) sang đợt đăng ký văn nghệ riêng (period_id=3).

## 2. Hiện trạng vs Mục tiêu

| Hiện trạng | Mục tiêu |
|------------|----------|
| `talent_entries.registration_id` → registration đợt 1 | `talent_entries.registration_id` → registration đợt 3 |
| Chưa có registration đợt văn nghệ | Mỗi đơn vị có talent_entries sẽ có 1 registration đợt 3 |

## 3. Flow mới

```
┌─────────────────────────────────────────────────────────────────┐
│  ĐỢT 1: ĐĂNG KÝ CHÍNH (period_id=1, type=general)               │
│  ├── Registration: đăng ký người tham dự đại hội                │
│  ├── Attendees: người tham dự (thẻ, bữa ăn...)                  │
│  └── Talent entry: thông tin sơ bộ (tên, thể loại) ← XÓA       │
├─────────────────────────────────────────────────────────────────┤
│  ĐỢT 3: ĐĂNG KÝ VĂN NGHỆ (period_id=3, type=talent)             │
│  ├── Registration MỚI: đăng ký riêng cho văn nghệ              │
│  ├── Talent entries: chuyển từ đợt 1 sang đây                  │
│  └── Attendees biểu diễn:                                       │
│      • Link attendee có sẵn (đợt 1) + thêm vai trò              │
│      • HOẶC tạo attendee mới                                    │
└─────────────────────────────────────────────────────────────────┘
```

## 4. Migration Script

### 4.0 Thêm cột `alliance_org_ids` vào `talent_entries`

```sql
-- Giống sport_teams.alliance_org_ids - lưu danh sách property_id liên quân
ALTER TABLE `talent_entries`
ADD COLUMN `alliance_org_ids` VARCHAR(255) DEFAULT NULL 
COMMENT 'Danh sách property_id liên quân, phân cách bởi dấu phẩy: 1,2,3'
AFTER `is_alliance_team`;
```

### 4.0.1 Migrate dữ liệu liên quân hiện có

```sql
-- Cập nhật alliance_org_ids cho các tiết mục liên quân
-- Lấy từ bảng alliance_requests với event_content_id = 4 (văn nghệ)

UPDATE `talent_entries` te
INNER JOIN `registrations` r ON te.registration_id = r.id
SET te.alliance_org_ids = (
    SELECT GROUP_CONCAT(
        DISTINCT CASE 
            WHEN ar.requester_org_id = r.property_id THEN ar.target_org_id
            ELSE ar.requester_org_id
        END
        ORDER BY 1 SEPARATOR ','
    )
    FROM `alliance_requests` ar
    WHERE ar.event_content_id = 4  -- Văn nghệ
      AND ar.status = 2            -- Approved
      AND (ar.requester_org_id = r.property_id OR ar.target_org_id = r.property_id)
      AND ar.deleted_at IS NULL
)
WHERE te.is_alliance_team = 1
  AND te.deleted_at IS NULL
  AND r.deleted_at IS NULL;
```

### 4.1 Thêm cột `type` vào `registration_periods` (nếu chưa có)

```sql
-- Kiểm tra và thêm cột type
ALTER TABLE `registration_periods`
  ADD COLUMN `type` VARCHAR(50) NOT NULL DEFAULT 'general' 
  COMMENT 'general: đăng ký chính | talent: văn nghệ | sport: thể thao'
  AFTER `is_active`;

-- Cập nhật type cho đợt văn nghệ (period_id=3)
UPDATE `registration_periods` 
SET `type` = 'talent' 
WHERE `id` = 3;
```

### 4.2 Tạo registrations văn nghệ cho các đơn vị

```sql
-- Lấy danh sách đơn vị có talent_entries (từ registration đợt 1)
-- Tạo registration mới với period_id=3

INSERT INTO `registrations` (
    `event_id`, 
    `property_id`, 
    `relation_property_id`,
    `period_id`, 
    `submitted_by`, 
    `status`, 
    `created_at`, 
    `updated_at`
)
SELECT DISTINCT
    r.event_id,
    r.property_id,
    r.relation_property_id,
    3 AS period_id,  -- Đợt văn nghệ
    r.submitted_by,
    0 AS status,     -- Draft
    NOW(),
    NOW()
FROM `talent_entries` te
INNER JOIN `registrations` r ON te.registration_id = r.id
WHERE r.period_id = 1
  AND r.deleted_at IS NULL
  AND te.deleted_at IS NULL
  AND NOT EXISTS (
      -- Tránh tạo trùng nếu đã có registration đợt 3
      SELECT 1 FROM `registrations` r2 
      WHERE r2.property_id = r.property_id 
        AND r2.event_id = r.event_id
        AND r2.period_id = 3
        AND r2.deleted_at IS NULL
  );
```

### 4.3 Cập nhật talent_entries trỏ sang registration mới

```sql
-- Cập nhật registration_id của talent_entries sang registration đợt 3
UPDATE `talent_entries` te
INNER JOIN `registrations` r_old ON te.registration_id = r_old.id
INNER JOIN `registrations` r_new ON (
    r_new.property_id = r_old.property_id 
    AND r_new.event_id = r_old.event_id
    AND r_new.period_id = 3
    AND r_new.deleted_at IS NULL
)
SET te.registration_id = r_new.id,
    te.updated_at = NOW()
WHERE r_old.period_id = 1
  AND te.deleted_at IS NULL;
```

### 4.4 Script PHP hoàn chỉnh

```php
<?php
/**
 * Migration: Tách đăng ký văn nghệ sang registration riêng + tạo bảng talent_entry_orgs
 * 
 * Chạy: php protected/commands/shell.php migrateTalentRegistrations
 */

class MigrateTalentRegistrationsCommand extends CConsoleCommand
{
    const TALENT_PERIOD_ID = 3;
    const GENERAL_PERIOD_ID = 1;
    
    public function run($args)
    {
        $db = Yii::app()->db;
        $transaction = $db->beginTransaction();
        
        try {
            // 1. Thêm cột alliance_org_ids vào talent_entries
            $this->ensureAllianceOrgIdsColumn($db);
            
            // 2. Đảm bảo cột type tồn tại
            $this->ensureTypeColumn($db);
            
            // 3. Lấy danh sách property có talent_entries từ đợt 1
            $properties = $this->getPropertiesWithTalent($db);
            
            echo "Found " . count($properties) . " properties with talent entries\n";
            
            $regCreated = 0;
            $entriesUpdated = 0;
            
            foreach ($properties as $prop) {
                // 4. Kiểm tra đã có registration đợt 3 chưa
                $existingReg = $db->createCommand()
                    ->select('id')
                    ->from('registrations')
                    ->where('property_id = :prop_id AND event_id = :event_id AND period_id = :period_id AND deleted_at IS NULL')
                    ->queryScalar(array(
                        ':prop_id' => $prop['property_id'],
                        ':event_id' => $prop['event_id'],
                        ':period_id' => self::TALENT_PERIOD_ID,
                    ));
                
                if (!$existingReg) {
                    // 5. Tạo registration mới cho đợt văn nghệ
                    $db->createCommand()->insert('registrations', array(
                        'event_id' => $prop['event_id'],
                        'property_id' => $prop['property_id'],
                        'relation_property_id' => $prop['relation_property_id'],
                        'period_id' => self::TALENT_PERIOD_ID,
                        'submitted_by' => $prop['submitted_by'],
                        'status' => 0, // Draft
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ));
                    $existingReg = $db->getLastInsertID();
                    $regCreated++;
                    echo "Created registration #{$existingReg} for property #{$prop['property_id']}\n";
                }
                
                // 6. Cập nhật talent_entries trỏ sang registration mới
                $affected = $db->createCommand()->update(
                    'talent_entries',
                    array(
                        'registration_id' => $existingReg,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ),
                    'registration_id = :old_reg_id AND deleted_at IS NULL',
                    array(':old_reg_id' => $prop['old_registration_id'])
                );
                $entriesUpdated += $affected;
            }
            
            // 7. Migrate dữ liệu liên quân vào alliance_org_ids
            $allianceUpdated = $this->migrateAllianceOrgIds($db);
            
            $transaction->commit();
            
            echo "\n=== Migration completed ===\n";
            echo "Registrations created: {$regCreated}\n";
            echo "Talent entries updated: {$entriesUpdated}\n";
            echo "Alliance entries updated: {$allianceUpdated}\n";
            
        } catch (Exception $e) {
            $transaction->rollback();
            echo "Error: " . $e->getMessage() . "\n";
            return 1;
        }
        
        return 0;
    }
    
    private function ensureAllianceOrgIdsColumn($db)
    {
        $columns = $db->createCommand("SHOW COLUMNS FROM talent_entries LIKE 'alliance_org_ids'")->queryAll();
        if (!empty($columns)) {
            echo "Column alliance_org_ids already exists\n";
            return;
        }
        
        $db->createCommand("
            ALTER TABLE `talent_entries`
            ADD COLUMN `alliance_org_ids` VARCHAR(255) DEFAULT NULL 
            COMMENT 'Danh sách property_id liên quân, phân cách bởi dấu phẩy: 1,2,3'
            AFTER `is_alliance_team`
        ")->execute();
        
        echo "Added column alliance_org_ids to talent_entries\n";
    }
    
    private function ensureTypeColumn($db)
    {
        $columns = $db->createCommand("SHOW COLUMNS FROM registration_periods LIKE 'type'")->queryAll();
        if (empty($columns)) {
            $db->createCommand("
                ALTER TABLE `registration_periods`
                ADD COLUMN `type` VARCHAR(50) NOT NULL DEFAULT 'general' 
                COMMENT 'general: đăng ký chính | talent: văn nghệ | sport: thể thao'
                AFTER `is_active`
            ")->execute();
            echo "Added 'type' column to registration_periods\n";
        }
        
        // Cập nhật type cho đợt văn nghệ
        $db->createCommand()->update(
            'registration_periods',
            array('type' => 'talent'),
            'id = :id',
            array(':id' => self::TALENT_PERIOD_ID)
        );
    }
    
    private function getPropertiesWithTalent($db)
    {
        return $db->createCommand()
            ->select('DISTINCT r.id AS old_registration_id, r.event_id, r.property_id, r.relation_property_id, r.submitted_by')
            ->from('talent_entries te')
            ->join('registrations r', 'te.registration_id = r.id')
            ->where('r.period_id = :period_id AND r.deleted_at IS NULL AND te.deleted_at IS NULL')
            ->queryAll(true, array(':period_id' => self::GENERAL_PERIOD_ID));
    }
    
    const TALENT_EVENT_CONTENT_ID = 4; // event_content_id cho văn nghệ
    
    private function migrateAllianceOrgIds($db)
    {
        $updated = 0;
        
        // Lấy tất cả talent_entries có is_alliance_team = 1
        $allianceEntries = $db->createCommand()
            ->select('te.id AS entry_id, r.property_id')
            ->from('talent_entries te')
            ->join('registrations r', 'te.registration_id = r.id')
            ->where('te.is_alliance_team = 1 AND te.deleted_at IS NULL AND r.deleted_at IS NULL')
            ->queryAll();
        
        foreach ($allianceEntries as $entry) {
            // Lấy danh sách đơn vị liên quân từ bảng alliance_requests
            $partnerIds = $db->createCommand()
                ->select("CASE WHEN requester_org_id = :pid THEN target_org_id ELSE requester_org_id END AS partner_id")
                ->from('alliance_requests')
                ->where('event_content_id = :ecid AND status = 2 AND deleted_at IS NULL')
                ->andWhere('(requester_org_id = :pid OR target_org_id = :pid)')
                ->queryColumn(array(
                    ':ecid' => self::TALENT_EVENT_CONTENT_ID,
                    ':pid' => $entry['property_id'],
                ));
            
            if (!empty($partnerIds)) {
                $allianceOrgIds = implode(',', $partnerIds);
                $db->createCommand()->update(
                    'talent_entries',
                    array('alliance_org_ids' => $allianceOrgIds),
                    'id = :id',
                    array(':id' => $entry['entry_id'])
                );
                $updated++;
                echo "Updated entry #{$entry['entry_id']} with alliance_org_ids: {$allianceOrgIds}\n";
            }
        }
        
        return $updated;
    }
}
```

## 5. Logic thêm thành viên biểu diễn

### 5.1 Flow khi chọn thành viên

```
┌─────────────────────────────────────────────────────────────────┐
│  Modal "Thêm thành viên tiết mục"                               │
├─────────────────────────────────────────────────────────────────┤
│  ○ Chọn từ danh sách có sẵn (attendees đợt 1/3 đã approved)     │
│    → Link attendee_id có sẵn                                    │
│    → Thêm vai trò "Thi văn nghệ" vào attendee_roles             │
│                                                                 │
│  ○ Thêm người mới                                               │
│    → Tạo attendee mới trong registration đợt 3                  │
│    → Upload ảnh, thông tin cá nhân                              │
│    → Tự động gán vai trò "Thi văn nghệ"                         │
└─────────────────────────────────────────────────────────────────┘
```

### 5.2 API Endpoint

```php
/**
 * Lấy danh sách attendees có thể chọn cho tiết mục
 * GET /admin/talentEntries/getAvailableAttendees?property_id=X&event_id=Y
 */
public function actionGetAvailableAttendees()
{
    $propertyId = Yii::app()->request->getQuery('property_id');
    $eventId = Yii::app()->request->getQuery('event_id');
    
    // Lấy attendees từ CẢ đợt 1 và đợt 3, đã approved
    $attendees = Yii::app()->db->createCommand()
        ->select('a.id, a.name, a.position, a.avatar_path, r.period_id')
        ->from('attendees a')
        ->join('registrations r', 'a.registration_id = r.id')
        ->where('r.property_id = :prop_id AND r.event_id = :event_id')
        ->andWhere('r.period_id IN (1, 3)')  // Đợt chính hoặc đợt văn nghệ
        ->andWhere('a.status = 2')           // Approved
        ->andWhere('a.deleted_at IS NULL')
        ->andWhere('r.deleted_at IS NULL')
        ->queryAll(true, array(
            ':prop_id' => $propertyId,
            ':event_id' => $eventId,
        ));
    
    echo CJSON::encode(array('success' => true, 'data' => $attendees));
}

/**
 * Thêm thành viên vào tiết mục
 * POST /admin/talentEntries/addMember
 */
public function actionAddMember()
{
    $entryId = Yii::app()->request->getPost('entry_id');
    $attendeeId = Yii::app()->request->getPost('attendee_id');
    $role = Yii::app()->request->getPost('role', '');
    $isLead = Yii::app()->request->getPost('is_lead', 0);
    
    $transaction = Yii::app()->db->beginTransaction();
    
    try {
        // 1. Thêm vào talent_entry_members
        $member = new TalentEntryMembers();
        $member->entry_id = $entryId;
        $member->attendee_id = $attendeeId;
        $member->role = $role;
        $member->is_lead = $isLead;
        $member->save();
        
        // 2. Thêm vai trò "Thi văn nghệ" nếu chưa có
        $this->assignTalentRole($attendeeId);
        
        $transaction->commit();
        
        echo CJSON::encode(array('success' => true, 'message' => 'Đã thêm thành viên'));
        
    } catch (Exception $e) {
        $transaction->rollback();
        echo CJSON::encode(array('success' => false, 'message' => $e->getMessage()));
    }
}

private function assignTalentRole($attendeeId)
{
    // Lấy role_id của vai trò "Thi văn nghệ"
    $talentRoleId = Yii::app()->db->createCommand()
        ->select('id')
        ->from('roles')
        ->where("code = 'talent_performer' OR name LIKE '%văn nghệ%'")
        ->queryScalar();
    
    if (!$talentRoleId) return;
    
    // Kiểm tra đã có chưa
    $exists = AttendeeRoles::model()->exists(
        'attendee_id = :aid AND role_id = :rid AND deleted_at IS NULL',
        array(':aid' => $attendeeId, ':rid' => $talentRoleId)
    );
    
    if (!$exists) {
        $ar = new AttendeeRoles();
        $ar->attendee_id = $attendeeId;
        $ar->role_id = $talentRoleId;
        $ar->assigned_at = date('Y-m-d H:i:s');
        $ar->save();
    }
}
```

## 6. Checklist triển khai

- [ ] Backup database trước khi chạy migration
- [ ] Thêm cột `type` vào `registration_periods`
- [ ] Cập nhật `type = 'talent'` cho period_id=3
- [ ] Chạy migration script tạo registrations mới
- [ ] Verify: kiểm tra `talent_entries.registration_id` đã đúng
- [ ] Cập nhật Model `TalentEntries` để validate registration thuộc đợt talent
- [ ] Tạo API `getAvailableAttendees` và `addMember`
- [ ] Cập nhật UI modal thêm thành viên
- [ ] Test end-to-end

## 7. Rollback (nếu cần)

```sql
-- 1. Xóa dữ liệu alliance_org_ids
UPDATE `talent_entries` SET alliance_org_ids = NULL WHERE alliance_org_ids IS NOT NULL;

-- 2. Chuyển talent_entries về registration đợt 1
UPDATE `talent_entries` te
INNER JOIN `registrations` r_new ON te.registration_id = r_new.id
INNER JOIN `registrations` r_old ON (
    r_old.property_id = r_new.property_id 
    AND r_old.event_id = r_new.event_id
    AND r_old.period_id = 1
    AND r_old.deleted_at IS NULL
)
SET te.registration_id = r_old.id
WHERE r_new.period_id = 3;

-- 3. Xóa registrations đợt văn nghệ (soft delete)
UPDATE `registrations` 
SET deleted_at = NOW() 
WHERE period_id = 3;

-- 4. (Tùy chọn) Xóa cột alliance_org_ids
-- ALTER TABLE `talent_entries` DROP COLUMN `alliance_org_ids`;
```

## 8. So sánh cấu trúc Thể thao vs Văn nghệ

| Thể thao | Văn nghệ | Mô tả |
|----------|----------|-------|
| `alliances` | `alliances` | Dùng chung - liên kết 2 đơn vị |
| `alliance_requests` | `alliance_requests` | Dùng chung - yêu cầu liên quân |
| `sport_teams.is_alliance` | `talent_entries.is_alliance_team` | Cờ đội/tiết mục liên quân |
| `sport_teams.alliance_org_ids` | `talent_entries.alliance_org_ids` ✅ | Danh sách property_id liên quân |
| `sport_team_members` | `talent_entry_members` | Thành viên đội/tiết mục |

## 9. Logic khi đơn vị liên quân thêm attendee

Khi đơn vị B (trong `alliance_org_ids` của tiết mục) thêm attendee:

```php
// Kiểm tra đơn vị có quyền thêm vào tiết mục không
public function canAddMemberToEntry($propertyId, $entryId)
{
    $entry = TalentEntries::model()->with('registration')->findByPk($entryId);
    if (!$entry) return false;
    
    // Đơn vị chủ trì (owner)
    if ($entry->registration->property_id == $propertyId) {
        return true;
    }
    
    // Đơn vị liên quân
    if ($entry->is_alliance_team && $entry->alliance_org_ids) {
        $allianceIds = explode(',', $entry->alliance_org_ids);
        return in_array($propertyId, $allianceIds);
    }
    
    return false;
}
```
