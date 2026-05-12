# Tài Liệu Phân Tích Thiết Kế Hệ Thống — Quản Lý Sự Kiện Đại Hội

---

## 1. Tổng Quan Hệ Thống

### 1.1 Mục đích & Phạm vi

Hệ thống quản lý toàn bộ vòng đời của một sự kiện đại hội tập trung ~600 người tham dự từ nhiều đơn vị khác nhau, bao gồm:

- Quản lý đăng ký tham dự theo đơn vị trong khung thời gian quy định
- Phê duyệt danh sách tập trung từ HO (Head Office)
- Cấp và in thẻ tham dự có QR Code
- Quản lý thi nghiệp vụ, thi đấu thể thao, tiệc và bữa ăn
- Cung cấp thông tin lịch trình qua quét QR

### 1.2 Constraints & Assumptions

| Ràng buộc                  | Chi tiết                                                    |
| -------------------------- | ----------------------------------------------------------- |
| Tech stack                 | **Yii1 PHP Framework** (PHP 5.6 — 7.4)                      |
| Quy mô                     | ~600 người tham dự, ~50–100 đơn vị                          |
| Thời gian sử dụng cao điểm | Ngày đăng ký, ngày in thẻ, ngày diễn ra sự kiện             |
| QR Code                    | Được quét bằng bất kỳ thiết bị nào (không cần app riêng)    |
| In thẻ                     | Xuất ảnh PNG/JPG theo kích thước chuẩn (85×54mm — thẻ CR80) |
| Đơn vị                     | Mỗi đơn vị có đúng 1 tài khoản đăng ký                      |

---

## 2. Actors & Use Cases

### 2.1 Actors

| Actor                      | Tài khoản                                                             | Quyền hạn                            |
| -------------------------- | --------------------------------------------------------------------- | ------------------------------------ |
| **Admin HO**               | `users` (role=admin)                                                  | Toàn quyền hệ thống                  |
| **Nhân sự HO (HR)**        | `users` (role=hr)                                                     | Phê duyệt danh sách, quản lý đăng ký |
| **Đại diện đơn vị**        | `unit_accounts`                                                       | Đăng ký danh sách đơn vị, upload ảnh |
| **Trưởng đoàn**            | `users` (role=team_lead) hoặc `attendees` có flag `is_team_lead=true` | Báo cắt ăn cho đoàn                  |
| **BTC Thi nghiệp vụ**      | `users` (role=competition_organizer)                                  | Quản lý thi NV, cấp số báo danh      |
| **BTC Thể thao**           | `users` (role=sports_organizer)                                       | Quản lý lịch đấu, kết quả            |
| **BTC Tiệc**               | `users` (role=banquet_organizer)                                      | Quản lý sơ đồ bàn, phân chỗ          |
| **Người tham dự (Public)** | Không cần tài khoản                                                   | Quét QR xem thông tin                |

### 2.2 Use Case Map

[Đại diện đơn vị]
├── UC01: Đăng nhập tài khoản đơn vị
├── UC02: Tạo bản đăng ký danh sách tham dự ( chưa cần điền danh sách chi tiết, chỉ đăng ký các môn tham dự)  
 ├── UC03: Nhập danh sách người tham dự (tên, chức danh, ảnh)
├── UC04: Chỉnh sửa danh sách (khi status = draft)
├── UC05: Nộp đăng ký
└── UC06: Xem trạng thái phê duyệt

[Admin HO / HR]
├── UC06: Xem tất cả đăng ký
├── UC07: Phê duyệt / Từ chối đăng ký (kèm lý do)
├── UC08: Chỉnh sửa thông tin người tham dự sau phê duyệt
├── UC09: Gán vai trò cho người tham dự
├── UC10: Tạo/xuất thẻ tham dự theo lô
├── UC11: Gán trưởng đoàn cho từng đơn vị
└── UC12: Dashboard tổng hợp

[Trưởng đoàn]
├── UC13: Xem danh sách thành viên đoàn mình
├── UC14: Báo cắt ăn từng người
└── UC15: Báo cắt ăn cả đoàn (bulk)

[BTC Thi nghiệp vụ]
├── UC16: Tạo cuộc thi và các vòng thi
├── UC17: Cấp số báo danh (tự động hoặc thủ công)
├── UC18: Xuất danh sách thí sinh + số báo danh
└── UC19: Quản lý lịch thi từng vòng

[BTC Thể thao]
├── UC20: Tạo các môn thi đấu
├── UC21: Tạo lịch thi đấu (giải đấu, vòng bảng, knockout)
├── UC22: Cập nhật kết quả trận đấu
└── UC23: Xếp hạng và bảng điểm

[BTC Tiệc]
├── UC24: Tạo sự kiện tiệc
├── UC25: Thiết lập sơ đồ bàn (số bàn, vị trí, capacity)
├── UC26: Phân bổ người vào bàn/ghế
└── UC27: Xem sơ đồ tổng quan

[Người tham dự — Public]
├── UC28: Quét QR → Xem thông tin cá nhân
├── UC29: Quét QR → Xem agenda đại hội
├── UC30: Quét QR → Xem lịch thi nghiệp vụ của mình
└── UC31: Quét QR → Xem lịch thi đấu thể thao đơn vị mình

## 3. Database Schema

### 3.1 Danh sách tất cả bảng

<!==== DATA CENTER ===>

- units:

* Portal bắn API danh sách
* API endpoint tự có cơ chế đồng bộ.

- staff

* Nhân viên của Tập đoàn thì lấy từ SMILE (giống quản lý đào tạo)
* Nhân viên của Tập đoàn nhưng chưa dùng SMILE (CRUD)
* Nhân viên ngoài (CRUD)
  => có thể thêm tính năng import.

Bảng như hiện tại

- Phòng ban, chức vụ (như hiện tại)(nếu là SMILE thì không cho sửa)
  Đối với đơn vị ngoài thì có CRUD.

- contents (Nội dung tại sự kiện) "Thể thao, Miss, Nghiệp vụ...."
  - code (SP_Code)
  - name
  - description
  - status
- sports (Thể thao ) - Lưu dạng cha con (Bóng đá ( root = 0) ==> Bóng đá nam , Bóng đá nữ.....(parent_code = Bóng đá))
  - content_code
  - name
  - description
  - status
  - document
  - parent_code
- content_rounds (Các vòng thể thao của các môn)
  - round_code
  - content_code
  - status
- event_roles ( Vai trò trong đại hội) + name + code + description + status + created_at + updated_at
  ('Hỗ trợ đại hội', 'support'),
  ('Thi thể thao', 'sports'),
  ('Thi nghiệp vụ', 'competition'),
  ('Giám đốc', 'director'),
  ('Phó Giám đốc', 'deputy_director'),
  ('Khách mời', 'guest'),
  ('Trưởng đoàn', 'team_lead');
  ('Ban tổ chức', 'btc');

- transport (Phương tiện di chuyển)
  - code
  - name
  - description
  - status

<!==== END DATA CENTER ===>

<!==== TẠO THÔNG TIN CHUNG SỰ KIỆN ====>

- events:
  - code
  - name
  - fromdate
  - todate
  - description
  - status

- event_units
  - event_code
  - unit_code
  - status
  - description

- event_contents (Sự kiện có những nội dung nào)
  - code
  - event_code
  - content_code
  - status
- event_sports (Sự kiện cho thi đấu những nội dung nào?)
  - event_code
  - sport_code

- event_competition (sự kiện thi những nghiệp vụ nào)

<!==== END TẠO THÔNG TIN CHUNG SỰ KIỆN ====>

<!==== TẠO PHIẾU ĐĂNG KÝ THAM DỰ ĐẠI HỘI ====>

- registrations (Phiếu đăng ký của đơn vị) - Đơn vị điền các thông tin + upload các tài liệu được phê duyệt tại đơn vị
  - code
  - event_code
  - unit_code
  - relation_unit_code (Đơn vị liên quân)
  - document
  - status
  - approve_user_code
  - approve_at
  - comment (note)
  - regis_user_code (người tạo)
- registration_detail: (Chi tiết phiếu đăng ký)
  - registration_code
  - event_role_code
  - quantity
  - content_code (Lấy danh sách các nội dung có trong sự kiện hiện tại)
  - sport_code (Lấy danh sách các nội dung thể thao ở bảng event_sports - Nếu lựa chọn content_code là thể thao thì hiển thị chọn nội dung nào)
  - competition_code ( Nếu là thi nghiệp vụ)
  - comment (note)
  - status

<!==== ĐĂNG KÝ DANH SÁCH NHÂN VIÊN THAM GIA ĐẠI HỘI (Sau khi đăng ký mới chia các bộ môn chính thức, từ số lượng thì cho đăng ký)====>

- attendees
  - event_code
  - unit_code
  - staff_code
  - event_role_code
  - image
  - full_image
  - registration_code
  - check_in_date
  - check_out_date
  - transport_code
  - qr_code_number
  - status
  - created_at
  - updated_at
  - deleted_at
  - approve_user_code
  - approve_at
  - comment

<!==== END ĐĂNG KÝ ====>
<!==== MEAL ====>

- meals (bữa ăn) + code + event_code + name + meal_date + status + created_at + updated_at

- meal_tables + code + meal_code + name + capacity ( số lượng chỗ ngồi) + status + created_at + updated_at
- meal_attendees + code + meal_code + attendee_code + table_code + status
- meal_checkins + code + meal_code + attendee_code + check_in_time + status + created_at + updated_at
- meal_cutoffs + code + meal_code + attendee_code + reason + created_by + created_at + approve_by + approve_at

<!==== END MEAL ====>
<!==== SPORT ====>

- sport_teams
  - code
  - name
  - event_code
  - sport_code
  - unit_code (có thể nhiều code)
  - status
  - created_at
  - updated_at

- sport_team_members
  - code
  - sport_team_code
  - attendee_code
  - name
  - image
  - comment (note)
  - status
  - created_at
  - updated_at
- sport_matchs
  - code
  - sport_code
  - event_code
  - description
  - content_round_code
  - team_a_code
  - team_b_code
  - match_time
  - location
  - status
  - comment (note)
  - created_at
  - updated_at
  - final_score ( kết quả chung cuộc)
- sport_match_results
  - match_code
  - score_a
  - score_b
  - comment
  - status
  - created_by
  - created_at
  - updated_at

<!==== END SPORT ====>
