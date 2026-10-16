# PLAN: Tích hợp công cụ Quay số may mắn (Lucky Draw) vào Event Registration

> Yii 1.x · dữ liệu qua External API (Laravel even_API) · ngày lập: 2026-10-16

## 1. Mục tiêu
Nhúng tool Lucky Draw standalone (`e:\eventregis\lucky_manrong_19201080\`) vào hệ thống eventregis, **giữ nguyên 100% hiệu ứng** (animation quay, pháo hoa, âm thanh, skin), chuẩn hóa theo kiến trúc eventregis:
- Dữ liệu người tham gia lấy từ `final_attendee_rosters` (KHÔNG dùng bảng `participants`).
- Có màn hình Settings cho admin.
- Gate 2 lớp: quyền `luckydraw` + access code.
- Giao diện full-screen, không header/footer.

## 2. Quyết định đã chốt với user
- **Tích hợp hẳn vào eventregis**, bỏ app standalone.
- **Lưu trữ:** bảng mới phía Laravel (`lucky_prizes`, `lucky_winners`, `lucky_settings`), **dùng chung** (không gắn `event_id`).
- **Access code:** admin đổi được trong Settings (lưu `lucky_settings.access_code`).
- **Remote control:** CÓ (Slice 7 trong phạm vi chính).
- **Lọc ứng viên:**
  - Settings cho admin **tự chọn các cột loại trừ** từ `final_attendee_rosters` (vd `type`, `is_btc`, đơn vị...).
  - **Mỗi giải cấu hình "áp dụng cho các `type` nào"** → `lucky_prizes.eligible_types`.
  - `lucky_settings` lưu danh sách cột/điều kiện loại trừ toàn cục.
- **Giữ hiệu ứng:** copy nguyên `lucky-show.js`, `fireworks-modern.js`, CSS/audio/images; chỉ bọc 1 lớp adapter JS đổi endpoint. API mới PHẢI trả JSON đúng shape `lucky-show.js` mong đợi.

## 3. Phạm vi
**Trong:** controller admin (Settings + điều khiển), action full-screen quay số, lớp API eventregis→Model→ApiClient→Laravel (list ứng viên từ roster, prizes, spin, confirm/cancel winner, status, remote), copy asset hiệu ứng, layout full-screen, endpoint Laravel mới, gate quyền + access code.
**Ngoài:** viết lại core animation, đồng bộ winner ngược portal, reporting/analytics.

## 4. Vertical Slices
| # | Slice | Size | Phụ thuộc |
|---|-------|------|-----------|
| 0 | Chốt JSON contract từ `lucky-show.js` + mapping roster→ứng viên + danh mục `type` | ~0.5d | — (chặn mọi slice) |
| 1 | Laravel: schema `lucky_*` (prizes có `eligible_types`; settings có cột loại trừ + access_code + remote) + endpoint list ứng viên filter động | ~2d | 0 |
| 2 | Khung eventregis: quyền `luckydraw`, `admin/LuckyDrawController`, layout `fullscreen.php`, URL rules, `ApiEndpoints::LUCKY_*` | ~1d | 1 |
| 3 | Settings admin: CRUD giải (eligible_types), cấu hình cột loại trừ, access code, tiêu đề, background | ~2d | 1,2 |
| 4 | Access code gate (sau permission, verify API, lưu session) | ~0.5d | 2,3 |
| 5 | Màn quay số full-screen + port nguyên hiệu ứng (TRỌNG TÂM) | ~2.5d | 1,4 |
| 6 | Spin logic (random trong tập lọc theo eligible_types + cột loại trừ) + confirm/cancel/next/reset | ~1.5d | 5 |
| 7 | Remote control (polling `remote_command`) | ~1d | 6 |
| 8 | QA & nghiệm thu | ~1d | — |

**Tổng ~12 ngày công.** Đường găng: 0 → 1 → 5 → 6 → 7.

## 5. Rủi ro
| Rủi ro | Mức | Giảm thiểu |
|---|---|---|
| JSON contract không khớp `lucky-show.js` → vỡ animation | Cao | Slice 0 chốt trước; API trả đúng shape, không sửa core JS |
| Quyền `luckydraw` phải cấu hình phía Portal SSO (key = tên controller thật) | Cao | Xác nhận portal team sớm; fallback tạm bằng access code |
| `final_attendee_rosters` thiếu field (department/company/level) tool cần | TB | Map sang `unit_label`/`badge_org_name`, bỏ field thừa trong UI |
| Roster lớn → `ORDER BY RAND()` chậm | TB | Lọc trước; lấy id-pool rồi random phía app |
| Winner trỏ roster bị sửa/xóa sau khi trúng | TB | Lưu snapshot tên/đơn vị vào `lucky_winners` |
| Asset đụng CSS global theme | Thấp | Đặt trong thư mục con `luckydraw/`, scope CSS |
| Audio autoplay bị chặn | Thấp | Kích hoạt audio sau tương tác (nút bắt đầu) |

## 6. Mapping dữ liệu (chốt ở Slice 0)
| tool `participants` | `final_attendee_rosters` |
|---|---|
| code | lucky_number (hoặc id) |
| full_name | họ tên |
| department / company | unit_label / badge_org_name |
| level / is_partner | (map theo cột admin chọn loại trừ) |
| — | type → dùng cho `eligible_types` mỗi giải |

## 7. SLICE 0 — JSON CONTRACT (đã chốt từ lucky-show.js + show.php)

Các endpoint `window.__API` core JS gọi và shape bắt buộc phải trả về:

### `GET prize` (API.prize) — thông tin giải hiện tại
```json
{ "ok": true, "data": {
  "id": 1, "prize_name": "Giải Đặc Biệt", "code": "special",
  "quantity": 1, "awarded": 0, "duration": 12000, "stagger_duration": 0,
  "spin_count": 1, "finished": false
}}
```
- `code` dùng để nhận diện hiệu ứng: `'special'` → màn che + mega confetti; tên chứa "nhất"/"đặc biệt" → hãm chậm.
- `finished` = (awarded >= quantity). JS khóa quay khi quantity==awarded.

### `GET status` (API.status) — số người còn lại
```json
{ "ok": true, "data": { "remaining": 123 } }
```

### `POST spin` (API.spin) — quay, body `{}`
```json
{ "ok": true, "data": {
  "prize": { "id":1, "name":"Giải Nhất", "code":"first",
             "duration":15000, "stagger_duration":5000, "spin_count":5 },
  "winners": [ { "id":42, "code":"042", "full_name":"Nguyễn Văn A",
                 "department":"Phòng ABC", "company":"Đơn vị X" } ]
}}
```
- Lỗi: `{ "ok": false, "error": "..." }` (JS đọc `res.error`).
- `winners[].code` PHẢI là chuỗi số ≥3 ký tự (JS lấy 3 digit cuối cho reel). → **lucky_number map vào `code`**.
- `spin_count>1` hoặc nhiều winner → chế độ consolation (nhiều người/lần).

### `POST confirmWinner` (API.confirm) — body `{ id:'all'|<pid>, prize_id }`
`{ "ok": true }`

### `POST cancelWinner` (API.cancel) — body `{ id:'all'|<uid>, prize_id }`
`{ "ok": true }` / `{ "ok": false, "error"/"msg": "..." }`

### `POST nextPrize` (API.nextPrize)
`{ "ok": true }` / `{ "ok": false, "msg": "🎉 Đã hết toàn bộ giải" }`

### `POST checkAuth` (API.checkAuth) — body `{ code }`
`{ "ok": true }` / `{ "ok": false, "msg": "Mã truy cập không đúng!" }`
- JS lưu cookie `lucky_auth_token=granted` trong 3 giờ sau khi đúng mã.

### `GET checkRemote` (API.checkRemote) — poll mỗi 1s
`{ "ok": true, "command": "spin"|null }` (đọc xong reset về null)
### `POST remoteSpin` (API.remoteSpin)
`{ "ok": true }` / `{ "ok": false, "msg": "Chưa xác nhận kết quả trước!" }`

### DOM bắt buộc giữ nguyên (show.php)
Canvas: `fxCanvas, petalCanvas, boomCanvas`; `lightSweep`, `.screen`, `#board`, `#reelsWrap`, 3×`.reel/.reel-strip/.reel-cover`, `#prizeName`, `#specialMsg`; winner: `#winnerCenter/#winnerListCenter`, `#winnersLeft/#winnerListLeft`, `#winnersRight/#winnerListRight`, `#winnerOverlay`, `#winnerPopup` (`#bigCode #fullName #department #company #multiWinnerContainer #singleWinnerWrapper`); consolation: `#consolationBoard/#consolationGrid/#consolationActions`; `#remaining`, `#prizeCounter`, `#btnNextPrize`.
Phím: SPACE=quay, ENTER=xác nhận khi popup mở.

### Mapping roster → code JS (chốt)
| field JS | nguồn final_attendee_rosters |
|---|---|
| `code` | `lucky_number` (chuỗi số, pad ≥3) |
| `id` | roster `id` (dùng cho confirm/cancel) |
| `full_name` | họ tên |
| `department` | `unit_label` / `badge_org_name` |
| `company` | đơn vị cấp trên / để trống nếu không có |

→ **Adapter JS chỉ cần đổi `window.__API` sang URL eventregis; backend trả đúng shape trên → KHÔNG sửa lucky-show.js.**

## 8. TIẾN ĐỘ TRIỂN KHAI (cập nhật 2026-10-16)

| Slice | Trạng thái | Ghi chú |
|---|---|---|
| 0 Contract | ✅ Xong | JSON contract + mapping ở mục 6–7 |
| 1 Backend schema+endpoint | ✅ Xong | Module `Lucky` (Laravel), 16 route, migrate OK |
| 6 Spin logic | ✅ Xong (verify tinker) | spin/confirm/cancel/next/reset; lọc eligible_types + exclude_columns; snapshot winner |
| 2 Khung Yii | ✅ Xong | `LuckyDrawController`, `ApiEndpoints::LUCKY_*`, Model `LuckyDraw`, quyền `luckydraw` |
| 3 Settings admin | ✅ Xong | `views/luckydraw/index.php` + `_modal_prize.php` + `luckydraw-settings.js` |
| 4 Access code gate | ✅ Xong | permission `luckydraw` + checkAuth (cookie) sẵn trong lucky-show.js |
| 5 Màn quay full-screen | ✅ Xong | `show.php` + assets copy sang `themes/hope-ui/assets/luckydraw/`; `__ASSET_BASE` cho audio |
| 7 Remote | ✅ Xong | `remote.php` + `luckydraw-remote.js` |
| 8 QA nghiệm thu | ⏳ Cần chạy thật | Xem phụ thuộc dưới |

### Backend đã verify (tinker, php8.1.0)
types=["finalist","manual"]; remaining=805; spin 2 winner (spin_count=2, lọc finalist) + confirm → awarded 2/5. JSON khớp contract.

### Phụ thuộc còn lại trước khi nghiệm thu (Slice 8)
1. **Server backend :8000 bị "could not find driver"** — đúng vấn đề MAMP pdo_mysql đã ghi ở memory `backend-local-startup.md`. Cần khởi động lại backend bằng php có pdo_mysql (artisan CLI đã OK). KHÔNG phải bug code.
2. **Quyền `luckydraw` phải được Portal SSO cấp** cho tài khoản (admin có `*` là vào ngay; HR cần portal thêm key `luckydraw`).
3. **lucky_number**: ứng viên phải có `lucky_number` (đã có 805 người). Người chưa cấp số sẽ không vào danh sách quay.
4. Test giao diện quay thật: mở `/admin/luckyDraw/show` (admin đăng nhập) → nhập access code → SPACE quay → hiệu ứng.

### Các file đã tạo
**Laravel** `Modules/Lucky/`: module.json, composer.json, Config, Providers (Lucky/RouteServiceProvider), Routes (api/web), Entities (LuckyPrize/LuckyWinner/LuckySetting), Services (ILuckyService/LuckyService), Http/Controllers/LuckyController, Database/Migrations (3). Bật trong `modules_statuses.json`.
**eventregis**: `components/ApiEndpoints.php` (+LUCKY_*), `models/LuckyDraw.php`, `modules/admin/controllers/LuckyDrawController.php`, `modules/admin/views/luckydraw/{index,_modal_prize,show,remote}.php`, `themes/hope-ui/assets/luckydraw/*` (js/css/audio/images), `themes/hope-ui/assets/js/pages/{luckydraw-settings,luckydraw-remote}.js`.

## 9. Files tham chiếu
- Tool gốc: `lucky_manrong_19201080\` (js/lucky-show.js, js/fireworks-modern.js, views/site/show.php, protected/controllers/ApiController.php, lucky_draw.sql)
- eventregis: `protected/components/ApiEndpoints.php`, `PermissionHelper.php`, `AuthHandler.php`, `protected/config/RewriteUrlManager.php`, `protected/modules/admin/controllers/FinalAttendeeRostersController.php` (mẫu), `protected/models/FinalAttendeeRosters.php`
- Backend endpoint mới: repo Laravel even_API (local port 8000)
