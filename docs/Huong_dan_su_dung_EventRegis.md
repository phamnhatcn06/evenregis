# TÀI LIỆU HƯỚNG DẪN SỬ DỤNG HỆ THỐNG EVENTREGIS
**Hệ Thống Quản Lý Sự Kiện Đại Hội Tập Trung**  
*Phiên bản: 2.0 (Cập nhật 2026)*

---

## 1. Tổng Quan Hệ Thống & Phân Quyền Sử Dụng

Hệ thống **EventRegis** là giải pháp số hóa toàn diện được thiết kế để quản lý vòng đời của đại hội tập trung với quy mô ~600 đại biểu từ 50-100 đơn vị trực thuộc. Hệ thống bao gồm các phân hệ: Đăng ký thông tin đại biểu trực tuyến, kiểm duyệt hồ sơ, in thẻ tích hợp mã QR, phân bố sơ đồ bàn tiệc, quản lý thi nghiệp vụ, thi đấu thể thao chuyên nghiệp và trang tra cứu lịch trình cá nhân hóa qua mã QR.

### Bảng phân quyền chi tiết (Actors Permission Matrix)

| Vai trò (Actor) | Tài khoản sử dụng | Quyền hạn & Luồng nghiệp vụ chính |
| :--- | :--- | :--- |
| **Đại diện Đơn vị** | Tài khoản đơn vị (`unit_accounts`) | - Gửi và phê duyệt yêu cầu liên quân theo môn.<br>- Nhập danh sách đại biểu (đồng bộ từ SMILE hoặc nhập tay).<br>- Tải lên hồ sơ bắt buộc (Ảnh chân dung 530x530px, ảnh CCCD 2 mặt, Hợp đồng lao động).<br>- Đăng ký tham gia hoạt động (theo số lượng đội hoặc chi tiết người).<br>- Ghép đội thi đấu thể thao (ghép thành viên liên quân).<br>- Nộp phiếu và theo dõi phê duyệt. |
| **Nhân sự HO (HR)** | Tài khoản nội bộ (`users` - `role=hr`) | - Xem danh sách đăng ký của tất cả các đơn vị.<br>- Xem chi tiết hồ sơ, ảnh chân dung, CCCD, hợp đồng của đại biểu.<br>- Phê duyệt (Approve) toàn bộ phiếu (hệ thống tự sinh `qr_token` và cấp số thẻ `badge_number`).<br>- Từ chối (Reject) phiếu đăng ký kèm nhập lý do lỗi cụ thể.<br>- Gán vai trò sự kiện cho đại biểu, chỉ định Trưởng đoàn của đơn vị. |
| **Admin HO** | Tài khoản admin (`users` - `role=admin`) | - Toàn quyền cấu hình hệ thống.<br>- Thiết lập các đợt đăng ký và số môn thể thao tối đa của đại biểu.<br>- Quản lý in thẻ đại biểu hàng loạt (Badge Management). |
| **Trưởng đoàn** | Tài khoản đại biểu (có flag `is_team_lead=1`) | - Xem danh sách thành viên đoàn mình.<br>- Báo cắt suất ăn từng người hoặc toàn bộ đoàn (bulk cutoff) trước giờ khóa sổ. |
| **BTC Chuyên môn** | Tài khoản ban ngành (`users` - `role=competition/sports/banquet`) | - **BTC Nghiệp vụ**: Cấu hình phòng ban thi đấu, tự động sinh số báo danh (SBD) theo sequence, xuất danh sách phòng thi.<br>- **BTC Thể thao**: Thiết lập môn đấu (cha-con), lịch thi đấu (bracket/round robin), cập nhật tỉ số live score và tự tính xếp hạng.<br>- **BTC Tiệc**: Kéo thả sơ đồ bàn tiệc canvas, xếp ghế cho đại biểu. |
| **Đại biểu (Public)** | Không cần tài khoản | - Quét QR Code trên thẻ để xem Trang cá nhân di động, tra cứu Agenda chung, lịch thi nghiệp vụ riêng và lịch đấu thể thao của đoàn mình. |

---

## 2. Hướng Dẫn Dành Cho Đại Diện Đơn Vị

Đại diện Đơn vị thực hiện quy trình đăng ký cho đoàn của mình thông qua các bước trình tự sau:

### Bước 2.1: Đăng nhập & Thiết lập Liên quân theo từng Nội dung (Content-level Alliance)
Cơ chế **Liên quân theo nội dung** cho phép các đơn vị ghép nhân sự vào các đội thi đấu thể thao hoặc tiết mục tập thể độc lập theo từng môn, không bị bó buộc ở cấp sự kiện.
1. Đăng nhập tài khoản đơn vị được cấp.
2. Truy cập menu **Liên quân** -> Click nút **Gửi yêu cầu liên quân**.
3. Chọn đơn vị đối tác muốn ghép chung, chọn nội dung muốn liên quân (Ví dụ: *Bóng đá nam*), nhập ghi chú và nhấn **Gửi yêu cầu**.
4. Trạng thái yêu cầu sẽ lưu là `Chờ duyệt` (pending). Đơn vị đối tác sau khi đăng nhập sẽ nhìn thấy yêu cầu này trong danh sách nhận được và có thể click **Chấp nhận** (yêu cầu chuyển sang trạng thái active) hoặc **Từ chối** kèm lý do.

> **Ràng buộc nghiệp vụ:** Mỗi nội dung thi đấu có cấu hình số lượng đơn vị liên quân tối đa riêng biệt (`max_alliance_orgs`). Hệ thống sẽ tự động chặn nếu đơn vị cố tình gửi vượt quá số lượng đối tác cho phép.

### Bước 2.2: Nhập danh sách Đại biểu & Upload tài liệu bắt buộc
Trước khi tiến hành đăng ký môn thi hay ghép đội, đơn vị bắt buộc phải điền đầy đủ danh sách đại biểu tham gia để phục vụ việc in thẻ và thẩm định hồ sơ:
1. Vào phân hệ **Danh sách Đại biểu** -> Click nút **Thêm người tham dự**.
2. **Chọn nguồn dữ liệu**:
   - *Chọn từ danh sách nhân viên*: Nhập tên tìm kiếm nhân viên được đồng bộ từ hệ thống dữ liệu SMILE. Hệ thống sẽ tự động điền các thông tin: Họ tên, Chức vụ hiện tại, Phòng ban.
   - *Tự điền thông tin*: Tích chọn ô này nếu nhân sự chưa có trên hệ thống SMILE để nhập thủ công các trường Họ tên, Chức danh hiển thị trên thẻ.
3. **Tải lên tài liệu đính kèm bắt buộc (4 file)**:
   - **Ảnh CCCD Mặt trước**: Định dạng JPG/PNG, dung lượng < 5MB.
   - **Ảnh CCCD Mặt sau**: Định dạng JPG/PNG, dung lượng < 5MB.
   - **Ảnh chân dung in thẻ**: Định dạng JPG/PNG. **Lưu ý đặc biệt:** Bắt buộc ảnh tải lên phải có kích thước chuẩn xác là **530x530 pixel**. Hệ thống kiểm tra kích thước ảnh ở phía server; nếu ảnh sai kích thước, hệ thống sẽ báo lỗi validation và chặn không cho lưu hồ sơ.
   - **Scan Hợp đồng lao động**: File PDF hoặc hình ảnh scan rõ chữ ký/dấu đỏ thể hiện đại biểu là nhân sự chính thức của đơn vị, dung lượng < 10MB.
4. Nhấn **Lưu thông tin**.

### Bước 2.3: Đăng ký tham gia hoạt động (Event Registration)
Đại diện đơn vị vào trang **Đăng ký hoạt động** để tick chọn các bộ môn đơn vị sẽ tham gia tranh tài:
- **Môn đăng ký theo số lượng (Quantity-based):** Áp dụng cho các môn thể thao tập thể (Bóng đá, Kéo co, Cầu lông đôi...). Đơn vị chỉ cần nhập số lượng đội đăng ký (Ví dụ: Bóng đá nam - 2 đội). Bước này chưa cần điền tên vận động viên chi tiết.
- **Môn đăng ký theo danh sách chi tiết (Detailed-based):** Áp dụng cho thi nghiệp vụ, thi Miss, thi văn nghệ. Người dùng phải chọn trực tiếp đại biểu cụ thể trong danh sách đại biểu đã tạo ở Bước 2.2 để xếp vào nội dung đăng ký ngay lập tức. Số lượng người đăng ký bị khống chế theo giới hạn tối đa của cuộc thi (`max_per_org`).

### Bước 2.4: Ghép đội thi đấu thể thao (Create Teams)
Sau khi Phiếu đăng ký được HR HO kiểm tra và duyệt thành công:
1. Đại diện đơn vị truy cập vào menu **Quản lý Đội hình** -> Chọn bộ môn -> Click **Tạo đội**.
2. Nhập tên đội thi đấu (Ví dụ: *Đội bóng Mường Thanh Hà Nội*).
3. Tại danh sách lựa chọn thành viên:
   - Nếu môn thi đấu này đơn vị **không** có liên quân active: Danh sách chỉ hiển thị các đại biểu thuộc đơn vị mình.
   - Nếu môn thi đấu này đơn vị **đã có liên quân hoạt động** (ở Bước 2.1): Danh sách chọn sẽ tự động hiển thị gộp toàn bộ đại biểu của đơn vị mình cùng đại biểu của (các) đơn vị đối tác liên quân để người dùng thoải mái tick ghép chung thành viên vào một đội.
4. Chọn các vị trí (Hậu vệ, tiền đạo, thủ môn...), chỉ định số áo, chọn **Đội trưởng** (Captain) và nhấn **Lưu đội**.

---

## 3. Hướng Dẫn Ban Nhân Sự HO & Admin

Nhân sự HO và Admin quản trị toàn bộ hoạt động đăng ký, duyệt chất lượng hồ sơ và thực hiện các tác vụ in ấn thẻ đại biểu vật lý.

### Bước 3.1: Tiếp nhận và Phê duyệt/Từ chối đăng ký đại biểu
Khi đại diện đơn vị nhấn nút **Nộp đăng ký**, phiếu đăng ký của đơn vị chuyển trạng thái sang `submitted` (đã nộp) và khóa hoàn toàn quyền chỉnh sửa của đơn vị.
1. Nhân sự HR HO truy cập danh sách phiếu đăng ký đang chờ xử lý.
2. Click xem chi tiết từng đại biểu trong danh sách của đơn vị:
   - Click preview ảnh chân dung in thẻ (đảm bảo đúng quy chuẩn, sắc nét, không bị nhòe).
   - Kiểm tra ảnh CCCD 2 mặt và tệp scan Hợp đồng lao động để xác thực đại biểu là nhân sự hợp pháp của đơn vị, tránh gian lận vận động viên chuyên nghiệp bên ngoài tham dự thể thao đại hội.
3. **Đưa ra quyết định**:
   - **Phê duyệt (Approve):** Nếu tất cả hồ sơ đại biểu đạt chuẩn, nhấn **Phê duyệt**. Hệ thống sẽ tự động thực hiện 2 thao tác ngầm: Tạo mã token QR Code ngẫu nhiên duy nhất (`qr_token`) và cấp số thứ tự in thẻ (`badge_number` tăng dần theo sequence) cho từng đại biểu của đơn vị đó.
   - **Từ chối (Reject):** Nếu phát hiện hồ sơ bị lỗi (ví dụ: ảnh chân dung chụp nghiêng, scan hợp đồng mờ, sai phòng ban thi nghiệp vụ...), nhấn **Từ chối** và **bắt buộc phải điền rõ lý do cụ thể**. Phiếu đăng ký chuyển về trạng thái `rejected` và mở khóa quyền sửa đổi để đơn vị cập nhật hồ sơ lỗi và nộp lại.

### Bước 3.2: Quản lý & In thẻ đại biểu hàng loạt (Badge Management)
1. Admin truy cập trang **Badge Management** (Quản lý thẻ đại biểu).
2. Sử dụng bộ lọc thông minh ở phía trên: Lọc theo đơn vị trực thuộc, theo vai trò sự kiện hoặc trạng thái in ấn (Đã in / Chưa in).
3. **Lưới Preview thẻ**: Hiển thị mô phỏng các thẻ đại biểu CR80 hoàn chỉnh theo đúng chuẩn kích thước thực tế (85.6x53.98mm) với đầy đủ thông tin: Ảnh chân dung 530x530px, Họ tên, Chức vụ, Đơn vị hiển thị, Mã QR chứa liên kết cá nhân hóa, và dải màu nổi bật ở chân thẻ chỉ định vai trò (Vai trò VIP màu đỏ rượu, Giám đốc màu tím, Khách mời màu xám, BTC màu hồng sẫm...).
4. **Xuất lô in ấn hàng loạt**:
   - Click **Tải xuống ảnh ZIP**: Hệ thống tự động đóng gói toàn bộ ảnh thẻ chất lượng cao 300 DPI dạng file PNG vào một tệp nén ZIP để gửi trực tiếp cho đơn vị in thẻ nhựa.
   - Click **Xuất lô PDF**: Hệ thống ghép các thẻ đại biểu thành một file PDF nhiều trang để phục vụ việc in ấn trực tiếp từ máy in văn phòng.
5. Sau khi hoàn thành in ấn vật lý, Admin click chọn đại biểu và chọn **Đánh dấu đã in thẻ** (`badge_printed = 1`) để theo dõi thống kê tiến độ in thẻ của đại hội.

---

## 4. Hướng Dẫn Dành Cho Trưởng Đoàn (Team Lead)

Đại biểu được Admin gán cờ Trưởng đoàn (`is_team_lead = 1`) chịu trách nhiệm kiểm soát hậu cần ăn uống của toàn bộ thành viên trong đoàn mình nhằm tránh lãng phí chi phí bữa ăn của ban tổ chức.

### Quy trình Báo cắt suất ăn (Meal Cutoff)
1. Trưởng đoàn sử dụng mã truy cập quét QR Code hoặc đăng nhập bằng tài khoản đại biểu để vào phân hệ **Quản lý Suất ăn**.
2. Màn hình hiển thị danh sách các bữa ăn được tổ chức theo ngày đại hội (Bữa sáng, trưa, tối).
3. **Báo cắt ăn lẻ tẻ:** Tích chọn checkbox trước tên đại biểu muốn báo cắt suất ăn cho bữa ăn cụ thể đó, nhập lý do (tùy chọn) và nhấn **Lưu thay đổi**.
4. **Báo cắt ăn toàn đoàn (Bulk Cutoff):** Click chọn nút **Báo cắt ăn toàn đoàn** để hủy nhanh suất ăn của toàn bộ đoàn mình cho bữa ăn đó chỉ với 1 click chuột.
5. Suất ăn báo hủy thành công sẽ tự động trừ trực tiếp vào bảng số liệu nhà bếp chuẩn bị thực phẩm.

> **Quy định giờ khóa sổ (Cutoff Deadline):** Trưởng đoàn chỉ được phép báo cắt ăn trước giờ khóa sổ được quy định cho từng bữa ăn (Ví dụ: Bữa trưa khóa sổ báo cắt lúc 9:00 sáng cùng ngày, Bữa tối khóa sổ lúc 3:00 chiều cùng ngày). Nếu quá giờ, nút thao tác sẽ tự động bị mờ và hệ thống hiện cảnh báo: *"Đã quá giờ khóa sổ quy định, không thể thay đổi suất ăn."*

---

## 5. Hướng Dẫn Các Ban Tổ Chức Chuyên Môn

Hệ thống cung cấp các công cụ đồ họa trực quan và tự động hóa giúp các tiểu ban chuyên môn quản lý đại hội thuận tiện.

### 5.1 Ban Tổ Chức Thi Nghiệp Vụ
BTC Thi nghiệp vụ truy cập phân hệ **Thi Nghiệp Vụ**:
- **Cấu hình giới hạn phòng ban:** Để đảm bảo tính công bằng chuyên môn, BTC cấu hình giới hạn các mã phòng ban được phép đăng ký tham gia thi (Ví dụ: Cuộc thi Lễ tân xuất sắc chỉ chấp nhận đại biểu có mã phòng ban thuộc SMILE là `LETAN` hoặc `CSKH`). Nếu đơn vị chọn đại biểu sai phòng ban, hệ thống sẽ báo lỗi validation ngay lập tức.
- **Cấp Số báo danh tự động:** Sau khi chốt danh sách, BTC click nút **Cấp SBD tự động**. Hệ thống tự động tạo mã số báo danh tăng dần dựa trên cấu hình tiền tố cuộc thi (prefix), số bắt đầu và độ rộng đệm. Ví dụ: Cuộc thi Lễ tân có prefix='LT', start=1, pad=3 -> LT001, LT002, LT003...
- **Xuất danh sách phòng thi:** BTC click xuất file Excel danh sách thí sinh chính thức bao gồm đầy đủ SBD, tên đơn vị và phòng thi để in ấn dán trước phòng thi.

### 5.2 Ban Tổ Chức Thể Thao
BTC Thể thao quản lý lịch thi đấu và hiển thị trực quan nhánh đấu:
- **Thiết lập nhánh đấu:** Khởi tạo lịch thi đấu của môn thể thao (theo cấu trúc cha-con, ví dụ môn gốc 'Bóng đá' -> môn con 'Bóng đá nam', 'Bóng đá nữ'). Hỗ trợ tự động phân chia bảng đấu vòng tròn tính điểm (round robin) hoặc thi đấu loại trực tiếp (knockout) hiển thị dạng sơ đồ bracket trực quan.
- **Cập nhật tỉ số trực tiếp (Live Score):** Trọng tài hoặc BTC cập nhật tỉ số trực tiếp của trận đấu ngay tại sân qua giao diện điện thoại. Hệ thống sẽ tự động tính toán lại bảng xếp hạng standings (đối với bảng đấu vòng tròn) hoặc tự động đẩy đội thắng lên nhánh đấu tiếp theo (đối với thể thức knockout) hiển thị thời gian thực ra màn hình công cộng.

### 5.3 Ban Tổ Chức Tiệc & Phân chỗ ngồi
BTC Tiệc truy cập trang thiết kế sơ đồ tiệc trực quan bằng Canvas:
- **Thiết kế sơ đồ tiệc:** BTC kéo thả để đặt vị trí các bàn tiệc dạng hình tròn hoặc chữ nhật trên canvas.
  - *Bàn VIP:* Thiết lập màu Burgundy đỏ rượu sang trọng, đặt ở hàng ghế đầu gần khu vực sân khấu biểu diễn.
  - *Bàn thường:* Thiết lập màu xanh Navy thanh lịch dành cho các đoàn đại biểu đơn vị.
- **Phân chia ghế ngồi trực quan:** Click vào một bàn cụ thể trên canvas để hiển thị 10 vị trí ghế trống. Kéo thả trực tiếp tên đại biểu từ danh sách đại biểu chưa phân ghế ở thanh sidebar bên phải thả vào vị trí ghế mong muốn để xếp chỗ ngồi chi tiết.

---

## 6. Hướng Dẫn Dành Cho Đại Biểu (Public QR Page)

Đại biểu sau khi nhận được thẻ in vật lý chỉ cần sử dụng camera điện thoại quét mã QR in trên thẻ để truy cập Trang cá nhân di động (Responsive Mobile Landing Page) mà không cần tạo tài khoản đăng nhập:
1. **Thẻ Đại biểu Điện tử:** Phần trên cùng hiển thị thẻ điện tử sắc nét tích hợp ảnh đại diện, vai trò và thông tin cá nhân.
2. **Lịch trình cá nhân (My Agenda):** Hiển thị chi tiết giờ họp, địa điểm phòng hội nghị, chỗ ngồi tiệc tối của chính đại biểu đó.
3. **Lịch thi nghiệp vụ:** Tra cứu nhanh số báo danh, giờ thi phòng thi nghiệp vụ cá nhân (nếu đại biểu có đăng ký thi nghiệp vụ).
4. **Lịch thi đấu đoàn:** Xem lịch thi đấu và tỉ số trực tiếp tất cả các môn thể thao mà các đội đại diện cho đơn vị mình đang tham gia thi đấu tại đại hội.
5. **Agenda Đại hội chung:** Tra cứu nhanh chương trình tổng quan của đại hội theo từng ngày.
