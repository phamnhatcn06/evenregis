# Spec API — Slideshow & Thư viện (Media)

> Dành cho: đội backend `mt_registration_portal` (Laravel).
> Mục đích: cung cấp dữ liệu cho hero slider và khu vực Thư viện của trang `frontend/daihoi`.
> Frontend (Yii1) đã sẵn sàng tiêu thụ: models `Slideshow`, `MediaAlbum`, `MediaItem`,
> hằng số trong `ApiEndpoints.php`, và các method `Daihoi::getSlides()/getAlbums()/getAlbumItems()`.
> Migration DB: `docs/migrations/2026_10_06_slideshows_media.sql`.

Quy ước response theo `.claude/rules/api-conventions.md`:

```json
{ "success": true, "data": [ ... ] }           // list
{ "success": true, "data": { ... } }           // detail
```

---

## 1. Public endpoints (trang chủ gọi, không cần đăng nhập)

### GET `/api/daihoi/slides`
Trả về các slide **đang hiển thị** của sự kiện hiện hành, đã lọc sẵn ở backend:
`is_active = 1` AND (`start_at` IS NULL OR `start_at <= now()`) AND (`end_at` IS NULL OR `end_at >= now()`),
sắp xếp theo `sort_order ASC, id ASC`.

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "ĐẠI HỘI MƯỜNG THANH 2026",
      "subtitle": "Ninh Bình • Tháng 10/2026",
      "description": "Hội tụ tinh hoa tay nghề nghiệp vụ...",
      "image": "https://.../hero-1.jpg",
      "mobile_image": null,
      "button_text": "Khám phá chương trình",
      "button_url": "#noi-dung",
      "theme": "blue"
    }
  ]
}
```
`theme` ∈ `blue | amber | purple | teal` (quyết định màu gradient tiêu đề + màu chấm indicator). Khác giá trị → frontend coi như `blue`.

### GET `/api/daihoi/albums?per_page=8&page=1`
Album **đang hiển thị** (`is_active = 1`), sắp theo `sort_order ASC`. `item_count` là số item active (denormalized).

```json
{
  "success": true,
  "data": [
    {
      "id": 5,
      "title": "Thể thao sôi động",
      "slug": "the-thao-soi-dong",
      "badge": "BỘ MÔN",
      "cover_image": "https://.../album-5.jpg",
      "type": "photo",
      "item_count": 120
    }
  ]
}
```

### GET `/api/daihoi/albums/{id}/items`
Ảnh/video active trong một album, sắp theo `sort_order ASC`.

```json
{
  "success": true,
  "data": [
    { "id": 10, "type": "image", "url": "https://.../01.jpg", "thumbnail": "https://.../01-thumb.jpg", "title": null, "caption": null }
  ]
}
```

---

## 2. Admin endpoints (quản trị nội dung — REST, cùng style `/api/admin/news`)

### Slideshow
| Method | URL | Mô tả |
|---|---|---|
| GET | `/api/admin/slideshows` | Danh sách (hỗ trợ `page`, `per_page`, lọc `event_id`, `is_active`) |
| POST | `/api/admin/slideshows` | Tạo mới |
| GET | `/api/admin/slideshows/{id}` | Chi tiết |
| PUT | `/api/admin/slideshows/{id}` | Cập nhật |
| DELETE | `/api/admin/slideshows/{id}` | Xoá mềm (`deleted_at`) |
| POST | `/api/admin/slideshows/reorder` | Body `{ "ids": [3,1,2] }` → cập nhật `sort_order` |

Body tạo/sửa: `event_id`(bắt buộc), `title, subtitle, description, image, mobile_image, button_text, button_url, theme, sort_order, is_active, start_at, end_at`.

### Media Album
| Method | URL | Mô tả |
|---|---|---|
| GET | `/api/admin/media-albums` | Danh sách (lọc `event_id`, `is_active`) |
| POST | `/api/admin/media-albums` | Tạo mới (auto-slug từ `title` nếu bỏ trống; unique theo `event_id`) |
| GET | `/api/admin/media-albums/{id}` | Chi tiết |
| PUT | `/api/admin/media-albums/{id}` | Cập nhật |
| DELETE | `/api/admin/media-albums/{id}` | Xoá mềm (cascade `media_items`) |

Body: `event_id`(bắt buộc), `title`(bắt buộc), `slug, description, cover_image, badge, type(photo|video|mixed), sort_order, is_active`.

### Media Item
| Method | URL | Mô tả |
|---|---|---|
| GET | `/api/admin/media-items?album_id={id}` | Danh sách theo album |
| POST | `/api/admin/media-items` | Thêm item (cập nhật `media_albums.item_count`) |
| GET | `/api/admin/media-items/{id}` | Chi tiết |
| PUT | `/api/admin/media-items/{id}` | Cập nhật |
| DELETE | `/api/admin/media-items/{id}` | Xoá (cập nhật lại `item_count`) |

Body: `album_id`(bắt buộc), `event_id`(bắt buộc), `type(image|video)`, `url`(bắt buộc), `thumbnail, title, caption, sort_order, is_active`.

---

## 3. Ghi chú tích hợp
- Frontend đã có **fallback tĩnh**: khi `/api/daihoi/slides` hoặc `/albums` trả rỗng, trang vẫn hiển thị nội dung mẫu — bật dần không gây lỗi.
- Hero slider render động theo **số slide thực tế** (dots + counter tự khớp).
- `event_id` nên lấy theo sự kiện đang active; các public endpoint không cần truyền `event_id` (backend tự xác định sự kiện hiện hành như các `/api/daihoi/*` khác).
