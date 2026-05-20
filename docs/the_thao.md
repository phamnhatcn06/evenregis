# Cập nhật Quy tắc đặt tên và Cấu trúc lưu trữ Đội Thể thao

Thay đổi logic xử lý đăng ký môn thể thao từ việc chỉ lưu vào `RegistrationDetails` sang việc tạo trực tiếp `SportTeams` và `SportTeamMembers` theo yêu cầu.

## User Review Required

> [!WARNING]
> Việc tạo trực tiếp `SportTeams` ngay lúc nộp form (Draft) có thể dẫn đến rác dữ liệu nếu phiếu đăng ký (Registration) sau đó bị từ chối hoặc người dùng xóa phiếu. Hiện tại, bảng `sport_teams` không liên kết với `registration_id`. Xin vui lòng xác nhận bạn có muốn tạo `sport_teams` ngay tại bước này không, hay đợi đến khi phiếu được **Duyệt (Approve)** mới sinh ra team?
> Tạm thời theo yêu cầu, tôi sẽ thiết kế tạo luôn `sport_teams` ngay khi ấn Đăng ký môn thể thao.

> [!IMPORTANT]
> Về quy tắc "Chỉ khi đơn vị nhận được yêu cầu liên quân thì mới cập nhật ký hiệu đơn vị vào tên team": 
> Ở màn hình phía người gửi yêu cầu (Đơn vị tạo form), khi họ chọn liên quân với `XLA`, tên team sẽ tự động điền là `Liên quân HNO` (chưa có XLA) hay điền luôn `Liên quân HNO - XLA`? 
> Nếu để trống cho đến khi `XLA` đồng ý thì phần hiển thị lúc tạo sẽ chỉ là `Liên quân HNO`. Tôi dự kiến sẽ thiết kế: **Hiển thị luôn `Liên quân HNO - XLA`** trên UI để người dùng thấy rõ, còn backend lưu vào database. Xin hãy xác nhận nếu bạn muốn làm cách khác (ví dụ: chỉ hiện `Liên quân HNO` cho đến khi đối tác đồng ý)!

## Open Questions

1. Khi lưu vào `sport_teams`, thông tin liên quân (VD: chọn liên quân với XLA) có lưu vào bảng nào khác không (VD: `sport_team_alliances`) hay chỉ lưu dưới dạng chuỗi trong bảng `sport_teams` (thuộc tính `alliance_org_names` / `is_alliance = 1`)?
2. Trong quá trình tạo phiếu, người dùng thường có quyền chỉnh sửa. Nếu họ tạo một đội xong muốn sửa, chúng ta có hỗ trợ "Sửa" đội (thêm bớt người) không, hay chỉ hỗ trợ "Xóa" đội và tạo lại?

## Proposed Changes

### Frontend (`registrations-view.js` & `_modal_add_sport.php` & `view.php`)

- Cập nhật hàm Javascript để tự động sinh giá trị vào trường `team_name` dựa trên mã đơn vị hiện tại (`propertyCode`) và các mã đơn vị liên quân đã chọn.
  - Nếu không chọn liên quân: `team_name = "HNO"`
  - Nếu có chọn liên quân (VD `HNC`, `XLA`): `team_name = "Liên quân HNO - HNC - XLA"`.
- Thay đổi logic hiển thị bảng danh sách môn thi đấu trong `view.php`: Gọi API lấy danh sách `SportTeams` thay vì duyệt mảng `$detailsByContent['sports']`.
- Thay đổi action của nút Xóa từ `deleteDetail` sang action mới `deleteSportTeam`.

### Backend (`RegistrationsController.php`)

- **Hàm `actionAddSportRegistration`**:
  - KHÔNG sử dụng `RegistrationDetails::storeViaApi` để lưu đăng ký môn thể thao nữa.
  - Sử dụng `SportTeams::storeViaApi` để tạo mới một đội thể thao với các thông tin: `event_id`, `sport_id`, `property_id`, `team_name`, `is_alliance`.
  - Lấy `team_id` từ kết quả tạo mới, sau đó vòng lặp gọi `SportTeamMembers::storeViaApi` để thêm từng `attendee_id` đã chọn làm thành viên của đội.
- **Hàm `actionDeleteSportTeam` (MỚI)**:
  - Cung cấp API endpoint để xóa `SportTeams` (backend API của SportTeams có thể đã lo việc xóa `SportTeamMembers` tương ứng).
- **Hàm `actionView`**:
  - Load danh sách các `SportTeams` thuộc `event_id` và `property_id` hiện tại.
  - Sau đó load `SportTeamMembers` cho từng `team_id` để truyền xuống View hiển thị thay vì load qua `RegistrationDetails`.

## Verification Plan

### Manual Verification
1. Mở trang tạo phiếu, chọn môn thể thao, không chọn liên quân -> Tên đội mặc định là mã đơn vị.
2. Chọn thêm đơn vị liên quân -> Tên đội tự động cập nhật thành "Liên quân [mã đơn vị] - [mã liên quân]".
3. Ấn Đăng ký -> Kiểm tra CSDL xem bảng `sport_teams` và `sport_team_members` có được ghi vào không.
4. Kiểm tra danh sách hiển thị đã dùng dữ liệu từ `SportTeams` thay vì `RegistrationDetails`.
5. Ấn Xóa môn thi đấu -> Đội và thành viên bị xóa khỏi cả UI và DB.
