# Boost View Engine — KaiTiktok

## Cách hoạt động

Engine boost view hoạt động bằng cách gửi hàng loạt request **giả lập thiết bị Android thật** đến API nội bộ của TikTok, mỗi request mang chữ ký điện tử `X-Gorgon` để qua mặt hệ thống bảo mật.

```
User → /boost (UI)
         ↓
     RESOLVE_ENDPOINT (/boost/resolve)
         ↓ video_id
     RUN_ENDPOINT (/boost/run) × N lần (loop)
         ↓ stats JSON
     Cập nhật UI real-time
```

---

## Files liên quan

| File | Vai trò |
|------|---------|
| `templates/booster.php` | Giao diện người dùng + JavaScript loop |
| `src/Services/BuffService.php` | Engine chạy curl_multi, quản lý batch |
| `src/Models/Device.php` | Giả lập thông số thiết bị Android ngẫu nhiên |
| `src/Models/BoostStats.php` | Thu thập & tính toán số liệu thống kê |
| `src/Core/Signature.php` | Tạo chữ ký X-Gorgon cho mỗi request |

---

## API Endpoints (AJAX)

### POST /boost/resolve
Trích xuất Video ID từ TikTok URL.

**Request:**
```json
{ "url": "https://www.tiktok.com/@username/video/1234567890" }
```
**Response:**
```json
{ "success": true, "video_id": "1234567890" }
```

---

### POST /boost/run
Chạy một batch boost và trả về stats.

**Request:**
```json
{ "video_id": "1234567890", "batch_size": 100 }
```
**Response:**
```json
{
  "success": true,
  "stats": {
    "success": 87,
    "failed": 13,
    "vps": 12.4,
    "vpm": 744,
    "peak_vps": 15.2,
    "success_rate": 87.0,
    "elapsed": 7
  }
}
```

---

## Cấu hình (config.php)

```php
'boost' => [
    'batch_size'  => 100,   // Số request mỗi đợt (10–500)
    'batch_delay' => 0.05,  // Delay mặc định giữa các đợt (giây)
    'device_pool' => [      // Thêm thiết bị vào đây để đa dạng hơn
        ['model' => 'Pixel 7', 'os' => '13', 'api' => 33, 'brand' => 'Google'],
        // ...
    ],
],
```

---

## Adaptive Delay

Engine tự động điều chỉnh delay giữa các batch theo success rate:

| Success Rate | Delay multiplier |
|--------------|------------------|
| ≥ 70%        | × 1 (nhanh)      |
| 40–70%       | × 2              |
| < 40%        | × 4 (nghỉ lâu để tránh bị chặn) |
