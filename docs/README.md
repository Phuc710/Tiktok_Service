# KaiTiktok — Project Documentation

## Tổng quan

KaiTiktok là một web app PHP cho phép:
- **Tải video TikTok** không logo (Watermark-free), HD
- **Tải ảnh Slideshow** từ TikTok
- **Tải nhạc (MP3)** từ video TikTok
- **Boost View** (Tăng lượt xem tự động)
- **REST API** cho bên thứ 3 tích hợp

---

## Cấu trúc thư mục

```
Tiktok_Downloading/
├── public/                  ← Web root (Apache/Nginx trỏ vào đây)
│   ├── index.php            ← 🔥 Front Controller / Router duy nhất
│   ├── .htaccess            ← Apache rewrite: mọi request → index.php
│   └── assets/
│       ├── css/
│       │   ├── layout.css   ← Grid, flex, container utilities
│       │   ├── navbar.css   ← Shared header/navbar (dùng chung mọi trang)
│       │   └── style.css    ← Styles riêng của từng component/trang
│       └── js/
│           └── main.js      ← Frontend JS chung
├── src/                     ← Business Logic (PSR-4: App\...)
│   ├── Core/
│   │   ├── Auth.php         ← API Key authentication
│   │   ├── EnvLoader.php    ← Load biến môi trường từ .env
│   │   ├── HttpClient.php   ← cURL wrapper (single + multi-thread)
│   │   └── Signature.php    ← X-Gorgon signature (cho Boost engine)
│   ├── Models/
│   │   ├── VideoData.php    ← Model dữ liệu video TikTok
│   │   ├── BoostStats.php   ← Model thống kê kết quả boost
│   │   └── Device.php       ← Giả lập thiết bị Android (cho boost)
│   └── Services/
│       ├── TikTokService.php ← Giao tiếp với TikWM API (lấy link tải)
│       └── BuffService.php   ← Engine boost view (curl_multi)
├── templates/               ← HTML Templates (PHP views)
│   ├── header.php           ← Shared HTML head + navbar (include đầu tiên)
│   ├── footer.php           ← Shared footer (include cuối cùng)
│   ├── results.php          ← Kết quả tải video (sau khi submit form)
│   └── booster.php          ← Giao diện trang Boost View
├── config/
│   └── config.php           ← Toàn bộ config app (base_url, api_url, v.v.)
├── .env                     ← Biến môi trường bí mật (API_KEY, v.v.)
└── docs/                    ← Tài liệu dự án (thư mục này)
    ├── README.md            ← File này
    ├── routing.md           ← Chi tiết hệ thống routing
    ├── api.md               ← REST API Documentation
    └── boost.md             ← Boost View Engine Documentation
```

---

## Khởi chạy (Development)

```bash
# Chạy PHP built-in server (trỏ vào thư mục public/)
php -S localhost:8080 -t public
```

Sau đó mở trình duyệt: `http://localhost:8080`

---

## Cấu hình (.env)

```env
API_KEY=your_secret_key_here
```

---

## Xem thêm

- [Routing System](routing.md)
- [REST API](api.md)
- [Boost View Engine](boost.md)
