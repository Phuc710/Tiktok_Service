# Routing System — KaiTiktok

## Kiến trúc: Front Controller Pattern

Mọi HTTP request đều đi qua **một file duy nhất**: `public/index.php`.  
Apache `.htaccess` rewrite rule sẽ chuyển hướng tất cả traffic vào đó.

---

## Route Table

| Method | Path            | Handler (trong index.php) | Mô tả                            |
|--------|-----------------|---------------------------|----------------------------------|
| GET    | `/`             | Downloader page           | Trang chủ, form nhập link TikTok |
| POST   | `/`             | Xử lý form tải video      | Submit form → lấy thông tin video|
| GET    | `/boost`        | Boost View page           | Giao diện tăng lượt xem         |
| POST   | `/boost/resolve`| AJAX: extract video ID    | Trả về video_id từ TikTok URL   |
| POST   | `/boost/run`    | AJAX: run one boost batch | Chạy 1 đợt boost, trả về stats  |
| GET    | `/api`          | REST API (JSON)           | Lấy info video (GET ?url=...)    |
| POST   | `/api`          | REST API (JSON)           | Single hoặc bulk video info      |
| GET    | `/download`     | Force-download proxy      | Stream file xuống máy người dùng |
| *      | `/*` (khác)     | 404 handler               | Trang không tồn tại              |

---

## URL Format

- **Không có đuôi `.php`**: `/boost` thay vì `/boost.php`
- **Nested routes**: `/boost/resolve`, `/boost/run`
- **Query params**: `/api?url=...`, `/download?url=...&filename=...`

---

## Cách thêm Route mới

Mở `public/index.php`, thêm block vào vị trí phù hợp:

```php
// GET /my-new-page
if ($path === '/my-new-page' && $method === 'GET') {
    $pageTitle  = 'Trang mới';
    $activePage = 'my-new-page'; // highlight nav item
    renderTemplate('header');
    renderTemplate('my_new_template'); // templates/my_new_template.php
    renderTemplate('footer');
    exit();
}
```

---

## Navbar Active Highlighting

Biến `$activePage` dùng để highlight menu đang active trong `header.php`.

| Giá trị        | Menu được highlight |
|----------------|---------------------|
| `'downloader'` | Nút "Downloader"    |
| `'booster'`    | Nút "Boost View"    |
| `''`           | Không highlight gì  |

---

## Bảo mật

- `/api`, `/boost/resolve`, `/boost/run` yêu cầu header `X-Api-Key` hợp lệ
- `/download` chỉ chấp nhận domain TikTok CDN (whitelist), ngăn chặn SSRF
- Mọi URL params đều được validate trước khi xử lý
