<?php
/**
 * Export Test Cases to Excel
 * Run: php export_testcases.php
 */

require_once 'protected/extensions/phpexcel/Classes/PHPExcel.php';

$excel = new PHPExcel();
$sheet = $excel->getActiveSheet();
$sheet->setTitle('Test Cases');

// Header style
$headerStyle = array(
    'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF')),
    'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => '4472C4')),
    'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER),
    'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),
);

// Headers
$headers = array('ID', 'Module', 'Mô tả', 'Preconditions', 'Steps', 'Expected Result', 'Priority');
$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '1', $header);
    $col++;
}
$sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
$sheet->getRowDimension(1)->setRowHeight(25);

// Test cases data
$testCases = array(
    // Module 1: Authentication (19 TC)
    array('id' => 'AUTH-001', 'module' => 'Xác thực & Phân quyền', 'description' => 'Đăng nhập thành công với JWT hợp lệ từ Portal', 'preconditions' => 'Portal đã cấp JWT token hợp lệ, user tồn tại trong hệ thống', 'steps' => '1. Portal redirect đến /auth/callback?token=<JWT>
2. Hệ thống decode JWT
3. Tạo session', 'expected' => 'Session được tạo, redirect về Dashboard; thông tin user_id, full_name, unit_code lưu vào session', 'priority' => 'Critical'),
    array('id' => 'AUTH-002', 'module' => 'Xác thực & Phân quyền', 'description' => 'Đăng nhập thất bại với JWT hết hạn', 'preconditions' => 'JWT token đã hết hạn (exp < thời điểm hiện tại)', 'steps' => '1. Truy cập /auth/callback?token=<JWT_expired>', 'expected' => 'Redirect về trang lỗi, hiển thị thông báo "Phiên đăng nhập đã hết hạn"', 'priority' => 'Critical'),
    array('id' => 'AUTH-003', 'module' => 'Xác thực & Phân quyền', 'description' => 'Đăng nhập thất bại với JWT bị giả mạo', 'preconditions' => 'JWT token có chữ ký không hợp lệ', 'steps' => '1. Truy cập /auth/callback?token=<JWT_tampered>', 'expected' => 'Hệ thống từ chối, trả về HTTP 401, không tạo session', 'priority' => 'Critical'),
    array('id' => 'AUTH-004', 'module' => 'Xác thực & Phân quyền', 'description' => 'Đăng nhập thất bại khi thiếu token trong URL', 'preconditions' => 'URL callback không có param token', 'steps' => '1. Truy cập /auth/callback (không có token)', 'expected' => 'Redirect về trang lỗi, hiển thị "Token không hợp lệ"', 'priority' => 'High'),
    array('id' => 'AUTH-005', 'module' => 'Xác thực & Phân quyền', 'description' => 'Đăng nhập thất bại với token rỗng', 'preconditions' => 'Token param là chuỗi rỗng', 'steps' => '1. Truy cập /auth/callback?token=', 'expected' => 'Từ chối xử lý, hiển thị lỗi', 'priority' => 'High'),
    array('id' => 'AUTH-006', 'module' => 'Xác thực & Phân quyền', 'description' => 'Session hết hạn sau 30 phút không hoạt động', 'preconditions' => 'User đã đăng nhập, không thao tác 30 phút', 'steps' => '1. Chờ 30 phút
2. Thực hiện bất kỳ thao tác nào', 'expected' => 'Redirect về trang login/Portal, session bị xóa', 'priority' => 'High'),
    array('id' => 'AUTH-007', 'module' => 'Xác thực & Phân quyền', 'description' => 'Quyền Create (C) đúng theo JWT payload', 'preconditions' => 'JWT có "event": "1 1 1 1" (C=1)', 'steps' => '1. Đăng nhập
2. Truy cập action tạo sự kiện', 'expected' => 'Cho phép truy cập action create', 'priority' => 'Critical'),
    array('id' => 'AUTH-008', 'module' => 'Xác thực & Phân quyền', 'description' => 'Quyền Read (R) bị từ chối theo JWT payload', 'preconditions' => 'JWT có "event": "0 0 1 1" (R=0)', 'steps' => '1. Đăng nhập
2. Truy cập action list sự kiện', 'expected' => 'Trả về HTTP 403 "Forbidden"', 'priority' => 'Critical'),
    array('id' => 'AUTH-009', 'module' => 'Xác thực & Phân quyền', 'description' => 'Quyền Delete (D) bị từ chối đúng cách', 'preconditions' => 'JWT có "attendee": "1 1 1 0" (D=0)', 'steps' => '1. Đăng nhập
2. Thử xóa attendee', 'expected' => 'Nút Xóa bị ẩn trong view; nếu gọi trực tiếp URL trả về 403', 'priority' => 'Critical'),
    array('id' => 'AUTH-010', 'module' => 'Xác thực & Phân quyền', 'description' => 'Đăng nhập với token UTF-8 đặc biệt trong payload', 'preconditions' => 'JWT payload có full_name chứa Unicode "Nguyễn Văn A"', 'steps' => '1. Đăng nhập với JWT hợp lệ', 'expected' => 'Session lưu đúng full_name với ký tự tiếng Việt', 'priority' => 'Medium'),
    array('id' => 'AUTH-011', 'module' => 'Xác thực & Phân quyền', 'description' => 'Gọi API SSO /api/sso/me thành công sau đăng nhập', 'preconditions' => 'Đã đăng nhập thành công, token còn hiệu lực', 'steps' => '1. Hệ thống tự động gọi SSO API
2. Nhận profile', 'expected' => 'Profile được lưu vào localStorage phía client', 'priority' => 'High'),
    array('id' => 'AUTH-012', 'module' => 'Xác thực & Phân quyền', 'description' => 'Gọi API SSO thất bại (network timeout)', 'preconditions' => 'Mạng không ổn định', 'steps' => '1. Đăng nhập
2. API SSO timeout', 'expected' => 'Session vẫn được tạo từ JWT; user vẫn vào được hệ thống (degraded mode)', 'priority' => 'Medium'),
    array('id' => 'AUTH-013', 'module' => 'Xác thực & Phân quyền', 'description' => 'Đăng xuất (logout) xóa session đúng cách', 'preconditions' => 'User đang đăng nhập', 'steps' => '1. Click Đăng xuất', 'expected' => 'Session bị hủy, redirect về Portal, không thể truy cập trang admin nữa', 'priority' => 'High'),
    array('id' => 'AUTH-014', 'module' => 'Xác thực & Phân quyền', 'description' => 'Truy cập trang admin khi chưa đăng nhập', 'preconditions' => 'Chưa có session', 'steps' => '1. Truy cập trực tiếp URL admin bất kỳ', 'expected' => 'Redirect về trang login/Portal', 'priority' => 'Critical'),
    array('id' => 'AUTH-015', 'module' => 'Xác thực & Phân quyền', 'description' => 'JWT với permissions null hoặc thiếu key', 'preconditions' => 'JWT payload không có trường permissions', 'steps' => '1. Đăng nhập với JWT thiếu permissions', 'expected' => 'Hệ thống dùng quyền mặc định (tất cả D=0); không crash', 'priority' => 'High'),
    array('id' => 'AUTH-016', 'module' => 'Xác thực & Phân quyền', 'description' => 'Admin HO có toàn quyền hệ thống', 'preconditions' => 'User có role=admin trong JWT', 'steps' => '1. Đăng nhập với role admin
2. Truy cập tất cả module', 'expected' => 'Tất cả CRUD đều được phép; không bị 403 ở bất kỳ action nào', 'priority' => 'Critical'),
    array('id' => 'AUTH-017', 'module' => 'Xác thực & Phân quyền', 'description' => 'HR chỉ thấy chức năng phê duyệt đăng ký', 'preconditions' => 'User có role=hr', 'steps' => '1. Đăng nhập với role hr
2. Vào module Competition', 'expected' => 'Chỉ có quyền Read; không thấy nút Create/Delete trong Competition nếu không được cấp', 'priority' => 'High'),
    array('id' => 'AUTH-018', 'module' => 'Xác thực & Phân quyền', 'description' => 'BTC Thể thao không vào được module Thi nghiệp vụ', 'preconditions' => 'User có role=sports_organizer; không có quyền competition', 'steps' => '1. Đăng nhập
2. Truy cập /admin/competition', 'expected' => 'Trả về 403 hoặc không hiển thị menu', 'priority' => 'High'),
    array('id' => 'AUTH-019', 'module' => 'Xác thực & Phân quyền', 'description' => 'Đại diện đơn vị không truy cập được trang admin HO', 'preconditions' => 'unit_account đăng nhập', 'steps' => '1. Đăng nhập tài khoản đơn vị
2. Truy cập trang admin', 'expected' => 'Từ chối truy cập, redirect về dashboard đơn vị', 'priority' => 'Critical'),

    // Module 2: Organizations (18 TC)
    array('id' => 'ORG-001', 'module' => 'Quản lý Đơn vị', 'description' => 'Tạo khu vực mới thành công', 'preconditions' => 'Admin đã đăng nhập', 'steps' => '1. Vào Quản lý Khu vực
2. Nhập mã "KV01", tên "Khu vực Hà Nội"
3. Lưu', 'expected' => 'Khu vực được tạo, xuất hiện trong danh sách', 'priority' => 'High'),
    array('id' => 'ORG-002', 'module' => 'Quản lý Đơn vị', 'description' => 'Tạo khu vực với mã trùng lặp', 'preconditions' => 'Đã có khu vực mã "KV01"', 'steps' => '1. Tạo khu vực mới với mã "KV01"', 'expected' => 'Lỗi validation "Mã khu vực đã tồn tại"', 'priority' => 'High'),
    array('id' => 'ORG-003', 'module' => 'Quản lý Đơn vị', 'description' => 'Tạo khu vực với tên rỗng', 'preconditions' => 'Admin đã đăng nhập', 'steps' => '1. Để trống trường Tên
2. Lưu', 'expected' => 'Lỗi validation "Tên khu vực là bắt buộc"', 'priority' => 'High'),
    array('id' => 'ORG-004', 'module' => 'Quản lý Đơn vị', 'description' => 'Soft delete khu vực', 'preconditions' => 'Khu vực không có đơn vị liên kết', 'steps' => '1. Xóa khu vực', 'expected' => 'Trường deleted_at được gán timestamp; khu vực không hiển thị trong danh sách active', 'priority' => 'Medium'),
    array('id' => 'ORG-005', 'module' => 'Quản lý Đơn vị', 'description' => 'Xóa khu vực đang có đơn vị liên kết', 'preconditions' => 'Khu vực có 5 đơn vị con', 'steps' => '1. Thử xóa khu vực', 'expected' => 'Hệ thống cảnh báo hoặc regional_id các đơn vị được SET NULL theo FK constraint', 'priority' => 'Medium'),
    array('id' => 'ORG-006', 'module' => 'Quản lý Đơn vị', 'description' => 'Tạo đơn vị mới với đầy đủ thông tin', 'preconditions' => 'Admin đã đăng nhập, khu vực tồn tại', 'steps' => '1. Nhập tên, mã đơn vị, chọn khu vực
2. Lưu', 'expected' => 'Đơn vị được tạo, liên kết đúng với khu vực', 'priority' => 'High'),
    array('id' => 'ORG-007', 'module' => 'Quản lý Đơn vị', 'description' => 'Tạo đơn vị với mã code trùng', 'preconditions' => 'Đã có đơn vị mã "HN01"', 'steps' => '1. Tạo đơn vị mới mã "HN01"', 'expected' => 'Lỗi "Mã đơn vị đã tồn tại"', 'priority' => 'High'),
    array('id' => 'ORG-008', 'module' => 'Quản lý Đơn vị', 'description' => 'Tạo đơn vị không chọn khu vực', 'preconditions' => 'Không có khu vực nào', 'steps' => '1. Tạo đơn vị với regional_id=NULL', 'expected' => 'Đơn vị tạo thành công (regional_id cho phép NULL)', 'priority' => 'Medium'),
    array('id' => 'ORG-009', 'module' => 'Quản lý Đơn vị', 'description' => 'Mã đơn vị có ký tự đặc biệt', 'preconditions' => 'Admin đã đăng nhập', 'steps' => '1. Nhập mã "HN-01 / #1"', 'expected' => 'Validation từ chối ký tự đặc biệt HOẶC lưu đúng nếu không có rule', 'priority' => 'Medium'),
    array('id' => 'ORG-010', 'module' => 'Quản lý Đơn vị', 'description' => 'Cập nhật thông tin đơn vị', 'preconditions' => 'Đơn vị đã tồn tại', 'steps' => '1. Sửa tên đơn vị
2. Lưu', 'expected' => 'Thông tin cập nhật thành công, updated_at được gán', 'priority' => 'Medium'),
    array('id' => 'ORG-011', 'module' => 'Quản lý Đơn vị', 'description' => 'Tạo tài khoản đơn vị thành công', 'preconditions' => 'Đơn vị đã tồn tại, chưa có tài khoản', 'steps' => '1. Tạo tài khoản với username/password
2. Liên kết đơn vị', 'expected' => 'Tài khoản được tạo, liên kết 1-1 với đơn vị', 'priority' => 'High'),
    array('id' => 'ORG-012', 'module' => 'Quản lý Đơn vị', 'description' => 'Tạo tài khoản thứ 2 cho cùng đơn vị', 'preconditions' => 'Đơn vị đã có tài khoản', 'steps' => '1. Tạo tài khoản mới cho cùng organization_id', 'expected' => 'Lỗi "Đơn vị đã có tài khoản" (UNIQUE KEY)', 'priority' => 'High'),
    array('id' => 'ORG-013', 'module' => 'Quản lý Đơn vị', 'description' => 'Đăng nhập tài khoản đơn vị thành công', 'preconditions' => 'Tài khoản đơn vị tồn tại, is_active=1', 'steps' => '1. Nhập username/password đúng', 'expected' => 'Đăng nhập thành công, vào dashboard đơn vị', 'priority' => 'Critical'),
    array('id' => 'ORG-014', 'module' => 'Quản lý Đơn vị', 'description' => 'Đăng nhập với mật khẩu sai', 'preconditions' => 'Tài khoản tồn tại', 'steps' => '1. Nhập sai mật khẩu', 'expected' => 'Hiển thị lỗi "Tên đăng nhập hoặc mật khẩu không đúng"', 'priority' => 'Critical'),
    array('id' => 'ORG-015', 'module' => 'Quản lý Đơn vị', 'description' => 'Đăng nhập tài khoản bị vô hiệu hóa', 'preconditions' => 'is_active=0', 'steps' => '1. Nhập đúng username/password', 'expected' => 'Hiển thị lỗi "Tài khoản đã bị vô hiệu hóa"', 'priority' => 'High'),
    array('id' => 'ORG-016', 'module' => 'Quản lý Đơn vị', 'description' => 'Đăng nhập với username chứa SQL injection', 'preconditions' => 'Tài khoản tồn tại', 'steps' => "1. Nhập username: admin' OR '1'='1", 'expected' => 'Hệ thống xử lý an toàn, không bị SQL injection; trả về lỗi đăng nhập', 'priority' => 'Critical'),
    array('id' => 'ORG-017', 'module' => 'Quản lý Đơn vị', 'description' => 'Đổi mật khẩu tài khoản đơn vị', 'preconditions' => 'Đã đăng nhập', 'steps' => '1. Nhập mật khẩu cũ đúng
2. Nhập mật khẩu mới
3. Lưu', 'expected' => 'Mật khẩu được cập nhật, đăng nhập lại bằng mật khẩu mới thành công', 'priority' => 'Medium'),
    array('id' => 'ORG-018', 'module' => 'Quản lý Đơn vị', 'description' => 'Password_hash không lưu plaintext', 'preconditions' => 'Tạo tài khoản với password "123456"', 'steps' => '1. Kiểm tra DB field password_hash', 'expected' => 'Trường password_hash chứa chuỗi hash (bcrypt/SHA), không phải "123456"', 'priority' => 'Critical'),

    // Module 3: Registration (20 TC)
    array('id' => 'REG-001', 'module' => 'Đăng ký Tham dự', 'description' => 'Admin tạo đợt đăng ký hợp lệ', 'preconditions' => 'Admin đã đăng nhập', 'steps' => '1. Nhập tên đợt, start_time, end_time, max_per_org=20
2. Lưu', 'expected' => 'Đợt đăng ký được tạo thành công', 'priority' => 'High'),
    array('id' => 'REG-002', 'module' => 'Đăng ký Tham dự', 'description' => 'Tạo đợt đăng ký với end_time < start_time', 'preconditions' => 'Admin đã đăng nhập', 'steps' => '1. Nhập end_time trước start_time
2. Lưu', 'expected' => 'Lỗi validation "Thời gian kết thúc phải sau thời gian bắt đầu"', 'priority' => 'High'),
    array('id' => 'REG-003', 'module' => 'Đăng ký Tham dự', 'description' => 'Tạo đợt với max_per_org=0', 'preconditions' => 'Admin đã đăng nhập', 'steps' => '1. Nhập max_per_org=0', 'expected' => 'Lỗi validation (0 là giá trị vô nghĩa) HOẶC NULL (không giới hạn)', 'priority' => 'Medium'),
    array('id' => 'REG-004', 'module' => 'Đăng ký Tham dự', 'description' => 'Tạo đợt với max_per_org âm', 'preconditions' => 'Admin đã đăng nhập', 'steps' => '1. Nhập max_per_org=-5', 'expected' => 'Lỗi validation "Số người tối đa phải là số dương"', 'priority' => 'Medium'),
    array('id' => 'REG-005', 'module' => 'Đăng ký Tham dự', 'description' => 'Đơn vị thấy đợt đăng ký đang mở', 'preconditions' => 'Đợt đăng ký is_active=1, trong thời hạn', 'steps' => '1. Đăng nhập tài khoản đơn vị
2. Xem danh sách đợt', 'expected' => 'Đợt đăng ký đang mở hiển thị, cho phép tạo phiếu', 'priority' => 'High'),
    array('id' => 'REG-006', 'module' => 'Đăng ký Tham dự', 'description' => 'Đơn vị không thấy đợt đăng ký đã đóng', 'preconditions' => 'Đợt đăng ký end_time đã qua', 'steps' => '1. Đăng nhập tài khoản đơn vị', 'expected' => 'Đợt đã đóng không hiển thị hoặc hiển thị với trạng thái "Đã đóng"', 'priority' => 'High'),
    array('id' => 'REG-007', 'module' => 'Đăng ký Tham dự', 'description' => 'Tạo phiếu đăng ký thành công', 'preconditions' => 'Đơn vị đã đăng nhập, đợt đang mở', 'steps' => '1. Chọn sự kiện
2. Chọn đợt đăng ký
3. Lưu nháp', 'expected' => 'Phiếu được tạo với status="draft", liên kết đúng org và period', 'priority' => 'Critical'),
    array('id' => 'REG-008', 'module' => 'Đăng ký Tham dự', 'description' => 'Đơn vị tạo phiếu thứ 2 trong cùng đợt', 'preconditions' => 'Đơn vị đã có phiếu trong đợt', 'steps' => '1. Tạo phiếu mới trong cùng đợt', 'expected' => 'Lỗi "Đơn vị đã có phiếu đăng ký trong đợt này" (UNIQUE KEY)', 'priority' => 'Critical'),
    array('id' => 'REG-009', 'module' => 'Đăng ký Tham dự', 'description' => 'Thêm người tham dự vào phiếu draft', 'preconditions' => 'Phiếu có status="draft"', 'steps' => '1. Thêm người tham dự với tên, chức danh
2. Lưu', 'expected' => 'Người tham dự được thêm vào attendees, liên kết với registration_id', 'priority' => 'High'),
    array('id' => 'REG-010', 'module' => 'Đăng ký Tham dự', 'description' => 'Upload ảnh người tham dự hợp lệ', 'preconditions' => 'Phiếu draft, file ảnh JPG 300KB', 'steps' => '1. Upload ảnh cho người tham dự', 'expected' => 'Ảnh được lưu vào uploads/, đường dẫn cập nhật vào photo_path', 'priority' => 'High'),
    array('id' => 'REG-011', 'module' => 'Đăng ký Tham dự', 'description' => 'Upload ảnh quá dung lượng', 'preconditions' => 'File ảnh >10MB', 'steps' => '1. Upload ảnh 10MB+', 'expected' => 'Lỗi "File ảnh vượt quá dung lượng cho phép"', 'priority' => 'Medium'),
    array('id' => 'REG-012', 'module' => 'Đăng ký Tham dự', 'description' => 'Upload file không phải ảnh (PDF, EXE)', 'preconditions' => 'Phiếu draft', 'steps' => '1. Upload file .pdf', 'expected' => 'Lỗi "Chỉ chấp nhận file ảnh JPG/PNG/GIF"', 'priority' => 'High'),
    array('id' => 'REG-013', 'module' => 'Đăng ký Tham dự', 'description' => 'Chỉnh sửa thông tin người tham dự khi draft', 'preconditions' => 'Phiếu status="draft", có attendee', 'steps' => '1. Sửa tên, chức danh
2. Lưu', 'expected' => 'Thông tin cập nhật thành công', 'priority' => 'High'),
    array('id' => 'REG-014', 'module' => 'Đăng ký Tham dự', 'description' => 'Không cho chỉnh sửa khi phiếu đã submitted', 'preconditions' => 'Phiếu status="submitted"', 'steps' => '1. Thử sửa thông tin attendee', 'expected' => 'Hiển thị lỗi "Không thể chỉnh sửa phiếu đã nộp"', 'priority' => 'Critical'),
    array('id' => 'REG-015', 'module' => 'Đăng ký Tham dự', 'description' => 'Nộp phiếu đăng ký', 'preconditions' => 'Phiếu draft có ít nhất 1 attendee', 'steps' => '1. Click "Nộp đăng ký"
2. Xác nhận', 'expected' => 'Status chuyển thành "submitted", submitted_at được gán, thông báo thành công', 'priority' => 'Critical'),
    array('id' => 'REG-016', 'module' => 'Đăng ký Tham dự', 'description' => 'Nộp phiếu rỗng (không có attendee)', 'preconditions' => 'Phiếu draft, chưa thêm ai', 'steps' => '1. Click "Nộp đăng ký"', 'expected' => 'Lỗi "Vui lòng thêm ít nhất một người tham dự trước khi nộp"', 'priority' => 'High'),
    array('id' => 'REG-017', 'module' => 'Đăng ký Tham dự', 'description' => 'Nộp phiếu vượt quá max_per_org', 'preconditions' => 'max_per_org=5, phiếu có 7 attendees', 'steps' => '1. Nộp phiếu', 'expected' => 'Lỗi "Số người vượt quá giới hạn cho phép (tối đa 5 người)"', 'priority' => 'High'),
    array('id' => 'REG-018', 'module' => 'Đăng ký Tham dự', 'description' => 'Xem trạng thái phê duyệt', 'preconditions' => 'Phiếu đã nộp', 'steps' => '1. Đơn vị xem phiếu của mình', 'expected' => 'Hiển thị đúng status (draft/submitted/approved/rejected) và lý do từ chối nếu có', 'priority' => 'High'),
    array('id' => 'REG-019', 'module' => 'Đăng ký Tham dự', 'description' => 'Tạo phiếu ngoài thời hạn đăng ký', 'preconditions' => 'end_time của đợt đã qua', 'steps' => '1. Thử tạo phiếu', 'expected' => 'Lỗi "Đợt đăng ký đã đóng"', 'priority' => 'High'),
    array('id' => 'REG-020', 'module' => 'Đăng ký Tham dự', 'description' => 'submitted_by lấy từ SSO token', 'preconditions' => 'Đã đăng nhập qua Portal SSO', 'steps' => '1. Tạo và nộp phiếu', 'expected' => 'submitted_by trong DB chứa ID từ SSO (không phải Yii::app()->user->id)', 'priority' => 'Critical'),

    // Approval
    array('id' => 'REG-021', 'module' => 'Đăng ký Tham dự', 'description' => 'HR phê duyệt phiếu đăng ký (approve)', 'preconditions' => 'Phiếu status="submitted"', 'steps' => '1. HR chọn phiếu
2. Click "Phê duyệt"', 'expected' => 'Status chuyển thành "approved", reviewed_by và reviewed_at được gán', 'priority' => 'Critical'),
    array('id' => 'REG-022', 'module' => 'Đăng ký Tham dự', 'description' => 'HR từ chối phiếu đăng ký kèm lý do', 'preconditions' => 'Phiếu status="submitted"', 'steps' => '1. HR click "Từ chối"
2. Nhập lý do
3. Xác nhận', 'expected' => 'Status chuyển thành "rejected", rejection_reason được lưu', 'priority' => 'Critical'),
    array('id' => 'REG-023', 'module' => 'Đăng ký Tham dự', 'description' => 'Từ chối phiếu mà không nhập lý do', 'preconditions' => 'Phiếu status="submitted"', 'steps' => '1. Click "Từ chối"
2. Để trống lý do
3. Xác nhận', 'expected' => 'Lỗi "Vui lòng nhập lý do từ chối"', 'priority' => 'High'),
    array('id' => 'REG-024', 'module' => 'Đăng ký Tham dự', 'description' => 'Phê duyệt phiếu đã ở status approved', 'preconditions' => 'Phiếu đã approved', 'steps' => '1. Thử approve lại', 'expected' => 'Hệ thống không thay đổi, hiển thị cảnh báo "Phiếu đã được phê duyệt"', 'priority' => 'Medium'),
    array('id' => 'REG-025', 'module' => 'Đăng ký Tham dự', 'description' => 'Đơn vị không thể tự phê duyệt phiếu của mình', 'preconditions' => 'Đơn vị đã đăng nhập', 'steps' => '1. Truy cập URL approve phiếu của mình', 'expected' => 'Trả về 403 Forbidden', 'priority' => 'Critical'),
    array('id' => 'REG-026', 'module' => 'Đăng ký Tham dự', 'description' => 'Xem tất cả đăng ký theo trạng thái', 'preconditions' => 'HR đã đăng nhập, có nhiều phiếu', 'steps' => '1. Lọc theo status="submitted"', 'expected' => 'Chỉ hiển thị phiếu đã nộp, đúng số lượng', 'priority' => 'Medium'),

    // Module 4: Attendees (16 TC)
    array('id' => 'ATT-001', 'module' => 'Người tham dự', 'description' => 'Admin chỉnh sửa thông tin attendee sau phê duyệt', 'preconditions' => 'Phiếu đã approved, attendee tồn tại', 'steps' => '1. Admin mở attendee
2. Sửa tên/chức danh
3. Lưu', 'expected' => 'Thông tin cập nhật, updated_at gán mới; audit log ghi lại', 'priority' => 'High'),
    array('id' => 'ATT-002', 'module' => 'Người tham dự', 'description' => 'QR token tự động sinh khi attendee được approved', 'preconditions' => 'Phiếu vừa được approved', 'steps' => '1. Phê duyệt phiếu', 'expected' => 'Mỗi attendee có qr_token duy nhất 64 ký tự', 'priority' => 'Critical'),
    array('id' => 'ATT-003', 'module' => 'Người tham dự', 'description' => 'QR token là duy nhất (không trùng giữa các attendee)', 'preconditions' => 'Nhiều attendee trong hệ thống', 'steps' => '1. Tạo 100 attendee
2. Kiểm tra qr_token', 'expected' => 'Tất cả qr_token khác nhau (UNIQUE constraint)', 'priority' => 'Critical'),
    array('id' => 'ATT-004', 'module' => 'Người tham dự', 'description' => 'Badge number được gán tự động và duy nhất', 'preconditions' => 'Phiếu approved', 'steps' => '1. Phê duyệt phiếu', 'expected' => 'badge_number được gán dạng "001", "002"... không trùng', 'priority' => 'High'),
    array('id' => 'ATT-005', 'module' => 'Người tham dự', 'description' => 'Gán vai trò cho người tham dự', 'preconditions' => 'Attendee đã approved, role tồn tại', 'steps' => '1. Admin chọn attendee
2. Gán role "Trưởng đoàn"', 'expected' => 'Bản ghi trong attendee_roles được tạo', 'priority' => 'High'),
    array('id' => 'ATT-006', 'module' => 'Người tham dự', 'description' => 'Gán vai trò trùng lặp cho cùng attendee', 'preconditions' => 'Attendee đã có role "support"', 'steps' => '1. Gán lại role "support" cho attendee', 'expected' => 'Lỗi hoặc không tạo bản ghi mới (UNIQUE KEY)', 'priority' => 'Medium'),
    array('id' => 'ATT-007', 'module' => 'Người tham dự', 'description' => 'Gán trưởng đoàn cho đơn vị', 'preconditions' => 'Đơn vị có nhiều attendee', 'steps' => '1. Admin chọn 1 attendee làm trưởng đoàn
2. Lưu', 'expected' => 'is_team_lead=1 cho attendee đó; các attendee khác vẫn is_team_lead=0', 'priority' => 'High'),
    array('id' => 'ATT-008', 'module' => 'Người tham dự', 'description' => 'Soft delete attendee', 'preconditions' => 'Admin muốn xóa attendee', 'steps' => '1. Admin xóa attendee', 'expected' => 'is_active chuyển thành 0 hoặc deleted_at được gán; không xóa khỏi DB', 'priority' => 'High'),
    array('id' => 'ATT-009', 'module' => 'Người tham dự', 'description' => 'Thêm attendee với tên rỗng', 'preconditions' => 'Phiếu draft', 'steps' => '1. Thêm attendee không có tên', 'expected' => 'Lỗi validation "Họ tên là bắt buộc"', 'priority' => 'High'),
    array('id' => 'ATT-010', 'module' => 'Người tham dự', 'description' => 'Thêm attendee với tên quá dài (>255 ký tự)', 'preconditions' => 'Phiếu draft', 'steps' => '1. Nhập tên 300 ký tự', 'expected' => 'Lỗi validation "Tên không được vượt quá 255 ký tự"', 'priority' => 'Medium'),
    array('id' => 'ATT-011', 'module' => 'Người tham dự', 'description' => 'Tìm kiếm attendee theo tên', 'preconditions' => 'Nhiều attendee trong hệ thống', 'steps' => '1. Tìm kiếm "Nguyễn"', 'expected' => 'Danh sách lọc đúng attendee có tên chứa "Nguyễn"', 'priority' => 'Medium'),
    array('id' => 'ATT-012', 'module' => 'Người tham dự', 'description' => 'Thông tin check-in/check-out date hợp lệ', 'preconditions' => 'Attendee được phê duyệt', 'steps' => '1. Nhập check_in_date=2026-11-01, check_out_date=2026-11-03', 'expected' => 'Dữ liệu lưu đúng', 'priority' => 'Low'),
    array('id' => 'ATT-013', 'module' => 'Người tham dự', 'description' => 'check_out_date trước check_in_date', 'preconditions' => 'Admin chỉnh sửa attendee', 'steps' => '1. check_out_date < check_in_date', 'expected' => 'Lỗi validation', 'priority' => 'Medium'),
    array('id' => 'ATT-014', 'module' => 'Người tham dự', 'description' => 'Upload ảnh CCCD mặt trước', 'preconditions' => 'Attendee tồn tại', 'steps' => '1. Upload file CCCD mặt trước JPG', 'expected' => 'File lưu vào uploads/, cccd_front_path cập nhật', 'priority' => 'Medium'),
    array('id' => 'ATT-015', 'module' => 'Người tham dự', 'description' => 'Upload file hợp đồng PDF', 'preconditions' => 'Attendee tồn tại', 'steps' => '1. Upload file .pdf', 'expected' => 'File lưu vào uploads/, contract_path cập nhật', 'priority' => 'Medium'),
    array('id' => 'ATT-016', 'module' => 'Người tham dự', 'description' => 'Upload file .exe vào trường hợp đồng', 'preconditions' => 'Attacker thử upload file nguy hiểm', 'steps' => '1. Upload file .exe', 'expected' => 'Hệ thống từ chối, hiển thị lỗi về loại file', 'priority' => 'Critical'),

    // Module 5: Badges (9 TC)
    array('id' => 'BAD-001', 'module' => 'Thẻ tham dự', 'description' => 'Tạo thẻ cho 1 attendee', 'preconditions' => 'Attendee đã approved, có đầy đủ thông tin', 'steps' => '1. Chọn attendee
2. Click "Tạo thẻ"', 'expected' => 'File ảnh thẻ được tạo (85.60×53.98mm, 300dpi), badge_generated=1', 'priority' => 'Critical'),
    array('id' => 'BAD-002', 'module' => 'Thẻ tham dự', 'description' => 'Tạo thẻ theo lô (batch)', 'preconditions' => 'Nhiều attendee đã approved', 'steps' => '1. Chọn tất cả attendee
2. Tạo thẻ hàng loạt', 'expected' => 'Tất cả thẻ được tạo; báo cáo thành công/thất bại', 'priority' => 'High'),
    array('id' => 'BAD-003', 'module' => 'Thẻ tham dự', 'description' => 'Thẻ chứa QR code đúng qr_token', 'preconditions' => 'Attendee có qr_token', 'steps' => '1. Tạo thẻ
2. Quét QR', 'expected' => 'URL trong QR dẫn đến /frontend/attendee/view?token=<qr_token> của đúng attendee', 'priority' => 'Critical'),
    array('id' => 'BAD-004', 'module' => 'Thẻ tham dự', 'description' => 'Kích thước ảnh thẻ đúng chuẩn CR80', 'preconditions' => 'Thẻ vừa được tạo', 'steps' => '1. Tạo thẻ
2. Kiểm tra kích thước file', 'expected' => 'Ảnh có tỉ lệ đúng 85.60:53.98mm, DPI=300 (tương đương 1013×638 pixel)', 'priority' => 'High'),
    array('id' => 'BAD-005', 'module' => 'Thẻ tham dự', 'description' => 'Tạo thẻ cho attendee chưa có ảnh', 'preconditions' => 'Attendee chưa upload ảnh', 'steps' => '1. Tạo thẻ', 'expected' => 'Thẻ vẫn được tạo với ảnh mặc định/placeholder, không crash', 'priority' => 'Medium'),
    array('id' => 'BAD-006', 'module' => 'Thẻ tham dự', 'description' => 'In thẻ lần đầu cập nhật print_count', 'preconditions' => 'Thẻ đã tạo', 'steps' => '1. Click "In thẻ"', 'expected' => 'print_count tăng thêm 1, last_printed_at cập nhật', 'priority' => 'Medium'),
    array('id' => 'BAD-007', 'module' => 'Thẻ tham dự', 'description' => 'Tạo thẻ cho attendee chưa approved', 'preconditions' => 'Phiếu status="draft"', 'steps' => '1. Thử tạo thẻ', 'expected' => 'Lỗi "Chỉ tạo thẻ cho người tham dự đã được phê duyệt"', 'priority' => 'High'),
    array('id' => 'BAD-008', 'module' => 'Thẻ tham dự', 'description' => 'Tái tạo thẻ (regenerate) sau khi sửa thông tin', 'preconditions' => 'Thẻ đã tạo, admin sửa tên attendee', 'steps' => '1. Sửa tên
2. Tái tạo thẻ', 'expected' => 'Thẻ mới phản ánh tên mới; file cũ bị ghi đè', 'priority' => 'Medium'),
    array('id' => 'BAD-009', 'module' => 'Thẻ tham dự', 'description' => 'Xuất thẻ khi ảnh file bị xóa khỏi disk', 'preconditions' => 'photo_path trỏ đến file không tồn tại', 'steps' => '1. Tạo thẻ', 'expected' => 'Hệ thống dùng ảnh placeholder, không crash với fatal error', 'priority' => 'High'),

    // Module 6: Sports (15 TC)
    array('id' => 'SPT-001', 'module' => 'Thi Thể thao', 'description' => 'Tạo môn thể thao cấp gốc', 'preconditions' => 'BTC Thể thao đã đăng nhập', 'steps' => '1. Tạo môn "Bóng đá" (parent_id=NULL, type=team)', 'expected' => 'Môn được tạo thành công, hiển thị trong danh sách', 'priority' => 'High'),
    array('id' => 'SPT-002', 'module' => 'Thi Thể thao', 'description' => 'Tạo môn thể thao con (child)', 'preconditions' => 'Đã có môn "Bóng đá"', 'steps' => '1. Tạo "Bóng đá nam" với parent_id=ID_Bong_da', 'expected' => 'Môn con được tạo, liên kết đúng với môn cha', 'priority' => 'High'),
    array('id' => 'SPT-003', 'module' => 'Thi Thể thao', 'description' => 'Tạo môn với mã code trùng lặp', 'preconditions' => 'Môn "BD" đã tồn tại', 'steps' => '1. Tạo môn mới với code="BD"', 'expected' => 'Lỗi "Mã môn đã tồn tại" (UNIQUE KEY)', 'priority' => 'High'),
    array('id' => 'SPT-004', 'module' => 'Thi Thể thao', 'description' => 'Upload file điều lệ thi đấu', 'preconditions' => 'Môn thể thao tồn tại', 'steps' => '1. Upload file PDF điều lệ', 'expected' => 'File lưu thành công, document path cập nhật', 'priority' => 'Medium'),
    array('id' => 'SPT-005', 'module' => 'Thi Thể thao', 'description' => 'Tạo đội thi đấu cho đơn vị', 'preconditions' => 'Môn thể thao tồn tại, đơn vị tồn tại', 'steps' => '1. Tạo đội "Đội Bóng đá HN01" cho môn Bóng đá
2. Liên kết đơn vị', 'expected' => 'Đội được tạo với organization_id đúng', 'priority' => 'High'),
    array('id' => 'SPT-006', 'module' => 'Thi Thể thao', 'description' => 'Tạo đội hỗn hợp (không thuộc đơn vị nào)', 'preconditions' => 'Môn thể thao tồn tại', 'steps' => '1. Tạo đội với organization_id=NULL', 'expected' => 'Đội được tạo với organization_id=NULL', 'priority' => 'Medium'),
    array('id' => 'SPT-007', 'module' => 'Thi Thể thao', 'description' => 'Thêm thành viên vào đội', 'preconditions' => 'Đội và attendee tồn tại', 'steps' => '1. Thêm attendee vào đội
2. Gán số áo, vị trí', 'expected' => 'Bản ghi sport_team_members được tạo', 'priority' => 'High'),
    array('id' => 'SPT-008', 'module' => 'Thi Thể thao', 'description' => 'Thêm cùng attendee vào đội 2 lần', 'preconditions' => 'Attendee đã là thành viên đội', 'steps' => '1. Thêm lại attendee', 'expected' => 'Lỗi "Thành viên đã có trong đội" (UNIQUE KEY)', 'priority' => 'High'),
    array('id' => 'SPT-009', 'module' => 'Thi Thể thao', 'description' => 'Chỉ có 1 thuyền trưởng (is_captain)', 'preconditions' => 'Đội đã có captain', 'steps' => '1. Đặt thêm 1 thành viên khác là captain', 'expected' => 'Hệ thống cho phép nhiều captain HOẶC giới hạn 1 (kiểm tra logic)', 'priority' => 'Medium'),
    array('id' => 'SPT-010', 'module' => 'Thi Thể thao', 'description' => 'Xóa thành viên khỏi đội', 'preconditions' => 'Thành viên trong đội', 'steps' => '1. Xóa thành viên', 'expected' => 'Bản ghi trong sport_team_members bị xóa hoặc status=0', 'priority' => 'Medium'),
    array('id' => 'SPT-011', 'module' => 'Thi Thể thao', 'description' => 'Tạo trận đấu vòng bảng', 'preconditions' => '2 đội tồn tại, môn thể thao tồn tại', 'steps' => '1. Tạo trận Đội A vs Đội B, loại=group, thời gian=...', 'expected' => 'Trận được tạo với status="scheduled"', 'priority' => 'High'),
    array('id' => 'SPT-012', 'module' => 'Thi Thể thao', 'description' => 'Tạo trận với team_a = team_b (đội đấu với chính mình)', 'preconditions' => '2 đội tồn tại', 'steps' => '1. Chọn team_a_id = team_b_id', 'expected' => 'Lỗi validation "Đội A và Đội B không được trùng nhau"', 'priority' => 'High'),
    array('id' => 'SPT-013', 'module' => 'Thi Thể thao', 'description' => 'Tạo trận chưa biết đội (TBD)', 'preconditions' => 'Giai đoạn knockout chưa có đội thắng', 'steps' => '1. Tạo trận với team_a_id=NULL', 'expected' => 'Trận được tạo với team NULL (chờ kết quả vòng trước)', 'priority' => 'Medium'),
    array('id' => 'SPT-014', 'module' => 'Thi Thể thao', 'description' => 'Trận đấu trùng thời gian cùng địa điểm', 'preconditions' => 'Sân A đã có trận lúc 9h', 'steps' => '1. Tạo trận mới cùng sân A lúc 9h', 'expected' => 'Cảnh báo "Địa điểm đã có trận đấu vào thời điểm này"', 'priority' => 'Medium'),
    array('id' => 'SPT-015', 'module' => 'Thi Thể thao', 'description' => 'Cập nhật kết quả trận thắng-thua', 'preconditions' => 'Trận status="ongoing"', 'steps' => '1. Nhập score_a="3", score_b="1"
2. Chọn winner=Đội A', 'expected' => 'Bản ghi sport_match_results tạo/cập nhật; trận status="completed"', 'priority' => 'High'),

    // Module 7: Competition (11 TC)
    array('id' => 'CMP-001', 'module' => 'Thi Nghiệp vụ', 'description' => 'Tạo cuộc thi nghiệp vụ mới', 'preconditions' => 'BTC Nghiệp vụ đã đăng nhập', 'steps' => '1. Nhập tên cuộc thi, prefix="NV", has_qualification=1
2. Lưu', 'expected' => 'Cuộc thi được tạo thành công', 'priority' => 'High'),
    array('id' => 'CMP-002', 'module' => 'Thi Nghiệp vụ', 'description' => 'Tạo vòng thi cho cuộc thi', 'preconditions' => 'Cuộc thi đã tạo', 'steps' => '1. Thêm "Vòng loại" round_order=1
2. Thêm "Chung kết" round_order=2', 'expected' => '2 vòng thi được tạo, liên kết đúng competition_id', 'priority' => 'High'),
    array('id' => 'CMP-003', 'module' => 'Thi Nghiệp vụ', 'description' => 'Đăng ký thí sinh tự động cấp số báo danh', 'preconditions' => 'Attendee tồn tại, cuộc thi tồn tại', 'steps' => '1. Đăng ký attendee vào cuộc thi', 'expected' => 'candidate_number = prefix + số thứ tự, VD "NV001"', 'priority' => 'Critical'),
    array('id' => 'CMP-004', 'module' => 'Thi Nghiệp vụ', 'description' => 'Số báo danh tăng dần không bị trùng', 'preconditions' => 'Đã có NV001, NV002', 'steps' => '1. Đăng ký thêm thí sinh', 'expected' => 'Cấp NV003; không có 2 thí sinh cùng số báo danh', 'priority' => 'Critical'),
    array('id' => 'CMP-005', 'module' => 'Thi Nghiệp vụ', 'description' => 'Đăng ký thí sinh vượt max_per_org', 'preconditions' => 'max_per_org=2, đơn vị đã có 2 thí sinh', 'steps' => '1. Đăng ký thêm thí sinh thứ 3 cùng đơn vị', 'expected' => 'Lỗi "Đơn vị đã đạt giới hạn số lượng thí sinh"', 'priority' => 'High'),
    array('id' => 'CMP-006', 'module' => 'Thi Nghiệp vụ', 'description' => 'Đăng ký thí sinh trùng lặp', 'preconditions' => 'Attendee đã đăng ký cuộc thi', 'steps' => '1. Đăng ký lại cùng attendee', 'expected' => 'Lỗi (UNIQUE KEY)', 'priority' => 'High'),
    array('id' => 'CMP-007', 'module' => 'Thi Nghiệp vụ', 'description' => 'Xuất danh sách thí sinh ra Excel', 'preconditions' => 'Cuộc thi có nhiều thí sinh', 'steps' => '1. Click "Xuất Excel"', 'expected' => 'File Excel tải xuống với đầy đủ thông tin: tên, đơn vị, số báo danh', 'priority' => 'High'),
    array('id' => 'CMP-008', 'module' => 'Thi Nghiệp vụ', 'description' => 'Nhập kết quả vòng thi', 'preconditions' => 'Vòng thi đang diễn ra, thí sinh có số báo danh', 'steps' => '1. Nhập điểm cho từng thí sinh', 'expected' => 'Bản ghi competition_round_results được tạo', 'priority' => 'High'),

    // Module 8: Beauty Contest (8 TC)
    array('id' => 'BCT-001', 'module' => 'Thi Sắc đẹp', 'description' => 'Tạo cuộc thi Miss', 'preconditions' => 'Admin đã đăng nhập', 'steps' => '1. Tạo cuộc thi với gender=female, age_min=18, age_max=35', 'expected' => 'Cuộc thi được tạo thành công', 'priority' => 'High'),
    array('id' => 'BCT-002', 'module' => 'Thi Sắc đẹp', 'description' => 'Đăng ký thí sinh hợp lệ', 'preconditions' => 'Cuộc thi tồn tại, attendee nữ đủ tuổi', 'steps' => '1. Đăng ký thí sinh với height, weight, measurements', 'expected' => 'Thí sinh được thêm vào beauty_contestants', 'priority' => 'High'),
    array('id' => 'BCT-003', 'module' => 'Thi Sắc đẹp', 'description' => 'Đăng ký thí sinh không đủ tuổi', 'preconditions' => 'age_min=18, thí sinh 16 tuổi', 'steps' => '1. Đăng ký thí sinh', 'expected' => 'Lỗi "Thí sinh không đủ tuổi tham gia cuộc thi"', 'priority' => 'High'),
    array('id' => 'BCT-004', 'module' => 'Thi Sắc đẹp', 'description' => 'Chấm điểm thí sinh theo giám khảo', 'preconditions' => 'Thí sinh và vòng thi tồn tại, user là giám khảo', 'steps' => '1. Nhập điểm cho thí sinh ở vòng áo dài', 'expected' => 'Bản ghi beauty_scores được tạo với judge_id đúng', 'priority' => 'High'),
    array('id' => 'BCT-005', 'module' => 'Thi Sắc đẹp', 'description' => 'Tính điểm trung bình vòng thi', 'preconditions' => 'Nhiều giám khảo đã chấm', 'steps' => '1. Xem kết quả vòng thi', 'expected' => 'Điểm TB được tính đúng theo công thức', 'priority' => 'High'),

    // Module 9: Talent Show (8 TC)
    array('id' => 'TAL-001', 'module' => 'Văn nghệ', 'description' => 'Tạo cuộc thi văn nghệ', 'preconditions' => 'Admin đã đăng nhập', 'steps' => '1. Tạo talent show với tên, thời gian', 'expected' => 'Cuộc thi được tạo', 'priority' => 'High'),
    array('id' => 'TAL-002', 'module' => 'Văn nghệ', 'description' => 'Đăng ký tiết mục đơn ca', 'preconditions' => 'Cuộc thi có category đơn ca, attendee tồn tại', 'steps' => '1. Đăng ký tiết mục "Quê hương" (title, duration, music_path)', 'expected' => 'Tiết mục được tạo trong talent_entries', 'priority' => 'High'),
    array('id' => 'TAL-003', 'module' => 'Văn nghệ', 'description' => 'Đăng ký tiết mục tốp ca (nhiều thành viên)', 'preconditions' => 'Tiết mục tốp ca được tạo', 'steps' => '1. Thêm 5 thành viên vào tiết mục', 'expected' => '5 bản ghi talent_entry_members được tạo', 'priority' => 'High'),
    array('id' => 'TAL-004', 'module' => 'Văn nghệ', 'description' => 'Duration âm hoặc bằng 0', 'preconditions' => '', 'steps' => '1. Nhập duration=-60 hoặc 0', 'expected' => 'Lỗi validation "Thời lượng phải lớn hơn 0"', 'priority' => 'Medium'),
    array('id' => 'TAL-005', 'module' => 'Văn nghệ', 'description' => 'Chấm điểm tiết mục văn nghệ', 'preconditions' => 'Tiết mục và giám khảo tồn tại', 'steps' => '1. Nhập điểm cho tiết mục', 'expected' => 'Bản ghi talent_scores được tạo', 'priority' => 'High'),

    // Module 10: Meal & Banquet (15 TC)
    array('id' => 'MEA-001', 'module' => 'Bữa ăn & Tiệc', 'description' => 'Tạo bữa ăn (breakfast/lunch/dinner)', 'preconditions' => 'Admin đã đăng nhập', 'steps' => '1. Tạo bữa sáng ngày 01/11, cutoff_deadline=30 phút trước', 'expected' => 'Bữa ăn được tạo', 'priority' => 'High'),
    array('id' => 'MEA-002', 'module' => 'Bữa ăn & Tiệc', 'description' => 'Trưởng đoàn xem danh sách thành viên', 'preconditions' => 'is_team_lead=1, đơn vị có nhiều attendee', 'steps' => '1. Đăng nhập trưởng đoàn
2. Xem danh sách', 'expected' => 'Chỉ hiển thị attendee cùng đơn vị với trưởng đoàn', 'priority' => 'High'),
    array('id' => 'MEA-003', 'module' => 'Bữa ăn & Tiệc', 'description' => 'Báo cắt ăn cho từng người', 'preconditions' => 'Trưởng đoàn đã đăng nhập, bữa chưa qua cutoff', 'steps' => '1. Chọn thành viên A
2. Báo cắt bữa sáng', 'expected' => 'Bản ghi meal_cutoffs được tạo cho attendee A', 'priority' => 'High'),
    array('id' => 'MEA-004', 'module' => 'Bữa ăn & Tiệc', 'description' => 'Báo cắt ăn sau cutoff_deadline', 'preconditions' => 'Đã quá giờ cutoff', 'steps' => '1. Trưởng đoàn báo cắt', 'expected' => 'Lỗi "Đã qua thời hạn báo cắt ăn cho bữa này"', 'priority' => 'High'),
    array('id' => 'MEA-005', 'module' => 'Bữa ăn & Tiệc', 'description' => 'Báo cắt ăn cả đoàn (bulk)', 'preconditions' => 'Trưởng đoàn đã đăng nhập, còn trong hạn', 'steps' => '1. Click "Báo cắt tất cả"', 'expected' => 'Tất cả thành viên đoàn được tạo bản ghi meal_cutoffs', 'priority' => 'High'),
    array('id' => 'MEA-006', 'module' => 'Bữa ăn & Tiệc', 'description' => 'Trưởng đoàn báo cắt đoàn khác', 'preconditions' => 'Trưởng đoàn đơn vị A thử báo cho đơn vị B', 'steps' => '1. Gửi request với attendee_id của đơn vị B', 'expected' => 'Hệ thống từ chối, 403 hoặc validation lỗi', 'priority' => 'Critical'),
    array('id' => 'BAN-001', 'module' => 'Bữa ăn & Tiệc', 'description' => 'Tạo sự kiện tiệc', 'preconditions' => 'BTC Tiệc đã đăng nhập', 'steps' => '1. Nhập tên "Tiệc tối khai mạc", thời gian, địa điểm
2. Lưu', 'expected' => 'Sự kiện tiệc được tạo', 'priority' => 'High'),
    array('id' => 'BAN-002', 'module' => 'Bữa ăn & Tiệc', 'description' => 'Thiết lập sơ đồ bàn', 'preconditions' => 'Sự kiện tiệc tồn tại', 'steps' => '1. Tạo 30 bàn với capacity=10
2. Gán vị trí (pos_x, pos_y)', 'expected' => '30 bàn được tạo trong banquet_tables', 'priority' => 'High'),
    array('id' => 'BAN-003', 'module' => 'Bữa ăn & Tiệc', 'description' => 'Phân bổ người vào bàn', 'preconditions' => 'Bàn tiệc và attendee tồn tại', 'steps' => '1. Gán attendee vào bàn 5, ghế 3', 'expected' => 'Bản ghi banquet_seats được tạo', 'priority' => 'High'),
    array('id' => 'BAN-004', 'module' => 'Bữa ăn & Tiệc', 'description' => 'Phân bổ người vào bàn đã đầy', 'preconditions' => 'Bàn capacity=10, đã có 10 người', 'steps' => '1. Gán người thứ 11 vào bàn', 'expected' => 'Lỗi "Bàn đã đầy"', 'priority' => 'High'),
    array('id' => 'BAN-005', 'module' => 'Bữa ăn & Tiệc', 'description' => 'Xem sơ đồ tổng quan tiệc', 'preconditions' => 'Sự kiện tiệc có đủ bàn và người', 'steps' => '1. Xem sơ đồ tổng quan', 'expected' => 'Hiển thị sơ đồ canvas với tất cả bàn, hiển thị số ghế trống/đã lấp', 'priority' => 'High'),

    // Module 11: QR Code (12 TC)
    array('id' => 'QR-001', 'module' => 'QR Code Public', 'description' => 'Quét QR xem thông tin cá nhân hợp lệ', 'preconditions' => 'Attendee có qr_token hợp lệ', 'steps' => '1. Truy cập /frontend/attendee/view?token=<qr_token>', 'expected' => 'Hiển thị thông tin: tên, đơn vị, chức danh, ảnh', 'priority' => 'Critical'),
    array('id' => 'QR-002', 'module' => 'QR Code Public', 'description' => 'Truy cập với token không tồn tại', 'preconditions' => 'Token ngẫu nhiên không có trong DB', 'steps' => '1. Truy cập ?token=abc123xyz_fake', 'expected' => 'Hiển thị trang "Không tìm thấy thông tin" hoặc 404', 'priority' => 'High'),
    array('id' => 'QR-003', 'module' => 'QR Code Public', 'description' => 'Truy cập với token rỗng', 'preconditions' => '', 'steps' => '1. Truy cập ?token=', 'expected' => 'Trang lỗi thân thiện, không crash PHP', 'priority' => 'High'),
    array('id' => 'QR-004', 'module' => 'QR Code Public', 'description' => 'Truy cập với token chứa SQL injection', 'preconditions' => '', 'steps' => "1. Truy cập ?token='; DROP TABLE attendees; --", 'expected' => 'Hệ thống xử lý an toàn, không bị SQL injection', 'priority' => 'Critical'),
    array('id' => 'QR-005', 'module' => 'QR Code Public', 'description' => 'Truy cập với token chứa XSS', 'preconditions' => '', 'steps' => '1. Token là <script>alert(1)</script>', 'expected' => 'Hệ thống encode output đúng cách, không chạy script', 'priority' => 'Critical'),
    array('id' => 'QR-006', 'module' => 'QR Code Public', 'description' => 'Xem agenda đại hội', 'preconditions' => 'Event agenda đã nhập, is_public=1', 'steps' => '1. Truy cập trang agenda qua QR', 'expected' => 'Hiển thị danh sách chương trình theo thứ tự thời gian', 'priority' => 'High'),
    array('id' => 'QR-007', 'module' => 'QR Code Public', 'description' => 'Xem lịch thi nghiệp vụ cá nhân', 'preconditions' => 'Attendee đã đăng ký thi nghiệp vụ', 'steps' => '1. Quét QR của attendee
2. Xem lịch thi', 'expected' => 'Chỉ hiển thị cuộc thi và vòng thi mà attendee đăng ký', 'priority' => 'High'),
    array('id' => 'QR-008', 'module' => 'QR Code Public', 'description' => 'Trang QR không cần đăng nhập', 'preconditions' => 'Chưa có session', 'steps' => '1. Truy cập trang frontend QR', 'expected' => 'Truy cập được mà không cần đăng nhập', 'priority' => 'Critical'),
    array('id' => 'QR-009', 'module' => 'QR Code Public', 'description' => 'URL QR không lộ ID của attendee', 'preconditions' => 'Attendee id=123', 'steps' => '1. Kiểm tra URL trong QR', 'expected' => 'URL chứa qr_token, không chứa số ID "123"', 'priority' => 'High'),
    array('id' => 'QR-010', 'module' => 'QR Code Public', 'description' => 'Attendee bị soft delete vẫn trả về không tìm thấy', 'preconditions' => 'Attendee is_active=0', 'steps' => '1. Quét QR của attendee đã xóa', 'expected' => 'Hiển thị "Không tìm thấy thông tin"', 'priority' => 'Medium'),

    // Module 12: Security (11 TC)
    array('id' => 'SEC-001', 'module' => 'Bảo mật', 'description' => 'SQL Injection trong form tìm kiếm', 'preconditions' => '', 'steps' => "1. Nhập ' OR '1'='1 vào ô tìm kiếm", 'expected' => 'Câu query được parameterized; không trả về dữ liệu toàn bộ', 'priority' => 'Critical'),
    array('id' => 'SEC-002', 'module' => 'Bảo mật', 'description' => 'XSS trong tên người tham dự', 'preconditions' => '', 'steps' => "1. Nhập <script>alert('xss')</script> vào tên", 'expected' => 'Tên hiển thị dưới dạng text đã escape, script không chạy', 'priority' => 'Critical'),
    array('id' => 'SEC-003', 'module' => 'Bảo mật', 'description' => 'CSRF attack khi xóa dữ liệu', 'preconditions' => '', 'steps' => '1. Tạo form bên ngoài POST đến URL xóa', 'expected' => 'Yii CSRF token validation từ chối request', 'priority' => 'Critical'),
    array('id' => 'SEC-004', 'module' => 'Bảo mật', 'description' => 'Direct URL access vào trang admin không cần login', 'preconditions' => '', 'steps' => '1. Truy cập /admin/attendees/index không có session', 'expected' => 'Redirect về login', 'priority' => 'Critical'),
    array('id' => 'SEC-005', 'module' => 'Bảo mật', 'description' => 'Path traversal trong upload file', 'preconditions' => '', 'steps' => '1. Upload file với tên ../../config/main.php', 'expected' => 'Hệ thống sanitize tên file, lưu vào đúng thư mục uploads', 'priority' => 'Critical'),
    array('id' => 'SEC-006', 'module' => 'Bảo mật', 'description' => 'Password hash kiểm tra không dùng MD5', 'preconditions' => '', 'steps' => '1. Kiểm tra password_hash trong DB', 'expected' => 'Hash phải là bcrypt ($2y$) hoặc SHA-256+salt, không phải MD5 raw', 'priority' => 'Critical'),
    array('id' => 'SEC-007', 'module' => 'Bảo mật', 'description' => 'API key không lộ trong HTML source', 'preconditions' => '', 'steps' => '1. View source trang admin', 'expected' => 'externalApiKey không xuất hiện trong HTML response', 'priority' => 'High'),

    // Performance (5 TC)
    array('id' => 'PERF-001', 'module' => 'Hiệu năng', 'description' => 'Tải trang danh sách 600 attendee', 'preconditions' => '', 'steps' => '1. Xem danh sách toàn bộ attendee', 'expected' => 'Trang tải < 3 giây; sử dụng pagination', 'priority' => 'Medium'),
    array('id' => 'PERF-002', 'module' => 'Hiệu năng', 'description' => 'Tạo thẻ hàng loạt cho 600 người', 'preconditions' => '', 'steps' => '1. Batch generate 600 badges', 'expected' => 'Không timeout (cần xử lý background job hoặc chunking)', 'priority' => 'Medium'),
    array('id' => 'PERF-003', 'module' => 'Hiệu năng', 'description' => 'Xuất Excel 600 dòng', 'preconditions' => '', 'steps' => '1. Xuất Excel danh sách 600 attendee', 'expected' => 'File xuất thành công < 30 giây', 'priority' => 'Medium'),
    array('id' => 'PERF-004', 'module' => 'Hiệu năng', 'description' => 'Concurrent: 50 đơn vị nộp phiếu cùng lúc', 'preconditions' => '', 'steps' => '1. Simulate 50 request submit đồng thời', 'expected' => 'Không xảy ra race condition hoặc duplicate registration', 'priority' => 'High'),
);

$row = 2;
foreach ($testCases as $tc) {
    $sheet->setCellValue('A' . $row, $tc['id']);
    $sheet->setCellValue('B' . $row, $tc['module']);
    $sheet->setCellValue('C' . $row, $tc['description']);
    $sheet->setCellValue('D' . $row, $tc['preconditions']);
    $sheet->setCellValue('E' . $row, $tc['steps']);
    $sheet->setCellValue('F' . $row, $tc['expected']);
    $sheet->setCellValue('G' . $row, $tc['priority']);

    // Priority color
    $priorityColors = array(
        'Critical' => 'FF0000',
        'High' => 'FFC000',
        'Medium' => '00B050',
        'Low' => '808080',
    );
    if (isset($priorityColors[$tc['priority']])) {
        $sheet->getStyle('G' . $row)->getFill()
            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor()->setRGB($priorityColors[$tc['priority']]);
        if ($tc['priority'] == 'Critical') {
            $sheet->getStyle('G' . $row)->getFont()->getColor()->setRGB('FFFFFF');
        }
    }
    $row++;
}

// Column widths
$sheet->getColumnDimension('A')->setWidth(12);
$sheet->getColumnDimension('B')->setWidth(22);
$sheet->getColumnDimension('C')->setWidth(45);
$sheet->getColumnDimension('D')->setWidth(35);
$sheet->getColumnDimension('E')->setWidth(45);
$sheet->getColumnDimension('F')->setWidth(55);
$sheet->getColumnDimension('G')->setWidth(12);

// Wrap text
$sheet->getStyle('C2:F' . ($row - 1))->getAlignment()->setWrapText(true);
$sheet->getStyle('A2:G' . ($row - 1))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

// Borders
$sheet->getStyle('A1:G' . ($row - 1))->getBorders()->getAllBorders()
    ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

// Freeze first row
$sheet->freezePane('A2');

// Save
$outputPath = 'docs/Test_cases.xlsx';
$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
$writer->save($outputPath);

echo "Exported " . count($testCases) . " test cases to: $outputPath\n";
