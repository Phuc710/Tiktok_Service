<?php

return [
    'app_name' => 'Tiktok Service',
    'app_title' => 'Tiktok Service - Trình Tải Video',
    'api_url' => 'https://www.tikwm.com/api/',
    'author_url' => 'https://www.kaishop.id.vn',
    'author_name' => 'Kai',

    /**
     * Tự động xác định Base URL (Domain động)
     */
    'base_url' => (function() {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        // Lấy path của thư mục chứa index.php, loại bỏ /index.php ở cuối
        $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        // Nếu ở web root thì scriptDir = '' hoặc '/', đều trả về ''
        $base = ($scriptDir === '/' || $scriptDir === '.') ? '' : $scriptDir;
        return "$scheme://$host$base";
    })(),

    /**
     * Proxy tuỳ chọn (để null nếu không dùng)
     * Format:
     *   "ip:port"                - HTTP proxy không xác thực
     *   "user:password@ip:port"  - HTTP proxy có xác thực
     *   "socks5://ip:port"       - SOCKS5 proxy
     */
    'proxy' => null,

    /**
     * Số luồng đồng thời mặc định cho Bulk download
     */
    'default_concurrency' => 8,

    /**
     * Cấu hình HTTP & Anti-detect
     */
    'http' => [
        'timeout' => 30,
        'connect_timeout' => 15,
        'user_agents' => [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (iPad; CPU OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.6099.144 Mobile Safari/537.36',
            'Mozilla/5.0 (Linux; Android 12; SM-G998B) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/21.0 Chrome/110.0.5481.154 Mobile Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 Edg/122.0.0.0',
        ],
        'default_headers' => [
            'Accept: application/json, text/html, */*',
            'Accept-Language: vi-VN,vi;q=0.9,en-US;q=0.8,en;q=0.7',
        ]
    ],

    /**
     * ⚡ Boost Engine Config
     */
    'boost' => [
        // Số request gửi đồng thời mỗi đợt (khuyên 50–200)
        'batch_size' => 100,
        // Delay mặc định giữa các đợt (giây) — Adaptive delay sẽ tự điều chỉnh
        'batch_delay' => 0.05,
        // Danh sách thiết bị Android giả lập — thêm thoải mái
        'device_pool' => [
            ['model' => 'Pixel 6', 'os' => '12', 'api' => 31, 'brand' => 'Google'],
            ['model' => 'Pixel 7', 'os' => '13', 'api' => 33, 'brand' => 'Google'],
            ['model' => 'SM-G998B', 'os' => '13', 'api' => 33, 'brand' => 'Samsung'],
            ['model' => 'SM-A525F', 'os' => '12', 'api' => 31, 'brand' => 'Samsung'],
            ['model' => 'CPH2387', 'os' => '12', 'api' => 31, 'brand' => 'OPPO'],
            ['model' => '2201122G', 'os' => '12', 'api' => 31, 'brand' => 'Xiaomi'],
            ['model' => 'RMX3370', 'os' => '11', 'api' => 30, 'brand' => 'Realme'],
        ],
    ],
];
