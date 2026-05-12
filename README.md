# Website Đăng Ký và Triển Khai Sự Kiện (Event Registration System)

Hệ thống quản lý toàn bộ vòng đời của một sự kiện/đại hội tập trung quy mô lớn, hỗ trợ quản lý người tham dự từ nhiều đơn vị khác nhau, tổ chức thi đấu nghiệp vụ, thể thao, sắp xếp tiệc và cung cấp thông tin lịch trình qua mã QR.

---

## 🌟 Tính Năng Chính

*   **Quản lý Đăng ký:** Cho phép các đơn vị đăng ký danh sách người tham dự theo khung thời gian quy định. Phê duyệt danh sách tập trung từ Trụ sở chính (HO).
*   **Quản lý Thẻ & Mã QR:** Cấp, tạo và in thẻ tham dự có mã QR Code. Người tham dự có thể quét QR để xem thông tin cá nhân, lịch trình sự kiện mà không cần cài đặt thêm ứng dụng.
*   **Quản lý Thi Đấu Thể Thao & Nghiệp Vụ:** Hỗ trợ lên lịch, cấp số báo danh, chia bảng thi đấu, nhập điểm và kết quả cho các môn thi nghiệp vụ, thể thao, văn nghệ, thi sắc đẹp (Miss).
*   **Quản lý Tiệc & Bữa Ăn:** Quản lý sơ đồ bàn tiệc, phân bổ chỗ ngồi. Trưởng đoàn có thể báo cắt ăn cho các thành viên.
*   **Phân Quyền Đa Dạng:** Nhiều vai trò như Admin, HR, BTC Thi nghiệp vụ, BTC Thể thao, BTC Tiệc, Trưởng đoàn, Đại diện đơn vị.

---

## 🛠 Tech Stack

*   **Framework:** Yii 1 PHP Framework (PHP 5.6 — 7.4)
*   **Cơ Sở Dữ Liệu:** MySQL / MariaDB
*   **Frontend UI:** Bootstrap (thông qua YiiBooster), Theme Hope UI
*   **Thư viện hỗ trợ:** mPDF, HTML2PDF, ChartJS, MobileDetect, PHPExcel

---

## 📂 Cấu Trúc Thư Mục

```text
eventregis/
├── assets/                 # Các file assets sinh tự động của Yii
├── docs/                   # Tài liệu phân tích thiết kế hệ thống
├── framework/              # Lõi của Yii 1 framework
├── protected/              # Thư mục mã nguồn chính (Controllers, Models, Views, Config)
│   ├── components/         # Các thành phần dùng chung
│   ├── config/             # Cấu hình hệ thống (main.php, database.php)
│   ├── controllers/        # Các Controllers chính của ứng dụng
│   ├── extensions/         # Các thư viện mở rộng (booster, chartjs, mpdf,...)
│   ├── models/             # Các Models làm việc với CSDL
│   ├── modules/            # Các modules chức năng (admin, frontend, analytic)
│   └── views/              # Các file giao diện (Views)
├── themes/                 # Thư mục giao diện (Hope UI)
└── index.php               # Điểm khởi chạy của ứng dụng (Entry script)
```

---

## 🚀 Hướng Dẫn Cài Đặt (Local Development)

### Yêu Cầu Hệ Thống

*   Web Server: Apache / Nginx / XAMPP / WAMP
*   PHP Version: **5.6 - 7.4**
*   Database: MySQL / MariaDB

### Các Bước Cài Đặt

1.  **Clone mã nguồn**
    ```bash
    git clone https://github.com/phamnhatcn06/evenregis.git
    cd evenregis
    ```

2.  **Cấu hình Cơ Sở Dữ Liệu**
    *   Tạo một database trên MySQL.
    *   Import file SQL (nếu có) để khởi tạo các bảng và dữ liệu mẫu.
    *   Mở file cấu hình database: `protected/config/database.php`
    *   Cập nhật thông tin kết nối CSDL (host, dbname, username, password).

    ```php
    return array(
        'connectionString' => 'mysql:host=localhost;dbname=ten_database',
        'emulatePrepare' => true,
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
    );
    ```

3.  **Cấu hình Quyền (Permissions)**
    Đảm bảo các thư mục sau có quyền ghi (write permissions):
    *   `assets/`
    *   `protected/runtime/`

4.  **Chạy Ứng Dụng**
    Truy cập vào ứng dụng thông qua đường dẫn localhost tương ứng với thư mục dự án của bạn:
    ```text
    http://localhost/eventregis/
    ```

---

## 🔒 Quy Định Bảo Mật

*   **Không bao giờ** commit các file chứa mật khẩu, API keys hay cấu hình nhạy cảm lên Git (`protected/config/database.php`).
*   Bảo vệ thư mục `protected` để không cho phép truy cập trực tiếp từ trình duyệt (sử dụng `.htaccess` đối với Apache).
*   Luôn escape/validate dữ liệu đầu vào theo chuẩn của Yii để tránh SQL Injection và XSS.

---

## 👥 Đóng Góp (Contributing)

*   Tạo branch mới từ `main` cho tính năng bạn muốn phát triển.
*   Tuân thủ tiêu chuẩn code (Code Convention) của dự án.
*   Tạo Pull Request và yêu cầu review trước khi merge.
