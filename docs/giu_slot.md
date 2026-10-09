# Đặc Tả Kỹ Thuật: Giữ Slot & Cấp BIB Fun Run

> **Ngày cập nhật:** 2026-10-09  
> **Trạng thái:** Đã thống nhất đặc tả  
> **Áp dụng cho:** Module Đăng ký Fun Run (`RunEvents`, `RunRegistrations`)

---

## 1. Yêu Cầu Nghiệp Vụ

### 1.1. Mục tiêu
Ban tổ chức (BTC) có nhu cầu giữ lại một số lượng slot/số BIB nhất định (`reserved_slots`) cho các thành phần đặc biệt (VIP, Đại biểu, BTC, Khách mời) ở từng cự ly chạy.

### 1.2. Quy tắc Đánh số BIB
Mã BIB được tạo theo công thức ghép:
$$\text{Mã BIB} = \text{Số KM} + \text{Số thứ tự slot (định dạng 3 chữ số)}$$

- **Ví dụ với Cự ly 5km (`code = 5`), Tổng Quota = 320, Giữ lại 45 slot (`reserved_slots = 45`):**
  - **Dành riêng VIP/BTC:** Từ slot `001` đến `045` (tương ứng BIB từ `5001` đến `5045`).
  - **Người đăng ký công khai đầu tiên (#1):** Nhận slot `046` $\rightarrow$ Mã BIB **`5046`**.
  - **Người đăng ký công khai thứ 2 (#2):** Nhận slot `047` $\rightarrow$ Mã BIB **`5047`**.
- **Với Cự ly 10km (`code = 10`):** Slot `046` $\rightarrow$ Mã BIB **`10046`**.
- **Với Cự ly 15km (`code = 15`):** Slot `046` $\rightarrow$ Mã BIB **`15046`**.

---

## 2. Quy Tắc Hiển Thị Giao Diện (FE & UI)

### 2.1. Tiến độ đăng ký (Progress Bar & Badges)
Khi chưa có người dùng công khai nào đăng ký, hệ thống tính các slot giữ lại vào chỉ số **Đã đăng ký**:
- **Trạng thái ban đầu (Ví dụ 5km: Quota = 320, Reserved = 45):**
  - Hiển thị: **`45 / 320 đã đăng ký`**
  - Số chỗ khả dụng cho cộng đồng: **`Còn 275 chỗ`** (`remaining = 320 - 45 = 275`).
  - Tiến độ (Progress Bar): $45 / 320 \approx 14.06\%$.

### 2.2. Bảng theo dõi lượt đăng ký minh họa

| Thời điểm | Đã đăng ký hiển thị (FE) | Chỗ còn lại | Slot cấp | Mã BIB người dùng |
| :--- | :---: | :---: | :---: | :---: |
| **Chưa mở công khai** | **45 / 320** | **275 chỗ** | Slot 46 | *(Chờ)* |
| **Công khai #1** | **46 / 320** | **274 chỗ** | **Slot 46** | **`5046`** |
| **Công khai #2** | **47 / 320** | **273 chỗ** | **Slot 273** | **`5047`** |
| **...** | ... | ... | ... | ... |
| **Công khai cuối cùng (#275)** | **320 / 320** | **0 chỗ (Hết chỗ)** | **Slot 320** | **`5320`** |

---

## 3. Kiến Trúc Kỹ Thuật & Bảo Vệ Quota

### 3.1. Cập nhật Cơ sở dữ liệu (Database Schema)
- Bảng `run_events`: Thêm cột `reserved_slots` (`INT UNSIGNED NOT NULL DEFAULT 0`).
- Ràng buộc dữ liệu: `quota > reserved_slots`.

### 3.2. Chống Race Condition & Vượt Quota (Database Atomic Update)
Đảm bảo tuyệt đối không bị vỡ Quota khi nhiều người cùng bấm đăng ký đồng thời:

```sql
UPDATE run_events
   SET registered_count = registered_count + 1
 WHERE id = :run_event_id
   AND status = 'open'
   AND (open_at IS NULL OR UNIX_TIMESTAMP() >= open_at)
   AND (close_at IS NULL OR UNIX_TIMESTAMP() <= close_at)
   AND (reserved_slots + registered_count) < quota; -- VÙNG RÀNG BUỘC AN TOÀN NGUYÊN TỬ
```

- Nếu `(reserved_slots + registered_count) >= quota`: Câu lệnh SQL trả về `0 rows affected` $\rightarrow$ Rollback transaction $\rightarrow$ Phản hồi lỗi **HTTP 409 (Hết chỗ)**.

### 3.3. Tính toán Mã BIB ở Backend

```php
// 1. k là lượt đăng ký công khai thành công (tăng từ 1)
$k = $runEvent->registered_count + 1; 

// 2. Số thứ tự slot thực tế cấp cho người đăng ký
$slotNumber = $runEvent->reserved_slots + $k; 

// 3. Ghép mã BIB: [Mã cự ly (Số KM)] + 3 chữ số slot
$bibNumber = $runEvent->code . sprintf('%03d', $slotNumber);
```

---

## 4. Danh Sách Kiểm Tra (Checklist Triển Khai)

- [ ] **Migration:** Thêm cột `reserved_slots` vào bảng `run_events`.
- [ ] **Admin UI:** Thêm ô nhập `reserved_slots` trong form tạo/sửa cự ly (`RunEventsController`).
- [ ] **Backend API:** Cập nhật Atomic UPDATE `(reserved_slots + registered_count) < quota` + logic sinh `bib_number = code . sprintf('%03d', reserved_slots + k)`.
- [ ] **Frontend UI:** Cập nhật tính toán hiển thị `(reserved_slots + registered_count) / quota` và chỗ còn lại.
