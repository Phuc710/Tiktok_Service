# REST API — KaiTiktok

## Authentication

Tất cả API endpoint yêu cầu header:

```
X-Api-Key: <your_api_key>
```

API Key được cấu hình trong file `.env`:
```env
API_KEY=your_secret_key_here
```

---

## Endpoints

### GET /api?url={tiktok_url}

Lấy thông tin một video TikTok.

**Request:**
```
GET /api?url=https://www.tiktok.com/@username/video/1234567890
X-Api-Key: your_key
```

**Response thành công:**
```json
{
  "success": true,
  "data": {
    "id": "1234567890",
    "title": "Tiêu đề video",
    "cover": "https://...",
    "play_url": "https://...",
    "music_url": "https://...",
    "author": {
      "name": "username",
      "nickname": "Tên hiển thị",
      "avatar": "https://..."
    },
    "stats": {
      "views": 100000,
      "likes": 5000,
      "comments": 200,
      "shares": 100
    },
    "is_slideshow": false,
    "images": []
  }
}
```

---

### POST /api — Single Video (JSON Body)

```json
{
  "url": "https://www.tiktok.com/@username/video/1234567890"
}
```

---

### POST /api — Bulk Videos (tối đa 50 URLs)

```json
{
  "urls": [
    "https://www.tiktok.com/@user/video/111",
    "https://www.tiktok.com/@user/video/222"
  ],
  "concurrency": 8
}
```

**Response:**
```json
{
  "success": true,
  "data": [
    { "url": "...", "status": "ok", "data": { ... } },
    { "url": "...", "status": "error", "data": null }
  ]
}
```

---

## Error Responses

| HTTP Code | Ý nghĩa                    |
|-----------|----------------------------|
| 400       | Thiếu hoặc sai tham số     |
| 401       | Sai hoặc thiếu API Key     |
| 404       | Không tìm thấy video       |
| 500       | Lỗi server                 |

```json
{
  "success": false,
  "error": "Mô tả lỗi..."
}
```
