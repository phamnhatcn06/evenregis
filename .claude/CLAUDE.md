# Event Registration System - AI Agent Configuration

## Overview

Hệ thống quản lý sự kiện đại hội (~600 người tham dự) sử dụng **Yii 1.x PHP Framework**.

**Mục đích chính:**
- Quản lý đăng ký tham dự theo đơn vị trong khung thời gian quy định
- Phê duyệt danh sách tập trung từ HO (Head Office)
- Cấp và in thẻ tham dự có QR Code (85×54mm - thẻ CR80)
- Quản lý thi nghiệp vụ, thi đấu thể thao, tiệc và bữa ăn
- Quản lý thi sắc đẹp (Miss) và văn nghệ
- Cung cấp thông tin lịch trình qua quét QR

---

## Tech Stack

| Component | Technology |
|-----------|------------|
| Framework | **Yii 1.x** (PHP MVC) |
| PHP Version | 7.4+ |
| Database | MySQL (InnoDB, utf8mb4) |
| UI Theme | Hope UI (Bootstrap 5) |
| Icons | Font Awesome 4.7 (local) |
| Authentication | JWT từ Portal SSO |
| QR Code | php-qrcode extension |
| Excel Export | PHPExcel |
| Email | yii-mail (SwiftMailer) |

### Asset Rules

- **KHÔNG sử dụng CDN** cho CSS/JS libraries. Tải file về local và đặt trong `themes/hope-ui/assets/`
- Font Awesome 4.7: `themes/hope-ui/assets/css/font-awesome/font-awesome.min.css`
- Fonts: `themes/hope-ui/assets/fonts/`

### UI/Text Rules

- **Tất cả text hiển thị** (labels, messages, placeholders, headers) phải là **tiếng Việt có dấu**
- Ví dụ: "Tên khu vực", "Trạng thái", "Ngày tạo", "Thêm đơn vị", "Lưu thay đổi"

### JavaScript Rules

- **KHÔNG viết inline JS** trong file view (không dùng `<script>` tag trực tiếp)
- Tạo file JS riêng cho từng page trong `themes/hope-ui/assets/js/pages/`
- Đặt tên file theo format: `{controller}-{action}.js` (vd: `events-view.js`)
- Register JS bằng Yii:
```php
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/events-view.js',
    CClientScript::POS_END
);
```

---

## Project Structure

```
eventregis/
├── .claude/                          # AI Agent configuration
│   ├── agents/                       # Sub-agent definitions
│   ├── commands/                     # Reusable command workflows
│   ├── references/                   # Reference checklists
│   ├── rules/                        # Mandatory coding rules
│   ├── skills/                       # Specialized AI skills
│   └── CLAUDE.md                     # This file
│
├── docs/
│   └── system-design.md              # Full system design document (50 tables)
│
├── protected/                        # Application core (Yii protected folder)
│   ├── commands/                     # Console commands
│   ├── components/                   # Base controllers & helpers
│   │   ├── Controller.php            # Base controller
│   │   ├── AdminController.php       # Admin base controller (check session)
│   │   ├── AuthHandler.php           # JWT validation + session management
│   │   ├── ApiClient.php             # Gọi External API với API Key
│   │   ├── ApiEndpoints.php          # Centralized API endpoint constants
│   │   ├── PermissionHelper.php      # Check CRUD permissions
│   │   ├── MyHelper.php              # Utility functions
│   │   └── ...
│   ├── config/
│   │   ├── main.php                  # Main config
│   │   ├── params.php                # API key, Portal URL, session config
│   │   └── console.php               # Console config
│   ├── extensions/                   # Yii extensions
│   │   ├── jwt/                      # JWT decode library
│   │   ├── booster/                  # Bootstrap widgets
│   │   ├── php-qrcode/               # QR Code generator
│   │   ├── phpexcel/                 # Excel export
│   │   └── yii-mail/                 # Email sending
│   ├── models/                       # Database models
│   │   └── _base/                    # Base models (auto-generated)
│   ├── modules/
│   │   ├── admin/                    # Admin module
│   │   │   ├── controllers/
│   │   │   └── views/
│   │   └── frontend/                 # Frontend module (public QR page)
│   ├── runtime/                      # Runtime files (logs, cache)
│   └── views/layouts/                # Layout templates
│
├── themes/hope-ui/                   # Hope UI dashboard theme
├── uploads/                          # Uploaded files (photos)
├── badges/                           # Generated badge images
├── admin.php                         # Admin entry point
└── index.php                         # Public entry point (QR scan)
```

---

## Authentication Flow (Portal SSO + JWT)

### Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────────┐
│                     portal.muongthanh.vn                                 │
│  User đăng nhập → Click button "Event Regis"                            │
│  → Redirect với JWT token trong URL                                      │
└───────────────────────────────┬─────────────────────────────────────────┘
                                │ redirect?token=<JWT>
                                ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                      FRONTEND (Yii1 MVC)                                 │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐                   │
│  │ AuthHandler  │  │ ApiClient    │  │ Controllers  │                   │
│  │ - validate   │  │ - API key    │  │ + Views      │                   │
│  │   JWT token  │  │ - gọi API    │  │              │                   │
│  │ - create     │  │   external   │  │              │                   │
│  │   session    │  │              │  │              │                   │
│  └──────────────┘  └──────────────┘  └──────────────┘                   │
└─────────────────────────────────────────────────────────────────────────┘
```

### Login Flow

```
Portal → /auth/callback?token=<JWT>
              ↓
    AuthHandler::handleCallback($token)
              ↓
    Decode + validate JWT
              ↓
    Create Yii session (user info, permissions)
              ↓
    Call SSO API: GET https://api.portal.muongthanh.vn/api/sso/me
              ↓
    Return user profile → JS saves to localStorage
              ↓
    Redirect → Dashboard
```

### SSO User Profile API

Sau khi redirect từ Portal, hệ thống gọi SSO API để lấy đầy đủ thông tin người dùng:

```
GET https://api.portal.muongthanh.vn/api/sso/me
Authorization: Bearer <JWT_TOKEN>
```

**Response:** Toàn bộ thông tin user profile từ Portal.

**Sử dụng trong PHP:**
```php
// Lấy profile sau login
$profile = AuthHandler::fetchUserProfile($token);

// Hoặc lấy từ session token hiện tại
$profile = AuthHandler::getUserProfileForClient();
```

**Lưu vào localStorage (JS):**
```javascript
// Trong callback page, nhận data từ PHP và lưu
localStorage.setItem('sso_user_profile', JSON.stringify(userProfile));
```

### JWT Token Payload từ Portal

```json
{
  "sub": "12345",
  "username": "nguyenvana",
  "full_name": "Nguyễn Văn A",
  "email": "nguyenvana@muongthanh.vn",
  "unit_code": "HN01",
  "permissions": {
    "event": "1 1 1 1",
    "registration": "1 1 1 0",
    "attendee": "1 1 1 1",
    "badge": "1 0 0 0",
    "sport": "1 1 1 1",
    "competition": "1 1 1 1",
    "meal": "1 1 0 0",
    "report": "1 0 0 0"
  },
  "iat": 1714838400,
  "exp": 1714842000
}
```

**Permission format**: `"controller": "C R U D"` (1=có quyền, 0=không)

| Position | Operation | Actions |
|----------|-----------|---------|
| 0 | Create | create, store |
| 1 | Read | index, view, list |
| 2 | Update | edit, update |
| 3 | Delete | delete, destroy |

### Session Management

```
SESSION_TIMEOUT = 30 phút (không hoạt động → hết session)
REFRESH_INTERVAL = 15 phút (refresh token nếu active)
```

---

## Actors & Roles

| Actor | Tài khoản | Quyền hạn |
|-------|-----------|-----------|
| **Admin HO** | `users` (role=admin) | Toàn quyền hệ thống |
| **Nhân sự HO (HR)** | `users` (role=hr) | Phê duyệt danh sách, quản lý đăng ký |
| **Đại diện đơn vị** | `unit_accounts` | Đăng ký danh sách đơn vị, upload ảnh |
| **Trưởng đoàn** | `attendees` (is_team_lead=1) | Báo cắt ăn cho đoàn |
| **BTC Thi nghiệp vụ** | `users` (role=competition_organizer) | Quản lý thi NV, cấp số báo danh |
| **BTC Thể thao** | `users` (role=sports_organizer) | Quản lý lịch đấu, kết quả |
| **BTC Tiệc** | `users` (role=banquet_organizer) | Quản lý sơ đồ bàn, phân chỗ |
| **Người tham dự** | Không cần tài khoản | Quét QR xem thông tin |

---

## Database Schema (50 tables)

### Core Entities

| Table | Mô tả |
|-------|-------|
| `events` | Sự kiện đại hội |
| `organizations` | Đơn vị tham dự |
| `unit_accounts` | Tài khoản đăng nhập của đơn vị (1-1 với org) |
| `users` | Người dùng nội bộ (Admin HO, BTC) |
| `staff` | Nhân viên (sync từ SMILE hoặc CRUD) |

### Registration Flow

| Table | Mô tả |
|-------|-------|
| `registration_periods` | Khung thời gian đăng ký |
| `registrations` | Phiếu đăng ký của đơn vị (status: draft/submitted/approved/rejected) |
| `registration_details` | Chi tiết đăng ký theo nội dung |
| `attendees` | Người tham dự (qr_token unique cho QR) |
| `badges` | Thông tin thẻ tham dự (85.60×53.98mm, 300dpi) |

### Roles & Permissions

| Table | Mô tả |
|-------|-------|
| `roles` | Danh mục vai trò (support, sports, director, team_lead, btc...) |
| `attendee_roles` | Gán vai trò cho người tham dự (many-to-many) |

### Sports Module

| Table | Mô tả |
|-------|-------|
| `sports` | Môn thể thao (hỗ trợ cha-con: Bóng đá → Bóng đá nam/nữ) |
| `sport_teams` | Đội thi đấu |
| `sport_team_members` | Thành viên đội (jersey_number, position, is_captain) |
| `sport_stages` | Giai đoạn (qualification/playoff/final) |
| `sport_stage_teams` | Đội tham gia từng giai đoạn |
| `sport_matches` | Trận đấu (group/knockout/playoff/final) |
| `sport_match_results` | Kết quả trận đấu |

### Competition Module (Thi nghiệp vụ)

| Table | Mô tả |
|-------|-------|
| `competitions` | Cuộc thi nghiệp vụ (candidate_number_prefix: NV → NV001) |
| `competition_rounds` | Các vòng thi |
| `competition_registrations` | Đăng ký thi + số báo danh |
| `competition_round_results` | Kết quả từng vòng (qualification/direct) |

### Beauty Contest Module (Thi sắc đẹp)

| Table | Mô tả |
|-------|-------|
| `beauty_contests` | Cuộc thi Miss (gender=female, age_min/max) |
| `beauty_contestants` | Thí sinh (height, weight, measurements, talent) |
| `beauty_rounds` | Vòng thi (ao_dai/bikini/talent/qa/final) |
| `beauty_scores` | Điểm chấm từng vòng theo giám khảo |

### Talent Show Module (Văn nghệ)

| Table | Mô tả |
|-------|-------|
| `talent_shows` | Cuộc thi văn nghệ |
| `talent_categories` | Thể loại (solo_singing, group_singing, solo_dance, group_dance, instrument, comedy) |
| `talent_entries` | Tiết mục đăng ký (title, duration, music_path) |
| `talent_entry_members` | Thành viên tiết mục (cho tốp ca/nhóm múa) |
| `talent_scores` | Điểm chấm văn nghệ |

### Meal & Banquet Module

| Table | Mô tả |
|-------|-------|
| `meals` | Các bữa ăn (breakfast/lunch/dinner, cutoff_deadline) |
| `meal_tables` | Bàn ăn |
| `meal_attendees` | Phân bổ người vào bàn |
| `meal_cutoffs` | Báo cắt ăn (trưởng đoàn báo) |
| `meal_checkins` | Check-in bữa ăn |
| `banquet_events` | Sự kiện tiệc (canvas_width/height cho sơ đồ) |
| `banquet_tables` | Bàn tiệc (pos_x, pos_y, shape: circle/rectangle) |
| `banquet_seats` | Phân chỗ ngồi |

### Event Contents

| Table | Mô tả |
|-------|-------|
| `contents` | Nội dung hoạt động (sports, competition, miss, talent, ceremony) |
| `event_contents` | Sự kiện có những nội dung nào |
| `event_sports` | Sự kiện thi đấu môn nào |
| `event_competitions` | Sự kiện thi nghiệp vụ nào |
| `event_units` | Đơn vị tham gia sự kiện |

### Supporting Tables

| Table | Mô tả |
|-------|-------|
| `transports` | Phương tiện di chuyển (plane/train/bus/self) |
| `event_agenda` | Chương trình đại hội (plenary/break/workshop/ceremony) |
| `audit_logs` | Lịch sử thay đổi (actor_type, action, old_data, new_data) |

---

## Key Relationships

```
events (1) ─── (N) registrations ─── (N) attendees ─── (1) badges
                                          │
                    ┌─────────────────────┼─────────────────────┐
                    │                     │                     │
                    ▼                     ▼                     ▼
          competition_registrations  sport_team_members   meal_cutoffs
                    │                     │                     │
                    ▼                     ▼                     ▼
              competitions           sport_teams             meals

organizations (1) ─── (1) unit_accounts
organizations (1) ─── (N) staff
organizations (1) ─── (N) registrations
organizations (1) ─── (N) sport_teams

attendees (N) ─── (M) roles              [attendee_roles]
attendees (N) ─── (M) competitions       [competition_registrations]
attendees (N) ─── (M) sport_teams        [sport_team_members]
attendees (N) ─── (M) banquet_tables     [banquet_seats]
attendees (N) ─── (M) meals              [meal_cutoffs, meal_attendees]
```

---

## Use Cases

### Đại diện đơn vị
- UC01: Đăng nhập tài khoản đơn vị
- UC02: Tạo bản đăng ký tham dự
- UC03: Nhập danh sách người tham dự (tên, chức danh, ảnh)
- UC04: Chỉnh sửa danh sách (khi status = draft)
- UC05: Nộp đăng ký
- UC06: Xem trạng thái phê duyệt

### Admin HO / HR
- UC06: Xem tất cả đăng ký
- UC07: Phê duyệt / Từ chối đăng ký (kèm lý do)
- UC08: Chỉnh sửa thông tin người tham dự sau phê duyệt
- UC09: Gán vai trò cho người tham dự
- UC10: Tạo/xuất thẻ tham dự theo lô
- UC11: Gán trưởng đoàn cho từng đơn vị
- UC12: Dashboard tổng hợp

### Trưởng đoàn
- UC13: Xem danh sách thành viên đoàn mình
- UC14: Báo cắt ăn từng người
- UC15: Báo cắt ăn cả đoàn (bulk)

### BTC Thi nghiệp vụ
- UC16: Tạo cuộc thi và các vòng thi
- UC17: Cấp số báo danh (tự động hoặc thủ công)
- UC18: Xuất danh sách thí sinh + số báo danh
- UC19: Quản lý lịch thi từng vòng

### BTC Thể thao
- UC20: Tạo các môn thi đấu
- UC21: Tạo lịch thi đấu (giải đấu, vòng bảng, knockout)
- UC22: Cập nhật kết quả trận đấu
- UC23: Xếp hạng và bảng điểm

### BTC Tiệc
- UC24: Tạo sự kiện tiệc
- UC25: Thiết lập sơ đồ bàn (số bàn, vị trí, capacity)
- UC26: Phân bổ người vào bàn/ghế
- UC27: Xem sơ đồ tổng quan

### Người tham dự (Public)
- UC28: Quét QR → Xem thông tin cá nhân
- UC29: Quét QR → Xem agenda đại hội
- UC30: Quét QR → Xem lịch thi nghiệp vụ của mình
- UC31: Quét QR → Xem lịch thi đấu thể thao đơn vị mình

---

## QR Code Flow

```
1. Attendee được phê duyệt → Generate qr_token (unique, không phải ID)
2. Generate badge image với QR code (85.60×53.98mm, 300dpi)
3. User quét QR → /frontend/attendee/view?token=xxx
4. Hiển thị thông tin: cá nhân, lịch thi, agenda
```

---

## MVC Pattern — Data Layer in Models

**QUAN TRỌNG**: Models chịu trách nhiệm tương tác với **tất cả nguồn dữ liệu** (Database + External API + DataProvider). Controllers chỉ gọi methods từ Models, **KHÔNG được gọi trực tiếp** `ApiClient` hay `new ApiDataProvider`.

### Model — Các methods chuẩn

```php
// protected/models/Events.php
class Events extends BaseEvents
{
    // 1. Lấy chi tiết từ API
    public static function fetchFromApi($id)
    {
        $result = ApiClient::get('/api/events/detail/' . $id);
        if ($result['success'] && isset($result['data'])) {
            $data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
            $model = new self;
            $model->setAttributes($data, false);
            $model->id = $id;
            return $model;
        }
        return null;
    }

    // 2. Tạo mới qua API
    public function storeViaApi()
    {
        $data = array_filter($this->attributes, function ($value) {
            return $value !== null && $value !== '';
        });
        return ApiClient::post('/api/events/store', $data);
    }

    // 3. Cập nhật qua API
    public function updateViaApi()
    {
        return ApiClient::post('/api/events/update/' . $this->id, $this->attributes);
    }

    // 4. Xóa qua API
    public static function deleteViaApi($id)
    {
        return ApiClient::delete('/api/events/destroy/' . $id);
    }

    // 5. DataProvider cho danh sách
    public static function getApiDataProvider($params = array(), $pageSize = 25)
    {
        return new ApiDataProvider('/api/events', array(
            'modelClass' => 'Events',
            'params' => $params,
            'pagination' => array('pageSize' => $pageSize),
        ));
    }
}
```

### Controller — Chỉ gọi Model methods

```php
// protected/modules/admin/controllers/EventsController.php
class EventsController extends AdminController
{
    public function actionView($id)
    {
        $model = $this->loadModelById($id);  // ✅ Gọi helper method
        $this->render('view', array('model' => $model));
    }

    public function actionCreate()
    {
        $model = new Events;
        if (isset($_POST['Events'])) {
            $model->setAttributes($_POST['Events']);
            if ($model->validate()) {
                $result = $model->storeViaApi();  // ✅ Gọi Model method
                // handle result...
            }
        }
        $this->render('create', array('model' => $model));
    }

    public function actionAdmin()
    {
        $params = array();
        // build params from $_GET...
        $dataProvider = Events::getApiDataProvider($params);  // ✅ Gọi Model method
        $this->render('admin', array('dataProvider' => $dataProvider));
    }

    protected function loadModelById($id)
    {
        $model = Events::fetchFromApi($id);  // ✅ Gọi Model method
        if ($model === null) {
            throw new CHttpException(404, 'Không tìm thấy.');
        }
        return $model;
    }
}
```

### ❌ KHÔNG được làm trong Controller

```php
// ❌ SAI — Gọi ApiClient trực tiếp
$result = ApiClient::get('/api/events/detail/' . $id);

// ❌ SAI — Tạo ApiDataProvider trực tiếp
$dataProvider = new ApiDataProvider('/api/events', array(...));

// ✅ ĐÚNG — Luôn gọi qua Model
$model = Events::fetchFromApi($id);
$dataProvider = Events::getApiDataProvider($params);
```

### ❌ KHÔNG được làm trong View

**QUAN TRỌNG**: View chỉ hiển thị dữ liệu, **KHÔNG được gọi Model** để lấy dữ liệu. Tất cả dữ liệu cần thiết phải được truyền từ Controller qua `render()`.

```php
// ❌ SAI — Gọi Model trong View để lấy dữ liệu
// protected/modules/admin/views/sports/_form.php
$sports = Sports::getApiDataProvider(array('is_active' => 1), 100)->getData();

// ❌ SAI — Query trực tiếp trong View
$categories = Categories::model()->findAll();

// ✅ ĐÚNG — Controller truyền dữ liệu qua render()
// Controller:
$parentSports = Sports::getActiveList();
$this->render('_form', array(
    'model' => $model,
    'parentSports' => $parentSports,
));

// View:
echo $form->dropDownListGroup($model, 'parent_id', array(
    'widgetOptions' => array(
        'data' => $parentSports,
    )
));
```

---

## API Endpoints — Centralized Constants

**QUAN TRỌNG**: Tất cả API endpoints phải được khai báo trong `protected/components/ApiEndpoints.php`. Models **KHÔNG được viết trực tiếp** endpoint string vào code.

### File: `protected/components/ApiEndpoints.php`

```php
class ApiEndpoints
{
    // Event
    const EVENT_LIST = '/api/events';
    const EVENT_STORE = '/api/events/store';
    const EVENT_DETAIL = '/api/events/detail/{id}';
    const EVENT_UPDATE = '/api/events/update/{id}';
    const EVENT_DESTROY = '/api/events/destroy/{id}';

    // Staff
    const STAFF_LIST = '/api/staffs';
    const STAFF_STORE = '/api/staffs/store';
    // ... các endpoint khác theo danh mục

    // Helper method để replace {id}
    public static function url($endpoint, $params = array())
    {
        $url = $endpoint;
        foreach ($params as $key => $value) {
            $url = str_replace('{' . $key . '}', $value, $url);
        }
        return $url;
    }
}
```

### Model — Sử dụng ApiEndpoints constants

```php
class Events extends BaseEvents
{
    public static function fetchFromApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::EVENT_DETAIL, array('id' => $id));
        $result = ApiClient::get($url);
        // ...
    }

    public function storeViaApi()
    {
        return ApiClient::post(ApiEndpoints::EVENT_STORE, $this->attributes);
    }

    public function updateViaApi()
    {
        $url = ApiEndpoints::url(ApiEndpoints::EVENT_UPDATE, array('id' => $this->id));
        return ApiClient::post($url, $this->attributes);
    }

    public static function deleteViaApi($id)
    {
        $url = ApiEndpoints::url(ApiEndpoints::EVENT_DESTROY, array('id' => $id));
        return ApiClient::delete($url);
    }

    public static function getApiDataProvider($params = array(), $pageSize = 25)
    {
        return new ApiDataProvider(ApiEndpoints::EVENT_LIST, array(
            'modelClass' => 'Events',
            'params' => $params,
            'pagination' => array('pageSize' => $pageSize),
        ));
    }
}
```

### ❌ KHÔNG được làm trong Model

```php
// ❌ SAI — Viết trực tiếp endpoint string
$result = ApiClient::get('/api/events/detail/' . $id);

// ✅ ĐÚNG — Dùng ApiEndpoints constant
$url = ApiEndpoints::url(ApiEndpoints::EVENT_DETAIL, array('id' => $id));
$result = ApiClient::get($url);
```

---

## Permission Check Pattern

```php
// Trong controller
if (!PermissionHelper::can('attendee', 'create')) {
    throw new CHttpException(403, 'Forbidden');
}

// Trong view
<?php if (PermissionHelper::can('attendee', 'update')): ?>
    <a href="...">Edit</a>
<?php endif; ?>
```

---

## User ID từ SSO Token — submitted_by / created_by

**QUAN TRỌNG**: Khi cần lưu thông tin người thực hiện (`submitted_by`, `created_by`, `updated_by`, `approved_by`...), **PHẢI lấy từ SSO token** qua `AuthHandler::getUser()`, **KHÔNG dùng** `Yii::app()->user->id`.

### Lý do

- `Yii::app()->user->id` là user ID của hệ thống local (nếu có)
- User thực tế đăng nhập qua Portal SSO, ID nằm trong JWT token
- Cần đồng bộ với hệ thống Portal để tracking và audit

### Cách sử dụng đúng

```php
// ✅ ĐÚNG — Lấy từ SSO token
$ssoUser = AuthHandler::getUser();
$model->submitted_by = isset($ssoUser['id']) ? $ssoUser['id'] : null;
$model->created_by = isset($ssoUser['id']) ? $ssoUser['id'] : null;

// ❌ SAI — Dùng local user
$model->submitted_by = Yii::app()->user->id;
```

### Các trường thường gặp

| Field | Mô tả | Lấy từ |
|-------|-------|--------|
| `submitted_by` | Người nộp/submit | `AuthHandler::getUser()['id']` |
| `created_by` | Người tạo | `AuthHandler::getUser()['id']` |
| `updated_by` | Người cập nhật | `AuthHandler::getUser()['id']` |
| `approved_by` | Người phê duyệt | `AuthHandler::getUser()['id']` |
| `deleted_by` | Người xóa | `AuthHandler::getUser()['id']` |

---

## Status Constants — Sử dụng hằng số thay vì magic numbers

**QUAN TRỌNG**: Khi so sánh hoặc gán giá trị cho các trường `status`, **PHẢI dùng hằng số (constants)** được khai báo trong Model, **KHÔNG được dùng** magic numbers hoặc strings trực tiếp.

### Cách khai báo trong Model

```php
class Registrations extends BaseRegistrations
{
    const STATUS_DRAFT = 0;
    const STATUS_SUBMITTED = 1;
    const STATUS_APPROVED = 2;
    const STATUS_REJECTED = 3;

    public static function getStatusLabel($status)
    {
        $labels = array(
            self::STATUS_DRAFT => '<span class="badge bg-secondary">Nháp</span>',
            self::STATUS_SUBMITTED => '<span class="badge bg-info">Đã nộp</span>',
            self::STATUS_APPROVED => '<span class="badge bg-success">Đã duyệt</span>',
            self::STATUS_REJECTED => '<span class="badge bg-danger">Từ chối</span>',
        );
        return isset($labels[$status]) ? $labels[$status] : $status;
    }
}
```

### Cách sử dụng đúng

```php
// ✅ ĐÚNG — Dùng constants
$model->status = Registrations::STATUS_DRAFT;
if ($model->status == Registrations::STATUS_SUBMITTED) { ... }

// ❌ SAI — Dùng magic numbers
$model->status = 0;
if ($model->status == 1) { ... }

// ❌ SAI — Dùng strings
$model->status = 'draft';
if ($model->status === 'submitted') { ... }
```

### Trong View

```php
// ✅ ĐÚNG
<?php if ($model->status == Registrations::STATUS_DRAFT): ?>
    <button>Nộp đăng ký</button>
<?php endif; ?>

// ❌ SAI
<?php if ($model->status === 'draft'): ?>
<?php if ($model->status == 0): ?>
```

### Các Model có status constants

| Model | Constants |
|-------|-----------|
| `Registrations` | `STATUS_DRAFT`, `STATUS_SUBMITTED`, `STATUS_APPROVED`, `STATUS_REJECTED` |
| `AllianceRequests` | `STATUS_PENDING`, `STATUS_APPROVED`, `STATUS_REJECTED` |

---

## Toast Notification — Thay thế Alert

**QUAN TRỌNG**: Sau các thao tác CRUD (create, update, delete), **KHÔNG dùng** Bootstrap Alert (`alert alert-success alert-dismissible`). Thay vào đó, sử dụng **Toast JS** với auto close sau 5 giây.

### Quy ước màu sắc Toast

| Type | Màu nền | CSS Class |
|------|---------|-----------|
| `success` | Xanh lá cây | `bg-success text-white` |
| `error` | Đỏ | `bg-danger text-white` |
| `warning` | Vàng | `bg-warning text-dark` |
| `info` | Xanh dương | `bg-primary text-white` |

### File: `themes/hope-ui/assets/js/plugins/toast.js`

```javascript
// Sử dụng trong JS
Toast.success('Tạo thành công!');
Toast.error('Có lỗi xảy ra!');
Toast.warning('Cảnh báo!');
Toast.info('Thông tin');

// Custom duration (ms)
Toast.success('Thành công!', 3000);
```

### Trong View — Hiển thị flash message bằng Toast

```php
<?php
// Đầu file view hoặc trong layout
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/plugins/toast.js',
    CClientScript::POS_END
);

// Hiển thị flash messages
$flashMessages = Yii::app()->user->getFlashes();
if (!empty($flashMessages)):
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php foreach ($flashMessages as $type => $message): ?>
    Toast.<?php echo $type === 'error' ? 'error' : ($type === 'warning' ? 'warning' : 'success'); ?>('<?php echo addslashes($message); ?>');
    <?php endforeach; ?>
});
</script>
<?php endif; ?>
```

### ❌ KHÔNG được làm

```php
// ❌ SAI — Dùng Bootstrap Alert
<div class="alert alert-success alert-dismissible fade show">
    <?php echo Yii::app()->user->getFlash('success'); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

// ✅ ĐÚNG — Dùng Toast JS
Toast.success('<?php echo Yii::app()->user->getFlash("success"); ?>');
```

---

## SweetAlert — Xác nhận Delete

**QUAN TRỌNG**: Khi xóa dữ liệu, **KHÔNG dùng** `confirm()` của browser. Thay vào đó, sử dụng **SweetAlert2** để hiển thị popup xác nhận.

### Cách hoạt động

1. Nút delete sử dụng `type="button"` (không phải submit)
2. Khi click, gọi function `confirmDelete(formId)`
3. SweetAlert hiển thị popup xác nhận
4. Nếu user chọn "Xóa", form được submit bằng POST

### Helper Functions

| Helper | File | Sử dụng |
|--------|------|---------|
| `IconHelper::deleteBtn()` | `protected/components/IconHelper.php` | Nút delete trong DataTable |
| `MyHelper::renderDeleteButton()` | `protected/components/MyHelper.php` | Nút delete trong view (menu) |

### JavaScript Function (đã có trong layouts)

```javascript
function confirmDelete(formId) {
    Swal.fire({
        title: 'Xác nhận xóa',
        text: 'Bạn có chắc chắn muốn xóa? Hành động này không thể hoàn tác.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy'
    }).then(function(result) {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    });
}
```

### ❌ KHÔNG được làm

```php
// ❌ SAI — Dùng confirm() của browser
<a href="delete?id=1" onclick="return confirm('Bạn có chắc?')">Xóa</a>

// ❌ SAI — Dùng GET để xóa
<a href="delete?id=1">Xóa</a>

// ✅ ĐÚNG — Dùng IconHelper hoặc MyHelper
IconHelper::actionButtons($data, array('view', 'update', 'delete'), '/admin/events');
```

---

## Dependent Dropdown — Load dữ liệu phụ thuộc qua AJAX

Khi dropdown B phụ thuộc vào giá trị của dropdown A, sử dụng AJAX để load dữ liệu.

### Ví dụ: Registration Form — Event → Period

```
┌─────────────────┐        AJAX GET                    ┌─────────────────┐
│  Chọn Event     │  ────────────────────────────────► │  API Backend    │
│  (event_id)     │  /api/registration-periods/        │                 │
└────────┬────────┘  list-active?event_id=X            └────────┬────────┘
         │                                                      │
         │           JSON Response                              │
         │  ◄────────────────────────────────────────────────── │
         ▼                                                      
┌─────────────────┐
│  Load Periods   │
│  vào dropdown   │
└─────────────────┘
```

### Luồng xử lý

1. **Khởi tạo**: Dropdown Period hiển thị "-- Chọn sự kiện trước --"
2. **User chọn Event**: Trigger `change` event
3. **AJAX Request**: Gọi API với `event_id` param
4. **Response**: Populate dropdown Period với dữ liệu trả về

### API Endpoint

| Endpoint | Method | Params | Response |
|----------|--------|--------|----------|
| `/api/registration-periods/list-active` | GET | `event_id` | Danh sách periods đang active của event |

### Code mẫu

**View (PHP)** — Truyền config qua data attributes:

```php
<?php echo $form->dropDownList($model, 'event_id', $eventList, array(
    'class' => 'form-select',
    'prompt' => '-- Chọn sự kiện --',
    'id' => 'event-select',
)); ?>

<?php echo $form->dropDownList($model, 'period_id', $periods, array(
    'class' => 'form-select',
    'prompt' => $model->event_id ? '-- Chọn đợt đăng ký --' : '-- Chọn sự kiện trước --',
    'id' => 'period-select',
    'data-api-url' => Yii::app()->params['externalApiUrl'] . '/api/registration-periods/list-active',
    'data-api-key' => Yii::app()->params['externalApiKey'],
)); ?>
```

**JS file** (`themes/hope-ui/assets/js/pages/registrations-form.js`):

```javascript
document.addEventListener('DOMContentLoaded', function() {
    var eventSelect = document.getElementById('event-select');
    var periodSelect = document.getElementById('period-select');

    if (eventSelect && periodSelect) {
        var apiUrl = periodSelect.getAttribute('data-api-url');
        var apiKey = periodSelect.getAttribute('data-api-key');

        eventSelect.addEventListener('change', function() {
            var eventId = this.value;
            periodSelect.innerHTML = '<option value="">-- Đang tải... --</option>';

            if (!eventId) {
                periodSelect.innerHTML = '<option value="">-- Chọn sự kiện trước --</option>';
                return;
            }

            fetch(apiUrl + '?event_id=' + eventId, {
                headers: {
                    'Authorization': 'Bearer ' + apiKey,
                    'Accept': 'application/json'
                }
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                periodSelect.innerHTML = '<option value="">-- Chọn đợt đăng ký --</option>';
                var items = data.data || data;
                if (Array.isArray(items) && items.length > 0) {
                    items.forEach(function(p) {
                        var option = document.createElement('option');
                        option.value = p.id;
                        option.textContent = p.name;
                        periodSelect.appendChild(option);
                    });
                }
            })
            .catch(function() {
                periodSelect.innerHTML = '<option value="">-- Lỗi tải dữ liệu --</option>';
            });
        });
    }
});
```

### Các trường hợp tương tự

| Parent Dropdown | Child Dropdown | API Endpoint |
|-----------------|----------------|--------------|
| Event | Registration Period | `/api/registration-periods/list-active?event_id=X` |
| Property | Relation Properties | `/admin/registrations/getRelationProperties?property_id=X` |

---

## View Layout — Multi-Column Display

**QUAN TRỌNG**: Trong trang view chi tiết, **KHÔNG dùng** CDetailView mặc định (hiển thị 1 cột dài). Thay vào đó, tự động chia thành nhiều cột dựa trên số lượng thuộc tính.

### Quy tắc chia cột

| Số thuộc tính | Số cột | CSS Class |
|---------------|--------|-----------|
| ≤ 4 | 1 cột | `col-12` |
| 5 - 8 | 2 cột | `col-md-6` |
| > 8 | 3 cột | `col-md-4` |

### Cấu trúc code

```php
<?php
$attributes = array(
    array('label' => $model->getAttributeLabel('code'), 'value' => $model->code),
    array('label' => $model->getAttributeLabel('name'), 'value' => $model->name),
    array(
        'label' => $model->getAttributeLabel('status'),
        'value' => '<span class="badge bg-success">Active</span>',
        'raw' => true  // Cho phép HTML
    ),
);

$totalAttrs = count($attributes);
if ($totalAttrs <= 4) {
    $colClass = 'col-12';
    $columns = 1;
} elseif ($totalAttrs <= 8) {
    $colClass = 'col-md-6';
    $columns = 2;
} else {
    $colClass = 'col-md-4';
    $columns = 3;
}
$perColumn = ceil($totalAttrs / $columns);
?>

<div class="row">
    <?php for ($col = 0; $col < $columns; $col++): ?>
    <div class="<?php echo $colClass; ?>">
        <table class="table table-bordered table-striped">
            <tbody>
            <?php
            $start = $col * $perColumn;
            $end = min($start + $perColumn, $totalAttrs);
            for ($i = $start; $i < $end; $i++):
                $attr = $attributes[$i];
            ?>
                <tr>
                    <th style="width:40%;background:#f8f9fa;"><?php echo CHtml::encode($attr['label']); ?></th>
                    <td><?php echo isset($attr['raw']) && $attr['raw'] ? $attr['value'] : CHtml::encode($attr['value']); ?></td>
                </tr>
            <?php endfor; ?>
            </tbody>
        </table>
    </div>
    <?php endfor; ?>
</div>
```

### ❌ KHÔNG được làm

```php
// ❌ SAI — Dùng CDetailView (1 cột dài, tốn không gian)
$this->widget('zii.widgets.CDetailView', array(
    'data' => $model,
    'attributes' => array('code', 'name', 'status', ...),
));
```

---

## Development Workflow

```
/spec  →  /plan  →  /build  →  /test  →  /review  →  Ship
```

| Phase | Command | Purpose |
|-------|---------|---------|
| Define | `/spec` | Create PRD with objectives, scope |
| Plan | `/plan` | Decompose into vertical slices |
| Build | `/build` | Implement using TDD |
| Verify | `/test` | Write and verify tests |
| Review | `/review` | Five-axis code review |

---

## Naming Conventions

### Database
- Tables: **snake_case** plural (`attendees`, `sport_teams`)
- Columns: **snake_case** (`created_at`, `organization_id`)
- Indexes: `idx_{table}_{columns}`
- Foreign keys: `fk_{child}_{parent}`
- Unique keys: `uq_{table}_{columns}`

### PHP/Yii
- Models: **PascalCase** với prefix M (`MAttendees`, `MSportTeams`)
- Controllers: **PascalCase** + Controller (`AttendeeController`)
- Views: **lowercase** (`index.php`, `_form.php`)
- Components: **PascalCase** (`AuthHandler`, `ApiClient`)

---

## Important Files

| File | Purpose |
|------|---------|
| `docs/system-design.md` | Full system design (50 tables, API, flows) |
| `protected/components/AuthHandler.php` | JWT validation + session |
| `protected/components/ApiClient.php` | Call External API |
| `protected/components/PermissionHelper.php` | CRUD permission check |
| `protected/config/params.php` | API keys, Portal URL |

---

## Agent Guidelines

1. **Read docs/system-design.md** trước khi implement feature mới
2. **Follow authentication flow** — luôn check session và permissions
3. **Use ApiClient** — không gọi API trực tiếp
4. **Check permissions** — CRUD trước mỗi action
5. **Soft delete** — dùng `deleted_at` thay vì xóa thật
6. **Audit log** — ghi lại mọi thay đổi quan trọng
7. **QR token** — không dùng ID trong URL public
8. **Unix timestamp** — dùng INT UNSIGNED cho datetime columns

---

## Available Agents

### Development Agents
| Agent | When to Invoke |
|-------|---------------|
| **Frontend Developer** | Components, pages, routing, UI |
| **Backend Developer** | APIs, services, DB queries |
| **Systems Architect** | Architecture decisions |

### Quality Agents
| Agent | When to Invoke |
|-------|---------------|
| **Code Reviewer** | Five-axis PR review |
| **Test Engineer** | Test strategy, TDD |
| **Security Auditor** | Vulnerability assessment |
| **QA Engineer** | Test plans, E2E tests |

### Product Agents
| Agent | When to Invoke |
|-------|---------------|
| **Project Manager** | User stories, sprint planning |
| **UI/UX Designer** | Design system, wireframes |
| **Copywriter/SEO** | Page copy, SEO optimization |

---

## Mandatory Rules

All rules in `.claude/rules/` are **mandatory**:

| Rule | Description |
|------|-------------|
| `clean-code.md` | Variables, functions, SOLID |
| `code-style.md` | Formatting, naming conventions |
| `error-handling.md` | Error patterns |
| `security.md` | **CRITICAL** — Never violate |
| `database.md` | Query patterns, transactions |
| `api-conventions.md` | REST standards |
| `git-workflow.md` | Branching, commits |
