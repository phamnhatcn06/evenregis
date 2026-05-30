# HƯỚNG DẪN SỬ DỤNG HỆ THỐNG EVENTREGIS

## 4. Đăng nhập hệ thống

### 4.1 Đăng nhập hệ thống qua Portal SSO
*   **Bước 1**: Mở trình duyệt Chrome và truy cập địa chỉ hệ thống EventRegis: `http://event.mt:8080/`.
*   **Bước 2**: Hệ thống kiểm tra phiên đăng nhập. Nếu chưa đăng nhập, trình duyệt sẽ tự động chuyển hướng sang trang đăng nhập Portal Mường Thanh. Người dùng nhập các thông tin xác thực bắt buộc bao gồm:
    *   Tên đăng nhập (Email hoặc Số điện thoại)
    *   Mật khẩu
*   **Bước 3**: Nhấn nút **“Đăng nhập”**.
*   **Kết quả**: Hệ thống Portal xác thực thông tin tài khoản, tự động sinh mã Token JWT và điều hướng ngược trở lại EventRegis. Hệ thống khởi tạo Session và đưa bạn vào màn hình Dashboard chính (`http://event.mt:8080/admin/default/index`).
*   *(Hình minh họa)*

### 4.2 Đăng nhập nhanh từ trang chủ Portal (Portal Dashboard)
*   *Áp dụng trong trường hợp tài khoản đã được đăng nhập sẵn trên trình duyệt Chrome.*
*   **Bước 1**: Mở trình duyệt và truy cập trang chủ Portal: `https://portal.muongthanh.vn`.
*   **Bước 2**: Tại giao diện màn hình chính Portal (hiển thị danh mục các ứng dụng được phân quyền), người dùng tìm và click chọn biểu tượng ứng dụng **“Đăng ký Sự kiện”** (hoặc **“Event Regis”**).
*   **Bước 3**: Hệ thống Portal tự động sinh mã JWT Token và thực hiện chuyển hướng trình duyệt thẳng tới hệ thống EventRegis.
*   **Kết quả**: Bạn được đưa trực tiếp vào màn hình làm việc chính của EventRegis trong chưa đầy 1 giây mà không cần nhập lại bất kỳ thông tin nào.
*   *(Hình minh họa)*

### 4.3 Đăng nhập nhanh từ trang chủ EventRegis (Silent Login)
*   *Áp dụng trong trường hợp tài khoản đã được đăng nhập sẵn trên trình duyệt Chrome.*
*   **Bước 1**: Nhập trực tiếp địa chỉ hệ thống EventRegis trên thanh địa chỉ Chrome: `http://event.mt:8080/`.
*   **Bước 2**: Tại màn hình chào mừng của EventRegis, người dùng click vào nút màu tím **“Đăng nhập với Portal”**.
*   **Bước 3**: Hệ thống tự động chuyển hướng sang Portal để kiểm tra phiên. Do trình duyệt đã lưu session của bạn, Portal sẽ xác thực tự động ngay lập tức mà không hiển thị màn hình điền Tên đăng nhập & Mật khẩu.
*   **Kết quả**: Hệ thống điều hướng ngược lại EventRegis kèm mã sso_token, thiết lập session và cho phép bạn làm việc ngay lập tức.
*   *(Hình minh họa)*

---

## 5. Đăng ký tham gia sự kiện (Dành cho Đại diện Đơn vị)

### 5.1 Xem danh sách đợt đăng ký sự kiện
*   **Bước 1**: Đăng nhập vào hệ thống EventRegis với tư cách là Đại diện Đơn vị.
*   **Bước 2**: Trên thanh điều hướng bên trái (Sidebar), tìm và click chọn danh mục **“Đăng ký Sự kiện”** (hoặc truy cập trực tiếp `http://event.mt:8080/admin/registrations/admin`).
*   **Kết quả**: Hệ thống hiển thị bảng danh sách các đợt đăng ký sự kiện đang có trên hệ thống, bao gồm các thông tin:
    *   Tên đợt đăng ký (Sự kiện)
    *   Thời gian mở đăng ký
    *   Thời gian đóng đăng ký
    *   Trạng thái hoạt động
*   *(Hình minh họa)*

### 5.2 Khởi tạo Phiếu Đăng ký
*   **Bước 1**: Tại bảng danh sách đợt đăng ký sự kiện đang mở, người dùng click vào nút **“Khởi tạo Phiếu Đăng ký”** tương ứng với sự kiện muốn tham gia.
*   **Bước 2**: Hệ thống tự động tạo một phiếu đăng ký mới gắn liền với đơn vị thành viên của bạn.
*   **Kết quả**: Phiếu đăng ký được khởi tạo thành công với trạng thái mặc định ban đầu là **“draft”** (Bản nháp). Người dùng được chuyển đến giao diện chi tiết phiếu đăng ký để bắt đầu khai báo dữ liệu.
*   *Lưu ý nghiệp vụ*: Mỗi đơn vị chỉ được phép khởi tạo và sở hữu đúng một phiếu đăng ký hoạt động trong mỗi đợt đăng ký sự kiện.
*   *(Hình minh họa)*

### 5.3 Nhập danh sách Đại biểu & Tải lên hồ sơ pháp lý bắt buộc
*   **Bước 1**: Tại giao diện chi tiết Phiếu Đăng ký, click chọn tab **“Danh sách Đại biểu”** và nhấn nút **“Thêm đại biểu”**.
*   **Bước 2**: Lựa chọn một trong hai phương thức khai báo nhân sự:
    *   *Phương thức 1: Đồng bộ từ hệ thống SMILE (Khuyên dùng)*: Nhập Tên hoặc Mã nhân sự vào ô tìm kiếm. Click chọn nhân sự phù hợp từ danh sách gợi ý. Hệ thống sẽ tự động điền các trường thông tin: Họ tên, Chức vụ hiện tại, Mã phòng ban.
        *   *Ràng buộc nghiệp vụ*: Hệ thống chỉ hiển thị và cho phép đăng ký những nhân sự có ngày gia nhập đơn vị **trước ngày 01/06/2026**. Nhân sự gia nhập từ ngày 01/06/2026 trở đi sẽ bị chặn đăng ký.
    *   *Phương thức 2: Tự nhập thông tin*: Click chọn ô **“Tự nhập thông tin”** để điền thủ công thông tin nhân viên chính thức nếu chưa được cập nhật dữ liệu kịp thời trên SMILE.
*   **Bước 3**: Tại phần hồ sơ tài liệu đính kèm của đại biểu, người dùng tải lên đầy đủ 4 tệp tin bắt buộc:
    *   *Ảnh mặt trước CCCD*: Định dạng ảnh JPG/PNG, dung lượng tối đa 5MB.
    *   *Ảnh mặt sau CCCD*: Định dạng ảnh JPG/PNG, dung lượng tối đa 5MB.
    *   *Ảnh chân dung in thẻ (Portrait)*: Bắt buộc ảnh rõ nét, phông nền sáng và có kích thước chính xác **530x530 pixel** (Validate phía server; nếu sai kích thước, hệ thống sẽ báo lỗi và từ chối lưu).
    *   *Scan Hợp đồng lao động*: File scan định dạng PDF hoặc ảnh JPG rõ chữ ký/dấu đỏ thể hiện nhân sự chính thức, dung lượng tối đa 10MB.
*   **Bước 4**: Click nút **“Lưu lại”** để hoàn tất thêm mới đại biểu.
*   *(Hình minh họa)*

### 5.4 Thiết lập Liên quân theo từng Nội dung (Ghép đội thi đấu)
*   *Áp dụng khi các đơn vị thành viên quy mô nhỏ cần ghép nhân sự với nhau để thành lập đội thi đấu các nội dung tập thể.*
*   **Bước 1**: Tại giao diện chi tiết Phiếu Đăng ký, click chọn tab **“Liên quân”** và nhấn nút **“Gửi yêu cầu liên quân”**.
*   **Bước 2**: Khai báo biểu mẫu yêu cầu ghép đội bao gồm các trường thông tin:
    *   Đơn vị đối tác liên kết
    *   Nội dung muốn liên quân (Ví dụ: Bóng đá nam, Kéo co...)
*   **Bước 3**: Click nút **“Gửi yêu cầu”**.
*   **Kết quả**: Yêu cầu liên quân được tạo ở trạng thái **“pending”** (Chờ duyệt). Khi đơn vị đối tác đăng nhập và chọn **“Đồng ý”**, liên quân sẽ chính thức được kích hoạt (active).
*   *Lưu ý nghiệp vụ*: Số lượng đơn vị tham gia ghép đội liên quân không được vượt quá số lượng tối đa cấu hình cho nội dung đó (`max_alliance_orgs`).
*   *(Hình minh họa)*

### 5.5 Đăng ký hoạt động chi tiết cho Đại biểu
*   **Bước 1**: Tại giao diện chi tiết Phiếu Đăng ký, click chọn tab **“Đăng ký hoạt động”**.
*   **Bước 2**: Thực hiện đăng ký cho từng nội dung sự kiện:
    *   *Môn thi đấu tập thể (Đăng ký theo số lượng)*: Tích chọn tham gia và nhập số lượng đội thi đấu. Không cần chọn chi tiết danh sách thành viên ở bước này.
    *   *Môn thi cá nhân/ nghiệp vụ (Đăng ký theo danh sách)*: Click chọn nội dung thi đấu (Miss, văn nghệ cá nhân, thi nghiệp vụ...), sau đó click chọn đại biểu cụ thể trong danh sách đại biểu đã tạo ở bước 5.3 để gán vào nội dung thi đấu.
*   **Bước 3**: Click **“Lưu thông tin đăng ký”**.
*   *Kiểm duyệt ràng buộc tự động*:
    *   Mỗi đại biểu được đăng ký tham gia tối đa **3 môn thể thao root** (max_sports_per_attendee = 3).
    *   Đại biểu thi nghiệp vụ bắt buộc phải có mã phòng ban thuộc SMILE nằm trong danh mục phòng ban chuyên môn được phép thi đấu (`competition_departments`).
*   *(Hình minh họa)*

### 5.6 Nộp hồ sơ đăng ký cho HO
*   **Bước 1**: Rà soát kỹ lưỡng toàn bộ danh sách đại biểu, tệp hồ sơ đính kèm, thông tin liên quân và danh sách môn thi đã đăng ký.
*   **Bước 2**: Cuộn lên đầu trang chi tiết Phiếu Đăng ký và click nút **“Nộp đăng ký”**.
*   **Bước 3**: Xác nhận nộp trong hộp thoại cảnh báo hiện ra.
*   **Kết quả**: Trạng thái phiếu đăng ký chuyển từ **“draft”** sang **“submitted”** (Đã nộp). Hệ thống tự động kích hoạt chế độ **Khóa chỉnh sửa (Read-only)**. Đại diện Đơn vị chỉ có thể xem mà không thể sửa đổi bất kỳ thông tin nào nữa để đảm bảo tính toàn vẹn dữ liệu trong suốt quá trình kiểm duyệt của HO.
*   *(Hình minh họa)*

---

## 6. Quy Trình Kiểm Duyệt & Phê Duyệt (Dành cho Nhân sự HO)

### 6.1 Tiếp nhận & Thẩm định Hồ sơ Đại biểu
*   **Bước 1**: Đăng nhập hệ thống quản trị EventRegis bằng tài khoản Nhân sự HO (HR HO).
*   **Bước 2**: Truy cập mục **“Kiểm duyệt Đăng ký”** (hoặc `http://event.mt:8080/admin/registrations/admin`). Tìm và mở chi tiết phiếu đăng ký của đơn vị đang có trạng thái **“submitted”**.
*   **Bước 3**: Cuộn xuống danh sách đại biểu, click xem chi tiết từng nhân sự để kiểm duyệt trực quan 4 tệp hồ sơ pháp lý bắt buộc:
    *   Kiểm tra kích thước và độ rõ nét của ảnh chân dung in thẻ (đúng chuẩn 530x530px).
    *   Kiểm tra thông tin trên ảnh 2 mặt CCCD.
    *   Kiểm tra tính hợp lệ của tệp scan HĐLĐ để xác minh đại biểu là nhân sự chính thức của đơn vị.
*   *(Hình minh họa)*

### 6.2 Từ chối Phiếu Đăng ký (Reject)
*   *Áp dụng khi phát hiện hồ sơ của ít nhất một đại biểu trong danh sách bị lỗi hoặc không đạt tiêu chuẩn nghiệp vụ.*
*   **Bước 1**: Tại giao diện chi tiết phiếu đăng ký cần từ chối, HR HO click nút **“Từ chối”**.
*   **Bước 2**: Trong hộp thoại hiện ra, bắt buộc phải nhập lý do từ chối chi tiết (Ví dụ: *“Đại biểu Nguyễn Văn A ảnh chân dung không đúng 530x530px bị mờ; Đại biểu Trần Thị B ảnh CCCD mặt sau bị khuyết góc”*).
*   **Bước 3**: Click nút **“Xác nhận từ chối”**.
*   **Kết quả**: Trạng thái phiếu đăng ký chuyển sang **“rejected”** (Từ chối). Hệ thống tự động gửi thông báo đến Đại diện Đơn vị và mở khóa quyền chỉnh sửa trên phiếu đăng ký để đơn vị cập nhật hồ sơ lỗi và nộp lại.
*   *(Hình minh họa)*

### 6.3 Phê duyệt Phiếu Đăng ký (Approve)
*   *Áp dụng khi toàn bộ danh sách đại biểu và hồ sơ đính kèm của đơn vị đã đạt yêu cầu nghiệp vụ.*
*   **Bước 1**: Tại giao diện chi tiết phiếu đăng ký của đơn vị, HR HO click nút **“Phê duyệt”**.
*   **Bước 2**: Click **“Xác nhận phê duyệt”** trong hộp thoại thông báo.
*   **Kết quả**: Trạng thái phiếu chuyển sang **“approved”** (Đã duyệt) và bị khóa vĩnh viễn (Đơn vị không thể chỉnh sửa, HR HO không thể hoàn tác).
*   **Tác vụ tự động của hệ thống**: Sau khi click phê duyệt thành công, hệ thống tự động thực hiện ngầm:
    *   *Sinh mã QR duy nhất (`qr_token`)*: Tạo chuỗi token ngẫu nhiên 64 ký tự gán cho thuộc tính `qr_token` của từng đại biểu để quét tra cứu di động bảo mật.
    *   *Cấp số thứ tự in thẻ (`badge_number`)*: Tự động đánh số thứ tự in thẻ tăng dần theo sequence (Ví dụ: 001, 002, 003...) chuẩn bị in thẻ vật lý.
*   *(Hình minh họa)*

---

## 7. Liên hệ hỗ trợ

### 7.1 Bộ phận hỗ trợ kỹ thuật EventRegis
*   **Email**: `event.support@muongthanh.vn`
*   **Hotline**: `1900 xxxx` (nhánh số 2)
*   **Thời gian hỗ trợ**: Từ 08:00 đến 17:30 (Thứ 2 đến Thứ 6 hàng tuần)
