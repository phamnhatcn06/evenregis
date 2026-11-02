# Luồng đăng nhập Portal SSO (JWT)

> Tài liệu mô tả chính xác luồng xác thực đang chạy trong hệ thống, dựa trên code thực tế:
> - `protected/controllers/SiteController.php` — điểm nhận token, render trang
> - `protected/components/AuthHandler.php` — giải mã JWT, tạo session, gọi Portal
> - `protected/components/AdminController.php` — guard cho khu vực admin
> - `protected/config/params.php` — cấu hình Portal

---

## 1. Tổng quan

Hệ thống **không có form đăng nhập riêng**. Người dùng đăng nhập ở **Portal Mường Thanh**, Portal cấp **JWT** rồi redirect về hệ thống kèm token trên URL (`?sso_token=...`). Hệ thống giải mã token, tạo session và nạp quyền.

| Thành phần | Giá trị (params.php → `portal`) |
|------------|--------------------------------|
| Portal URL | `https://portal.muongthanh.vn` |
| Portal API | `https://api.portal.muongthanh.vn` |
| Endpoint `/me` | `/api/sso/me` |
| Endpoint quyền | `/api/sso/permissions/me` |
| JWT secret | `JWT_SECRET` (env) — có fallback hardcode |
| Thuật toán JWT | `portal.jwt_algorithm` |

---

## 2. Sơ đồ luồng đăng nhập

```
┌──────────────────────────┐
│  portal.muongthanh.vn    │  User đăng nhập ở Portal
│  → bấm vào app Event      │
└────────────┬─────────────┘
             │ redirect  /?sso_token=<JWT>
             ▼
┌──────────────────────────────────────────────────────────────┐
│  SiteController::actionIndex()                                 │
│                                                                │
│  1. Có sso_token trên URL?                                     │
│     ├─ AuthHandler::logout()            (xoá session cũ)       │
│     ├─ AuthHandler::handleCallback($t)  (decode JWT + session) │
│     │     └─ token sai/hết hạn → flash error → /site/login     │
│     ├─ AuthHandler::fetchPermissions($t)      (GET .../permissions/me) │
│     ├─ AuthHandler::fetchUserProfile($t)      (GET .../sso/me)        │
│     ├─ AuthHandler::updateSessionWithProfile(...)             │
│     └─ redirect 302 → /site/index    (token biến mất khỏi URL) │
│                                                                │
│  2. Không có token, đã có session hợp lệ?                       │
│     ├─ validateWithPortal() OK → render 'home'                 │
│     └─ validateWithPortal() fail → render 'login'              │
│                                                                │
│  3. Chưa đăng nhập → render 'login' (landing công khai)        │
└──────────────────────────────────────────────────────────────┘
```

### Vì sao redirect 302 sau khi nhận token
Token nhận qua `?sso_token=` được xử lý xong thì server **redirect 302 về `/site/index`**, nên token **biến mất khỏi thanh địa chỉ ngay lập tức** (không lưu lại trong history, không bị share nhầm).

---

## 3. Các bước xử lý token (`AuthHandler`)

### 3.1. `handleCallback($token)` — giải mã & tạo session
1. Rỗng token → `false`.
2. Nạp `JWT.php` từ `application.extensions.jwt`, gọi `JWT::decode($token, $secret, $algorithm)`.
3. Decode lỗi → log WARNING `auth`, trả `false`.
4. Thành công → `createSession($payload, $token)`.

### 3.2. `createSession()` — thông tin cơ bản vào session
Token của Portal dùng **claim kiểu SAML** (không phải key phẳng). Mapping:

| Session field | Claim trong JWT |
|---------------|-----------------|
| `id` | `.../claims/sid` |
| `email` | `.../claims/emailaddress` |
| `full_name` | `.../claims/name` |
| `software_id`, `exp`, `property_id`, `property_code`, `regional_id` | key phẳng cùng tên |

Các key session được ghi:
- `sso_user` — thông tin user
- `sso_token` — JWT gốc (dùng để gọi lại Portal)
- `sso_last_activity` — mốc thời gian
- `sso_permissions` = `[]`, `sso_menu_permissions` = `[]` (nạp sau)

### 3.3. `fetchPermissions($token)` — nạp quyền
`GET {api_url}/api/sso/permissions/me` với header `Authorization: Bearer <token>`.
- Lỗi mạng / HTTP ≠ 200 → log WARNING, trả `false` (session vẫn còn, chỉ là chưa có quyền).
- Response dạng `{ permissions: [...] }` (cũng chấp nhận `{ data: [...] }` hoặc mảng gốc).
- Mỗi item có `controller` → sinh 2 cấu trúc:
  - **CRUD** (`sso_permissions`): `strtolower(controller) => "C R U D"`. Nếu `action === '*'` → `"1 1 1 1"`.
  - **Menu** (`sso_menu_permissions`): dùng dựng sidebar (name/module/controller/action/root/sort).
- Quyền `'*'` → `['*' => '1 1 1 1']` (toàn quyền).
- Gọi `inheritRelatedPermissions()` để controller con kế thừa quyền controller cha.
- Xoá cache menu/quyền của token (`CacheHelper::clearMenuCache()`, `clearPermsCache()`).

> **Lưu ý (xem memory `permissions-from-sso-api`)**: quyền lấy từ **API này**, không lấy từ payload JWT. Key quyền là **tên controller thật** (vd `sportdraw`), không phải nhãn module.

### 3.4. `fetchUserProfile($token)` + `updateSessionWithProfile()` — bổ sung profile
`GET {api_url}/api/sso/me`. Dữ liệu `Employee` được map thêm vào `sso_user`:
Hotel (property_code, hotel_symbol, hotel_name), Area (regional_id/name), Department, Position, employee_id, avatar, mobile.

---

## 4. Kế thừa quyền giữa controller (`inheritRelatedPermissions`)

Nếu có `['*']` → bỏ qua. Ngược lại controller con **thừa hưởng quyền của cha** khi chưa có quyền riêng:

| Controller con | Kế thừa từ |
|----------------|-----------|
| `registrationperiods`, `registrations`, `eventsports`, `eventcontents`, `beautycontests`, `beautycontestants`, `talententries`, `talentrounds`, `sportteams`, `competitionregistrations` | `events` |
| `registrationdetails` | `registrations` |

---

## 5. Guard & kiểm tra phiên mỗi lần load trang

### 5.1. `AdminController::init()` — mọi trang admin
```
1. AuthHandler::isAuthenticated()  → false → flash + redirect /site/login
2. AuthHandler::validateWithPortal() → false → flash + redirect /site/login
3. $this->user = AuthHandler::getUser()
```

### 5.2. `isAuthenticated()`
- Ngữ cảnh console (không phải `CWebApplication`) → luôn `false`.
- Có `sso_user` trong session → `true`, cập nhật `sso_last_activity`.
- **Không** tự kiểm tra `exp`/timeout cục bộ — việc token còn hiệu lực hay không **giao cho Portal** qua `validateWithPortal()`.

### 5.3. `validateWithPortal()` — hỏi Portal token còn sống không
Gọi `GET /api/sso/me` với token trong session.

| Kết quả | Hành vi |
|---------|---------|
| Không có token | `false` |
| Đã validate trong `PORTAL_CHECK_TTL` (60s) gần đây | `true` (bỏ qua, không gọi lại — cache theo session) |
| HTTP **401 / 403** | Portal từ chối → `logout()` → `false` |
| Lỗi mạng / HTTP khác | **fail-open**: giữ phiên, trả `true`, không cập nhật mốc để lần sau thử lại |
| HTTP **200** | ghi mốc `sso_portal_checked_at = time()` → `true` |

Kết quả còn được cache trong **phạm vi 1 request** (`static $result`) để không gọi API nhiều lần trong cùng một lần load trang.

---

## 6. Phân quyền CRUD (`AdminController::beforeAction`)

1. Public actions (`index, view, list, search, admin, addContent, removeContent`) → bỏ qua kiểm tra.
2. `mapActionToOperation()` quy đổi action → thao tác CRUD:
   - create: `create/add/store`
   - read: `index/view/list/admin/search`
   - update: `update/edit/save`
   - delete: `delete/destroy/remove`
3. `PermissionHelper::can($controllerId, $operation)` fail → `CHttpException(403)`.

Format quyền `"C R U D"` (vị trí 0=Create, 1=Read, 2=Update, 3=Delete; `1`=có, `0`=không).

---

## 7. Đăng xuất

`SiteController::actionLogout()`:
```
AuthHandler::logout()        // xoá sso_user, sso_permissions, sso_token,
Yii::app()->user->logout()   //      sso_last_activity, sso_portal_checked_at
→ flash info → redirect /site/login
```

---

## 8. Các routes liên quan

| Route | Mục đích |
|-------|----------|
| `/site/index` (nhận `?sso_token=`) | Điểm vào SSO — xử lý token, tạo session |
| `/site/login` | Trang landing; nếu session còn hợp lệ → vào `/admin/default/index`; nếu không → redirect Portal |
| `/site/logout` | Đăng xuất |
| `/site/me`, `/site/menuPermissions` | Trả thông tin user / menu cho client (localStorage) |

> `actionLogin()` **ưu tiên dùng lại session hiện có**: nếu `isAuthenticated() && validateWithPortal()` thì vào thẳng admin, **không** redirect ra Portal lần nữa. Chỉ khi chưa có session hợp lệ mới gọi `redirectToPortal()` (tới `{portal.url}/login?redirect=<returnUrl>`).

---

## 9. Lưu ý bảo mật / vận hành

- `JWT_SECRET` nên đặt qua **biến môi trường**; fallback hardcode trong `params.php` chỉ dùng tạm cho dev, **không commit secret thật** (xem `.claude/rules/security.md`, `gitignore-check.md`).
- Các lời gọi Portal đang tắt verify TLS (`CURLOPT_SSL_VERIFYPEER/HOST => false`) — chấp nhận trong môi trường nội bộ; cân nhắc bật lại ở production.
- Khi cần lưu người thực hiện (`created_by`, `submitted_by`...) → lấy `AuthHandler::getUser()['email']`, **không** dùng `Yii::app()->user->id`.
- Console command không có session → `isAuthenticated()` trả `false`; các job cần auth phải xử lý riêng (xem memory `sport-stage-rules-config` về guard AuthHandler cho console).
