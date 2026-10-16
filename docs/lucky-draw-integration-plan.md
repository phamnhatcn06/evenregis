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

## 7. Files tham chiếu
- Tool gốc: `lucky_manrong_19201080\` (js/lucky-show.js, js/fireworks-modern.js, views/site/show.php, protected/controllers/ApiController.php, lucky_draw.sql)
- eventregis: `protected/components/ApiEndpoints.php`, `PermissionHelper.php`, `AuthHandler.php`, `protected/config/RewriteUrlManager.php`, `protected/modules/admin/controllers/FinalAttendeeRostersController.php` (mẫu), `protected/models/FinalAttendeeRosters.php`
- Backend endpoint mới: repo Laravel even_API (local port 8000)
