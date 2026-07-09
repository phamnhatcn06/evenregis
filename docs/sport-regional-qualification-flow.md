# Luồng Phân cụm & Chỉ tiêu Thể thao

## Tổng quan

Hệ thống hỗ trợ phân chia các đội/VĐV theo cụm (regional) và gộp cụm khi cần thiết để thi đấu vòng loại, sau đó chọn đội/VĐV vào vòng chung kết (VCK).

## Database Schema

### Bảng có sẵn
| Bảng | Mục đích |
|------|----------|
| `regionals` | Danh mục cụm gốc (Cụm 1, Cụm 2...) |
| `event_sports` | Môn thể thao của sự kiện |
| `sport_stages` | Vòng đấu (qualification/playoff/final) |
| `sport_stage_teams` | Đội tham gia từng vòng |

### Bảng mới
| Bảng | Mục đích |
|------|----------|
| `sport_regional_groups` | Nhóm cụm (gộp nhiều regional) + chỉ tiêu |
| `sport_regional_group_members` | Regional nào thuộc nhóm nào |

## Cấu trúc dữ liệu

```
event_sports (môn)
    └── sport_regional_groups (nhóm cụm + quota)
            └── sport_regional_group_members (regional thuộc nhóm)
                    └── regionals (cụm gốc)
```

## Các loại qualification_method

| Method | Mô tả | Ví dụ |
|--------|-------|-------|
| `direct` | Đi thẳng VCK, không qua vòng loại | VIP, đặc cách |
| `elimination` | Đấu loại, quota có thể là 0.5/1/1.5/2/2.5... | Bóng đá, cầu lông |
| `time_based` | Tổng hợp thời gian, lấy top N | Bơi, chạy |

## Logic xử lý Quota 0.5

Quota 0.5 nghĩa là cần đấu **playoff** giữa các nhóm:

```
Ví dụ: 
- Nhóm A (Cụm 1+2+3): quota = 2.5 → lấy 2 đội chắc chắn, đội hạng 3 đấu playoff
- Nhóm B (Cụm 4+5): quota = 2.5 → lấy 2 đội chắc chắn, đội hạng 3 đấu playoff
→ 2 đội hạng 3 đấu với nhau → thắng đi VCK
→ Tổng: 2 + 2 + 1 = 5 đội vào VCK
```

### Công thức tính:
```php
$guaranteed = floor($quotaValue);     // Suất chắc chắn
$hasPlayoff = ($quotaValue - $guaranteed) == 0.5;
$playoffRank = $hasPlayoff ? $guaranteed + 1 : null;
```

## Luồng nghiệp vụ

```
┌─────────────────────────────────────────────────────────────────┐
│ 1. SETUP - Thiết lập môn thi đấu                                │
├─────────────────────────────────────────────────────────────────┤
│ • Tạo môn trong event_sports                                    │
│ • Xác định số đội/VĐV mỗi cụm (regional)                        │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│ 2. MERGE - Gộp cụm (nếu cần)                                    │
├─────────────────────────────────────────────────────────────────┤
│ • BTC quyết định gộp cụm nào với nhau                           │
│ • Tạo sport_regional_groups với group_code (A, B, C...)         │
│ • Thêm regional vào sport_regional_group_members                │
│ • Ví dụ: Cụm 1+2+3 → Nhóm A, Cụm 4+5 → Nhóm B                   │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│ 3. QUOTA - Phân chỉ tiêu                                        │
├─────────────────────────────────────────────────────────────────┤
│ • Gán quota_value cho từng nhóm (2.5, 1.5, 1.0...)              │
│ • Chọn qualification_method (elimination/time_based/direct)     │
│ • Cập nhật total_participants                                   │
│ • Ví dụ: Nhóm A = 2.5, Nhóm B = 2.5, Cụm 6 = 1.5, Nhóm C = 1.5  │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│ 4. QUALIFICATION - Vòng loại                                    │
├─────────────────────────────────────────────────────────────────┤
│ A) elimination:                                                 │
│    • Thi đấu loại trong từng nhóm                               │
│    • Xếp hạng → sport_stage_teams.final_rank                    │
│    • Top N đội (N = floor(quota)) → qualified                   │
│    • Đội hạng N+1 (nếu có 0.5) → is_playoff = 1                 │
│                                                                 │
│ B) time_based (bơi/chạy):                                       │
│    • Ghi nhận thời gian → sport_stage_teams.time_result         │
│    • Tổng hợp tất cả, sắp xếp theo time_result                  │
│    • Lấy top_n người nhanh nhất → qualified                     │
│                                                                 │
│ C) direct:                                                      │
│    • Đi thẳng VCK, không cần thi loại                           │
│    • entry_type = 'registered'                                  │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│ 5. PLAYOFF - Đấu play-off (nếu có suất 0.5)                     │
├─────────────────────────────────────────────────────────────────┤
│ • Lấy các đội có is_playoff = 1                                 │
│ • Tạo trận đấu playoff trong sport_matches                      │
│ • Người thắng → qualified, entry_type = 'playoff_winner'        │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│ 6. FINAL - Vòng chung kết                                       │
├─────────────────────────────────────────────────────────────────┤
│ • Tạo sport_stages với stage_type = 'final'                     │
│ • Thêm các đội qualified vào sport_stage_teams                  │
│ • Tiến hành thi đấu VCK                                         │
└─────────────────────────────────────────────────────────────────┘
```

## Ví dụ: Bóng bàn Đôi nam (8 đội vào VCK)

### Bước 1: Gộp cụm
```sql
-- Nhóm A: Cụm 1+2+3
INSERT INTO sport_regional_groups VALUES 
(1, 101, 'A', 'Cụm 1+2+3', 7, 2.5, 'elimination', NULL, NULL, NOW(), NOW(), NULL);

INSERT INTO sport_regional_group_members VALUES
(1, 1, 1, UNIX_TIMESTAMP()),  -- Cụm 1
(2, 1, 2, UNIX_TIMESTAMP()),  -- Cụm 2
(3, 1, 3, UNIX_TIMESTAMP());  -- Cụm 3

-- Nhóm B: Cụm 4+5
INSERT INTO sport_regional_groups VALUES 
(2, 101, 'B', 'Cụm 4+5', 7, 2.5, 'elimination', NULL, NULL, NOW(), NOW(), NULL);

-- Cụm 6 (đơn lẻ)
INSERT INTO sport_regional_groups VALUES 
(3, 101, 'C', 'Cụm 6', 3, 1.5, 'elimination', NULL, NULL, NOW(), NOW(), NULL);

-- Nhóm D: Cụm 7+8
INSERT INTO sport_regional_groups VALUES 
(4, 101, 'D', 'Cụm 7+8', 5, 1.5, 'elimination', NULL, NULL, NOW(), NOW(), NULL);
```

### Bước 2: Tính toán
```
Nhóm A: 2.5 → 2 chắc + 1 playoff
Nhóm B: 2.5 → 2 chắc + 1 playoff
Nhóm C: 1.5 → 1 chắc + 1 playoff
Nhóm D: 1.5 → 1 chắc + 1 playoff

Tổng chắc chắn: 2 + 2 + 1 + 1 = 6 đội
Playoff: 4 đội đấu lấy 2 → 6 + 2 = 8 đội vào VCK ✓
```

## Ví dụ: Bơi (time_based)

```sql
-- Tất cả cụm gộp thành 1 nhóm, lấy top 10 theo thời gian
INSERT INTO sport_regional_groups VALUES 
(5, 102, 'ALL', 'Tất cả cụm', 50, 10, 'time_based', 10, NULL, NOW(), NOW(), NULL);

-- Ghi nhận thời gian từng VĐV
UPDATE sport_stage_teams SET time_result = 58230 WHERE team_id = 1;  -- 58.23s
UPDATE sport_stage_teams SET time_result = 59100 WHERE team_id = 2;  -- 59.10s

-- Lấy top 10 nhanh nhất
SELECT * FROM sport_stage_teams 
WHERE stage_id = ? 
ORDER BY time_result ASC 
LIMIT 10;
```

## Use Cases

| UC | Mô tả |
|----|-------|
| UC-Q1 | Tạo nhóm cụm cho môn thể thao |
| UC-Q2 | Thêm regional vào nhóm |
| UC-Q3 | Gán chỉ tiêu (quota) cho nhóm |
| UC-Q4 | Chọn phương thức vòng loại |
| UC-Q5 | Ghi nhận kết quả vòng loại |
| UC-Q6 | Xác định đội đi playoff |
| UC-Q7 | Tạo trận playoff |
| UC-Q8 | Đánh dấu đội qualified vào VCK |
| UC-Q9 | Ghi nhận thời gian (time_based) |
| UC-Q10 | Lấy top N theo thời gian |

## API Endpoints (đề xuất)

```
GET    /api/event-sports/{id}/regional-groups     - Danh sách nhóm cụm
POST   /api/event-sports/{id}/regional-groups     - Tạo nhóm cụm
PUT    /api/regional-groups/{id}                  - Cập nhật nhóm
DELETE /api/regional-groups/{id}                  - Xóa nhóm

POST   /api/regional-groups/{id}/members          - Thêm regional vào nhóm
DELETE /api/regional-groups/{id}/members/{rid}    - Xóa regional khỏi nhóm

GET    /api/regional-groups/{id}/standings        - Bảng xếp hạng trong nhóm
POST   /api/regional-groups/{id}/qualify          - Xác nhận đội đi tiếp
```

## Migration File

Xem: `docs/migrations/sport_regional_groups.sql`
