# Permission & Menu Structure

## Danh sách Menu Chính Hệ Thống Admin

### 1. Dashboard
| Controller | Mô tả |
|------------|-------|
| `dashboard` | Tổng quan thống kê |

---

### 2. Quản lý Sự kiện (Event Management)
| Controller | Mô tả |
|------------|-------|
| `event` | Quản lý sự kiện đại hội |
| `eventAgenda` | Chương trình đại hội |
| `eventContent` | Nội dung hoạt động trong sự kiện |
| `eventUnit` | Đơn vị tham gia sự kiện |
| `registrationPeriod` | Khung thời gian đăng ký |

---

### 3. Quản lý Đăng ký (Registration)
| Controller | Mô tả |
|------------|-------|
| `registration` | Phiếu đăng ký của đơn vị |
| `registrationDetail` | Chi tiết đăng ký theo nội dung |
| `attendee` | Người tham dự |
| `badge` | Thẻ tham dự (in QR code) |

---

### 4. Quản lý Đơn vị & Tài khoản
| Controller | Mô tả |
|------------|-------|
| `organization` | Danh sách đơn vị |
| `unitAccount` | Tài khoản đơn vị (đăng ký danh sách) |
| `staff` | Nhân viên (sync từ SMILE hoặc CRUD) |
| `user` | Người dùng nội bộ (Admin, HR, BTC) |
| `role` | Danh mục vai trò |

---

### 5. Quản lý Thi Nghiệp vụ (Competition)
| Controller | Mô tả |
|------------|-------|
| `competition` | Cuộc thi nghiệp vụ |
| `competitionRound` | Các vòng thi |
| `competitionRegistration` | Đăng ký thi + số báo danh |
| `competitionRoundResult` | Kết quả từng vòng |

---

### 6. Quản lý Thể thao (Sports)
| Controller | Mô tả |
|------------|-------|
| `sport` | Môn thể thao |
| `sportTeam` | Đội thi đấu |
| `sportTeamMember` | Thành viên đội |
| `sportStage` | Giai đoạn thi đấu (vòng loại/chung kết) |
| `sportMatch` | Trận đấu |
| `sportMatchResult` | Kết quả trận đấu |

---

### 7. Quản lý Thi Sắc đẹp (Beauty Contest / Miss)
| Controller | Mô tả |
|------------|-------|
| `beautyContest` | Cuộc thi Miss |
| `beautyContestant` | Thí sinh thi Miss |
| `beautyRound` | Vòng thi (áo dài, bikini, tài năng...) |
| `beautyScore` | Điểm chấm từng vòng |

---

### 8. Quản lý Văn nghệ (Talent Show)
| Controller | Mô tả |
|------------|-------|
| `talentShow` | Cuộc thi văn nghệ |
| `talentCategory` | Thể loại (đơn ca, tốp ca, múa...) |
| `talentEntry` | Tiết mục đăng ký |
| `talentEntryMember` | Thành viên tiết mục |
| `talentScore` | Điểm chấm văn nghệ |

---

### 9. Quản lý Bữa ăn (Meal)
| Controller | Mô tả |
|------------|-------|
| `meal` | Các bữa ăn |
| `mealTable` | Bàn ăn |
| `mealAttendee` | Phân bổ người vào bàn |
| `mealCutoff` | Báo cắt ăn |
| `mealCheckin` | Check-in bữa ăn |

---

### 10. Quản lý Tiệc (Banquet)
| Controller | Mô tả |
|------------|-------|
| `banquetEvent` | Sự kiện tiệc |
| `banquetTable` | Bàn tiệc (sơ đồ) |
| `banquetSeat` | Phân chỗ ngồi |

---

### 11. Danh mục & Cấu hình
| Controller | Mô tả |
|------------|-------|
| `content` | Nội dung hoạt động (sports, miss, talent...) |
| `transport` | Phương tiện di chuyển |

---

### 12. Báo cáo & Audit
| Controller | Mô tả |
|------------|-------|
| `report` | Báo cáo tổng hợp |
| `auditLog` | Lịch sử thay đổi |

---

## Permission Format

Mỗi controller có 4 quyền CRUD:
```
"controller": "C R U D"
```

| Position | Operation | Actions |
|----------|-----------|---------|
| 0 | **C**reate | create, store |
| 1 | **R**ead | index, view, list, export |
| 2 | **U**pdate | edit, update |
| 3 | **D**elete | delete, destroy |

**Ví dụ:**
```json
{
  "attendee": "1 1 1 0",    // Có quyền tạo, xem, sửa; không có quyền xóa
  "badge": "1 0 0 0",       // Chỉ có quyền tạo (generate badge)
  "report": "0 1 0 0"       // Chỉ có quyền xem báo cáo
}
```

---

## Role-Based Default Permissions

### Admin (role=admin)
Toàn quyền tất cả controllers: `"1 1 1 1"`

### HR (role=hr)
| Controller | Permission | Ghi chú |
|------------|------------|---------|
| registration | `1 1 1 0` | Phê duyệt, không xóa |
| attendee | `1 1 1 1` | Toàn quyền |
| badge | `1 1 0 0` | Tạo/xem thẻ |
| organization | `0 1 0 0` | Chỉ xem |
| report | `0 1 0 0` | Chỉ xem |

### Competition Organizer (role=competition_organizer)
| Controller | Permission |
|------------|------------|
| competition | `1 1 1 1` |
| competitionRound | `1 1 1 1` |
| competitionRegistration | `1 1 1 0` |
| competitionRoundResult | `1 1 1 0` |
| attendee | `0 1 0 0` |

### Sports Organizer (role=sports_organizer)
| Controller | Permission |
|------------|------------|
| sport | `1 1 1 1` |
| sportTeam | `1 1 1 1` |
| sportTeamMember | `1 1 1 1` |
| sportMatch | `1 1 1 1` |
| sportMatchResult | `1 1 1 0` |
| attendee | `0 1 0 0` |

### Banquet Organizer (role=banquet_organizer)
| Controller | Permission |
|------------|------------|
| banquetEvent | `1 1 1 1` |
| banquetTable | `1 1 1 1` |
| banquetSeat | `1 1 1 1` |
| meal | `1 1 1 1` |
| mealTable | `1 1 1 1` |
| attendee | `0 1 0 0` |

---

## Menu Structure (Sidebar)

```
├── Dashboard
│
├── Sự kiện
│   ├── Danh sách sự kiện
│   ├── Chương trình
│   └── Khung đăng ký
│
├── Đăng ký
│   ├── Phiếu đăng ký
│   ├── Người tham dự
│   └── Thẻ tham dự
│
├── Đơn vị
│   ├── Danh sách đơn vị
│   ├── Tài khoản đơn vị
│   └── Nhân viên
│
├── Thi nghiệp vụ
│   ├── Cuộc thi
│   ├── Vòng thi
│   ├── Đăng ký thi
│   └── Kết quả
│
├── Thể thao
│   ├── Môn thi đấu
│   ├── Đội thi đấu
│   ├── Lịch thi đấu
│   └── Kết quả
│
├── Miss & Văn nghệ
│   ├── Thi sắc đẹp
│   ├── Văn nghệ
│   └── Điểm chấm
│
├── Bữa ăn & Tiệc
│   ├── Bữa ăn
│   ├── Báo cắt ăn
│   ├── Sự kiện tiệc
│   └── Sơ đồ bàn
│
├── Báo cáo
│   ├── Thống kê đăng ký
│   ├── Thống kê thể thao
│   └── Xuất Excel
│
├── Danh mục
│   ├── Vai trò
│   ├── Nội dung hoạt động
│   └── Phương tiện
│
└── Hệ thống
    ├── Người dùng
    └── Lịch sử thao tác
```
