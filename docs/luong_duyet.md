# Luồng Phê Duyệt Đăng Ký — Phân tích & Kế hoạch

> Cập nhật: 2026-06-02

---

## Yêu cầu

Quay về **2 cấp**: Gửi → Duyệt (bỏ workflow đa bước).
Có thêm tính năng **Trả về** (để đơn vị chỉnh sửa và gửi lại).

---

## State Machine

```
DRAFT (0) ──── [Đơn vị nộp] ───► SUBMITTED (1)
                                        │
                              ┌─────────┼─────────┐
                              │                   │
                    [Admin duyệt]        [Admin trả về]   [Admin từ chối]
                              │                   │              │
                              ▼                   ▼              ▼
                         APPROVED (2)        RETURNED (4)   REJECTED (3)
                                                  │
                                    [Đơn vị sửa & gửi lại]
                                                  │
                                                  ▼
                                            SUBMITTED (1)
```

### Bảng trạng thái

| Mã | Hằng số | Ý nghĩa | Actor |
|----|---------|---------|-------|
| 0 | `STATUS_DRAFT` | Nháp, đang soạn | Đơn vị |
| 1 | `STATUS_SUBMITTED` | Đã nộp, chờ duyệt | Đơn vị |
| 2 | `STATUS_APPROVED` | Đã phê duyệt | Admin HO / HR |
| 3 | `STATUS_REJECTED` | Từ chối vĩnh viễn | Admin HO / HR |
| 4 | `STATUS_RETURNED` | Trả về để chỉnh sửa | Admin HO / HR |

**Phân biệt REJECTED vs RETURNED:**
- `REJECTED` = từ chối dứt khoát, hồ sơ đóng, không gửi lại được
- `RETURNED` = trả về tạm thời, đơn vị sửa xong có thể gửi lại

---

## Các vấn đề phát hiện trong code hiện tại

### 🔴 Bug nghiêm trọng — Chức năng nộp đơn bị khóa

**File:** `protected/modules/admin/controllers/RegistrationsController.php` — line 665–666

```php
// actionSubmit()
var_dump($submittedAt);
die;  // ← Debug code bỏ quên — chức năng submit HOÀN TOÀN BỊ VÔ HIỆU HÓA
```

### 🟠 Logic sai — Trả về = Từ chối

**File:** `protected/modules/admin/controllers/ApproveRegistrationsController.php` — line 561

```php
// actionReturn() đang set STATUS_REJECTED thay vì status riêng
$model->status = Registrations::STATUS_REJECTED;  // ← SAI
```

Trả về ≠ Từ chối. Cần dùng `STATUS_RETURNED = 4`.

---

## Kế hoạch implement

### Bước 1 — Xóa debug code (Bug fix)

**File:** `RegistrationsController.php::actionSubmit()`

Xóa 2 dòng:
```php
var_dump($submittedAt);
die;
```

---

### Bước 2 — Thêm STATUS_RETURNED vào Model

**File:** `protected/models/Registrations.php`

```php
const STATUS_DRAFT     = 0;
const STATUS_SUBMITTED = 1;
const STATUS_APPROVED  = 2;
const STATUS_REJECTED  = 3;
const STATUS_RETURNED  = 4;  // Thêm mới

public static function getStatusLabel($status)
{
    $labels = array(
        self::STATUS_DRAFT     => '<span class="badge bg-secondary">Nháp</span>',
        self::STATUS_SUBMITTED => '<span class="badge bg-info">Đã nộp</span>',
        self::STATUS_APPROVED  => '<span class="badge bg-success">Đã duyệt</span>',
        self::STATUS_REJECTED  => '<span class="badge bg-danger">Từ chối</span>',
        self::STATUS_RETURNED  => '<span class="badge bg-warning text-dark">Trả về</span>',
    );
    return isset($labels[$status]) ? $labels[$status] : $status;
}

public function isEditable()
{
    return in_array($this->status, array(self::STATUS_DRAFT, self::STATUS_RETURNED));
}

public function isSubmittable()
{
    return in_array($this->status, array(self::STATUS_DRAFT, self::STATUS_RETURNED));
}
```

---

### Bước 3 — Sửa actionReturn() dùng STATUS_RETURNED

**File:** `ApproveRegistrationsController.php::actionReturn()`

```php
// Trước (SAI):
$model->status = Registrations::STATUS_REJECTED;

// Sau (ĐÚNG):
$model->status = Registrations::STATUS_RETURNED;
```

Thêm field `returned_reason` nếu API backend hỗ trợ.

---

### Bước 4 — Thêm actionResubmit() cho đơn vị gửi lại

**File:** `RegistrationsController.php`

```php
public function actionResubmit($id)
{
    $this->checkRegistrationAccess($id);
    if (Yii::app()->getRequest()->getIsPostRequest()) {
        $model = $this->loadModelById($id);

        if ($model->status != Registrations::STATUS_RETURNED) {
            Yii::app()->user->setFlash('error', 'Phiếu không ở trạng thái có thể gửi lại.');
            $this->redirect(array('view', 'id' => $id));
            return;
        }

        $ssoUser = AuthHandler::getUser();
        $updateData = array(
            'status'       => Registrations::STATUS_SUBMITTED,
            'submitted_at' => date('Y-m-d H:i:s'),
            'submitted_by' => isset($ssoUser['email']) ? $ssoUser['email'] : null,
        );

        $result = $model->updateViaApi($updateData);

        if ($result['success']) {
            Attendees::resetRejectedToPending($id);
            Yii::app()->user->setFlash('success', 'Đã gửi lại phiếu đăng ký.');
        } else {
            Yii::app()->user->setFlash('error', 'Không thể gửi lại phiếu đăng ký.');
        }
        $this->redirect(array('view', 'id' => $id));
    }
}
```

---

### Bước 5 — UI Admin: approveRegistrations/admin.php

Thêm filter theo tất cả status (hiện tại chỉ lọc SUBMITTED):

```php
// ApproveRegistrationsController::actionAdmin()
// Bỏ filter cứng status = SUBMITTED
// Thêm dropdown filter status cho admin
```

Cập nhật bảng danh sách: hiển thị tất cả status, không chỉ "chờ duyệt".

---

### Bước 6 — UI Admin: approveRegistrations/view.php

**Hiện tại:** Chỉ có nút "Duyệt đăng ký" và "Trả lại".

**Cần thêm/sửa:**

```
┌─ Card thông tin đăng ký ─────────────────────────────────┐
│                                          [Duyệt] [Trả về] [Từ chối] │
│  Sự kiện: ...                                                        │
│  Đơn vị:  ...                                                        │
│  Trạng thái: [badge]                                                 │
│  Lý do trả về: (hiển thị nếu status = RETURNED)                     │
└─────────────────────────────────────────────────────────────────────┘
```

- Nút **"Duyệt"**: chỉ hiện khi `status = SUBMITTED`
- Nút **"Trả về"**: chỉ hiện khi `status = SUBMITTED` → mở modal nhập lý do
- Nút **"Từ chối"**: chỉ hiện khi `status = SUBMITTED` → mở modal nhập lý do (dùng modal hiện có `_modal_reject.php` nếu có)
- **Banner lý do trả về**: hiển thị nếu `status = RETURNED` và có `rejection_reason`

---

### Bước 7 — UI Đơn vị: registrations/view.php

**Cần thêm:**

```
┌─ Banner cảnh báo (chỉ hiện khi status = RETURNED) ──────┐
│ ⚠ Phiếu đăng ký cần được chỉnh sửa                      │
│ Lý do: [rejection_reason]                                │
│                                    [Chỉnh sửa] [Gửi lại] │
└──────────────────────────────────────────────────────────┘
```

- Nút **"Gửi lại"**: gọi `actionResubmit($id)` bằng POST
- Nút **"Chỉnh sửa"**: link đến `registrations/update/$id`
- Nút **"Nộp đăng ký"** (hiện có): đổi điều kiện hiển thị từ `STATUS_DRAFT` thành `isSubmittable()` (cả DRAFT và RETURNED)

---

## Thứ tự thực hiện

| # | Việc làm | File | Độ ưu tiên |
|---|----------|------|------------|
| 1 | Xóa `var_dump/die` trong actionSubmit | `RegistrationsController.php` | 🔴 Ngay |
| 2 | Thêm `STATUS_RETURNED = 4` + `getStatusLabel` | `Registrations.php` | 🔴 Ngay |
| 3 | Sửa `actionReturn()` dùng `STATUS_RETURNED` | `ApproveRegistrationsController.php` | 🟠 Cao |
| 4 | Thêm `actionResubmit()` | `RegistrationsController.php` | 🟠 Cao |
| 5 | UI admin `view.php`: tách nút Từ chối riêng + modal lý do trả về | `approveRegistrations/view.php` | 🟡 Trung bình |
| 6 | UI admin `admin.php`: filter theo tất cả status | `approveRegistrations/admin.php` | 🟡 Trung bình |
| 7 | UI đơn vị `view.php`: banner RETURNED + nút Gửi lại | `registrations/view.php` | 🟡 Trung bình |

---

## Ghi chú kỹ thuật

- `STATUS_RETURNED` cần được thêm vào API endpoint `REGISTRATION_UPDATE` phía backend — xác nhận backend đã hỗ trợ `status=4` chưa trước khi deploy
- `registration_approvals.status = 4 (revision)` đã tồn tại trong DB — có thể sync với `registrations.status = 4` nếu cần
- `registration_approval_logs.action = 3 (revision)` và `action = 5 (resubmitted)` đã có sẵn để ghi log
- `actionReturn()` trong `ApproveRegistrationsController` hiện không ghi log — cần bổ sung ghi vào `registration_approval_logs`
