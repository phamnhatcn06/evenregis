# Tài Liệu Test Case — Hệ Thống Quản Lý Sự Kiện Đại Hội

**Hệ thống:** Event Registration System (Yii 1.x PHP)  
**Phiên bản tài liệu:** 1.0  
**Ngày tạo:** 2026-05-26  
**Người tạo:** QA Engineer

---

## Mục Lục

1. [Module: Xác thực & Phân quyền (Authentication)](#1-module-xác-thực--phân-quyền)
2. [Module: Quản lý Đơn vị & Tài khoản (Organizations)](#2-module-quản-lý-đơn-vị--tài-khoản)
3. [Module: Đăng ký Tham dự (Registration)](#3-module-đăng-ký-tham-dự)
4. [Module: Người tham dự (Attendees)](#4-module-người-tham-dự)
5. [Module: Thẻ tham dự (Badges)](#5-module-thẻ-tham-dự)
6. [Module: Thi Thể thao (Sports)](#6-module-thi-thể-thao)
7. [Module: Thi Nghiệp vụ (Competition)](#7-module-thi-nghiệp-vụ)
8. [Module: Thi Sắc đẹp (Beauty Contest)](#8-module-thi-sắc-đẹp)
9. [Module: Văn nghệ (Talent Show)](#9-module-văn-nghệ)
10. [Module: Bữa ăn & Tiệc (Meal & Banquet)](#10-module-bữa-ăn--tiệc)
11. [Module: QR Code & Thông tin Công khai](#11-module-qr-code--thông-tin-công-khai)
12. [Module: Dashboard & Báo cáo](#12-module-dashboard--báo-cáo)

---

## 1. Module: Xác thực & Phân quyền

### 1.1 Đăng nhập Portal SSO (JWT)

| ID | Mô tả | Preconditions | Steps | Expected Result | Priority |
|----|-------|---------------|-------|-----------------|----------|
| AUTH-001 | Đăng nhập thành công với JWT hợp lệ từ Portal | Portal đã cấp JWT token hợp lệ, user tồn tại trong hệ thống | 1. Portal redirect đến `/auth/callback?token=<JWT>` 2. Hệ thống decode JWT 3. Tạo session | Session được tạo, redirect về Dashboard; thông tin user_id, full_name, unit_code lưu vào session | Critical |
| AUTH-002 | Đăng nhập thất bại với JWT hết hạn | JWT token đã hết hạn (`exp` < thời điểm hiện tại) | 1. Truy cập `/auth/callback?token=<JWT_expired>` | Redirect về trang lỗi, hiển thị thông báo "Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại từ Portal." | Critical |
| AUTH-003 | Đăng nhập thất bại với JWT bị giả mạo (signature sai) | JWT token có chữ ký không hợp lệ | 1. Truy cập `/auth/callback?token=<JWT_tampered>` | Hệ thống từ chối, trả về HTTP 401, không tạo session | Critical |
| AUTH-004 | Đăng nhập thất bại khi thiếu token trong URL | URL callback không có param `token` | 1. Truy cập `/auth/callback` (không có token) | Redirect về trang lỗi, hiển thị "Token không hợp lệ" | High |
| AUTH-005 | Đăng nhập thất bại với token rỗng | Token param là chuỗi rỗng | 1. Truy cập `/auth/callback?token=` | Từ chối xử lý, hiển thị lỗi | High |
| AUTH-006 | Session hết hạn sau 30 phút không hoạt động | User đã đăng nhập, không thao tác 30 phút | 1. Chờ 30 phút 2. Thực hiện bất kỳ thao tác nào | Redirect về trang login/Portal, session bị xóa | High |
| AUTH-007 | Quyền Create (C) đúng theo JWT payload | JWT có `"event": "1 1 1 1"` (C=1) | 1. Đăng nhập 2. Truy cập action tạo sự kiện | Cho phép truy cập action create | Critical |
| AUTH-008 | Quyền Read (R) bị từ chối theo JWT payload | JWT có `"event": "0 0 1 1"` (R=0) | 1. Đăng nhập 2. Truy cập action list sự kiện | Trả về HTTP 403 "Forbidden" | Critical |
| AUTH-009 | Quyền Delete (D) bị từ chối đúng cách | JWT có `"attendee": "1 1 1 0"` (D=0) | 1. Đăng nhập 2. Thử xóa attendee | Nút Xóa bị ẩn trong view; nếu gọi trực tiếp URL trả về 403 | Critical |
| AUTH-010 | Đăng nhập với token UTF-8 đặc biệt trong payload | JWT payload có full_name chứa Unicode "Nguyễn Văn A" | 1. Đăng nhập với JWT hợp lệ | Session lưu đúng full_name với ký tự tiếng Việt | Medium |
| AUTH-011 | Gọi API SSO `/api/sso/me` thành công sau đăng nhập | Đã đăng nhập thành công, token còn hiệu lực | 1. Hệ thống tự động gọi SSO API 2. Nhận profile | Profile được lưu vào localStorage phía client | High |
| AUTH-012 | Gọi API SSO thất bại (network timeout) | Mạng không ổn định | 1. Đăng nhập 2. API SSO timeout | Session vẫn được tạo từ JWT; user vẫn vào được hệ thống (degraded mode) | Medium |
| AUTH-013 | Đăng xuất (logout) xóa session đúng cách | User đang đăng nhập | 1. Click Đăng xuất | Session bị hủy, redirect về Portal, không thể truy cập trang admin nữa | High |
| AUTH-014 | Truy cập trang admin khi chưa đăng nhập | Chưa có session | 1. Truy cập trực tiếp URL admin bất kỳ | Redirect về trang login/Portal | Critical |
| AUTH-015 | JWT với permissions null hoặc thiếu key | JWT payload không có trường `permissions` | 1. Đăng nhập với JWT thiếu permissions | Hệ thống dùng quyền mặc định (tất cả D=0); không crash | High |

### 1.2 Phân quyền theo Role

| ID | Mô tả | Preconditions | Steps | Expected Result | Priority |
|----|-------|---------------|-------|-----------------|----------|
| AUTH-016 | Admin HO có toàn quyền hệ thống | User có role=admin trong JWT | 1. Đăng nhập với role admin 2. Truy cập tất cả module | Tất cả CRUD đều được phép; không bị 403 ở bất kỳ action nào | Critical |
| AUTH-017 | HR chỉ thấy chức năng phê duyệt đăng ký | User có role=hr | 1. Đăng nhập với role hr 2. Vào module Competition | Chỉ có quyền Read; không thấy nút Create/Delete trong Competition nếu không được cấp | High |
| AUTH-018 | BTC Thể thao không vào được module Thi nghiệp vụ | User có role=sports_organizer; không có quyền competition | 1. Đăng nhập 2. Truy cập `/admin/competition` | Trả về 403 hoặc không hiển thị menu | High |
| AUTH-019 | Đại diện đơn vị không truy cập được trang admin HO | unit_account đăng nhập | 1. Đăng nhập tài khoản đơn vị 2. Truy cập trang admin | Từ chối truy cập, redirect về dashboard đơn vị | Critical |

---

## 2. Module: Quản lý Đơn vị & Tài khoản

### 2.1 Quản lý Khu vực (Regionals)

| ID | Mô tả | Preconditions | Steps | Expected Result | Priority |
|----|-------|---------------|-------|-----------------|----------|
| ORG-001 | Tạo khu vực mới thành công | Admin đã đăng nhập | 1. Vào Quản lý Khu vực 2. Nhập mã "KV01", tên "Khu vực Hà Nội" 3. Lưu | Khu vực được tạo, xuất hiện trong danh sách | High |
| ORG-002 | Tạo khu vực với mã trùng lặp | Đã có khu vực mã "KV01" | 1. Tạo khu vực mới với mã "KV01" | Lỗi validation "Mã khu vực đã tồn tại" | High |
| ORG-003 | Tạo khu vực với tên rỗng | Admin đã đăng nhập | 1. Để trống trường Tên 2. Lưu | Lỗi validation "Tên khu vực là bắt buộc" | High |
| ORG-004 | Soft delete khu vực (không xóa thật) | Khu vực không có đơn vị liên kết | 1. Xóa khu vực | Trường `deleted_at` được gán timestamp; khu vực không hiển thị trong danh sách active | Medium |
| ORG-005 | Xóa khu vực đang có đơn vị liên kết | Khu vực có 5 đơn vị con | 1. Thử xóa khu vực | Hệ thống cảnh báo hoặc `regional_id` các đơn vị được SET NULL theo FK constraint | Medium |

### 2.2 Quản lý Đơn vị (Organizations)

| ID | Mô tả | Preconditions | Steps | Expected Result | Priority |
|----|-------|---------------|-------|-----------------|----------|
| ORG-006 | Tạo đơn vị mới với đầy đủ thông tin | Admin đã đăng nhập, khu vực tồn tại | 1. Nhập tên, mã đơn vị, chọn khu vực 2. Lưu | Đơn vị được tạo, liên kết đúng với khu vực | High |
| ORG-007 | Tạo đơn vị với mã code trùng | Đã có đơn vị mã "HN01" | 1. Tạo đơn vị mới mã "HN01" | Lỗi "Mã đơn vị đã tồn tại" | High |
| ORG-008 | Tạo đơn vị không chọn khu vực | Không có khu vực nào | 1. Tạo đơn vị với regional_id=NULL | Đơn vị tạo thành công (regional_id cho phép NULL) | Medium |
| ORG-009 | Mã đơn vị có ký tự đặc biệt | Admin đã đăng nhập | 1. Nhập mã "HN-01 / #1" | Validation từ chối ký tự đặc biệt HOẶC lưu đúng nếu không có rule | Medium |
| ORG-010 | Cập nhật thông tin đơn vị | Đơn vị đã tồn tại | 1. Sửa tên đơn vị 2. Lưu | Thông tin cập nhật thành công, `updated_at` được gán | Medium |

### 2.3 Quản lý Tài khoản Đơn vị (Unit Accounts)

| ID | Mô tả | Preconditions | Steps | Expected Result | Priority |
|----|-------|---------------|-------|-----------------|----------|
| ORG-011 | Tạo tài khoản đơn vị thành công | Đơn vị đã tồn tại, chưa có tài khoản | 1. Tạo tài khoản với username/password 2. Liên kết đơn vị | Tài khoản được tạo, liên kết 1-1 với đơn vị | High |
| ORG-012 | Tạo tài khoản thứ 2 cho cùng đơn vị | Đơn vị đã có tài khoản | 1. Tạo tài khoản mới cho cùng organization_id | Lỗi "Đơn vị đã có tài khoản" (UNIQUE KEY `uq_unit_accounts_org`) | High |
| ORG-013 | Đăng nhập tài khoản đơn vị thành công | Tài khoản đơn vị tồn tại, is_active=1 | 1. Nhập username/password đúng | Đăng nhập thành công, vào dashboard đơn vị | Critical |
| ORG-014 | Đăng nhập với mật khẩu sai | Tài khoản tồn tại | 1. Nhập sai mật khẩu | Hiển thị lỗi "Tên đăng nhập hoặc mật khẩu không đúng" | Critical |
| ORG-015 | Đăng nhập tài khoản bị vô hiệu hóa | is_active=0 | 1. Nhập đúng username/password | Hiển thị lỗi "Tài khoản đã bị vô hiệu hóa" | High |
| ORG-016 | Đăng nhập với username chứa SQL injection | Tài khoản tồn tại | 1. Nhập username: `admin' OR '1'='1` | Hệ thống xử lý an toàn, không bị SQL injection; trả về lỗi đăng nhập | Critical |
| ORG-017 | Đổi mật khẩu tài khoản đơn vị | Đã đăng nhập | 1. Nhập mật khẩu cũ đúng 2. Nhập mật khẩu mới 3. Lưu | Mật khẩu được cập nhật, đăng nhập lại bằng mật khẩu mới thành công | Medium |
| ORG-018 | Password_hash không lưu plaintext | Tạo tài khoản với password "123456" | 1. Kiểm tra DB field `password_hash` | Trường `password_hash` chứa chuỗi hash (bcrypt/SHA), không phải "123456" | Critical |

---

## 3. Module: Đăng ký Tham dự

### 3.1 Quản lý Đợt Đăng ký (Registration Periods)

| ID | Mô tả | Preconditions | Steps | Expected Result | Priority |
|----|-------|---------------|-------|-----------------|----------|
| REG-001 | Admin tạo đợt đăng ký hợp lệ | Admin đã đăng nhập | 1. Nhập tên đợt, start_time, end_time, max_per_org=20 2. Lưu | Đợt đăng ký được tạo thành công | High |
| REG-002 | Tạo đợt đăng ký với end_time < start_time | Admin đã đăng nhập | 1. Nhập end_time trước start_time 2. Lưu | Lỗi validation "Thời gian kết thúc phải sau thời gian bắt đầu" | High |
| REG-003 | Tạo đợt với max_per_org=0 | Admin đã đăng nhập | 1. Nhập max_per_org=0 | Lỗi validation (0 là giá trị vô nghĩa) HOẶC NULL (không giới hạn) | Medium |
| REG-004 | Tạo đợt với max_per_org âm | Admin đã đăng nhập | 1. Nhập max_per_org=-5 | Lỗi validation "Số người tối đa phải là số dương" | Medium |
| REG-005 | Đơn vị thấy đợt đăng ký đang mở | Đợt đăng ký is_active=1, trong thời hạn | 1. Đăng nhập tài khoản đơn vị 2. Xem danh sách đợt | Đợt đăng ký đang mở hiển thị, cho phép tạo phiếu | High |
| REG-006 | Đơn vị không thấy đợt đăng ký đã đóng | Đợt đăng ký end_time đã qua | 1. Đăng nhập tài khoản đơn vị | Đợt đã đóng không hiển thị hoặc hiển thị với trạng thái "Đã đóng" | High |

### 3.2 Tạo & Quản lý Phiếu Đăng ký (UC02, UC03, UC04, UC05)

| ID | Mô tả | Preconditions | Steps | Expected Result | Priority |
|----|-------|---------------|-------|-----------------|----------|
| REG-007 | Tạo phiếu đăng ký thành công (UC02) | Đơn vị đã đăng nhập, đợt đang mở | 1. Chọn sự kiện 2. Chọn đợt đăng ký 3. Lưu nháp | Phiếu được tạo với status="draft", liên kết đúng org và period | Critical |
| REG-008 | Đơn vị tạo phiếu thứ 2 trong cùng đợt | Đơn vị đã có phiếu trong đợt | 1. Tạo phiếu mới trong cùng đợt | Lỗi "Đơn vị đã có phiếu đăng ký trong đợt này" (UNIQUE KEY `uq_registrations_org_period`) | Critical |
| REG-009 | Thêm người tham dự vào phiếu draft (UC03) | Phiếu có status="draft" | 1. Thêm người tham dự với tên, chức danh 2. Lưu | Người tham dự được thêm vào attendees, liên kết với registration_id | High |
| REG-010 | Upload ảnh người tham dự hợp lệ | Phiếu draft, file ảnh JPG 300KB | 1. Upload ảnh cho người tham dự | Ảnh được lưu vào `uploads/`, đường dẫn cập nhật vào photo_path | High |
| REG-011 | Upload ảnh quá dung lượng | File ảnh >10MB | 1. Upload ảnh 10MB+ | Lỗi "File ảnh vượt quá dung lượng cho phép" | Medium |
| REG-012 | Upload file không phải ảnh (PDF, EXE) | Phiếu draft | 1. Upload file .pdf | Lỗi "Chỉ chấp nhận file ảnh JPG/PNG/GIF" | High |
| REG-013 | Chỉnh sửa thông tin người tham dự khi draft (UC04) | Phiếu status="draft", có attendee | 1. Sửa tên, chức danh 2. Lưu | Thông tin cập nhật thành công | High |
| REG-014 | Không cho chỉnh sửa khi phiếu đã submitted | Phiếu status="submitted" | 1. Thử sửa thông tin attendee | Hiển thị lỗi "Không thể chỉnh sửa phiếu đã nộp" | Critical |
| REG-015 | Nộp phiếu đăng ký (UC05) | Phiếu draft có ít nhất 1 attendee | 1. Click "Nộp đăng ký" 2. Xác nhận | Status chuyển thành "submitted", `submitted_at` được gán, thông báo thành công | Critical |
| REG-016 | Nộp phiếu rỗng (không có attendee) | Phiếu draft, chưa thêm ai | 1. Click "Nộp đăng ký" | Lỗi "Vui lòng thêm ít nhất một người tham dự trước khi nộp" | High |
| REG-017 | Nộp phiếu vượt quá max_per_org | max_per_org=5, phiếu có 7 attendees | 1. Nộp phiếu | Lỗi "Số người vượt quá giới hạn cho phép (tối đa 5 người)" | High |
| REG-018 | Xem trạng thái phê duyệt (UC06) | Phiếu đã nộp | 1. Đơn vị xem phiếu của mình | Hiển thị đúng status (draft/submitted/approved/rejected) và lý do từ chối nếu có | High |
| REG-019 | Tạo phiếu ngoài thời hạn đăng ký | end_time của đợt đã qua | 1. Thử tạo phiếu | Lỗi "Đợt đăng ký đã đóng" | High |
| REG-020 | submitted_by lấy từ SSO token (không phải local user ID) | Đã đăng nhập qua Portal SSO | 1. Tạo và nộp phiếu | `submitted_by` trong DB chứa ID từ SSO (không phải `Yii::app()->user->id`) | Critical |

### 3.3 Phê duyệt Đăng ký (UC07)

| ID | Mô tả | Preconditions | Steps | Expected Result | Priority |
|----|-------|---------------|-------|-----------------|----------|
| REG-021 | HR phê duyệt phiếu đăng ký (approve) | Phiếu status="submitted" | 1. HR chọn phiếu 2. Click "Phê duyệt" | Status chuyển thành "approved", `reviewed_by` và `reviewed_at` được gán | Critical |
| REG-022 | HR từ chối phiếu đăng ký kèm lý do (reject) | Phiếu status="submitted" | 1. HR click "Từ chối" 2. Nhập lý do 3. Xác nhận | Status chuyển thành "rejected", `rejection_reason` được lưu | Critical |
| REG-023 | Từ chối phiếu mà không nhập lý do | Phiếu status="submitted" | 1. Click "Từ chối" 2. Để trống lý do 3. Xác nhận | Lỗi "Vui lòng nhập lý do từ chối" | High |
| REG-024 | Phê duyệt phiếu đã ở status "approved" | Phiếu đã approved | 1. Thử approve lại | Hệ thống không thay đổi, hiển thị cảnh báo "Phiếu đã được phê duyệt" | Medium |
| REG-025 | Đơn vị không thể tự phê duyệt phiếu của mình | Đơn vị đã đăng nhập | 1. Truy cập URL approve phiếu của mình | Trả về 403 Forbidden | Critical |
| REG-026 | Xem tất cả đăng ký theo trạng thái (UC06) | HR đã đăng nhập, có nhiều phiếu | 1. Lọc theo status="submitted" | Chỉ hiển thị phiếu đã nộp, đúng số lượng | Medium |

---

## 4. Module: Người tham dự (Attendees)

### 4.1 Quản lý Thông tin Người tham dự

| ID | Mô tả | Preconditions | Steps | Expected Result | Priority |
|----|-------|---------------|-------|-----------------|----------|
| ATT-001 | Admin chỉnh sửa thông tin attendee sau phê duyệt (UC08) | Phiếu đã approved, attendee tồn tại | 1. Admin mở attendee 2. Sửa tên/chức danh 3. Lưu | Thông tin cập nhật, `updated_at` gán mới; audit log ghi lại | High |
| ATT-002 | QR token tự động sinh khi attendee được approved | Phiếu vừa được approved | 1. Phê duyệt phiếu | Mỗi attendee có `qr_token` duy nhất 64 ký tự | Critical |
| ATT-003 | QR token là duy nhất (không trùng giữa các attendee) | Nhiều attendee trong hệ thống | 1. Tạo 100 attendee 2. Kiểm tra qr_token | Tất cả qr_token khác nhau (UNIQUE constraint) | Critical |
| ATT-004 | Badge number được gán tự động và duy nhất | Phiếu approved | 1. Phê duyệt phiếu | `badge_number` được gán dạng "001", "002"... không trùng | High |
| ATT-005 | Gán vai trò cho người tham dự (UC09) | Attendee đã approved, role tồn tại | 1. Admin chọn attendee 2. Gán role "Trưởng đoàn" | Bản ghi trong `attendee_roles` được tạo | High |
| ATT-006 | Gán vai trò trùng lặp cho cùng attendee | Attendee đã có role "support" | 1. Gán lại role "support" cho attendee | Lỗi hoặc không tạo bản ghi mới (UNIQUE KEY `uq_attendee_role`) | Medium |
| ATT-007 | Gán trưởng đoàn cho đơn vị (UC11) | Đơn vị có nhiều attendee | 1. Admin chọn 1 attendee làm trưởng đoàn 2. Lưu | `is_team_lead=1` cho attendee đó; các attendee khác vẫn `is_team_lead=0` | High |
| ATT-008 | Soft delete attendee (is_active=0) | Admin muốn xóa attendee | 1. Admin xóa attendee | `is_active` chuyển thành 0 hoặc `deleted_at` được gán; không xóa khỏi DB | High |
| ATT-009 | Thêm attendee với tên rỗng | Phiếu draft | 1. Thêm attendee không có tên | Lỗi validation "Họ tên là bắt buộc" | High |
| ATT-010 | Thêm attendee với tên quá dài (>255 ký tự) | Phiếu draft | 1. Nhập tên 300 ký tự | Lỗi validation "Tên không được vượt quá 255 ký tự" | Medium |
| ATT-011 | Tìm kiếm attendee theo tên | Nhiều attendee trong hệ thống | 1. Tìm kiếm "Nguyễn" | Danh sách lọc đúng attendee có tên chứa "Nguyễn" | Medium |
| ATT-012 | Thông tin check-in/check-out date hợp lệ | Attendee được phê duyệt | 1. Nhập check_in_date=2026-11-01, check_out_date=2026-11-03 | Dữ liệu lưu đúng | Low |
| ATT-013 | check_out_date trước check_in_date | Admin chỉnh sửa attendee | 1. check_out_date < check_in_date | Lỗi validation | Medium |

### 4.2 Upload Tài liệu (CCCD, Hợp đồng)

| ID | Mô tả | Preconditions | Steps | Expected Result | Priority |
|----|-------|---------------|-------|-----------------|----------|
| ATT-014 | Upload ảnh CCCD mặt trước | Attendee tồn tại | 1. Upload file CCCD mặt trước JPG | File lưu vào `uploads/`, `cccd_front_path` cập nhật | Medium |
| ATT-015 | Upload file hợp đồng PDF | Attendee tồn tại | 1. Upload file .pdf | File lưu vào `uploads/`, `contract_path` cập nhật | Medium |
| ATT-016 | Upload file .exe vào trường hợp đồng | Attackker thử upload file nguy hiểm | 1. Upload file .exe | Hệ thống từ chối, hiển thị lỗi về loại file | Critical |

---

## 5. Module: Thẻ tham dự (Badges)

### 5.1 Tạo và Xuất Thẻ (UC10)

| ID | Mô tả | Preconditions | Steps | Expected Result | Priority |
|----|-------|---------------|-------|-----------------|----------|
| BAD-001 | Tạo thẻ cho 1 attendee | Attendee đã approved, có đầy đủ thông tin | 1. Chọn attendee 2. Click "Tạo thẻ" | File ảnh thẻ được tạo (85.60×53.98mm, 300dpi), `badge_generated=1`, đường dẫn lưu vào `badges.generated_path` | Critical |
| BAD-002 | Tạo thẻ theo lô (batch) | Nhiều attendee đã approved | 1. Chọn tất cả attendee 2. Tạo thẻ hàng loạt | Tất cả thẻ được tạo; báo cáo thành công/thất bại | High |
| BAD-003 | Thẻ chứa QR code đúng qr_token | Attendee có qr_token | 1. Tạo thẻ 2. Quét QR | URL trong QR dẫn đến `/frontend/attendee/view?token=<qr_token>` của đúng attendee | Critical |
| BAD-004 | Kích thước ảnh thẻ đúng chuẩn CR80 | Thẻ vừa được tạo | 1. Tạo thẻ 2. Kiểm tra kích thước file | Ảnh có tỉ lệ đúng 85.60:53.98mm, DPI=300 (tương đương 1013×638 pixel) | High |
| BAD-005 | Tạo thẻ cho attendee chưa có ảnh | Attendee chưa upload ảnh | 1. Tạo thẻ | Thẻ vẫn được tạo với ảnh mặc định/placeholder, không crash | Medium |
| BAD-006 | In thẻ lần đầu cập nhật print_count | Thẻ đã tạo | 1. Click "In thẻ" | `print_count` tăng thêm 1, `last_printed_at` cập nhật | Medium |
| BAD-007 | Tạo thẻ cho attendee chưa approved | Phiếu status="draft" | 1. Thử tạo thẻ | Lỗi "Chỉ tạo thẻ cho người tham dự đã được phê duyệt" | High |
| BAD-008 | Tái tạo thẻ (regenerate) sau khi sửa thông tin | Thẻ đã tạo, admin sửa tên attendee | 1. Sửa tên 2. Tái tạo thẻ | Thẻ mới phản ánh tên mới; file cũ bị ghi đè | Medium |
| BAD-009 | Xuất thẻ khi ảnh file bị xóa khỏi disk | `photo_path` trỏ đến file không tồn tại | 1. Tạo thẻ | Hệ thống dùng ảnh placeholder, không crash với fatal error | High |

---

## 6. Module: Thi Thể thao (Sports)

### 6.1 Quản lý Môn Thể thao (UC20)

| ID | Mô tả | Preconditions | Steps | Expected Result | Priority |
|----|-------|---------------|-------|-----------------|----------|
| SPT-001 | Tạo môn thể thao cấp gốc | BTC Thể thao đã đăng nhập | 1. Tạo môn "Bóng đá" (parent_id=NULL, type=team) | Môn được tạo thành công, hiển thị trong danh sách | High |
| SPT-002 | Tạo môn thể thao con (child) | Đã có môn "Bóng đá" | 1. Tạo "Bóng đá nam" với parent_id=ID_Bong_da | Môn con được tạo, liên kết đúng với môn cha | High |
| SPT-003 | Tạo môn với mã code trùng lặp | Môn "BD" đã tồn tại | 1. Tạo môn mới với code="BD" | Lỗi "Mã môn đã tồn tại" (UNIQUE KEY) | High |
| SPT-004 | Upload file điều lệ thi đấu (document) | Môn thể thao tồn tại | 1. Upload file PDF điều lệ | File lưu thành công, `document` path cập nhật | Medium |

### 6.2 Quản lý Đội Thi đấu

| ID | Mô tả | Preconditions | Steps | Expected Result | Priority |
|----|-------|---------------|-------|-----------------|----------|
| SPT-005 | Tạo đội thi đấu cho đơn vị | Môn thể thao tồn tại, đơn vị tồn tại | 1. Tạo đội "Đội Bóng đá HN01" cho môn Bóng đá 2. Liên kết đơn vị | Đội được tạo với organization_id đúng | High |
| SPT-006 | Tạo đội hỗn hợp (không thuộc đơn vị nào) | Môn thể thao tồn tại | 1. Tạo đội với organization_id=NULL | Đội được tạo với organization_id=NULL | Medium |
| SPT-007 | Thêm thành viên vào đội | Đội và attendee tồn tại | 1. Thêm attendee vào đội 2. Gán số áo, vị trí | Bản ghi `sport_team_members` được tạo | High |
| SPT-008 | Thêm cùng attendee vào đội 2 lần | Attendee đã là thành viên đội | 1. Thêm lại attendee | Lỗi "Thành viên đã có trong đội" (UNIQUE KEY `uq_team_member`) | High |
| SPT-009 | Chỉ có 1 thuyền trưởng (is_captain) | Đội đã có captain | 1. Đặt thêm 1 thành viên khác là captain | Hệ thống cho phép nhiều captain HOẶC giới hạn 1 (kiểm tra logic) | Medium |
| SPT-010 | Xóa thành viên khỏi đội | Thành viên trong đội | 1. Xóa thành viên | Bản ghi trong `sport_team_members` bị xóa hoặc `status=0` | Medium |

### 6.3 Quản lý Lịch Thi đấu (UC21)

| ID | Mô tả | Preconditions | Steps | Expected Result | Priority |
|----|-------|---------------|-------|-----------------|----------|
| SPT-011 | Tạo trận đấu vòng bảng | 2 đội tồn tại, môn thể thao tồn tại | 1. Tạo trận Đội A vs Đội B, loại=group, thời gian=... | Trận được tạo với status="scheduled" | High |
| SPT-012 | Tạo trận với team_a = team_b (đội đấu với chính mình) | 2 đội tồn tại | 1. Chọn team_a_id = team_b_id | Lỗi validation "Đội A và Đội B không được trùng nhau" | High |
| SPT-013 | Tạo trận chưa biết đội (TBD) | Giai đoạn knockout chưa có đội thắng | 1. Tạo trận với team_a_id=NULL | Trận được tạo với team NULL (chờ kết quả vòng trước) | Medium |
| SPT-014 | Trận đấu trùng thời gian cùng địa điểm | Sân A đã có trận lúc 9h | 1. Tạo trận mới cùng sân A lúc 9h | Cảnh báo "Địa điểm đã có trận đấu vào thời điểm này" | Medium |

### 6.4 Cập nhật Kết quả (UC22) và Xếp hạng (UC23)

| ID | Mô tả | Preconditions | Steps | Expected Result | Priority |
|----|-------|---------------|-------|-----------------|----------|
| SPT-015 | Cập nhật kết quả trận thắng-thua | Trận status="ongoing" | 1. Nhập score_a="3", score_b="1" 2. Chọn winner=Đội A | Bản ghi `sport_match_results` tạo/cập nhật; trận status="completed" | High |
| SPT-016 | Cập nhật kết quả hòa (is_draw=1) | Trận đang diễn ra | 1. Nhập score="1-1", is_draw=1, winner=NULL | Kết quả lưu với is_draw=1, winner_team_id=NULL | High |
| SPT-017 | Cập nhật kết quả trận đã hoàn thành (ghi đè) | Trận status="completed" | 1. Sửa lại kết quả | Kết quả được cập nhật; audit log ghi lại | Medium |
| SPT-018 | Winner không phải team_a hoặc team_b | Nhập winner là đội không liên quan | 1. Chọn winner_team_id khác team_a và team_b | Lỗi validation | High |
| SPT-019 | Huỷ trận đấu (status=cancelled) | Trận scheduled hoặc ongoing | 1. Chuyển trận sang cancelled | status="cancelled", không tính vào bảng xếp hạng | Medium |

---

## 7. Module: Thi Nghiệp vụ (Competition)

### 7.1 Tạo Cuộc thi và Vòng thi (UC16)

| ID | Mô tả | Preconditions | Steps | Expected Result | Priority |
|----|-------|---------------|-------|-----------------|----------|
| CMP-001 | Tạo cuộc thi nghiệp vụ mới | BTC Nghiệp vụ đã đăng nhập | 1. Nhập tên cuộc thi, prefix="NV", has_qualification=1 2. Lưu | Cuộc thi được tạo thành công | High |
| CMP-002 | Tạo cuộc thi với prefix trùng | Cuộc thi prefix="NV" đã tồn tại | 1. Tạo cuộc thi mới với prefix="NV" | Cần kiểm tra: có cho phép trùng không? Nếu không: lỗi | Medium |
| CMP-003 | Tạo vòng thi cho cuộc thi | Cuộc thi đã tạo | 1. Thêm "Vòng loại" round_order=1 2. Thêm "Chung kết" round_order=2 | 2 vòng thi được tạo, liên kết đúng competition_id | High |
| CMP-004 | Vòng thi có thời gian start > end | Cuộc thi tồn tại | 1. Tạo vòng với end_time < start_time | Lỗi validation | Medium |
| CMP-005 | Xóa vòng thi đang có thí sinh | Vòng thi có competition_registrations | 1. Xóa vòng thi | Lỗi "Không thể xóa vòng thi đã có thí sinh đăng ký" HOẶC cascade delete | High |

### 7.2 Cấp Số Báo danh (UC17)

| ID | Mô tả | Preconditions | Steps | Expected Result | Priority |
|----|-------|---------------|-------|-----------------|----------|
| CMP-006 | Đăng ký thí sinh tự động cấp số báo danh | Attendee tồn tại, cuộc thi tồn tại | 1. Đăng ký attendee vào cuộc thi | `candidate_number` = prefix + số thứ tự, VD "NV001" | Critical |
| CMP-007 | Số báo danh tăng dần không bị trùng | Đã có NV001, NV002 | 1. Đăng ký thêm thí sinh | Cấp NV003; không có 2 thí sinh cùng số báo danh | Critical |
| CMP-008 | Số báo danh theo độ dài chuẩn (candidate_number_pad) | pad=3, prefix="NV", start=1 | 1. Cấp số thứ 5 | Kết quả là "NV005" (3 chữ số, có padding 0) | High |
| CMP-009 | Đăng ký thí sinh vượt max_per_org | max_per_org=2, đơn vị đã có 2 thí sinh | 1. Đăng ký thêm thí sinh thứ 3 cùng đơn vị | Lỗi "Đơn vị đã đạt giới hạn số lượng thí sinh" | High |
| CMP-010 | Đăng ký thí sinh trùng lặp | Attendee đã đăng ký cuộc thi | 1. Đăng ký lại cùng attendee | Lỗi (UNIQUE KEY `uq_comp_reg_attendee`) | High |
| CMP-011 | Cấp số báo danh thủ công | BTC nhập số báo danh cụ thể | 1. Nhập candidate_number="NV099" thủ công | Số được lưu; kiểm tra nếu đã tồn tại thì báo lỗi | Medium |
| CMP-012 | Huỷ đăng ký thí sinh (cancelled) | Thí sinh đã đăng ký | 1. Chuyển status="cancelled" | status cập nhật; số báo danh vẫn giữ nguyên (không tái sử dụng) | Medium |

### 7.3 Xuất Danh sách và Kết quả (UC18, UC19)

| ID | Mô tả | Preconditions | Steps | Expected Result | Priority |
|----|-------|---------------|-------|-----------------|----------|
| CMP-013 | Xuất danh sách thí sinh ra Excel | Cuộc thi có nhiều thí sinh | 1. Click "Xuất Excel" | File Excel tải xuống với đầy đủ thông tin: tên, đơn vị, số báo danh | High |
| CMP-014 | Xuất Excel khi không có thí sinh | Cuộc thi chưa có ai đăng ký | 1. Click "Xuất Excel" | File Excel tải xuống với header nhưng không có dữ liệu | Medium |
| CMP-015 | Nhập kết quả vòng thi | Vòng thi đang diễn ra, thí sinh có số báo danh | 1. Nhập điểm cho từng thí sinh | Bản ghi `competition_round_results` được tạo | High |
| CMP-016 | Tìm thí sinh theo số báo danh | Thí sinh có số báo danh NV003 | 1. Tìm kiếm "NV003" | Trả về đúng thí sinh có số báo danh NV003 | Medium |

---

## 8. Module: Thi Sắc đẹp (Beauty Contest)

### 8.1 Quản lý Cuộc thi Miss

| ID | Mô tả | Preconditions | Steps | Expected Result | Priority |
|----|-------|---------------|-------|-----------------|----------|
| BCT-001 | Tạo cuộc thi Miss | Admin đã đăng nhập | 1. Tạo cuộc thi với gender=female, age_min=18, age_max=35 | Cuộc thi được tạo thành công | High |
| BCT-002 | Đăng ký thí sinh hợp lệ | Cuộc thi tồn tại, attendee nữ đủ tuổi | 1. Đăng ký thí sinh với height, weight, measurements | Thí sinh được thêm vào `beauty_contestants` | High |
| BCT-003 | Đăng ký thí sinh không đủ tuổi | age_min=18, thí sinh 16 tuổi | 1. Đăng ký thí sinh | Lỗi "Thí sinh không đủ tuổi tham gia cuộc thi" | High |
| BCT-004 | Tạo vòng thi Miss (áo dài, bikini, tài năng) | Cuộc thi tồn tại | 1. Tạo vòng "Áo dài" 2. Tạo vòng "Tài năng" | Các vòng thi được tạo đúng, liên kết với contest | High |
| BCT-005 | Chấm điểm thí sinh theo giám khảo | Thí sinh và vòng thi tồn tại, user là giám khảo | 1. Nhập điểm cho thí sinh ở vòng áo dài | Bản ghi `beauty_scores` được tạo với judge_id đúng | High |
| BCT-006 | Giám khảo chấm điểm 2 lần cùng thí sinh-vòng | Đã chấm điểm lần 1 | 1. Chấm điểm lần 2 cùng thí sinh | Cập nhật điểm lần 2 hoặc lỗi trùng (tùy business rule) | Medium |
| BCT-007 | Tính điểm trung bình vòng thi | Nhiều giám khảo đã chấm | 1. Xem kết quả vòng thi | Điểm TB được tính đúng theo công thức | High |
| BCT-008 | Measurements thí sinh nhập sai định dạng | | 1. Nhập measurements="abc" thay vì "90-60-90" | Validation kiểm tra định dạng hoặc lưu chuỗi tự do | Low |

---

## 9. Module: Văn nghệ (Talent Show)

### 9.1 Quản lý Cuộc thi Văn nghệ

| ID | Mô tả | Preconditions | Steps | Expected Result | Priority |
|----|-------|---------------|-------|-----------------|----------|
| TAL-001 | Tạo cuộc thi văn nghệ | Admin đã đăng nhập | 1. Tạo talent show với tên, thời gian | Cuộc thi được tạo | High |
| TAL-002 | Tạo thể loại thi (đơn ca, tốp ca, múa) | Cuộc thi tồn tại | 1. Tạo category "Đơn ca" | Category được tạo, liên kết talent_show | High |
| TAL-003 | Đăng ký tiết mục đơn ca | Cuộc thi có category đơn ca, attendee tồn tại | 1. Đăng ký tiết mục "Quê hương" (title, duration, music_path) | Tiết mục được tạo trong `talent_entries` | High |
| TAL-004 | Đăng ký tiết mục tốp ca (nhiều thành viên) | Tiết mục tốp ca được tạo | 1. Thêm 5 thành viên vào tiết mục | 5 bản ghi `talent_entry_members` được tạo | High |
| TAL-005 | Duration âm hoặc bằng 0 | | 1. Nhập duration=-60 hoặc 0 | Lỗi validation "Thời lượng phải lớn hơn 0" | Medium |
| TAL-006 | Upload file nhạc cho tiết mục | Tiết mục tồn tại | 1. Upload file MP3 | File lưu thành công, `music_path` cập nhật | Medium |
| TAL-007 | Chấm điểm tiết mục văn nghệ | Tiết mục và giám khảo tồn tại | 1. Nhập điểm cho tiết mục | Bản ghi `talent_scores` được tạo | High |
| TAL-008 | Thành viên tiết mục không phải attendee hợp lệ | | 1. Thêm attendee_id không tồn tại | Lỗi foreign key HOẶC validation | Medium |

---

## 10. Module: Bữa ăn & Tiệc (Meal & Banquet)

### 10.1 Quản lý Bữa ăn

| ID | Mô tả | Preconditions | Steps | Expected Result | Priority |
|----|-------|---------------|-------|-----------------|----------|
| MEA-001 | Tạo bữa ăn (breakfast/lunch/dinner) | Admin đã đăng nhập | 1. Tạo bữa sáng ngày 01/11, cutoff_deadline=30 phút trước | Bữa ăn được tạo | High |
| MEA-002 | Trưởng đoàn xem danh sách thành viên (UC13) | is_team_lead=1, đơn vị có nhiều attendee | 1. Đăng nhập trưởng đoàn 2. Xem danh sách | Chỉ hiển thị attendee cùng đơn vị với trưởng đoàn | High |
| MEA-003 | Báo cắt ăn cho từng người (UC14) | Trưởng đoàn đã đăng nhập, bữa chưa qua cutoff | 1. Chọn thành viên A 2. Báo cắt bữa sáng | Bản ghi `meal_cutoffs` được tạo cho attendee A | High |
| MEA-004 | Báo cắt ăn sau cutoff_deadline | Đã quá giờ cutoff | 1. Trưởng đoàn báo cắt | Lỗi "Đã qua thời hạn báo cắt ăn cho bữa này" | High |
| MEA-005 | Báo cắt ăn cả đoàn (bulk, UC15) | Trưởng đoàn đã đăng nhập, còn trong hạn | 1. Click "Báo cắt tất cả" | Tất cả thành viên đoàn được tạo bản ghi `meal_cutoffs` | High |
| MEA-006 | Trưởng đoàn báo cắt đoàn khác | Trưởng đoàn đơn vị A thử báo cho đơn vị B | 1. Gửi request với attendee_id của đơn vị B | Hệ thống từ chối, 403 hoặc validation lỗi | Critical |
| MEA-007 | Check-in bữa ăn | Bữa ăn diễn ra | 1. Check-in cho attendee | Bản ghi `meal_checkins` được tạo | Medium |
| MEA-008 | Check-in bữa ăn khi đã báo cắt | Attendee đã báo cắt bữa đó | 1. Check-in cho attendee đã báo cắt | Hiển thị cảnh báo "Người này đã báo cắt bữa ăn" | Medium |

### 10.2 Quản lý Sự kiện Tiệc (UC24 - UC27)

| ID | Mô tả | Preconditions | Steps | Expected Result | Priority |
|----|-------|---------------|-------|-----------------|----------|
| BAN-001 | Tạo sự kiện tiệc (UC24) | BTC Tiệc đã đăng nhập | 1. Nhập tên "Tiệc tối khai mạc", thời gian, địa điểm 2. Lưu | Sự kiện tiệc được tạo | High |
| BAN-002 | Thiết lập sơ đồ bàn (UC25) | Sự kiện tiệc tồn tại | 1. Tạo 30 bàn với capacity=10 2. Gán vị trí (pos_x, pos_y) | 30 bàn được tạo trong `banquet_tables` | High |
| BAN-003 | Bàn số âm hoặc bằng 0 | Sự kiện tiệc tồn tại | 1. Tạo bàn số table_number=0 | Lỗi validation | Medium |
| BAN-004 | Phân bổ người vào bàn (UC26) | Bàn tiệc và attendee tồn tại | 1. Gán attendee vào bàn 5, ghế 3 | Bản ghi `banquet_seats` được tạo | High |
| BAN-005 | Phân bổ người vào bàn đã đầy | Bàn capacity=10, đã có 10 người | 1. Gán người thứ 11 vào bàn | Lỗi "Bàn đã đầy" | High |
| BAN-006 | Phân bổ 1 người vào 2 bàn khác nhau | Attendee đã có ghế tại bàn 5 | 1. Gán lại vào bàn 6 | Lỗi "Người này đã được phân bàn" HOẶC hủy ghế cũ và tạo ghế mới | Medium |
| BAN-007 | Xem sơ đồ tổng quan tiệc (UC27) | Sự kiện tiệc có đủ bàn và người | 1. Xem sơ đồ tổng quan | Hiển thị sơ đồ canvas với tất cả bàn, hiển thị số ghế trống/đã lấp | High |
| BAN-008 | Canvas kích thước hợp lệ | | 1. Tạo tiệc với canvas_width=0, canvas_height=0 | Lỗi validation hoặc dùng giá trị mặc định 1200x800 | Medium |

---

## 11. Module: QR Code & Thông tin Công khai

### 11.1 Quét QR và Xem Thông tin (UC28 - UC31)

| ID | Mô tả | Preconditions | Steps | Expected Result | Priority |
|----|-------|---------------|-------|-----------------|----------|
| QR-001 | Quét QR xem thông tin cá nhân hợp lệ (UC28) | Attendee có qr_token hợp lệ | 1. Truy cập `/frontend/attendee/view?token=<qr_token>` | Hiển thị thông tin: tên, đơn vị, chức danh, ảnh | Critical |
| QR-002 | Truy cập với token không tồn tại | Token ngẫu nhiên không có trong DB | 1. Truy cập `?token=abc123xyz_fake` | Hiển thị trang "Không tìm thấy thông tin" hoặc 404 | High |
| QR-003 | Truy cập với token rỗng | | 1. Truy cập `?token=` | Trang lỗi thân thiện, không crash PHP | High |
| QR-004 | Truy cập với token chứa SQL injection | | 1. Truy cập `?token='; DROP TABLE attendees; --` | Hệ thống xử lý an toàn, không bị SQL injection | Critical |
| QR-005 | Truy cập với token chứa XSS | | 1. Token là `<script>alert(1)</script>` | Hệ thống encode output đúng cách, không chạy script | Critical |
| QR-006 | Xem agenda đại hội (UC29) | Event agenda đã nhập, is_public=1 | 1. Truy cập trang agenda qua QR | Hiển thị danh sách chương trình theo thứ tự thời gian | High |
| QR-007 | Agenda private không hiển thị (is_public=0) | Có agenda với is_public=0 | 1. Xem agenda công khai | Item có is_public=0 không xuất hiện | Medium |
| QR-008 | Xem lịch thi nghiệp vụ cá nhân (UC30) | Attendee đã đăng ký thi nghiệp vụ | 1. Quét QR của attendee 2. Xem lịch thi | Chỉ hiển thị cuộc thi và vòng thi mà attendee đăng ký | High |
| QR-009 | Xem lịch thi thể thao của đơn vị (UC31) | Đơn vị có đội thi đấu | 1. Quét QR 2. Xem lịch đấu | Hiển thị các trận đấu của đội tuyển đơn vị, sắp xếp theo thời gian | High |
| QR-010 | Trang QR không cần đăng nhập | Chưa có session | 1. Truy cập trang frontend QR | Truy cập được mà không cần đăng nhập | Critical |
| QR-011 | URL QR không lộ ID của attendee | Attendee id=123 | 1. Kiểm tra URL trong QR | URL chứa `qr_token`, không chứa số ID "123" | High |
| QR-012 | Attendee bị soft delete vẫn trả về không tìm thấy | Attendee is_active=0 | 1. Quét QR của attendee đã xóa | Hiển thị "Không tìm thấy thông tin" | Medium |

---

## 12. Module: Dashboard & Báo cáo

### 12.1 Dashboard Tổng hợp (UC12)

| ID | Mô tả | Preconditions | Steps | Expected Result | Priority |
|----|-------|---------------|-------|-----------------|----------|
| DSH-001 | Dashboard hiển thị tổng số đăng ký | Nhiều phiếu đăng ký các trạng thái | 1. Admin xem dashboard | Số liệu đúng: tổng draft, submitted, approved, rejected | High |
| DSH-002 | Dashboard thống kê theo đơn vị | Nhiều đơn vị đã đăng ký | 1. Xem thống kê theo đơn vị | Bảng hiển thị đúng số lượng attendee mỗi đơn vị | Medium |
| DSH-003 | Xuất báo cáo Excel toàn bộ attendee | Nhiều attendee đã approved | 1. Xuất Excel | File Excel chứa đầy đủ thông tin tất cả attendee | High |
| DSH-004 | Dashboard hiển thị khi không có dữ liệu | DB trống | 1. Xem dashboard | Không crash; hiển thị "Chưa có dữ liệu" hoặc số 0 | Medium |

---

## Phụ lục: Test Cases Bảo mật

| ID | Mô tả | Steps | Expected Result | Priority |
|----|-------|-------|-----------------|----------|
| SEC-001 | SQL Injection trong form tìm kiếm | 1. Nhập `' OR '1'='1` vào ô tìm kiếm | Câu query được parameterized; không trả về dữ liệu toàn bộ | Critical |
| SEC-002 | XSS trong tên người tham dự | 1. Nhập `<script>alert('xss')</script>` vào tên | Tên hiển thị dưới dạng text đã escape, script không chạy | Critical |
| SEC-003 | CSRF attack khi xóa dữ liệu | 1. Tạo form bên ngoài POST đến URL xóa | Yii CSRF token validation từ chối request | Critical |
| SEC-004 | Direct URL access vào trang admin không cần login | 1. Truy cập `/admin/attendees/index` không có session | Redirect về login | Critical |
| SEC-005 | Path traversal trong upload file | 1. Upload file với tên `../../config/main.php` | Hệ thống sanitize tên file, lưu vào đúng thư mục uploads | Critical |
| SEC-006 | Password hash kiểm tra không dùng MD5 | 1. Kiểm tra `password_hash` trong DB | Hash phải là bcrypt ($2y$) hoặc SHA-256+salt, không phải MD5 raw | Critical |
| SEC-007 | API key không lộ trong HTML source | 1. View source trang admin | `externalApiKey` không xuất hiện trong HTML response | High |

---

## Phụ lục: Test Cases Hiệu năng & Giới hạn

| ID | Mô tả | Steps | Expected Result | Priority |
|----|-------|-------|-----------------|----------|
| PERF-001 | Tải trang danh sách 600 attendee | 1. Xem danh sách toàn bộ attendee | Trang tải < 3 giây; sử dụng pagination | Medium |
| PERF-002 | Tạo thẻ hàng loạt cho 600 người | 1. Batch generate 600 badges | Không timeout (cần xử lý background job hoặc chunking) | Medium |
| PERF-003 | Xuất Excel 600 dòng | 1. Xuất Excel danh sách 600 attendee | File xuất thành công < 30 giây | Medium |
| PERF-004 | Concurrent: 50 đơn vị nộp phiếu cùng lúc | 1. Simulate 50 request submit đồng thời | Không xảy ra race condition hoặc duplicate registration | High |
| PERF-005 | Upload ảnh avatar 8MB | 1. Upload ảnh 8MB | Xử lý thành công hoặc báo lỗi rõ ràng (không white screen) | Low |

---

*Tài liệu này được tạo ngày 2026-05-26. Cần cập nhật khi có thay đổi business logic hoặc schema database.*
